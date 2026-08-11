<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Services\TransactionLifecycleService;
use Illuminate\Http\Request;
use Throwable;

class BuyerTransactionActionController extends Controller
{
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
                . $hours
                . '-hour inspection period has started.'
            );
    }

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

            if (
                $secureTransaction->status
                ===
                SecureTransaction::STATUS_COMPLETED
            ) {
                $message =
                    'Order accepted successfully and the seller payout has been completed.';

            } elseif (
                $secureTransaction->payout_status
                ===
                'seller_setup_required'
            ) {
                $message =
                    'Order accepted successfully. Your payment release is approved; the seller must complete their payout setup before MidPoint can send the funds.';

            } else {
                $message =
                    'Order accepted successfully. Seller payout is now being processed.';
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
                    'We could not complete this action right now. Please try again.'
                );
        }
    }
}