<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class BuyerProfileSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Profile Settings Page
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        $preferences =
            UserNotificationPreference::query()
                ->firstOrNew([
                    'user_id' => $user->id,
                ]);

        return view(
            'buyer.profile-settings.index',
            compact(
                'user',
                'preferences'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Personal Details
    |--------------------------------------------------------------------------
    |
    | Buyer and seller share the SAME users table row.
    |
    */

    public function updateProfile(Request $request)
    {
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
        | Email is intentionally NOT editable
        |--------------------------------------------------------------------------
        |
        | Otherwise somebody could modify the verified login email using
        | a manually crafted request.
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
                    !empty($validated['phone'])
                        ? trim($validated['phone'])
                        : null,

                'city' =>
                    !empty($validated['city'])
                        ? trim($validated['city'])
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
    | Notification Preferences
    |--------------------------------------------------------------------------
    |
    | These settings belong to the account.
    |
    | Therefore:
    |
    | Buyer changes notification settings
    |               ↓
    | user_notification_preferences.user_id
    |               ↓
    | Seller sees the same settings
    |
    */

    public function updateNotifications(Request $request)
    {
        UserNotificationPreference::query()
            ->updateOrCreate(
                [
                    'user_id' =>
                        $request->user()->id,
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
    |
    | IMPORTANT:
    |
    | There is only ONE password because Buyer View and Seller View belong
    | to the same Midpoint account.
    |
    */

    public function changePassword(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Use manual validator
        |--------------------------------------------------------------------------
        |
        | This lets us reopen the password modal when validation fails.
        |
        */

        $validator =
            validator(
                $request->all(),
                [
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
                ]
            );


        if ($validator->fails()) {
            return back()
                ->withErrors(
                    $validator
                )
                ->with(
                    'open_password_modal',
                    true
                );
        }


        $validated =
            $validator->validated();


        /*
        |--------------------------------------------------------------------------
        | Read the actual fresh user record
        |--------------------------------------------------------------------------
        |
        | Do not rely only on the already-authenticated model.
        |
        */

        $result =
            DB::transaction(
                function () use (
                    $request,
                    $validated
                ) {

                    $user =
                        User::query()
                            ->whereKey(
                                $request->user()->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Verify existing password
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !Hash::check(
                            $validated['current_password'],
                            $user->password
                        )
                    ) {
                        return [
                            'ok' => false,

                            'field' =>
                                'current_password',

                            'message' =>
                                'Your current password is incorrect.',
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | New password must be different
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Hash::check(
                            $validated['password'],
                            $user->password
                        )
                    ) {
                        return [
                            'ok' => false,

                            'field' =>
                                'password',

                            'message' =>
                                'Your new password must be different from your current password.',
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Save New Password
                    |--------------------------------------------------------------------------
                    */

                    $newPasswordHash =
                        Hash::make(
                            $validated['password']
                        );


                    $user->forceFill([
                        'password' =>
                            $newPasswordHash,

                        /*
                        |--------------------------------------------------------------------------
                        | Disable previous Remember Me tokens
                        |--------------------------------------------------------------------------
                        */

                        'remember_token' =>
                            null,
                    ])->saveOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Reload DB value
                    |--------------------------------------------------------------------------
                    */

                    $user->refresh();


                    /*
                    |--------------------------------------------------------------------------
                    | Verify that password really changed
                    |--------------------------------------------------------------------------
                    |
                    | We do NOT display success unless the actual DB password hash
                    | matches the submitted NEW password.
                    |
                    */

                    if (
                        !Hash::check(
                            $validated['password'],
                            $user->password
                        )
                    ) {
                        throw new \RuntimeException(
                            'The new password could not be verified after saving.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Verify OLD password no longer matches
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Hash::check(
                            $validated['current_password'],
                            $user->password
                        )
                    ) {
                        throw new \RuntimeException(
                            'The old password still matches after password update.'
                        );
                    }


                    return [
                        'ok' => true,
                        'user' => $user,
                    ];
                }
            );


        if (!$result['ok']) {
            return back()
                ->withErrors([
                    $result['field'] =>
                        $result['message'],
                ])
                ->with(
                    'open_password_modal',
                    true
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh authenticated user
        |--------------------------------------------------------------------------
        */

        Auth::setUser(
            $result['user']
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Current Session
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        return back()
            ->with(
                'success',
                'Password changed successfully. Use your new password the next time you sign in.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Start Two-Factor Authentication Setup
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


        /*
        |--------------------------------------------------------------------------
        | Check current password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $validated['current_password'],
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


        /*
        |--------------------------------------------------------------------------
        | Already enabled
        |--------------------------------------------------------------------------
        */

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
        | Generate Secret
        |--------------------------------------------------------------------------
        */

        $secret =
            $twoFactor
                ->generateSecret();


        /*
        |--------------------------------------------------------------------------
        | Store encrypted secret
        |--------------------------------------------------------------------------
        |
        | It remains inactive until authenticator verification succeeds.
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


        /*
        |--------------------------------------------------------------------------
        | Generate OTP URI
        |--------------------------------------------------------------------------
        */

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
    | Confirm Two-Factor Authentication
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


        /*
        |--------------------------------------------------------------------------
        | Secret must exist
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Decrypt Secret
        |--------------------------------------------------------------------------
        */

        try {

            $secret =
                Crypt::decryptString(
                    $user->two_factor_secret
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


        /*
        |--------------------------------------------------------------------------
        | Verify Authenticator Code
        |--------------------------------------------------------------------------
        */

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
        | Create Recovery Codes
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
        | Activate 2FA
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
    | Disable Two-Factor Authentication
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


        /*
        |--------------------------------------------------------------------------
        | Check Password
        |--------------------------------------------------------------------------
        */

        if (
            !Hash::check(
                $validated['current_password'],
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


        /*
        |--------------------------------------------------------------------------
        | Disable 2FA
        |--------------------------------------------------------------------------
        */

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