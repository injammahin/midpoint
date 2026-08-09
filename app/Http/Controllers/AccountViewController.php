<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountViewController extends Controller
{
    public function switch(
        Request $request,
        string $view
    ) {

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Only normal MidPoint users can use buyer / seller dashboards
        |--------------------------------------------------------------------------
        */

        if (
            !$user
            ||
            $user->role !== 'user'
        ) {

            abort(
                403,
                'This account cannot switch between buyer and seller dashboards.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate requested view
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $view,
                [
                    'seller',
                    'buyer',
                ],
                true
            )
        ) {

            abort(404);
        }


        /*
        |--------------------------------------------------------------------------
        | Store only in session
        |--------------------------------------------------------------------------
        |
        | preferred_role stays as the role chosen during registration.
        |
        | This session value only controls the current dashboard view.
        |
        */

        $request
            ->session()
            ->put(
                'account_view',
                $view
            );


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            $view . '.dashboard'
        );
    }
}