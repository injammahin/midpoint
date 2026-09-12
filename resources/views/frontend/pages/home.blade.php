@extends('frontend.layouts.app')


@section(
    'title',
    'Midpoint — Buy with confidence. Sell with confidence.'
)


@section(
    'meta_description',
    'The safe middle for online transactions in Nigeria. Midpoint holds buyer payments securely until the buyer confirms and accepts the item.'
)


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Home Theme
    |--------------------------------------------------------------------------
    */

    .Midpoint-home {

        --forest:
            #0B3D2E;

        --forest-2:
            #0E4A38;

        --emerald:
            #12B76A;

        --mint:
            #E8F7EF;

        --purple:
            #7A5AF8;

        --lav:
            #F1EDFE;

        --ink:
            #0D120F;

        --slate:
            #5A6660;

        --line:
            #E4EAE6;

        --paper:
            #F6F9F7;

        --amber:
            #F79009;

    }


    .Midpoint-home .display-font {

        font-family:
            'Bricolage Grotesque',
            sans-serif;

    }


    /*
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    */

    .Midpoint-hero {

        position:
            relative;

        overflow:
            hidden;

        color:
            #FFFFFF;

        background:

            linear-gradient(
                160deg,
                #0B3D2E 0%,
                #0E4A38 55%,
                #123B54 130%
            );

    }


    .Midpoint-hero::before {

        content:
            '';

        position:
            absolute;

        inset:
            0;

        opacity:
            .12;

        pointer-events:
            none;

        background-image:

            linear-gradient(
                rgba(
                    255,
                    255,
                    255,
                    .06
                ) 1px,
                transparent 1px
            ),

            linear-gradient(
                90deg,
                rgba(
                    255,
                    255,
                    255,
                    .06
                ) 1px,
                transparent 1px
            );

        background-size:
            42px 42px;

    }


    .Midpoint-hero::after {

        content:
            '';

        position:
            absolute;

        top:
            -170px;

        right:
            -180px;

        width:
            520px;

        height:
            520px;

        pointer-events:
            none;

        border-radius:
            50%;

        background:

            radial-gradient(
                circle,
                rgba(
                    122,
                    90,
                    248,
                    .28
                ),
                transparent 67%
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Eyebrow
    |--------------------------------------------------------------------------
    */

    .home-eyebrow::before {

        content:
            '';

        width:
            22px;

        height:
            2px;

        flex:
            none;

        border-radius:
            9999px;

        background:
            #12B76A;

    }


    /*
    |--------------------------------------------------------------------------
    | Hero Image
    |--------------------------------------------------------------------------
    |
    | Image location:
    | public/img/hero image.png
    |
    | Important:
    | This block only controls the hero image.
    | Testimonial, business, FAQ and other existing styles are untouched.
    |
    */

    .mp-hero-image-wrap {

        position:
            relative;

        width:
            100%;

        max-width:
            650px;

        margin-left:
            auto;

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        overflow:
            visible;

        isolation:
            isolate;

    }


    .mp-hero-image-wrap::before {

        content:
            '';

        position:
            absolute;

        left:
            50%;

        top:
            52%;

        z-index:
            -1;

        width:
            88%;

        aspect-ratio:
            1 / 1;

        transform:
            translate(
                -50%,
                -50%
            );

        border-radius:
            50%;

        pointer-events:
            none;

        background:

            radial-gradient(
                circle,
                rgba(
                    126,
                    240,
                    182,
                    .15
                ) 0%,
                rgba(
                    18,
                    183,
                    106,
                    .08
                ) 38%,
                transparent 70%
            );

        filter:
            blur(12px);

    }


    .mp-hero-image {

        position:
            relative;

        z-index:
            2;

        display:
            block;

        width:
            100%;

        max-width:
            none;

        height:
            auto;

        object-fit:
            contain;

        object-position:
            center;

        filter:

            drop-shadow(
                0 24px 36px
                rgba(
                    0,
                    0,
                    0,
                    .20
                )
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Desktop Hero Image
    |--------------------------------------------------------------------------
    */

    @media(min-width: 961px) {

        .mp-hero-image-wrap {

            min-height:
                430px;

        }


        .mp-hero-image {

            width:
                118%;

            transform:
                translate(
                    10px,
                    4px
                );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Tablet Hero Layout
    |--------------------------------------------------------------------------
    |
    | Tablet only:
    | 641px - 960px
    |
    | Center:
    | - badge
    | - title
    | - description
    | - buttons
    | - stats
    | - hero image
    |
    | Desktop and mobile stay unchanged.
    |
    */

    @media(min-width: 641px) and (max-width: 960px) {

        .Midpoint-hero-inner {

            row-gap:
                18px;

            padding-top:
                48px !important;

            padding-bottom:
                52px !important;

        }


        .Midpoint-hero-copy {

            width:
                100%;

            max-width:
                700px;

            margin-left:
                auto;

            margin-right:
                auto;

            text-align:
                center;

        }


        .Midpoint-hero-badge {

            margin-left:
                auto;

            margin-right:
                auto;

        }


        .Midpoint-hero-title {

            max-width:
                650px !important;

            margin-left:
                auto;

            margin-right:
                auto;

        }


        .Midpoint-hero-description {

            max-width:
                590px !important;

            margin-left:
                auto;

            margin-right:
                auto;

        }


        .Midpoint-hero-actions {

            justify-content:
                center;

        }


        .Midpoint-hero-stats {

            justify-content:
                center;

            text-align:
                center;

        }


        .mp-hero-image-wrap {

            max-width:
                650px;

            margin:
                4px auto 0;

        }


        .mp-hero-image {

            width:
                min(
                    100%,
                    610px
                );

            transform:
                none;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Hero Image
    |--------------------------------------------------------------------------
    */

    @media(max-width: 640px) {

        .mp-hero-image-wrap {

            max-width:
                520px;

            margin:
                8px auto 0;

        }


        .mp-hero-image-wrap::before {

            width:
                94%;

        }


        .mp-hero-image {

            width:
                100%;

            max-width:
                500px;

            transform:
                none;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Business Cards
    |--------------------------------------------------------------------------
    */

    .home-business-card {

        transition:

            transform
            .2s ease,

            box-shadow
            .2s ease,

            border-color
            .2s ease;

    }


    .home-business-card:hover {

        transform:
            translateY(-3px);

        box-shadow:

            0 18px 50px -16px
            rgba(
                11,
                61,
                46,
                .22
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Testimonial Slider
    |--------------------------------------------------------------------------
    */

    .home-testimonial-shell {

        position:
            relative;

        padding:
            0 48px;

    }


    .home-testimonial-viewport {

        overflow:
            hidden;

    }


    .home-testimonial-track {

        display:
            flex;

        gap:
            18px;

        will-change:
            transform;

        transition:

            transform
            .45s
            cubic-bezier(
                .2,
                .7,
                .2,
                1
            );

    }


    .home-testimonial-slide {

        flex:

            0
            0
            calc(
                (100% - 36px)
                /
                3
            );

        min-width:
            0;

    }


    .home-testimonial-card {

        height:
            100%;

        min-height:
            220px;

        display:
            flex;

        flex-direction:
            column;

        padding:
            24px;

        border:
            1px solid
            rgba(
                255,
                255,
                255,
                .13
            );

        border-radius:
            18px;

        background:
            rgba(
                255,
                255,
                255,
                .06
            );

        box-shadow:

            inset
            0 1px 0
            rgba(
                255,
                255,
                255,
                .03
            );

    }


    .home-testimonial-card
    .stars {

        color:
            #F5B301;

        font-size:
            13px;

        letter-spacing:
            2px;

    }


    .home-testimonial-card
    blockquote {

        margin:
            13px 0 18px;

        color:
            #D7E5DE;

        font-size:
            14px;

        line-height:
            1.65;

    }


    .home-testimonial-person {

        margin-top:
            auto;

        display:
            flex;

        align-items:
            center;

        gap:
            10px;

    }


    .home-testimonial-avatar {

        width:
            38px;

        height:
            38px;

        flex:
            0 0 38px;

        display:
            grid;

        place-items:
            center;

        border-radius:
            11px;

        color:
            #FFFFFF;

        font-size:
            12px;

        font-weight:
            800;

    }


    .home-testimonial-person
    strong {

        display:
            block;

        color:
            #FFFFFF;

        font-size:
            13px;

    }


    .home-testimonial-person
    span {

        display:
            block;

        margin-top:
            2px;

        color:
            #9DBBAF;

        font-size:
            12px;

    }


    .home-testimonial-arrow {

        position:
            absolute;

        top:
            50%;

        z-index:
            5;

        width:
            40px;

        height:
            40px;

        display:
            grid;

        place-items:
            center;

        transform:
            translateY(-50%);

        border:
            1px solid
            rgba(
                255,
                255,
                255,
                .17
            );

        border-radius:
            50%;

        color:
            #7EF0B6;

        background:
            rgba(
                255,
                255,
                255,
                .08
            );

        cursor:
            pointer;

        transition:

            background
            .2s ease,

            transform
            .2s ease;

    }


    .home-testimonial-arrow:hover {

        background:
            rgba(
                255,
                255,
                255,
                .14
            );

        transform:
            translateY(-50%)
            scale(1.05);

    }


    .home-testimonial-arrow.prev {

        left:
            0;

    }


    .home-testimonial-arrow.next {

        right:
            0;

    }


    .home-testimonial-arrow[hidden] {

        display:
            none;

    }


    /*
    |--------------------------------------------------------------------------
    | FAQ
    |--------------------------------------------------------------------------
    */

    .home-faq summary {

        list-style:
            none;

    }


    .home-faq summary::-webkit-details-marker {

        display:
            none;

    }


    .home-faq summary::after {

        content:
            '+';

        margin-left:
            20px;

        flex:
            none;

        color:
            #12B76A;

        font-size:
            20px;

        line-height:
            1;

    }


    .home-faq
    details[open]
    summary::after {

        content:
            '–';

    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 960px) {

        .home-testimonial-slide {

            flex-basis:

                calc(
                    (100% - 18px)
                    /
                    2
                );

        }

    }


    @media(max-width: 640px) {

        .Midpoint-hero::after {

            top:
                -190px;

            right:
                -240px;

            width:
                440px;

            height:
                440px;

        }


        .home-testimonial-shell {

            padding:
                0 44px;

        }


        .home-testimonial-slide {

            flex-basis:
                100%;

        }


        .home-testimonial-card {

            min-height:
                235px;

            padding:
                21px;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Reduced Motion
    |--------------------------------------------------------------------------
    */

    @media(
        prefers-reduced-motion:
        reduce
    ) {

        .home-testimonial-track {

            transition:
                none;

        }

    }

</style>

@endpush



@section('content')


    <div class="Midpoint-home">


        {{-- =========================================================
            HERO
        ========================================================== --}}

        <section class="Midpoint-hero">


            <div
                class="
                    Midpoint-hero-inner
                    relative
                    z-10
                    mx-auto
                    grid
                    max-w-[1160px]
                    grid-cols-1
                    items-center
                    gap-[42px]
                    px-[22px]
                    py-[56px]
                    min-[961px]:grid-cols-[1.02fr_.98fr]
                    min-[961px]:py-[76px]
                    min-[961px]:pb-[84px]
                "
            >


                {{-- Hero Text --}}

                <div class="Midpoint-hero-copy">


                    <div
                        class="
                            Midpoint-hero-badge
                            inline-flex
                            items-center
                            gap-2
                            rounded-full
                            border
                            border-white/[.18]
                            bg-white/10
                            px-[15px]
                            py-[7px]
                            text-[12px]
                            font-semibold
                            sm:text-[13px]
                        "
                    >

                        <span>
                            🛡️
                        </span>


                        <span>
                            {{ $home->hero_badge }}
                        </span>

                    </div>



                    <h1
                        class="
                            Midpoint-hero-title
                            display-font
                            my-[16px]
                            max-w-[580px]
                            text-[34px]
                            font-extrabold
                            leading-[1.06]
                            tracking-[-0.02em]
                            sm:text-[42px]
                            lg:text-[50px]
                            xl:text-[56px]
                        "
                    >

                        {{ $home->hero_title_before }}

                        <span class="text-[#7EF0B6]">

                            {{ $home->hero_title_highlight }}

                        </span>{{ $home->hero_title_after }}

                    </h1>



                    <p
                        class="
                            Midpoint-hero-description
                            max-w-[480px]
                            text-[15px]
                            leading-[1.65]
                            text-[#C8DAD2]
                            sm:text-[17.5px]
                        "
                    >

                        {{ $home->hero_description }}

                    </p>



                    {{-- Buttons --}}

                    <div
                        class="
                            Midpoint-hero-actions
                            mt-[26px]
                            flex
                            flex-wrap
                            gap-3
                        "
                    >


                        <a
                            href="{{
        $home->hero_primary_button_url
                            }}"

                            class="
                                inline-flex
                                min-h-[52px]
                                items-center
                                justify-center
                                rounded-[14px]
                                bg-[#12B76A]
                                px-7
                                py-[15px]
                                text-[15px]
                                font-semibold
                                text-white
                                transition
                                hover:-translate-y-px
                                hover:brightness-105
                                sm:text-[16px]
                            "
                        >

                            {{ $home->hero_primary_button_text }}

                        </a>



                        <a
                            href="{{
        $home->hero_secondary_button_url
                            }}"

                            class="
                                inline-flex
                                min-h-[52px]
                                items-center
                                justify-center
                                rounded-[14px]
                                border
                                border-white/20
                                bg-white/10
                                px-7
                                py-[15px]
                                text-[15px]
                                font-semibold
                                text-white
                                transition
                                hover:bg-white/[.14]
                                sm:text-[16px]
                            "
                        >

                            {{ $home->hero_secondary_button_text }}

                        </a>


                    </div>



                    {{-- Stats --}}

                    <div
                        class="
                            Midpoint-hero-stats
                            mt-[38px]
                            flex
                            flex-wrap
                            gap-x-[34px]
                            gap-y-5
                        "
                    >


                        @foreach ([
                                [
                                    $home->stat_one_value,
                                    $home->stat_one_label,
                                ],
                                [
                                    $home->stat_two_value,
                                    $home->stat_two_label,
                                ],
                                [
                                    $home->stat_three_value,
                                    $home->stat_three_label,
                                ],
                            ] as [$value, $label])


                                                    <div>


                                                        <strong
                                                            class="
                                                                display-font
                                                                block
                                                                text-[34px]
                                                                font-extrabold
                                                                sm:text-[34px]
                                                            "
                                                        >

                                                            {{ $value }}

                                                        </strong>


                                                        <span
                                                            class="
                                                                text-[12.5px]
                                                                text-[#9DBBAF]
                                                            "
                                                        >

                                                            {{ $label }}

                                                        </span>


                                                    </div>


                        @endforeach


                    </div>


                </div>



                {{-- =====================================================
                    MIDPOINT HERO IMAGE
                ====================================================== --}}

                <div
                    class="mp-hero-image-wrap"
                    aria-label="Midpoint securely connects buyers and sellers."
                >

                    <img
                        src="{{ asset('img/hero image.png') }}"
                        alt="Buyer and seller connected securely through Midpoint"
                        class="mp-hero-image"
                        width="1254"
                        height="1254"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >

                </div>


            </div>


        </section>



        {{-- =========================================================
            THREE SIMPLE STEPS
        ========================================================== --}}

        <section
            class="
                bg-[#F6F9F7]
                py-[52px]
                sm:py-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[1160px]
                    px-[22px]
                "
            >


                <div
                    class="
                        mx-auto
                        max-w-[620px]
                        text-center
                    "
                >


                    <div
                        class="
                            home-eyebrow
                            mb-[14px]
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            text-[12.5px]
                            font-bold
                            uppercase
                            tracking-[.12em]
                            text-[#12B76A]
                        "
                    >

                        {{ $home->steps_eyebrow }}

                    </div>


                    <h2
                        class="
                            display-font
                            text-[27px]
                            font-extrabold
                            leading-[1.15]
                            sm:text-[34px]
                            lg:text-[38px]
                        "
                    >

                        {{ $home->steps_title }}

                    </h2>


                    <p
                        class="
                            mt-[10px]
                            text-[15px]
                            leading-[1.6]
                            text-[#5A6660]
                            sm:text-[16px]
                        "
                    >

                        {{ $home->steps_description }}

                    </p>


                </div>



                <div
                    class="
                        mt-10
                        grid
                        grid-cols-1
                        gap-5
                        md:grid-cols-2
                        min-[961px]:grid-cols-3
                    "
                >


                    @foreach ([
        [
            1,
            $home->step_one_title,
            $home->step_one_description,
        ],
        [
            2,
            $home->step_two_title,
            $home->step_two_description,
        ],
        [
            3,
            $home->step_three_title,
            $home->step_three_description,
        ],
    ] as [$number, $title, $description])


                        <article
                            class="
                                rounded-[18px]
                                border
                                border-[#E4EAE6]
                                bg-white
                                p-[26px]
                                shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]
                                {{
            $number === 3
            ? 'md:col-span-2 min-[961px]:col-span-1'
            : ''
                                }}
                            "
                        >


                            <div
                                class="
                                    display-font
                                    mb-4
                                    grid
                                    h-[42px]
                                    w-[42px]
                                    place-items-center
                                    rounded-[13px]
                                    bg-[#0B3D2E]
                                    text-[18px]
                                    font-extrabold
                                    text-[#7EF0B6]
                                "
                            >

                                {{ $number }}

                            </div>


                            <h3
                                class="
                                    display-font
                                    mb-2
                                    text-[18px]
                                    font-bold
                                "
                            >

                                {{ $title }}

                            </h3>


                            <p
                                class="
                                    text-[14px]
                                    leading-[1.6]
                                    text-[#5A6660]
                                "
                            >

                                {{ $description }}

                            </p>


                        </article>


                    @endforeach


                </div>


            </div>


        </section>



        {{-- =========================================================
            WHY MIDPOINT
        ========================================================== --}}

        <section
            class="
                border-y
                border-[#E4EAE6]
                bg-white
                py-[52px]
                sm:py-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[1160px]
                    px-[22px]
                "
            >


                <div class="max-w-[620px]">


                    <div
                        class="
                            home-eyebrow
                            mb-[14px]
                            inline-flex
                            items-center
                            gap-2
                            text-[12.5px]
                            font-bold
                            uppercase
                            tracking-[.12em]
                            text-[#12B76A]
                        "
                    >

                        {{ $home->why_eyebrow }}

                    </div>


                    <h2
                        class="
                            display-font
                            max-w-[560px]
                            text-[27px]
                            font-extrabold
                            sm:text-[34px]
                            lg:text-[38px]
                        "
                    >

                        {{ $home->why_title }}

                    </h2>


                </div>



                <div
                    class="
                        mt-[42px]
                        grid
                        grid-cols-1
                        gap-x-5
                        gap-y-8
                        sm:grid-cols-2
                        min-[961px]:grid-cols-4
                    "
                >


                    @foreach ([
        [
            $home->why_one_icon,
            $home->why_one_title,
            $home->why_one_description,
            '#E8F7EF',
        ],
        [
            $home->why_two_icon,
            $home->why_two_title,
            $home->why_two_description,
            '#F1EDFE',
        ],
        [
            $home->why_three_icon,
            $home->why_three_title,
            $home->why_three_description,
            '#E8F7EF',
        ],
        [
            $home->why_four_icon,
            $home->why_four_title,
            $home->why_four_description,
            '#F1EDFE',
        ],
    ] as [$icon, $title, $description, $background])


                        <div>


                            <div
                                class="
                                    grid
                                    h-11
                                    w-11
                                    place-items-center
                                    rounded-[13px]
                                    text-[20px]
                                "

                                style="
                                    background:
                                    {{ $background }};
                                "
                            >

                                {{ $icon }}

                            </div>


                            <h3
                                class="
                                    display-font
                                    mb-[6px]
                                    mt-3
                                    text-[16px]
                                    font-bold
                                "
                            >

                                {{ $title }}

                            </h3>


                            <p
                                class="
                                    text-[13px]
                                    leading-[1.6]
                                    text-[#5A6660]
                                "
                            >

                                {{ $description }}

                            </p>


                        </div>


                    @endforeach


                </div>


            </div>


        </section>



        {{-- =========================================================
            FEATURED BUSINESSES
        ========================================================== --}}

        <section
            class="
                bg-[#F6F9F7]
                py-[52px]
                sm:py-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[1160px]
                    px-[22px]
                "
            >


                <div
                    class="
                        mb-[42px]
                        flex
                        flex-col
                        gap-5
                        sm:flex-row
                        sm:items-end
                        sm:justify-between
                    "
                >


                    <div>


                        <div
                            class="
                                home-eyebrow
                                mb-[14px]
                                inline-flex
                                items-center
                                gap-2
                                text-[12.5px]
                                font-bold
                                uppercase
                                tracking-[.12em]
                                text-[#12B76A]
                            "
                        >

                            {{ $home->featured_eyebrow }}

                        </div>


                        <h2
                            class="
                                display-font
                                text-[27px]
                                font-extrabold
                                sm:text-[34px]
                                lg:text-[38px]
                            "
                        >

                            {{ $home->featured_title }}

                        </h2>


                    </div>



                    <a
                        href="{{ route('featured-businesses') }}"

                        class="
                            inline-flex
                            min-h-[43px]
                            w-fit
                            items-center
                            justify-center
                            rounded-xl
                            border-[1.5px]
                            border-[#E4EAE6]
                            bg-white
                            px-5
                            py-[11px]
                            text-[14px]
                            font-semibold
                            text-[#0B3D2E]
                            transition
                            hover:border-[#12B76A]
                            hover:text-[#12B76A]
                        "
                    >

                        {{ $home->featured_view_all_text }}

                    </a>


                </div>



                @if(
        $featuredBusinesses->count()
    )


                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-5
                            md:grid-cols-2
                            min-[961px]:grid-cols-3
                        "
                    >


                        @foreach ($featuredBusinesses as $seller)


                            @php

            /*
            |--------------------------------------------------------------------------
            | Seller Data
            |--------------------------------------------------------------------------
            */

            $subscription =
                $seller
                    ->activeSellerSubscription;


            $application =
                optional(
                    $subscription
                )->application;


            $profile =
                $seller
                    ->sellerBusinessProfile;


            $businessName =
                optional(
                    $application
                )->business_name
                ?: $seller->name;


            $categoryName =
                optional(
                    $application
                )->category
                ?: 'Verified Seller';


            $publicLocation =
                optional(
                    $profile
                )->location
                ?: optional(
                    $application
                )->location
                ?: 'Location not specified';


            $description =
                optional(
                    $profile
                )->tagline
                ?: optional(
                    $profile
                )->about
                ?: optional(
                    $application
                )->description
                ?: 'Verified MidPoint seller.';


            $initials =
                collect(
                    preg_split(
                        '/\s+/',
                        trim(
                            $businessName
                        )
                    )
                )
                    ->filter()
                    ->take(2)
                    ->map(
                        fn($word) =>
                        strtoupper(
                            substr(
                                $word,
                                0,
                                1
                            )
                        )
                    )
                    ->implode('')
                ?: 'MP';


            $gradients = [

                'linear-gradient(135deg,#0B3D2E,#12B76A)',

                'linear-gradient(135deg,#6941C6,#9E77ED)',

                'linear-gradient(135deg,#B54708,#F79009)',

                'linear-gradient(135deg,#175CD3,#53B1FD)',

                'linear-gradient(135deg,#9E165F,#EE46BC)',

            ];


            $gradient =
                $gradients[
                    $seller->id
                    %
                    count(
                        $gradients
                    )
                ];


            $businessUrl =
                route(
                    'featured-businesses.show',
                    $seller
                );

                            @endphp



                            <article
                                class="
                                    home-business-card
                                    flex
                                    h-full
                                    flex-col
                                    gap-3
                                    rounded-[18px]
                                    border
                                    border-[#E4EAE6]
                                    bg-white
                                    p-[22px]
                                    shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]
                                "
                            >


                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-3
                                    "
                                >


                                    <div
                                        class="
                                            grid
                                            h-[50px]
                                            w-[50px]
                                            shrink-0
                                            place-items-center
                                            overflow-hidden
                                            rounded-[15px]
                                            text-[15px]
                                            font-bold
                                            text-white
                                        "

                                        style="
                                            background:
                                            {{ $gradient }};
                                        "
                                    >


                                        @if(
                $profile
                &&
                $profile->profile_image_url
            )


                                            <img
                                                src="{{
                    $profile->profile_image_url
                                                }}"

                                                alt="{{
                    $businessName
                                                }}"

                                                class="
                                                    h-full
                                                    w-full
                                                    object-cover
                                                "

                                                loading="lazy"
                                            >


                                        @else


                                            {{ $initials }}


                                        @endif


                                    </div>



                                    <div
                                        class="
                                            min-w-0
                                            flex-1
                                        "
                                    >


                                        <div
                                            class="
                                                flex
                                                items-center
                                                gap-1.5
                                            "
                                        >


                                            <h3
                                                class="
                                                    truncate
                                                    text-[15px]
                                                    font-bold
                                                "
                                            >

                                                {{ $businessName }}

                                            </h3>


                                            <span
                                                class="
                                                    inline-flex
                                                    h-[16px]
                                                    w-[16px]
                                                    shrink-0
                                                    items-center
                                                    justify-center
                                                    rounded-full
                                                    bg-[#E8F7EF]
                                                    text-[8px]
                                                    font-bold
                                                    text-[#0E7A4C]
                                                "
                                            >
                                                ✓
                                            </span>


                                        </div>


                                        <div
                                            class="
                                                truncate
                                                text-[13px]
                                                text-[#5A6660]
                                            "
                                        >

                                            {{ $categoryName }}

                                        </div>


                                    </div>



                                    <div
                                        class="
                                            ml-auto
                                            inline-flex
                                            shrink-0
                                            items-center
                                            gap-[5px]
                                            text-[13px]
                                            font-bold
                                            text-[#0E7A4C]
                                        "
                                    >


                                        @if(
                $seller->seller_rating
            )


                                            <span class="text-[#F79009]">
                                                ★
                                            </span>


                                            <span>

                                                {{
                    number_format(
                        $seller->seller_rating,
                        1
                    )
                                                }}

                                            </span>


                                        @else


                                            <span
                                                class="
                                                    rounded-full
                                                    bg-[#E8F7EF]
                                                    px-2
                                                    py-1
                                                    text-[10px]
                                                "
                                            >
                                                New
                                            </span>


                                        @endif


                                    </div>


                                </div>



                                <p
                                    class="
                                        text-[13px]
                                        leading-[1.6]
                                        text-[#5A6660]
                                    "
                                >

                                    {{
                \Illuminate\Support\Str::limit(
                    strip_tags(
                        $description
                    ),
                    120
                )
                                    }}

                                </p>



                                <div
                                    class="
                                        text-[13px]
                                        text-[#5A6660]
                                    "
                                >

                                    📍 {{ $publicLocation }}

                                </div>



                                <div
                                    class="
                                        mt-auto
                                        flex
                                        flex-col
                                        gap-2
                                        pt-1
                                        sm:flex-row
                                    "
                                >


                                    <a
                                        href="{{
                $businessUrl
                                        }}#products"

                                        class="
                                            inline-flex
                                            min-h-[36px]
                                            flex-1
                                            items-center
                                            justify-center
                                            rounded-[10px]
                                            bg-[#12B76A]
                                            px-[13px]
                                            py-[7px]
                                            text-center
                                            text-[12px]
                                            font-semibold
                                            text-white
                                            transition
                                            hover:brightness-105
                                        "
                                    >

                                        Start secure transaction

                                    </a>



                                    <a
                                        href="{{
                $businessUrl
                                        }}"

                                        class="
                                            inline-flex
                                            min-h-[36px]
                                            items-center
                                            justify-center
                                            rounded-[10px]
                                            border-[1.5px]
                                            border-[#E4EAE6]
                                            bg-white
                                            px-[13px]
                                            py-[7px]
                                            text-[12px]
                                            font-semibold
                                            text-[#0B3D2E]
                                            transition
                                            hover:border-[#12B76A]
                                            hover:text-[#12B76A]
                                        "
                                    >

                                        View business

                                    </a>


                                </div>


                            </article>


                        @endforeach


                    </div>


                @else


                    <div
                        class="
                            rounded-[18px]
                            border
                            border-[#E4EAE6]
                            bg-white
                            p-10
                            text-center
                            text-[13px]
                            text-[#5A6660]
                        "
                    >

                        Verified businesses will appear here automatically.

                    </div>


                @endif


            </div>


        </section>



        {{-- =========================================================
            TESTIMONIALS
        ========================================================== --}}

        <section
            class="
                bg-[linear-gradient(160deg,#0B3D2E,#123B54)]
                py-[52px]
                text-white
                sm:py-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[1160px]
                    px-[22px]
                "
            >


                <div class="mb-[42px]">


                    <div
                        class="
                            home-eyebrow
                            mb-[14px]
                            inline-flex
                            items-center
                            gap-2
                            text-[12.5px]
                            font-bold
                            uppercase
                            tracking-[.12em]
                            text-[#7EF0B6]
                        "
                    >

                        {{ $home->testimonials_eyebrow }}

                    </div>


                    <h2
                        class="
                            display-font
                            text-[27px]
                            font-extrabold
                            text-white
                            sm:text-[34px]
                            lg:text-[38px]
                        "
                    >

                        {{ $home->testimonials_title }}

                    </h2>


                </div>



                @if(
        $homeTestimonials->count()
    )


                    <div
                        class="home-testimonial-shell"
                        data-home-testimonial-slider
                    >


                        {{-- Previous --}}

                        <button
                            type="button"
                            class="home-testimonial-arrow prev"
                            data-testimonial-prev
                            aria-label="Previous testimonials"
                        >

                            <i class="fa-solid fa-chevron-left"></i>

                        </button>



                        {{-- Slider --}}

                        <div class="home-testimonial-viewport">


                            <div
                                class="home-testimonial-track"
                                data-testimonial-track
                            >


                                @foreach ($homeTestimonials as $testimonial)


                                    @php

            $initials =
                $testimonial->avatar_initials

                ?:

                collect(
                    preg_split(
                        '/\s+/',
                        trim(
                            $testimonial->reviewer_name
                        )
                    )
                )
                    ->filter()
                    ->take(2)
                    ->map(
                        fn($word) =>
                        strtoupper(
                            substr(
                                $word,
                                0,
                                1
                            )
                        )
                    )
                    ->implode('');

                                    @endphp



                                    <article class="home-testimonial-slide">


                                        <div class="home-testimonial-card">


                                            <div
                                                class="stars"

                                                aria-label="{{
                $testimonial->rating
                                                }} out of 5 stars"
                                            >

                                                {{
                str_repeat(
                    '★',
                    $testimonial->rating
                )
                                                }}

                                                {{
                str_repeat(
                    '☆',
                    5 - $testimonial->rating
                )
                                                }}

                                            </div>



                                            <blockquote>

                                                “{{ $testimonial->review_text }}”

                                            </blockquote>



                                            <div class="home-testimonial-person">


                                                <div
                                                    class="home-testimonial-avatar"

                                                    style="
                                                        background:
                                                        {{ $testimonial->avatar_color }};
                                                    "
                                                >

                                                    {{ $initials }}

                                                </div>



                                                <div>


                                                    <strong>
                                                        {{ $testimonial->reviewer_name }}
                                                    </strong>


                                                    <span>
                                                        {{ $testimonial->reviewer_meta }}
                                                    </span>


                                                </div>


                                            </div>


                                        </div>


                                    </article>


                                @endforeach


                            </div>


                        </div>



                        {{-- Next --}}

                        <button
                            type="button"
                            class="home-testimonial-arrow next"
                            data-testimonial-next
                            aria-label="Next testimonials"
                        >

                            <i class="fa-solid fa-chevron-right"></i>

                        </button>


                    </div>


                @else


                    <div
                        class="
                            rounded-[18px]
                            border
                            border-white/[.12]
                            bg-white/[.06]
                            p-8
                            text-center
                            text-[#D7E5DE]
                        "
                    >

                        Testimonials will appear here when enabled by
                        the administrator.

                    </div>


                @endif


            </div>


        </section>



        {{-- =========================================================
            FAQ
        ========================================================== --}}

        <section
            class="
                bg-[#F6F9F7]
                py-[52px]
                sm:py-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[760px]
                    px-[22px]
                "
            >


                <div
                    class="
                        mx-auto
                        mb-[42px]
                        max-w-[620px]
                        text-center
                    "
                >


                    <div
                        class="
                            home-eyebrow
                            mb-[14px]
                            inline-flex
                            items-center
                            justify-center
                            gap-2
                            text-[12.5px]
                            font-bold
                            uppercase
                            tracking-[.12em]
                            text-[#12B76A]
                        "
                    >

                        {{ $home->faq_eyebrow }}

                    </div>


                    <h2
                        class="
                            display-font
                            text-[27px]
                            font-extrabold
                            sm:text-[34px]
                            lg:text-[38px]
                        "
                    >

                        {{ $home->faq_title }}

                    </h2>


                </div>



                <div class="home-faq">


                    @forelse ($homeFaqs as $faq)


                        <details
                            class="
                                mb-[10px]
                                overflow-hidden
                                rounded-[14px]
                                border
                                border-[#E4EAE6]
                                bg-white
                            "
                        >


                            <summary
                                class="
                                    flex
                                    cursor-pointer
                                    items-center
                                    justify-between
                                    px-5
                                    py-[17px]
                                    text-[14px]
                                    font-semibold
                                    sm:text-[15px]
                                "
                            >

                                {{ $faq->question }}

                            </summary>


                            <div
                                class="
                                    px-5
                                    pb-[17px]
                                    text-[14px]
                                    leading-[1.65]
                                    text-[#5A6660]
                                "
                            >

                                {!!
            nl2br(
                e(
                    $faq->answer
                )
            )
                                !!}

                            </div>


                        </details>


                    @empty


                        <div
                            class="
                                rounded-[14px]
                                border
                                border-[#E4EAE6]
                                bg-white
                                p-6
                                text-center
                                text-[13px]
                                text-[#5A6660]
                            "
                        >

                            FAQs will be available soon.

                        </div>


                    @endforelse


                </div>



                <div
                    class="
                        mt-[22px]
                        text-center
                    "
                >


                    <a
                        href="{{ route('faqs') }}"

                        class="
                            inline-flex
                            min-h-[43px]
                            items-center
                            justify-center
                            rounded-xl
                            border-[1.5px]
                            border-[#E4EAE6]
                            bg-white
                            px-5
                            py-[11px]
                            text-[14px]
                            font-semibold
                            text-[#0B3D2E]
                            transition
                            hover:border-[#12B76A]
                            hover:text-[#12B76A]
                        "
                    >

                        {{ $home->faq_view_all_text }}

                    </a>


                </div>


            </div>


        </section>



        {{-- =========================================================
            FINAL CTA
        ========================================================== --}}

        <section
            class="
                bg-[#F6F9F7]
                pb-[52px]
                sm:pb-[74px]
            "
        >


            <div
                class="
                    mx-auto
                    max-w-[1160px]
                    px-[22px]
                "
            >


                <div
                    class="
                        rounded-[18px]
                        bg-[linear-gradient(120deg,#0B3D2E,#7A5AF8)]
                        px-6
                        py-10
                        text-center
                        text-white
                        shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]
                        sm:px-12
                        sm:py-12
                    "
                >


                    <h2
                        class="
                            display-font
                            text-[25px]
                            font-extrabold
                            sm:text-[30px]
                            lg:text-[34px]
                        "
                    >

                        {{ $home->final_cta_title }}

                    </h2>


                    <p
                        class="
                            mx-auto
                            mb-6
                            mt-[10px]
                            max-w-[460px]
                            text-[14px]
                            leading-[1.6]
                            text-[#E4DEFB]
                        "
                    >

                        {{ $home->final_cta_description }}

                    </p>


                    <a
                        href="{{
        $home->final_cta_button_url
                        }}"

                        class="
                            inline-flex
                            min-h-[52px]
                            items-center
                            justify-center
                            rounded-[14px]
                            bg-white
                            px-7
                            py-[15px]
                            text-[15px]
                            font-semibold
                            text-[#0B3D2E]
                            transition
                            hover:-translate-y-px
                            hover:shadow-lg
                            sm:text-[16px]
                        "
                    >

                        {{ $home->final_cta_button_text }}

                    </a>


                </div>


            </div>


        </section>


    </div>


@endsection



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Testimonial Slider
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-home-testimonial-slider]'
            )
            .forEach(
                function (slider) {

                    const track =
                        slider.querySelector(
                            '[data-testimonial-track]'
                        );


                    const slides =
                        Array.from(
                            track.children
                        );


                    const prev =
                        slider.querySelector(
                            '[data-testimonial-prev]'
                        );


                    const next =
                        slider.querySelector(
                            '[data-testimonial-next]'
                        );


                    let index =
                        0;


                    /*
                    |--------------------------------------------------------------------------
                    | Number Of Visible Reviews
                    |--------------------------------------------------------------------------
                    */

                    function visibleCount()
                    {
                        if (
                            window.innerWidth
                            <=
                            640
                        ) {

                            return 1;

                        }


                        if (
                            window.innerWidth
                            <=
                            960
                        ) {

                            return 2;

                        }


                        return 3;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Maximum Index
                    |--------------------------------------------------------------------------
                    */

                    function maxIndex()
                    {
                        return Math.max(

                            0,

                            slides.length
                            -
                            visibleCount()

                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Render Slider
                    |--------------------------------------------------------------------------
                    */

                    function render()
                    {
                        const visible =
                            visibleCount();


                        const max =
                            maxIndex();


                        if (
                            index
                            >
                            max
                        ) {

                            index =
                                max;

                        }


                        const firstSlide =
                            slides[0];


                        if (
                            !firstSlide
                        ) {

                            return;

                        }


                        const gap =
                            parseFloat(
                                getComputedStyle(
                                    track
                                ).gap
                            )
                            ||
                            0;


                        const distance =

                            (
                                firstSlide
                                    .getBoundingClientRect()
                                    .width

                                +
                                gap
                            )

                            *

                            index;


                        track.style.transform =

                            `translateX(-${distance}px)`;


                        /*
                        |--------------------------------------------------------------------------
                        | Hide Arrows If Not Needed
                        |--------------------------------------------------------------------------
                        */

                        const needsControls =

                            slides.length
                            >
                            visible;


                        prev.hidden =
                            !needsControls;


                        next.hidden =
                            !needsControls;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Previous
                    |--------------------------------------------------------------------------
                    */

                    prev.addEventListener(
                        'click',
                        function () {

                            const max =
                                maxIndex();


                            index =

                                index <= 0

                                    ? max

                                    : index - 1;


                            render();

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Next
                    |--------------------------------------------------------------------------
                    */

                    next.addEventListener(
                        'click',
                        function () {

                            const max =
                                maxIndex();


                            index =

                                index >= max

                                    ? 0

                                    : index + 1;


                            render();

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Responsive Recalculation
                    |--------------------------------------------------------------------------
                    */

                    window.addEventListener(
                        'resize',
                        render
                    );


                    render();

                }
            );

    }
);

</script>

@endpush