<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerInvoice;
use App\Models\SellerPackage;
use App\Models\SellerSubscription;
use App\Services\SellerSubscriptionService;
use Illuminate\Http\Request;

class SellerSubscriptionController extends Controller
{
    public function index(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {

        /*
        |--------------------------------------------------------------------------
        | Synchronize Expiry
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptions();


        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query =
            SellerSubscription::query()

                ->with([
                    'user',
                    'package',
                    'application',
                    'invoice',
                    'renewedFrom',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request
                ->filled(
                    'search'
                )
        ) {

            $search =
                trim(
                    $request->search
                );


            $query->where(
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query

                        ->where(
                            'package_name',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'payment_reference',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhereHas(
                            'invoice',
                            function (
                                $query
                            ) use (
                                $search
                            ) {

                                $query->where(
                                    'invoice_number',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                );
                            }
                        )

                        ->orWhereHas(
                            'user',
                            function (
                                $query
                            ) use (
                                $search
                            ) {

                                $query
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
                        )

                        ->orWhereHas(
                            'application',
                            function (
                                $query
                            ) use (
                                $search
                            ) {

                                $query->where(
                                    'business_name',
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
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->status
            ===
            'active'
        ) {

            $query->active();
        }


        if (
            $request->status
            ===
            'expired'
        ) {

            $query->expired();
        }


        /*
        |--------------------------------------------------------------------------
        | Purchase Type
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $request
                    ->purchase_type,
                [
                    SellerInvoice::TYPE_INITIAL,
                    SellerInvoice::TYPE_RENEWAL,
                    SellerInvoice::TYPE_UPGRADE,
                    SellerInvoice::TYPE_DOWNGRADE,
                ],
                true
            )
        ) {

            $query->where(
                'purchase_type',
                $request
                    ->purchase_type
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Package
        |--------------------------------------------------------------------------
        */

        if (
            $request
                ->filled(
                    'package_id'
                )
        ) {

            $query->where(
                'seller_package_id',
                $request
                    ->package_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Expiring
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                (int)
                $request
                    ->expiring,
                [
                    7,
                    30,
                ],
                true
            )
        ) {

            $days =
                (int)
                $request
                    ->expiring;


            $query

                ->active()

                ->whereNotNull(
                    'expires_at'
                )

                ->where(
                    'expires_at',
                    '<=',
                    now()
                        ->addDays(
                            $days
                        )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $sellerSubscriptions =
            $query

                ->latest(
                    'id'
                )

                ->paginate(
                    25
                )

                ->withQueryString();


        $packages =
            SellerPackage::query()

                ->orderBy(
                    'sort_order'
                )

                ->orderBy(
                    'name'
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' =>
                SellerSubscription::query()
                    ->count(),

            'active' =>
                SellerSubscription::query()
                    ->active()
                    ->count(),

            'expired' =>
                SellerSubscription::query()
                    ->expired()
                    ->count(),

            'renewals' =>
                SellerSubscription::query()

                    ->where(
                        'purchase_type',
                        SellerInvoice::TYPE_RENEWAL
                    )

                    ->count(),

            'plan_changes' =>
                SellerSubscription::query()

                    ->whereIn(
                        'purchase_type',
                        [
                            SellerInvoice::TYPE_UPGRADE,
                            SellerInvoice::TYPE_DOWNGRADE,
                        ]
                    )

                    ->count(),

            'expiring_7' =>
                SellerSubscription::query()

                    ->active()

                    ->whereNotNull(
                        'expires_at'
                    )

                    ->where(
                        'expires_at',
                        '<=',
                        now()
                            ->addDays(
                                7
                            )
                    )

                    ->count(),
        ];


        return view(
            'admin.billing.subscriptions.index',
            compact(
                'sellerSubscriptions',
                'packages',
                'stats'
            )
        );
    }
}