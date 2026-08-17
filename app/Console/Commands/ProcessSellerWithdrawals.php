<?php

namespace App\Console\Commands;

use App\Models\SellerWithdrawal;
use App\Services\SellerWithdrawalService;
use Illuminate\Console\Command;

class ProcessSellerWithdrawals extends Command
{
    protected $signature =
        'seller-withdrawals:process';


    protected $description =
        'Reconcile pending seller wallet withdrawals with Paystack.';


    public function handle(
        SellerWithdrawalService $withdrawals
    ): int {

        $items =
            SellerWithdrawal::query()

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
                    100
                )

                ->get();


        foreach (
            $items
            as
            $withdrawal
        ) {

            $withdrawals
                ->reconcile(
                    $withdrawal
                );
        }


        $this->info(
            'Reconciled '
            .
            $items->count()
            .
            ' pending seller withdrawal(s).'
        );


        return self::SUCCESS;
    }
}