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
        @yield('title', 'Buyer Dashboard') | MidPoint
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
        | Buyer Dashboard Components
        |--------------------------------------------------------------------------
        |
        | Buyer Dashboard already uses classes from account-dashboard.css.
        |
        */

        'resources/css/account-dashboard.css',
        'resources/js/account-dashboard.js',
    ])


    {{-- =========================================================
        UNIFIED BUYER SIDEBAR STYLE
    ========================================================== --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | Buyer Shell
        |--------------------------------------------------------------------------
        */

        .buyer-account-shell {
            width: 100%;
            max-width: 1200px;
            min-height: calc(100vh - 66px);

            display: flex;

            margin: 0 auto;
        }


        .buyer-account-main {
            min-width: 0;
            flex: 1;

            padding: 32px;
        }



        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        */

        .buyer-main-sidebar {
            position: relative;

            width: 220px;
            flex: 0 0 220px;

            padding:
                32px
                20px
                32px
                0;

            border-right: 1px solid #E4EAE6;

            overflow: visible;
        }



        /*
        |--------------------------------------------------------------------------
        | Section Title
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-section-title {
            margin-bottom: 12px;

            padding:
                0
                12px;

            color: #98A49E;

            font-size: 10px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .12em;
        }


        .buyer-sidebar-switch-title {
            margin-top: 28px;
        }



        /*
        |--------------------------------------------------------------------------
        | Navigation
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-nav {
            display: flex;
            flex-direction: column;

            gap: 4px;
        }


        .buyer-sidebar-link {
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

            background: transparent;

            color: #5A6660;

            font-family:
                'Inter',
                sans-serif;

            font-size: 13px;
            font-weight: 600;

            text-align: left;
            text-decoration: none;

            cursor: pointer;

            transition:
                color .16s ease,
                background .16s ease;
        }


        .buyer-sidebar-link:hover {
            background: #E8F7EF;

            color: #0B3D2E;
        }


        .buyer-sidebar-link.active {
            background: #0B3D2E;

            color: #FFFFFF;
        }



        /*
        |--------------------------------------------------------------------------
        | Icon
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-icon {
            width: 18px;
            height: 18px;

            flex: 0 0 18px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
        }


        .buyer-sidebar-label {
            min-width: 0;

            flex: 1;
        }



        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        .buyer-sidebar-logout:hover {
            background: #FFF1F2;

            color: #D92D20;
        }



        /*
        |--------------------------------------------------------------------------
        | Mobile Navigation
        |--------------------------------------------------------------------------
        */

        .buyer-mobile-navigation {
            display: none;

            padding:
                12px
                16px;

            border-bottom: 1px solid #E4EAE6;

            background: #F6F9F7;
        }


        .buyer-mobile-menu-button {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            padding:
                9px
                12px;

            border: 1px solid #DDE5E1;
            border-radius: 10px;

            background: #FFFFFF;

            color: #0B3D2E;

            font-size: 12px;
            font-weight: 700;

            cursor: pointer;
        }


        .buyer-sidebar-backdrop {
            display: none;
        }



        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 1023px) {

            .buyer-mobile-navigation {
                display: block;
            }


            .buyer-account-shell {
                display: block;
            }


            .buyer-main-sidebar {
                position: fixed;

                left: 0;
                top: 0;
                bottom: 0;

                z-index: 10000;

                width: 260px;

                padding:
                    90px
                    20px
                    25px;

                border-right: 1px solid #E4EAE6;

                background: #F6F9F7;

                box-shadow:
                    15px
                    0
                    40px
                    rgba(11, 61, 46, .12);

                transform:
                    translateX(-105%);

                transition:
                    transform .2s ease;
            }


            .buyer-main-sidebar.is-open {
                transform:
                    translateX(0);
            }


            .buyer-sidebar-backdrop {
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


            .buyer-sidebar-backdrop.is-open {
                opacity: 1;
                visibility: visible;
            }


            .buyer-account-main {
                width: 100%;

                padding:
                    24px
                    16px;
            }

        }

    </style>


    {{-- =========================================================
        PAGE SPECIFIC STYLE
    ========================================================== --}}

    @stack('styles')

</head>


<body class="bg-[#F6F9F7] text-[#17251F]">


    {{-- =========================================================
        WEBSITE HEADER
    ========================================================== --}}

    @include('frontend.partials.header')



    {{-- =========================================================
        MOBILE BUYER MENU
    ========================================================== --}}

    <div class="buyer-mobile-navigation">

        <button
            type="button"

            id="buyerSidebarToggle"

            class="buyer-mobile-menu-button"

            aria-expanded="false"
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
    ></div>



    {{-- =========================================================
        BUYER SHELL
    ========================================================== --}}

    <div class="buyer-account-shell">


        {{-- =====================================================
            SIDEBAR
        ====================================================== --}}

        @include(
            'buyer.partials.sidebar'
        )



        {{-- =====================================================
            CONTENT
        ====================================================== --}}

        <main class="buyer-account-main">

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
        MOBILE SIDEBAR SCRIPT
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
                | Open
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



                /*
                |--------------------------------------------------------------------------
                | Close
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