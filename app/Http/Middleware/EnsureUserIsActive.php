<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        $user =
            $request->user();


        if (
            $user
            &&
            !$user->status
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
                        'Your account has been deactivated. Please contact Midpoint support.',

                ]);

        }


        return $next(
            $request
        );
    }
}