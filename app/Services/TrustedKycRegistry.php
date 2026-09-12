<?php

namespace App\Services;

use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use App\Support\KycIdentityFingerprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Reuses trusted application evidence of a completed Paystack BVN/bank check.
 * This is NOT a new Paystack verification, a name-only match, or proof of DOB.
 */
class TrustedKycRegistry
{
    public const SOURCE = 'trusted_identity_registry';

    public function __construct(protected KycIdentityFingerprint $fingerprints)
    {
    }

    public function environment(): string
    {
        $key = (string) config('services.paystack.secret_key');
        $mode = str_starts_with($key, 'sk_live_') ? 'live'
            : (str_starts_with($key, 'sk_test_') ? 'test' : '');

        if ($mode === '' || $mode !== (string) config('services.paystack.mode')) {
            throw new RuntimeException('Paystack key mode and configured mode must agree.');
        }

        return $mode;
    }

    public function merchantFingerprint(): string
    {
        $this->environment();

        return hash('sha256', (string) config('services.paystack.secret_key'));
    }

    public function bankFingerprint(string $bankCode, string $accountNumber): string
    {
        $key = (string) config('midpoint.kyc.fingerprint_key');

        if ($key === '') {
            throw new RuntimeException('The KYC fingerprint key is not configured.');
        }

        return hash_hmac('sha256', 'midpoint:bank:v1|' . $bankCode . '|' . $accountNumber, $key);
    }

    public function normalizeName(string $name): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', strtolower(Str::ascii($name))));
    }

    private function bankName(string $name): string
    {
        $parts = preg_split('/\s+/', $this->normalizeName($name), -1, PREG_SPLIT_NO_EMPTY);
        sort($parts, SORT_STRING);

        return implode(' ', $parts);
    }

    /** Only the exact, expected export fields survive validation. */
    public function validatePayload(array $payload): array
    {
        $rules = [
            'version' => ['required', 'integer', 'in:1'],
            'environment' => ['required', 'in:live,test'],
            'merchant_fingerprint' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'bvn' => ['required', 'string', 'regex:/\A[0-9]{11}\z/'],
            'bank_code' => ['required', 'string', 'regex:/\A[0-9]{1,50}\z/'],
            'account_number' => ['required', 'string', 'regex:/\A[0-9]{10}\z/'],
            'account_name' => ['required', 'string', 'max:180'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'recorded_date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'customer_code' => ['required', 'string', 'regex:/\ACUS_[a-zA-Z0-9]+\z/'],
            'verified_at' => ['required', 'date'],
        ];

        $validator = Validator::make($payload, $rules);
        if ($validator->fails()) {
            // Do not attach the invalid payload or validator to an exception.
            throw new RuntimeException('The identity evidence is incomplete or invalid.');
        }

        $safe = array_intersect_key($payload, $rules);
        $safe['middle_name'] = (string) ($safe['middle_name'] ?? '');

        if ($safe['environment'] !== $this->environment()
            || !hash_equals($this->merchantFingerprint(), $safe['merchant_fingerprint'])
            || strtotime($safe['verified_at']) > time() + 300
            || $this->normalizeName($safe['first_name']) === ''
            || $this->normalizeName($safe['last_name']) === ''
            || $this->bankName($safe['account_name']) === '') {
            throw new RuntimeException('The evidence belongs to a different Paystack key/mode or has invalid identity details.');
        }

        return $safe;
    }

    /**
     * The OLD application's trusted DB is the source of truth here. Never
     * manufacture these flags for a historic/manual/metadata-only approval.
     */
    public function fromVerification(SellerKycVerification $kyc): array
    {
        $bank = $kyc->withdrawalAccount;
        $audit = $kyc->provider_response ?? [];

        if (!$bank || !$bank->is_verified
            || (int) $bank->seller_id !== (int) $kyc->seller_id
            || $kyc->status !== SellerKycVerification::STATUS_APPROVED
            || $kyc->provider !== 'paystack'
            || $kyc->provider_environment !== $this->environment()
            || $kyc->paystack_identification_status !== 'success'
            || !$kyc->paystack_identification_requested_at
            || !$kyc->paystack_identification_completed_at
            || $kyc->name_match !== true || $kyc->bank_name_match !== true
            || data_get($audit, 'verification_source') !== 'signed_webhook'
            || data_get($audit, 'exact_bvn_confirmed') !== true
            || data_get($audit, 'customer_identified_before_submission') !== false
            || data_get($audit, 'customer_identified_after_submission') !== true
            || data_get($audit, 'status') !== 'success'
            || data_get($audit, 'identification.country') !== 'NG'
            || data_get($audit, 'identification.type') !== 'bank_account') {
            throw new RuntimeException('No eligible original signed-webhook evidence.');
        }

        $bvn = (string) $kyc->id_number; // Decrypted by the ORIGINAL APP_KEY.
        $accountNumber = (string) $bank->account_number;
        if (!hash_equals($this->fingerprints->make($bvn), (string) $kyc->identity_fingerprint)
            || !hash_equals(hash('sha256', $accountNumber), (string) $bank->account_number_hash)
            || (string) data_get($audit, 'customer_code') !== (string) $kyc->paystack_customer_code
            || (string) data_get($audit, 'identification.bank_code') !== (string) $bank->bank_code
            || !$this->matchesOriginalMask((string) data_get($audit, 'identification.bvn'), $bvn)
            || !$this->matchesOriginalMask((string) data_get($audit, 'identification.account_number'), $accountNumber)
            || $this->normalizeName((string) data_get($audit, 'submitted_first_name')) !== $this->normalizeName((string) $kyc->identity_first_name)
            || $this->normalizeName((string) data_get($audit, 'submitted_last_name')) !== $this->normalizeName((string) $kyc->identity_last_name)) {
            throw new RuntimeException('The saved verification evidence no longer matches the original BVN/bank submission.');
        }

        return $this->validatePayload([
            'version' => 1,
            'environment' => $kyc->provider_environment,
            'merchant_fingerprint' => $this->merchantFingerprint(),
            'bvn' => $bvn,
            'bank_code' => (string) $bank->bank_code,
            'account_number' => $accountNumber,
            'account_name' => (string) $bank->account_name,
            'first_name' => (string) $kyc->identity_first_name,
            'middle_name' => (string) data_get($audit, 'submitted_middle_name', ''),
            'last_name' => (string) $kyc->identity_last_name,
            'recorded_date_of_birth' => $kyc->date_of_birth->format('Y-m-d'),
            'customer_code' => (string) $kyc->paystack_customer_code,
            'verified_at' => $kyc->paystack_identification_completed_at->toIso8601String(),
        ]);
    }

    private function matchesOriginalMask(string $mask, string $number): bool
    {
        // This checks consistency of original evidence. It NEVER validates a
        // newly submitted BVN: new submissions are compared in full below.
        return preg_match('/\A([0-9]{3})\*+([0-9]{3})\z/', $mask, $matches) === 1
            && hash_equals(substr($number, 0, 3) . substr($number, -3), $matches[1] . $matches[2]);
    }

    public function store(array $payload, string $origin): object
    {
        $payload = $this->validatePayload($payload);
        $bank = $this->bankFingerprint($payload['bank_code'], $payload['account_number']);
        $identity = $this->fingerprints->make($payload['bvn']);
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return DB::transaction(function () use ($payload, $bank, $identity, $json, $origin) {
            DB::table('trusted_kyc_identities')->insertOrIgnore([
                'environment' => $payload['environment'],
                'bank_fingerprint' => $bank,
                'identity_fingerprint' => $identity,
                'payload_encrypted' => Crypt::encryptString($json),
                'payload_digest' => hash('sha256', $json),
                'origin' => $origin,
                'verified_at' => date('Y-m-d H:i:s', strtotime($payload['verified_at'])),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $row = DB::table('trusted_kyc_identities')->where('environment', $payload['environment'])
                ->where('bank_fingerprint', $bank)->lockForUpdate()->first();
            if (!$row || $row->revoked_at || !hash_equals((string) $row->identity_fingerprint, $identity)) {
                throw new RuntimeException('A conflicting or revoked bank identity requires a manual review. No evidence was overwritten.');
            }
            $existing = $this->decode($row);
            foreach (['first_name', 'middle_name', 'last_name', 'recorded_date_of_birth', 'account_name'] as $field) {
                if ($field === 'recorded_date_of_birth'
                    ? $existing[$field] !== $payload[$field]
                    : $this->normalizeName($existing[$field]) !== $this->normalizeName($payload[$field])) {
                    throw new RuntimeException('Conflicting identity details require a manual review.');
                }
            }

            return $row;
        }, 3);
    }

    public function exportPayload(object $row): array
    {
        return $this->decode($row);
    }

    private function decode(object $row): array
    {
        $json = Crypt::decryptString($row->payload_encrypted);
        if (!hash_equals((string) $row->payload_digest, hash('sha256', $json))) {
            throw new RuntimeException('Identity evidence integrity check failed.');
        }
        $payload = $this->validatePayload(json_decode($json, true, 32, JSON_THROW_ON_ERROR));
        if ($row->revoked_at || $row->environment !== $payload['environment']
            || !hash_equals((string) $row->identity_fingerprint, $this->fingerprints->make($payload['bvn']))
            || !hash_equals((string) $row->bank_fingerprint, $this->bankFingerprint($payload['bank_code'], $payload['account_number']))) {
            throw new RuntimeException('Identity evidence is revoked or inconsistent.');
        }

        return $payload;
    }

    public function approveForSeller(User $seller, array $data): ?SellerKycVerification
    {
        return DB::transaction(function () use ($seller, $data) {
            User::query()->whereKey($seller->id)->lockForUpdate()->firstOrFail();
            $bank = SellerWithdrawalAccount::query()->where('seller_id', $seller->id)
                ->where('is_verified', true)->where('is_active', true)->lockForUpdate()->first();
            if (!$bank) {
                throw ValidationException::withMessages(['bvn' => 'Add, verify and activate a withdrawal bank account first.']);
            }
            $bankFingerprint = $this->bankFingerprint((string) $bank->bank_code, (string) $bank->account_number);
            $row = DB::table('trusted_kyc_identities')->where('environment', $this->environment())
                ->where('bank_fingerprint', $bankFingerprint)->lockForUpdate()->first();

            if (!$row) {
                // Lazily retain eligible fresh verifications too. No other
                // seller's wallet, user record, or KYC row is ever copied.
                $sources = SellerKycVerification::query()->with('withdrawalAccount')
                    ->where('status', SellerKycVerification::STATUS_APPROVED)
                    ->where('provider', 'paystack')->where('provider_environment', $this->environment())
                    ->whereHas('withdrawalAccount', fn ($query) => $query
                        ->where('bank_code', $bank->bank_code)->where('account_number_hash', $bank->account_number_hash))
                    ->get();
                foreach ($sources as $source) {
                    if (data_get($source->provider_response, 'verification_source') !== 'signed_webhook') {
                        continue;
                    }
                    try {
                        $payload = $this->fromVerification($source);
                    } catch (Throwable $exception) {
                        continue; // An old approved flag alone is not evidence.
                    }
                    $row = $this->store($payload, 'local_signed_webhook');
                }
            }

            if (!$row) {
                return null; // Only then may the normal fresh Paystack flow run.
            }

            $payload = $this->decode($row);
            $this->assertSubmissionMatches($payload, $bank, $data);
            $record = SellerKycVerification::query()->where('seller_id', $seller->id)->lockForUpdate()->first()
                ?: new SellerKycVerification();
            $record->fill([
                'seller_id' => $seller->id,
                'legal_name' => trim(implode(' ', array_filter([$data['first_name'], $data['middle_name'] ?? '', $data['last_name']]))),
                'date_of_birth' => $data['date_of_birth'], 'country_code' => 'NG', 'id_type' => 'bvn',
                'id_number' => $data['bvn'], 'identity_fingerprint' => $this->fingerprints->make($data['bvn']),
                'document_front_path' => '', 'document_back_path' => null, 'selfie_path' => '',
                'status' => SellerKycVerification::STATUS_APPROVED,
                'provider' => 'paystack', 'provider_environment' => $this->environment(),
                'provider_status' => 'trusted_identity_match', 'verification_method' => 'trusted_bvn_bank_reuse',
                // Do not attach another seller's Paystack customer to this seller.
                'paystack_identification_status' => 'success',
                'paystack_identification_requested_at' => null, 'paystack_identification_completed_at' => now(),
                'identity_first_name' => $payload['first_name'], 'identity_middle_name' => null,
                'identity_last_name' => $payload['last_name'], 'identity_date_of_birth' => null,
                'name_match' => true, 'bank_name_match' => true, 'dob_match' => null,
                'liveness_passed' => null, 'liveness_probability' => null, 'face_match' => null, 'face_confidence' => null,
                'seller_withdrawal_account_id' => $bank->id,
                'reused_from_kyc_id' => null, 'identity_reused_at' => now(),
                'failure_code' => null, 'failure_message' => null, 'rejection_reason' => null,
                'reviewed_by' => null, 'reviewed_at' => null, 'rejected_at' => null,
                'approved_at' => now(), 'auto_verified_at' => now(), 'submitted_at' => now(),
                'verification_attempts' => (int) $record->verification_attempts + 1,
                'last_verification_attempt_at' => now(),
                'provider_response' => [
                    'verification_source' => self::SOURCE, 'exact_bvn_confirmed' => true,
                    'trusted_evidence_id' => $row->id, 'trusted_evidence_digest' => $row->payload_digest,
                    'original_verified_at' => $payload['verified_at'], 'bank_code' => $bank->bank_code,
                    'bank_account_last4' => $bank->account_number_last4,
                    'date_of_birth_source' => 'original_seller_declaration_not_paystack_verified',
                ],
            ]);
            $record->save();

            return $record->fresh();
        }, 3);
    }

    private function assertSubmissionMatches(array $payload, SellerWithdrawalAccount $bank, array $data): void
    {
        if (!hash_equals($payload['bvn'], (string) ($data['bvn'] ?? ''))) {
            throw ValidationException::withMessages(['bvn' => 'The BVN does not match the verified identity for this bank account. Check all 11 digits.']);
        }
        if (!hash_equals($payload['bank_code'], (string) $bank->bank_code)
            || !hash_equals($payload['account_number'], (string) $bank->account_number)
            || $this->bankName($payload['account_name']) !== $this->bankName((string) $bank->account_name)) {
            throw ValidationException::withMessages(['bvn' => 'The active bank details do not match the saved verification evidence. Contact support.']);
        }
        $errors = [];
        foreach (['first_name', 'middle_name', 'last_name'] as $field) {
            if ($this->normalizeName((string) ($data[$field] ?? '')) !== $this->normalizeName($payload[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' does not match the original KYC record.';
            }
        }
        if ((string) ($data['date_of_birth'] ?? '') !== $payload['recorded_date_of_birth']) {
            $errors['date_of_birth'] = 'Date of birth does not match the originally recorded date. This is a record-consistency check, not a Paystack DOB verification.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function isApproved(SellerKycVerification $kyc, ?SellerWithdrawalAccount $bank): bool
    {
        try {
            if (!$bank || !$bank->is_verified || !$bank->is_active
                || (int) $bank->seller_id !== (int) $kyc->seller_id
                || (int) $kyc->seller_withdrawal_account_id !== (int) $bank->id
                || $kyc->status !== SellerKycVerification::STATUS_APPROVED
                || $kyc->provider !== 'paystack' || $kyc->provider_environment !== $this->environment()
                || $kyc->paystack_identification_status !== 'success'
                || !$kyc->paystack_identification_completed_at
                || $kyc->name_match !== true || $kyc->bank_name_match !== true
                || data_get($kyc->provider_response, 'exact_bvn_confirmed') !== true
                || !hash_equals($this->fingerprints->make((string) $kyc->id_number), (string) $kyc->identity_fingerprint)) {
                return false;
            }
            if (data_get($kyc->provider_response, 'verification_source') === 'signed_webhook') {
                $payload = $this->fromVerification($kyc);
                $bankEvidence = DB::table('trusted_kyc_identities')->where('environment', $this->environment())
                    ->where('bank_fingerprint', $this->bankFingerprint((string) $bank->bank_code, (string) $bank->account_number))->first();
                if ($bankEvidence && !hash_equals($this->decode($bankEvidence)['bvn'], $payload['bvn'])) {
                    return false;
                }
            } elseif (data_get($kyc->provider_response, 'verification_source') === self::SOURCE) {
                $row = DB::table('trusted_kyc_identities')->where('id', data_get($kyc->provider_response, 'trusted_evidence_id'))->first();
                if (!$row || !hash_equals($row->payload_digest, (string) data_get($kyc->provider_response, 'trusted_evidence_digest'))) {
                    return false;
                }
                $payload = $this->decode($row);
            } else {
                return false;
            }
            $this->assertSubmissionMatches($payload, $bank, [
                'bvn' => (string) $kyc->id_number, 'first_name' => (string) $kyc->identity_first_name,
                'middle_name' => $payload['middle_name'], 'last_name' => (string) $kyc->identity_last_name,
                'date_of_birth' => $kyc->date_of_birth->format('Y-m-d'),
            ]);

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
}
