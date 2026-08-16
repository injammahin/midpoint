<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;

use App\Services\TransactionTimelineService;

use Illuminate\Http\Request;

class SellerTransactionController extends Controller
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

                    'buyer',

                    'dispute',

                ])

                ->where(
                    'seller_id',
                    $user->id
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
                        )

                        ->orWhere(
                            'buyer_email',
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
                    'id'
                )

                ->paginate(
                    12
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Stats Base
        |--------------------------------------------------------------------------
        */

        $base =
            SecureTransaction::query()

                ->where(
                    'seller_id',
                    $user->id
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


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'seller.transactions.index',
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
        | Seller Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(

            (int) $secureTransaction->seller_id
            ===
            (int) $request
                ->user()
                ->id,

            403

        );


        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([

            /*
            |--------------------------------------------------------------------------
            | Seller
            |--------------------------------------------------------------------------
            */

            'seller',


            /*
            |--------------------------------------------------------------------------
            | Buyer
            |--------------------------------------------------------------------------
            */

            'buyer',


            /*
            |--------------------------------------------------------------------------
            | Ordered Listed Product
            |--------------------------------------------------------------------------
            |
            | This allows the seller transaction page to know exactly which
            | SellerProduct is connected to this transaction.
            |
            */

            'product',


            /*
            |--------------------------------------------------------------------------
            | Successful Payment
            |--------------------------------------------------------------------------
            */

            'successfulPayment',


            /*
            |--------------------------------------------------------------------------
            | Dispute
            |--------------------------------------------------------------------------
            */

            'dispute',

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
        | View
        |--------------------------------------------------------------------------
        */

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