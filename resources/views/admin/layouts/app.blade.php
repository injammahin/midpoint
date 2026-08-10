<!DOCTYPE html>


<html
    lang="en"
    data-admin-theme="light"
>


<head>

    <meta charset="UTF-8">


    {{-- =====================================================
        ADMIN THEME - APPLY BEFORE FIRST PAINT
    ====================================================== --}}

    <script>
        (function () {

            try {

                /*
                |--------------------------------------------------------------------------
                | Saved Theme
                |--------------------------------------------------------------------------
                */

                const savedTheme =
                    localStorage.getItem(
                        'midpoint_admin_theme'
                    );


                /*
                |--------------------------------------------------------------------------
                | Resolve Theme
                |--------------------------------------------------------------------------
                */

                const theme =
                    savedTheme === 'dark'
                    ||
                    savedTheme === 'light'

                        ? savedTheme

                        : 'light';


                /*
                |--------------------------------------------------------------------------
                | Apply Before CSS Paint
                |--------------------------------------------------------------------------
                */

                document.documentElement
                    .setAttribute(
                        'data-admin-theme',
                        theme
                    );


                /*
                |--------------------------------------------------------------------------
                | Browser Native UI
                |--------------------------------------------------------------------------
                */

                document.documentElement.style.colorScheme =
                    theme;


            } catch (error) {

                /*
                |--------------------------------------------------------------------------
                | Safe Light Fallback
                |--------------------------------------------------------------------------
                */

                document.documentElement
                    .setAttribute(
                        'data-admin-theme',
                        'light'
                    );


                document.documentElement.style.colorScheme =
                    'light';
            }

        })();
    </script>


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >


    <title>
        @yield('title', 'Admin Dashboard') | MidPoint
    </title>


    {{-- =====================================================
        Fonts
    ====================================================== --}}

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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Bricolage+Grotesque:wght@500;600;700&display=swap"
        rel="stylesheet"
    >


    {{-- =====================================================
        Admin Assets
    ====================================================== --}}

    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])


    {{-- =====================================================
        Page Specific CSS
    ====================================================== --}}

    @stack('styles')

</head>



<body class="admin-body">


    <div
        id="adminShell"
        class="admin-shell"
    >


        {{-- =================================================
            Sidebar
        ================================================== --}}

        @include(
            'admin.partials.sidebar.index'
        )


        {{-- =================================================
            Mobile Sidebar Overlay
        ================================================== --}}

        <div
            id="adminSidebarOverlay"
            class="admin-sidebar-overlay"
            aria-hidden="true"
        ></div>


        {{-- =================================================
            Main
        ================================================== --}}

        <div class="admin-main">


            {{-- =============================================
                Header
            ============================================== --}}

            @include(
                'admin.partials.header'
            )


            {{-- =============================================
                Page Content
            ============================================== --}}

            <main
                class="admin-content"
                id="adminContent"
            >

                @yield('content')

            </main>

        </div>

    </div>


    {{-- =====================================================
        Page Specific Scripts
    ====================================================== --}}

    @stack('scripts')

</body>


</html>