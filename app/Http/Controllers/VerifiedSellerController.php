<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerPackage;
use App\Services\SellerSubscriptionService;
use Illuminate\Http\Request;

class VerifiedSellerController extends Controller
{
    public function index(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        /*
        |--------------------------------------------------------------------------
        | Active Seller Packages
        |--------------------------------------------------------------------------
        */

        $packages =
            SellerPackage::query()

                ->where(
                    'is_active',
                    true
                )

                ->orderBy(
                    'sort_order'
                )

                ->orderBy(
                    'id'
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Active User Subscription
        |--------------------------------------------------------------------------
        */

        $activeSubscription =
            null;


        if (
            $request->user()
        ) {

            /*
            |--------------------------------------------------------------------------
            | activeForUser()
            |--------------------------------------------------------------------------
            |
            | This also checks whether the package has expired.
            |
            */

            $activeSubscription =
                $subscriptions
                    ->activeForUser(
                        $request->user()
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Package From URL
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | /verified-sellers?package=2
        |
        */

        $requestedPackage =
            $request->integer(
                'package'
            );


        $defaultPackage =
            $packages->firstWhere(
                'id',
                $requestedPackage
            );


        /*
        |--------------------------------------------------------------------------
        | If Seller Already Has Active Package
        |--------------------------------------------------------------------------
        |
        | Make that package selected by default.
        |
        */

        if (
            !$defaultPackage
            &&
            $activeSubscription
        ) {

            $defaultPackage =
                $packages->firstWhere(
                    'id',
                    $activeSubscription
                        ->seller_package_id
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Otherwise Popular Package
        |--------------------------------------------------------------------------
        */

        $defaultPackage =
            $defaultPackage

            ??
            $packages->firstWhere(
                'is_popular',
                true
            )

            ??
            $packages->first();


        /*
        |--------------------------------------------------------------------------
        | Application
        |--------------------------------------------------------------------------
        */

        $latestApplication =
            null;


        /*
        |--------------------------------------------------------------------------
        | Pending Invoice
        |--------------------------------------------------------------------------
        */

        $pendingInvoice =
            null;


        /*
        |--------------------------------------------------------------------------
        | Logged In User State
        |--------------------------------------------------------------------------
        */

        if (
            $request->user()
        ) {

            $latestApplication =
                SellerApplication::query()

                    ->with([
                        'invoice',
                    ])

                    ->where(
                        'user_id',
                        $request
                            ->user()
                            ->id
                    )

                    ->latest('id')

                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Pending Invoice
            |--------------------------------------------------------------------------
            */

            if (
                $latestApplication
                &&
                $latestApplication->invoice
                &&
                $latestApplication
                    ->invoice
                    ->status
                ===
                'unpaid'
            ) {

                $pendingInvoice =
                    $latestApplication
                        ->invoice;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'frontend.pages.verified-sellers',
            compact(
                'packages',
                'defaultPackage',
                'latestApplication',
                'pendingInvoice',
                'activeSubscription'
            )
        );
    }
}