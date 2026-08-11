<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Services\TransactionTimelineService;
use Illuminate\Http\Request;

class SellerTransactionController extends Controller
{
    public function index(
        Request $request
    ) {
        $user =
            $request->user();

        $query =
            SecureTransaction::query()
                ->with([
                    'buyer',
                ])
                ->where(
                    'seller_id',
                    $user->id
                );

        if ($request->filled('search')) {
            $search =
                trim(
                    $request->input(
                        'search'
                    )
                );

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'title',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'buyer_email',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input(
                    'status'
                )
            );
        }

        $transactions =
            $query
                ->latest('id')
                ->paginate(12)
                ->withQueryString();

        $base =
            SecureTransaction::query()
                ->where(
                    'seller_id',
                    $user->id
                );

        $stats = [
            'total' =>
                (clone $base)->count(),

            'awaiting' =>
                (clone $base)
                    ->where(
                        'status',
                        SecureTransaction::STATUS_AWAITING_PAYMENT
                    )
                    ->count(),

            'secured' =>
                (clone $base)
                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )
                    ->whereNotIn(
                        'status',
                        [
                            SecureTransaction::STATUS_COMPLETED,
                            SecureTransaction::STATUS_CANCELLED,
                        ]
                    )
                    ->count(),

            'completed' =>
                (clone $base)
                    ->where(
                        'status',
                        SecureTransaction::STATUS_COMPLETED
                    )
                    ->count(),
        ];

        return view(
            'seller.transactions.index',
            compact(
                'transactions',
                'stats'
            )
        );
    }

    public function show(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionTimelineService $timelineService
    ) {
        abort_unless(
            (int) $secureTransaction->seller_id
            ===
            (int) $request->user()->id,
            403
        );

        $secureTransaction->load([
            'seller',
            'buyer',
            'successfulPayment',
        ]);

        $timeline =
            $timelineService->build(
                $secureTransaction
            );

        return view(
            'seller.transactions.show',
            [
                'transaction' =>
                    $secureTransaction,

                'timeline' =>
                    $timeline,
            ]
        );
    }
}