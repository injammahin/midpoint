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
        | Admin
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === 'admin'
        ) {

            return redirect()
                ->route(
                    'admin.dashboard'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Verify First
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
        | Preferred View
        |--------------------------------------------------------------------------
        */

        if (
            $user->preferred_role
            === 'buyer'
        ) {

            return redirect()
                ->route(
                    'buyer.dashboard'
                );

        }


        return redirect()
            ->route(
                'seller.dashboard'
            );
    }
}