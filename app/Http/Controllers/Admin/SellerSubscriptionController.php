<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
        | Synchronize Expired Plans
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
                ]);


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
                    $request->search
                );


            $query->where(
                function ($query) use ($search) {

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
                            'user',
                            function ($query) use ($search) {

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
                            function ($query) use ($search) {

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
        | Package
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'package_id'
            )
        ) {

            $query->where(
                'seller_package_id',
                $request->package_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Expiring Soon
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                (int) $request->expiring,
                [
                    7,
                    30,
                ],
                true
            )
        ) {

            $days =
                (int)
                $request->expiring;


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

                ->paginate(25)

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */

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
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                SellerSubscription::count(),

            'active' =>
                SellerSubscription::query()
                    ->active()
                    ->count(),

            'expired' =>
                SellerSubscription::query()
                    ->expired()
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
                            ->addDays(7)
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