<?php

namespace App\Console\Commands;

use App\Models\SellerKycVerification;
use App\Support\KycIdentityFingerprint;
use Illuminate\Console\Command;
use Throwable;

class BackfillKycIdentityFingerprints extends Command
{
    protected $signature =
        'seller-kyc:backfill-fingerprints {--dry-run}';

    protected $description =
        'Create secure fingerprints for existing encrypted seller BVNs.';

    public function handle(
        KycIdentityFingerprint $fingerprint
    ): int {
        $updated = 0;
        $failed = 0;
        $dryRun = (bool) $this->option('dry-run');

        SellerKycVerification::query()
            ->whereNull('identity_fingerprint')
            ->chunkById(
                100,
                function ($records) use (
                    $fingerprint,
                    $dryRun,
                    &$updated,
                    &$failed
                ) {
                    foreach ($records as $record) {
                        try {
                            $bvn = trim(
                                (string) $record->id_number
                            );

                            if ($bvn === '') {
                                $failed++;
                                continue;
                            }

                            $value =
                                $fingerprint->make($bvn);

                            if (!$dryRun) {
                                $record->forceFill([
                                    'identity_fingerprint' =>
                                        $value,
                                ])->saveQuietly();
                            }

                            $updated++;
                        } catch (Throwable $exception) {
                            $failed++;

                            $this->warn(
                                'KYC ID '
                                . $record->id
                                . ': '
                                . $exception->getMessage()
                            );
                        }
                    }
                }
            );

        $this->info(
            ($dryRun ? 'Would update ' : 'Updated ')
            . $updated
            . ' fingerprint(s); '
            . $failed
            . ' skipped.'
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}