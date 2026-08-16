
@if(session()->has('impersonator_admin_id'))

    <div
        class="fixed
               bottom-5
               left-1/2
               z-[9999]
               flex
               -translate-x-1/2
               items-center
               gap-4
               rounded-xl
               bg-[#111827]
               px-5 py-3
               text-[12px]
               text-white
               shadow-2xl"
    >

        <div>

            <strong>
                Administrator mode
            </strong>

            <span class="ml-1 text-slate-300">
                You are viewing this user's account.
            </span>

        </div>


        <form
            method="POST"
            action="{{ route('impersonation.stop') }}"
        >

            @csrf


            <button
                type="submit"
                class="rounded-lg
                       bg-white
                       px-3 py-1.5
                       font-semibold
                       text-slate-900"
            >
                Return to admin
            </button>

        </form>

    </div>

@endif
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
        @yield('title', 'Midpoint — Buy with confidence. Sell with confidence.')
    </title>

    <meta
        name="description"
        content="@yield('meta_description', 'The trusted middle for online transactions in Nigeria. Buy with confidence. Sell with confidence.')"
    >

    {{-- Google Fonts --}}
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
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body
    class="min-h-screen bg-[#F6F9F7] font-['Inter'] text-[#0D120F] antialiased"
>

    {{-- ============================
        PUBLIC HEADER
    ============================= --}}
    @include('frontend.partials.header')


    {{-- ============================
        PAGE CONTENT
    ============================= --}}
    <main>
        @yield('content')
    </main>


    {{-- ============================
        PUBLIC FOOTER
    ============================= --}}
    @hasSection('hide_footer')
    @else
        @include('frontend.partials.footer')
    @endif


    @stack('scripts')

</body>

</html>