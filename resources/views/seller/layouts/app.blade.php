<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >


    <title>
        @yield('title', 'Seller Dashboard') | MidPoint
    </title>


    {{-- =========================================================
        FONTS
    ========================================================== --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
        APPLICATION ASSETS
    ========================================================== --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',

        /*
        |--------------------------------------------------------------------------
        | Keep Dashboard Styling
        |--------------------------------------------------------------------------
        |
        | Seller Dashboard previously used account.layouts.app.
        | Therefore its cards, tables, charts etc. depend on this stylesheet.
        |
        */

        'resources/css/account-dashboard.css',
        'resources/js/account-dashboard.js',
    ])


    {{-- =========================================================
        UNIFIED SELLER SIDEBAR CSS
    ========================================================== --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | Seller Shell
        |--------------------------------------------------------------------------
        */

        .seller-account-shell {
            width: 100%;
            max-width: 1200px;
            min-height: calc(100vh - 66px);

            display: flex;

            margin: 0 auto;
        }


        .seller-account-main {
            min-width: 0;
            flex: 1;

            padding: 32px;
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Sidebar
        |--------------------------------------------------------------------------
        */

        .seller-main-sidebar {
            width: 220px;
            flex: 0 0 220px;

            position: relative;

            padding: 32px 20px 32px 0;

            border-right: 1px solid #E4EAE6;

            overflow: visible;
        }


        .seller-sidebar-section-title {
            margin-bottom: 12px;
            padding: 0 12px;

            color: #98A49E;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .12em;
        }


        .seller-sidebar-switch-title {
            margin-top: 28px;
        }


        /*
        |--------------------------------------------------------------------------
        | Navigation
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-nav {
            display: flex;
            flex-direction: column;

            gap: 4px;
        }


        .seller-sidebar-link {
            position: relative;

            width: 100%;
            min-height: 44px;

            display: flex;
            align-items: center;

            gap: 12px;

            padding: 10px 12px;

            border: 0;
            border-radius: 12px;

            background: transparent;

            color: #5A6660;

            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;

            text-align: left;
            text-decoration: none;

            cursor: pointer;

            transition:
                color .16s ease,
                background .16s ease,
                opacity .16s ease;
        }


        .seller-sidebar-link:hover {
            background: #E8F7EF;

            color: #0B3D2E;
        }


        .seller-sidebar-link.active {
            background: #0B3D2E;

            color: #FFFFFF;
        }


        .seller-sidebar-icon {
            width: 18px;
            height: 18px;

            flex: 0 0 18px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
        }


        .seller-sidebar-label {
            min-width: 0;
            flex: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Locked Product Feature
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-link.is-locked {
            color: #9CA7A1;

            background: transparent;

            cursor: pointer;
        }


        .seller-sidebar-link.is-locked:hover,
        .seller-sidebar-link.is-locked:focus {
            background: #F0F4F2;

            color: #68746E;
        }


        .seller-sidebar-link.is-locked
        .seller-sidebar-icon {
            color: #95A19B;
        }


        .seller-sidebar-lock {
            width: 22px;
            height: 22px;

            flex: 0 0 22px;

            display: grid;
            place-items: center;

            border-radius: 7px;

            background: #EEF2F0;

            color: #7E8A84;

            font-size: 8px;
        }


        /*
        |--------------------------------------------------------------------------
        | Tooltip Wrapper
        |--------------------------------------------------------------------------
        */

        .seller-locked-menu-wrapper {
            position: relative;

            overflow: visible;
        }


        /*
        |--------------------------------------------------------------------------
        | Tooltip
        |--------------------------------------------------------------------------
        */

        .seller-plan-tooltip {
            position: absolute;

            left: calc(100% + 12px);
            top: 50%;

            z-index: 9999;

            width: 280px;

            display: flex;
            align-items: flex-start;

            gap: 11px;

            padding: 14px;

            border: 1px solid #DCE5E0;
            border-radius: 13px;

            background: #FFFFFF;

            box-shadow:
                0 16px 45px
                rgba(11, 61, 46, .16);

            opacity: 0;
            visibility: hidden;

            pointer-events: none;

            transform:
                translate(
                    -6px,
                    -50%
                );

            transition:
                opacity .16s ease,
                visibility .16s ease,
                transform .16s ease;
        }


        .seller-plan-tooltip::before {
            content: "";

            position: absolute;

            left: -6px;
            top: 50%;

            width: 11px;
            height: 11px;

            border-left: 1px solid #DCE5E0;
            border-bottom: 1px solid #DCE5E0;

            background: #FFFFFF;

            transform:
                translateY(-50%)
                rotate(45deg);
        }


        /*
        |--------------------------------------------------------------------------
        | Show Tooltip
        |--------------------------------------------------------------------------
        */

        .seller-locked-menu-wrapper:hover
        .seller-plan-tooltip,

        .seller-locked-menu-wrapper:focus-within
        .seller-plan-tooltip {

            opacity: 1;
            visibility: visible;

            transform:
                translate(
                    0,
                    -50%
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Tooltip Icon
        |--------------------------------------------------------------------------
        */

        .seller-plan-tooltip-icon {
            width: 34px;
            height: 34px;

            flex: 0 0 34px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            background: #FFF7E8;

            color: #B54708;

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | Tooltip Typography
        |--------------------------------------------------------------------------
        */

        .seller-plan-tooltip-content {
            min-width: 0;
            flex: 1;
        }


        .seller-plan-tooltip strong {
            display: block;

            margin-bottom: 4px;

            color: #17251F;

            font-size: 11px;
            font-weight: 700;
            line-height: 1.4;
        }


        .seller-plan-tooltip span {
            display: block;

            color: #69766F;

            font-size: 10px;
            line-height: 1.55;
        }


        .seller-plan-tooltip small {
            display: block;

            margin-top: 6px;

            color: #12B76A;

            font-size: 9px;
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-logout:hover {
            background: #FFF1F2;

            color: #D92D20;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile Seller Menu Button
        |--------------------------------------------------------------------------
        */

        .seller-mobile-navigation {
            display: none;

            padding: 12px 16px;

            border-bottom: 1px solid #E4EAE6;

            background: #F6F9F7;
        }


        .seller-mobile-menu-button {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            padding: 9px 12px;

            border: 1px solid #DDE5E1;
            border-radius: 10px;

            background: #FFFFFF;

            color: #0B3D2E;

            font-size: 12px;
            font-weight: 700;

            cursor: pointer;
        }


        .seller-sidebar-backdrop {
            display: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 1023px) {

            .seller-mobile-navigation {
                display: block;
            }


            .seller-account-shell {
                display: block;
            }


            .seller-main-sidebar {
                position: fixed;

                left: 0;
                top: 0;
                bottom: 0;

                z-index: 10000;

                width: 260px;

                padding:
                    90px 20px
                    25px;

                border-right: 1px solid #E4EAE6;

                background: #F6F9F7;

                box-shadow:
                    15px 0 40px
                    rgba(11, 61, 46, .12);

                transform:
                    translateX(-105%);

                transition:
                    transform .2s ease;
            }


            .seller-main-sidebar.is-open {
                transform:
                    translateX(0);
            }


            .seller-sidebar-backdrop {
                position: fixed;

                inset: 0;

                z-index: 9999;

                display: block;

                background:
                    rgba(11, 31, 23, .45);

                opacity: 0;
                visibility: hidden;

                transition:
                    opacity .2s ease,
                    visibility .2s ease;
            }


            .seller-sidebar-backdrop.is-open {
                opacity: 1;
                visibility: visible;
            }


            .seller-account-main {
                width: 100%;

                padding: 24px 16px;
            }


            /*
            |--------------------------------------------------------------------------
            | Mobile Tooltip
            |--------------------------------------------------------------------------
            */

            .seller-plan-tooltip {
                left: 12px;
                top: calc(100% + 7px);

                width:
                    calc(
                        100% - 24px
                    );

                transform:
                    translateY(-5px);
            }


            .seller-plan-tooltip::before {
                display: none;
            }


            .seller-locked-menu-wrapper:hover
            .seller-plan-tooltip,

            .seller-locked-menu-wrapper:focus-within
            .seller-plan-tooltip {

                transform:
                    translateY(0);
            }

        }

    </style>


    {{-- =========================================================
        PAGE SPECIFIC CSS
    ========================================================== --}}

    @stack('styles')

</head>


<body class="bg-[#F6F9F7] text-[#17251F]">


    {{-- =========================================================
        WEBSITE HEADER
    ========================================================== --}}

    @include('frontend.partials.header')



    {{-- =========================================================
        MOBILE NAVIGATION
    ========================================================== --}}

    <div class="seller-mobile-navigation">

        <button
            type="button"
            id="sellerSidebarToggle"
            class="seller-mobile-menu-button"
            aria-expanded="false"
        >

            <i class="fa-solid fa-bars"></i>

            Seller menu

        </button>

    </div>



    {{-- =========================================================
        MOBILE BACKDROP
    ========================================================== --}}

    <div
        id="sellerSidebarBackdrop"
        class="seller-sidebar-backdrop"
    ></div>



    {{-- =========================================================
        SELLER SHELL
    ========================================================== --}}

    <div class="seller-account-shell">


        {{-- Sidebar --}}
        @include('seller.partials.sidebar')


        {{-- Main --}}
        <main class="seller-account-main">

            @yield('content')

        </main>

    </div>



    {{-- =========================================================
        ADMIN IMPERSONATION
    ========================================================== --}}

    @if(session()->has('impersonator_admin_id'))

        <div
            class="
                fixed
                bottom-5
                left-1/2
                z-[99999]
                flex
                -translate-x-1/2
                items-center
                gap-4
                rounded-xl
                bg-[#101915]
                px-5
                py-3
                text-[12px]
                text-white
                shadow-2xl
            "
        >

            <span>

                <strong>
                    Administrator mode:
                </strong>

                viewing
                {{ auth()->user()->name }}'s account.

            </span>


            <form
                method="POST"
                action="{{ route('impersonation.stop') }}"
            >

                @csrf


                <button
                    type="submit"
                    class="
                        rounded-lg
                        bg-white
                        px-3
                        py-2
                        font-semibold
                        text-[#0B3D2E]
                    "
                >

                    Return to admin

                </button>

            </form>

        </div>

    @endif



    {{-- =========================================================
        PAGE SCRIPTS
    ========================================================== --}}

    @stack('scripts')



    {{-- =========================================================
        SELLER SIDEBAR SCRIPT
    ========================================================== --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const sidebar =
                    document.getElementById(
                        'sellerMainSidebar'
                    );


                const toggle =
                    document.getElementById(
                        'sellerSidebarToggle'
                    );


                const backdrop =
                    document.getElementById(
                        'sellerSidebarBackdrop'
                    );


                function openSidebar() {

                    if (!sidebar) {
                        return;
                    }


                    sidebar.classList.add(
                        'is-open'
                    );


                    if (backdrop) {

                        backdrop.classList.add(
                            'is-open'
                        );
                    }


                    if (toggle) {

                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                    }


                    document.body.style.overflow =
                        'hidden';
                }


                function closeSidebar() {

                    if (!sidebar) {
                        return;
                    }


                    sidebar.classList.remove(
                        'is-open'
                    );


                    if (backdrop) {

                        backdrop.classList.remove(
                            'is-open'
                        );
                    }


                    if (toggle) {

                        toggle.setAttribute(
                            'aria-expanded',
                            'false'
                        );
                    }


                    document.body.style.overflow =
                        '';
                }


                if (toggle) {

                    toggle.addEventListener(
                        'click',
                        function () {

                            if (
                                sidebar
                                &&
                                sidebar.classList.contains(
                                    'is-open'
                                )
                            ) {

                                closeSidebar();

                            } else {

                                openSidebar();
                            }
                        }
                    );
                }


                if (backdrop) {

                    backdrop.addEventListener(
                        'click',
                        closeSidebar
                    );
                }


                window.addEventListener(
                    'resize',
                    function () {

                        if (
                            window.innerWidth
                            >=
                            1024
                        ) {

                            closeSidebar();
                        }
                    }
                );

            }
        );

    </script>

</body>

</html>