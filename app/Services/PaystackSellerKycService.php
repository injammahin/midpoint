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
        | Normalize And Fingerprint The Submitted Identity First
        |--------------------------------------------------------------------------
        |
        | These values must be available before any early return. Otherwise an
        | already-approved or currently-processing record could accept a different
        | BVN merely because the seller and bank account were unchanged.
        |
        */

        $firstName = trim(
            (string) ($data['first_name'] ?? '')
        );

        $middleName = trim(
            (string) ($data['middle_name'] ?? '')
        );

        $lastName = trim(
            (string) ($data['last_name'] ?? '')
        );

        $bvn = (string) preg_replace(
            '/\D+/',
            '',
            (string) ($data['bvn'] ?? '')
        );


        if (strlen($bvn) !== 11) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'BVN must be exactly 11 digits.',

            ]);
        }


        /*
         * Reject obvious placeholder values locally. This is only an early
         * sanity check; genuine BVN ownership must still be established by
         * the matching signed Paystack webhook below.
         */
        if (
            preg_match(
                '/^(\d)\1{10}$/',
                $bvn
            ) === 1
        ) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'Enter your valid 11-digit BVN. Repeated placeholder numbers are not accepted.',

            ]);
        }


        $fingerprint =
            $this
                ->identityFingerprint
                ->make($bvn);


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
            && $existing
                ->isApprovedForWithdrawalAccount(
                    $activeBank
                )
        ) {

            $storedFingerprint = trim(
                (string) $existing->identity_fingerprint
            );


            if (
                $storedFingerprint !== ''
                && hash_equals(
                    $storedFingerprint,
                    $fingerprint
                )
            ) {
                return $existing;
            }


            throw ValidationException::withMessages([

                'bvn' =>
                    'This bank already has a verified KYC identity, but the entered BVN is different.',

            ]);
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

                $storedFingerprint = trim(
                    (string) $existing->identity_fingerprint
                );


                if (
                    $storedFingerprint !== ''
                    && hash_equals(
                        $storedFingerprint,
                        $fingerprint
                    )
                ) {
                    return $existing;
                }


                throw ValidationException::withMessages([

                    'bvn' =>
                        'A different BVN verification is already processing for this bank. Wait for its result before trying again.',

                ]);
            }


            throw ValidationException::withMessages([

                'bvn' =>
                    'A Paystack identity verification is still processing for your previous active bank. Please wait for that result before verifying another bank account.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | No Cross-Account KYC Reuse
        |--------------------------------------------------------------------------
        |
        | A verification belonging to another seller is never an approval source.
        | Each seller and bank must be correlated to its own Paystack request and
        | signed final webhook.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Ensure Paystack Customer Exists
        |--------------------------------------------------------------------------
        */

        $attemptId = bin2hex(
            random_bytes(16)
        );


        $customer =
            $this
                ->ensurePaystackCustomer(
                    $seller,
                    $firstName,
                    $lastName,
                    $existing,
                    $attemptId
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


        $verificationEmail = strtolower(
            trim(
                (string) (
                    $customer['email']
                    ??
                    ''
                )
            )
        );


        if (
            $customerCode === ''
            || $verificationEmail === ''
        ) {

            throw new RuntimeException(
                'Paystack did not return a complete customer record for this verification attempt.'
            );
        }


        /*
         * A previously identified customer must never receive another BVN
         * submission. Paystack can answer from that customer's historic state,
         * which would not prove the BVN submitted in this request.
         */
        if ((bool) ($customer['identified'] ?? false)) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'Your Paystack customer is already identified, but Midpoint does not have signed proof that the BVN entered now is the verified BVN. For your protection, verification has been stopped. Do not submit again. Contact Midpoint Support and ask for a Paystack identity reset.',

            ]);
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
                    $customerId,
                    $verificationEmail,
                    $attemptId
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


                            'verification_attempt_id' =>
                                $attemptId,


                            'paystack_verification_email' =>
                                $verificationEmail,


                            'verification_source' =>
                                'pending_signed_webhook',


                            'customer_identified_before_submission' =>
                                false,


                            'exact_bvn_confirmed' =>
                                false,

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
                $message =
                    'Paystack says this customer was already validated. Midpoint did not approve the KYC because that response does not prove the BVN submitted in this attempt. Contact Paystack support to reset the customer before retrying.';


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
                            'paystack_customer_already_identified',


                        'failure_message' =>
                            $message,


                        'rejection_reason' =>
                            null,


                        'provider_response' =>
                            array_merge(
                                $kyc->provider_response ?? [],
                                [
                                    'exact_bvn_confirmed' =>
                                        false,

                                    'reconciliation' =>
                                        'blocked_already_identified',
                                ]
                            ),

                    ])
                    ->save();


                Log::warning(
                    'Blocked unsafe recovery of an already-identified Paystack customer.',
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


            /*
             * Never expose Paystack's raw integration error to the seller.
             * Keep the raw message in the application log and store a safe,
             * actionable message on the KYC record instead.
             */
            $failure =
                $this
                    ->paystackRequestFailureDetails(
                        $exception,
                        $kyc
                    );


            $kyc
                ->forceFill([

                    'status' =>
                        SellerKycVerification::STATUS_PROVIDER_ERROR,


                    'provider_status' =>
                        $failure[
                            'provider_status'
                        ],


                    'paystack_identification_status' =>
                        $failure[
                            'provider_status'
                        ],


                    'failure_code' =>
                        $failure[
                            'failure_code'
                        ],


                    'failure_message' =>
                        $failure[
                            'message'
                        ],


                    'rejection_reason' =>
                        null,


                    'provider_response' =>
                        array_merge(
                            $kyc->provider_response ?? [],
                            [
                                'exact_bvn_confirmed' =>
                                    false,

                                'last_request_failure_code' =>
                                    $failure[
                                        'failure_code'
                                    ],
                            ]
                        ),

                ])
                ->save();


            throw ValidationException::withMessages([

                'bvn' =>
                    $failure[
                        'message'
                    ],

            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Seller-Facing Paystack Error
    |--------------------------------------------------------------------------
    */

    protected function paystackRequestFailureDetails(
        Throwable $exception,
        SellerKycVerification $kyc
    ): array {

        $rawMessage = strtolower(
            trim(
                $exception->getMessage()
            )
        );


        if (
            str_contains(
                $rawMessage,
                'bvn has been used by too many customers'
            )
            || str_contains(
                $rawMessage,
                'bvn has been used by too many customer'
            )
        ) {

            return [
                'provider_status' =>
                    'bvn_customer_limit',

                'failure_code' =>
                    'paystack_bvn_customer_limit',

                'message' =>
                    'We could not complete this BVN verification because Paystack has reached its customer-use limit for this BVN. Your BVN is not necessarily incorrect. Do not submit it again. Please contact Midpoint Support and provide KYC reference #'
                    .
                    $kyc->id
                    .
                    '.',
            ];
        }


        return [
            'provider_status' =>
                'request_failed',

            'failure_code' =>
                'paystack_request_failed',

            'message' =>
                'Paystack could not start identity verification. Please try again later. If the problem continues, contact Midpoint Support and provide KYC reference #'
                .
                $kyc->id
                .
                '.',
        ];
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

        /*
         * Permanently disabled. Paystack's customer-level `identified` state
         * can belong to an older request and is never proof for the BVN that
         * the seller just submitted.
         */
        return null;

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


        $expectedEmail = strtolower(
            trim(
                (string) (
                    data_get(
                        $kyc->provider_response,
                        'paystack_verification_email'
                    )
                    ?:
                    $seller->email
                )
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
            && $expectedEmail !== ''
            && $returnedEmail !== ''
            && hash_equals(
                $expectedEmail,
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

        /*
         * Permanently disabled. Every seller and active withdrawal bank must
         * complete its own fresh Paystack verification attempt.
         */
        return null;

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

        throw new RuntimeException(
            'Cross-account KYC reuse is disabled.'
        );

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
                                array_merge(
                                    $locked->provider_response ?? [],
                                    $this
                                        ->safeWebhookPayload(
                                            $data,
                                            'failed',
                                            [
                                                'verification_source' =>
                                                    'signed_webhook_failed',

                                                'exact_bvn_confirmed' =>
                                                    false,
                                            ]
                                        )
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
             * Return a server error through the webhook controller so Paystack
             * retries. Approval requires both the signed success event and a
             * successful authenticated lookup of this exact fresh customer.
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


            throw new RuntimeException(
                'Could not confirm the successful Paystack customer record.',
                0,
                $exception
            );
        }


        if (!is_array($customer)) {
            throw new RuntimeException(
                'Paystack returned an invalid customer record after KYC success.'
            );
        }


        $submittedFirstName = trim(
            (string) data_get(
                $kyc->provider_response,
                'submitted_first_name'
            )
        );


        $submittedLastName = trim(
            (string) data_get(
                $kyc->provider_response,
                'submitted_last_name'
            )
        );


        if (
            !((bool) ($customer['identified'] ?? false))
            || !$this->paystackCustomerMatchesSeller(
                $customer,
                $kyc->seller,
                $kyc,
                $customerCode
            )
            || !$this->paystackCustomerNameMatches(
                $customer,
                $submittedFirstName,
                $submittedLastName
            )
            || !$this->activeBankMatchesCustomerName(
                $kyc->withdrawalAccount,
                $customer,
                $kyc->seller
            )
        ) {

            Log::warning(
                'Paystack KYC success customer did not pass strict local matching.',
                [
                    'kyc_id' =>
                        $kyc->id,

                    'customer_code' =>
                        $customerCode,
                ]
            );


            throw new RuntimeException(
                'The Paystack success result did not match the exact verification customer, submitted legal name, and active bank account.'
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
                            array_merge(
                                $locked->provider_response ?? [],
                                $this
                                    ->safeWebhookPayload(
                                        $data,
                                        'success',
                                        [

                                            'verification_source' =>
                                                'signed_webhook',


                                            'exact_bvn_confirmed' =>
                                                true,


                                            'customer_identified_after_submission' =>
                                                true,


                                            'verified_first_name' =>
                                                $verifiedFirstName !== ''
                                                    ? $verifiedFirstName
                                                    : null,


                                            'verified_last_name' =>
                                                $verifiedLastName !== ''
                                                    ? $verifiedLastName
                                                    : null,

                                        ]
                                    )
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
        string $lastName,
        ?SellerKycVerification $existing,
        string $attemptId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Reuse One Durable Paystack Customer
        |--------------------------------------------------------------------------
        |
        | Repeatedly creating customer records for the same BVN can trigger
        | Paystack's customer-use limit. Prefer the customer already attached
        | to this seller's KYC record; otherwise look it up by the seller email.
        | A previous failed attempt may safely reuse an unidentified customer
        | because every final webhook is matched against the current masked BVN
        | and bank account before approval.
        */

        $storedCustomerCode = trim(
            (string) (
                $existing?->paystack_customer_code
                ??
                ''
            )
        );


        $customerLookup =
            $storedCustomerCode !== ''
                ? $storedCustomerCode
                : (string) $seller->email;

        $customer =
            $this
                ->paystack
                ->fetchCustomer(
                    $customerLookup
                );


        /*
         * If an old locally stored customer code no longer exists at Paystack,
         * fall back to the seller's real email before attempting creation.
         */
        if (
            !$customer
            && $storedCustomerCode !== ''
        ) {

            $customer =
                $this
                    ->paystack
                    ->fetchCustomer(
                        (string) $seller->email
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Create If Missing
        |--------------------------------------------------------------------------
        */

        if (!$customer) {

            return $this
                ->createFreshPaystackCustomer(
                    $seller,
                    $firstName,
                    $lastName,
                    $attemptId,
                    null
                );
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
        | Never Replace An Already-Identified Customer Automatically
        |--------------------------------------------------------------------------
        |
        | Paystack may refuse to validate an identified customer again. Creating
        | aliases and more customer records is not a safe workaround because the
        | same BVN can reach Paystack's customer-use limit. Only Paystack support
        | can reset an unproven already-identified customer.
        |
        */

        if ((bool) ($customer['identified'] ?? false)) {

            throw ValidationException::withMessages([

                'bvn' =>
                    'Your Paystack customer is already identified, but Midpoint does not have signed proof that the BVN entered now is the verified BVN. For your protection, verification has been stopped. Do not submit again. Contact Midpoint Support and ask for a Paystack identity reset.',

            ]);
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
    | Create A Customer Dedicated To One KYC Attempt
    |--------------------------------------------------------------------------
    */

    protected function createFreshPaystackCustomer(
        User $seller,
        string $firstName,
        string $lastName,
        string $attemptId,
        ?string $previousCustomerCode = null
    ): array {

        $verificationEmail = $this
            ->verificationCustomerEmail(
                (string) $seller->email,
                $attemptId,
                trim((string) $previousCustomerCode) !== ''
            );

        try {

            $customer =
                $this
                    ->paystack
                    ->createCustomer([

                        'email' =>
                            $verificationEmail,

                        'first_name' =>
                            $firstName,

                        'last_name' =>
                            $lastName,

                        'metadata' => [

                            'midpoint_seller_id' =>
                                $seller->id,

                            'purpose' =>
                                'seller_withdrawal_kyc',

                            'midpoint_kyc_attempt_id' =>
                                $attemptId,

                        ],

                    ]);

        } catch (Throwable $exception) {

            Log::warning(
                'Paystack could not create a fresh customer for KYC.',
                [
                    'seller_id' =>
                        $seller->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            throw new RuntimeException(
                'Paystack could not create a fresh customer for this BVN verification. Contact support before retrying.',
                0,
                $exception
            );
        }


        $customerCode = trim(
            (string) ($customer['customer_code'] ?? '')
        );

        $customerEmail = strtolower(
            trim((string) ($customer['email'] ?? ''))
        );

        $expectedEmail = strtolower(
            trim($verificationEmail)
        );

        $previousCustomerCode = trim(
            (string) $previousCustomerCode
        );


        if (
            $customerCode === ''
            || $customerEmail === ''
            || $expectedEmail === ''
            || !hash_equals($expectedEmail, $customerEmail)
            || (bool) ($customer['identified'] ?? false)
            || (
                $previousCustomerCode !== ''
                && hash_equals(
                    $previousCustomerCode,
                    $customerCode
                )
            )
        ) {

            throw new RuntimeException(
                'Paystack returned an existing or invalid customer instead of a fresh verification customer. Midpoint refused to reuse the previous identity result. Contact Paystack support to reset this customer.'
            );
        }


        return $customer;
    }


    /*
    |--------------------------------------------------------------------------
    | Verification Customer Email
    |--------------------------------------------------------------------------
    |
    | Never create plus-address aliases for retries. Paystack can count every
    | alias as another customer using the same BVN and eventually reject the
    | BVN for exceeding its customer-use limit. One seller keeps one customer.
    |
    */

    protected function verificationCustomerEmail(
        string $sellerEmail,
        string $attemptId,
        bool $mustBeUnique
    ): string {

        $sellerEmail = strtolower(
            trim($sellerEmail)
        );


        if (!$mustBeUnique) {
            return $sellerEmail;
        }


        throw ValidationException::withMessages([

            'bvn' =>
                'This seller already has a Paystack customer. Midpoint will not create another customer for the same BVN. Contact Midpoint Support if Paystack must reset the existing identity.',

        ]);
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

        $isAwaitingResult =
            $kyc->status === SellerKycVerification::STATUS_PROCESSING
            || (
                $kyc->status === SellerKycVerification::STATUS_PROVIDER_ERROR
                && $kyc->provider_status === 'result_timeout'
            );


        if (
            $kyc->provider !== 'paystack'
            || $kyc->provider_environment
                !== (string) config('services.paystack.mode', 'test')
            || !$isAwaitingResult
            || !in_array(
                $kyc->paystack_identification_status,
                [
                    'processing',
                    'result_timeout',
                ],
                true
            )
            || data_get(
                $kyc->provider_response,
                'verification_source'
            ) !== 'pending_signed_webhook'
            || data_get(
                $kyc->provider_response,
                'customer_identified_before_submission'
            ) !== false
            || data_get(
                $kyc->provider_response,
                'exact_bvn_confirmed'
            ) !== false
            || trim((string) $kyc->identity_fingerprint) === ''
            || !$kyc->paystack_identification_requested_at
        ) {
            return false;
        }

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


        if (
            !$account
            || !$kyc->seller
            || !$account->is_verified
            || !$account->is_active
            || (int) $account->seller_id !== (int) $kyc->seller_id
        ) {
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


        $expectedEmail = strtolower(
            trim(
                (string) (
                    data_get(
                        $kyc->provider_response,
                        'paystack_verification_email'
                    )
                    ?:
                    $kyc->seller->email
                )
            )
        );


        if (
            $expectedEmail === ''
            || $eventEmail === ''
            || !hash_equals($expectedEmail, $eventEmail)
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
