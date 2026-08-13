<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $query =
            User::query()
                ->where(
                    'role',
                    'user'
                );


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {

            $search =
                trim(
                    $request->search
                );


            $query->where(
                function ($q) use (
                    $search
                ) {

                    $q
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->status ===
            'active'
        ) {

            $query->where(
                'status',
                true
            );

        } elseif (
            $request->status ===
            'inactive'
        ) {

            $query->where(
                'status',
                false
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Verification
        |--------------------------------------------------------------------------
        */

        if (
            $request->verification ===
            'verified'
        ) {

            $query->whereNotNull(
                'email_verified_at'
            );

        } elseif (
            $request->verification ===
            'unverified'
        ) {

            $query->whereNull(
                'email_verified_at'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Preferred View
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $request->preferred_role,
                [
                    'buyer',
                    'seller',
                ],
                true
            )
        ) {

            $query->where(
                'preferred_role',
                $request->preferred_role
            );

        }


        $users =
            $query
                ->latest()
                ->paginate(20)
                ->withQueryString();


        $stats = [

            'total' =>
                User::where(
                    'role',
                    'user'
                )->count(),

            'active' =>
                User::where(
                    'role',
                    'user'
                )
                    ->where(
                        'status',
                        true
                    )
                    ->count(),

            'inactive' =>
                User::where(
                    'role',
                    'user'
                )
                    ->where(
                        'status',
                        false
                    )
                    ->count(),

            'verified' =>
                User::where(
                    'role',
                    'user'
                )
                    ->whereNotNull(
                        'email_verified_at'
                    )
                    ->count(),

        ];


        return view(
            'admin.users.index',
            compact(
                'users',
                'stats'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Request $request,
        User $user
    ) {

        abort_if(
            $user->role !== 'user',
            403
        );


        $newStatus =
            !$user->status;


        $user->forceFill([

            'status' =>
                $newStatus,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Invalidate Remember-Me Token
        |--------------------------------------------------------------------------
        */

        if (!$newStatus) {

            $user->setRememberToken(
                Str::random(60)
            );

        }


        $user->save();


        /*
        |--------------------------------------------------------------------------
        | Remove Database Sessions When Possible
        |--------------------------------------------------------------------------
        */

        if (
            !$newStatus
            &&
            config('session.driver')
                === 'database'
        ) {

            DB::table(
                config(
                    'session.table',
                    'sessions'
                )
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->delete();

        }


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $user->notify(
            new AccountStatusChangedNotification(
                $newStatus
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        Log::notice(
            'Admin changed user status.',
            [

                'admin_id' =>
                    $request->user()->id,

                'user_id' =>
                    $user->id,

                'new_status' =>
                    $newStatus,

            ]
        );


        return back()
            ->with(
                'success',

                $newStatus
                    ? 'User activated successfully.'
                    : 'User deactivated successfully.'
            );
    }
}