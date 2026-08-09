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
    )
    {
        if (
            $request->filled(
                'redirect'
            )
        ) {

            $redirect =
                $request->input(
                    'redirect'
                );


            /*
            * Only accept local application URLs.
            */
            if (
                str_starts_with(
                    $redirect,
                    url('/')
                )
            ) {

                session(
                    [
                        'url.intended' =>
                            $redirect,
                    ]
                );

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
            $request->validate(
                [

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

                ]
            );


        $login =
            trim(
                $validated['login']
            );


        /*
        |--------------------------------------------------------------------------
        | Email or Phone
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->where(
                    'email',
                    strtolower(
                        $login
                    )
                )
                ->orWhere(
                    'phone',
                    $login
                )
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
                $validated['password'],
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
        | Disabled
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
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login(

            $user,

            $request->boolean(
                'remember'
            )

        );


        /*
        |--------------------------------------------------------------------------
        | Session Fixation Protection
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


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
        | Verify First
        |--------------------------------------------------------------------------
        */

        if (
            $user->role !== 'admin'
            &&
            !$user->hasVerifiedEmail()
        ) {

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