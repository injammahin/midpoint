@extends('admin.layouts.app')

@section('title', 'App Settings')
@section('page-title', 'App Settings')


@push('styles')

    <style>
        /*
        |--------------------------------------------------------------------------
        | Wrapper
        |--------------------------------------------------------------------------
        */

        .app-settings-wrap {
            display: grid;
            gap: 18px;
        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .app-settings-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }


        .app-settings-head h2 {
            margin: 0;

            color:
                var(--admin-heading);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 24px;
        }


        .app-settings-head p {
            max-width: 760px;

            margin:
                6px 0 0;

            color:
                var(--admin-muted);

            font-size: 13px;
            line-height: 1.6;
        }


        .app-settings-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            min-height: 38px;

            padding:
                0 12px;

            border:
                1px solid var(--admin-border);

            border-radius: 10px;

            color:
                var(--admin-accent);

            background:
                var(--admin-accent-soft);

            font-size: 11px;
            font-weight: 800;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        .app-alert {
            display: flex;
            align-items: flex-start;

            gap: 10px;

            padding:
                13px 14px;

            border:
                1px solid var(--admin-border);

            border-radius: 11px;

            color:
                var(--admin-text);

            background:
                var(--admin-surface);

            font-size: 12px;

            line-height: 1.55;
        }


        .app-alert.success {
            border-color:
                var(--admin-accent);

            background:
                var(--admin-accent-soft);
        }


        .app-alert.warning {
            border-color:
                var(--admin-warning);
        }


        .app-alert.error {
            border-color:
                var(--admin-danger);
        }


        .app-alert ul {
            margin:
                6px 0 0 18px;

            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Tabs
        |--------------------------------------------------------------------------
        */

        .app-settings-tabs {
            display: flex;

            gap: 7px;

            overflow-x: auto;

            padding: 7px;

            border:
                1px solid var(--admin-border);

            border-radius: 13px;

            background:
                var(--admin-surface);

            box-shadow:
                var(--admin-shadow);
        }


        .app-settings-tab {
            flex: 0 0 auto;

            min-height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 7px;

            padding:
                0 13px;

            border:
                1px solid transparent;

            border-radius: 9px;

            color:
                var(--admin-muted);

            background:
                transparent;

            cursor: pointer;

            font: inherit;

            font-size: 11px;

            font-weight: 800;

            transition:
                .18s ease;
        }


        .app-settings-tab:hover {
            color:
                var(--admin-heading);

            background:
                var(--admin-surface-hover);
        }


        .app-settings-tab.active {
            border-color:
                var(--admin-accent);

            color:
                var(--admin-accent);

            background:
                var(--admin-accent-soft);
        }


        /*
        |--------------------------------------------------------------------------
        | Panels
        |--------------------------------------------------------------------------
        */

        .app-settings-panel {
            display: none;
        }


        .app-settings-panel.active {
            display: block;
        }


        /*
        |--------------------------------------------------------------------------
        | Grid
        |--------------------------------------------------------------------------
        */

        .app-settings-grid {
            display: grid;

            grid-template-columns:
                repeat(2,
                    minmax(0, 1fr));

            gap: 16px;
        }


        /*
        |--------------------------------------------------------------------------
        | Card
        |--------------------------------------------------------------------------
        */

        .app-settings-card {
            overflow: hidden;

            border:
                1px solid var(--admin-border);

            border-radius: 14px;

            background:
                var(--admin-surface);

            box-shadow:
                var(--admin-shadow);
        }


        .app-settings-card.full {
            grid-column:
                1 / -1;
        }


        .app-settings-card-head {
            display: flex;
            align-items: center;

            gap: 11px;

            padding:
                15px 16px;

            border-bottom:
                1px solid var(--admin-border-soft);
        }


        .app-settings-card-icon {
            width: 36px;
            height: 36px;

            flex:
                0 0 36px;

            display: grid;

            place-items: center;

            border-radius: 10px;

            color:
                var(--admin-accent);

            background:
                var(--admin-accent-soft);
        }


        .app-settings-card-head h3 {
            margin: 0;

            color:
                var(--admin-heading);

            font-size: 14px;
        }


        .app-settings-card-head p {
            margin:
                3px 0 0;

            color:
                var(--admin-muted);

            font-size: 10px;
        }


        .app-settings-card-body {
            padding: 16px;
        }


        /*
        |--------------------------------------------------------------------------
        | Fields
        |--------------------------------------------------------------------------
        */

        .app-fields {
            display: grid;

            gap: 14px;
        }


        .app-fields.two {
            grid-template-columns:
                repeat(2,
                    minmax(0, 1fr));
        }


        .app-field {
            display: grid;

            gap: 7px;

            min-width: 0;
        }


        .app-field label {
            color:
                var(--admin-heading);

            font-size: 11px;
            font-weight: 800;
        }


        .app-field small {
            color:
                var(--admin-muted);

            font-size: 10px;

            line-height: 1.5;
        }


        .app-field code {
            color:
                var(--admin-accent);

            font-size: 10px;

            word-break:
                break-all;
        }


        .app-field input[type="number"],
        .app-field input[type="file"] ,
        .app-field input[type="text"],
        .app-field input[type="password"] {

            width: 100%;

            min-height: 42px;

            border:
                1px solid
                var(--admin-border);

            border-radius: 9px;

            outline: none;

            color:
                var(--admin-text);

            background:
                var(--admin-surface-soft);

            font: inherit;

            font-size: 12px;
        }


        .app-field input[type="number"] {
            padding:
                0 11px;
        }


        .app-field input[type="text"],
        .app-field input[type="password"] {
            padding:
                0 11px;
        }


        .app-field input[type="file"] {
            padding:
                8px 10px;
        }


        .app-field input:focus {
            border-color:
                var(--admin-accent);

            box-shadow:
                0 0 0 3px
                var(--admin-accent-soft);
        }


        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        .app-logo-layout {
            display: grid;

            grid-template-columns:
                minmax(
                    220px,
                    .75fr
                )
                minmax(
                    0,
                    1.25fr
                );

            gap: 18px;

            align-items: stretch;
        }


        .app-logo-preview-box {
            min-height: 190px;

            display: grid;

            place-items: center;

            padding: 24px;

            border:
                1px dashed
                var(--admin-border);

            border-radius: 13px;

            background:
                var(--admin-surface-soft);
        }


        .app-logo-preview-box img {
            max-width: 230px;

            max-height: 100px;

            object-fit:
                contain;
        }


        .app-logo-fallback {
            display: flex;
            align-items: center;

            gap: 10px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 22px;

            font-weight: 800;
        }


        .app-logo-fallback-mark {
            width: 46px;
            height: 46px;

            display: grid;

            place-items: center;

            border-radius: 13px;

            color: #FFFFFF;

            background:
                linear-gradient(
                    135deg,
                    #0B3D2E,
                    #12B76A
                );
        }


        .app-logo-fallback .mid {
            color:
                var(--admin-heading);
        }


        .app-logo-fallback .point {
            color:
                #7A5AF8;
        }


        /*
        |--------------------------------------------------------------------------
        | Notes
        |--------------------------------------------------------------------------
        */

        .app-settings-note {
            display: flex;
            align-items: flex-start;

            gap: 10px;

            padding:
                12px 13px;

            border:
                1px solid
                var(--admin-border);

            border-radius: 10px;

            color:
                var(--admin-muted);

            background:
                var(--admin-surface-soft);

            font-size: 10.5px;

            line-height: 1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | Preview
        |--------------------------------------------------------------------------
        */

        .app-metric-preview {
            display: grid;

            grid-template-columns:
                repeat(
                    3,
                    minmax(0, 1fr)
                );

            gap: 10px;

            margin-top: 14px;
        }


        .app-metric-box {
            padding: 13px;

            border:
                1px solid
                var(--admin-border-soft);

            border-radius: 10px;

            background:
                var(--admin-surface-soft);
        }


        .app-metric-box span {
            display: block;

            color:
                var(--admin-muted);

            font-size: 9.5px;

            font-weight: 700;
        }


        .app-metric-box strong {
            display: block;

            margin-top: 4px;

            color:
                var(--admin-heading);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 17px;
        }


        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        */

        .app-settings-actions {
            position: sticky;

            bottom: 12px;

            z-index: 20;

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 18px;

            padding: 11px;

            border:
                1px solid
                var(--admin-border);

            border-radius: 12px;

            background:
                var(--admin-surface);

            box-shadow:
                var(--admin-shadow);
        }


        .app-settings-btn {
            min-height: 40px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                0 15px;

            border:
                1px solid
                transparent;

            border-radius: 9px;

            cursor: pointer;

            font: inherit;

            font-size: 11px;

            font-weight: 800;

            text-decoration: none;
        }


        .app-settings-btn.primary {
            color: #FFFFFF;

            background:
                var(--admin-accent-strong);
        }


        .app-settings-btn.danger {
            border-color:
                var(--admin-danger);

            color:
                var(--admin-danger);

            background:
                transparent;
        }


        .app-settings-btn.secondary {
            border-color:
                var(--admin-border);

            color:
                var(--admin-heading);

            background:
                var(--admin-surface);
        }


        /*
        |--------------------------------------------------------------------------
        | Administrator Two-Factor Authentication
        |--------------------------------------------------------------------------
        */

        .admin-2fa-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;

            padding: 16px;

            border: 1px solid var(--admin-border);
            border-radius: 12px;

            background: var(--admin-surface-soft);
        }


        .admin-2fa-status-copy {
            display: grid;
            gap: 5px;
        }


        .admin-2fa-status-copy strong {
            color: var(--admin-heading);
            font-size: 13px;
        }


        .admin-2fa-status-copy span {
            color: var(--admin-muted);
            font-size: 10px;
            line-height: 1.55;
        }


        .admin-2fa-pill {
            flex: 0 0 auto;

            display: inline-flex;
            align-items: center;
            gap: 7px;

            min-height: 34px;
            padding: 0 11px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 800;
        }


        .admin-2fa-pill.enabled {
            color: var(--admin-accent);
            background: var(--admin-accent-soft);
        }


        .admin-2fa-pill.disabled {
            color: var(--admin-warning);
            background: rgba(245, 158, 11, .12);
        }


        .admin-2fa-instructions {
            margin: 0;
            padding-left: 19px;

            color: var(--admin-text);
            font-size: 11px;
            line-height: 1.75;
        }


        .admin-2fa-setup-grid {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }


        .admin-2fa-qr {
            min-height: 220px;

            display: grid;
            place-items: center;

            padding: 16px;

            border: 1px solid var(--admin-border);
            border-radius: 12px;

            background: #FFFFFF;
        }


        .admin-2fa-manual-key {
            display: grid;
            gap: 6px;

            margin-top: 12px;
            padding: 12px;

            border: 1px dashed var(--admin-border);
            border-radius: 10px;

            background: var(--admin-surface-soft);
        }


        .admin-2fa-manual-key span {
            color: var(--admin-muted);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }


        .admin-2fa-manual-key code {
            color: var(--admin-heading);
            font-size: 12px;
            word-break: break-all;
        }


        .admin-2fa-form {
            display: grid;
            gap: 14px;
        }


        .admin-2fa-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }


        .admin-2fa-recovery-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;

            margin-top: 14px;
        }


        .admin-2fa-recovery-grid code {
            padding: 10px;

            border: 1px solid var(--admin-border);
            border-radius: 8px;

            color: var(--admin-heading);
            background: var(--admin-surface-soft);

            font-size: 11px;
            text-align: center;
        }


        .admin-2fa-danger {
            border-color: rgba(240, 68, 56, .35);
        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 900px) {

            .app-settings-grid,
            .app-logo-layout,
            .admin-2fa-setup-grid {

                grid-template-columns:
                    1fr;

            }

        }


        @media(max-width: 700px) {

            .app-settings-head {
                flex-direction:
                    column;
            }


            .app-settings-badge {
                width: 100%;

                justify-content:
                    center;
            }


            .app-fields.two,
            .app-metric-preview {

                grid-template-columns:
                    1fr;

            }


            .app-settings-actions {
                position:
                    static;

                flex-direction:
                    column;
            }


            .app-settings-btn {
                width: 100%;
            }

        }

    </style>

@endpush



@section('content')


    @php

        $activeTab =
            request(
                'tab',
                'branding'
            );


        if (
            !in_array(
                $activeTab,
                [
                    'branding',
                    'transactions',
                    'two-factor',
                ],
                true
            )
        ) {

            $activeTab =
                'branding';

        }

    @endphp



    <div class="app-settings-wrap">


        {{-- =========================================================
            HEADER
        ========================================================== --}}

        <div class="app-settings-head">


            <div>

                <h2>
                    App Settings
                </h2>


                <p>

                    Manage MidPoint's global logo and transaction
                    configuration values.

                    Only the settings displayed on this page are written
                    to the .env file.

                    Other database credentials, mail credentials,
                    payment keys and environment secrets are never
                    displayed here.

                </p>

            </div>



            <div class="app-settings-badge">

                <i class="fa-solid fa-shield-halved"></i>

                Admin-only configuration

            </div>


        </div>



        {{-- =========================================================
            SUCCESS
        ========================================================== --}}

        @if(session('success'))

            <div class="app-alert success">

                <i class="fa-solid fa-circle-check"></i>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif



        {{-- =========================================================
            WARNING
        ========================================================== --}}

        @if(session('warning'))

            <div class="app-alert warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>
                    {{ session('warning') }}
                </span>

            </div>

        @endif



        {{-- =========================================================
            ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="app-alert error">

                <i class="fa-solid fa-circle-exclamation"></i>


                <div>

                    <strong>
                        Please fix the following:
                    </strong>


                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        @endif



        {{-- =========================================================
            TABS
        ========================================================== --}}

        <div
            class="app-settings-tabs"
            role="tablist"
        >


            <button
                type="button"
                class="app-settings-tab"
                data-app-tab="branding"
            >

                <i class="fa-regular fa-image"></i>

                Logo & Branding

            </button>



            <button
                type="button"
                class="app-settings-tab"
                data-app-tab="transactions"
            >

                <i class="fa-solid fa-sliders"></i>

                Transaction Rules

            </button>



            <button
                type="button"
                class="app-settings-tab"
                data-app-tab="two-factor"
            >

                <i class="fa-solid fa-shield-halved"></i>

                Google Authenticator

            </button>


        </div>



        {{-- =========================================================
            MAIN SETTINGS FORM
        ========================================================== --}}

        <form

            method="POST"

            action="{{
        route(
            'admin.website-settings.app-settings.update'
        )
            }}"

            enctype="multipart/form-data"

        >

            @csrf

            @method('PUT')



            {{-- =====================================================
                BRANDING
            ====================================================== --}}

            <section
                class="app-settings-panel"
                data-app-panel="branding"
            >


                <div class="app-settings-card">


                    <div class="app-settings-card-head">

                        <span class="app-settings-card-icon">

                            <i class="fa-regular fa-image"></i>

                        </span>


                        <div>

                            <h3>
                                Application Logo
                            </h3>

                            <p>
                                Upload the main MidPoint brand logo.
                            </p>

                        </div>

                    </div>



                    <div class="app-settings-card-body">


                        <div class="app-logo-layout">


                            {{-- =========================================
                                PREVIEW
                            ========================================== --}}

                            <div class="app-logo-preview-box">


                                @if(
                                        $logoExists
                                        &&
                                        $logoUrl
                                    )

                                        <img

                                            src="{{ $logoUrl }}"

                                            alt="Current MidPoint logo"

                                            id="current-logo-preview"

                                        >

                                @else

                                    <div
                                        class="app-logo-fallback"
                                        id="logo-fallback-preview"
                                    >

                                        <span class="app-logo-fallback-mark">
                                            M
                                        </span>


                                        <span>

                                            <span class="mid">
                                                Mid
                                            </span><span class="point">Point</span>

                                        </span>

                                    </div>

                                @endif



                                {{-- New upload preview --}}

                                <img

                                    id="new-logo-preview"

                                    alt="New logo preview"

                                    style="display:none;"

                                >


                            </div>



                            {{-- =========================================
                                UPLOAD
                            ========================================== --}}

                            <div class="app-fields">


                                <div class="app-field">

                                    <label for="app-logo-input">

                                        Upload new logo

                                    </label>


                                    <input

                                        id="app-logo-input"

                                        type="file"

                                        name="logo"

                                        accept="
                                            image/png,
                                            image/jpeg,
                                            image/webp
                                        "

                                    >


                                    <small>

                                        Allowed:

                                        PNG,

                                        JPG,

                                        WEBP.

                                        Maximum file size:

                                        3 MB.

                                        A transparent horizontal logo is recommended.

                                    </small>

                                </div>



                                <div class="app-settings-note">

                                    <i class="fa-solid fa-circle-info"></i>


                                    <div>

                                        Uploaded logos are stored inside:

                                        <br>

                                        <code>
                                            public/uploads/app
                                        </code>

                                        <br><br>

                                        The active path is stored in:

                                        <br>

                                        <code>
                                            MIDPOINT_APP_LOGO_PATH
                                        </code>

                                    </div>

                                </div>



                                @if(
                                        $logoExists
                                        &&
                                        $logoUrl
                                    )

                                        <div>

                                            <button

                                                type="button"

                                                class="
                                                    app-settings-btn
                                                    danger
                                                "

                                                onclick="
                                                    document
                                                        .getElementById(
                                                            'remove-app-logo-form'
                                                        )
                                                        .submit();
                                                "

                                            >

                                                <i class="fa-solid fa-trash"></i>

                                                Remove custom logo

                                            </button>

                                        </div>

                                @endif


                            </div>


                        </div>


                    </div>


                </div>


            </section>



            {{-- =====================================================
                TRANSACTION SETTINGS
            ====================================================== --}}

            <section
                class="app-settings-panel"
                data-app-panel="transactions"
            >


                <div class="app-settings-grid">


                    {{-- =============================================
                        FEES
                    ============================================== --}}

                    <div class="app-settings-card">


                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">

                                <i class="fa-solid fa-percent"></i>

                            </span>


                            <div>

                                <h3>
                                    Fee Configuration
                                </h3>

                                <p>
                                    Controls seller transaction fees.
                                </p>

                            </div>

                        </div>



                        <div class="app-settings-card-body">


                            <div class="app-fields two">


                                {{-- Service fee --}}

                                <div class="app-field">

                                    <label for="service_fee_percent">

                                        MidPoint service fee (%)

                                    </label>


                                    <input

                                        id="service_fee_percent"

                                        type="number"

                                        name="service_fee_percent"

                                        min="0"

                                        max="100"

                                        step="0.01"

                                        value="{{
        old(
            'service_fee_percent',
            $settings[
                'service_fee_percent'
            ]
        )
                                        }}"

                                        required

                                    >


                                    <small>

                                        <code>
                                            MIDPOINT_SERVICE_FEE_PERCENT
                                        </code>

                                    </small>

                                </div>



                                {{-- VAT --}}

                                <div class="app-field">

                                    <label for="fee_vat_percent">

                                        VAT on MidPoint fee (%)

                                    </label>


                                    <input

                                        id="fee_vat_percent"

                                        type="number"

                                        name="fee_vat_percent"

                                        min="0"

                                        max="100"

                                        step="0.01"

                                        value="{{
        old(
            'fee_vat_percent',
            $settings[
                'fee_vat_percent'
            ]
        )
                                        }}"

                                        required

                                    >


                                    <small>

                                        <code>
                                            MIDPOINT_FEE_VAT_PERCENT
                                        </code>

                                    </small>

                                </div>


                            </div>



                            {{-- =========================================
                                LIVE CALCULATION
                            ========================================== --}}

                            <div class="app-metric-preview">


                                <div class="app-metric-box">

                                    <span>
                                        Example product
                                    </span>

                                    <strong>
                                        ₦100,000
                                    </strong>

                                </div>



                                <div class="app-metric-box">

                                    <span>
                                        Service fee
                                    </span>

                                    <strong id="fee-preview">
                                        ₦5,000
                                    </strong>

                                </div>



                                <div class="app-metric-box">

                                    <span>
                                        VAT on fee
                                    </span>

                                    <strong id="vat-preview">
                                        ₦375
                                    </strong>

                                </div>


                            </div>


                        </div>


                    </div>



                    {{-- =============================================
                        TIMERS
                    ============================================== --}}

                    <div class="app-settings-card">


                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">

                                <i class="fa-regular fa-clock"></i>

                            </span>


                            <div>

                                <h3>
                                    Protection Timers
                                </h3>

                                <p>
                                    Buyer inspection and delivery protection windows.
                                </p>

                            </div>

                        </div>



                        <div class="app-settings-card-body">


                            <div class="app-fields two">


                                {{-- Inspection --}}

                                <div class="app-field">

                                    <label for="inspection_hours">

                                        Buyer inspection window
                                        (hours)

                                    </label>


                                    <input

                                        id="inspection_hours"

                                        type="number"

                                        name="inspection_hours"

                                        min="1"

                                        max="168"

                                        step="1"

                                        value="{{
        old(
            'inspection_hours',
            $settings[
                'inspection_hours'
            ]
        )
                                        }}"

                                        required

                                    >


                                    <small>

                                        <code>
                                            MIDPOINT_INSPECTION_HOURS
                                        </code>

                                    </small>

                                </div>



                                {{-- Auto completion --}}

                                <div class="app-field">

                                    <label for="delivery_auto_complete_hours">

                                        Delivery auto-complete window
                                        (hours)

                                    </label>


                                    <input

                                        id="delivery_auto_complete_hours"

                                        type="number"

                                        name="delivery_auto_complete_hours"

                                        min="1"

                                        max="720"

                                        step="1"

                                        value="{{
        old(
            'delivery_auto_complete_hours',
            $settings[
                'delivery_auto_complete_hours'
            ]
        )
                                        }}"

                                        required

                                    >


                                    <small>

                                        <code>
                                            MIDPOINT_DELIVERY_AUTO_COMPLETE_HOURS
                                        </code>

                                    </small>

                                </div>


                            </div>



                            <div
                                class="app-settings-note"
                                style="margin-top:14px;"
                            >

                                <i class="fa-solid fa-circle-info"></i>


                                <div>

                                    These settings control new timer
                                    calculations.

                                    Existing transactions may already
                                    contain stored inspection hours,
                                    inspection ending times or
                                    auto-completion timestamps.

                                    Updating the global setting does not
                                    rewrite historical transaction
                                    timestamps.

                                </div>

                            </div>


                        </div>


                    </div>



                    {{-- =============================================
                        ENVIRONMENT KEYS
                    ============================================== --}}

                    <div
                        class="
                            app-settings-card
                            full
                        "
                    >


                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">

                                <i class="fa-solid fa-code"></i>

                            </span>


                            <div>

                                <h3>
                                    Managed Environment Keys
                                </h3>

                                <p>
                                    Only these transaction values are edited.
                                </p>

                            </div>

                        </div>



                        <div class="app-settings-card-body">


                            <div class="app-fields two">


                                <div class="app-settings-note">

                                    <code>
                                        MIDPOINT_SERVICE_FEE_PERCENT
                                    </code>

                                </div>



                                <div class="app-settings-note">

                                    <code>
                                        MIDPOINT_FEE_VAT_PERCENT
                                    </code>

                                </div>



                                <div class="app-settings-note">

                                    <code>
                                        MIDPOINT_INSPECTION_HOURS
                                    </code>

                                </div>



                                <div class="app-settings-note">

                                    <code>
                                        MIDPOINT_DELIVERY_AUTO_COMPLETE_HOURS
                                    </code>

                                </div>


                            </div>


                        </div>


                    </div>


                </div>


            </section>



            {{-- =====================================================
                SAVE
            ====================================================== --}}

            <div
                class="app-settings-actions"
                data-app-global-actions
            >


                <button

                    type="submit"

                    class="
                        app-settings-btn
                        primary
                    "

                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save App Settings

                </button>


            </div>


        </form>



        {{-- =========================================================
            ADMINISTRATOR GOOGLE AUTHENTICATOR
        ========================================================== --}}

        @php

            $adminUser =
                auth()->user();


            $adminRecoveryCodes =
                json_decode(
                    (string) (
                        $adminUser->two_factor_recovery_codes
                        ??
                        '[]'
                    ),
                    true
                );


            $adminRecoveryCodeCount =
                is_array($adminRecoveryCodes)
                ? count($adminRecoveryCodes)
                : 0;

        @endphp


        <section
            class="app-settings-panel"
            data-app-panel="two-factor"
        >

            <div class="app-settings-grid">


                <div class="app-settings-card full">

                    <div class="app-settings-card-head">

                        <span class="app-settings-card-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>


                        <div>

                            <h3>
                                Google Authenticator
                            </h3>


                            <p>
                                Protect this administrator account with a time-based one-time password.
                            </p>

                        </div>

                    </div>


                    <div class="app-settings-card-body">

                        <div class="admin-2fa-status">

                            <div class="admin-2fa-status-copy">

                                <strong>
                                    {{ $adminUser->email }}
                                </strong>


                                <span>

                                    @if($adminUser->hasTwoFactorEnabled())

                                        Enabled {{ $adminUser->two_factor_confirmed_at?->diffForHumans() }}.
                                        {{ $adminRecoveryCodeCount }} unused recovery code(s) remain.

                                    @else

                                        Password-only admin login is currently active.

                                    @endif

                                </span>

                            </div>


                            <span class="admin-2fa-pill {{ $adminUser->hasTwoFactorEnabled() ? 'enabled' : 'disabled' }}">

                                <i class="fa-solid {{ $adminUser->hasTwoFactorEnabled() ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>

                                {{ $adminUser->hasTwoFactorEnabled() ? 'Enabled' : 'Disabled' }}

                            </span>

                        </div>

                    </div>

                </div>


                @if(!$adminUser->hasTwoFactorEnabled())

                    <div class="app-settings-card full">

                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </span>


                            <div>

                                <h3>
                                    Enable two-factor authentication
                                </h3>


                                <p>
                                    Confirm your password before Midpoint creates a private setup key.
                                </p>

                            </div>

                        </div>


                        <div class="app-settings-card-body">

                            <form
                                method="POST"
                                action="{{ route('admin.website-settings.app-settings.two-factor.setup') }}"
                                class="admin-2fa-form"
                            >

                                @csrf


                                <ol class="admin-2fa-instructions">

                                    <li>Install Google Authenticator or another TOTP-compatible app.</li>
                                    <li>Enter your current administrator password below.</li>
                                    <li>Scan the QR code and confirm the generated six-digit code.</li>
                                    <li>Save the one-time recovery codes somewhere private.</li>

                                </ol>


                                <div class="app-field">

                                    <label for="adminTwoFactorPassword">
                                        Current administrator password
                                    </label>


                                    <input
                                        id="adminTwoFactorPassword"
                                        type="password"
                                        name="current_password"
                                        autocomplete="current-password"
                                        required
                                    >


                                    @error('admin_two_factor_password')

                                        <small style="color:var(--admin-danger);">
                                            {{ $message }}
                                        </small>

                                    @enderror

                                </div>


                                <div class="admin-2fa-form-actions">

                                    <button
                                        type="submit"
                                        class="app-settings-btn primary"
                                    >

                                        <i class="fa-solid fa-qrcode"></i>

                                        Start secure setup

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>


                    @if(
                            session('two_factor_setup_secret')
                            &&
                            session('two_factor_setup_uri')
                        )

                            <div class="app-settings-card full">

                                <div class="app-settings-card-head">

                                    <span class="app-settings-card-icon">
                                        <i class="fa-solid fa-qrcode"></i>
                                    </span>


                                    <div>

                                        <h3>
                                            Scan and confirm
                                        </h3>


                                        <p>
                                            2FA remains disabled until the correct authenticator code is confirmed.
                                        </p>

                                    </div>

                                </div>


                                <div class="app-settings-card-body">

                                    <div class="admin-2fa-setup-grid">

                                        <div>

                                            <div class="admin-2fa-qr">
                                                <canvas id="adminTwoFactorQrCanvas"></canvas>
                                            </div>


                                            <div class="admin-2fa-manual-key">

                                                <span>
                                                    Manual setup key
                                                </span>


                                                <code id="adminTwoFactorManualKey">
                                                    {{ session('two_factor_setup_secret') }}
                                                </code>

                                            </div>

                                        </div>


                                        <form
                                            method="POST"
                                            action="{{ route('admin.website-settings.app-settings.two-factor.confirm') }}"
                                            class="admin-2fa-form"
                                        >

                                            @csrf


                                            <ol class="admin-2fa-instructions">

                                                <li>Open Google Authenticator.</li>
                                                <li>Tap the plus button and scan the QR code.</li>
                                                <li>Enter the current six-digit code before it expires.</li>

                                            </ol>


                                            <div class="app-field">

                                                <label for="adminTwoFactorCode">
                                                    Six-digit authenticator code
                                                </label>


                                                <input
                                                    id="adminTwoFactorCode"
                                                    type="text"
                                                    name="code"
                                                    inputmode="numeric"
                                                    autocomplete="one-time-code"
                                                    pattern="[0-9]{6}"
                                                    maxlength="6"
                                                    placeholder="000000"
                                                    required
                                                >


                                                @error('admin_two_factor_code')

                                                    <small style="color:var(--admin-danger);">
                                                        {{ $message }}
                                                    </small>

                                                @enderror

                                            </div>


                                            <div class="admin-2fa-form-actions">

                                                <button
                                                    type="submit"
                                                    class="app-settings-btn primary"
                                                >

                                                    <i class="fa-solid fa-shield"></i>

                                                    Confirm and enable

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                    @endif

                @else

                    <div class="app-settings-card">

                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">
                                <i class="fa-solid fa-key"></i>
                            </span>


                            <div>

                                <h3>
                                    Replace recovery codes
                                </h3>


                                <p>
                                    Invalidates every old recovery code and creates eight new codes.
                                </p>

                            </div>

                        </div>


                        <div class="app-settings-card-body">

                            <form
                                method="POST"
                                action="{{ route('admin.website-settings.app-settings.two-factor.recovery-codes') }}"
                                class="admin-2fa-form"
                            >

                                @csrf


                                <div class="app-field">

                                    <label for="adminRecoveryPassword">
                                        Current password
                                    </label>


                                    <input
                                        id="adminRecoveryPassword"
                                        type="password"
                                        name="current_password"
                                        autocomplete="current-password"
                                        required
                                    >

                                </div>


                                <div class="app-field">

                                    <label for="adminRecoveryCode">
                                        Authenticator or recovery code
                                    </label>


                                    <input
                                        id="adminRecoveryCode"
                                        type="text"
                                        name="code"
                                        autocomplete="one-time-code"
                                        maxlength="32"
                                        required
                                    >


                                    @error('admin_recovery_verification')

                                        <small style="color:var(--admin-danger);">
                                            {{ $message }}
                                        </small>

                                    @enderror

                                </div>


                                <div class="admin-2fa-form-actions">

                                    <button
                                        type="submit"
                                        class="app-settings-btn secondary"
                                    >

                                        <i class="fa-solid fa-rotate"></i>

                                        Generate new codes

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>


                    <div class="app-settings-card admin-2fa-danger">

                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">
                                <i class="fa-solid fa-shield-virus"></i>
                            </span>


                            <div>

                                <h3>
                                    Disable two-factor authentication
                                </h3>


                                <p>
                                    Requires both your password and a current second-factor code.
                                </p>

                            </div>

                        </div>


                        <div class="app-settings-card-body">

                            <form
                                method="POST"
                                action="{{ route('admin.website-settings.app-settings.two-factor.disable') }}"
                                class="admin-2fa-form"
                                onsubmit="return confirm('Disable two-factor authentication for this administrator?');"
                            >

                                @csrf
                                @method('DELETE')


                                <div class="app-field">

                                    <label for="adminDisableTwoFactorPassword">
                                        Current password
                                    </label>


                                    <input
                                        id="adminDisableTwoFactorPassword"
                                        type="password"
                                        name="current_password"
                                        autocomplete="current-password"
                                        required
                                    >

                                </div>


                                <div class="app-field">

                                    <label for="adminDisableTwoFactorCode">
                                        Authenticator or recovery code
                                    </label>


                                    <input
                                        id="adminDisableTwoFactorCode"
                                        type="text"
                                        name="code"
                                        autocomplete="one-time-code"
                                        maxlength="32"
                                        required
                                    >


                                    @error('admin_disable_two_factor')

                                        <small style="color:var(--admin-danger);">
                                            {{ $message }}
                                        </small>

                                    @enderror

                                </div>


                                <div class="admin-2fa-form-actions">

                                    <button
                                        type="submit"
                                        class="app-settings-btn danger"
                                    >

                                        <i class="fa-solid fa-shield-halved"></i>

                                        Disable 2FA

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                @endif


                @if(session('two_factor_recovery_codes_plain'))

                    <div class="app-settings-card full">

                        <div class="app-settings-card-head">

                            <span class="app-settings-card-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>


                            <div>

                                <h3>
                                    Save these recovery codes now
                                </h3>


                                <p>
                                    Each code can be used once. They will not be shown again after this page is closed.
                                </p>

                            </div>

                        </div>


                        <div class="app-settings-card-body">

                            <div
                                class="admin-2fa-recovery-grid"
                                id="adminTwoFactorRecoveryCodes"
                            >

                                @foreach(session('two_factor_recovery_codes_plain', []) as $recoveryCode)

                                    <code>{{ $recoveryCode }}</code>

                                @endforeach

                            </div>


                            <div
                                class="admin-2fa-form-actions"
                                style="margin-top:14px;"
                            >

                                <button
                                    type="button"
                                    class="app-settings-btn secondary"
                                    id="copyAdminRecoveryCodes"
                                >

                                    <i class="fa-regular fa-copy"></i>

                                    Copy recovery codes

                                </button>

                            </div>

                        </div>

                    </div>

                @endif

            </div>

        </section>



        {{-- =========================================================
            REMOVE LOGO FORM
        ========================================================== --}}

        @if(
                $logoExists
                &&
                $logoUrl
            )

                <form

                    id="remove-app-logo-form"

                    method="POST"

                    action="{{
                route(
                    'admin.website-settings.app-settings.logo.destroy'
                )
                    }}"

                    onsubmit="
                        return confirm(
                            'Remove the custom MidPoint logo and restore the default text logo?'
                        );
                    "

                    style="display:none;"

                >

                    @csrf

                    @method('DELETE')

                </form>

        @endif


    </div>


@endsection



@push('scripts')

    <script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |--------------------------------------------------------------------------
            | Tabs
            |--------------------------------------------------------------------------
            */

            const tabs =
                Array.from(
                    document.querySelectorAll(
                        '[data-app-tab]'
                    )
                );


            const panels =
                Array.from(
                    document.querySelectorAll(
                        '[data-app-panel]'
                    )
                );


            let activeTab =
                @json($activeTab);


            function activateTab(
                name
            ) {

                tabs.forEach(
                    function (tab) {

                        tab.classList.toggle(

                            'active',

                            tab.dataset.appTab
                            ===
                            name

                        );

                    }
                );


                panels.forEach(
                    function (panel) {

                        panel.classList.toggle(

                            'active',

                            panel.dataset.appPanel
                            ===
                            name

                        );

                    }
                );


                const globalActions =
                    document.querySelector(
                        '[data-app-global-actions]'
                    );


                if (globalActions) {
                    globalActions.style.display =
                        name === 'two-factor'
                            ? 'none'
                            : '';
                }


                /*
                |--------------------------------------------------------------------------
                | Remember Tab In URL
                |--------------------------------------------------------------------------
                */

                const url =
                    new URL(
                        window.location.href
                    );


                url.searchParams.set(
                    'tab',
                    name
                );


                window.history.replaceState(

                    {},

                    '',

                    url.toString()

                );
            }


            tabs.forEach(
                function (tab) {

                    tab.addEventListener(
                        'click',
                        function () {

                            activateTab(
                                tab.dataset.appTab
                            );

                        }
                    );

                }
            );


            activateTab(
                activeTab
            );


            /*
            |--------------------------------------------------------------------------
            | Administrator Two-Factor QR Code
            |--------------------------------------------------------------------------
            */

            @if(session('two_factor_setup_uri'))

                const adminTwoFactorQrCanvas =
                    document.getElementById(
                        'adminTwoFactorQrCanvas'
                    );


                if (
                    adminTwoFactorQrCanvas
                    &&
                    window.MidpointQRCode
                ) {
                    window.MidpointQRCode.toCanvas(
                        adminTwoFactorQrCanvas,
                        @json(session('two_factor_setup_uri')),
                        {
                            width: 190,
                            margin: 1,
                            errorCorrectionLevel: 'M',
                        }
                    ).catch(function (error) {
                        console.error(
                            'Unable to generate the administrator 2FA QR code:',
                            error
                        );
                    });
                }

            @endif


            const copyAdminRecoveryCodes =
                document.getElementById(
                    'copyAdminRecoveryCodes'
                );


            if (copyAdminRecoveryCodes) {
                copyAdminRecoveryCodes.addEventListener(
                    'click',
                    async function () {
                        const codes = Array.from(
                            document.querySelectorAll(
                                '#adminTwoFactorRecoveryCodes code'
                            )
                        ).map(function (item) {
                            return item.textContent.trim();
                        });


                        try {
                            await navigator.clipboard.writeText(
                                codes.join('\n')
                            );

                            copyAdminRecoveryCodes.innerHTML =
                                '<i class="fa-solid fa-check"></i> Copied';
                        } catch (error) {
                            window.prompt(
                                'Copy your recovery codes:',
                                codes.join('\n')
                            );
                        }
                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Logo Preview
            |--------------------------------------------------------------------------
            */

            const logoInput =

                document.getElementById(
                    'app-logo-input'
                );


            const newLogoPreview =

                document.getElementById(
                    'new-logo-preview'
                );


            const currentLogoPreview =

                document.getElementById(
                    'current-logo-preview'
                );


            const fallbackPreview =

                document.getElementById(
                    'logo-fallback-preview'
                );


            if (
                logoInput
                &&
                newLogoPreview
            ) {

                logoInput.addEventListener(
                    'change',
                    function () {

                        const file =

                            logoInput.files

                            &&

                            logoInput.files[0]

                                ? logoInput.files[0]

                                : null;


                        if (
                            !file
                        ) {

                            newLogoPreview.style.display =
                                'none';


                            if (
                                currentLogoPreview
                            ) {

                                currentLogoPreview
                                    .style
                                    .display = '';

                            }


                            if (
                                fallbackPreview
                            ) {

                                fallbackPreview
                                    .style
                                    .display = '';

                            }


                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Hide Current Preview
                        |--------------------------------------------------------------------------
                        */

                        if (
                            currentLogoPreview
                        ) {

                            currentLogoPreview
                                .style
                                .display =
                                    'none';

                        }


                        if (
                            fallbackPreview
                        ) {

                            fallbackPreview
                                .style
                                .display =
                                    'none';

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Show New Preview
                        |--------------------------------------------------------------------------
                        */

                        newLogoPreview.src =

                            URL.createObjectURL(
                                file
                            );


                        newLogoPreview.style.display =
                            'block';

                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Fee Example Preview
            |--------------------------------------------------------------------------
            */

            const feeInput =

                document.getElementById(
                    'service_fee_percent'
                );


            const vatInput =

                document.getElementById(
                    'fee_vat_percent'
                );


            const feePreview =

                document.getElementById(
                    'fee-preview'
                );


            const vatPreview =

                document.getElementById(
                    'vat-preview'
                );


            function formatNaira(
                value
            ) {

                return

                    '₦'

                    +

                    Number(
                        value || 0
                    )
                        .toLocaleString(

                            'en-NG',

                            {

                                maximumFractionDigits:
                                    2,

                            }

                        );
            }


            function updateFeePreview()
            {

                if (

                    !feeInput

                    ||

                    !vatInput

                    ||

                    !feePreview

                    ||

                    !vatPreview

                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Example ₦100,000 Transaction
                |--------------------------------------------------------------------------
                */

                const exampleAmount =
                    100000;


                const feeRate =

                    Number(
                        feeInput.value
                        ||
                        0
                    );


                const vatRate =

                    Number(
                        vatInput.value
                        ||
                        0
                    );


                const feeAmount =

                    exampleAmount

                    *

                    (
                        feeRate
                        /
                        100
                    );


                const vatAmount =

                    feeAmount

                    *

                    (
                        vatRate
                        /
                        100
                    );


                feePreview.textContent =

                    formatNaira(
                        feeAmount
                    );


                vatPreview.textContent =

                    formatNaira(
                        vatAmount
                    );

            }


            if (
                feeInput
            ) {

                feeInput.addEventListener(

                    'input',

                    updateFeePreview

                );

            }


            if (
                vatInput
            ) {

                vatInput.addEventListener(

                    'input',

                    updateFeePreview

                );

            }


            updateFeePreview();

        }
    );

    </script>

@endpush
