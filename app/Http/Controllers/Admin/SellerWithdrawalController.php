<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawal;
use App\Services\SellerWithdrawalService;
use Illuminate\Http\Request;

class SellerWithdrawalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List All Withdrawals
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $this
            ->authorizeViewingWithdrawals(
                $request
            );


        $query =
            SellerWithdrawal::query()

                ->with([
                    'seller:id,name,email,phone',
                    'withdrawalAccount',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                (string)
                $request->get(
                    'search',
                    ''
                )
            );


        if (
            $search !== ''
        ) {

            $query->where(
                function (
                    $builder
                ) use (
                    $search
                ) {

                    $builder

                        ->where(
                            'reference',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'paystack_transfer_reference',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'paystack_transfer_code',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'bank_name',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'account_name',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhereHas(
                            'seller',
                            function (
                                $sellerQuery
                            ) use (
                                $search
                            ) {

                                $sellerQuery

                                    ->where(
                                        'name',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        $status =
            trim(
                (string)
                $request->get(
                    'status',
                    ''
                )
            );


        $allowedStatuses = [

            SellerWithdrawal::STATUS_PENDING,

            SellerWithdrawal::STATUS_PROCESSING,

            SellerWithdrawal::STATUS_OTP,

            SellerWithdrawal::STATUS_SUCCESSFUL,

            SellerWithdrawal::STATUS_FAILED,

            SellerWithdrawal::STATUS_REVERSED,

        ];


        if (
            $status !== ''
            &&
            in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            $query->where(
                'status',
                $status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        if (
            $request
                ->filled(
                    'date_from'
                )
        ) {

            $query->whereDate(
                'requested_at',
                '>=',
                $request
                    ->date_from
            );
        }


        if (
            $request
                ->filled(
                    'date_to'
                )
        ) {

            $query->whereDate(
                'requested_at',
                '<=',
                $request
                    ->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total_count' =>

                SellerWithdrawal::query()
                    ->count(),


            'total_requested' =>

                (float)
                SellerWithdrawal::query()
                    ->sum(
                        'amount'
                    ),


            'successful_count' =>

                SellerWithdrawal::query()

                    ->where(
                        'status',
                        SellerWithdrawal::STATUS_SUCCESSFUL
                    )

                    ->count(),


            'successful_amount' =>

                (float)
                SellerWithdrawal::query()

                    ->where(
                        'status',
                        SellerWithdrawal::STATUS_SUCCESSFUL
                    )

                    ->sum(
                        'amount'
                    ),


            'processing_count' =>

                SellerWithdrawal::query()

                    ->whereIn(
                        'status',
                        [

                            SellerWithdrawal::STATUS_PENDING,

                            SellerWithdrawal::STATUS_PROCESSING,

                            SellerWithdrawal::STATUS_OTP,

                        ]
                    )

                    ->count(),


            'failed_count' =>

                SellerWithdrawal::query()

                    ->whereIn(
                        'status',
                        [

                            SellerWithdrawal::STATUS_FAILED,

                            SellerWithdrawal::STATUS_REVERSED,

                        ]
                    )

                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $withdrawals =
            $query

                ->orderByDesc(
                    'id'
                )

                ->paginate(
                    20
                )

                ->withQueryString();


        return view(
            'admin.withdrawals.index',
            [

                'withdrawals' =>
                    $withdrawals,

                'stats' =>
                    $stats,

                'status' =>
                    $status,

                'search' =>
                    $search,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Withdrawal
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        SellerWithdrawal $withdrawal
    ) {

        $this
            ->authorizeViewingWithdrawals(
                $request
            );


        $withdrawal
            ->load([

                'seller',

                'wallet',

                'withdrawalAccount',

                'walletTransactions' =>
                    function (
                        $query
                    ) {

                        $query
                            ->latest(
                                'id'
                            );
                    },

            ]);


        $kyc =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $withdrawal
                        ->seller_id
                )

                ->first();


        return view(
            'admin.withdrawals.show',
            [

                'withdrawal' =>
                    $withdrawal,

                'kyc' =>
                    $kyc,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sync Paystack Status
    |--------------------------------------------------------------------------
    |
    | This does NOT approve the withdrawal.
    |
    | It only checks Paystack if admin wants to inspect a stuck transaction.
    |
    */

    public function sync(
        Request $request,
        SellerWithdrawal $withdrawal,
        SellerWithdrawalService $withdrawalService
    ) {

        $this
            ->authorizeViewingWithdrawals(
                $request
            );


        if (
            $withdrawal
                ->isFinal()
        ) {

            return back()
                ->with(
                    'success',
                    'This withdrawal already has a final status.'
                );
        }


        $fresh =
            $withdrawalService
                ->reconcile(
                    $withdrawal
                );


        return back()
            ->with(
                'success',

                'Paystack status synced. Current status: '

                .

                $fresh
                    ->status_label

                .

                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    */

    protected function authorizeViewingWithdrawals(
        Request $request
    ): void {

        $user =
            $request
                ->user();


        abort_unless(

            $user

            &&

            (
                $user
                    ->isAdmin()

                ||

                $user
                    ->hasAdminPermission(
                        'transactions.view'
                    )
            ),

            403
        );
    }
}