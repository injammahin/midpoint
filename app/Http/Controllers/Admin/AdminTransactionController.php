<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;

use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Paid Transactions
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Admin must NEVER see transaction links that were only generated.
    |
    | Transaction becomes visible here ONLY after buyer payment has been
    | successfully verified and payment_status becomes "paid".
    |
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
            SecureTransaction::query()

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->with([
                    'buyer',
                    'seller',
                    'successfulPayment',
                    'dispute',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                (string) $request->get(
                    'search'
                )
            );


        if (
            $search !== ''
        ) {

            $query->where(
                function ($builder) use ($search) {

                    $builder

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
                        )

                        ->orWhereHas(
                            'buyer',
                            function ($buyerQuery) use ($search) {

                                $buyerQuery
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        )

                        ->orWhereHas(
                            'seller',
                            function ($sellerQuery) use ($search) {

                                $sellerQuery
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction Status Filter
        |--------------------------------------------------------------------------
        */

        $status =
            trim(
                (string) $request->get(
                    'status'
                )
            );


        if (
            $status !== ''
        ) {

            $query->where(
                'status',
                $status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Payout Status
        |--------------------------------------------------------------------------
        */

        $payoutStatus =
            trim(
                (string) $request->get(
                    'payout_status'
                )
            );


        if (
            $payoutStatus !== ''
        ) {

            $query->where(
                'payout_status',
                $payoutStatus
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Dispute Filter
        |--------------------------------------------------------------------------
        */

        $dispute =
            trim(
                (string) $request->get(
                    'dispute'
                )
            );


        if (
            $dispute === 'yes'
        ) {

            $query->whereHas(
                'dispute'
            );
        }


        if (
            $dispute === 'no'
        ) {

            $query->whereDoesntHave(
                'dispute'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_from'
            )
        ) {

            $query->whereDate(
                'paid_at',
                '>=',
                $request->date_from
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_to'
            )
        ) {

            $query->whereDate(
                'paid_at',
                '<=',
                $request->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $paidTransactionsQuery =
            SecureTransaction::query()

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                );


        $stats = [

            'total' =>
                SecureTransaction::query()

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->count(),


            'secured_amount' =>
                (float)
                SecureTransaction::query()

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->get([
                        'paid_amount',
                        'total_amount',
                    ])

                    ->sum(
                        function ($transaction) {

                            return (float)
                            (
                                $transaction->paid_amount
                                ?:
                                $transaction->total_amount
                            );
                        }
                    ),


            'disputed' =>
                SecureTransaction::query()

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->where(
                        'status',
                        SecureTransaction::STATUS_DISPUTED
                    )

                    ->count(),


            'completed' =>
                SecureTransaction::query()

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->where(
                        'status',
                        SecureTransaction::STATUS_COMPLETED
                    )

                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Paginate
        |--------------------------------------------------------------------------
        */

        $transactions =
            $query

                ->orderByDesc(
                    'paid_at'
                )

                ->orderByDesc(
                    'id'
                )

                ->paginate(
                    20
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.transactions.index',
            [

                'transactions' =>
                    $transactions,

                'stats' =>
                    $stats,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Details
    |--------------------------------------------------------------------------
    */

    public function show(
        SecureTransaction $secureTransaction
    ) {
        /*
        |--------------------------------------------------------------------------
        | SECURITY / BUSINESS RULE
        |--------------------------------------------------------------------------
        |
        | Even if admin manually enters a transaction URL, an unpaid transaction
        | must not be exposed through this monitoring module.
        |
        */

        abort_unless(
            $secureTransaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Load Everything Needed For Monitoring
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([

            'buyer',

            'seller',

            'product',

            'successfulPayment',

            'payments' =>
                function ($query) {

                    $query
                        ->latest(
                            'id'
                        );
                },

            'dispute.buyer',

            'dispute.seller',

        ]);


        return view(
            'admin.transactions.show',
            [

                'transaction' =>
                    $secureTransaction,

            ]
        );
    }
}