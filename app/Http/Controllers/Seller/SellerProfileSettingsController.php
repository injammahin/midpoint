<?php

namespace App\Http\Controllers\Seller;

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
    |
    | IMPORTANT:
    |
    | Buyer View and Seller View use the SAME users table record.
    |
    | Therefore if a seller changes:
    |
    | - name
    | - phone
    | - city
    |
    | the Buyer View automatically receives the same updated information.
    |
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
        | Email is read-only on this page.
        |
        | This prevents somebody from manually adding an email field to the
        | request and changing the verified login email.
        |
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->findOrFail(
                    $request->user()->id
                );


        $user->forceFill([
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
        ])->saveOrFail();


        /*
        |--------------------------------------------------------------------------
        | Refresh Authenticated User
        |--------------------------------------------------------------------------
        */

        $user->refresh();

        Auth::setUser(
            $user
        );


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
    |
    | Seller-only setting.
    |
    | Buyer Profile Settings must NOT expose this form.
    |
    |--------------------------------------------------------------------------
    */

    public function updateBank(
        Request $request
    ) {

        return redirect()
            ->route(
                'seller.wallet'
            )
            ->with(
                'error',
                'Payout bank details are now managed from Wallet & Withdrawals. Add and verify a bank account there.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Preferences
    |--------------------------------------------------------------------------
    |
    | Buyer and Seller share the same notification preference row.
    |
    | user_notification_preferences.user_id
    |
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
    |
    | IMPORTANT FIX:
    |
    | The old implementation called:
    |
    | $user->save();
    |
    | and immediately displayed:
    |
    | "Password changed successfully."
    |
    | This implementation does much more:
    |
    | 1. Validates all password fields.
    | 2. Reloads the actual user directly from the database.
    | 3. Locks the user row while updating.
    | 4. Verifies the current password.
    | 5. Makes sure new password differs from old password.
    | 6. Creates the new password hash.
    | 7. Uses saveOrFail().
    | 8. Reloads the password from the database.
    | 9. Verifies the NEW password against the persisted hash.
    | 10. Verifies the OLD password no longer matches.
    | 11. Only then displays success.
    |
    |--------------------------------------------------------------------------
    */

    public function changePassword(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Manual Validator
        |--------------------------------------------------------------------------
        |
        | We use the validator helper instead of $request->validate() because
        | password validation errors must reopen the password modal.
        |
        |--------------------------------------------------------------------------
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
                        'string',
                        'confirmed',

                        Password::min(8)
                            ->letters()
                            ->numbers(),
                    ],
                ],
                [
                    'current_password.required' =>
                        'Please enter your current password.',

                    'password.required' =>
                        'Please enter your new password.',

                    'password.confirmed' =>
                        'The new password confirmation does not match.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Validation Failed
        |--------------------------------------------------------------------------
        */

        if (
            $validator->fails()
        ) {
            return back()
                ->withErrors(
                    $validator
                )
                ->withInput()
                ->with(
                    'open_password_modal',
                    true
                );
        }


        $validated =
            $validator->validated();


        /*
        |--------------------------------------------------------------------------
        | Update Password Within Transaction
        |--------------------------------------------------------------------------
        */

        $result =
            DB::transaction(
                function () use (
                    $request,
                    $validated
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Fetch Fresh User From Database
                    |--------------------------------------------------------------------------
                    */

                    $user =
                        User::query()
                            ->whereKey(
                                $request
                                    ->user()
                                    ->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Verify Current Password
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !Hash::check(
                            $validated[
                                'current_password'
                            ],
                            $user->password
                        )
                    ) {
                        return [
                            'success' =>
                                false,

                            'field' =>
                                'current_password',

                            'message' =>
                                'Your current password is incorrect.',
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | New Password Cannot Equal Current Password
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Hash::check(
                            $validated[
                                'password'
                            ],
                            $user->password
                        )
                    ) {
                        return [
                            'success' =>
                                false,

                            'field' =>
                                'password',

                            'message' =>
                                'Your new password must be different from your current password.',
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Hash New Password
                    |--------------------------------------------------------------------------
                    */

                    $newPasswordHash =
                        Hash::make(
                            $validated[
                                'password'
                            ]
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Save New Password
                    |--------------------------------------------------------------------------
                    */

                    $user->forceFill([
                        'password' =>
                            $newPasswordHash,

                        /*
                        |--------------------------------------------------------------------------
                        | Invalidate Existing Remember-Me Token
                        |--------------------------------------------------------------------------
                        */

                        'remember_token' =>
                            null,
                    ])->saveOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Reload Actual Database Values
                    |--------------------------------------------------------------------------
                    */

                    $user->refresh();


                    /*
                    |--------------------------------------------------------------------------
                    | Verify The NEW Password Really Exists In Database
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !Hash::check(
                            $validated[
                                'password'
                            ],
                            $user->password
                        )
                    ) {
                        throw new \RuntimeException(
                            'The new password could not be verified after saving.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Verify OLD Password No Longer Matches
                    |--------------------------------------------------------------------------
                    */

                    if (
                        Hash::check(
                            $validated[
                                'current_password'
                            ],
                            $user->password
                        )
                    ) {
                        throw new \RuntimeException(
                            'The previous password still matches after password update.'
                        );
                    }


                    return [
                        'success' =>
                            true,

                        'user' =>
                            $user,
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | User-Side Password Error
        |--------------------------------------------------------------------------
        */

        if (
            !$result[
                'success'
            ]
        ) {
            return back()
                ->withErrors([
                    $result[
                        'field'
                    ] =>
                        $result[
                            'message'
                        ],
                ])
                ->with(
                    'open_password_modal',
                    true
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Replace Stale Authenticated User Model
        |--------------------------------------------------------------------------
        */

        Auth::setUser(
            $result[
                'user'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Regenerate Session ID
        |--------------------------------------------------------------------------
        |
        | User remains logged into the current browser.
        |
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Clear Password Confirmation Timestamp
        |--------------------------------------------------------------------------
        |
        | If Laravel password-confirm middleware is added later, the previous
        | password confirmation should not remain trusted after a password
        | change.
        |
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                'auth.password_confirmed_at'
            );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        |
        | At this point:
        |
        | - new password has been persisted
        | - new password was verified against database value
        | - previous password no longer matches
        |
        |--------------------------------------------------------------------------
        */

        return back()
            ->with(
                'success',
                'Password changed successfully. Your new password is now active.'
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
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validator =
            validator(
                $request->all(),
                [
                    'current_password' => [
                        'required',
                        'string',
                    ],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Reopen Modal On Validation Failure
        |--------------------------------------------------------------------------
        */

        if (
            $validator->fails()
        ) {
            return back()
                ->withErrors([
                    'two_factor_password' =>
                        $validator
                            ->errors()
                            ->first(
                                'current_password'
                            ),
                ])
                ->with(
                    'open_enable_2fa_modal',
                    true
                );
        }


        $validated =
            $validator->validated();


        /*
        |--------------------------------------------------------------------------
        | Fresh User
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->findOrFail(
                    $request->user()->id
                );


        /*
        |--------------------------------------------------------------------------
        | Verify Current Password
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Already Enabled?
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
        | It is NOT considered active until confirmation succeeds.
        |
        |--------------------------------------------------------------------------
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
        ])->saveOrFail();


        /*
        |--------------------------------------------------------------------------
        | Refresh Authenticated User
        |--------------------------------------------------------------------------
        */

        $user->refresh();

        Auth::setUser(
            $user
        );


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
    | Confirm Two Factor
    |--------------------------------------------------------------------------
    */

    public function confirmTwoFactor(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validator =
            validator(
                $request->all(),
                [
                    'code' => [
                        'required',
                        'string',
                        'max:12',
                    ],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Validation Failed
        |--------------------------------------------------------------------------
        */

        if (
            $validator->fails()
        ) {
            return back()
                ->withErrors([
                    'two_factor_code' =>
                        $validator
                            ->errors()
                            ->first(
                                'code'
                            ),
                ])
                ->with(
                    'open_two_factor_setup',
                    true
                );
        }


        $validated =
            $validator->validated();


        /*
        |--------------------------------------------------------------------------
        | Fresh User
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->findOrFail(
                    $request->user()->id
                );


        /*
        |--------------------------------------------------------------------------
        | Secret Must Exist
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
                    $user
                        ->two_factor_secret
                );

        } catch (
            \Throwable $exception
        ) {

            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Broken Setup
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
                $validated[
                    'code'
                ]
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
        | Generate Recovery Codes
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
        ])->saveOrFail();


        /*
        |--------------------------------------------------------------------------
        | Refresh User
        |--------------------------------------------------------------------------
        */

        $user->refresh();

        Auth::setUser(
            $user
        );


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
    | Disable Two Factor
    |--------------------------------------------------------------------------
    */

    public function disableTwoFactor(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator =
            validator(
                $request->all(),
                [
                    'current_password' => [
                        'required',
                        'string',
                    ],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Validation Failure
        |--------------------------------------------------------------------------
        */

        if (
            $validator->fails()
        ) {
            return back()
                ->withErrors([
                    'disable_two_factor_password' =>
                        $validator
                            ->errors()
                            ->first(
                                'current_password'
                            ),
                ])
                ->with(
                    'open_disable_2fa_modal',
                    true
                );
        }


        $validated =
            $validator->validated();


        /*
        |--------------------------------------------------------------------------
        | Fresh User
        |--------------------------------------------------------------------------
        */

        $user =
            User::query()
                ->findOrFail(
                    $request->user()->id
                );


        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Disable
        |--------------------------------------------------------------------------
        */

        $user->forceFill([
            'two_factor_secret' =>
                null,

            'two_factor_recovery_codes' =>
                null,

            'two_factor_confirmed_at' =>
                null,
        ])->saveOrFail();


        /*
        |--------------------------------------------------------------------------
        | Refresh User
        |--------------------------------------------------------------------------
        */

        $user->refresh();

        Auth::setUser(
            $user
        );


        return back()
            ->with(
                'success',
                'Two-factor authentication has been disabled.'
            );
    }
}