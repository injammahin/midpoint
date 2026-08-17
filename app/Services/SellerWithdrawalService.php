<?php

namespace App\Services;

use App\Models\SellerKycVerification;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SellerWithdrawalService
{
    public function __construct(
        protected PaystackService $paystack
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Request Withdrawal
    |--------------------------------------------------------------------------
    */

    public function requestWithdrawal(
        User $seller,
        float $amount
    ): SellerWithdrawal {

        $amount =
            round(
                $amount,
                2
            );


        $minimum =
            (float)
            config(
                'services.paystack.minimum_withdrawal',
                1000
            );


        /*
        |--------------------------------------------------------------------------
        | Minimum Withdrawal
        |--------------------------------------------------------------------------
        */

        if (
            $amount <
            $minimum
        ) {

            throw ValidationException::withMessages([
                'amount' =>
                    'Minimum withdrawal is ₦'
                    .
                    number_format(
                        $minimum,
                        0
                    )
                    .
                    '.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Reserve Seller Funds
        |--------------------------------------------------------------------------
        */

        $withdrawal =
            DB::transaction(
                function () use (
                    $seller,
                    $amount
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Seller
                    |--------------------------------------------------------------------------
                    */

                    User::query()

                        ->whereKey(
                            $seller->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | KYC
                    |--------------------------------------------------------------------------
                    */

                    $kyc =
                        SellerKycVerification::query()

                            ->where(
                                'seller_id',
                                $seller->id
                            )

                            ->first();


                    if (
                        !$kyc
                        ||
                        $kyc->status
                        !==
                        SellerKycVerification::STATUS_APPROVED
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Complete and verify your KYC before withdrawing funds.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Automated Bank / KYC Match
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $kyc->bank_name_match
                        !==
                        null
                        &&
                        $kyc->bank_name_match
                        ===
                        false
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Your verified bank account does not match your verified identity.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Active Verified Bank
                    |--------------------------------------------------------------------------
                    */

                    $account =
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

                            ->lockForUpdate()

                            ->first();


                    if (
                        !$account
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Add, verify, and activate a bank account before withdrawing funds.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Recipient Code
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$account
                            ->paystack_recipient_code
                    ) {

                        throw new RuntimeException(
                            'The active bank account is missing its Paystack recipient code.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Protect Production From Local Fake Accounts
                    |--------------------------------------------------------------------------
                    |
                    | RCP_LOCAL_ accounts are ONLY allowed when APP_ENV=local.
                    |
                    */

                    $isLocalRecipient =
                        Str::startsWith(
                            (string)
                            $account
                                ->paystack_recipient_code,
                            'RCP_LOCAL_'
                        );


                    if (
                        $isLocalRecipient
                        &&
                        !app()->environment(
                            'local'
                        )
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'This test withdrawal bank account cannot be used in production.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Seller Wallet
                    |--------------------------------------------------------------------------
                    */

                    $wallet =
                        SellerWallet::query()

                            ->where(
                                'seller_id',
                                $seller->id
                            )

                            ->lockForUpdate()

                            ->first();


                    if (
                        !$wallet
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Seller wallet could not be found.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Balance Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (
                            (float)
                            $wallet
                                ->available_balance
                        )
                        <
                        $amount
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Your available Midpoint balance is not enough for this withdrawal.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Balance Before
                    |--------------------------------------------------------------------------
                    */

                    $balanceBefore =
                        round(
                            (float)
                            $wallet
                                ->available_balance,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Balance After
                    |--------------------------------------------------------------------------
                    */

                    $balanceAfter =
                        round(
                            $balanceBefore
                            -
                            $amount,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Pending Withdrawal Balance
                    |--------------------------------------------------------------------------
                    */

                    $pendingAfter =
                        round(
                            (
                                (float)
                                $wallet
                                    ->pending_withdrawal_balance
                            )
                            +
                            $amount,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Reserve Wallet Balance
                    |--------------------------------------------------------------------------
                    */

                    $wallet
                        ->forceFill([
                            'available_balance' =>
                                $balanceAfter,

                            'pending_withdrawal_balance' =>
                                $pendingAfter,
                        ])
                        ->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Generate Withdrawal Reference
                    |--------------------------------------------------------------------------
                    */

                    $reference =
                        SellerWithdrawal::generateReference(
                            $seller->id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Withdrawal
                    |--------------------------------------------------------------------------
                    */

                    $withdrawal =
                        SellerWithdrawal::create([
                            'seller_wallet_id' =>
                                $wallet->id,

                            'seller_id' =>
                                $seller->id,

                            'seller_withdrawal_account_id' =>
                                $account->id,

                            'reference' =>
                                $reference,

                            /*
                             * Important:
                             *
                             * The same reference is used for Paystack.
                             */
                            'paystack_transfer_reference' =>
                                $reference,

                            'paystack_recipient_code' =>
                                $account
                                    ->paystack_recipient_code,

                            /*
                             * Snapshot bank information.
                             *
                             * Even if seller deletes bank later,
                             * withdrawal history remains accurate.
                             */
                            'bank_name' =>
                                $account
                                    ->bank_name,

                            'account_name' =>
                                $account
                                    ->account_name,

                            'account_number_last4' =>
                                $account
                                    ->account_number_last4,

                            'currency' =>
                                strtoupper(
                                    $wallet
                                        ->currency
                                    ?:
                                    'NGN'
                                ),

                            'amount' =>
                                $amount,

                            'status' =>
                                SellerWithdrawal::STATUS_PENDING,

                            'requested_at' =>
                                now(),

                            'failure_reason' =>
                                null,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Wallet Ledger
                    |--------------------------------------------------------------------------
                    */

                    SellerWalletTransaction::create([
                        'seller_wallet_id' =>
                            $wallet->id,

                        'seller_id' =>
                            $seller->id,

                        'secure_transaction_id' =>
                            null,

                        'seller_withdrawal_id' =>
                            $withdrawal->id,

                        'reference' =>
                            SellerWalletTransaction::generateReference(),

                        'type' =>
                            SellerWalletTransaction::TYPE_WITHDRAWAL_REQUEST,

                        'direction' =>
                            SellerWalletTransaction::DIRECTION_DEBIT,

                        'status' =>
                            SellerWalletTransaction::STATUS_PENDING,

                        'currency' =>
                            $withdrawal
                                ->currency,

                        'amount' =>
                            $amount,

                        'balance_before' =>
                            $balanceBefore,

                        'balance_after' =>
                            $balanceAfter,

                        'description' =>
                            'Withdrawal requested to '
                            .
                            $account
                                ->bank_name
                            .
                            ' ••••'
                            .
                            $account
                                ->account_number_last4,

                        'meta' => [
                            'withdrawal_reference' =>
                                $reference,

                            'bank_name' =>
                                $account
                                    ->bank_name,

                            'account_name' =>
                                $account
                                    ->account_name,

                            'account_last4' =>
                                $account
                                    ->account_number_last4,
                        ],
                    ]);


                    return $withdrawal;
                },
                3
            );


        /*
        |--------------------------------------------------------------------------
        | LOCAL AUTOMATIC WITHDRAWAL SIMULATION
        |--------------------------------------------------------------------------
        |
        | This is the missing part in your existing service.
        |
        | If:
        |
        | APP_ENV=local
        |
        | AND
        |
        | PAYSTACK_FAKE_WITHDRAWALS=true
        |
        | OR recipient begins RCP_LOCAL_
        |
        | we DO NOT contact Paystack.
        |
        | Instead we simulate transfer.success.
        |
        */

        if (
            $this
                ->shouldSimulateWithdrawal(
                    $withdrawal
                )
        ) {

            return $this
                ->simulateSuccessfulWithdrawal(
                    $withdrawal
                );
        }


        /*
        |--------------------------------------------------------------------------
        | REAL PAYSTACK TRANSFER
        |--------------------------------------------------------------------------
        |
        | Only reached when this is NOT a local fake withdrawal.
        |
        */

        try {

            $data =
                $this
                    ->paystack
                    ->initiateTransfer([
                        'source' =>
                            'balance',

                        /*
                         * Paystack expects kobo.
                         *
                         * ₦1,000 = 100000
                         */
                        'amount' =>
                            (int)
                            round(
                                (
                                    (float)
                                    $withdrawal
                                        ->amount
                                )
                                *
                                100
                            ),

                        'recipient' =>
                            $withdrawal
                                ->paystack_recipient_code,

                        'reference' =>
                            $withdrawal
                                ->paystack_transfer_reference,

                        'reason' =>
                            'Midpoint seller withdrawal '
                            .
                            $withdrawal
                                ->reference,

                        'currency' =>
                            $withdrawal
                                ->currency,
                    ]);


            /*
            |--------------------------------------------------------------------------
            | Paystack Status
            |--------------------------------------------------------------------------
            */

            $providerStatus =
                strtolower(
                    (string) (
                        $data[
                            'status'
                        ]
                        ??
                        'pending'
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Immediate Success
            |--------------------------------------------------------------------------
            */

            if (
                $providerStatus
                ===
                'success'
            ) {

                $withdrawal
                    ->forceFill([
                        'paystack_transfer_code' =>
                            $data[
                                'transfer_code'
                            ]
                            ??
                            null,

                        'initiated_at' =>
                            now(),

                        'meta' =>
                            array_merge(
                                $withdrawal
                                    ->meta
                                ??
                                [],
                                [
                                    'paystack_initial_status' =>
                                        $providerStatus,

                                    'paystack_transfer_id' =>
                                        $data[
                                            'id'
                                        ]
                                        ??
                                        null,
                                ]
                            ),
                    ])
                    ->save();


                return $this
                    ->markSuccessful(
                        $withdrawal
                            ->reference,
                        $data
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Immediate Failure
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $providerStatus,
                    [
                        'failed',
                        'abandoned',
                        'blocked',
                        'rejected',
                    ],
                    true
                )
            ) {

                return $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_FAILED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            $data[
                                'message'
                            ]
                            ??
                            'Paystack transfer was not accepted.'
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Reversed
            |--------------------------------------------------------------------------
            */

            if (
                $providerStatus
                ===
                'reversed'
            ) {

                return $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_REVERSED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            'Paystack transfer was reversed.'
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Pending / Received / OTP
            |--------------------------------------------------------------------------
            */

            $localStatus =
                $providerStatus
                ===
                'otp'

                    ? SellerWithdrawal::STATUS_OTP

                    : SellerWithdrawal::STATUS_PROCESSING;


            $freshWithdrawal =
                SellerWithdrawal::query()

                    ->findOrFail(
                        $withdrawal
                            ->id
                    );


            $freshWithdrawal
                ->forceFill([
                    'paystack_transfer_code' =>
                        $data[
                            'transfer_code'
                        ]
                        ??
                        $freshWithdrawal
                            ->paystack_transfer_code,

                    'status' =>
                        $localStatus,

                    'initiated_at' =>
                        now(),

                    'failure_reason' =>
                        null,

                    'meta' =>
                        array_merge(
                            $freshWithdrawal
                                ->meta
                            ??
                            [],
                            [
                                'paystack_initial_status' =>
                                    $providerStatus,

                                'paystack_transfer_id' =>
                                    $data[
                                        'id'
                                    ]
                                    ??
                                    null,
                            ]
                        ),
                ])
                ->save();


            return $freshWithdrawal
                ->fresh();

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT: UNCERTAIN TRANSFER
            |--------------------------------------------------------------------------
            |
            | Do NOT automatically refund here.
            |
            | Paystack may have received the transfer request but Laravel
            | may have lost the HTTP response.
            |
            | The scheduled reconciliation or webhook will resolve it.
            |
            */

            Log::error(
                'Seller withdrawal Paystack initiation response was not confirmed.',
                [
                    'withdrawal_id' =>
                        $withdrawal
                            ->id,

                    'reference' =>
                        $withdrawal
                            ->reference,

                    'seller_id' =>
                        $withdrawal
                            ->seller_id,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );


            $freshWithdrawal =
                SellerWithdrawal::query()

                    ->findOrFail(
                        $withdrawal
                            ->id
                    );


            $freshWithdrawal
                ->forceFill([
                    'status' =>
                        SellerWithdrawal::STATUS_PROCESSING,

                    'failure_reason' =>
                        'Paystack initiation response is awaiting reconciliation.',

                    'meta' =>
                        array_merge(
                            $freshWithdrawal
                                ->meta
                            ??
                            [],
                            [
                                'initiation_exception' =>
                                    $exception
                                        ->getMessage(),
                            ]
                        ),
                ])
                ->save();


            return $freshWithdrawal
                ->fresh();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Should Simulate Withdrawal?
    |--------------------------------------------------------------------------
    |
    | IMPORTANT SECURITY RULE:
    |
    | Fake payouts are ONLY possible when APP_ENV=local.
    |
    */

    protected function shouldSimulateWithdrawal(
        SellerWithdrawal $withdrawal
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Production Must Never Simulate
        |--------------------------------------------------------------------------
        */

        if (
            !app()->environment(
                'local'
            )
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Explicit Fake Mode
        |--------------------------------------------------------------------------
        */

        $fakeWithdrawalEnabled =
            (bool)
            config(
                'services.paystack.fake_withdrawals',
                false
            );


        /*
        |--------------------------------------------------------------------------
        | Local Fake Recipient
        |--------------------------------------------------------------------------
        */

        $localRecipient =
            Str::startsWith(
                (string)
                $withdrawal
                    ->paystack_recipient_code,
                'RCP_LOCAL_'
            );


        /*
         * Either one enables simulation locally.
         */
        return
            $fakeWithdrawalEnabled
            ||
            $localRecipient;
    }


    /*
    |--------------------------------------------------------------------------
    | Simulate Successful Local Withdrawal
    |--------------------------------------------------------------------------
    */

    protected function simulateSuccessfulWithdrawal(
        SellerWithdrawal $withdrawal
    ): SellerWithdrawal {

        /*
        |--------------------------------------------------------------------------
        | Fake Paystack Transfer Code
        |--------------------------------------------------------------------------
        */

        $transferCode =
            'TRF_LOCAL_'
            .
            Str::upper(
                Str::random(
                    18
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Fake Paystack Response
        |--------------------------------------------------------------------------
        */

        $fakeProviderData = [
            'id' =>
                random_int(
                    100000,
                    999999
                ),

            'status' =>
                'success',

            'transfer_code' =>
                $transferCode,

            'reference' =>
                $withdrawal
                    ->paystack_transfer_reference,

            'recipient' =>
                $withdrawal
                    ->paystack_recipient_code,

            /*
             * Kobo
             */
            'amount' =>
                (int)
                round(
                    (
                        (float)
                        $withdrawal
                            ->amount
                    )
                    *
                    100
                ),

            'currency' =>
                $withdrawal
                    ->currency,

            'reason' =>
                'Midpoint local seller withdrawal test',

            'test_mode' =>
                true,

            'simulated' =>
                true,
        ];


        /*
        |--------------------------------------------------------------------------
        | Save Simulation Information
        |--------------------------------------------------------------------------
        */

        $withdrawal
            ->forceFill([
                'paystack_transfer_code' =>
                    $transferCode,

                'status' =>
                    SellerWithdrawal::STATUS_PROCESSING,

                'initiated_at' =>
                    now(),

                'failure_reason' =>
                    null,

                'meta' =>
                    array_merge(
                        $withdrawal
                            ->meta
                        ??
                        [],
                        [
                            'local_test' =>
                                true,

                            'simulated_paystack_transfer' =>
                                true,

                            'paystack_initial_status' =>
                                'success',

                            'paystack_transfer_id' =>
                                $fakeProviderData[
                                    'id'
                                ],
                        ]
                    ),
            ])
            ->save();


        /*
        |--------------------------------------------------------------------------
        | Finish Exactly Like transfer.success Webhook
        |--------------------------------------------------------------------------
        */

        return $this
            ->markSuccessful(
                $withdrawal
                    ->reference,
                $fakeProviderData
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reconcile Seller Pending Withdrawals
    |--------------------------------------------------------------------------
    */

    public function reconcilePendingForSeller(
        User $seller,
        int $limit = 5
    ): void {

        $items =
            SellerWithdrawal::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->whereIn(
                    'status',
                    [
                        SellerWithdrawal::STATUS_PENDING,
                        SellerWithdrawal::STATUS_PROCESSING,
                        SellerWithdrawal::STATUS_OTP,
                    ]
                )

                ->where(
                    'requested_at',
                    '<=',
                    now()
                        ->subSeconds(
                            20
                        )
                )

                ->oldest(
                    'id'
                )

                ->limit(
                    $limit
                )

                ->get();


        foreach (
            $items
            as
            $withdrawal
        ) {

            $this
                ->reconcile(
                    $withdrawal
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Reconcile Single Withdrawal
    |--------------------------------------------------------------------------
    */

    public function reconcile(
        SellerWithdrawal $withdrawal
    ): SellerWithdrawal {

        /*
        |--------------------------------------------------------------------------
        | Already Final
        |--------------------------------------------------------------------------
        */

        if (
            $withdrawal
                ->isFinal()
        ) {

            return $withdrawal;
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL OLD / STUCK TEST WITHDRAWAL
        |--------------------------------------------------------------------------
        |
        | This is useful for withdrawals created BEFORE this fix.
        |
        | If you already have:
        |
        | status = processing
        | recipient = RCP_LOCAL_...
        |
        | refreshing the wallet can now complete it automatically.
        |
        */

        if (
            $this
                ->shouldSimulateWithdrawal(
                    $withdrawal
                )
        ) {

            return $this
                ->simulateSuccessfulWithdrawal(
                    $withdrawal
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Real Paystack Reconciliation
        |--------------------------------------------------------------------------
        */

        try {

            $data =
                $this
                    ->paystack
                    ->verifyTransfer(
                        $withdrawal
                            ->paystack_transfer_reference
                    );


            $status =
                strtolower(
                    (string) (
                        $data[
                            'status'
                        ]
                        ??
                        'pending'
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            if (
                $status
                ===
                'success'
            ) {

                return $this
                    ->markSuccessful(
                        $withdrawal
                            ->reference,
                        $data
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Failed
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $status,
                    [
                        'failed',
                        'abandoned',
                        'blocked',
                        'rejected',
                    ],
                    true
                )
            ) {

                return $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_FAILED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            'Paystack transfer ended with status: '
                            .
                            $status
                            .
                            '.'
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Reversed
            |--------------------------------------------------------------------------
            */

            if (
                $status
                ===
                'reversed'
            ) {

                return $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_REVERSED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            'Paystack transfer was reversed.'
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Still Pending
            |--------------------------------------------------------------------------
            */

            $freshWithdrawal =
                SellerWithdrawal::query()

                    ->findOrFail(
                        $withdrawal
                            ->id
                    );


            $freshWithdrawal
                ->forceFill([
                    'status' =>
                        $status
                        ===
                        'otp'

                            ? SellerWithdrawal::STATUS_OTP

                            : SellerWithdrawal::STATUS_PROCESSING,

                    'paystack_transfer_code' =>
                        $data[
                            'transfer_code'
                        ]
                        ??
                        $freshWithdrawal
                            ->paystack_transfer_code,

                    'failure_reason' =>
                        null,

                    'meta' =>
                        array_merge(
                            $freshWithdrawal
                                ->meta
                            ??
                            [],
                            [
                                'paystack_last_status' =>
                                    $status,
                            ]
                        ),
                ])
                ->save();


            return $freshWithdrawal
                ->fresh();

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Check Transfer Not Found
            |--------------------------------------------------------------------------
            */

            $notFound =
                str_contains(
                    strtolower(
                        $exception
                            ->getMessage()
                    ),
                    'not found'
                );


            /*
            |--------------------------------------------------------------------------
            | If Not Found For > 2 Minutes, Refund
            |--------------------------------------------------------------------------
            */

            if (
                $notFound
                &&
                $withdrawal
                    ->requested_at
                &&
                $withdrawal
                    ->requested_at
                    ->lte(
                        now()
                            ->subMinutes(
                                2
                            )
                    )
            ) {

                return $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_FAILED,

                        'Paystack did not create the requested transfer.'
                    );
            }


            Log::warning(
                'Seller withdrawal reconciliation is still pending.',
                [
                    'withdrawal_id' =>
                        $withdrawal
                            ->id,

                    'reference' =>
                        $withdrawal
                            ->reference,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );


            return $withdrawal
                ->fresh();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Paystack Transfer Webhook
    |--------------------------------------------------------------------------
    */

    public function handlePaystackEvent(
        string $eventName,
        array $data
    ): ?SellerWithdrawal {

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            trim(
                (string) (
                    $data[
                        'reference'
                    ]
                    ??
                    ''
                )
            );


        if (
            $reference === ''
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Find Withdrawal
        |--------------------------------------------------------------------------
        */

        $withdrawal =
            SellerWithdrawal::query()

                ->where(
                    'paystack_transfer_reference',
                    $reference
                )

                ->orWhere(
                    'reference',
                    $reference
                )

                ->first();


        if (
            !$withdrawal
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Handle Event
        |--------------------------------------------------------------------------
        */

        return match (
            $eventName
        ) {

            'transfer.success' =>
                $this
                    ->markSuccessful(
                        $withdrawal
                            ->reference,
                        $data
                    ),


            'transfer.failed' =>
                $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_FAILED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            $data[
                                'message'
                            ]
                            ??
                            'Paystack transfer failed.'
                        )
                    ),


            'transfer.reversed' =>
                $this
                    ->restoreReservedFunds(
                        $withdrawal
                            ->reference,

                        SellerWithdrawal::STATUS_REVERSED,

                        (string) (
                            $data[
                                'reason'
                            ]
                            ??
                            $data[
                                'message'
                            ]
                            ??
                            'Paystack transfer was reversed.'
                        )
                    ),


            default =>
                $withdrawal,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Successful
    |--------------------------------------------------------------------------
    */

    protected function markSuccessful(
        string $reference,
        array $providerData = []
    ): SellerWithdrawal {

        return DB::transaction(
            function () use (
                $reference,
                $providerData
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Withdrawal
                |--------------------------------------------------------------------------
                */

                $withdrawal =
                    SellerWithdrawal::query()

                        ->where(
                            'reference',
                            $reference
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Idempotency
                |--------------------------------------------------------------------------
                |
                | Webhook can be delivered more than once.
                |
                */

                if (
                    $withdrawal
                        ->status
                    ===
                    SellerWithdrawal::STATUS_SUCCESSFUL
                ) {

                    return $withdrawal;
                }


                /*
                 * A failed/reversed withdrawal has already been refunded.
                 *
                 * Do not debit wallet again.
                 */
                if (
                    in_array(
                        $withdrawal
                            ->status,
                        [
                            SellerWithdrawal::STATUS_FAILED,
                            SellerWithdrawal::STATUS_REVERSED,
                        ],
                        true
                    )
                ) {

                    return $withdrawal;
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Wallet
                |--------------------------------------------------------------------------
                */

                $wallet =
                    SellerWallet::query()

                        ->whereKey(
                            $withdrawal
                                ->seller_wallet_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Amount
                |--------------------------------------------------------------------------
                */

                $amount =
                    round(
                        (float)
                        $withdrawal
                            ->amount,
                        2
                    );


                $pending =
                    round(
                        (float)
                        $wallet
                            ->pending_withdrawal_balance,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Safety Check
                |--------------------------------------------------------------------------
                */

                if (
                    $pending
                    +
                    0.001
                    <
                    $amount
                ) {

                    throw new RuntimeException(
                        'Wallet pending withdrawal balance is inconsistent.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Finalize Wallet
                |--------------------------------------------------------------------------
                |
                | available_balance was already reduced when withdrawal started.
                |
                | Now:
                |
                | pending -= amount
                | total_withdrawn += amount
                |
                */

                $wallet
                    ->forceFill([
                        'pending_withdrawal_balance' =>
                            round(
                                $pending
                                -
                                $amount,
                                2
                            ),

                        'total_withdrawn' =>
                            round(
                                (
                                    (float)
                                    $wallet
                                        ->total_withdrawn
                                )
                                +
                                $amount,
                                2
                            ),
                    ])
                    ->save();


                /*
                |--------------------------------------------------------------------------
                | Post Wallet Debit
                |--------------------------------------------------------------------------
                */

                SellerWalletTransaction::query()

                    ->where(
                        'seller_withdrawal_id',
                        $withdrawal
                            ->id
                    )

                    ->where(
                        'type',
                        SellerWalletTransaction::TYPE_WITHDRAWAL_REQUEST
                    )

                    ->update([
                        'status' =>
                            SellerWalletTransaction::STATUS_POSTED,

                        'processed_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Final Withdrawal
                |--------------------------------------------------------------------------
                */

                $withdrawal
                    ->forceFill([
                        'status' =>
                            SellerWithdrawal::STATUS_SUCCESSFUL,

                        'paystack_transfer_code' =>
                            $providerData[
                                'transfer_code'
                            ]
                            ??
                            $withdrawal
                                ->paystack_transfer_code,

                        'completed_at' =>
                            now(),

                        'failed_at' =>
                            null,

                        'failure_reason' =>
                            null,

                        'meta' =>
                            array_merge(
                                $withdrawal
                                    ->meta
                                ??
                                [],
                                [
                                    'paystack_final_status' =>
                                        'success',

                                    'paystack_transfer_id' =>
                                        $providerData[
                                            'id'
                                        ]
                                        ??
                                        data_get(
                                            $withdrawal
                                                ->meta,
                                            'paystack_transfer_id'
                                        ),
                                ]
                            ),
                    ])
                    ->save();


                return $withdrawal
                    ->fresh();
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Restore Reserved Funds
    |--------------------------------------------------------------------------
    */

    protected function restoreReservedFunds(
        string $reference,
        string $finalStatus,
        ?string $reason = null
    ): SellerWithdrawal {

        return DB::transaction(
            function () use (
                $reference,
                $finalStatus,
                $reason
            ) {

                /*
                |--------------------------------------------------------------------------
                | Withdrawal
                |--------------------------------------------------------------------------
                */

                $withdrawal =
                    SellerWithdrawal::query()

                        ->where(
                            'reference',
                            $reference
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Already Failed / Reversed
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $withdrawal
                            ->status,
                        [
                            SellerWithdrawal::STATUS_FAILED,
                            SellerWithdrawal::STATUS_REVERSED,
                        ],
                        true
                    )
                ) {

                    return $withdrawal;
                }


                /*
                |--------------------------------------------------------------------------
                | Was Successful?
                |--------------------------------------------------------------------------
                */

                $wasSuccessful =
                    $withdrawal
                        ->status
                    ===
                    SellerWithdrawal::STATUS_SUCCESSFUL;


                /*
                 * Don't convert successful to failed.
                 *
                 * Only successful → reversed is valid.
                 */
                if (
                    $wasSuccessful
                    &&
                    $finalStatus
                    !==
                    SellerWithdrawal::STATUS_REVERSED
                ) {

                    return $withdrawal;
                }


                /*
                |--------------------------------------------------------------------------
                | Wallet
                |--------------------------------------------------------------------------
                */

                $wallet =
                    SellerWallet::query()

                        ->whereKey(
                            $withdrawal
                                ->seller_wallet_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Amount
                |--------------------------------------------------------------------------
                */

                $amount =
                    round(
                        (float)
                        $withdrawal
                            ->amount,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Available Balance
                |--------------------------------------------------------------------------
                */

                $availableBefore =
                    round(
                        (float)
                        $wallet
                            ->available_balance,
                        2
                    );


                $availableAfter =
                    round(
                        $availableBefore
                        +
                        $amount,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Pending Balance
                |--------------------------------------------------------------------------
                */

                $pendingBefore =
                    round(
                        (float)
                        $wallet
                            ->pending_withdrawal_balance,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Wallet Updates
                |--------------------------------------------------------------------------
                */

                $walletUpdates = [
                    'available_balance' =>
                        $availableAfter,
                ];


                /*
                |--------------------------------------------------------------------------
                | Reversal After Success
                |--------------------------------------------------------------------------
                */

                if (
                    $wasSuccessful
                ) {

                    $walletUpdates[
                        'total_withdrawn'
                    ] =
                        max(
                            0,
                            round(
                                (
                                    (float)
                                    $wallet
                                        ->total_withdrawn
                                )
                                -
                                $amount,
                                2
                            )
                        );

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Failure Before Success
                    |--------------------------------------------------------------------------
                    */

                    $walletUpdates[
                        'pending_withdrawal_balance'
                    ] =
                        max(
                            0,
                            round(
                                $pendingBefore
                                -
                                $amount,
                                2
                            )
                        );
                }


                $wallet
                    ->forceFill(
                        $walletUpdates
                    )
                    ->save();


                /*
                |--------------------------------------------------------------------------
                | Original Debit Failed
                |--------------------------------------------------------------------------
                */

                if (
                    !$wasSuccessful
                ) {

                    SellerWalletTransaction::query()

                        ->where(
                            'seller_withdrawal_id',
                            $withdrawal
                                ->id
                        )

                        ->where(
                            'type',
                            SellerWalletTransaction::TYPE_WITHDRAWAL_REQUEST
                        )

                        ->update([
                            'status' =>
                                SellerWalletTransaction::STATUS_FAILED,

                            'processed_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Refund Ledger Entry
                |--------------------------------------------------------------------------
                */

                SellerWalletTransaction::firstOrCreate(
                    [
                        'seller_withdrawal_id' =>
                            $withdrawal
                                ->id,

                        'type' =>
                            SellerWalletTransaction::TYPE_WITHDRAWAL_REFUND,
                    ],
                    [
                        'seller_wallet_id' =>
                            $wallet
                                ->id,

                        'seller_id' =>
                            $withdrawal
                                ->seller_id,

                        'secure_transaction_id' =>
                            null,

                        'reference' =>
                            SellerWalletTransaction::generateReference(),

                        'direction' =>
                            SellerWalletTransaction::DIRECTION_CREDIT,

                        'status' =>
                            SellerWalletTransaction::STATUS_POSTED,

                        'currency' =>
                            $withdrawal
                                ->currency,

                        'amount' =>
                            $amount,

                        'balance_before' =>
                            $availableBefore,

                        'balance_after' =>
                            $availableAfter,

                        'description' =>
                            'Returned failed withdrawal '
                            .
                            $withdrawal
                                ->reference
                            .
                            ' to available balance.',

                        'meta' => [
                            'withdrawal_reference' =>
                                $withdrawal
                                    ->reference,

                            'reason' =>
                                $reason,
                        ],

                        'processed_at' =>
                            now(),
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Withdrawal Final State
                |--------------------------------------------------------------------------
                */

                $withdrawal
                    ->forceFill([
                        'status' =>
                            $finalStatus,

                        'failure_reason' =>
                            $reason,

                        'failed_at' =>
                            now(),

                        'meta' =>
                            array_merge(
                                $withdrawal
                                    ->meta
                                ??
                                [],
                                [
                                    'paystack_final_status' =>
                                        $finalStatus,
                                ]
                            ),
                    ])
                    ->save();


                return $withdrawal
                    ->fresh();
            },
            3
        );
    }
}