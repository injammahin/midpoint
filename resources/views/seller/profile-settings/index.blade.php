@extends('seller.layouts.app')


@section(
    'title',
    'Profile Settings'
)


@section('content')


<div class="ps-page">


    {{-- =========================================================
        FLASH
    ========================================================== --}}

    @if(session('success'))

        <div class="ps-alert success">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    @if(session('error'))

        <div class="ps-alert error">

            <i class="fa-solid fa-circle-exclamation"></i>

            {{ session('error') }}

        </div>

    @endif



    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="ps-page-header">

        <div>

            <h1>

                Profile settings

            </h1>


            <p>

                Manage your personal information, payout
                details, notification preferences and account security.

            </p>

        </div>


        @if($user->hasTwoFactorEnabled())

            <span class="ps-security-status enabled">

                <i class="fa-solid fa-shield-halved"></i>

                2FA enabled

            </span>

        @else

            <span class="ps-security-status">

                <i class="fa-solid fa-shield"></i>

                Standard security

            </span>

        @endif

    </div>



    <div class="ps-grid">


        {{-- =====================================================
            LEFT COLUMN
        ====================================================== --}}

        <div class="ps-column">


            {{-- =================================================
                PERSONAL DETAILS
            ================================================== --}}

            <section class="ps-card">

                <div class="ps-card-heading">

                    <div class="ps-card-icon">

                        <i class="fa-regular fa-user"></i>

                    </div>


                    <div>

                        <h2>

                            Personal details

                        </h2>


                        <p>

                            Update your public account information.

                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{
                        route(
                            'seller.profile-settings.personal'
                        )
                    }}"
                >

                    @csrf
                    @method('PUT')


                    <div class="ps-field">

                        <label>
                            Full name
                        </label>


                        <input
                            type="text"
                            name="name"
                            value="{{
                                old(
                                    'name',
                                    $user->name
                                )
                            }}"
                            required
                        >

                    </div>


                    <div class="ps-field">

                        <label>
                            Phone
                        </label>


                        <input
                            type="text"
                            name="phone"
                            value="{{
                                old(
                                    'phone',
                                    $user->phone
                                )
                            }}"
                            placeholder="e.g. 0803 552 1194"
                        >

                    </div>


                    <div class="ps-field">

                        <label>

                            Email

                            <span class="ps-readonly-label">

                                Read only

                            </span>

                        </label>


                        <div class="ps-readonly-input">

                            <i class="fa-solid fa-envelope"></i>


                            <input
                                type="email"
                                value="{{ $user->email }}"
                                readonly
                            >


                            @if($user->hasVerifiedEmail())

                                <span>

                                    <i class="fa-solid fa-circle-check"></i>

                                    Verified

                                </span>

                            @endif

                        </div>

                    </div>


                    <div class="ps-field">

                        <label>
                            City / location
                        </label>


                        <input
                            type="text"
                            name="city"
                            value="{{
                                old(
                                    'city',
                                    $user->city
                                )
                            }}"
                            placeholder="e.g. Ikeja, Lagos"
                        >

                    </div>


                    <button
                        type="submit"
                        class="ps-primary-button"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save changes

                    </button>

                </form>

            </section>



            {{-- =================================================
                PAYOUT BANK
            ================================================== --}}

            <section class="ps-card">

                <div class="ps-card-heading">

                    <div class="ps-card-icon">

                        <i class="fa-solid fa-building-columns"></i>

                    </div>


                    <div>

                        <h2>

                            Payout bank details

                        </h2>


                        <p>

                            These details will be used for seller payouts.

                        </p>

                    </div>


                    @if(
                        $user->bank_name
                        &&
                        $user->bank_account_number
                    )

                        <span class="ps-saved-badge">

                            <i class="fa-solid fa-check"></i>

                            Saved

                        </span>

                    @endif

                </div>


                <form
                    method="POST"
                    action="{{
                        route(
                            'seller.profile-settings.bank'
                        )
                    }}"
                >

                    @csrf
                    @method('PUT')


                    <div class="ps-field">

                        <label>
                            Bank
                        </label>


                        <input
                            type="text"
                            name="bank_name"
                            value="{{
                                old(
                                    'bank_name',
                                    $user->bank_name
                                )
                            }}"
                            list="midpointBanks"
                            placeholder="Choose or type bank name"
                            required
                        >


                        <datalist id="midpointBanks">

                            <option value="Access Bank">

                            <option value="First Bank">

                            <option value="GTBank">

                            <option value="UBA">

                            <option value="Zenith Bank">

                            <option value="Fidelity Bank">

                            <option value="Stanbic IBTC">

                            <option value="Sterling Bank">

                            <option value="Union Bank">

                            <option value="Wema Bank">

                        </datalist>

                    </div>


                    <div class="ps-field">

                        <label>
                            Account name
                        </label>


                        <input
                            type="text"
                            name="bank_account_name"
                            value="{{
                                old(
                                    'bank_account_name',
                                    $user->bank_account_name
                                )
                            }}"
                            placeholder="Name registered with the bank"
                            required
                        >

                    </div>


                    <div class="ps-field">

                        <label>
                            Account number
                        </label>


                        <input
                            type="text"
                            inputmode="numeric"
                            name="bank_account_number"
                            value="{{
                                old(
                                    'bank_account_number',
                                    $user->bank_account_number
                                )
                            }}"
                            placeholder="Enter bank account number"
                            required
                        >

                    </div>


                    <div class="ps-bank-note">

                        <i class="fa-solid fa-circle-info"></i>


                        <span>

                            Saving a bank account here does not itself
                            verify ownership. When a payment provider is
                            integrated, account-name resolution can be
                            connected to this section.

                        </span>

                    </div>


                    <button
                        type="submit"
                        class="ps-primary-button"
                    >

                        Save bank details

                    </button>

                </form>

            </section>

        </div>



        {{-- =====================================================
            RIGHT COLUMN
        ====================================================== --}}

        <div class="ps-column">


            {{-- =================================================
                NOTIFICATIONS
            ================================================== --}}

            <section class="ps-card">

                <div class="ps-card-heading">

                    <div class="ps-card-icon">

                        <i class="fa-regular fa-bell"></i>

                    </div>


                    <div>

                        <h2>

                            Notifications

                        </h2>


                        <p>

                            Choose the alerts you want to receive.

                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{
                        route(
                            'seller.profile-settings.notifications'
                        )
                    }}"
                >

                    @csrf
                    @method('PUT')


                    @php

                        $notificationOptions = [

                            [
                                'name' =>
                                    'payment_alerts',

                                'title' =>
                                    'Payment alerts',

                                'description' =>
                                    'Payments, escrow and payout activity.',

                                'enabled' =>
                                    old(
                                        'payment_alerts',
                                        $preferences
                                            ->payment_alerts
                                    ),
                            ],

                            [
                                'name' =>
                                    'dispatch_updates',

                                'title' =>
                                    'Dispatch updates',

                                'description' =>
                                    'Updates relating to order dispatch.',

                                'enabled' =>
                                    old(
                                        'dispatch_updates',
                                        $preferences
                                            ->dispatch_updates
                                    ),
                            ],

                            [
                                'name' =>
                                    'inspection_reminders',

                                'title' =>
                                    'Inspection reminders',

                                'description' =>
                                    'Buyer inspection and transaction deadlines.',

                                'enabled' =>
                                    old(
                                        'inspection_reminders',
                                        $preferences
                                            ->inspection_reminders
                                    ),
                            ],

                            [
                                'name' =>
                                    'whatsapp_notifications',

                                'title' =>
                                    'WhatsApp notifications',

                                'description' =>
                                    'Allow important alerts through WhatsApp when available.',

                                'enabled' =>
                                    old(
                                        'whatsapp_notifications',
                                        $preferences
                                            ->whatsapp_notifications
                                    ),
                            ],

                            [
                                'name' =>
                                    'marketing_emails',

                                'title' =>
                                    'Marketing emails',

                                'description' =>
                                    'Product news, promotions and MidPoint announcements.',

                                'enabled' =>
                                    old(
                                        'marketing_emails',
                                        $preferences
                                            ->marketing_emails
                                    ),
                            ],

                        ];

                    @endphp


                    <div class="ps-notification-list">

                        @foreach ($notificationOptions as $option)

                            <label class="ps-notification-row">

                                <div>

                                    <strong>

                                        {{ $option['title'] }}

                                    </strong>


                                    <span>

                                        {{ $option['description'] }}

                                    </span>

                                </div>


                                <span class="ps-switch">

                                    <input
                                        type="checkbox"
                                        name="{{ $option['name'] }}"
                                        value="1"
                                        @checked(
                                            $option['enabled']
                                        )
                                    >


                                    <span></span>

                                </span>

                            </label>

                        @endforeach

                    </div>


                    <button
                        type="submit"
                        class="ps-outline-save"
                    >

                        Save notification preferences

                    </button>

                </form>

            </section>



            {{-- =================================================
                SECURITY
            ================================================== --}}

            <section class="ps-card">

                <div class="ps-card-heading">

                    <div class="ps-card-icon">

                        <i class="fa-solid fa-lock"></i>

                    </div>


                    <div>

                        <h2>

                            Security

                        </h2>


                        <p>

                            Protect access to your MidPoint account.

                        </p>

                    </div>

                </div>



                {{-- Password --}}
                <button
                    type="button"
                    class="ps-security-action"
                    data-open-modal="changePasswordModal"
                >

                    <span class="ps-security-action-icon">

                        <i class="fa-solid fa-key"></i>

                    </span>


                    <span>

                        <strong>
                            Change password
                        </strong>


                        <small>
                            Update your account password
                        </small>

                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </button>



                {{-- 2FA --}}
                @if($user->hasTwoFactorEnabled())

                    <div class="ps-two-factor-enabled">

                        <div class="ps-two-factor-enabled-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>


                        <div>

                            <strong>

                                Two-factor authentication enabled

                            </strong>


                            <p>

                                Authenticator verification is required
                                whenever you log in.

                            </p>


                            <small>

                                Enabled

                                {{
                                    optional(
                                        $user->two_factor_confirmed_at
                                    )
                                    ->format(
                                        'd M Y, h:i A'
                                    )
                                }}

                            </small>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="ps-danger-outline"
                        data-open-modal="disableTwoFactorModal"
                    >

                        Disable two-factor authentication

                    </button>

                @else

                    <button
                        type="button"
                        class="ps-security-action"
                        data-open-modal="enableTwoFactorModal"
                    >

                        <span class="ps-security-action-icon green">

                            <i class="fa-solid fa-shield-halved"></i>

                        </span>


                        <span>

                            <strong>
                                Enable two-factor authentication
                            </strong>


                            <small>
                                Use an authenticator app when logging in
                            </small>

                        </span>


                        <i class="fa-solid fa-chevron-right"></i>

                    </button>

                @endif

            </section>



            {{-- =================================================
                SECURITY INFO
            ================================================== --}}

            <section class="ps-security-tip">

                <div>

                    <i class="fa-solid fa-shield"></i>

                </div>


                <p>

                    <strong>

                        Protect your seller account

                    </strong>


                    Seller accounts may contain payout and transaction
                    information. Enabling two-factor authentication adds
                    another verification step after your password.

                </p>

            </section>

        </div>

    </div>

</div>



{{-- =========================================================
    CHANGE PASSWORD MODAL
========================================================== --}}

<div
    id="changePasswordModal"
    class="ps-modal"
    hidden
>

    <div
        class="ps-modal-backdrop"
        data-close-modal
    ></div>


    <div class="ps-modal-dialog">

        <div class="ps-modal-header">

            <div>

                <span>
                    Account security
                </span>


                <h2>
                    Change password
                </h2>

            </div>


            <button
                type="button"
                data-close-modal
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <form
            method="POST"
            action="{{
                route(
                    'seller.profile-settings.password'
                )
            }}"
        >

            @csrf
            @method('PUT')


            <div class="ps-modal-body">

                <div class="ps-field">

                    <label>
                        Current password
                    </label>


                    <input
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >


                    @error('current_password')

                        <span class="ps-field-error">

                            {{ $message }}

                        </span>

                    @enderror

                </div>


                <div class="ps-field">

                    <label>
                        New password
                    </label>


                    <input
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        required
                    >


                    @error('password')

                        <span class="ps-field-error">

                            {{ $message }}

                        </span>

                    @enderror

                </div>


                <div class="ps-field">

                    <label>
                        Confirm new password
                    </label>


                    <input
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                    >

                </div>

            </div>


            <div class="ps-modal-footer">

                <button
                    type="button"
                    class="secondary"
                    data-close-modal
                >

                    Cancel

                </button>


                <button
                    type="submit"
                    class="primary"
                >

                    Change password

                </button>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
    ENABLE 2FA PASSWORD MODAL
========================================================== --}}

<div
    id="enableTwoFactorModal"
    class="ps-modal"
    hidden
>

    <div
        class="ps-modal-backdrop"
        data-close-modal
    ></div>


    <div class="ps-modal-dialog">

        <div class="ps-modal-header">

            <div>

                <span>
                    Extra security
                </span>


                <h2>
                    Enable two-factor authentication
                </h2>

            </div>


            <button
                type="button"
                data-close-modal
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <form
            method="POST"
            action="{{
                route(
                    'seller.profile-settings.two-factor.setup'
                )
            }}"
        >

            @csrf


            <div class="ps-modal-body">

                <div class="ps-modal-info">

                    Enter your current password first.

                    MidPoint will then generate an authenticator
                    setup QR code.

                </div>


                <div class="ps-field">

                    <label>
                        Current password
                    </label>


                    <input
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >


                    @error('two_factor_password')

                        <span class="ps-field-error">

                            {{ $message }}

                        </span>

                    @enderror

                </div>

            </div>


            <div class="ps-modal-footer">

                <button
                    type="button"
                    class="secondary"
                    data-close-modal
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="primary"
                >

                    Continue setup

                </button>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
    2FA QR SETUP MODAL
========================================================== --}}

<div
    id="twoFactorSetupModal"
    class="ps-modal"
    hidden
>

    <div
        class="ps-modal-backdrop"
    ></div>


    <div class="ps-modal-dialog ps-2fa-dialog">

        <div class="ps-modal-header">

            <div>

                <span>
                    Authenticator setup
                </span>


                <h2>
                    Connect your authenticator
                </h2>

            </div>

        </div>


        <form
            method="POST"
            action="{{
                route(
                    'seller.profile-settings.two-factor.confirm'
                )
            }}"
        >

            @csrf


            <div class="ps-modal-body">


                <ol class="ps-2fa-steps">

                    <li>

                        Open Google Authenticator,
                        Microsoft Authenticator, 1Password or
                        another TOTP authenticator.

                    </li>


                    <li>

                        Scan the QR code below.

                    </li>


                    <li>

                        Enter the generated 6-digit code.

                    </li>

                </ol>



                <div class="ps-qr-box">

                    <canvas id="twoFactorQrCanvas"></canvas>

                </div>



                @if(
                    session(
                        'two_factor_setup_secret'
                    )
                )

                    <div class="ps-manual-key">

                        <span>
                            Manual setup key
                        </span>


                        <code>

                            {{
                                session(
                                    'two_factor_setup_secret'
                                )
                            }}

                        </code>

                    </div>

                @endif



                <div class="ps-field">

                    <label>
                        6-digit authenticator code
                    </label>


                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="8"
                        placeholder="000000"
                        required
                    >


                    @error('two_factor_code')

                        <span class="ps-field-error">

                            {{ $message }}

                        </span>

                    @enderror

                </div>

            </div>


            <div class="ps-modal-footer">

                <button
                    type="submit"
                    class="primary"
                >

                    Confirm and enable

                </button>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
    RECOVERY CODES MODAL
========================================================== --}}

@if(
    session(
        'two_factor_recovery_codes_plain'
    )
)

    <div
        id="recoveryCodesModal"
        class="ps-modal"
        hidden
    >

        <div class="ps-modal-backdrop"></div>


        <div class="ps-modal-dialog">

            <div class="ps-modal-header">

                <div>

                    <span>
                        Important
                    </span>


                    <h2>
                        Save your recovery codes
                    </h2>

                </div>

            </div>


            <div class="ps-modal-body">

                <div class="ps-recovery-warning">

                    <i class="fa-solid fa-triangle-exclamation"></i>


                    Save these somewhere secure.

                    Each code works only once and can be used
                    if you lose access to your authenticator.

                </div>


                <div class="ps-recovery-codes">

                   @foreach (session('two_factor_recovery_codes_plain', []) as $recoveryCode)

                        <code>

                            {{ $recoveryCode }}

                        </code>

                    @endforeach

                </div>


                <button
                    type="button"
                    id="copyRecoveryCodes"
                    class="ps-copy-codes"
                >

                    <i class="fa-regular fa-copy"></i>

                    Copy recovery codes

                </button>

            </div>


            <div class="ps-modal-footer">

                <button
                    type="button"
                    class="primary"
                    data-close-modal
                >

                    I have saved them

                </button>

            </div>

        </div>

    </div>

@endif



{{-- =========================================================
    DISABLE 2FA
========================================================== --}}

<div
    id="disableTwoFactorModal"
    class="ps-modal"
    hidden
>

    <div
        class="ps-modal-backdrop"
        data-close-modal
    ></div>


    <div class="ps-modal-dialog">

        <div class="ps-modal-header">

            <div>

                <span>
                    Security
                </span>


                <h2>
                    Disable two-factor authentication?
                </h2>

            </div>


            <button
                type="button"
                data-close-modal
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        <form
            method="POST"
            action="{{
                route(
                    'seller.profile-settings.two-factor.disable'
                )
            }}"
        >

            @csrf
            @method('DELETE')


            <div class="ps-modal-body">

                <div class="ps-danger-note">

                    After disabling 2FA, your password alone
                    will be enough to access the account.

                </div>


                <div class="ps-field">

                    <label>
                        Current password
                    </label>


                    <input
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >


                    @error(
                        'disable_two_factor_password'
                    )

                        <span class="ps-field-error">

                            {{ $message }}

                        </span>

                    @enderror

                </div>

            </div>


            <div class="ps-modal-footer">

                <button
                    type="button"
                    class="secondary"
                    data-close-modal
                >

                    Keep 2FA

                </button>


                <button
                    type="submit"
                    class="danger"
                >

                    Disable 2FA

                </button>

            </div>

        </form>

    </div>

</div>



@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    .ps-page {
        width: 100%;
    }


    .ps-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 20px;

        margin-bottom: 18px;
    }


    .ps-page-header h1 {
        margin: 0;

        color: #101915;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 24px;
        font-weight: 800;
    }


    .ps-page-header p {
        margin:
            4px
            0
            0;

        color: #6F7C75;

        font-size: 10px;
    }


    .ps-security-status {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding:
            7px
            10px;

        border-radius: 999px;

        background: #F1F4F2;

        color: #657169;

        font-size: 11px;
        font-weight: 800;
    }


    .ps-security-status.enabled {
        background: #E8F7EF;

        color: #067647;
    }


    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .ps-alert {
        display: flex;
        align-items: center;

        gap: 7px;

        margin-bottom: 15px;
        padding: 11px 13px;

        border-radius: 10px;

        font-size: 12px;
    }


    .ps-alert.success {
        border: 1px solid #ABEFC6;

        background: #ECFDF3;

        color: #067647;
    }


    .ps-alert.error {
        border: 1px solid #FECDD3;

        background: #FFF1F2;

        color: #B42318;
    }


    /*
    |--------------------------------------------------------------------------
    | Grid
    |--------------------------------------------------------------------------
    */

    .ps-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 1fr)
            minmax(0, 1fr);

        align-items: start;

        gap: 18px;
    }


    .ps-column {
        display: flex;
        flex-direction: column;

        gap: 18px;
    }


    /*
    |--------------------------------------------------------------------------
    | Cards
    |--------------------------------------------------------------------------
    */

    .ps-card {
        padding: 21px;

        border: 1px solid #DCE5E0;
        border-radius: 17px;

        background: #FFFFFF;

        box-shadow:
            0 10px 30px -25px
            rgba(11,61,46,.3);
    }


    .ps-card-heading {
        display: flex;
        align-items: center;

        gap: 10px;

        margin-bottom: 18px;
    }


    .ps-card-icon {
        width: 38px;
        height: 38px;

        flex: 0 0 38px;

        display: grid;
        place-items: center;

        border-radius: 11px;

        background: #E8F7EF;

        color: #087443;

        font-size: 12px;
    }


    .ps-card-heading h2 {
        margin: 0;

        color: #101915;

        font-size: 13px;
        font-weight: 800;
    }


    .ps-card-heading p {
        margin:
            2px
            0
            0;

        color: #748079;

        font-size: 11px;
    }


    .ps-saved-badge {
        margin-left: auto;

        padding:
            5px
            8px;

        border-radius: 999px;

        background: #ECFDF3;

        color: #067647;

        font-size: 10px;
        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .ps-field {
        margin-bottom: 14px;
    }


    .ps-field label {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 6px;

        color: #24322B;

        font-size: 12px;
        font-weight: 700;
    }


    .ps-field input {
        width: 100%;
        height: 43px;

        padding:
            0
            12px;

        border: 1px solid #DCE5E0;
        border-radius: 10px;

        background: #FFFFFF;

        color: #101915;

        font-size: 10px;

        outline: none;
    }


    .ps-field input:focus {
        border-color: #12B76A;

        box-shadow:
            0 0 0 3px
            rgba(18,183,106,.08);
    }


    .ps-readonly-label {
        color: #89958F;

        font-size: 10px;
        font-weight: 600;
    }


    .ps-readonly-input {
        position: relative;
    }


    .ps-readonly-input > i {
        position: absolute;

        left: 12px;
        top: 50%;

        color: #8B9791;

        font-size: 12px;

        transform:
            translateY(-50%);
    }


    .ps-readonly-input input {
        padding:
            0
            85px
            0
            32px;

        background: #F7F9F8;

        color: #66736C;
    }


    .ps-readonly-input span {
        position: absolute;

        right: 9px;
        top: 50%;

        display: inline-flex;
        align-items: center;

        gap: 3px;

        color: #087443;

        font-size: 10px;
        font-weight: 700;

        transform:
            translateY(-50%);
    }


    .ps-field-error {
        display: block;

        margin-top: 5px;

        color: #D92D20;

        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .ps-primary-button {
        min-height: 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        padding:
            0
            15px;

        border: 0;
        border-radius: 9px;

        background: #0B3D2E;

        color: #FFFFFF;

        font-size: 12px;
        font-weight: 800;

        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | Bank Note
    |--------------------------------------------------------------------------
    */

    .ps-bank-note {
        display: flex;
        align-items: flex-start;

        gap: 7px;

        margin-bottom: 14px;
        padding: 10px;

        border-radius: 9px;

        background: #F7F9F8;

        color: #68756E;

        font-size: 11px;
        line-height: 1.5;
    }


    .ps-bank-note i {
        margin-top: 2px;

        color: #12B76A;
    }


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    .ps-notification-list {
        border-top: 1px solid #EDF1EF;
    }


    .ps-notification-row {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        padding:
            13px
            0;

        border-bottom: 1px solid #EDF1EF;

        cursor: pointer;
    }


    .ps-notification-row strong {
        display: block;

        color: #26342D;

        font-size: 12px;
    }


    .ps-notification-row span span {
        display: block;

        margin-top: 2px;

        color: #7A8780;

        font-size: 10px;
        line-height: 1.4;
    }


    .ps-switch {
        position: relative;

        width: 38px;
        height: 22px;

        flex: 0 0 38px;
    }


    .ps-switch input {
        position: absolute;

        opacity: 0;
    }


    .ps-switch > span {
        position: absolute;

        inset: 0;

        border-radius: 999px;

        background: #DCE3DF;

        transition: .18s;
    }


    .ps-switch > span::after {
        content: '';

        position: absolute;

        left: 3px;
        top: 3px;

        width: 16px;
        height: 16px;

        border-radius: 50%;

        background: #FFFFFF;

        box-shadow:
            0 1px 4px
            rgba(0,0,0,.15);

        transition: .18s;
    }


    .ps-switch input:checked + span {
        background: #12B76A;
    }


    .ps-switch input:checked + span::after {
        transform:
            translateX(
                16px
            );
    }


    .ps-outline-save {
        width: 100%;
        min-height: 39px;

        margin-top: 14px;

        border: 1px solid #DCE5E0;
        border-radius: 9px;

        background: #FFFFFF;

        color: #0B3D2E;

        font-size: 12px;
        font-weight: 800;

        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    .ps-security-action {
        width: 100%;

        display: flex;
        align-items: center;

        gap: 10px;

        padding:
            12px
            0;

        border: 0;
        border-bottom: 1px solid #EDF1EF;

        background: transparent;

        color: #26342D;

        text-align: left;

        cursor: pointer;
    }


    .ps-security-action:last-child {
        border-bottom: 0;
    }


    .ps-security-action-icon {
        width: 35px;
        height: 35px;

        flex: 0 0 35px;

        display: grid;
        place-items: center;

        border-radius: 10px;

        background: #F0ECFF;

        color: #6941C6;
    }


    .ps-security-action-icon.green {
        background: #E8F7EF;

        color: #087443;
    }


    .ps-security-action > span:nth-child(2) {
        min-width: 0;

        flex: 1;
    }


    .ps-security-action strong {
        display: block;

        font-size: 12px;
    }


    .ps-security-action small {
        display: block;

        margin-top: 2px;

        color: #7C8982;

        font-size: 10px;
    }


    .ps-security-action > i {
        color: #9BA59F;

        font-size: 11px;
    }


    .ps-two-factor-enabled {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        padding: 13px;

        border: 1px solid #ABEFC6;
        border-radius: 11px;

        background: #ECFDF3;
    }


    .ps-two-factor-enabled-icon {
        width: 36px;
        height: 36px;

        flex: 0 0 36px;

        display: grid;
        place-items: center;

        border-radius: 10px;

        background: #D1FADF;

        color: #067647;
    }


    .ps-two-factor-enabled strong {
        color: #05603A;

        font-size: 12px;
    }


    .ps-two-factor-enabled p {
        margin:
            3px
            0;

        color: #317556;

        font-size: 11px;
        line-height: 1.5;
    }


    .ps-two-factor-enabled small {
        color: #559176;

        font-size: 10px;
    }


    .ps-danger-outline {
        width: 100%;
        min-height: 38px;

        margin-top: 10px;

        border: 1px solid #FECDD3;
        border-radius: 9px;

        background: #FFFFFF;

        color: #D92D20;

        font-size: 11px;
        font-weight: 800;

        cursor: pointer;
    }


    .ps-security-tip {
        display: flex;

        gap: 10px;

        padding: 15px;

        border: 1px solid #DCE5E0;
        border-radius: 13px;

        background: #F9FBFA;
    }


    .ps-security-tip > div {
        width: 34px;
        height: 34px;

        flex: 0 0 34px;

        display: grid;
        place-items: center;

        border-radius: 9px;

        background: #E8F7EF;

        color: #087443;
    }


    .ps-security-tip p {
        margin: 0;

        color: #66736C;

        font-size: 11px;
        line-height: 1.6;
    }


    .ps-security-tip strong {
        display: block;

        margin-bottom: 3px;

        color: #26342D;

        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    .ps-modal[hidden] {
        display: none !important;
    }


    .ps-modal {
        position: fixed;

        inset: 0;

        z-index: 99999;

        display: grid;
        place-items: center;

        padding: 16px;
    }


    .ps-modal-backdrop {
        position: absolute;

        inset: 0;

        background: rgba(11,28,20,.58);

        backdrop-filter:
            blur(4px);
    }


    .ps-modal-dialog {
        position: relative;

        z-index: 1;

        width: min(
            460px,
            100%
        );

        max-height:
            calc(
                100vh - 30px
            );

        overflow-y: auto;

        border: 1px solid #DCE5E0;
        border-radius: 18px;

        background: #FFFFFF;

        box-shadow:
            0 30px 90px
            rgba(0,0,0,.25);
    }


    .ps-2fa-dialog {
        width: min(
            520px,
            100%
        );
    }


    .ps-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        padding: 18px 20px;

        border-bottom: 1px solid #E8ECEA;
    }


    .ps-modal-header span {
        display: block;

        margin-bottom: 2px;

        color: #12B76A;

        font-size: 10px;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .1em;
    }


    .ps-modal-header h2 {
        margin: 0;

        font-size: 16px;
        font-weight: 800;
    }


    .ps-modal-header > button {
        width: 32px;
        height: 32px;

        display: grid;
        place-items: center;

        border: 1px solid #DFE6E2;
        border-radius: 8px;

        background: #FFFFFF;

        cursor: pointer;
    }


    .ps-modal-body {
        padding: 20px;
    }


    .ps-modal-footer {
        display: flex;
        justify-content: flex-end;

        gap: 8px;

        padding:
            14px
            20px;

        border-top: 1px solid #E8ECEA;
    }


    .ps-modal-footer button {
        min-height: 38px;

        padding:
            0
            13px;

        border-radius: 9px;

        font-size: 11px;
        font-weight: 800;

        cursor: pointer;
    }


    .ps-modal-footer .secondary {
        border: 1px solid #DCE5E0;

        background: #FFFFFF;

        color: #3E4B44;
    }


    .ps-modal-footer .primary {
        border: 0;

        background: #0B3D2E;

        color: #FFFFFF;
    }


    .ps-modal-footer .danger {
        border: 0;

        background: #D92D20;

        color: #FFFFFF;
    }


    .ps-modal-info,
    .ps-danger-note {
        margin-bottom: 14px;
        padding: 11px;

        border-radius: 9px;

        font-size: 11px;
        line-height: 1.6;
    }


    .ps-modal-info {
        background: #F0F9F4;

        color: #34765A;
    }


    .ps-danger-note {
        background: #FFF1F2;

        color: #B42318;
    }


    /*
    |--------------------------------------------------------------------------
    | 2FA
    |--------------------------------------------------------------------------
    */

    .ps-2fa-steps {
        margin:
            0
            0
            16px;

        padding-left: 20px;

        color: #66736C;

        font-size: 11px;
        line-height: 1.7;
    }


    .ps-qr-box {
        width: 210px;
        height: 210px;

        display: grid;
        place-items: center;

        margin:
            0
            auto
            15px;

        padding: 10px;

        border: 1px solid #DCE5E0;
        border-radius: 14px;

        background: #FFFFFF;
    }


    .ps-qr-box canvas {
        max-width: 100%;
        height: auto !important;
    }


    .ps-manual-key {
        margin-bottom: 15px;
        padding: 11px;

        border-radius: 9px;

        background: #F5F7F6;

        text-align: center;
    }


    .ps-manual-key span {
        display: block;

        margin-bottom: 5px;

        color: #76827C;

        font-size: 10px;
    }


    .ps-manual-key code {
        word-break: break-all;

        color: #0B3D2E;

        font-size: 10px;
        font-weight: 800;

        letter-spacing: .05em;
    }


    /*
    |--------------------------------------------------------------------------
    | Recovery Codes
    |--------------------------------------------------------------------------
    */

    .ps-recovery-warning {
        display: flex;
        align-items: flex-start;

        gap: 8px;

        margin-bottom: 14px;
        padding: 11px;

        border-radius: 9px;

        background: #FFF7E8;

        color: #9C5B08;

        font-size: 11px;
        line-height: 1.5;
    }


    .ps-recovery-codes {
        display: grid;

        grid-template-columns:
            1fr
            1fr;

        gap: 7px;
    }


    .ps-recovery-codes code {
        padding:
            9px
            10px;

        border: 1px solid #DCE5E0;
        border-radius: 8px;

        background: #F7F9F8;

        color: #17251F;

        font-size: 10px;
        font-weight: 800;

        text-align: center;
    }


    .ps-copy-codes {
        width: 100%;
        min-height: 38px;

        margin-top: 12px;

        border: 1px solid #DCE5E0;
        border-radius: 8px;

        background: #FFFFFF;

        color: #0B3D2E;

        font-size: 11px;
        font-weight: 800;

        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 850px) {

        .ps-grid {
            grid-template-columns:
                1fr;
        }

    }


    @media(max-width: 600px) {

        .ps-page-header {
            flex-direction: column;
        }


        .ps-card {
            padding: 16px;
        }


        .ps-recovery-codes {
            grid-template-columns:
                1fr;
        }

    }

</style>

@endpush



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Modals
        |--------------------------------------------------------------------------
        */

        function openModal(
            modalId
        ) {

            const modal =
                document.getElementById(
                    modalId
                );


            if (!modal) {
                return;
            }


            modal.hidden =
                false;


            document.body.style.overflow =
                'hidden';
        }


        function closeModal(
            modal
        ) {

            if (!modal) {
                return;
            }


            modal.hidden =
                true;


            document.body.style.overflow =
                '';
        }


        /*
        |--------------------------------------------------------------------------
        | Open Buttons
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-open-modal]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            openModal(
                                button.dataset
                                    .openModal
                            );
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Close Buttons
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-close-modal]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            closeModal(
                                button.closest(
                                    '.ps-modal'
                                )
                            );
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Automatically Reopen Password Errors
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'open_password_modal'
            )
        )

            openModal(
                'changePasswordModal'
            );

        @endif


        /*
        |--------------------------------------------------------------------------
        | Automatically Reopen 2FA Password Error
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'open_enable_2fa_modal'
            )
        )

            openModal(
                'enableTwoFactorModal'
            );

        @endif


        /*
        |--------------------------------------------------------------------------
        | Automatically Open Setup
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'open_two_factor_setup'
            )
        )

            openModal(
                'twoFactorSetupModal'
            );

        @endif


        /*
        |--------------------------------------------------------------------------
        | Disable Error
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'open_disable_2fa_modal'
            )
        )

            openModal(
                'disableTwoFactorModal'
            );

        @endif


        /*
        |--------------------------------------------------------------------------
        | Recovery Codes
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'open_recovery_codes_modal'
            )
        )

            openModal(
                'recoveryCodesModal'
            );

        @endif


        /*
        |--------------------------------------------------------------------------
        | QR Code
        |--------------------------------------------------------------------------
        */

        @if(
            session(
                'two_factor_setup_uri'
            )
        )

            const qrCanvas =
                document.getElementById(
                    'twoFactorQrCanvas'
                );


            const otpUri =
                @json(
                    session(
                        'two_factor_setup_uri'
                    )
                );


            if (
                qrCanvas
                &&
                window.MidPointQRCode
            ) {

                window
                    .MidPointQRCode
                    .toCanvas(

                        qrCanvas,

                        otpUri,

                        {
                            width:
                                185,

                            margin:
                                1,

                            errorCorrectionLevel:
                                'M',
                        }

                    )

                    .catch(
                        function (
                            error
                        ) {

                            console.error(
                                'Unable to generate 2FA QR code:',
                                error
                            );
                        }
                    );
            }

        @endif


        /*
        |--------------------------------------------------------------------------
        | Copy Recovery Codes
        |--------------------------------------------------------------------------
        */

        const copyButton =
            document.getElementById(
                'copyRecoveryCodes'
            );


        if (copyButton) {

            copyButton.addEventListener(
                'click',
                async function () {

                    const codes =
                        Array.from(
                            document.querySelectorAll(
                                '.ps-recovery-codes code'
                            )
                        )

                        .map(
                            function (
                                element
                            ) {

                                return element
                                    .textContent
                                    .trim();
                            }
                        )

                        .join(
                            '\n'
                        );


                    try {

                        await navigator
                            .clipboard
                            .writeText(
                                codes
                            );


                        copyButton.innerHTML =
                            '<i class="fa-solid fa-check"></i> Copied';

                    } catch (
                        error
                    ) {

                        console.error(
                            error
                        );
                    }
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Escape
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key !==
                    'Escape'
                ) {
                    return;
                }


                const openModalElement =
                    Array
                        .from(
                            document.querySelectorAll(
                                '.ps-modal'
                            )
                        )

                        .find(
                            function (
                                modal
                            ) {

                                return !modal.hidden;
                            }
                        );


                /*
                |--------------------------------------------------------------------------
                | Do not accidentally close authenticator setup/recovery screen
                |--------------------------------------------------------------------------
                */

                if (
                    openModalElement
                    &&
                    ![
                        'twoFactorSetupModal',
                        'recoveryCodesModal',
                    ].includes(
                        openModalElement.id
                    )
                ) {

                    closeModal(
                        openModalElement
                    );
                }
            }
        );

    }
);

</script>

@endpush


@endsection