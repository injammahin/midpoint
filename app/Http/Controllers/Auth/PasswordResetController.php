<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Forgot Password Page
    |--------------------------------------------------------------------------
    */

    public function showForgotForm()
    {
        return view(
            'frontend.pages.forgot-password'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Password Reset Email
    |--------------------------------------------------------------------------
    */

    public function sendResetLink(
        Request $request
    ) {
        $validated =
            $request->validate(
                [
                    'email' => [
                        'required',
                        'string',
                        'email:rfc',
                        'max:255',
                    ],
                ],
                [
                    'email.required' =>
                        'Please enter your email address.',

                    'email.email' =>
                        'Please enter a valid email address.',
                ]
            );


        $email =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Account Enumeration Protection
        |--------------------------------------------------------------------------
        |
        | We intentionally return the same success message if the account
        | does not exist.
        |
        */

        $user =
            User::query()
                ->where(
                    'email',
                    $email
                )
                ->first();


        if (
            !$user
            ||
            !$user->isActive()
        ) {
            return back()
                ->withInput([
                    'email' =>
                        $email,
                ])
                ->with(
                    'status',
                    'If an active Midpoint account exists for this email, we have sent a password reset link.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Send Reset Email
        |--------------------------------------------------------------------------
        */

        try {

            $status =
                Password::broker()
                    ->sendResetLink([
                        'email' =>
                            $email,
                    ]);

        } catch (\Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Log actual mail error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Password reset email could not be sent.',
                [
                    'user_id' =>
                        $user->id,

                    'exception' =>
                        $exception->getMessage(),
                ]
            );


            return back()
                ->withInput([
                    'email' =>
                        $email,
                ])
                ->withErrors([
                    'email' =>
                        'We could not send the reset email right now. Please try again shortly.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Broker Throttling
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            Password::RESET_THROTTLED
        ) {
            return back()
                ->withInput([
                    'email' =>
                        $email,
                ])
                ->withErrors([
                    'email' =>
                        'A reset link was requested recently. Please wait a moment before requesting another one.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Generic Success
        |--------------------------------------------------------------------------
        */

        return back()
            ->withInput([
                'email' =>
                    $email,
            ])
            ->with(
                'status',
                'If an active Midpoint account exists for this email, we have sent a password reset link.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password Page
    |--------------------------------------------------------------------------
    */

    public function showResetForm(
        Request $request,
        string $token
    ) {
        return view(
            'frontend.pages.reset-password',
            [
                'token' =>
                    $token,

                'email' =>
                    $request->query(
                        'email',
                        ''
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function reset(
        Request $request
    ) {
        $validated =
            $request->validate(
                [
                    'token' => [
                        'required',
                        'string',
                    ],

                    'email' => [
                        'required',
                        'string',
                        'email:rfc',
                        'max:255',
                    ],

                    'password' => [
                        'required',
                        'string',
                        'confirmed',

                        PasswordRule::min(8)
                            ->letters()
                            ->numbers(),
                    ],
                ],
                [
                    'email.required' =>
                        'Your email address is required.',

                    'email.email' =>
                        'Please enter a valid email address.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.confirmed' =>
                        'The password confirmation does not match.',
                ]
            );


        $credentials = [

            'email' =>
                strtolower(
                    trim(
                        $validated['email']
                    )
                ),

            'password' =>
                $validated['password'],

            'password_confirmation' =>
                $request->input(
                    'password_confirmation'
                ),

            'token' =>
                $validated['token'],
        ];


        /*
        |--------------------------------------------------------------------------
        | Laravel Password Broker
        |--------------------------------------------------------------------------
        |
        | Laravel checks:
        |
        | - email
        | - token
        | - token expiration
        | - password reset record
        |
        */

        $status =
            Password::broker()
                ->reset(
                    $credentials,

                    function (
                        User $user,
                        string $password
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Save New Password
                        |--------------------------------------------------------------------------
                        */

                        $user->forceFill([

                            'password' =>
                                Hash::make(
                                    $password
                                ),


                            /*
                            |--------------------------------------------------------------------------
                            | Invalidate Remember Me
                            |--------------------------------------------------------------------------
                            */

                            'remember_token' =>
                                Str::random(
                                    60
                                ),

                        ])->saveOrFail();


                        /*
                        |--------------------------------------------------------------------------
                        | Password Reset Event
                        |--------------------------------------------------------------------------
                        */

                        event(
                            new PasswordReset(
                                $user
                            )
                        );
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        if (
            $status ===
            Password::PASSWORD_RESET
        ) {
            return redirect()
                ->route(
                    'login'
                )
                ->with(
                    'status',
                    'Your password has been reset successfully. You can now log in with your new password.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Invalid / Expired Token
        |--------------------------------------------------------------------------
        */

        return back()
            ->withInput(
                $request->only(
                    'email'
                )
            )
            ->withErrors([
                'email' =>
                    $this->resetErrorMessage(
                        $status
                    ),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Friendly Reset Errors
    |--------------------------------------------------------------------------
    */

    private function resetErrorMessage(
        string $status
    ): string {

        return match (
            $status
        ) {

            Password::INVALID_TOKEN =>
                'This password reset link is invalid or has expired. Please request a new one.',

            Password::INVALID_USER =>
                'We could not reset the password for this account.',

            Password::RESET_THROTTLED =>
                'Please wait a moment before trying again.',

            default =>
                'We could not reset your password. Please request a new reset link and try again.',
        };
    }
}