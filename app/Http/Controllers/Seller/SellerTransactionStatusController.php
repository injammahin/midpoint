<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Services\TransactionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerTransactionStatusController extends Controller
{
    public function update(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionLifecycleService $lifecycle
    ) {
        $validated =
            $request->validate([
                'status' => [
                    'required',
                    Rule::in([
                        SecureTransaction::STATUS_PREPARING_ITEM,
                        SecureTransaction::STATUS_DISPATCHED,
                        SecureTransaction::STATUS_IN_TRANSIT,
                        SecureTransaction::STATUS_DELIVERED,
                    ]),
                ],
            ]);

        $lifecycle->sellerUpdate(
            $secureTransaction,
            $request->user(),
            $validated['status']
        );

        return redirect()
            ->route(
                'seller.transactions.show',
                [
                    'secureTransaction' =>
                        $secureTransaction->public_token,
                ]
            )
            ->with(
                'success',
                'Order status updated to '
                . $secureTransaction->fresh()->status_label
                . '. The buyer has been notified by email and in MidPoint.'
            );
    }
}