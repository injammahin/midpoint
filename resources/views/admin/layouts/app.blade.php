<!DOCTYPE html>

<html
    lang="en"
    data-admin-theme="dark"
>

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
        @yield('title', 'Admin Dashboard') | MidPoint
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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Bricolage+Grotesque:wght@500;600;700&display=swap"
        rel="stylesheet"
    >


    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])


    @stack('styles')

</head>


<body class="admin-body">

    <div
        id="adminShell"
        class="admin-shell"
    >

        {{-- Sidebar --}}
        @include(
            'admin.partials.sidebar.index'
        )


        {{-- Overlay for mobile --}}
        <div
            id="adminSidebarOverlay"
            class="admin-sidebar-overlay"
        ></div>


        <div class="admin-main">

            {{-- Header --}}
            @include(
                'admin.partials.header'
            )


            {{-- Page content --}}
            <main class="admin-content">

                @yield('content')

            </main>

        </div>

    </div>


    @stack('scripts')

</body>

</html>