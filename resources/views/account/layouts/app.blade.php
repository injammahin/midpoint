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
        @yield('title', 'Dashboard') | Midpoint
    </title>


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


    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/account-dashboard.css',
        'resources/js/account-dashboard.js',
    ])


    @stack('styles')

</head>


<body class="Midpoint-account-body">


    {{-- =========================================================
        WEBSITE HEADER
    ========================================================== --}}

    @include('frontend.partials.header')



    {{-- =========================================================
        MOBILE ACCOUNT MENU
    ========================================================== --}}

    <div class="account-mobile-navigation">

        <button
            type="button"
            id="accountSidebarToggle"
            class="account-mobile-menu-button"
            aria-label="Open account navigation"
            aria-expanded="false"
        >

            <i class="fa-solid fa-bars"></i>

            <span>
                Account menu
            </span>

        </button>

    </div>



    {{-- =========================================================
        MOBILE BACKDROP
    ========================================================== --}}

    <div
        id="accountSidebarBackdrop"
        class="account-sidebar-backdrop"
    ></div>



    {{-- =========================================================
        DASHBOARD SHELL
    ========================================================== --}}

    <div class="account-shell">

        @include(
            'account.partials.sidebar',
            [
                'dashboardRole' => $dashboardRole,
            ]
        )


        <main class="account-main">

            @yield('content')

        </main>

    </div>



    {{-- =========================================================
        TOAST
    ========================================================== --}}

    <div
        id="accountToast"
        class="account-toast"
        role="status"
        aria-live="polite"
    ></div>



    @stack('scripts')

</body>

</html>