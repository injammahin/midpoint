<?php

namespace App\Services;

use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use App\Support\KycIdentityFingerprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PaystackSellerKycService
{
    public function __construct(
        protected PaystackService $paystack,
        protected KycIdentityFingerprint $identityFingerprint
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Start Verification
    |--------------------------------------------------------------------------
    */

    public function startVerification(
        User $seller,
        array $data
    ): SellerKycVerification {

        /*
        |--------------------------------------------------------------------------
        | Active Verified Bank
        |--------------------------------------------------------------------------
        */

        $activeBank =
            SellerWithdrawalAccount::query()
                ->where(
                    'seller_id',
                    $seller->id
                )
                ->where(
                    'is_verified',
                    true
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (!$activeBank) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'Add, verify, and activate a withdrawal bank account before verifying your identity.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Existing KYC
        |--------------------------------------------------------------------------
        */

        $existing =
            SellerKycVerification::query()
                ->where(
                    'seller_id',
                    $seller->id
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Already Verified For This Exact Bank
        |--------------------------------------------------------------------------
        */

        if (
            $existing
            &&
            $existing->status
            ===
            SellerKycVerification::STATUS_APPROVED
            &&
            (int)
            $existing
                ->seller_withdrawal_account_id
            ===
            (int)
            $activeBank->id
            &&
            $existing->bank_name_match
            ===
            true
        ) {

            return $existing;
        }


        /*
        |--------------------------------------------------------------------------
        | Don't Submit Duplicate Verification
        |--------------------------------------------------------------------------
        */

        if (
            $existing
            &&
            $existing->status
            ===
            SellerKycVerification::STATUS_PROCESSING
            &&
            $existing
                ->paystack_identification_requested_at
            &&
            $existing
                ->paystack_identification_requested_at
                ->gt(
                    now()->subMinutes(
                        15
                    )
                )
        ) {

            if (
                (int)
                $existing
                    ->seller_withdrawal_account_id
                ===
                (int)
                $activeBank->id
            ) {

                return $existing;
            }


            throw ValidationException::withMessages([

                'bvn' =>
                    'A Paystack identity verification is still processing for your previous active bank. Please wait for that result before verifying another bank account.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Input
        |--------------------------------------------------------------------------
        */

        $firstName =
            trim(
                (string)
                $data['first_name']
            );


        $middleName =
            trim(
                (string) (
                    $data['middle_name']
                    ??
                    ''
                )
            );


        $lastName =
            trim(
                (string)
                $data['last_name']
            );


        $bvn =
            preg_replace(
                '/\D+/',
                '',
                (string)
                $data['bvn']
            );


        if (
            strlen(
                $bvn
            )
            !==
            11
        ) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'BVN must be exactly 11 digits.',

            ]);
        }


        $fingerprint =
            $this
                ->identityFingerprint
                ->make(
                    $bvn
                );


        /*
        |--------------------------------------------------------------------------
        | Reuse A Previous Successful Verification
        |--------------------------------------------------------------------------
        |
        | Paystack identifies customers asynchronously and may reject repeated
        | submissions of the same identity. When explicitly enabled, Midpoint
        | can reuse a previous successful Paystack verification. Reuse requires
        | an exact identity fingerprint, DOB, first/last name, bank code, and
        | account-number match.
        |
        */

        $reusableVerification =
            $this
                ->findReusableVerification(
                    $seller,
                    $activeBank,
                    $fingerprint,
                    $firstName,
                    $middleName,
                    $lastName,
                    (string) $data['date_of_birth']
                );


        if ($reusableVerification) {

            return $this
                ->approveFromReusableVerification(
                    $seller,
                    $existing,
                    $activeBank,
                    $reusableVerification,
                    $data,
                    $firstName,
                    $middleName,
                    $lastName,
                    $bvn,
                    $fingerprint
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Paystack Customer Exists
        |--------------------------------------------------------------------------
        */

        $customer =
            $this
                ->ensurePaystackCustomer(
                    $seller,
                    $firstName,
                    $lastName
                );


        $customerCode =
            trim(
                (string) (
                    $customer[
                        'customer_code'
                    ]
                    ??
                    ''
                )
            );


        $customerId =
            (string) (
                $customer[
                    'id'
                ]
                ??
                ''
            );


        if (
            $customerCode === ''
        ) {

            throw new RuntimeException(
                'Paystack did not return a customer code for this seller.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Save Processing KYC
        |--------------------------------------------------------------------------
        */

        $kyc =
            DB::transaction(
                function () use (
                    $seller,
                    $existing,
                    $activeBank,
                    $data,
                    $firstName,
                    $middleName,
                    $lastName,
                    $bvn,
                    $fingerprint,
                    $customerCode,
                    $customerId
                ) {

                    $record =
                        $existing
                        ??
                        new SellerKycVerification();


                    $legalName =
                        trim(
                            implode(
                                ' ',
                                array_filter([
                                    $firstName,
                                    $middleName,
                                    $lastName,
                                ])
                            )
                        );


                    $record->fill([

                        'seller_id' =>
                            $seller->id,


                        'legal_name' =>
                            $legalName,


                        /*
                         * Kept because the existing Midpoint KYC table
                         * already requires DOB.
                         *
                         * Paystack BVN-bank validation does not use
                         * this DOB value.
                         */

                        'date_of_birth' =>
                            $data[
                                'date_of_birth'
                            ],


                        'country_code' =>
                            'NG',


                        'id_type' =>
                            'bvn',


                        /*
                         * SellerKycVerification encrypts this automatically.
                         */

                        'id_number' =>
                            $bvn,


                        'identity_fingerprint' =>
                            $fingerprint,


                        'reused_from_kyc_id' =>
                            null,


                        'identity_reused_at' =>
                            null,


                        /*
                        |--------------------------------------------------------------------------
                        | Legacy Columns
                        |--------------------------------------------------------------------------
                        |
                        | The old Dojah flow used documents/selfies.
                        | Paystack does not.
                        |
                        */

                        'document_front_path' =>
                            $record
                                ->document_front_path
                            ?:
                            '',


                        'document_back_path' =>
                            null,


                        'selfie_path' =>
                            $record
                                ->selfie_path
                            ?:
                            '',


                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */

                        'status' =>
                            SellerKycVerification::STATUS_PROCESSING,


                        'verification_method' =>
                            'paystack_bvn_bank_account',


                        'provider' =>
                            'paystack',


                        'provider_environment' =>
                            (string)
                            config(
                                'services.paystack.mode',
                                'test'
                            ),


                        'provider_status' =>
                            'processing',


                        /*
                        |--------------------------------------------------------------------------
                        | Old Dojah-specific Results
                        |--------------------------------------------------------------------------
                        */

                        'identity_first_name' =>
                            null,


                        'identity_middle_name' =>
                            null,


                        'identity_last_name' =>
                            null,


                        'identity_date_of_birth' =>
                            null,


                        'liveness_passed' =>
                            null,


                        'liveness_probability' =>
                            null,


                        'face_match' =>
                            null,


                        'face_confidence' =>
                            null,


                        'name_match' =>
                            null,


                        'dob_match' =>
                            null,


                        'bank_name_match' =>
                            null,


                        /*
                        |--------------------------------------------------------------------------
                        | Exact Bank Used For KYC
                        |--------------------------------------------------------------------------
                        */

                        'seller_withdrawal_account_id' =>
                            $activeBank->id,


                        /*
                        |--------------------------------------------------------------------------
                        | Reset Old Failure
                        |--------------------------------------------------------------------------
                        */

                        'failure_code' =>
                            null,


                        'failure_message' =>
                            null,


                        'rejection_reason' =>
                            null,


                        /*
                        |--------------------------------------------------------------------------
                        | Safe Audit Information Only
                        |--------------------------------------------------------------------------
                        |
                        | Never store raw BVN here.
                        |
                        */

                        'provider_response' => [

                            'customer_code' =>
                                $customerCode,


                            'bank_code' =>
                                $activeBank
                                    ->bank_code,


                            'bank_name' =>
                                $activeBank
                                    ->bank_name,


                            'bank_account_last4' =>
                                $activeBank
                                    ->account_number_last4,


                            'submitted_name' =>
                                $legalName,


                            'submitted_first_name' =>
                                $firstName,


                            'submitted_middle_name' =>
                                $middleName !== ''
                                    ? $middleName
                                    : null,


                            'submitted_last_name' =>
                                $lastName,

                        ],


                        'verification_attempts' =>
                            (
                                (int)
                                $record
                                    ->verification_attempts
                            )
                            +
                            1,


                        'last_verification_attempt_at' =>
                            now(),


                        'submitted_at' =>
                            now(),


                        'reviewed_by' =>
                            null,


                        'reviewed_at' =>
                            null,


                        'approved_at' =>
                            null,


                        'rejected_at' =>
                            null,


                        'auto_verified_at' =>
                            null,


                        /*
                        |--------------------------------------------------------------------------
                        | Paystack
                        |--------------------------------------------------------------------------
                        */

                        'paystack_customer_code' =>
                            $customerCode,


                        'paystack_customer_id' =>
                            $customerId !== ''
                                ? $customerId
                                : null,


                        'paystack_identification_status' =>
                            'processing',


                        'paystack_identification_requested_at' =>
                            now(),


                        'paystack_identification_completed_at' =>
                            null,

                    ]);


                    $record->save();


                    return $record->fresh();

                },
                3
            );


        /*
        |--------------------------------------------------------------------------
        | Submit BVN + Active Bank To Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $payload = [

                'country' =>
                    'NG',


                'type' =>
                    'bank_account',


                'account_number' =>
                    $activeBank
                        ->account_number,


                'bvn' =>
                    $bvn,


                'bank_code' =>
                    $activeBank
                        ->bank_code,


                'first_name' =>
                    $firstName,


                'last_name' =>
                    $lastName,

            ];


            if (
                $middleName !== ''
            ) {

                $payload[
                    'middle_name'
                ] =
                    $middleName;
            }


            $response =
                $this
                    ->paystack
                    ->validateCustomerIdentity(
                        $customerCode,
                        $payload
                    );


            $kyc
                ->forceFill([

                    'provider_status' =>
                        'processing',


                    'paystack_identification_status' =>
                        'processing',


                    'provider_response' =>
                        array_merge(
                            $kyc
                                ->provider_response
                            ??
                            [],
                            [

                                'submission_message' =>
                                    (string) (
                                        $response[
                                            'message'
                                        ]
                                        ??
                                        'Customer Identification in progress'
                                    ),

                            ]
                        ),

                ])
                ->save();


            return $kyc->fresh();


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Reconcile An Already-Identified Paystack Customer Safely
            |--------------------------------------------------------------------------
            |
            | Paystack may return HTTP 400 when this customer was identified before
            | Midpoint received the success webhook. Never trust the customer-level
            | `identified` flag by itself. Recovery is allowed only when Paystack's
            | stored identification contains the exact submitted BVN and the verified
            | bank-account holder name matches the identified customer's name.
            |
            */

            if (
                $this
                    ->isAlreadyValidatedWithSameCredentials(
                        $exception
                    )
            ) {

                $reconciled =
                    $this
                        ->reconcileAlreadyValidatedCustomer(
                            $seller,
                            $activeBank,
                            $kyc,
                            $customerCode,
                            $bvn,
                            $firstName,
                            $lastName
                        );


                if ($reconciled) {
                    return $reconciled;
                }


                $message =
                    'Paystack has an existing identity for this email, but it does not exactly match the submitted BVN and verified bank account. Use the correct BVN or contact support to reset the old Paystack customer.';


                $kyc
                    ->forceFill([

                        'status' =>
                            SellerKycVerification::STATUS_PROVIDER_ERROR,


                        'provider_status' =>
                            'existing_identity_mismatch',


                        'paystack_identification_status' =>
                            'existing_identity_mismatch',


                        'name_match' =>
                            false,


                        'bank_name_match' =>
                            false,


                        'approved_at' =>
                            null,


                        'auto_verified_at' =>
                            null,


                        'paystack_identification_completed_at' =>
                            null,


                        'failure_code' =>
                            'paystack_existing_identity_mismatch',


                        'failure_message' =>
                            $message,


                        'rejection_reason' =>
                            null,

                    ])
                    ->save();


                Log::warning(
                    'Rejected unsafe recovery of an already-identified Paystack customer.',
                    [
                        'seller_id' =>
                            $seller->id,

                        'kyc_id' =>
                            $kyc->id,

                        'customer_code' =>
                            $customerCode,
                    ]
                );


                throw ValidationException::withMessages([

                    'bvn' =>
                        $message,

                ]);
            }


            Log::warning(
                'Paystack seller identity verification request failed.',
                [

                    'seller_id' =>
                        $seller->id,


                    'kyc_id' =>
                        $kyc->id,


                    'customer_code' =>
                        $customerCode,


                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            $kyc
                ->forceFill([

                    'status' =>
                        SellerKycVerification::STATUS_PROVIDER_ERROR,


                    'provider_status' =>
                        'request_failed',


                    'paystack_identification_status' =>
                        'request_failed',


                    'failure_code' =>
                        'paystack_request_failed',


                    'failure_message' =>
                        $exception
                            ->getMessage(),


                    'rejection_reason' =>
                        null,

                ])
                ->save();


            throw ValidationException::withMessages([

                'bvn' =>
                    $exception
                        ->getMessage()
                    ?:
                    'Paystack could not start identity verification. Please try again.',

            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Already Validated Paystack Response
    |--------------------------------------------------------------------------
    */

    protected function isAlreadyValidatedWithSameCredentials(
        Throwable $exception
    ): bool {

        $message = strtolower(
            trim(
                $exception
                    ->getMessage()
            )
        );


        return
            str_contains(
                $message,
                'customer already validated using the same credentials'
            )
            &&
            str_contains(
                $message,
                '[http 400]'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reconcile An Exact Stored Paystack Identity
    |--------------------------------------------------------------------------
    |
    | This deliberately fails closed. Paystack must return the full 11-digit
    | stored BVN in the authenticated customer response. An opaque, masked, or
    | mismatching value is not sufficient for approval.
    |
    */

    protected function reconcileAlreadyValidatedCustomer(
        User $seller,
        SellerWithdrawalAccount $activeBank,
        SellerKycVerification $kyc,
        string $customerCode,
        string $bvn,
        string $firstName,
        string $lastName
    ): ?SellerKycVerification {

        try {

            $customer =
                $this
                    ->paystack
                    ->fetchCustomer(
                        $customerCode
                    );

        } catch (
            Throwable $exception
        ) {

            Log::warning(
                'Could not fetch an already-identified Paystack customer for strict reconciliation.',
                [
                    'seller_id' =>
                        $seller->id,

                    'kyc_id' =>
                        $kyc->id,

                    'customer_code' =>
                        $customerCode,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );


            return null;
        }


        if (
            !$customer
            || !((bool) ($customer['identified'] ?? false))
            || !$this
                ->paystackCustomerMatchesSeller(
                    $customer,
                    $seller,
                    $kyc,
                    $customerCode
                )
            || !$this
                ->paystackCustomerNameMatches(
                    $customer,
                    $firstName,
                    $lastName
                )
            || !$this
                ->paystackCustomerHasExactBvn(
                    $customer,
                    $bvn
                )
            || !$this
                ->activeBankMatchesCustomerName(
                    $activeBank,
                    $customer,
                    $seller
                )
        ) {

            return null;
        }


        $verifiedFirstName = trim(
            (string) ($customer['first_name'] ?? '')
        );


        $verifiedLastName = trim(
            (string) ($customer['last_name'] ?? '')
        );


        DB::transaction(
            function () use (
                $seller,
                $activeBank,
                $kyc,
                $customer,
                $customerCode,
                $verifiedFirstName,
                $verifiedLastName
            ) {

                $locked =
                    SellerKycVerification::query()
                        ->whereKey(
                            $kyc->id
                        )
                        ->where(
                            'seller_id',
                            $seller->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $locked
                    ->forceFill([

                        'status' =>
                            SellerKycVerification::STATUS_APPROVED,


                        'verification_method' =>
                            'paystack_stored_identity_match',


                        'provider_status' =>
                            'reconciled_exact_stored_identity',


                        'paystack_customer_id' =>
                            (string) (
                                $customer['id']
                                ??
                                $locked->paystack_customer_id
                            ),


                        'paystack_identification_status' =>
                            'success',


                        'identity_first_name' =>
                            $verifiedFirstName,


                        'identity_middle_name' =>
                            null,


                        'identity_last_name' =>
                            $verifiedLastName,


                        'name_match' =>
                            true,


                        'bank_name_match' =>
                            true,


                        'failure_code' =>
                            null,


                        'failure_message' =>
                            null,


                        'rejection_reason' =>
                            null,


                        'approved_at' =>
                            now(),


                        'auto_verified_at' =>
                            now(),


                        'rejected_at' =>
                            null,


                        'paystack_identification_completed_at' =>
                            now(),


                        'provider_response' =>
                            array_merge(
                                $locked->provider_response
                                ??
                                [],
                                [
                                    'status' =>
                                        'success',

                                    'customer_code' =>
                                        $customerCode,

                                    'customer_identified' =>
                                        true,

                                    'reconciliation' =>
                                        'exact_stored_bvn_and_bank_name',

                                    'verification_source' =>
                                        'authenticated_customer_lookup',

                                    'stored_bvn_exact_match' =>
                                        true,

                                    'verified_bank_name_match' =>
                                        true,
                                ]
                            ),

                    ])
                    ->save();

            },
            3
        );


        Log::notice(
            'Reconciled an already-identified Paystack customer using an exact stored BVN and verified bank-name match.',
            [
                'seller_id' =>
                    $seller->id,

                'kyc_id' =>
                    $kyc->id,

                'customer_code' =>
                    $customerCode,
            ]
        );


        return $kyc->fresh();
    }


    protected function paystackCustomerMatchesSeller(
        array $customer,
        User $seller,
        SellerKycVerification $kyc,
        string $customerCode
    ): bool {

        $returnedCode = trim(
            (string) ($customer['customer_code'] ?? '')
        );


        $returnedEmail = strtolower(
            trim(
                (string) ($customer['email'] ?? '')
            )
        );


        $sellerEmail = strtolower(
            trim(
                (string) $seller->email
            )
        );


        $storedCustomerId = trim(
            (string) $kyc->paystack_customer_id
        );


        $returnedCustomerId = trim(
            (string) ($customer['id'] ?? '')
        );


        return
            $returnedCode !== ''
            && hash_equals(
                $customerCode,
                $returnedCode
            )
            && $sellerEmail !== ''
            && $returnedEmail !== ''
            && hash_equals(
                $sellerEmail,
                $returnedEmail
            )
            && (
                $storedCustomerId === ''
                || (
                    $returnedCustomerId !== ''
                    && hash_equals(
                        $storedCustomerId,
                        $returnedCustomerId
                    )
                )
            );
    }


    protected function paystackCustomerNameMatches(
        array $customer,
        string $firstName,
        string $lastName
    ): bool {

        $verifiedFirstName = $this
            ->normalizeName(
                (string) ($customer['first_name'] ?? '')
            );


        $verifiedLastName = $this
            ->normalizeName(
                (string) ($customer['last_name'] ?? '')
            );


        return
            $verifiedFirstName !== ''
            && $verifiedLastName !== ''
            && hash_equals(
                $verifiedFirstName,
                $this->normalizeName($firstName)
            )
            && hash_equals(
                $verifiedLastName,
                $this->normalizeName($lastName)
            );
    }


    protected function paystackCustomerHasExactBvn(
        array $customer,
        string $bvn
    ): bool {

        $submittedBvn = (string) preg_replace(
            '/\D+/',
            '',
            $bvn
        );


        if (strlen($submittedBvn) !== 11) {
            return false;
        }


        $rawIdentifications =
            $customer['identifications']
            ??
            [];


        if (!is_array($rawIdentifications)) {
            return false;
        }


        $identifications = array_is_list(
            $rawIdentifications
        )
            ? $rawIdentifications
            : [$rawIdentifications];


        foreach ($identifications as $identification) {

            if (!is_array($identification)) {
                continue;
            }


            if (
                strtoupper(
                    trim(
                        (string) ($identification['country'] ?? '')
                    )
                )
                !==
                'NG'
                || strtolower(
                    trim(
                        (string) ($identification['type'] ?? '')
                    )
                )
                !==
                'bank_account'
            ) {
                continue;
            }


            $storedBvn = (string) preg_replace(
                '/\D+/',
                '',
                (string) ($identification['value'] ?? '')
            );


            if (
                strlen($storedBvn) === 11
                && hash_equals(
                    $submittedBvn,
                    $storedBvn
                )
            ) {
                return true;
            }
        }


        return false;
    }


    protected function activeBankMatchesCustomerName(
        SellerWithdrawalAccount $activeBank,
        array $customer,
        User $seller
    ): bool {

        if (
            (int) $activeBank->seller_id
                !==
                (int) $seller->id
            || !$activeBank->is_verified
            || !$activeBank->is_active
            || strlen(
                (string) preg_replace(
                    '/\D+/',
                    '',
                    (string) $activeBank->account_number
                )
            ) !== 10
        ) {
            return false;
        }


        $accountName = $this
            ->normalizeName(
                (string) $activeBank->account_name
            );


        $verifiedFirstName = $this
            ->normalizeName(
                (string) ($customer['first_name'] ?? '')
            );


        $verifiedLastName = $this
            ->normalizeName(
                (string) ($customer['last_name'] ?? '')
            );


        return
            $accountName !== ''
            && $verifiedFirstName !== ''
            && $verifiedLastName !== ''
            && str_contains(
                $accountName,
                $verifiedFirstName
            )
            && str_contains(
                $accountName,
                $verifiedLastName
            );
    }


    protected function normalizeName(
        string $value
    ): string {

        $transliterated = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            trim($value)
        );


        return strtolower(
            (string) preg_replace(
                '/[^a-z0-9]+/i',
                '',
                $transliterated !== false
                    ? $transliterated
                    : $value
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find A Reusable Successful Verification
    |--------------------------------------------------------------------------
    */

    protected function findReusableVerification(
        User $seller,
        SellerWithdrawalAccount $activeBank,
        string $fingerprint,
        string $firstName,
        string $middleName,
        string $lastName,
        string $dateOfBirth
    ): ?SellerKycVerification {

        if (
            !config(
                'midpoint.kyc.allow_verified_identity_reuse',
                false
            )
        ) {
            return null;
        }


        $activeAccountHash = trim(
            (string) $activeBank->account_number_hash
        );


        if ($activeAccountHash === '') {
            return null;
        }


        $candidates =
            SellerKycVerification::query()
                ->with('withdrawalAccount')
                ->where(
                    'seller_id',
                    '!=',
                    $seller->id
                )
                ->where(
                    'status',
                    SellerKycVerification::STATUS_APPROVED
                )
                ->where(
                    'provider',
                    'paystack'
                )
                ->where(
                    'provider_environment',
                    (string) config(
                        'services.paystack.mode',
                        'test'
                    )
                )
                ->where(
                    'identity_fingerprint',
                    $fingerprint
                )
                ->where(
                    'bank_name_match',
                    true
                )
                ->whereDate(
                    'date_of_birth',
                    $dateOfBirth
                )
                ->whereHas(
                    'withdrawalAccount',
                    function ($query) use (
                        $activeBank,
                        $activeAccountHash
                    ) {

                        $query
                            ->where(
                                'bank_code',
                                $activeBank->bank_code
                            )
                            ->where(
                                'account_number_hash',
                                $activeAccountHash
                            )
                            ->where(
                                'is_verified',
                                true
                            );
                    }
                )
                ->latest('approved_at')
                ->limit(20)
                ->get();


        return $candidates
            ->first(
                fn (SellerKycVerification $candidate) =>
                    data_get(
                        $candidate->provider_response,
                        'verification_source'
                    ) === 'signed_webhook'
                    && $candidate->paystack_identification_status === 'success'
                    && $candidate->paystack_identification_completed_at !== null
                    && $this
                        ->identityFingerprint
                        ->submittedNameMatches(
                            $candidate,
                            $firstName,
                            $middleName,
                            $lastName
                        )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve From A Reusable Successful Verification
    |--------------------------------------------------------------------------
    */

    protected function approveFromReusableVerification(
        User $seller,
        ?SellerKycVerification $existing,
        SellerWithdrawalAccount $activeBank,
        SellerKycVerification $source,
        array $data,
        string $firstName,
        string $middleName,
        string $lastName,
        string $bvn,
        string $fingerprint
    ): SellerKycVerification {

        $legalName = trim(
            implode(
                ' ',
                array_filter([
                    $firstName,
                    $middleName,
                    $lastName,
                ])
            )
        );


        $kyc = DB::transaction(
            function () use (
                $seller,
                $existing,
                $activeBank,
                $source,
                $data,
                $firstName,
                $middleName,
                $lastName,
                $legalName,
                $bvn,
                $fingerprint
            ) {

                $record =
                    SellerKycVerification::query()
                        ->where(
                            'seller_id',
                            $seller->id
                        )
                        ->lockForUpdate()
                        ->first()
                    ??
                    $existing
                    ??
                    new SellerKycVerification();


                $record->fill([

                    'seller_id' =>
                        $seller->id,


                    'legal_name' =>
                        $legalName,


                    'date_of_birth' =>
                        $data['date_of_birth'],


                    'country_code' =>
                        'NG',


                    'id_type' =>
                        'bvn',


                    'id_number' =>
                        $bvn,


                    'identity_fingerprint' =>
                        $fingerprint,


                    'reused_from_kyc_id' =>
                        $source->id,


                    'identity_reused_at' =>
                        now(),


                    'document_front_path' =>
                        $record->document_front_path ?: '',


                    'document_back_path' =>
                        null,


                    'selfie_path' =>
                        $record->selfie_path ?: '',


                    'status' =>
                        SellerKycVerification::STATUS_APPROVED,


                    'verification_method' =>
                        'paystack_identity_reuse',


                    'provider' =>
                        'midpoint',


                    'provider_environment' =>
                        (string) config(
                            'services.paystack.mode',
                            'test'
                        ),


                    'provider_status' =>
                        'reused_verified_identity',


                    /*
                     * Do not copy the source Paystack customer code. A webhook
                     * for that customer belongs to the original verification.
                     */
                    'paystack_customer_code' =>
                        null,


                    'paystack_customer_id' =>
                        null,


                    'paystack_identification_status' =>
                        'reused',


                    'paystack_identification_requested_at' =>
                        null,


                    'paystack_identification_completed_at' =>
                        now(),


                    'identity_first_name' =>
                        $source->identity_first_name
                        ?: $firstName,


                    'identity_middle_name' =>
                        $source->identity_middle_name
                        ?: ($middleName !== '' ? $middleName : null),


                    'identity_last_name' =>
                        $source->identity_last_name
                        ?: $lastName,


                    'identity_date_of_birth' =>
                        $source->identity_date_of_birth,


                    'liveness_passed' =>
                        $source->liveness_passed,


                    'liveness_probability' =>
                        $source->liveness_probability,


                    'face_match' =>
                        $source->face_match,


                    'face_confidence' =>
                        $source->face_confidence,


                    'name_match' =>
                        true,


                    'dob_match' =>
                        $source->dob_match,


                    'bank_name_match' =>
                        true,


                    'seller_withdrawal_account_id' =>
                        $activeBank->id,


                    'failure_code' =>
                        null,


                    'failure_message' =>
                        null,


                    'rejection_reason' =>
                        null,


                    'provider_response' => [

                        'status' =>
                            'success',


                        'verification_source' =>
                            'existing_paystack_verification',


                        'source_kyc_id' =>
                            $source->id,


                        'matched_controls' => [
                            'bvn_fingerprint',
                            'date_of_birth',
                            'first_name',
                            'last_name',
                            'bank_code',
                            'bank_account',
                        ],

                    ],


                    'verification_attempts' =>
                        ((int) $record->verification_attempts) + 1,


                    'last_verification_attempt_at' =>
                        now(),


                    'submitted_at' =>
                        now(),


                    'reviewed_by' =>
                        null,


                    'reviewed_at' =>
                        null,


                    'approved_at' =>
                        now(),


                    'rejected_at' =>
                        null,


                    'auto_verified_at' =>
                        now(),

                ]);


                $record->save();


                return $record->fresh();
            },
            3
        );


        Log::notice(
            'Reused a successful Paystack identity verification for another authorised seller account.',
            [
                'seller_id' =>
                    $seller->id,

                'kyc_id' =>
                    $kyc->id,

                'source_kyc_id' =>
                    $source->id,
            ]
        );


        return $kyc;
    }


    /*
    |--------------------------------------------------------------------------
    | Release A Stale Processing Record
    |--------------------------------------------------------------------------
    |
    | A late valid Paystack webhook can still approve this record. Until then,
    | changing it to provider_error makes the form available for a safe retry.
    |
    */

    public function releaseIfStale(
        SellerKycVerification $kyc
    ): SellerKycVerification {

        $timeoutMinutes = max(
            15,
            (int) config(
                'midpoint.kyc.processing_timeout_minutes',
                30
            )
        );


        return DB::transaction(
            function () use (
                $kyc,
                $timeoutMinutes
            ) {

                $locked =
                    SellerKycVerification::query()
                        ->whereKey($kyc->id)
                        ->lockForUpdate()
                        ->first();


                if (
                    !$locked
                    || $locked->status
                        !== SellerKycVerification::STATUS_PROCESSING
                ) {
                    return $locked ?: $kyc;
                }


                $startedAt =
                    $locked->paystack_identification_requested_at
                    ?: $locked->submitted_at
                    ?: $locked->updated_at;


                if (
                    !$startedAt
                    || $startedAt->gt(
                        now()->subMinutes($timeoutMinutes)
                    )
                ) {
                    return $locked;
                }


                $locked
                    ->forceFill([

                        'status' =>
                            SellerKycVerification::STATUS_PROVIDER_ERROR,


                        'provider_status' =>
                            'result_timeout',


                        'paystack_identification_status' =>
                            'result_timeout',


                        'failure_code' =>
                            'paystack_result_timeout',


                        'failure_message' =>
                            'Paystack did not return the final verification result in time. Please submit the verification again.',

                    ])
                    ->save();


                Log::warning(
                    'Released a stale Paystack seller KYC verification for retry.',
                    [
                        'kyc_id' =>
                            $locked->id,

                        'seller_id' =>
                            $locked->seller_id,

                        'requested_at' =>
                            optional($startedAt)->toIso8601String(),
                    ]
                );


                return $locked->fresh();
            },
            3
        );
    }


    public function releaseStaleProcessing(
        int $limit = 100
    ): int {

        $timeoutMinutes = max(
            15,
            (int) config(
                'midpoint.kyc.processing_timeout_minutes',
                30
            )
        );


        $records =
            SellerKycVerification::query()
                ->where(
                    'status',
                    SellerKycVerification::STATUS_PROCESSING
                )
                ->where(
                    'paystack_identification_requested_at',
                    '<=',
                    now()->subMinutes($timeoutMinutes)
                )
                ->oldest('id')
                ->limit(max(1, min($limit, 500)))
                ->get();


        $released = 0;


        foreach ($records as $record) {

            if (
                $this
                    ->releaseIfStale($record)
                    ->status
                ===
                SellerKycVerification::STATUS_PROVIDER_ERROR
            ) {
                $released++;
            }
        }


        return $released;
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Paystack Identity Webhook
    |--------------------------------------------------------------------------
    */

    public function handleWebhook(
        string $eventName,
        array $data
    ): bool {

        if (
            !in_array(
                $eventName,
                [
                    'customeridentification.success',
                    'customeridentification.failed',
                ],
                true
            )
        ) {

            return false;
        }


        $customerCode =
            trim(
                (string) (
                    $data[
                        'customer_code'
                    ]
                    ??
                    ''
                )
            );


        if (
            $customerCode === ''
        ) {

            Log::warning(
                'Paystack KYC webhook missing customer code.',
                [
                    'event' =>
                        $eventName,
                ]
            );


            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Find Local KYC
        |--------------------------------------------------------------------------
        */

        $kyc =
            SellerKycVerification::query()
                ->with([
                    'withdrawalAccount',
                    'seller',
                ])
                ->where(
                    'provider',
                    'paystack'
                )
                ->where(
                    'paystack_customer_code',
                    $customerCode
                )
                ->latest('id')
                ->first();


        if (!$kyc) {

            Log::warning(
                'Paystack KYC webhook customer code was not found locally.',
                [

                    'event' =>
                        $eventName,


                    'customer_code' =>
                        $customerCode,

                ]
            );


            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Stale Webhook From Approving Wrong Bank
        |--------------------------------------------------------------------------
        */

        if (
            !$this
                ->webhookMatchesCurrentVerification(
                    $kyc,
                    $data
                )
        ) {

            Log::warning(
                'Ignored stale or mismatched Paystack KYC webhook.',
                [

                    'event' =>
                        $eventName,


                    'kyc_id' =>
                        $kyc->id,


                    'customer_code' =>
                        $customerCode,

                ]
            );


            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Failed
        |--------------------------------------------------------------------------
        */

        if (
            $eventName
            ===
            'customeridentification.failed'
        ) {

            $reason =
                trim(
                    (string) (
                        $data[
                            'reason'
                        ]
                        ??
                        'Paystack could not verify the BVN and bank-account details.'
                    )
                );


            DB::transaction(
                function () use (
                    $kyc,
                    $data,
                    $reason
                ) {

                    $locked =
                        SellerKycVerification::query()
                            ->whereKey(
                                $kyc->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    /*
                     * Never downgrade a completed approved record because
                     * of a duplicated/stale failure webhook.
                     */

                    if (
                        $locked->status
                        ===
                        SellerKycVerification::STATUS_APPROVED
                    ) {
                        return;
                    }


                    $locked
                        ->forceFill([

                            'status' =>
                                SellerKycVerification::STATUS_REJECTED,


                            'provider_status' =>
                                'failed',


                            'paystack_customer_id' =>
                                (string) (
                                    $data[
                                        'customer_id'
                                    ]
                                    ??
                                    $locked
                                        ->paystack_customer_id
                                ),


                            'paystack_identification_status' =>
                                'failed',


                            'bank_name_match' =>
                                false,


                            'name_match' =>
                                false,


                            'failure_code' =>
                                'paystack_identification_failed',


                            'failure_message' =>
                                $reason,


                            'rejection_reason' =>
                                $reason,


                            'rejected_at' =>
                                now(),


                            'approved_at' =>
                                null,


                            'paystack_identification_completed_at' =>
                                now(),


                            'provider_response' =>
                                $this
                                    ->safeWebhookPayload(
                                        $data,
                                        'failed'
                                    ),

                        ])
                        ->save();

                },
                3
            );


            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        |
        | Paystack documentation states that after successful validation,
        | the customer's first/last names are updated to the BVN identity.
        |
        */

        $customer =
            null;


        try {

            $customer =
                $this
                    ->paystack
                    ->fetchCustomer(
                        $customerCode
                    );


        } catch (
            Throwable $exception
        ) {

            /*
             * Do NOT reject the webhook.
             *
             * customeridentification.success is already authoritative.
             * Fetching customer is only used to store the verified name.
             */

            Log::warning(
                'Could not fetch Paystack customer after successful KYC webhook.',
                [

                    'kyc_id' =>
                        $kyc->id,


                    'customer_code' =>
                        $customerCode,


                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );
        }


        $verifiedFirstName =
            trim(
                (string) (
                    $customer[
                        'first_name'
                    ]
                    ??
                    ''
                )
            );


        $verifiedLastName =
            trim(
                (string) (
                    $customer[
                        'last_name'
                    ]
                    ??
                    ''
                )
            );


        DB::transaction(
            function () use (
                $kyc,
                $data,
                $verifiedFirstName,
                $verifiedLastName
            ) {

                $locked =
                    SellerKycVerification::query()
                        ->whereKey(
                            $kyc->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $locked
                    ->forceFill([

                        'status' =>
                            SellerKycVerification::STATUS_APPROVED,


                        'provider_status' =>
                            'success',


                        'paystack_customer_id' =>
                            (string) (
                                $data[
                                    'customer_id'
                                ]
                                ??
                                $locked
                                    ->paystack_customer_id
                            ),


                        'paystack_identification_status' =>
                            'success',


                        /*
                        |--------------------------------------------------------------------------
                        | Verified Paystack/BVN Name
                        |--------------------------------------------------------------------------
                        */

                        'identity_first_name' =>
                            $verifiedFirstName !== ''
                                ? $verifiedFirstName
                                : null,


                        'identity_middle_name' =>
                            null,


                        'identity_last_name' =>
                            $verifiedLastName !== ''
                                ? $verifiedLastName
                                : null,


                        /*
                         * Paystack customer validation succeeded,
                         * therefore the submitted identity and BVN passed.
                         */

                        'name_match' =>
                            true,


                        /*
                         * The critical result:
                         *
                         * Paystack validated this BVN against THIS bank account.
                         */

                        'bank_name_match' =>
                            true,


                        /*
                         * Paystack doesn't perform these in this API.
                         */

                        'dob_match' =>
                            null,


                        'face_match' =>
                            null,


                        'face_confidence' =>
                            null,


                        'liveness_passed' =>
                            null,


                        'liveness_probability' =>
                            null,


                        'failure_code' =>
                            null,


                        'failure_message' =>
                            null,


                        'rejection_reason' =>
                            null,


                        'approved_at' =>
                            now(),


                        'auto_verified_at' =>
                            now(),


                        'rejected_at' =>
                            null,


                        'paystack_identification_completed_at' =>
                            now(),


                        'provider_response' =>
                            $this
                                ->safeWebhookPayload(
                                    $data,
                                    'success',
                                    [

                                        'verification_source' =>
                                            'signed_webhook',


                                        'verified_first_name' =>
                                            $verifiedFirstName !== ''
                                                ? $verifiedFirstName
                                                : null,


                                        'verified_last_name' =>
                                            $verifiedLastName !== ''
                                                ? $verifiedLastName
                                                : null,

                                    ]
                                ),

                    ])
                    ->save();

            },
            3
        );


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Paystack Customer
    |--------------------------------------------------------------------------
    */

    protected function ensurePaystackCustomer(
        User $seller,
        string $firstName,
        string $lastName
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Search By Seller Email
        |--------------------------------------------------------------------------
        */

        $customer =
            $this
                ->paystack
                ->fetchCustomer(
                    $seller->email
                );


        /*
        |--------------------------------------------------------------------------
        | Create If Missing
        |--------------------------------------------------------------------------
        */

        if (!$customer) {

            return $this
                ->paystack
                ->createCustomer([

                    'email' =>
                        $seller->email,


                    'first_name' =>
                        $firstName,


                    'last_name' =>
                        $lastName,


                    'metadata' => [

                        'midpoint_seller_id' =>
                            $seller->id,


                        'purpose' =>
                            'seller_withdrawal_kyc',

                    ],

                ]);
        }


        $customerCode =
            trim(
                (string) (
                    $customer[
                        'customer_code'
                    ]
                    ??
                    ''
                )
            );


        if (
            $customerCode === ''
        ) {

            throw new RuntimeException(
                'The Paystack customer record is missing its customer code.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Name Before First Validation
        |--------------------------------------------------------------------------
        |
        | Once Paystack identifies the customer, Paystack no longer allows
        | ordinary name updates.
        |
        */

        if (
            !(
                (bool) (
                    $customer[
                        'identified'
                    ]
                    ??
                    false
                )
            )
        ) {

            try {

                $customer =
                    $this
                        ->paystack
                        ->updateCustomer(
                            $customerCode,
                            [

                                'first_name' =>
                                    $firstName,


                                'last_name' =>
                                    $lastName,

                            ]
                        );


            } catch (
                Throwable $exception
            ) {

                Log::info(
                    'Paystack customer name could not be updated before KYC.',
                    [

                        'seller_id' =>
                            $seller->id,


                        'customer_code' =>
                            $customerCode,


                        'error' =>
                            $exception
                                ->getMessage(),

                    ]
                );
            }
        }


        return $customer;
    }


    /*
    |--------------------------------------------------------------------------
    | Match Webhook To Current Verification
    |--------------------------------------------------------------------------
    |
    | Important because Paystack customer identification does not provide our
    | own arbitrary transaction reference.
    |
    | We use the masked bank and BVN information returned by Paystack.
    |
    */

    protected function webhookMatchesCurrentVerification(
        SellerKycVerification $kyc,
        array $data
    ): bool {

        $identification =
            is_array(
                $data[
                    'identification'
                ]
                ??
                null
            )
                ? $data[
                    'identification'
                ]
                : [];


        $account =
            $kyc
                ->withdrawalAccount;


        if (!$account) {
            return false;
        }


        if (!$kyc->seller) {
            return false;
        }


        $eventCustomerCode = trim(
            (string) ($data['customer_code'] ?? '')
        );


        if (
            $eventCustomerCode === ''
            || !hash_equals(
                (string) $kyc->paystack_customer_code,
                $eventCustomerCode
            )
        ) {
            return false;
        }


        $eventCustomerId = trim(
            (string) ($data['customer_id'] ?? '')
        );


        if ($eventCustomerId === '') {
            return false;
        }


        if (
            trim((string) $kyc->paystack_customer_id) !== ''
            && !hash_equals(
                (string) $kyc->paystack_customer_id,
                $eventCustomerId
            )
        ) {
            return false;
        }


        $eventEmail = strtolower(
            trim((string) ($data['email'] ?? ''))
        );


        $sellerEmail = strtolower(
            trim((string) $kyc->seller->email)
        );


        if (
            $sellerEmail === ''
            || $eventEmail === ''
            || !hash_equals($sellerEmail, $eventEmail)
        ) {
            return false;
        }


        if (
            strtoupper(trim((string) ($identification['country'] ?? '')))
                !== 'NG'
            || strtolower(trim((string) ($identification['type'] ?? '')))
                !== 'bank_account'
        ) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Bank Code
        |--------------------------------------------------------------------------
        */

        $eventBankCode =
            trim(
                (string) (
                    $identification[
                        'bank_code'
                    ]
                    ??
                    ''
                )
            );


        if (
            $eventBankCode === ''
            || !hash_equals(
                (string) $account->bank_code,
                $eventBankCode
            )
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Masked Account Number
        |--------------------------------------------------------------------------
        |
        | Example from Paystack:
        |
        | 012****789
        |
        */

        if (
            !$this->paystackMaskedNumberMatches(
                (string) ($identification['account_number'] ?? ''),
                (string) $account->account_number,
                10
            )
        ) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Masked BVN
        |--------------------------------------------------------------------------
        */

        if (
            !$this->paystackMaskedNumberMatches(
                (string) ($identification['bvn'] ?? ''),
                (string) $kyc->id_number,
                11
            )
        ) {
            return false;
        }


        return true;
    }


    protected function paystackMaskedNumberMatches(
        string $masked,
        string $plain,
        int $expectedLength
    ): bool {

        $plain = (string) preg_replace(
            '/\D+/',
            '',
            $plain
        );


        if (strlen($plain) !== $expectedLength) {
            return false;
        }


        if (
            preg_match(
                '/^(\d{3})\D+(\d{3})$/u',
                trim($masked),
                $parts
            ) !== 1
        ) {
            return false;
        }


        return hash_equals(
            substr($plain, 0, 3) . substr($plain, -3),
            $parts[1] . $parts[2]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Webhook Audit Data
    |--------------------------------------------------------------------------
    |
    | Paystack returns masked BVN/account information.
    |
    | Never save raw BVN in provider_response.
    |
    */

    protected function safeWebhookPayload(
        array $data,
        string $status,
        array $extra = []
    ): array {

        $identification =
            is_array(
                $data[
                    'identification'
                ]
                ??
                null
            )
                ? $data[
                    'identification'
                ]
                : [];


        return array_merge(
            [

                'status' =>
                    $status,


                'customer_code' =>
                    (string) (
                        $data[
                            'customer_code'
                        ]
                        ??
                        ''
                    ),


                'customer_id' =>
                    (string) (
                        $data[
                            'customer_id'
                        ]
                        ??
                        ''
                    ),


                'email' =>
                    (string) (
                        $data[
                            'email'
                        ]
                        ??
                        ''
                    ),


                'identification' => [

                    'country' =>
                        (string) (
                            $identification[
                                'country'
                            ]
                            ??
                            ''
                        ),


                    'type' =>
                        (string) (
                            $identification[
                                'type'
                            ]
                            ??
                            ''
                        ),


                    /*
                     * These are masked by Paystack.
                     */

                    'bvn' =>
                        (string) (
                            $identification[
                                'bvn'
                            ]
                            ??
                            ''
                        ),


                    'account_number' =>
                        (string) (
                            $identification[
                                'account_number'
                            ]
                            ??
                            ''
                        ),


                    'bank_code' =>
                        (string) (
                            $identification[
                                'bank_code'
                            ]
                            ??
                            ''
                        ),

                ],


                'reason' =>
                    (string) (
                        $data[
                            'reason'
                        ]
                        ??
                        ''
                    ),

            ],
            $extra
        );
    }
}
