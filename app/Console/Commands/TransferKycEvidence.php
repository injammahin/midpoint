<?php

namespace App\Console\Commands;

use App\Models\SellerKycVerification;
use App\Services\TrustedKycRegistry;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class TransferKycEvidence extends Command
{
    protected $signature = 'kyc:transfer {action : key, export, or import}
        {--file= : Absolute private file path; defaults to storage/app/private/kyc-evidence.enc}
        {--dry-run : Validate an import and its database conflicts, then roll back}';

    protected $description = 'Transfer only trusted BVN/bank identity evidence; never users, wallets, or transactions';

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        if ($action === 'key') {
            $this->line(bin2hex(random_bytes(32)));
            $this->warn('New transfer key only. Keep it private and send it separately from the encrypted evidence file.');

            return self::SUCCESS;
        }
        if (!in_array($action, ['export', 'import'], true)) {
            $this->error('Use key, export, or import.');

            return self::FAILURE;
        }

        try {
            $registry = app(TrustedKycRegistry::class);
            $file = (string) ($this->option('file') ?: storage_path('app/private/kyc-evidence.enc'));
            $key = trim((string) $this->secret('Enter the 64-character transfer key (not APP_KEY or a Paystack key)', false));
            if (preg_match('/\A[a-fA-F0-9]{64}\z/', $key) !== 1) {
                throw new RuntimeException('A random 64-character hexadecimal transfer key is required.');
            }
            $cipher = new Encrypter(hex2bin($key), 'AES-256-GCM');

            if ($action === 'export') {
                return $this->exportEvidence($registry, $cipher, $file);
            }

            return $this->importEvidence($registry, $cipher, $file);
        } catch (Throwable $exception) {
            // An exception can contain SQL bindings or personal data. Do not
            // echo it, report it, or print a stack trace from this command.
            $this->error('Stopped safely. Check the transfer key, private path, original encryption/fingerprint configuration, same Paystack live key/mode, and evidence eligibility. No existing evidence is overwritten.');

            return self::FAILURE;
        }
    }

    private function exportEvidence(TrustedKycRegistry $registry, Encrypter $cipher, string $file): int
    {
        $payloads = [];
        $skipped = 0;
        SellerKycVerification::query()->with('withdrawalAccount')
            ->where('provider', 'paystack')->where('status', SellerKycVerification::STATUS_APPROVED)
            ->chunkById(100, function ($records) use ($registry, &$payloads, &$skipped) {
                foreach ($records as $record) {
                    if (data_get($record->provider_response, 'verification_source') === TrustedKycRegistry::SOURCE) {
                        continue; // Export the independent evidence row below.
                    }
                    try {
                        $payload = $registry->fromVerification($record);
                    } catch (Throwable $exception) {
                        $skipped++;
                        $this->warn('Skipped KYC #' . (int) $record->id . ': original evidence is incomplete, inconsistent, or cannot be decrypted.');
                        continue;
                    }
                    $this->addPayload($registry, $payloads, $payload);
                }
            });

        if (Schema::hasTable('trusted_kyc_identities')) {
            DB::table('trusted_kyc_identities')->whereNull('revoked_at')
                ->where('environment', $registry->environment())->orderBy('id')
                ->chunkById(100, function ($rows) use ($registry, &$payloads) {
                    foreach ($rows as $row) {
                        $this->addPayload($registry, $payloads, $registry->exportPayload($row));
                    }
                });
        }

        if (!$payloads) {
            $this->error('No eligible identities. Do not change approved flags to force an export. A fresh authoritative verification or original verification evidence is required.');

            return self::FAILURE;
        }
        $bundle = [
            'format' => 'midpoint-kyc-evidence-v1', 'exported_at' => now()->toIso8601String(),
            'environment' => $registry->environment(), 'merchant_fingerprint' => $registry->merchantFingerprint(),
            'identities' => array_values($payloads),
        ];
        $encrypted = $cipher->encryptString(json_encode($bundle, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        if (strlen($encrypted) > 20 * 1024 * 1024) {
            throw new RuntimeException('The encrypted export exceeds the supported size.');
        }

        $mask = umask(0077);
        $handle = false;
        try {
            if (!is_dir(dirname($file)) && !mkdir(dirname($file), 0700, true) && !is_dir(dirname($file))) {
                throw new RuntimeException('Could not create the private directory.');
            }
            $this->ensurePrivatePath($file);
            $handle = fopen($file, 'xb'); // Never overwrite any existing file.
            if ($handle === false || fwrite($handle, $encrypted) !== strlen($encrypted)) {
                throw new RuntimeException('Could not write the complete encrypted export.');
            }
            fflush($handle);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            umask($mask);
        }

        $this->info('Exported ' . count($payloads) . ' trusted identity/bank combinations. Skipped KYC rows: ' . $skipped . '.');
        $this->line('Encrypted file: ' . $file);
        $this->warn('Treat this file as sensitive. Transfer privately, never place it in public/, and keep the key separate.');

        return self::SUCCESS;
    }

    private function addPayload(TrustedKycRegistry $registry, array &$payloads, array $payload): void
    {
        $key = $registry->bankFingerprint($payload['bank_code'], $payload['account_number']);
        if (isset($payloads[$key])) {
            foreach (['bvn', 'bank_code', 'account_number', 'first_name', 'middle_name', 'last_name', 'recorded_date_of_birth', 'account_name'] as $field) {
                if ($payloads[$key][$field] !== $payload[$field]) {
                    throw new RuntimeException('Conflicting original evidence requires a manual review.');
                }
            }
            return;
        }
        if (count($payloads) >= 10000) {
            throw new RuntimeException('This transfer supports a maximum of 10000 bank identities.');
        }
        $payloads[$key] = $payload;
    }

    private function importEvidence(TrustedKycRegistry $registry, Encrypter $cipher, string $file): int
    {
        $this->ensurePrivatePath($file);
        if (!is_file($file) || filesize($file) > 20 * 1024 * 1024 || !Schema::hasTable('trusted_kyc_identities')) {
            throw new RuntimeException('The import file or destination migration is missing/invalid.');
        }
        $bundle = json_decode($cipher->decryptString(file_get_contents($file)), true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($bundle) || ($bundle['format'] ?? '') !== 'midpoint-kyc-evidence-v1'
            || ($bundle['environment'] ?? '') !== $registry->environment()
            || !hash_equals($registry->merchantFingerprint(), (string) ($bundle['merchant_fingerprint'] ?? ''))
            || !is_array($bundle['identities'] ?? null) || !$bundle['identities']
            || !array_is_list($bundle['identities']) || count($bundle['identities']) > 10000) {
            throw new RuntimeException('The transfer does not match this Paystack key/mode or format.');
        }

        // Validate every record before writing any of them.
        $payloads = [];
        foreach ($bundle['identities'] as $payload) {
            $this->addPayload($registry, $payloads, $registry->validatePayload($payload));
        }
        $dryRun = (bool) $this->option('dry-run');
        if (!$dryRun && !$this->confirm('Import only these trusted KYC identities into this database? Users, banks, wallets and transactions will NOT be imported.', false)) {
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            foreach ($payloads as $payload) {
                $registry->store($payload, 'encrypted_import');
            }
            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Throwable $exception) {
            DB::rollBack();
            throw new RuntimeException('The entire import was rolled back.');
        }

        $this->info(($dryRun ? 'Dry run passed; rolled back ' : 'Imported/retained ') . count($payloads) . ' identities.');
        $this->line('Each seller must still add/activate their own verified bank and submit the matching BVN and KYC details.');

        return self::SUCCESS;
    }

    private function ensurePrivatePath(string $file): void
    {
        $parent = realpath(dirname($file));
        $public = realpath(public_path());
        if ($parent === false || $public === false || is_link($file)
            || $parent === $public || str_starts_with($parent, $public . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Use a regular file outside the public directory.');
        }
    }
}
