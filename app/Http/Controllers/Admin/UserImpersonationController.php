<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserImpersonationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Start
    |--------------------------------------------------------------------------
    */

    public function start(
        Request $request,
        User $user
    ) {

        $admin =
            $request->user();


        abort_unless(
            $admin->role === 'admin',
            403
        );


        abort_if(
            $user->role === 'admin',
            403,
            'You cannot impersonate another administrator.'
        );


        abort_if(
            !$user->status,
            422,
            'Inactive users cannot be impersonated.'
        );


        abort_if(
            !$user->hasVerifiedEmail(),
            422,
            'Unverified users cannot be impersonated.'
        );


        abort_if(
            $request
                ->session()
                ->has(
                    'impersonator_admin_id'
                ),
            422,
            'An impersonation session is already active.'
        );


        $adminId =
            $admin->id;


        /*
        |--------------------------------------------------------------------------
        | Login As User
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user
        );


        $request
            ->session()
            ->regenerate();


        $request
            ->session()
            ->put(
                'impersonator_admin_id',
                $adminId
            );


        Log::notice(
            'Admin started user impersonation.',
            [

                'admin_id' =>
                    $adminId,

                'user_id' =>
                    $user->id,

            ]
        );


        return redirect()
            ->route(
                'dashboard'
            )
            ->with(
                'success',
                "You are now viewing {$user->name}'s account."
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Stop
    |--------------------------------------------------------------------------
    */

    public function stop(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Original Super Admin
        |--------------------------------------------------------------------------
        */

        $adminId =
            $request
                ->session()
                ->get(
                    'impersonator_admin_id'
                );


        abort_unless(
            $adminId,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Current Impersonated User
        |--------------------------------------------------------------------------
        */

        $impersonatedUserId =
            Auth::id();


        $wasAdminStaff =
            Auth::user()
                ?->isAdminStaff()
            ??
            false;


        /*
        |--------------------------------------------------------------------------
        | Retrieve Main Admin
        |--------------------------------------------------------------------------
        */

        $admin =
            User::query()

                ->where(
                    'id',
                    $adminId
                )

                ->where(
                    'role',
                    'admin'
                )

                ->where(
                    'status',
                    true
                )

                ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Restore Admin
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $admin
        );


        $request
            ->session()
            ->regenerate();


        $request
            ->session()
            ->forget(
                'impersonator_admin_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Restore Admin Session Version
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'admin_session_version',
                (int) (
                    $admin->session_version
                    ??
                    1
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        Log::notice(
            'Admin stopped user impersonation.',
            [

                'admin_id' =>
                    $admin->id,

                'user_id' =>
                    $impersonatedUserId,

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(

                $wasAdminStaff

                    ? 'admin.staff.index'

                    : 'admin.users.index'

            )
            ->with(
                'success',
                'Returned to administrator account.'
            );
    }
}