<?php

namespace App\Services;

use App\Models\SellerInvoice;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SellerPackageWalletPaymentService
{
    public function __construct(
        protected SellerInvoicePaymentService $sellerPayments
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Pay Seller Package Invoice From Midpoint Wallet
    |--------------------------------------------------------------------------
    */

    public function pay(
        User $seller,
        SellerInvoice $invoice
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Financial Transaction
        |--------------------------------------------------------------------------
        |
        | Everything below is atomic:
        |
        | wallet deduction
        | wallet ledger
        | invoice paid
        | subscription activation
        |
        | If ANY of these financial operations fail, Laravel rolls everything back.
        |
        */

        $result =
            DB::transaction(
                function () use (
                    $seller,
                    $invoice
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Seller
                    |--------------------------------------------------------------------------
                    */

                    $seller =
                        User::query()

                            ->whereKey(
                                $seller->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Invoice
                    |--------------------------------------------------------------------------
                    */

                    $invoice =
                        SellerInvoice::query()

                            ->whereKey(
                                $invoice->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Ownership
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $invoice->user_id
                        !==
                        (int) $seller->id
                    ) {

                        throw new RuntimeException(
                            'This seller invoice does not belong to your account.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Initial Package Cannot Use Wallet
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice->isInitialPurchase()
                    ) {

                        throw new RuntimeException(
                            'Your first seller package payment must be completed with Paystack.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | No Downgrade
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice->purchase_type
                        ===
                        SellerInvoice::TYPE_DOWNGRADE
                    ) {

                        throw new RuntimeException(
                            'Seller package downgrades are not allowed.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Package Payment Ledger
                    |--------------------------------------------------------------------------
                    |
                    | Prevent duplicate debit.
                    |
                    */

                    $existingEntry =
                        SellerWalletTransaction::query()

                            ->where(
                                'seller_invoice_id',
                                $invoice->id
                            )

                            ->where(
                                'type',
                                SellerWalletTransaction::TYPE_PACKAGE_PAYMENT
                            )

                            ->lockForUpdate()

                            ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Already Paid
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice->status
                        ===
                        'paid'
                    ) {

                        /*
                         * Same invoice already paid by wallet.
                         */
                        if (
                            $invoice->payment_method
                            ===
                            'midpoint_wallet'
                            &&
                            $existingEntry
                        ) {

                            $wallet =
                                SellerWallet::query()

                                    ->where(
                                        'seller_id',
                                        $seller->id
                                    )

                                    ->first();


                            return [
                                'newly_paid' =>
                                    false,

                                'debited' =>
                                    false,

                                'invoice_id' =>
                                    $invoice->id,

                                'wallet_balance' =>
                                    (float) (
                                        $wallet
                                            ?->available_balance
                                        ?:
                                        0
                                    ),

                                'entry' =>
                                    $existingEntry,

                                'email_sent' =>
                                    null,

                                'seller_notified' =>
                                    null,
                            ];
                        }


                        throw new RuntimeException(
                            'This seller invoice has already been paid.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Must Be Unpaid
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice->status
                        !==
                        'unpaid'
                    ) {

                        throw new RuntimeException(
                            'This seller invoice is no longer payable.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Invoice Amount
                    |--------------------------------------------------------------------------
                    */

                    $amount =
                        round(
                            (float) $invoice->amount,
                            2
                        );


                    if (
                        $amount <= 0
                    ) {

                        throw new RuntimeException(
                            'The seller invoice amount is invalid.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Recovery Path
                    |--------------------------------------------------------------------------
                    |
                    | Normally impossible because wallet + invoice are committed
                    | together.
                    |
                    | But if a historical ledger exists while invoice is unfinished,
                    | do NOT debit again.
                    |
                    */

                    if (
                        $existingEntry
                    ) {

                        $newlyPaid =
                            $this
                                ->sellerPayments
                                ->fulfillAlternativePayment(
                                    $invoice,
                                    'midpoint_wallet',
                                    $existingEntry->reference,
                                    $existingEntry->processed_at
                                    ?:
                                    now()
                                );


                        $wallet =
                            SellerWallet::query()

                                ->where(
                                    'seller_id',
                                    $seller->id
                                )

                                ->first();


                        return [
                            'newly_paid' =>
                                $newlyPaid,

                            'debited' =>
                                false,

                            'invoice_id' =>
                                $invoice->id,

                            'wallet_balance' =>
                                (float) (
                                    $wallet
                                        ?->available_balance
                                    ?:
                                    0
                                ),

                            'entry' =>
                                $existingEntry,
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Seller Wallet
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

                        throw new RuntimeException(
                            'Your Midpoint Wallet does not have enough available balance for this package payment.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Currency Check
                    |--------------------------------------------------------------------------
                    */

                    $walletCurrency =
                        strtoupper(
                            trim(
                                (string) (
                                    $wallet->currency
                                    ?:
                                    'NGN'
                                )
                            )
                        );


                    $invoiceCurrency =
                        strtoupper(
                            trim(
                                (string) (
                                    $invoice->currency
                                    ?:
                                    'NGN'
                                )
                            )
                        );


                    if (
                        $walletCurrency
                        !==
                        $invoiceCurrency
                    ) {

                        throw new RuntimeException(
                            'Your wallet currency does not match this seller invoice.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Available Balance
                    |--------------------------------------------------------------------------
                    |
                    | Only available_balance is spendable.
                    |
                    | pending_withdrawal_balance must never be spent here.
                    |
                    */

                    $balanceBefore =
                        round(
                            (float) $wallet->available_balance,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Insufficient Balance
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $balanceBefore
                        <
                        $amount
                    ) {

                        $shortfall =
                            round(
                                $amount
                                -
                                $balanceBefore,
                                2
                            );


                        throw new RuntimeException(
                            'Insufficient Midpoint Wallet balance. You need ₦'
                            .
                            number_format(
                                $shortfall,
                                2
                            )
                            .
                            ' more, or you can pay with Paystack.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | New Wallet Balance
                    |--------------------------------------------------------------------------
                    */

                    $balanceAfter =
                        round(
                            $balanceBefore
                            -
                            $amount,
                            2
                        );


                    $totalSpent =
                        round(
                            (float) $wallet->total_spent
                            +
                            $amount,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Debit Wallet
                    |--------------------------------------------------------------------------
                    */

                    $wallet
                        ->forceFill([
                            'available_balance' =>
                                $balanceAfter,

                            'total_spent' =>
                                $totalSpent,
                        ])
                        ->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Wallet Transaction Reference
                    |--------------------------------------------------------------------------
                    */

                    $processedAt =
                        now();


                    $reference =
                        SellerWalletTransaction::generateReference();


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
                                $seller->id,

                            'secure_transaction_id' =>
                                null,

                            'seller_invoice_id' =>
                                $invoice->id,

                            'seller_withdrawal_id' =>
                                null,

                            'reference' =>
                                $reference,

                            'type' =>
                                SellerWalletTransaction::TYPE_PACKAGE_PAYMENT,

                            'direction' =>
                                SellerWalletTransaction::DIRECTION_DEBIT,

                            'status' =>
                                SellerWalletTransaction::STATUS_POSTED,

                            'currency' =>
                                $invoiceCurrency,

                            'amount' =>
                                $amount,

                            'balance_before' =>
                                $balanceBefore,

                            'balance_after' =>
                                $balanceAfter,

                            'description' =>
                                'Seller package '
                                .
                                strtolower(
                                    $invoice->purchase_type_label
                                )
                                .
                                ' payment for invoice '
                                .
                                $invoice->invoice_number,

                            'meta' => [

                                'invoice_id' =>
                                    $invoice->id,

                                'invoice_number' =>
                                    $invoice->invoice_number,

                                'purchase_type' =>
                                    $invoice->purchase_type,

                                'package_name' =>
                                    $invoice->effective_package_name,

                                'package_price' =>
                                    (float)
                                    $invoice
                                        ->effective_package_price,

                                'proration_credit' =>
                                    (float)
                                    $invoice
                                        ->proration_credit,

                                'amount_paid' =>
                                    $amount,
                            ],

                            'processed_at' =>
                                $processedAt,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Mark Invoice Paid + Activate Subscription
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | Reuse existing SellerInvoicePaymentService.
                    |
                    */

                    $newlyPaid =
                        $this
                            ->sellerPayments
                            ->fulfillAlternativePayment(
                                $invoice,
                                'midpoint_wallet',
                                $reference,
                                $processedAt
                            );


                    return [

                        'newly_paid' =>
                            $newlyPaid,

                        'debited' =>
                            true,

                        'invoice_id' =>
                            $invoice->id,

                        'wallet_balance' =>
                            $balanceAfter,

                        'entry' =>
                            $entry,
                    ];
                },
                3
            );


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: DATABASE HAS COMMITTED HERE
        |--------------------------------------------------------------------------
        |
        | Wallet debit + invoice paid + subscription activation are complete.
        |
        | Email and notifications are secondary.
        |
        | NEVER throw them back as payment failures.
        |
        */

        if (
            $result['newly_paid']
        ) {

            try {

                $communications =
                    $this
                        ->sellerPayments
                        ->sendSuccessfulPaymentCommunications(
                            $result['invoice_id']
                        );


                $result['seller_notified'] =
                    (bool) (
                        $communications[
                            'seller_notified'
                        ]
                        ??
                        false
                    );


                $result['email_sent'] =
                    (bool) (
                        $communications[
                            'email_sent'
                        ]
                        ??
                        false
                    );


            } catch (
                Throwable $exception
            ) {

                /*
                 * DO NOT rethrow.
                 *
                 * Payment is already successful.
                 */

                report(
                    $exception
                );


                $result['seller_notified'] =
                    false;


                $result['email_sent'] =
                    false;


                $result['communication_error'] =
                    $exception->getMessage();
            }
        }


        return $result;
    }
}