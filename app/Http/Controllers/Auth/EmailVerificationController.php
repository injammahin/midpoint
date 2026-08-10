<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verification Notice
    |--------------------------------------------------------------------------
    */

    public function notice(Request $request)
    {
        $user =
            $request->user();


        if (
            $user->hasVerifiedEmail()
        ) {

            return redirect()
                ->intended(
                    route(
                        'dashboard'
                    )
                );
        }


        $cooldown =
            (int) config(
                'verification.resend_seconds',
                60
            );


        $elapsed =
            $user->verification_sent_at
                ? $user
                    ->verification_sent_at
                    ->diffInSeconds(
                        now()
                    )
                : $cooldown;


        $secondsRemaining =
            max(
                0,
                $cooldown - $elapsed
            );


        return view(
            'frontend.pages.verify-email',
            compact(
                'user',
                'secondsRemaining'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify
    |--------------------------------------------------------------------------
    */

    public function verify(
        Request $request,
        $id,
        $hash,
        $token
    ) {

        $user =
            User::findOrFail(
                $id
            );


        /*
        |--------------------------------------------------------------------------
        | Confirm Email Hash
        |--------------------------------------------------------------------------
        */

        if (
            !hash_equals(
                (string) $hash,

                sha1(
                    $user
                        ->getEmailForVerification()
                )
            )
        ) {

            abort(
                403,
                'Invalid verification link.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Already Verified
        |--------------------------------------------------------------------------
        */

        if ($user->hasVerifiedEmail()) {

            if (
                Auth::check()
                &&
                Auth::id() === $user->id
            ) {

            return redirect()
                ->intended(
                    route(
                        'dashboard'
                    )
                );
            }


            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'success',
                    'Your email is already verified. Please log in.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Check Current Verification Token
        |--------------------------------------------------------------------------
        |
        | A newly-generated verification link rotates this token.
        | Therefore an older verification URL cannot be used.
        |
        */

        if (
            !$user->email_verification_token
            ||
            !hash_equals(

                $user->email_verification_token,

                hash(
                    'sha256',
                    $token
                )

            )
        ) {

            abort(
                403,
                'This verification link is no longer valid. Please request a new one.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Verified
        |--------------------------------------------------------------------------
        */

        $user
            ->markEmailAsVerified();


        /*
        |--------------------------------------------------------------------------
        | Kill Verification Token
        |--------------------------------------------------------------------------
        */

        $user->forceFill([

            'email_verification_token' =>
                null,

            'verification_sent_at' =>
                null,

        ])->saveQuietly();


        /*
        |--------------------------------------------------------------------------
        | Disabled Account
        |--------------------------------------------------------------------------
        */

        if (!$user->status) {

            Auth::logout();


            return redirect()
                ->route(
                    'login'
                )
                ->withErrors([
                    'login' =>
                        'Your account is currently inactive.',
                ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Same Browser / Existing Session
        |--------------------------------------------------------------------------
        */

        if (
            Auth::check()
            &&
            Auth::id() === $user->id
        ) {

        return redirect()
            ->intended(
                route(
                    'dashboard'
                )
            )
            ->with(
                'success',
                'Your email has been verified successfully.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Verification Opened In Another Browser
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'login'
            )
            ->with(
                'success',
                'Email verified successfully. You can now log in.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Resend Verification
    |--------------------------------------------------------------------------
    */

    public function resend(
        Request $request
    ) {

        $user =
            $request->user();


        if ($user->hasVerifiedEmail()) {

            return redirect()
                ->route(
                    'dashboard'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Cooldown
        |--------------------------------------------------------------------------
        */

        $cooldown =
            (int) config(
                'verification.resend_seconds',
                60
            );


        if ($user->verification_sent_at) {

            $availableAt =
                $user
                    ->verification_sent_at
                    ->copy()
                    ->addSeconds(
                        $cooldown
                    );


            if (
                now()->lt(
                    $availableAt
                )
            ) {

                $remaining =
                    now()
                        ->diffInSeconds(
                            $availableAt
                        );


                return back()
                    ->withErrors([

                        'resend' =>
                            "Please wait {$remaining} seconds before requesting another verification email.",

                    ]);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | New Link
        |--------------------------------------------------------------------------
        |
        | sendEmailVerificationNotification() rotates the token.
        | The previous link immediately stops working.
        |
        */

        $user
            ->sendEmailVerificationNotification();


        return back()
            ->with(
                'status',
                'verification-link-sent'
            );
    }
}