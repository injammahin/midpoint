<?php

namespace App\Services;

use App\Models\SellerKycVerification;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use App\Support\IdentityNameMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    | Create Withdrawal
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


        if (
            $amount < $minimum
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
        | Reserve Seller Money
        |--------------------------------------------------------------------------
        */

        $withdrawal =
            DB::transaction(
                function () use (
                    $seller,
                    $amount
                ) {

                    /*
                     * Lock seller so important seller-level operations
                     * cannot race against each other.
                     */
                    User::query()
                        ->whereKey(
                            $seller->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                    /*
                     * KYC must be approved.
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


                    /*
                     * Seller must have one active verified account.
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
                    if (
                        !IdentityNameMatcher::matches(
                            $kyc->verified_full_name,
                            $account->account_name
                        )
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Your active withdrawal bank account does not match your verified government identity.',
                        ]);
                    }

                    /*
                     * Lock wallet before checking/deducting balance.
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
                        ||
                        (float)
                        $wallet
                            ->available_balance
                        <
                        $amount
                    ) {

                        throw ValidationException::withMessages([
                            'amount' =>
                                'Your available Midpoint balance is not enough for this withdrawal.',
                        ]);
                    }


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
                    | Reserve Funds
                    |--------------------------------------------------------------------------
                    |
                    | Available balance goes DOWN immediately.
                    | Pending withdrawal goes UP immediately.
                    |
                    */

                    $balanceBefore =
                        round(
                            (float)
                            $wallet
                                ->available_balance,
                            2
                        );


                    $balanceAfter =
                        round(
                            $balanceBefore
                            -
                            $amount,
                            2
                        );


                    $pendingAfter =
                        round(
                            (float)
                            $wallet
                                ->pending_withdrawal_balance
                            +
                            $amount,
                            2
                        );


                    $wallet->forceFill([
                        'available_balance' =>
                            $balanceAfter,

                        'pending_withdrawal_balance' =>
                            $pendingAfter,
                    ])->save();


                    /*
                     * Generate our own reference.
                     */
                    $reference =
                        SellerWithdrawal::generateReference(
                            $seller->id
                        );


                    /*
                     * Save bank snapshot.
                     *
                     * Even if seller deletes the account later,
                     * withdrawal history remains complete.
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

                            'paystack_transfer_reference' =>
                                $reference,

                            'paystack_recipient_code' =>
                                $account
                                    ->paystack_recipient_code,

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
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Wallet Ledger Debit
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
                            $withdrawal->currency,

                        'amount' =>
                            $amount,

                        'balance_before' =>
                            $balanceBefore,

                        'balance_after' =>
                            $balanceAfter,

                        'description' =>
                            'Withdrawal requested to '
                            .
                            $account->bank_name
                            .
                            ' ••••'
                            .
                            $account
                                ->account_number_last4,

                        'meta' => [
                            'withdrawal_reference' =>
                                $reference,

                            'bank_name' =>
                                $account->bank_name,

                            'account_name' =>
                                $account->account_name,

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
        | Send Transfer To Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $data =
                $this
                    ->paystack
                    ->initiateTransfer([
                        'source' =>
                            'balance',

                        /*
                         * Paystack amount is Kobo.
                         */
                        'amount' =>
                            (int)
                            round(
                                (
                                    (float)
                                    $withdrawal->amount
                                )
                                *
                                100
                            ),

                        'recipient' =>
                            $withdrawal
                                ->paystack_recipient_code,

                        /*
                         * SAME reference is intentionally used.
                         */
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


            $localStatus =
                $providerStatus
                ===
                'otp'

                    ? SellerWithdrawal::STATUS_OTP

                    : SellerWithdrawal::STATUS_PROCESSING;


            $withdrawal->forceFill([
                'paystack_transfer_code' =>
                    $data[
                        'transfer_code'
                    ]
                    ??
                    null,

                'status' =>
                    $localStatus,

                'initiated_at' =>
                    now(),

                'meta' =>
                    array_merge(
                        $withdrawal->meta
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
            ])->save();


            /*
             * Test transfers can respond immediately as success.
             */
            if (
                $providerStatus
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


            return $withdrawal
                ->fresh();

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | CRITICAL: DO NOT IMMEDIATELY REFUND
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | 1. Paystack accepts transfer.
            | 2. Your server connection times out.
            | 3. Laravel receives exception.
            |
            | If we refunded immediately, seller might receive:
            |
            | bank payout + wallet refund.
            |
            | Therefore funds remain reserved until Paystack status is
            | reconciled using the SAME transfer reference.
            |
            */

            Log::error(
                'Seller withdrawal Paystack initiation response was not confirmed.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,

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


            $withdrawal->forceFill([
                'status' =>
                    SellerWithdrawal::STATUS_PROCESSING,

                'failure_reason' =>
                    'Paystack initiation response is awaiting reconciliation.',

                'meta' =>
                    array_merge(
                        $withdrawal->meta
                        ??
                        [],
                        [
                            'initiation_exception' =>
                                $exception
                                    ->getMessage(),
                        ]
                    ),
            ])->save();


            return $withdrawal
                ->fresh();
        }
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

            $this->reconcile(
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

        if (
            $withdrawal
                ->isFinal()
        ) {

            return $withdrawal;
        }


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
                            (
                                'Paystack transfer ended with status: '
                                .
                                $status
                                .
                                '.'
                            )
                        )
                    );
            }


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
             * Still processing.
             */
            $withdrawal->forceFill([
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
                    $withdrawal
                        ->paystack_transfer_code,

                'failure_reason' =>
                    null,
            ])->save();


            return $withdrawal
                ->fresh();

        } catch (
            Throwable $exception
        ) {

            /*
             * If initiation failed before Paystack ever created the
             * transfer, verification may return "not found".
             *
             * We don't release instantly because of race/network safety.
             */
            $notFound =
                str_contains(
                    strtolower(
                        $exception
                            ->getMessage()
                    ),
                    'not found'
                );


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
                        $withdrawal->id,

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


        /*
         * Not a new seller-wallet withdrawal.
         *
         * Existing legacy transaction payout handler can continue.
         */
        if (
            !$withdrawal
        ) {

            return null;
        }


        return match (
            $eventName
        ) {

            'transfer.success' =>
                $this->markSuccessful(
                    $withdrawal
                        ->reference,
                    $data
                ),

            'transfer.failed' =>
                $this->restoreReservedFunds(
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
                $this->restoreReservedFunds(
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

                $withdrawal =
                    SellerWithdrawal::query()

                        ->where(
                            'reference',
                            $reference
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                 * Idempotency:
                 * webhook may arrive more than once.
                 */
                if (
                    $withdrawal->status
                    ===
                    SellerWithdrawal::STATUS_SUCCESSFUL
                ) {

                    return $withdrawal;
                }


                /*
                 * Don't change an already-final failed withdrawal
                 * into successful due to a stale event.
                 */
                if (
                    in_array(
                        $withdrawal->status,
                        [
                            SellerWithdrawal::STATUS_FAILED,
                            SellerWithdrawal::STATUS_REVERSED,
                        ],
                        true
                    )
                ) {

                    return $withdrawal;
                }


                $wallet =
                    SellerWallet::query()

                        ->whereKey(
                            $withdrawal
                                ->seller_wallet_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


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


                if (
                    $pending + 0.001
                    <
                    $amount
                ) {

                    throw new RuntimeException(
                        'Wallet pending withdrawal balance is inconsistent.'
                    );
                }


                /*
                 * Pending decreases.
                 * Total withdrawn increases.
                 *
                 * Available was already deducted during request.
                 */
                $wallet->forceFill([
                    'pending_withdrawal_balance' =>
                        round(
                            $pending
                            -
                            $amount,
                            2
                        ),

                    'total_withdrawn' =>
                        round(
                            (float)
                            $wallet
                                ->total_withdrawn
                            +
                            $amount,
                            2
                        ),
                ])->save();


                /*
                 * Finalize debit ledger.
                 */
                SellerWalletTransaction::query()

                    ->where(
                        'seller_withdrawal_id',
                        $withdrawal->id
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


                $withdrawal->forceFill([
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

                    'failure_reason' =>
                        null,

                    'meta' =>
                        array_merge(
                            $withdrawal->meta
                            ??
                            [],
                            [
                                'paystack_final_status' =>
                                    'success',
                            ]
                        ),
                ])->save();


                return $withdrawal
                    ->fresh();
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Return Failed/Reversed Withdrawal To Wallet
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

                $withdrawal =
                    SellerWithdrawal::query()

                        ->where(
                            'reference',
                            $reference
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                 * Already restored.
                 */
                if (
                    in_array(
                        $withdrawal->status,
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
                 * A successful withdrawal may later be reversed.
                 */
                $wasSuccessful =
                    $withdrawal->status
                    ===
                    SellerWithdrawal::STATUS_SUCCESSFUL;


                /*
                 * Successful withdrawal should only be undone
                 * by a genuine Paystack reversal.
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


                $wallet =
                    SellerWallet::query()

                        ->whereKey(
                            $withdrawal
                                ->seller_wallet_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                $amount =
                    round(
                        (float)
                        $withdrawal
                            ->amount,
                        2
                    );


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


                $pendingBefore =
                    round(
                        (float)
                        $wallet
                            ->pending_withdrawal_balance,
                        2
                    );


                $walletUpdates = [
                    'available_balance' =>
                        $availableAfter,
                ];


                if (
                    $wasSuccessful
                ) {

                    /*
                     * Successful transfer was later reversed.
                     */
                    $walletUpdates[
                        'total_withdrawn'
                    ] =
                        max(
                            0,
                            round(
                                (float)
                                $wallet
                                    ->total_withdrawn
                                -
                                $amount,
                                2
                            )
                        );

                } else {

                    /*
                     * It was still reserved.
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
                 * Original withdrawal debit failed.
                 */
                if (
                    !$wasSuccessful
                ) {

                    SellerWalletTransaction::query()

                        ->where(
                            'seller_withdrawal_id',
                            $withdrawal->id
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
                 * firstOrCreate means duplicate webhook cannot
                 * create multiple refunds.
                 */
                SellerWalletTransaction::firstOrCreate(
                    [
                        'seller_withdrawal_id' =>
                            $withdrawal->id,

                        'type' =>
                            SellerWalletTransaction::TYPE_WITHDRAWAL_REFUND,
                    ],
                    [
                        'seller_wallet_id' =>
                            $wallet->id,

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


                $withdrawal->forceFill([
                    'status' =>
                        $finalStatus,

                    'failure_reason' =>
                        $reason,

                    'failed_at' =>
                        now(),

                    'meta' =>
                        array_merge(
                            $withdrawal->meta
                            ??
                            [],
                            [
                                'paystack_final_status' =>
                                    $finalStatus,
                            ]
                        ),
                ])->save();


                return $withdrawal
                    ->fresh();
            },
            3
        );
    }
}