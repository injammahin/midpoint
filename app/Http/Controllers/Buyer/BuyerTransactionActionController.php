<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Services\TransactionLifecycleService;
use Illuminate\Http\Request;
use Throwable;

class BuyerTransactionActionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Start Inspection
    |--------------------------------------------------------------------------
    */

    public function inspection(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionLifecycleService $lifecycle
    ) {

        $lifecycle->startInspection(
            $secureTransaction,
            $request->user()
        );


        $hours =
            (int)
            config(
                'secure_transactions.inspection_hours',
                8
            );


        return redirect()
            ->route(
                'buyer.transactions.show',
                [
                    'secureTransaction' =>
                        $secureTransaction->public_token,
                ]
            )
            ->with(
                'success',
                'Your '
                .
                $hours
                .
                '-hour inspection period has started.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Accept Order + Release To Seller Midpoint Wallet
    |--------------------------------------------------------------------------
    */

    public function accept(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionLifecycleService $lifecycle
    ) {

        try {

            $lifecycle->acceptAndRelease(
                $secureTransaction,
                $request->user()
            );


            $secureTransaction->refresh();


            /*
            |--------------------------------------------------------------------------
            | New Wallet-Based Completion
            |--------------------------------------------------------------------------
            */

            if (
                $secureTransaction->status
                ===
                SecureTransaction::STATUS_COMPLETED
                &&
                $secureTransaction->payout_status
                ===
                SecureTransaction::PAYOUT_WALLET_CREDITED
            ) {

                $message =
                    'Order accepted successfully. The seller funds have been released to the seller\'s Midpoint balance.';


            /*
            |--------------------------------------------------------------------------
            | Old Transaction Already Has Bank Transfer
            |--------------------------------------------------------------------------
            */

            } elseif (
                $secureTransaction
                    ->paystack_transfer_reference
            ) {

                $message =
                    'Order accepted successfully. This older transaction already has a bank payout in progress.';


            } else {

                $message =
                    'Order accepted successfully. The release has been approved and Midpoint is finalizing the seller wallet credit.';
            }


            return redirect()
                ->route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $secureTransaction->public_token,
                    ]
                )
                ->with(
                    'success',
                    $message
                );

        } catch (Throwable $exception) {

            report(
                $exception
            );


            return redirect()
                ->route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $secureTransaction->public_token,
                    ]
                )
                ->with(
                    'error',
                    'The order was accepted, but Midpoint could not finalize the fund release right now. Please try again or allow the automatic processor to retry.'
                );
        }
    }
}