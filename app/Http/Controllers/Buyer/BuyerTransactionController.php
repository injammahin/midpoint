<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;

use App\Services\TransactionTimelineService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

class BuyerTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Transaction List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query =
            SecureTransaction::query()

                ->with([

                    'seller',

                    'dispute',

                ])

                ->where(
                    'buyer_id',
                    $user->id
                )

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                );


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {

            $search =
                trim(
                    $request->input(
                        'search'
                    )
                );


            $query->where(
                function ($query) use ($search) {

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
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'status'
            )
        ) {

            $query->where(
                'status',
                $request->input(
                    'status'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        $transactions =
            $query

                ->latest(
                    'paid_at'
                )

                ->paginate(
                    12
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Statistics Base
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                (clone $base)
                    ->count(),


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


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'buyer.transactions.index',
            compact(
                'transactions',
                'stats'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Details
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionTimelineService $timelineService
    ) {
        /*
        |--------------------------------------------------------------------------
        | Buyer Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(

            (int) $secureTransaction->buyer_id
            ===
            (int) $request
                ->user()
                ->id,

            403

        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Paid
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([

            'seller',

            'buyer',

            'successfulPayment',

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Needed to distinguish:
            |
            | active dispute
            | resolved dispute
            |
            */

            'dispute',

            'sellerReview',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Timeline
        |--------------------------------------------------------------------------
        */

        $timeline =
            $timelineService->build(
                $secureTransaction
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Review Required
        |--------------------------------------------------------------------------
        |
        | After the buyer accepts the order and the transaction reaches the
        | completed state, show the seller-review popup until this transaction
        | has one review.
        |
        */

        $reviewRequired =

            $secureTransaction->status
            ===
            SecureTransaction::STATUS_COMPLETED

            &&

            $secureTransaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID

            &&

            !$secureTransaction->sellerReview;


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'buyer.transactions.show',
            [

                'transaction' =>
                    $secureTransaction,

                'timeline' =>
                    $timeline,

                'reviewRequired' =>
                    $reviewRequired,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Invoice
    |--------------------------------------------------------------------------
    */

    public function invoice(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(

            (int) $secureTransaction->buyer_id
            ===
            (int) $request
                ->user()
                ->id,

            403

        );


        /*
        |--------------------------------------------------------------------------
        | Paid Only
        |--------------------------------------------------------------------------
        */

        abort_unless(

            $secureTransaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,

            404

        );


        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([

            'seller',

            'buyer',

        ]);


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView(
            'pdf.transaction-payment-invoice',
            [

                'transaction' =>
                    $secureTransaction,

            ]
        )

            ->setPaper(
                'a4'
            )

            ->download(
                $secureTransaction
                    ->invoice_number
                .
                '.pdf'
            );
    }
}