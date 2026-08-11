<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Services\TransactionTimelineService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BuyerTransactionController extends Controller
{
    public function index(
        Request $request
    ) {
        $user =
            $request->user();

        $query =
            SecureTransaction::query()
                ->with([
                    'seller',
                ])
                ->where(
                    'buyer_id',
                    $user->id
                )
                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
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
                ->latest('paid_at')
                ->paginate(12)
                ->withQueryString();

        $base =
            SecureTransaction::query()
                ->where(
                    'buyer_id',
                    $user->id
                )
                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                );

        $stats = [
            'total' =>
                (clone $base)->count(),

            'active' =>
                (clone $base)
                    ->whereNotIn(
                        'status',
                        [
                            SecureTransaction::STATUS_COMPLETED,
                            SecureTransaction::STATUS_CANCELLED,
                        ]
                    )
                    ->count(),

            'inspection' =>
                (clone $base)
                    ->where(
                        'status',
                        SecureTransaction::STATUS_INSPECTION
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
            'buyer.transactions.index',
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
            (int) $secureTransaction->buyer_id
            ===
            (int) $request->user()->id,
            403
        );

        if (
            $secureTransaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {
            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                );
        }

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
            'buyer.transactions.show',
            [
                'transaction' =>
                    $secureTransaction,

                'timeline' =>
                    $timeline,
            ]
        );
    }

    public function invoice(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        abort_unless(
            (int) $secureTransaction->buyer_id
            ===
            (int) $request->user()->id,
            403
        );

        abort_unless(
            $secureTransaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,
            404
        );

        $secureTransaction->load([
            'seller',
            'buyer',
        ]);

        return Pdf::loadView(
            'pdf.transaction-payment-invoice',
            [
                'transaction' =>
                    $secureTransaction,
            ]
        )
            ->setPaper('a4')
            ->download(
                $secureTransaction->invoice_number
                .
                '.pdf'
            );
    }
}