<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

    public function showLogin(
        Request $request
    ) {
        if (
            $request->filled(
                'redirect'
            )
        ) {

            $redirect =
                $request->input(
                    'redirect'
                );


            if (
                str_starts_with(
                    $redirect,
                    url('/')
                )
            ) {

                session([

                    'url.intended' =>
                        $redirect,

                ]);
            }
        }


        return view(
            'frontend.pages.login'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        Request $request
    ) {
        $validated =
            $request->validate([

                'login' => [
                    'required',
                    'string',
                ],

                'password' => [
                    'required',
                    'string',
                ],

                'remember' => [
                    'nullable',
                    'boolean',
                ],

            ]);


        $login =
            trim(
                $validated[
                    'login'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Email Or Phone
        |--------------------------------------------------------------------------
        */

$user = User::where(function ($query) use ($login) {

        $query->where(
            'email',
            strtolower($login)
        )

        ->orWhere(
            'phone',
            $login
        );

    })
    ->first();


        /*
        |--------------------------------------------------------------------------
        | Wrong Credentials
        |--------------------------------------------------------------------------
        */

        if (
            !$user
            ||
            !Hash::check(
                $validated[
                    'password'
                ],
                $user->password
            )
        ) {

            return back()

                ->withErrors([

                    'login' =>
                        'The provided credentials do not match our records.',

                ])

                ->onlyInput(
                    'login'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Disabled Account
        |--------------------------------------------------------------------------
        */

        if (
            !$user->status
        ) {

            return back()

                ->withErrors([

                    'login' =>
                        'Your account is currently inactive. Please contact MidPoint support.',

                ])

                ->onlyInput(
                    'login'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Two Factor Challenge
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT authenticate the session until the second factor succeeds.
        |
        */

        if (
            $user->hasTwoFactorEnabled()
        ) {

            $request
                ->session()
                ->put([

                    'two_factor.login_user_id' =>
                        $user->id,

                    'two_factor.remember' =>
                        $request->boolean(
                            'remember'
                        ),

                    'two_factor.started_at' =>
                        now()->timestamp,

                ]);


            return redirect()
                ->route(
                    'two-factor.challenge'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Normal Login Without 2FA
        |--------------------------------------------------------------------------
        */

        return $this->completeLogin(
            $request,
            $user,
            $request->boolean(
                'remember'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Login
    |--------------------------------------------------------------------------
    */

    private function completeLogin(
        Request $request,
        User $user,
        bool $remember
    ) {
        Auth::login(
            $user,
            $remember
        );


        /*
        |--------------------------------------------------------------------------
        | Session Fixation Protection
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();

        if (
            $user->canAccessAdminPanel()
        ) {

            $request
                ->session()
                ->put(
                    'admin_session_version',
                    (int) (
                        $user->session_version
                        ??
                        1
                    )
                );

        }
        /*
        |--------------------------------------------------------------------------
        | Login Tracking
        |--------------------------------------------------------------------------
        */

        $user->forceFill([

            'last_login_at' =>
                now(),

            'last_login_ip' =>
                $request->ip(),

        ])->saveQuietly();


        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */

        if (
            !$user->canAccessAdminPanel()
            &&
            !$user->hasVerifiedEmail()
        ){

            return redirect()
                ->route(
                    'verification.notice'
                );
        }


        return redirect()
            ->intended(
                route(
                    'dashboard'
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(
        Request $request
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
                'home'
            );
    }
}