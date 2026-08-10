<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Models\UserNotificationPreference;

use App\Services\TwoFactorAuthenticationService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rules\Password;


class SellerProfileSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Settings Page
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $user =
            $request->user();


        $preferences =
            UserNotificationPreference::query()

                ->firstOrNew([

                    'user_id' =>
                        $user->id,

                ]);


        return view(
            'seller.profile-settings.index',
            compact(
                'user',
                'preferences'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Personal Details
    |--------------------------------------------------------------------------
    */

    public function updateProfile(
        Request $request
    ) {
        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Email Intentionally Not Accepted
        |--------------------------------------------------------------------------
        |
        | A malicious customer cannot send email in this endpoint and change it.
        |
        */

        $request
            ->user()
            ->update([

                'name' =>
                    trim(
                        $validated['name']
                    ),

                'phone' =>
                    !empty(
                        $validated['phone']
                    )

                        ? trim(
                            $validated['phone']
                        )

                        : null,

                'city' =>
                    !empty(
                        $validated['city']
                    )

                        ? trim(
                            $validated['city']
                        )

                        : null,

            ]);


        return back()
            ->with(
                'success',
                'Personal details updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Bank Details
    |--------------------------------------------------------------------------
    */

    public function updateBank(
        Request $request
    ) {
        $validated =
            $request->validate([

                'bank_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'bank_account_name' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'bank_account_number' => [
                    'required',
                    'string',
                    'regex:/^[0-9]{6,20}$/',
                ],

            ]);


        $request
            ->user()
            ->update([

                'bank_name' =>
                    trim(
                        $validated[
                            'bank_name'
                        ]
                    ),

                'bank_account_name' =>
                    trim(
                        $validated[
                            'bank_account_name'
                        ]
                    ),

                'bank_account_number' =>
                    trim(
                        $validated[
                            'bank_account_number'
                        ]
                    ),

            ]);


        return back()
            ->with(
                'success',
                'Bank details updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Preferences
    |--------------------------------------------------------------------------
    */

    public function updateNotifications(
        Request $request
    ) {
        UserNotificationPreference::query()

            ->updateOrCreate(

                [
                    'user_id' =>
                        $request
                            ->user()
                            ->id,
                ],

                [
                    'payment_alerts' =>
                        $request->boolean(
                            'payment_alerts'
                        ),

                    'dispatch_updates' =>
                        $request->boolean(
                            'dispatch_updates'
                        ),

                    'inspection_reminders' =>
                        $request->boolean(
                            'inspection_reminders'
                        ),

                    'whatsapp_notifications' =>
                        $request->boolean(
                            'whatsapp_notifications'
                        ),

                    'marketing_emails' =>
                        $request->boolean(
                            'marketing_emails'
                        ),
                ]

            );


        return back()
            ->with(
                'success',
                'Notification preferences updated.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    public function changePassword(
        Request $request
    ) {
        $validated =
            $request->validate([

                'current_password' => [
                    'required',
                    'string',
                ],

                'password' => [
                    'required',

                    'confirmed',

                    Password::min(8)
                        ->letters()
                        ->numbers(),
                ],

            ]);


        $user =
            $request->user();


        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $user->password
            )
        ) {

            return back()

                ->withErrors([

                    'current_password' =>
                        'Your current password is incorrect.',

                ])

                ->with(
                    'open_password_modal',
                    true
                );
        }


        if (
            Hash::check(
                $validated['password'],
                $user->password
            )
        ) {

            return back()

                ->withErrors([

                    'password' =>
                        'Your new password must be different from your current password.',

                ])

                ->with(
                    'open_password_modal',
                    true
                );
        }


        $user->forceFill([

            'password' =>
                Hash::make(
                    $validated[
                        'password'
                    ]
                ),

            'remember_token' =>
                null,

        ])->save();


        return back()
            ->with(
                'success',
                'Password changed successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Begin Two Factor Setup
    |--------------------------------------------------------------------------
    */

    public function setupTwoFactor(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated =
            $request->validate([

                'current_password' => [
                    'required',
                    'string',
                ],

            ]);


        $user =
            $request->user();


        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $user->password
            )
        ) {

            return back()

                ->withErrors([

                    'two_factor_password' =>
                        'Your current password is incorrect.',

                ])

                ->with(
                    'open_enable_2fa_modal',
                    true
                );
        }


        if (
            $user->hasTwoFactorEnabled()
        ) {

            return back()
                ->with(
                    'error',
                    'Two-factor authentication is already enabled.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Setup Secret
        |--------------------------------------------------------------------------
        */

        $secret =
            $twoFactor
                ->generateSecret();


        /*
        |--------------------------------------------------------------------------
        | Save Secret Encrypted
        |--------------------------------------------------------------------------
        |
        | It is NOT active until confirmation succeeds.
        |
        */

        $user->forceFill([

            'two_factor_secret' =>
                Crypt::encryptString(
                    $secret
                ),

            'two_factor_recovery_codes' =>
                null,

            'two_factor_confirmed_at' =>
                null,

        ])->saveQuietly();


        $uri =
            $twoFactor->otpAuthUri(
                $user,
                $secret
            );


        return back()

            ->with(
                'two_factor_setup_secret',
                $secret
            )

            ->with(
                'two_factor_setup_uri',
                $uri
            )

            ->with(
                'open_two_factor_setup',
                true
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Two Factor
    |--------------------------------------------------------------------------
    */

    public function confirmTwoFactor(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated =
            $request->validate([

                'code' => [
                    'required',
                    'string',
                    'max:12',
                ],

            ]);


        $user =
            $request->user();


        if (
            empty(
                $user->two_factor_secret
            )
        ) {

            return back()
                ->with(
                    'error',
                    'Start two-factor setup again.'
                );
        }


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


            return back()
                ->with(
                    'error',
                    'Unable to read the two-factor secret. Please restart setup.'
                );
        }


        if (
            !$twoFactor->verifyCode(
                $secret,
                $validated['code']
            )
        ) {

            return back()

                ->withErrors([

                    'two_factor_code' =>
                        'The authenticator code is invalid or expired.',

                ])

                ->with(
                    'two_factor_setup_secret',
                    $secret
                )

                ->with(
                    'two_factor_setup_uri',
                    $twoFactor->otpAuthUri(
                        $user,
                        $secret
                    )
                )

                ->with(
                    'open_two_factor_setup',
                    true
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Recovery Codes
        |--------------------------------------------------------------------------
        */

        $plainRecoveryCodes =
            $twoFactor
                ->generateRecoveryCodes();


        $hashedRecoveryCodes =
            $twoFactor
                ->hashRecoveryCodes(
                    $plainRecoveryCodes
                );


        /*
        |--------------------------------------------------------------------------
        | Activate
        |--------------------------------------------------------------------------
        */

        $user->forceFill([

            'two_factor_recovery_codes' =>
                json_encode(
                    $hashedRecoveryCodes
                ),

            'two_factor_confirmed_at' =>
                now(),

        ])->saveQuietly();


        return back()

            ->with(
                'success',
                'Two-factor authentication is now enabled.'
            )

            /*
            |--------------------------------------------------------------------------
            | Show Plain Codes Only Once
            |--------------------------------------------------------------------------
            */

            ->with(
                'two_factor_recovery_codes_plain',
                $plainRecoveryCodes
            )

            ->with(
                'open_recovery_codes_modal',
                true
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Disable Two Factor
    |--------------------------------------------------------------------------
    */

    public function disableTwoFactor(
        Request $request
    ) {
        $validated =
            $request->validate([

                'current_password' => [
                    'required',
                    'string',
                ],

            ]);


        $user =
            $request->user();


        if (
            !Hash::check(
                $validated[
                    'current_password'
                ],
                $user->password
            )
        ) {

            return back()

                ->withErrors([

                    'disable_two_factor_password' =>
                        'Your current password is incorrect.',

                ])

                ->with(
                    'open_disable_2fa_modal',
                    true
                );
        }


        $user->forceFill([

            'two_factor_secret' =>
                null,

            'two_factor_recovery_codes' =>
                null,

            'two_factor_confirmed_at' =>
                null,

        ])->saveQuietly();


        return back()
            ->with(
                'success',
                'Two-factor authentication has been disabled.'
            );
    }
}