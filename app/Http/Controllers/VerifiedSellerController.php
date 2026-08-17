<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerPackage;
use App\Models\SellerSubscription;
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
        | Packages
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


        $activeSubscription =
            null;

        $latestSubscription =
            null;

        $latestApplication =
            null;

        $approvedApplication =
            null;

        $pendingInvoice =
            null;

        $latestPaidInvoice =
            null;

        $canQuickRenew =
            false;


        /*
        |--------------------------------------------------------------------------
        | Logged In Seller
        |--------------------------------------------------------------------------
        */

        if (
            $request->user()
        ) {

            $user =
                $request->user();


            /*
             * This also expires an overdue package.
             */
            $activeSubscription =
                $subscriptions
                    ->activeForUser(
                        $user
                    );


            $latestSubscription =
                SellerSubscription::query()

                    ->with([
                        'invoice',
                        'package',
                    ])

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->latest(
                        'id'
                    )

                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Latest Application
            |--------------------------------------------------------------------------
            */

            $latestApplication =
                SellerApplication::query()

                    ->with([
                        'invoice',
                    ])

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->latest(
                        'id'
                    )

                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Previously Approved Application
            |--------------------------------------------------------------------------
            |
            | This is what allows quick renewal without another review.
            |
            */

            $approvedApplication =
                SellerApplication::query()

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->whereNotNull(
                        'approved_at'
                    )

                    ->whereIn(
                        'status',
                        [
                            SellerApplication::STATUS_ACTIVE,
                            SellerApplication::STATUS_EXPIRED,
                        ]
                    )

                    ->latest(
                        'approved_at'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Quick Renewal
            |--------------------------------------------------------------------------
            */

            $canQuickRenew =
                !$activeSubscription
                &&
                (bool)
                $approvedApplication
                &&
                (bool)
                $latestSubscription;


            /*
            |--------------------------------------------------------------------------
            | Any Pending Package Invoice
            |--------------------------------------------------------------------------
            |
            | We no longer use:
            |
            | $latestApplication->invoice
            |
            | because one approved application can now have many invoices.
            |
            */

            $pendingInvoice =
                SellerInvoice::query()

                    ->with([
                        'application',
                        'package',
                    ])

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->where(
                        'status',
                        'unpaid'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Latest Paid Invoice
            |--------------------------------------------------------------------------
            */

            $latestPaidInvoice =
                SellerInvoice::query()

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->where(
                        'status',
                        'paid'
                    )

                    ->latest(
                        'paid_at'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Package
        |--------------------------------------------------------------------------
        */

        $requestedPackage =
            $request->integer(
                'package'
            );


        $defaultPackage =
            $packages
                ->firstWhere(
                    'id',
                    $requestedPackage
                );


        /*
         * Current active package.
         */
        if (
            !$defaultPackage
            &&
            $activeSubscription
        ) {

            $defaultPackage =
                $packages
                    ->firstWhere(
                        'id',
                        $activeSubscription
                            ->seller_package_id
                    );
        }


        /*
         * Expired seller defaults to previous package.
         */
        if (
            !$defaultPackage
            &&
            !$activeSubscription
            &&
            $latestSubscription
        ) {

            $defaultPackage =
                $packages
                    ->firstWhere(
                        'id',
                        $latestSubscription
                            ->seller_package_id
                    );
        }


        $defaultPackage =
            $defaultPackage

            ??

            $packages
                ->firstWhere(
                    'is_popular',
                    true
                )

            ??

            $packages
                ->first();


        return view(
            'frontend.pages.verified-sellers',
            compact(
                'packages',
                'defaultPackage',
                'latestApplication',
                'approvedApplication',
                'activeSubscription',
                'latestSubscription',
                'pendingInvoice',
                'latestPaidInvoice',
                'canQuickRenew'
            )
        );
    }
}