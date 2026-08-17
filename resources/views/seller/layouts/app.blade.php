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
        @yield('title', 'Seller Dashboard') | Midpoint
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
        'resources/css/account-dashboard.css',
        'resources/js/account-dashboard.js',
    ])


    {{-- =========================================================
        SELLER DASHBOARD SHELL
    ========================================================== --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | Root / Viewport
        |--------------------------------------------------------------------------
        |
        | The dashboard owns the browser viewport.
        |
        | Header remains visible.
        | Sidebar remains visible.
        | Only seller-account-main scrolls.
        |
        */

        html {
            width: 100%;
            height: 100%;

            overflow: hidden;
        }


        body.seller-layout-body {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            min-height: 100vh;

            display: flex;
            flex-direction: column;

            margin: 0;

            overflow: hidden;
        }



        /*
        |--------------------------------------------------------------------------
        | Website Header Container
        |--------------------------------------------------------------------------
        */

        .seller-site-header {
            position: relative;

            z-index: 500;

            width: 100%;

            flex: 0 0 auto;
        }



        /*
        |--------------------------------------------------------------------------
        | Seller Shell
        |--------------------------------------------------------------------------
        */

        .seller-account-shell {
            width: 100%;
            max-width: 1200px;

            /*
             * Important:
             *
             * Occupy all remaining height below website header.
             */
            flex: 1 1 auto;

            min-height: 0;

            display: flex;

            margin: 0 auto;

            overflow: hidden;
        }



        /*
        |--------------------------------------------------------------------------
        | Seller Main Content
        |--------------------------------------------------------------------------
        */

        .seller-account-main {
            position: relative;

            min-width: 0;
            min-height: 0;

            height: 100%;

            flex: 1 1 auto;

            padding: 32px;

            /*
             * MAIN FIX:
             *
             * Large wallet pages, transaction pages, product pages,
             * etc. scroll here instead of moving the sidebar.
             */
            overflow-y: auto;
            overflow-x: hidden;

            overscroll-behavior: contain;

            scrollbar-gutter: stable;

            -webkit-overflow-scrolling: touch;
        }



        /*
        |--------------------------------------------------------------------------
        | Main Content Scrollbar
        |--------------------------------------------------------------------------
        */

        .seller-account-main::-webkit-scrollbar {
            width: 8px;
        }


        .seller-account-main::-webkit-scrollbar-track {
            background: transparent;
        }


        .seller-account-main::-webkit-scrollbar-thumb {
            border: 2px solid transparent;
            border-radius: 999px;

            background:
                rgba(
                    95,
                    119,
                    108,
                    .30
                );

            background-clip:
                padding-box;
        }


        .seller-account-main::-webkit-scrollbar-thumb:hover {
            background:
                rgba(
                    55,
                    88,
                    73,
                    .46
                );

            background-clip:
                padding-box;
        }



        /*
        |--------------------------------------------------------------------------
        | Seller Sidebar
        |--------------------------------------------------------------------------
        |
        | Because the shell itself never scrolls, this remains fixed.
        |
        */

        .seller-main-sidebar {
            position: relative;

            z-index: 30;

            width: 220px;
            height: 100%;

            min-height: 0;

            flex: 0 0 220px;

            padding:
                32px
                20px
                32px
                0;

            border-right:
                1px solid
                #E4EAE6;

            background:
                #F6F9F7;

            /*
             * Keep visible so desktop subscription tooltip
             * can continue appearing to the right.
             */
            overflow: visible;
        }



        /*
        |--------------------------------------------------------------------------
        | Sidebar Section Title
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-section-title {
            margin-bottom: 12px;

            padding:
                0
                12px;

            color:
                #98A49E;

            font-size:
                12px;

            font-weight:
                700;

            text-transform:
                uppercase;

            letter-spacing:
                .12em;
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

            padding:
                10px
                12px;

            border: 0;
            border-radius: 12px;

            background:
                transparent;

            color:
                #5A6660;

            font-family:
                'Inter',
                sans-serif;

            font-size:
                13px;

            font-weight:
                600;

            text-align:
                left;

            text-decoration:
                none;

            cursor:
                pointer;

            transition:
                color .16s ease,
                background .16s ease,
                opacity .16s ease;
        }


        .seller-sidebar-link:hover {
            background:
                #E8F7EF;

            color:
                #0B3D2E;
        }


        .seller-sidebar-link.active {
            background:
                #0B3D2E;

            color:
                #FFFFFF;
        }



        /*
        |--------------------------------------------------------------------------
        | Sidebar Icons
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-icon {
            width: 18px;
            height: 18px;

            flex:
                0 0 18px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                12px;
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
            color:
                #9CA7A1;

            background:
                transparent;

            cursor:
                pointer;
        }


        .seller-sidebar-link.is-locked:hover,
        .seller-sidebar-link.is-locked:focus {
            background:
                #F0F4F2;

            color:
                #68746E;
        }


        .seller-sidebar-link.is-locked
        .seller-sidebar-icon {
            color:
                #95A19B;
        }


        .seller-sidebar-lock {
            width: 22px;
            height: 22px;

            flex:
                0 0 22px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                7px;

            background:
                #EEF2F0;

            color:
                #7E8A84;

            font-size:
                8px;
        }



        /*
        |--------------------------------------------------------------------------
        | Locked Menu Tooltip Wrapper
        |--------------------------------------------------------------------------
        */

        .seller-locked-menu-wrapper {
            position:
                relative;

            overflow:
                visible;
        }



        /*
        |--------------------------------------------------------------------------
        | Subscription Tooltip
        |--------------------------------------------------------------------------
        */

        .seller-plan-tooltip {
            position:
                absolute;

            left:
                calc(
                    100% + 12px
                );

            top:
                50%;

            z-index:
                9999;

            width:
                280px;

            display:
                flex;

            align-items:
                flex-start;

            gap:
                11px;

            padding:
                14px;

            border:
                1px solid
                #DCE5E0;

            border-radius:
                13px;

            background:
                #FFFFFF;

            box-shadow:
                0
                16px
                45px
                rgba(
                    11,
                    61,
                    46,
                    .16
                );

            opacity:
                0;

            visibility:
                hidden;

            pointer-events:
                none;

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
            content:
                "";

            position:
                absolute;

            left:
                -6px;

            top:
                50%;

            width:
                11px;

            height:
                11px;

            border-left:
                1px solid
                #DCE5E0;

            border-bottom:
                1px solid
                #DCE5E0;

            background:
                #FFFFFF;

            transform:
                translateY(
                    -50%
                )
                rotate(
                    45deg
                );
        }


        .seller-locked-menu-wrapper:hover
        .seller-plan-tooltip,

        .seller-locked-menu-wrapper:focus-within
        .seller-plan-tooltip {
            opacity:
                1;

            visibility:
                visible;

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
            width:
                34px;

            height:
                34px;

            flex:
                0 0 34px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                10px;

            background:
                #FFF7E8;

            color:
                #B54708;

            font-size:
                11px;
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
            display:
                block;

            margin-bottom:
                4px;

            color:
                #17251F;

            font-size:
                11px;

            font-weight:
                700;

            line-height:
                1.4;
        }


        .seller-plan-tooltip span {
            display:
                block;

            color:
                #69766F;

            font-size:
                12px;

            line-height:
                1.55;
        }


        .seller-plan-tooltip small {
            display:
                block;

            margin-top:
                6px;

            color:
                #12B76A;

            font-size:
                11px;

            font-weight:
                700;
        }



        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-logout:hover {
            background:
                #FFF1F2;

            color:
                #D92D20;
        }



        /*
        |--------------------------------------------------------------------------
        | Mobile Seller Navigation
        |--------------------------------------------------------------------------
        */

        .seller-mobile-navigation {
            display:
                none;

            flex:
                0 0 auto;

            padding:
                12px
                16px;

            border-bottom:
                1px solid
                #E4EAE6;

            background:
                #F6F9F7;
        }


        .seller-mobile-menu-button {
            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                9px
                12px;

            border:
                1px solid
                #DDE5E1;

            border-radius:
                10px;

            background:
                #FFFFFF;

            color:
                #0B3D2E;

            font-size:
                12px;

            font-weight:
                700;

            cursor:
                pointer;
        }



        /*
        |--------------------------------------------------------------------------
        | Sidebar Backdrop
        |--------------------------------------------------------------------------
        */

        .seller-sidebar-backdrop {
            display:
                none;
        }



        /*
        |--------------------------------------------------------------------------
        | Short Desktop Screens
        |--------------------------------------------------------------------------
        |
        | If laptop screen height becomes very small we also allow the
        | sidebar itself to scroll.
        |
        */

        @media(
            min-width: 1024px
        )
        and
        (
            max-height: 700px
        ) {

            .seller-main-sidebar {
                overflow-y:
                    auto;

                overflow-x:
                    hidden;

                overscroll-behavior:
                    contain;
            }


            /*
             * Tooltip stays inside the sidebar when sidebar
             * itself needs scrolling.
             */
            .seller-plan-tooltip {
                left:
                    12px;

                top:
                    calc(
                        100% + 7px
                    );

                width:
                    calc(
                        100% - 24px
                    );

                transform:
                    translateY(
                        -5px
                    );
            }


            .seller-plan-tooltip::before {
                display:
                    none;
            }


            .seller-locked-menu-wrapper:hover
            .seller-plan-tooltip,

            .seller-locked-menu-wrapper:focus-within
            .seller-plan-tooltip {
                transform:
                    translateY(
                        0
                    );
            }

        }



        /*
        |--------------------------------------------------------------------------
        | Tablet / Mobile
        |--------------------------------------------------------------------------
        */

        @media(max-width: 1023px) {

            .seller-mobile-navigation {
                display:
                    block;
            }


            .seller-account-shell {
                display:
                    block;

                width:
                    100%;

                max-width:
                    none;

                min-height:
                    0;

                overflow:
                    hidden;
            }


            /*
            |--------------------------------------------------------------------------
            | Mobile Main Content Still Scrolls Independently
            |--------------------------------------------------------------------------
            */

            .seller-account-main {
                width:
                    100%;

                height:
                    100%;

                min-height:
                    0;

                padding:
                    24px
                    16px;

                overflow-y:
                    auto;

                overflow-x:
                    hidden;
            }


            /*
            |--------------------------------------------------------------------------
            | Drawer Sidebar
            |--------------------------------------------------------------------------
            */

            .seller-main-sidebar {
                position:
                    fixed;

                left:
                    0;

                top:
                    0;

                bottom:
                    0;

                z-index:
                    10000;

                width:
                    280px;

                height:
                    100vh;

                height:
                    100dvh;

                padding:
                    90px
                    20px
                    28px;

                border-right:
                    1px solid
                    #E4EAE6;

                background:
                    #F6F9F7;

                box-shadow:
                    15px
                    0
                    40px
                    rgba(
                        11,
                        61,
                        46,
                        .12
                    );

                /*
                 * Mobile sidebar itself may scroll if menu is large.
                 */
                overflow-y:
                    auto;

                overflow-x:
                    hidden;

                overscroll-behavior:
                    contain;

                transform:
                    translateX(
                        -105%
                    );

                transition:
                    transform
                    .2s ease;
            }


            .seller-main-sidebar.is-open {
                transform:
                    translateX(
                        0
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Mobile Backdrop
            |--------------------------------------------------------------------------
            */

            .seller-sidebar-backdrop {
                position:
                    fixed;

                inset:
                    0;

                z-index:
                    9999;

                display:
                    block;

                background:
                    rgba(
                        11,
                        31,
                        23,
                        .45
                    );

                opacity:
                    0;

                visibility:
                    hidden;

                transition:
                    opacity .2s ease,
                    visibility .2s ease;
            }


            .seller-sidebar-backdrop.is-open {
                opacity:
                    1;

                visibility:
                    visible;
            }


            /*
            |--------------------------------------------------------------------------
            | Mobile Tooltip
            |--------------------------------------------------------------------------
            */

            .seller-plan-tooltip {
                left:
                    12px;

                top:
                    calc(
                        100% + 7px
                    );

                width:
                    calc(
                        100% - 24px
                    );

                transform:
                    translateY(
                        -5px
                    );
            }


            .seller-plan-tooltip::before {
                display:
                    none;
            }


            .seller-locked-menu-wrapper:hover
            .seller-plan-tooltip,

            .seller-locked-menu-wrapper:focus-within
            .seller-plan-tooltip {
                transform:
                    translateY(
                        0
                    );
            }

        }



        /*
        |--------------------------------------------------------------------------
        | Very Small Mobile
        |--------------------------------------------------------------------------
        */

        @media(max-width: 480px) {

            .seller-main-sidebar {
                width:
                    min(
                        86vw,
                        290px
                    );
            }


            .seller-account-main {
                padding:
                    20px
                    13px;
            }

        }

    </style>


    {{-- =========================================================
        PAGE SPECIFIC CSS
    ========================================================== --}}

    @stack('styles')

</head>


<body class="seller-layout-body bg-[#F6F9F7] text-[#17251F]">


    {{-- =========================================================
        WEBSITE HEADER
    ========================================================== --}}

    <div class="seller-site-header">

        @include('frontend.partials.header')

    </div>



    {{-- =========================================================
        MOBILE NAVIGATION
    ========================================================== --}}

    <div class="seller-mobile-navigation">

        <button
            type="button"
            id="sellerSidebarToggle"
            class="seller-mobile-menu-button"
            aria-expanded="false"
            aria-controls="sellerMainSidebar"
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
        aria-hidden="true"
    ></div>



    {{-- =========================================================
        SELLER SHELL
    ========================================================== --}}

    <div class="seller-account-shell">


        {{-- =====================================================
            FIXED SIDEBAR
        ====================================================== --}}

        @include('seller.partials.sidebar')



        {{-- =====================================================
            SCROLLABLE CONTENT
        ====================================================== --}}

        <main
            id="sellerAccountMain"
            class="seller-account-main"
        >

            @yield('content')

        </main>

    </div>



    {{-- =========================================================
        ADMIN IMPERSONATION
    ========================================================== --}}

    @if(
        session()->has(
            'impersonator_admin_id'
        )
    )

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


                /*
                |--------------------------------------------------------------------------
                | Open Sidebar
                |--------------------------------------------------------------------------
                */

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


                        backdrop.setAttribute(
                            'aria-hidden',
                            'false'
                        );
                    }


                    if (toggle) {

                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                    }
                }



                /*
                |--------------------------------------------------------------------------
                | Close Sidebar
                |--------------------------------------------------------------------------
                */

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


                        backdrop.setAttribute(
                            'aria-hidden',
                            'true'
                        );
                    }


                    if (toggle) {

                        toggle.setAttribute(
                            'aria-expanded',
                            'false'
                        );
                    }
                }



                /*
                |--------------------------------------------------------------------------
                | Toggle
                |--------------------------------------------------------------------------
                */

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



                /*
                |--------------------------------------------------------------------------
                | Backdrop
                |--------------------------------------------------------------------------
                */

                if (backdrop) {

                    backdrop.addEventListener(
                        'click',
                        closeSidebar
                    );
                }



                /*
                |--------------------------------------------------------------------------
                | Close Mobile Drawer After Clicking Link
                |--------------------------------------------------------------------------
                */

                if (sidebar) {

                    sidebar
                        .querySelectorAll(
                            'a'
                        )
                        .forEach(
                            function (link) {

                                link.addEventListener(
                                    'click',
                                    function () {

                                        if (
                                            window.innerWidth
                                            <
                                            1024
                                        ) {

                                            closeSidebar();
                                        }
                                    }
                                );
                            }
                        );
                }



                /*
                |--------------------------------------------------------------------------
                | Escape Key
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key
                            ===
                            'Escape'
                        ) {

                            closeSidebar();
                        }
                    }
                );



                /*
                |--------------------------------------------------------------------------
                | Desktop Reset
                |--------------------------------------------------------------------------
                */

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