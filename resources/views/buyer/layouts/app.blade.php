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
        @yield('title', 'Buyer Dashboard') | Midpoint
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
        BUYER DASHBOARD SHELL
    ========================================================== --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | Root / Viewport
        |--------------------------------------------------------------------------
        */

        html {
            width:
                100%;

            height:
                100%;

            overflow:
                hidden;
        }


        body.buyer-layout-body {
            width:
                100%;

            height:
                100vh;

            height:
                100dvh;

            min-height:
                100vh;

            display:
                flex;

            flex-direction:
                column;

            margin:
                0;

            overflow:
                hidden;
        }



        /*
        |--------------------------------------------------------------------------
        | Website Header Container
        |--------------------------------------------------------------------------
        */

        .buyer-site-header {
            position:
                relative;

            z-index:
                500;

            width:
                100%;

            flex:
                0 0 auto;
        }



        /*
        |--------------------------------------------------------------------------
        | Buyer Shell
        |--------------------------------------------------------------------------
        */

        .buyer-account-shell {
            width:
                100%;

            max-width:
                1200px;

            flex:
                1 1 auto;

            min-height:
                0;

            display:
                flex;

            margin:
                0 auto;

            overflow:
                hidden;
        }



        /*
        |--------------------------------------------------------------------------
        | Buyer Sidebar
        |--------------------------------------------------------------------------
        */

        .buyer-main-sidebar {
            position:
                relative;

            z-index:
                30;

            width:
                220px;

            height:
                100%;

            min-height:
                0;

            flex:
                0 0 220px;

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
             * Sidebar can have its own scrollbar if menu becomes
             * taller than the screen.
             */
            overflow-y:
                auto;

            overflow-x:
                hidden;

            overscroll-behavior:
                contain;

            scrollbar-gutter:
                stable;
        }



        /*
        |--------------------------------------------------------------------------
        | Buyer Sidebar Scrollbar
        |--------------------------------------------------------------------------
        */

        .buyer-main-sidebar::-webkit-scrollbar {
            width:
                6px;
        }


        .buyer-main-sidebar::-webkit-scrollbar-track {
            background:
                transparent;
        }


        .buyer-main-sidebar::-webkit-scrollbar-thumb {
            border-radius:
                999px;

            background:
                rgba(
                    99,
                    121,
                    110,
                    .24
                );
        }



        /*
        |--------------------------------------------------------------------------
        | Main Content
        |--------------------------------------------------------------------------
        */

        .buyer-account-main {
            position:
                relative;

            min-width:
                0;

            min-height:
                0;

            height:
                100%;

            flex:
                1 1 auto;

            padding:
                32px;

            /*
             * Only page content scrolls.
             */
            overflow-y:
                auto;

            overflow-x:
                hidden;

            overscroll-behavior:
                contain;

            scrollbar-gutter:
                stable;

            -webkit-overflow-scrolling:
                touch;
        }



        /*
        |--------------------------------------------------------------------------
        | Content Scrollbar
        |--------------------------------------------------------------------------
        */

        .buyer-account-main::-webkit-scrollbar {
            width:
                8px;
        }


        .buyer-account-main::-webkit-scrollbar-track {
            background:
                transparent;
        }


        .buyer-account-main::-webkit-scrollbar-thumb {
            border:
                2px solid
                transparent;

            border-radius:
                999px;

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


        .buyer-account-main::-webkit-scrollbar-thumb:hover {
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
        | Sidebar Title
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-section-title {
            margin-bottom:
                12px;

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


        .buyer-sidebar-switch-title {
            margin-top:
                28px;
        }



        /*
        |--------------------------------------------------------------------------
        | Sidebar Navigation
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-nav {
            display:
                flex;

            flex-direction:
                column;

            gap:
                4px;
        }


        .buyer-sidebar-link {
            position:
                relative;

            width:
                100%;

            min-height:
                44px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                10px
                12px;

            border:
                0;

            border-radius:
                12px;

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
                background .16s ease;
        }


        .buyer-sidebar-link:hover {
            background:
                #E8F7EF;

            color:
                #0B3D2E;
        }


        .buyer-sidebar-link.active {
            background:
                #0B3D2E;

            color:
                #FFFFFF;
        }



        /*
        |--------------------------------------------------------------------------
        | Icons
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-icon {
            width:
                18px;

            height:
                18px;

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


        .buyer-sidebar-label {
            min-width:
                0;

            flex:
                1;
        }



        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-logout:hover {
            background:
                #FFF1F2;

            color:
                #D92D20;
        }



        /*
        |--------------------------------------------------------------------------
        | Mobile Navigation
        |--------------------------------------------------------------------------
        */

        .buyer-mobile-navigation {
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


        .buyer-mobile-menu-button {
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
        | Backdrop
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-backdrop {
            display:
                none;
        }



        /*
        |--------------------------------------------------------------------------
        | Tablet / Mobile
        |--------------------------------------------------------------------------
        */

        @media(max-width: 1023px) {

            .buyer-mobile-navigation {
                display:
                    block;
            }


            .buyer-account-shell {
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
            | Mobile Content
            |--------------------------------------------------------------------------
            */

            .buyer-account-main {
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
            | Mobile Drawer
            |--------------------------------------------------------------------------
            */

            .buyer-main-sidebar {
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


            .buyer-main-sidebar.is-open {
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

            .buyer-sidebar-backdrop {
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


            .buyer-sidebar-backdrop.is-open {
                opacity:
                    1;

                visibility:
                    visible;
            }

        }



        /*
        |--------------------------------------------------------------------------
        | Small Phone
        |--------------------------------------------------------------------------
        */

        @media(max-width: 480px) {

            .buyer-main-sidebar {
                width:
                    min(
                        86vw,
                        290px
                    );
            }


            .buyer-account-main {
                padding:
                    20px
                    13px;
            }

        }

    </style>


    {{-- =========================================================
        PAGE SPECIFIC STYLE
    ========================================================== --}}

    @stack('styles')

</head>


<body class="buyer-layout-body bg-[#F6F9F7] text-[#17251F]">


    {{-- =========================================================
        WEBSITE HEADER
    ========================================================== --}}

    <div class="buyer-site-header">

        @include('frontend.partials.header')

    </div>



    {{-- =========================================================
        MOBILE BUYER MENU
    ========================================================== --}}

    <div class="buyer-mobile-navigation">

        <button
            type="button"
            id="buyerSidebarToggle"
            class="buyer-mobile-menu-button"
            aria-expanded="false"
            aria-controls="buyerMainSidebar"
        >

            <i class="fa-solid fa-bars"></i>

            Buyer menu

        </button>

    </div>



    {{-- =========================================================
        MOBILE BACKDROP
    ========================================================== --}}

    <div
        id="buyerSidebarBackdrop"
        class="buyer-sidebar-backdrop"
        aria-hidden="true"
    ></div>



    {{-- =========================================================
        BUYER SHELL
    ========================================================== --}}

    <div class="buyer-account-shell">


        {{-- =====================================================
            FIXED BUYER SIDEBAR
        ====================================================== --}}

        @include(
            'buyer.partials.sidebar'
        )



        {{-- =====================================================
            SCROLLABLE CONTENT
        ====================================================== --}}

        <main
            id="buyerAccountMain"
            class="buyer-account-main"
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
        BUYER SIDEBAR SCRIPT
    ========================================================== --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const sidebar =
                    document.getElementById(
                        'buyerMainSidebar'
                    );


                const toggle =
                    document.getElementById(
                        'buyerSidebarToggle'
                    );


                const backdrop =
                    document.getElementById(
                        'buyerSidebarBackdrop'
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
                | Close Mobile Drawer After Link Click
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
                | Escape
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