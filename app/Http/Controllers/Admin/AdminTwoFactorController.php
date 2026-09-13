<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminTwoFactorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Start Google Authenticator Setup
    |--------------------------------------------------------------------------
    */

    public function setup(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $admin = $this->admin($request);

        if (
            !Hash::check(
                $validated['current_password'],
                $admin->password
            )
        ) {
            return $this->backToSecurity()
                ->withErrors([
                    'admin_two_factor_password' =>
                        'Your current password is incorrect.',
                ]);
        }

        if ($admin->hasTwoFactorEnabled()) {
            return $this->backToSecurity()
                ->with(
                    'warning',
                    'Google Authenticator is already enabled for this administrator.'
                );
        }

        $secret = $twoFactor->generateSecret();

        /*
         * Store the secret encrypted. It is not active until a correct
         * six-digit authenticator code confirms setup.
         */
        $admin->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->saveQuietly();

        Log::notice('Administrator started two-factor setup.', [
            'admin_id' => $admin->id,
            'ip' => $request->ip(),
        ]);

        return $this->backToSecurity()
            ->with('two_factor_setup_secret', $secret)
            ->with(
                'two_factor_setup_uri',
                $twoFactor->otpAuthUri($admin, $secret)
            )
            ->with('open_admin_two_factor_setup', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm And Enable
    |--------------------------------------------------------------------------
    */

    public function confirm(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'regex:/^\d{6}$/',
            ],
        ]);

        $admin = $this->admin($request);

        if (
            empty($admin->two_factor_secret)
            || $admin->hasTwoFactorEnabled()
        ) {
            return $this->backToSecurity()
                ->with(
                    'warning',
                    $admin->hasTwoFactorEnabled()
                        ? 'Google Authenticator is already enabled.'
                        : 'Start Google Authenticator setup again.'
                );
        }

        try {
            $secret = Crypt::decryptString(
                $admin->two_factor_secret
            );
        } catch (Throwable $exception) {
            report($exception);

            $admin->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->saveQuietly();

            return $this->backToSecurity()
                ->withErrors([
                    'admin_two_factor_code' =>
                        'The setup secret could not be read. Start setup again.',
                ]);
        }

        if (
            !$twoFactor->verifyCode(
                $secret,
                $validated['code']
            )
        ) {
            return $this->backToSecurity()
                ->withErrors([
                    'admin_two_factor_code' =>
                        'The authenticator code is invalid or expired.',
                ])
                ->with('two_factor_setup_secret', $secret)
                ->with(
                    'two_factor_setup_uri',
                    $twoFactor->otpAuthUri($admin, $secret)
                )
                ->with('open_admin_two_factor_setup', true);
        }

        $plainRecoveryCodes =
            $twoFactor->generateRecoveryCodes();

        $admin->forceFill([
            'two_factor_recovery_codes' => json_encode(
                $twoFactor->hashRecoveryCodes(
                    $plainRecoveryCodes
                )
            ),
            'two_factor_confirmed_at' => now(),
            'session_version' => $this->nextSessionVersion($admin),
        ])->saveQuietly();

        $request->session()->regenerate();
        $this->keepCurrentAdminSession($request, $admin);

        Log::notice('Administrator enabled two-factor authentication.', [
            'admin_id' => $admin->id,
            'ip' => $request->ip(),
        ]);

        return $this->backToSecurity()
            ->with(
                'success',
                'Google Authenticator is now enabled. Other admin sessions were signed out.'
            )
            ->with(
                'two_factor_recovery_codes_plain',
                $plainRecoveryCodes
            )
            ->with('show_admin_recovery_codes', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Regenerate Recovery Codes
    |--------------------------------------------------------------------------
    */

    public function regenerateRecoveryCodes(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:32',
            ],
        ]);

        $admin = $this->admin($request);

        if (
            !$admin->hasTwoFactorEnabled()
            || !Hash::check(
                $validated['current_password'],
                $admin->password
            )
            || !$this->validSecondFactor(
                $admin,
                $validated['code'],
                $twoFactor
            )
        ) {
            return $this->backToSecurity()
                ->withErrors([
                    'admin_recovery_verification' =>
                        'Your password or authenticator/recovery code is invalid.',
                ]);
        }

        $plainRecoveryCodes =
            $twoFactor->generateRecoveryCodes();

        $admin->forceFill([
            'two_factor_recovery_codes' => json_encode(
                $twoFactor->hashRecoveryCodes(
                    $plainRecoveryCodes
                )
            ),
            'session_version' => $this->nextSessionVersion($admin),
        ])->saveQuietly();

        $request->session()->regenerate();
        $this->keepCurrentAdminSession($request, $admin);

        Log::notice('Administrator regenerated two-factor recovery codes.', [
            'admin_id' => $admin->id,
            'ip' => $request->ip(),
        ]);

        return $this->backToSecurity()
            ->with(
                'success',
                'New recovery codes were generated. All previous recovery codes are invalid.'
            )
            ->with(
                'two_factor_recovery_codes_plain',
                $plainRecoveryCodes
            )
            ->with('show_admin_recovery_codes', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Disable Two-Factor Authentication
    |--------------------------------------------------------------------------
    */

    public function disable(
        Request $request,
        TwoFactorAuthenticationService $twoFactor
    ) {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:32',
            ],
        ]);

        $admin = $this->admin($request);

        if (!$admin->hasTwoFactorEnabled()) {
            return $this->backToSecurity()
                ->with(
                    'warning',
                    'Google Authenticator is already disabled.'
                );
        }

        if (
            !Hash::check(
                $validated['current_password'],
                $admin->password
            )
            || !$this->validSecondFactor(
                $admin,
                $validated['code'],
                $twoFactor
            )
        ) {
            return $this->backToSecurity()
                ->withErrors([
                    'admin_disable_two_factor' =>
                        'Your password or authenticator/recovery code is invalid.',
                ]);
        }

        $admin->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'session_version' => $this->nextSessionVersion($admin),
        ])->saveQuietly();

        $request->session()->regenerate();
        $this->keepCurrentAdminSession($request, $admin);

        Log::warning('Administrator disabled two-factor authentication.', [
            'admin_id' => $admin->id,
            'ip' => $request->ip(),
        ]);

        return $this->backToSecurity()
            ->with(
                'success',
                'Google Authenticator has been disabled. Other admin sessions were signed out.'
            );
    }


    private function admin(Request $request): User
    {
        /** @var User $admin */
        $admin = $request->user();

        abort_unless(
            $admin && $admin->canAccessAdminPanel(),
            403
        );

        return $admin;
    }


    private function validSecondFactor(
        User $admin,
        string $code,
        TwoFactorAuthenticationService $twoFactor
    ): bool {
        try {
            $secret = Crypt::decryptString(
                $admin->two_factor_secret
            );
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        if ($twoFactor->verifyCode($secret, $code)) {
            return true;
        }

        return $twoFactor->consumeRecoveryCode(
            $admin,
            $code
        );
    }


    private function nextSessionVersion(User $admin): int
    {
        return max(
            1,
            (int) ($admin->session_version ?? 1)
        ) + 1;
    }


    private function keepCurrentAdminSession(
        Request $request,
        User $admin
    ): void {
        $request->session()->put(
            'admin_session_version',
            (int) $admin->session_version
        );
    }


    private function backToSecurity()
    {
        return redirect()->route(
            'admin.website-settings.app-settings',
            [
                'tab' => 'two-factor',
            ]
        );
    }
}
