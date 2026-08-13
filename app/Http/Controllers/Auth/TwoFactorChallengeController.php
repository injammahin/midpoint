<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\User;

use App\Services\TwoFactorAuthenticationService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Crypt;


class TwoFactorChallengeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Challenge Page
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ) {
        $userId =
            $request
                ->session()
                ->get(
                    'two_factor.login_user_id'
                );


        if (!$userId) {

            return redirect()
                ->route(
                    'login'
                );
        }


        $user =
            User::find(
                $userId
            );


        if (
            !$user
            ||
            !$user->hasTwoFactorEnabled()
        ) {

            $this->clearPending(
                $request
            );


            return redirect()
                ->route(
                    'login'
                );
        }


        return view(
            'frontend.pages.two-factor-challenge',
            compact(
                'user'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Challenge
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated =
            $request->validate([

                'code' => [
                    'required',
                    'string',
                    'max:32',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Pending Login
        |--------------------------------------------------------------------------
        */

        $userId =
            $request
                ->session()
                ->get(
                    'two_factor.login_user_id'
                );


        $startedAt =
            $request
                ->session()
                ->get(
                    'two_factor.started_at'
                );


        if (
            !$userId
            ||
            !$startedAt
        ) {

            return redirect()
                ->route(
                    'login'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Expire Pending Challenge After 10 Minutes
        |--------------------------------------------------------------------------
        */

        if (
            now()->timestamp
            -
            (int)
            $startedAt
            >
            600
        ) {

            $this->clearPending(
                $request
            );


            return redirect()

                ->route(
                    'login'
                )

                ->withErrors([

                    'login' =>
                        'Your two-factor login session expired. Please log in again.',

                ]);
        }


        $user =
            User::find(
                $userId
            );


        if (
            !$user
            ||
            !$user->status
            ||
            !$user->hasTwoFactorEnabled()
        ) {

            $this->clearPending(
                $request
            );


            return redirect()
                ->route(
                    'login'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Decrypt TOTP Secret
        |--------------------------------------------------------------------------
        */

        try {

            $secret =
                Crypt::decryptString(
                    $user
                        ->two_factor_secret
                );

        } catch (\Throwable $exception) {

            report(
                $exception
            );


            $this->clearPending(
                $request
            );


            return redirect()

                ->route(
                    'login'
                )

                ->withErrors([

                    'login' =>
                        'Two-factor authentication could not be verified. Please contact support.',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Authenticator Code
        |--------------------------------------------------------------------------
        */

        $valid =
            $twoFactor
                ->verifyCode(
                    $secret,
                    $validated[
                        'code'
                    ]
                );


        /*
        |--------------------------------------------------------------------------
        | Recovery Code
        |--------------------------------------------------------------------------
        */

        if (!$valid) {

            $valid =
                $twoFactor
                    ->consumeRecoveryCode(
                        $user,
                        $validated[
                            'code'
                        ]
                    );
        }


        if (!$valid) {

            return back()
                ->withErrors([

                    'code' =>
                        'The authenticator or recovery code is invalid.',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Remember
        |--------------------------------------------------------------------------
        */

        $remember =
            (bool)
            $request
                ->session()
                ->get(
                    'two_factor.remember',
                    false
                );


        /*
        |--------------------------------------------------------------------------
        | Complete Authentication
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $remember
        );


        $this->clearPending(
            $request
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
        | Verification
        |--------------------------------------------------------------------------
        */

        if (
            !$user->canAccessAdminPanel()
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


    private function clearPending(
        Request $request
    ): void {

        $request
            ->session()
            ->forget([

                'two_factor.login_user_id',

                'two_factor.remember',

                'two_factor.started_at',

            ]);
    }
}