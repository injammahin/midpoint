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
            1px solid
            var(--admin-border);

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
            1px solid
            var(--admin-border);

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
            1px solid
            var(--admin-border);

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
            1px solid
            transparent;

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
            repeat(
                2,
                minmax(0, 1fr)
            );

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
            1px solid
            var(--admin-border);

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
            1px solid
            var(--admin-border-soft);
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
            repeat(
                2,
                minmax(0, 1fr)
            );
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
    .app-field input[type="file"] {

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
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 900px) {

        .app-settings-grid,
        .app-logo-layout {

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

        <div class="app-settings-actions">


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