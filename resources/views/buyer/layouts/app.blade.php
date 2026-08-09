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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Bricolage+Grotesque:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >


    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])


    @stack('styles')

</head>


<body class="bg-[#F6F9F7] text-[#17251F]">

    {{-- =========================================================
        PUBLIC HEADER
    ========================================================== --}}
    @include('frontend.partials.header')


    {{-- =========================================================
        SELLER APPLICATION
    ========================================================== --}}
    <div
        class="mx-auto
               flex
               min-h-[calc(100vh-66px)]
               max-w-[1200px]"
    >

        {{-- Sidebar --}}
        @include('buyer.partials.sidebar')


        {{-- Content --}}
        <main
            class="min-w-0
                   flex-1
                   px-4 py-8
                   sm:px-6
                   lg:px-8"
        >

            @yield('content')

        </main>

    </div>


    {{-- =========================================================
        ADMIN IMPERSONATION
    ========================================================== --}}
    @if(session()->has('impersonator_admin_id'))

        <div
            class="fixed
                   bottom-5
                   left-1/2
                   z-[999]
                   flex
                   -translate-x-1/2
                   items-center
                   gap-4
                   rounded-xl
                   bg-[#101915]
                   px-5 py-3
                   text-[12px]
                   text-white
                   shadow-2xl"
        >

            <span>
                <strong>Administrator mode:</strong>
                viewing {{ auth()->user()->name }}'s account.
            </span>


            <form
                method="POST"
                action="{{ route('impersonation.stop') }}"
            >

                @csrf


                <button
                    type="submit"
                    class="rounded-lg
                           bg-white
                           px-3 py-2
                           font-semibold
                           text-[#0B3D2E]"
                >
                    Return to admin
                </button>

            </form>

        </div>

    @endif


    @stack('scripts')

</body>

</html>