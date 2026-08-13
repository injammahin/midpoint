<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

use Illuminate\Validation\Rule;


class AdminStaffController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Super Admin Security
    |--------------------------------------------------------------------------
    */

    private function superAdminOnly(
        Request $request
    ): void {

        abort_unless(
            $request->user()
            &&
            $request
                ->user()
                ->isAdmin(),

            403,

            'Only the main administrator can manage admin users and permissions.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Role Management Page
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $this->superAdminOnly(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Admin Users
        |--------------------------------------------------------------------------
        */

        $staff =
            User::query()

                ->where(
                    'role',
                    'admin_staff'
                )

                ->with(
                    'adminPermissions'
                )

                ->latest()

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Available Permission Groups
        |--------------------------------------------------------------------------
        */

        $permissionGroups =
            collect(
                config(
                    'admin_permissions.permissions',
                    []
                )
            )

                ->map(
                    function (
                        $meta,
                        $key
                    ) {

                        return array_merge(
                            $meta,
                            [
                                'key' =>
                                    $key,
                            ]
                        );
                    }
                )

                ->groupBy(
                    'group'
                );


        return view(
            'admin.staff.index',
            compact(
                'staff',
                'permissionGroups'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Admin User
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        $this->superAdminOnly(
            $request
        );


        $permissionKeys =
            array_keys(
                config(
                    'admin_permissions.permissions',
                    []
                )
            );


        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],


                /*
                |--------------------------------------------------------------------------
                | Allows:
                |
                | support
                | support_manager
                | support-manager
                | support.manager
                |--------------------------------------------------------------------------
                */

                'username' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[A-Za-z0-9._-]+$/',
                    'unique:users,username',
                ],


                'email' => [
                    'required',
                    'email',
                    'max:190',
                    'unique:users,email',
                ],


                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],


                'permissions' => [
                    'nullable',
                    'array',
                ],


                'permissions.*' => [
                    'string',
                    Rule::in(
                        $permissionKeys
                    ),
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Create User + Permissions Atomically
        |--------------------------------------------------------------------------
        */

        $user =
            DB::transaction(
                function () use (
                    $validated
                ) {

                    $user =
                        User::create([

                            'name' =>
                                trim(
                                    $validated['name']
                                ),

                            'username' =>
                                strtolower(
                                    trim(
                                        $validated['username']
                                    )
                                ),

                            'email' =>
                                strtolower(
                                    trim(
                                        $validated['email']
                                    )
                                ),

                            'password' =>
                                Hash::make(
                                    $validated['password']
                                ),

                            'role' =>
                                'admin_staff',

                            'preferred_role' =>
                                'seller',

                            'status' =>
                                true,

                            'session_version' =>
                                1,

                            /*
                            |--------------------------------------------------------------------------
                            | Staff accounts are created by trusted super admin,
                            | so email is treated as verified.
                            |--------------------------------------------------------------------------
                            */

                            'email_verified_at' =>
                                now(),

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Permissions
                    |--------------------------------------------------------------------------
                    */

                    $permissions =
                        collect(
                            $validated[
                                'permissions'
                            ]
                            ??
                            []
                        )
                            ->unique()
                            ->values();


                    if (
                        $permissions->isNotEmpty()
                    ) {

                        $user
                            ->adminPermissions()
                            ->createMany(

                                $permissions

                                    ->map(
                                        fn ($permission) => [

                                            'permission' =>
                                                $permission,

                                        ]
                                    )

                                    ->all()

                            );

                    }


                    return $user;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        Log::notice(
            'Admin staff account created.',
            [

                'admin_id' =>
                    $request->user()->id,

                'staff_id' =>
                    $user->id,

            ]
        );


        return back()
            ->with(
                'success',
                'Admin user created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Admin User
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        User $staff
    ) {

        $this->superAdminOnly(
            $request
        );


        $this->ensureStaff(
            $staff
        );


        $permissionKeys =
            array_keys(
                config(
                    'admin_permissions.permissions',
                    []
                )
            );


        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:120',
                ],


                'username' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^[A-Za-z0-9._-]+$/',

                    Rule::unique(
                        'users',
                        'username'
                    )
                        ->ignore(
                            $staff->id
                        ),
                ],


                'email' => [
                    'required',
                    'email',
                    'max:190',

                    Rule::unique(
                        'users',
                        'email'
                    )
                        ->ignore(
                            $staff->id
                        ),
                ],


                'permissions' => [
                    'nullable',
                    'array',
                ],


                'permissions.*' => [
                    'string',
                    Rule::in(
                        $permissionKeys
                    ),
                ],

            ]);


        DB::transaction(
            function () use (
                $staff,
                $validated
            ) {

                /*
                |--------------------------------------------------------------------------
                | Account
                |--------------------------------------------------------------------------
                */

                $staff->update([

                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'username' =>
                        strtolower(
                            trim(
                                $validated['username']
                            )
                        ),

                    'email' =>
                        strtolower(
                            trim(
                                $validated['email']
                            )
                        ),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Remove Old Permissions
                |--------------------------------------------------------------------------
                */

                $staff
                    ->adminPermissions()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Save New Permissions
                |--------------------------------------------------------------------------
                */

                $permissions =
                    collect(
                        $validated[
                            'permissions'
                        ]
                        ??
                        []
                    )
                        ->unique()
                        ->values();


                if (
                    $permissions->isNotEmpty()
                ) {

                    $staff
                        ->adminPermissions()
                        ->createMany(

                            $permissions

                                ->map(
                                    fn ($permission) => [

                                        'permission' =>
                                            $permission,

                                    ]
                                )

                                ->all()

                        );

                }

            }
        );


        Log::notice(
            'Admin staff permissions updated.',
            [

                'admin_id' =>
                    $request->user()->id,

                'staff_id' =>
                    $staff->id,

            ]
        );


        return back()
            ->with(
                'success',
                'Admin user and permissions updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Request $request,
        User $staff
    ) {

        $this->superAdminOnly(
            $request
        );


        $this->ensureStaff(
            $staff
        );


        $staff->status =
            !$staff->status;


        /*
        |--------------------------------------------------------------------------
        | Force Existing Session To Become Invalid
        |--------------------------------------------------------------------------
        */

        $staff->session_version =
            (
                (int) $staff->session_version
            )
            +
            1;


        /*
        |--------------------------------------------------------------------------
        | Invalidate Remember-Me
        |--------------------------------------------------------------------------
        */

        $staff->setRememberToken(
            Str::random(
                60
            )
        );


        $staff->save();


        /*
        |--------------------------------------------------------------------------
        | Remove Database Sessions
        |--------------------------------------------------------------------------
        */

        $this->deleteDatabaseSessions(
            $staff
        );


        Log::notice(
            'Admin staff status changed.',
            [

                'admin_id' =>
                    $request->user()->id,

                'staff_id' =>
                    $staff->id,

                'status' =>
                    $staff->status,

            ]
        );


        return back()
            ->with(
                'success',

                $staff->status

                    ? 'Admin user activated successfully.'

                    : 'Admin user deactivated and signed out successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Force Change Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        Request $request,
        User $staff
    ) {

        $this->superAdminOnly(
            $request
        );


        $this->ensureStaff(
            $staff
        );


        $validated =
            $request->validate([

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],


                'logout_user' => [
                    'nullable',
                    'boolean',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Change Password
        |--------------------------------------------------------------------------
        */

        $staff->password =
            Hash::make(
                $validated['password']
            );


        /*
        |--------------------------------------------------------------------------
        | Invalidate Remember Token
        |--------------------------------------------------------------------------
        */

        $staff->setRememberToken(
            Str::random(
                60
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Optional Forced Logout
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean(
                'logout_user'
            )
        ) {

            $staff->session_version =
                (
                    (int) $staff->session_version
                )
                +
                1;

        }


        $staff->save();


        /*
        |--------------------------------------------------------------------------
        | Delete DB Sessions If Applicable
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean(
                'logout_user'
            )
        ) {

            $this->deleteDatabaseSessions(
                $staff
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        Log::notice(
            'Admin reset staff password.',
            [

                'admin_id' =>
                    $request->user()->id,

                'staff_id' =>
                    $staff->id,

                'forced_logout' =>
                    $request->boolean(
                        'logout_user'
                    ),

            ]
        );


        return back()
            ->with(
                'success',

                $request->boolean(
                    'logout_user'
                )

                    ? 'Password changed and all active sessions were invalidated.'

                    : 'Password changed successfully. Existing sessions were kept active.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Login As Admin User
    |--------------------------------------------------------------------------
    */

    public function impersonate(
        Request $request,
        User $staff
    ) {

        /*
        |--------------------------------------------------------------------------
        | ONLY Main Admin
        |--------------------------------------------------------------------------
        */

        $this->superAdminOnly(
            $request
        );


        $this->ensureStaff(
            $staff
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_if(
            !$staff->status,
            422,
            'Inactive admin users cannot be accessed.'
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Nested Impersonation
        |--------------------------------------------------------------------------
        */

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
            $request->user()->id;


        /*
        |--------------------------------------------------------------------------
        | Login As Staff
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $staff
        );


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Remember Original Admin
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'impersonator_admin_id',
                $adminId
            );


        /*
        |--------------------------------------------------------------------------
        | Staff Session Version
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'admin_session_version',
                (int) (
                    $staff->session_version
                    ??
                    1
                )
            );


        Log::notice(
            'Super admin logged into admin staff account.',
            [

                'admin_id' =>
                    $adminId,

                'staff_id' =>
                    $staff->id,

            ]
        );


        return redirect()
            ->route(
                'dashboard'
            )
            ->with(
                'success',
                "You are now viewing {$staff->name}'s admin account."
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Admin User
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        User $staff
    ) {

        $this->superAdminOnly(
            $request
        );


        $this->ensureStaff(
            $staff
        );


        $staffId =
            $staff->id;


        /*
        |--------------------------------------------------------------------------
        | Remove Sessions First
        |--------------------------------------------------------------------------
        */

        $this->deleteDatabaseSessions(
            $staff
        );


        /*
        |--------------------------------------------------------------------------
        | Permissions Automatically Cascade
        |--------------------------------------------------------------------------
        */

        $staff->delete();


        Log::notice(
            'Admin staff deleted.',
            [

                'admin_id' =>
                    $request->user()->id,

                'staff_id' =>
                    $staffId,

            ]
        );


        return back()
            ->with(
                'success',
                'Admin user deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Target Is Restricted Admin User
    |--------------------------------------------------------------------------
    */

    private function ensureStaff(
        User $staff
    ): void {

        abort_unless(
            $staff->role
            ===
            'admin_staff',

            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Sessions
    |--------------------------------------------------------------------------
    */

    private function deleteDatabaseSessions(
        User $staff
    ): void {

        /*
        |--------------------------------------------------------------------------
        | session_version handles file/redis/etc. sessions.
        |
        | When database sessions are used, we additionally delete them immediately.
        |--------------------------------------------------------------------------
        */

        if (
            config(
                'session.driver'
            )
            ===
            'database'
        ) {

            DB::table(
                config(
                    'session.table',
                    'sessions'
                )
            )
                ->where(
                    'user_id',
                    $staff->id
                )
                ->delete();

        }
    }
}