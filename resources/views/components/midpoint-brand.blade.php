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





    <span class="admin-brand-text">


        @if($logoUrl)


            <img

                src="{{ $logoUrl }}"

                alt="MidPoint"

                style="
                    display:block;
                    width:auto;
                    max-width:190px;
                    max-height:50px;
                    object-fit:contain;
                    object-position:left center;
                "

            >


        @else


            <strong>

                Mid<span>Point</span>

            </strong>


        @endif





    </span>



{{-- =========================================================
    AUTH DARK BACKGROUND
========================================================== --}}

@elseif($variant === 'auth')


    @if($logoUrl)


        <span
            class="
                inline-flex
                rounded-[10px]
                bg-white
                px-2.5
                py-1.5
            "
        >

            <img

                src="{{ $logoUrl }}"

                alt="MidPoint"

                class="
                    max-h-[34px]
                    w-auto
                    max-w-[190px]
                    object-contain
                "

            >

        </span>


    @else


        <span
            class="
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
            "
        >

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


        <span
            class="
                inline-flex
                rounded-[10px]
                bg-white
                px-2.5
                py-1.5
            "
        >

            <img

                src="{{ $logoUrl }}"

                alt="MidPoint"

                class="
                    max-h-[34px]
                    w-auto
                    max-w-[190px]
                    object-contain
                "

            >

        </span>


    @else


        <span
            class="
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
            "
        >

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


        <img

            src="{{ $logoUrl }}"

            alt="MidPoint"

            class="
                max-h-[38px]
                w-auto
                max-w-[190px]
                object-contain
            "

        >


    @else


        <span
            class="
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
            "
        >

            M

        </span>


        <span>

            <span class="text-[#0B3D2E]">

                Mid

            </span><span class="text-[#7A5AF8]">Point</span>

        </span>


    @endif


@endif