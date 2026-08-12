<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;
use App\Models\TransactionDispute;

use Illuminate\Http\Request;

class AdminDisputeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dispute List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        |
        | We only show disputes related to real paid transactions.
        |
        */

        $query =
            TransactionDispute::query()

                ->whereHas(
                    'transaction',
                    function ($transactionQuery) {

                        $transactionQuery
                            ->where(
                                'payment_status',
                                SecureTransaction::PAYMENT_PAID
                            );
                    }
                )

                ->with([
                    'transaction',
                    'buyer',
                    'seller',
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
                            'description',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhereHas(
                            'transaction',
                            function ($transactionQuery) use ($search) {

                                $transactionQuery

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
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reason
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'reason'
            )
        ) {

            $query->where(
                'reason',
                $request->reason
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
                'opened_at',
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
                'opened_at',
                '<=',
                $request->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                TransactionDispute::query()
                    ->count(),

            'open' =>
                TransactionDispute::query()
                    ->where(
                        'status',
                        'open'
                    )
                    ->count(),

            'under_review' =>
                TransactionDispute::query()
                    ->where(
                        'status',
                        'under_review'
                    )
                    ->count(),

            'resolved' =>
                TransactionDispute::query()
                    ->where(
                        'status',
                        'resolved'
                    )
                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $disputes =
            $query

                ->orderByDesc(
                    'opened_at'
                )

                ->paginate(
                    20
                )

                ->withQueryString();


        return view(
            'admin.disputes.index',
            [

                'disputes' =>
                    $disputes,

                'stats' =>
                    $stats,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Dispute Request
    |--------------------------------------------------------------------------
    */

    public function show(
        TransactionDispute $dispute
    ) {
        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $dispute->load([

            'buyer',

            'seller',

            'transaction.successfulPayment',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Only Real Paid Transactions
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $dispute->transaction
            &&
            $dispute
                ->transaction
                ->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,
            404
        );


        return view(
            'admin.disputes.show',
            [

                'dispute' =>
                    $dispute,

                'transaction' =>
                    $dispute->transaction,

            ]
        );
    }
}