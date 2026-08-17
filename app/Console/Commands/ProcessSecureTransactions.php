<?php

namespace App\Console\Commands;

use App\Models\SecureTransaction;
use App\Services\SellerPayoutService;
use App\Services\TransactionLifecycleService;
use Illuminate\Console\Command;
use Throwable;

class ProcessSecureTransactions extends Command
{
    protected $signature =
        'transactions:process';


    protected $description =
        'Process Midpoint transaction countdowns, seller wallet releases, and legacy payouts';


    public function handle(
        TransactionLifecycleService $lifecycle,
        SellerPayoutService $payouts
    ) {

        /*
        |--------------------------------------------------------------------------
        | Automatic Release After Delivery / Inspection Timeout
        |--------------------------------------------------------------------------
        */

        SecureTransaction::query()
            ->with(
                'dispute'
            )
            ->whereIn(
                'status',
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ]
            )
            ->whereNotNull(
                'auto_complete_at'
            )
            ->where(
                'auto_complete_at',
                '<=',
                now()
            )
            ->chunkById(
                50,
                function ($transactions) use (
                    $lifecycle
                ) {

                    foreach (
                        $transactions
                        as
                        $transaction
                    ) {

                        try {

                            $lifecycle->autoRelease(
                                $transaction
                            );

                        } catch (Throwable $exception) {

                            report(
                                $exception
                            );
                        }
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Recover Release-Approved Transactions Into Wallet
        |--------------------------------------------------------------------------
        |
        | This solves transactions such as the one in your screenshot.
        |
        | Old behavior:
        |
        | release_approved
        | seller_setup_required
        |
        | New behavior:
        |
        | release_approved
        |        ->
        | wallet credit
        |        ->
        | completed
        |
        |
        | Only transactions WITHOUT an existing Paystack transfer reference are
        | allowed here.
        |
        */

        SecureTransaction::query()
            ->where(
                'status',
                SecureTransaction::STATUS_RELEASE_APPROVED
            )
            ->where(
                'payment_status',
                SecureTransaction::PAYMENT_PAID
            )
            ->whereNull(
                'funds_released_at'
            )
            ->whereNull(
                'paystack_transfer_reference'
            )
            ->where(
                function ($query) {

                    $query
                        ->whereNull(
                            'payout_status'
                        )
                        ->orWhereIn(
                            'payout_status',
                            [
                                SecureTransaction::PAYOUT_LOCKED,

                                'seller_setup_required',

                                SecureTransaction::PAYOUT_WALLET_PENDING,
                            ]
                        );
                }
            )
            ->chunkById(
                50,
                function ($transactions) use (
                    $lifecycle
                ) {

                    foreach (
                        $transactions
                        as
                        $transaction
                    ) {

                        try {

                            $lifecycle
                                ->creditApprovedReleaseToWallet(
                                    $transaction
                                );

                        } catch (Throwable $exception) {

                            report(
                                $exception
                            );
                        }
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Legacy Direct Bank Transfers
        |--------------------------------------------------------------------------
        |
        | Do NOT start new bank transfers.
        |
        | Only verify old transfers that were already initialized before the
        | wallet system was introduced.
        |
        */

        SecureTransaction::query()
            ->where(
                'status',
                SecureTransaction::STATUS_PAYOUT_PENDING
            )
            ->whereNotNull(
                'paystack_transfer_reference'
            )
            ->chunkById(
                50,
                function ($transactions) use (
                    $lifecycle,
                    $payouts
                ) {

                    foreach (
                        $transactions
                        as
                        $transaction
                    ) {

                        try {

                            $data =
                                $payouts->verify(
                                    $transaction
                                );


                            $lifecycle->handleTransferStatus(
                                $transaction,
                                $data['status']
                                ??
                                'pending'
                            );

                        } catch (Throwable $exception) {

                            report(
                                $exception
                            );
                        }
                    }
                }
            );


        return self::SUCCESS;
    }
}