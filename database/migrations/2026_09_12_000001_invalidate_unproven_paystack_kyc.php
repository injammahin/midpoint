<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('seller_kyc_verifications')
            ->where('status', 'approved')
            ->where(function ($query) {
                $query
                    ->where('provider', 'paystack')
                    ->orWhere(
                        'verification_method',
                        'paystack_bvn_bank_account'
                    )
                    ->orWhere(
                        'verification_method',
                        'paystack_stored_identity_match'
                    )
                    ->orWhere(
                        'verification_method',
                        'paystack_identity_reuse'
                    );
            })
            ->orderBy('id')
            ->chunkById(
                100,
                function ($records) {
                    foreach ($records as $record) {
                        $response = $record->provider_response;


                        if (is_string($response)) {
                            $decoded = json_decode(
                                $response,
                                true
                            );

                            $response = is_array($decoded)
                                ? $decoded
                                : [];
                        }


                        if (!is_array($response)) {
                            $response = [];
                        }


                        $isProven =
                            $record->provider === 'paystack'
                            && $record->verification_method
                                === 'paystack_bvn_bank_account'
                            && $record->seller_withdrawal_account_id !== null
                            && $record->bank_name_match !== null
                            && (bool) $record->bank_name_match
                            && $record->paystack_identification_status
                                === 'success'
                            && $record->paystack_identification_completed_at
                                !== null
                            && data_get(
                                $response,
                                'verification_source'
                            ) === 'signed_webhook'
                            && data_get(
                                $response,
                                'exact_bvn_confirmed'
                            ) === true;


                        if ($isProven) {
                            continue;
                        }


                        $response['exact_bvn_confirmed'] = false;
                        $response['local_invalidation'] =
                            'unproven_exact_bvn';
                        $response['invalidated_at'] =
                            now()->toIso8601String();


                        DB::table('seller_kyc_verifications')
                            ->where('id', $record->id)
                            ->update([
                                'status' =>
                                    'pending',

                                'provider_status' =>
                                    'invalidated_unproven_exact_bvn',

                                'paystack_identification_status' =>
                                    'invalidated',

                                'name_match' =>
                                    null,

                                'bank_name_match' =>
                                    null,

                                'approved_at' =>
                                    null,

                                'auto_verified_at' =>
                                    null,

                                'paystack_identification_completed_at' =>
                                    null,

                                'reused_from_kyc_id' =>
                                    null,

                                'identity_reused_at' =>
                                    null,

                                'failure_code' =>
                                    'exact_bvn_proof_missing',

                                'failure_message' =>
                                    'The previous KYC approval was invalidated because it was not proven by a matching signed webhook for a fresh Paystack customer, the submitted BVN, and the linked bank account. Verify again using the correct BVN.',

                                'rejection_reason' =>
                                    null,

                                'provider_response' =>
                                    json_encode(
                                        $response,
                                        JSON_UNESCAPED_SLASHES
                                    ),

                                'updated_at' =>
                                    now(),
                            ]);
                    }
                },
                'id'
            );
    }


    public function down(): void
    {
        /*
         * Intentionally irreversible. Restoring an approval without its exact
         * verification evidence would recreate the security flaw.
         */
    }
};
