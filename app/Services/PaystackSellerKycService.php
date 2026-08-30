<?php

namespace App\Services;

use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PaystackSellerKycService
{
    public function __construct(
        protected PaystackService $paystack
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
                ->with(
                    'withdrawalAccount'
                )
                ->where(
                    'provider',
                    'paystack'
                )
                ->where(
                    'paystack_customer_code',
                    $customerCode
                )
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
            $eventBankCode !== ''
            &&
            $eventBankCode
            !==
            (string)
            $account
                ->bank_code
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

        $eventAccountDigits =
            preg_replace(
                '/\D+/',
                '',
                (string) (
                    $identification[
                        'account_number'
                    ]
                    ??
                    ''
                )
            );


        if (
            $eventAccountDigits !== ''
        ) {

            $eventLast3 =
                substr(
                    $eventAccountDigits,
                    -3
                );


            $localLast3 =
                substr(
                    (string)
                    $account
                        ->account_number_last4,
                    -3
                );


            if (
                $eventLast3
                !==
                $localLast3
            ) {

                return false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Masked BVN
        |--------------------------------------------------------------------------
        */

        $eventBvnDigits =
            preg_replace(
                '/\D+/',
                '',
                (string) (
                    $identification[
                        'bvn'
                    ]
                    ??
                    ''
                )
            );


        if (
            $eventBvnDigits !== ''
        ) {

            $eventLast3 =
                substr(
                    $eventBvnDigits,
                    -3
                );


            $localLast3 =
                substr(
                    (string)
                    $kyc
                        ->id_number_last4,
                    -3
                );


            if (
                $eventLast3
                !==
                $localLast3
            ) {

                return false;
            }
        }


        return true;
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