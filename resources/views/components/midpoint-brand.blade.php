@props([
    'variant' => 'header',
])


@php

    $logoPath =
        config(
            'midpoint.logo_path'
        );


    $logoUrl =
        $logoPath

        ? asset(
            ltrim(
                $logoPath,
                '/'
            )
        )

        : null;

@endphp



{{-- =========================================================
ADMIN SIDEBAR
========================================================== --}}

@if($variant === 'admin')

    <span class="admin-brand-text admin-brand-logo-wrapper">

        {{-- Light admin theme: keep the configured logo/fallback unchanged. --}}
        @if($logoUrl)

            <img src="{{ $logoUrl }}" alt="MidPoint" class="admin-brand-logo admin-brand-logo-light">

        @else

            <strong class="admin-brand-logo-light">
                Mid<span>Point</span>
            </strong>

        @endif


        {{-- Dark admin theme: show only the bundled sidebar logo. --}}
        <img src="{{ asset('logo/logo.png') }}" alt="Midpoint Logo"
            class="admin-brand-logo admin-brand-logo-dark h-10 w-auto">

    </span>


    <style>
        .admin-brand-logo-wrapper {
            display: inline-flex;
            align-items: center;
            min-width: 0;
        }

        .admin-brand-logo {
            width: auto;
            max-width: 190px;
            max-height: 50px;
            object-fit: contain;
            object-position: left center;
        }

        .admin-brand-logo-light {
            display: block;
        }

        .admin-brand-logo-dark {
            display: none;
        }

        html[data-admin-theme="dark"] .admin-brand-logo-light {
            display: none;
        }

        html[data-admin-theme="dark"] .admin-brand-logo-dark {
            display: block;
        }
    </style>



    {{-- =========================================================
    AUTH DARK BACKGROUND
    ========================================================== --}}

@elseif($variant === 'auth')


    @if($logoUrl)


        <span class="
                                        inline-flex
                                        rounded-[10px]
                                        bg-white
                                        px-2.5
                                        py-1.5
                                    ">

            <img src="{{ $logoUrl }}" alt="MidPoint" class="
                                            max-h-[34px]
                                            w-auto
                                            max-w-[190px]
                                            object-contain
                                        ">

        </span>


    @else


        <span class="
                                        grid
                                        h-8
                                        w-8
                                        place-items-center
                                        rounded-[10px]
                                        bg-gradient-to-br
                                        from-[#0B3D2E]
                                        to-[#12B76A]
                                        text-[15px]
                                        font-extrabold
                                        text-white
                                    ">

            M

        </span>


        <span>

            <span class="text-white">
                Mid
            </span><span class="text-[#C4B5FD]">Point</span>

        </span>


    @endif



    {{-- =========================================================
    FOOTER
    ========================================================== --}}

@elseif($variant === 'footer')


    @if($logoUrl)


        <span class="
                                        inline-flex
                                        rounded-[10px]
                                        bg-white
                                        px-2.5
                                        py-1.5
                                    ">

            <img src="{{ $logoUrl }}" alt="MidPoint" class="
                                            max-h-[34px]
                                            w-auto
                                            max-w-[190px]
                                            object-contain
                                        ">

        </span>


    @else


        <span class="
                                        grid
                                        h-8
                                        w-8
                                        place-items-center
                                        rounded-[10px]
                                        bg-gradient-to-br
                                        from-[#0B3D2E]
                                        to-[#12B76A]
                                        text-[15px]
                                        font-extrabold
                                        text-white
                                    ">

            M

        </span>


        <span>

            <span class="text-white">
                Mid
            </span><span class="text-[#C4B5FD]">Point</span>

        </span>


    @endif



    {{-- =========================================================
    PUBLIC HEADER
    ========================================================== --}}

@else


    @if($logoUrl)


        <img src="{{ $logoUrl }}" alt="MidPoint" class="
                                        max-h-[38px]
                                        w-auto
                                        max-w-[190px]
                                        object-contain
                                    ">


    @else


        <span class="
                                        grid
                                        h-8
                                        w-8
                                        place-items-center
                                        rounded-[10px]
                                        bg-gradient-to-br
                                        from-[#0B3D2E]
                                        to-[#12B76A]
                                        text-[15px]
                                        font-extrabold
                                        text-white
                                    ">

            M

        </span>


        <span>

            <span class="text-[#0B3D2E]">

                Mid

            </span><span class="text-[#7A5AF8]">Point</span>

        </span>


    @endif


@endif