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

@if(
    session()->has(
        'impersonator_admin_id'
    )
)

    <div
        style="
            position:fixed;
            left:50%;
            top:10px;
            transform:translateX(-50%);
            z-index:99999;
            display:flex;
            align-items:center;
            gap:12px;
            background:#172033;
            color:#fff;
            padding:9px 12px;
            border-radius:10px;
            box-shadow:0 10px 30px rgba(15,23,42,.22);
            font:600 12px/1.2 Inter,sans-serif;
        "
    >

        <span>

            <i
                class="fa-solid fa-user-secret"
                style="margin-right:6px;"
            ></i>

            Viewing

            {{ auth()->user()->name }}

            as an admin user

        </span>


        <form
            method="POST"
            action="{{ route('impersonation.stop') }}"
            style="margin:0;"
        >

            @csrf


            <button
                type="submit"
                style="
                    border:0;
                    border-radius:7px;
                    background:#fff;
                    color:#172033;
                    padding:6px 9px;
                    font-weight:800;
                    cursor:pointer;
                "
            >

                Return to Super Admin

            </button>

        </form>

    </div>

@endif
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