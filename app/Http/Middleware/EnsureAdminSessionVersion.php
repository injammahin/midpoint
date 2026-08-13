<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class EnsureAdminSessionVersion
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Not An Admin User
        |--------------------------------------------------------------------------
        */

        if (
            !$user
            ||
            !$user->canAccessAdminPanel()
        ) {

            return $next(
                $request
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Session Version
        |--------------------------------------------------------------------------
        */

        $sessionKey =
            'admin_session_version';


        $storedVersion =
            $request
                ->session()
                ->get(
                    $sessionKey
                );


        $currentVersion =
            (int) (
                $user->session_version
                ??
                1
            );


        /*
        |--------------------------------------------------------------------------
        | First Admin Request
        |--------------------------------------------------------------------------
        */

        if (
            $storedVersion === null
        ) {

            $request
                ->session()
                ->put(
                    $sessionKey,
                    $currentVersion
                );


            return $next(
                $request
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Version Mismatch = Administrator Forced Logout
        |--------------------------------------------------------------------------
        */

        if (
            (int) $storedVersion
            !==
            $currentVersion
        ) {

            Auth::logout();


            $request
                ->session()
                ->invalidate();


            $request
                ->session()
                ->regenerateToken();


            return redirect()
                ->route(
                    'login'
                )
                ->withErrors([

                    'login' =>
                        'Your administrator session was ended. Please sign in again.',

                ]);

        }


        return $next(
            $request
        );
    }
}