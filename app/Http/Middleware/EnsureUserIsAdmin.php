<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;


class EnsureUserIsAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        if (
            !Auth::check()
        ) {

            return redirect()
                ->route(
                    'login'
                );

        }


        $user =
            Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Admin Account Required
        |--------------------------------------------------------------------------
        */

        if (
            !$user->canAccessAdminPanel()
            ||
            !$user->status
        ) {

            abort(
                403,
                'You do not have permission to access the administration panel.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | role = admin
        |
        | Full unrestricted access.
        |
        */

        if (
            $user->isAdmin()
        ) {

            return $next(
                $request
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Current Route
        |--------------------------------------------------------------------------
        */

        $routeName =
            optional(
                $request->route()
            )
                ->getName();


        /*
        |--------------------------------------------------------------------------
        | Always Allowed Internal Routes
        |--------------------------------------------------------------------------
        */

        foreach (
            (array) config(
                'admin_permissions.always_allowed_routes',
                []
            )
            as $pattern
        ) {

            if (
                $routeName
                &&
                Str::is(
                    $pattern,
                    $routeName
                )
            ) {

                return $next(
                    $request
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Determine Required Permission
        |--------------------------------------------------------------------------
        */

        $requiredPermission =
            null;


        foreach (
            (array) config(
                'admin_permissions.routes',
                []
            )
            as $pattern => $permission
        ) {

            if (
                $routeName
                &&
                Str::is(
                    $pattern,
                    $routeName
                )
            ) {

                $requiredPermission =
                    $permission;

                break;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Fail Closed
        |--------------------------------------------------------------------------
        |
        | If an admin route is not mapped, admin_staff cannot access it.
        |
        | This is safer than accidentally allowing a newly created module.
        |
        */

        if (
            !$requiredPermission
        ) {

            abort(
                403,
                'You do not have permission to access this administration module.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Permission Check
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasAdminPermission(
                $requiredPermission
            )
        ) {

            abort(
                403,
                'You do not have permission to access this administration module.'
            );

        }


        return $next(
            $request
        );
    }
}