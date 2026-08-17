<?php

namespace App\Services;

use App\Models\SecureTransaction;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SellerWalletService
{
    /*
    |--------------------------------------------------------------------------
    | Credit Released Transaction To Seller Wallet
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We do NOT calculate seller earnings here.
    |
    | Your existing transaction payment algorithm already calculates:
    |
    | seller_net_amount
    |
    | So we use exactly that value.
    |
    */
    public function creditTransactionRelease(
        SecureTransaction $transaction
    ): array {

        return DB::transaction(
            function () use (
                $transaction
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Transaction
                |--------------------------------------------------------------------------
                */

                $lockedTransaction =
                    SecureTransaction::query()
                        ->whereKey(
                            $transaction->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Payment Must Already Be Successful
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedTransaction->payment_status
                    !==
                    SecureTransaction::PAYMENT_PAID
                ) {

                    throw new RuntimeException(
                        'Only a paid transaction can be released to the seller wallet.'
                    );
                }


                if (
                    !$lockedTransaction->seller_id
                ) {

                    throw new RuntimeException(
                        'Seller account is missing from this transaction.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Protect Old Paystack Direct Bank Transfers
                |--------------------------------------------------------------------------
                |
                | Some existing transactions may already have gone through your
                | previous direct-to-bank payout system.
                |
                | If Paystack transfer_reference already exists, NEVER add the
                | same amount to the wallet too.
                |
                | Otherwise seller could get paid twice.
                |
                */

                if (
                    $lockedTransaction
                        ->paystack_transfer_reference
                    &&
                    !$lockedTransaction
                        ->funds_released_at
                ) {

                    throw new RuntimeException(
                        'A legacy bank transfer has already been initialized for this transaction.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Seller
                |--------------------------------------------------------------------------
                |
                | Different transactions belonging to the same seller can be
                | completed simultaneously.
                |
                | Locking the seller serializes wallet balance changes.
                |
                */

                User::query()
                    ->whereKey(
                        $lockedTransaction->seller_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Idempotency Check
                |--------------------------------------------------------------------------
                |
                | Has this transaction already been credited?
                |
                */

                $existingEntry =
                    SellerWalletTransaction::query()
                        ->where(
                            'secure_transaction_id',
                            $lockedTransaction->id
                        )
                        ->where(
                            'type',
                            SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                        )
                        ->first();


                if (
                    $existingEntry
                ) {

                    $alreadyCompleted =
                        $lockedTransaction->status
                        ===
                        SecureTransaction::STATUS_COMPLETED
                        &&
                        !is_null(
                            $lockedTransaction
                                ->funds_released_at
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Repair Transaction State If Ledger Already Exists
                    |--------------------------------------------------------------------------
                    |
                    | The ledger is the financial proof.
                    |
                    | If it exists but transaction status was not updated because
                    | the request stopped unexpectedly, fix only the status.
                    |
                    | DO NOT credit again.
                    |
                    */

                    if (
                        !$alreadyCompleted
                    ) {

                        $releasedAt =
                            $existingEntry->processed_at
                            ?: now();


                        $lockedTransaction
                            ->forceFill([
                                'status' =>
                                    SecureTransaction::STATUS_COMPLETED,

                                'payout_status' =>
                                    SecureTransaction::PAYOUT_WALLET_CREDITED,

                                'funds_released_at' =>
                                    $lockedTransaction->funds_released_at
                                    ?: $releasedAt,

                                'completed_at' =>
                                    $lockedTransaction->completed_at
                                    ?: $releasedAt,

                                'auto_complete_at' =>
                                    null,
                            ])
                            ->save();
                    }


                    return [

                        'wallet' =>
                            SellerWallet::query()
                                ->findOrFail(
                                    $existingEntry
                                        ->seller_wallet_id
                                ),

                        'entry' =>
                            $existingEntry,

                        'credited' =>
                            false,

                        'completed_now' =>
                            !$alreadyCompleted,
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | Find Seller Wallet
                |--------------------------------------------------------------------------
                */

                $wallet =
                    SellerWallet::query()
                        ->where(
                            'seller_id',
                            $lockedTransaction->seller_id
                        )
                        ->lockForUpdate()
                        ->first();


                /*
                |--------------------------------------------------------------------------
                | First Release For This Seller
                |--------------------------------------------------------------------------
                */

                if (
                    !$wallet
                ) {

                    $wallet =
                        SellerWallet::create([

                            'seller_id' =>
                                $lockedTransaction->seller_id,

                            'currency' =>
                                strtoupper(
                                    $lockedTransaction->currency
                                    ?: 'NGN'
                                ),

                            'available_balance' =>
                                0,

                            'pending_withdrawal_balance' =>
                                0,

                            'total_credited' =>
                                0,

                            'total_withdrawn' =>
                                0,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Existing Seller Net Amount
                |--------------------------------------------------------------------------
                |
                | Example from your screenshot:
                |
                | Buyer paid       ₦143,600
                | Product subtotal ₦140,000
                | Delivery         ₦3,600
                | Service fee      -₦70,000
                | VAT              -₦5,250
                |
                | Seller net       ₦68,350
                |
                | We credit ₦68,350.
                |
                */

                $amount =
                    round(
                        (float)
                        $lockedTransaction
                            ->seller_net_amount,
                        2
                    );


                if (
                    $amount <= 0
                ) {

                    throw new RuntimeException(
                        'Seller wallet credit amount is invalid.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Calculate New Wallet Balance
                |--------------------------------------------------------------------------
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
                        +
                        $amount,
                        2
                    );


                $totalCredited =
                    round(
                        (float)
                        $wallet->total_credited
                        +
                        $amount,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Wallet
                |--------------------------------------------------------------------------
                */

                $wallet
                    ->forceFill([

                        'currency' =>
                            strtoupper(
                                $lockedTransaction->currency
                                ?: $wallet->currency
                                ?: 'NGN'
                            ),

                        'available_balance' =>
                            $balanceAfter,

                        'total_credited' =>
                            $totalCredited,

                    ])
                    ->save();


                $releasedAt =
                    now();


                /*
                |--------------------------------------------------------------------------
                | Create Financial Ledger
                |--------------------------------------------------------------------------
                */

                $entry =
                    SellerWalletTransaction::create([

                        'seller_wallet_id' =>
                            $wallet->id,

                        'seller_id' =>
                            $lockedTransaction->seller_id,

                        'secure_transaction_id' =>
                            $lockedTransaction->id,

                        'reference' =>
                            SellerWalletTransaction::generateReference(),

                        'type' =>
                            SellerWalletTransaction::TYPE_TRANSACTION_RELEASE,

                        'direction' =>
                            SellerWalletTransaction::DIRECTION_CREDIT,

                        'status' =>
                            SellerWalletTransaction::STATUS_POSTED,

                        'currency' =>
                            strtoupper(
                                $lockedTransaction->currency
                                ?: 'NGN'
                            ),

                        'amount' =>
                            $amount,

                        'balance_before' =>
                            $balanceBefore,

                        'balance_after' =>
                            $balanceAfter,

                        'description' =>
                            'Funds released from Midpoint transaction '
                            .
                            $lockedTransaction->reference,

                        'meta' => [

                            'transaction_reference' =>
                                $lockedTransaction->reference,

                            'transaction_source' =>
                                $lockedTransaction->transaction_source,

                            'gross_paid' =>
                                (float)
                                $lockedTransaction->paid_amount,

                            'service_fee' =>
                                (float)
                                $lockedTransaction->service_fee_amount,

                            'vat' =>
                                (float)
                                $lockedTransaction->vat_amount,

                            'seller_net_amount' =>
                                $amount,
                        ],

                        'processed_at' =>
                            $releasedAt,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Transaction Is Now Completed
                |--------------------------------------------------------------------------
                |
                | In the NEW algorithm:
                |
                | funds_released_at
                |
                | means released from escrow into Midpoint seller balance.
                |
                | It DOES NOT mean money was transferred to bank.
                |
                */

                $lockedTransaction
                    ->forceFill([

                        'status' =>
                            SecureTransaction::STATUS_COMPLETED,

                        'payout_status' =>
                            SecureTransaction::PAYOUT_WALLET_CREDITED,

                        'funds_released_at' =>
                            $releasedAt,

                        'completed_at' =>
                            $releasedAt,

                        'auto_complete_at' =>
                            null,

                    ])
                    ->save();


                return [

                    'wallet' =>
                        $wallet->fresh(),

                    'entry' =>
                        $entry,

                    'credited' =>
                        true,

                    'completed_now' =>
                        true,
                ];
            },

            /*
            |--------------------------------------------------------------------------
            | Deadlock Retry
            |--------------------------------------------------------------------------
            */

            3
        );
    }
}