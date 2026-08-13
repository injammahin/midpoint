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


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Main Administrator
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
        | Administration Staff
        |--------------------------------------------------------------------------
        */

        if (
            $user->isAdminStaff()
        ) {

            return redirect()
                ->route(
                    'admin.staff-dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Normal Marketplace Users
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