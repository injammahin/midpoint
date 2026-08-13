<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class DashboardRedirectController extends Controller
{
    public function __invoke(
        Request $request
    ) {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Administration
        |--------------------------------------------------------------------------
        */

        if (
            $user->canAccessAdminPanel()
        ) {

            /*
            |--------------------------------------------------------------------------
            | Super Admin
            |--------------------------------------------------------------------------
            */

            if (
                $user->isAdmin()
            ) {

                return redirect()
                    ->route(
                        'admin.dashboard'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Restricted Admin
            |--------------------------------------------------------------------------
            |
            | Find their first available page.
            |
            */

            $destinations = [

                'dashboard.view' =>
                    'admin.dashboard',

                'users.manage' =>
                    'admin.users.index',

                'seller_applications.manage' =>
                    'admin.website-settings.seller-applications.index',

                'billing.invoices.view' =>
                    'admin.billing.invoices.index',

                'billing.subscriptions.view' =>
                    'admin.billing.subscriptions.index',

                'transactions.view' =>
                    'admin.transactions.index',

                'disputes.manage' =>
                    'admin.disputes.index',

                'website.app_settings.manage' =>
                    'admin.website-settings.app-settings',

                'website.faqs.manage' =>
                    'admin.website-settings.faqs',

                'website.pricing.manage' =>
                    'admin.website-settings.pricing',

                'website.seller_packages.manage' =>
                    'admin.website-settings.become-seller',

                'support.contacts.manage' =>
                    'admin.support-inquiries.contacts',

                'support.messages.view' =>
                    'admin.support-inquiries.support-messages',

                'support.live.manage' =>
                    'admin.live-support.index',

                'support.live_settings.manage' =>
                    'admin.live-support.settings',

            ];


            foreach (
                $destinations
                as
                $permission => $route
            ) {

                if (
                    $user->hasAdminPermission(
                        $permission
                    )
                ) {

                    return redirect()
                        ->route(
                            $route
                        );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | No Permissions
            |--------------------------------------------------------------------------
            */

            abort(
                403,
                'No administration permissions have been assigned to this account.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normal User Verification
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasVerifiedEmail()
        ) {

            return redirect()
                ->route(
                    'verification.notice'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Buyer
        |--------------------------------------------------------------------------
        */

        if (
            $user->preferred_role
            ===
            'buyer'
        ) {

            return redirect()
                ->route(
                    'buyer.dashboard'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Seller
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'seller.dashboard'
            );
    }
}