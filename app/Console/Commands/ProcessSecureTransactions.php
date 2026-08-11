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
        'Process MidPoint transaction countdowns and seller payouts';

    public function handle(
        TransactionLifecycleService $lifecycle,
        SellerPayoutService $payouts
    ) {
        SecureTransaction::query()
            ->with('dispute')
            ->whereIn(
                'status',
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ]
            )
            ->whereNotNull('auto_complete_at')
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


        SecureTransaction::query()
            ->with('seller')
            ->where(
                'status',
                SecureTransaction::STATUS_RELEASE_APPROVED
            )
            ->where(
                'payout_status',
                'seller_setup_required'
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
                        if (
                            !$transaction->seller
                            ||
                            !$transaction
                                ->seller
                                ->bank_account_number
                            ||
                            !$transaction
                                ->seller
                                ->bank_code
                        ) {
                            continue;
                        }

                        try {
                            $lifecycle->retryPayout(
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