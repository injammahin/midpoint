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
    | Hero Visual
    |--------------------------------------------------------------------------
    */

    .mp-platform-visual {

        position:
            relative;

        width:
            100%;

        max-width:
            620px;

        height:
            470px;

        margin-left:
            auto;

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

    }


    .mp-platform-visual::before {

        content:
            '';

        position:
            absolute;

        left:
            50%;

        top:
            50%;

        width:
            440px;

        height:
            440px;

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
                    18,
                    183,
                    106,
                    .13
                ),
                transparent 68%
            );

    }


    .mp-platform-line {

        position:
            absolute;

        z-index:
            1;

        top:
            50%;

        left:
            14%;

        right:
            14%;

        height:
            2px;

        transform:
            translateY(-50%);

        background:

            repeating-linear-gradient(
                90deg,
                rgba(
                    255,
                    255,
                    255,
                    .25
                ) 0 10px,
                transparent 10px 20px
            );

    }


    .mp-platform-line::before {

        content:
            '';

        position:
            absolute;

        top:
            0;

        left:
            -120px;

        width:
            120px;

        height:
            2px;

        background:

            linear-gradient(
                90deg,
                transparent,
                #7EF0B6,
                transparent
            );

        box-shadow:

            0 0 15px
            rgba(
                126,
                240,
                182,
                .65
            );

        animation:
            midpointLineMove
            3.2s
            linear
            infinite;

    }


    @keyframes midpointLineMove {

        from {

            left:
                -120px;

        }

        to {

            left:
                calc(
                    100%
                    +
                    40px
                );

        }

    }


    .mp-platform-node {

        position:
            absolute;

        z-index:
            5;

        top:
            50%;

        transform:
            translate(
                -50%,
                -50%
            );

        text-align:
            center;

    }


    .mp-buyer-node {

        left:
            14%;

    }


    .mp-midpoint-node {

        left:
            50%;

        z-index:
            10;

    }


    .mp-seller-node {

        left:
            86%;

    }


    .mp-side-card {

        width:
            142px;

        height:
            150px;

        display:
            flex;

        flex-direction:
            column;

        align-items:
            center;

        justify-content:
            center;

        border:
            1px solid
            rgba(
                255,
                255,
                255,
                .19
            );

        border-radius:
            27px;

        background:

            linear-gradient(
                145deg,
                rgba(
                    255,
                    255,
                    255,
                    .13
                ),
                rgba(
                    255,
                    255,
                    255,
                    .07
                )
            );

        box-shadow:

            0 22px 50px
            rgba(
                0,
                0,
                0,
                .17
            ),

            inset
            0 1px 0
            rgba(
                255,
                255,
                255,
                .09
            );

        backdrop-filter:
            blur(13px);

    }


    .mp-side-icon {

        width:
            62px;

        height:
            62px;

        display:
            grid;

        place-items:
            center;

        border-radius:
            20px;

        color:
            #0B3D2E;

        background:

            linear-gradient(
                145deg,
                #FFFFFF,
                #DDF7E9
            );

        box-shadow:

            0 12px 28px
            rgba(
                0,
                0,
                0,
                .15
            );

    }


    .mp-side-icon svg {

        width:
            30px;

        height:
            30px;

    }


    .mp-seller-node
    .mp-side-icon {

        color:
            #6345E4;

        background:

            linear-gradient(
                145deg,
                #FFFFFF,
                #EEE9FF
            );

    }


    .mp-side-card strong {

        margin-top:
            13px;

        color:
            #FFFFFF;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            16px;

        font-weight:
            800;

    }


    /*
    |--------------------------------------------------------------------------
    | MidPoint Center
    |--------------------------------------------------------------------------
    */

    .mp-midpoint-core {

        position:
            relative;

        width:
            190px;

        height:
            190px;

        display:
            flex;

        flex-direction:
            column;

        align-items:
            center;

        justify-content:
            center;

        border:
            1px solid
            rgba(
                126,
                240,
                182,
                .36
            );

        border-radius:
            42px;

        background:

            radial-gradient(
                circle at 50% 18%,
                rgba(
                    126,
                    240,
                    182,
                    .16
                ),
                transparent 43%
            ),

            linear-gradient(
                145deg,
                rgba(
                    18,
                    183,
                    106,
                    .28
                ),
                rgba(
                    4,
                    48,
                    36,
                    .82
                )
            );

        box-shadow:

            0 28px 65px
            rgba(
                0,
                0,
                0,
                .24
            ),

            inset
            0 1px 0
            rgba(
                255,
                255,
                255,
                .12
            );

    }


    .mp-midpoint-core::before,
    .mp-midpoint-core::after {

        content:
            '';

        position:
            absolute;

        top:
            50%;

        left:
            50%;

        z-index:
            -1;

        width:
            205px;

        height:
            205px;

        border:
            1px solid
            rgba(
                126,
                240,
                182,
                .23
            );

        border-radius:
            50%;

        animation:
            midpointProtectPulse
            3.2s
            ease-out
            infinite;

    }


    .mp-midpoint-core::after {

        animation-delay:
            1.6s;

    }


    @keyframes midpointProtectPulse {

        0% {

            transform:
                translate(
                    -50%,
                    -50%
                )
                scale(.65);

            opacity:
                .9;

        }


        100% {

            transform:
                translate(
                    -50%,
                    -50%
                )
                scale(1.5);

            opacity:
                0;

        }

    }


    .mp-midpoint-shield {

        width:
            76px;

        height:
            76px;

        display:
            grid;

        place-items:
            center;

        border-radius:
            24px;

        color:
            #07432F;

        background:

            linear-gradient(
                145deg,
                #A8FFD6,
                #54E69D
            );

        box-shadow:

            0 17px 38px
            rgba(
                75,
                228,
                150,
                .31
            );

        animation:
            midpointShieldFloat
            3s
            ease-in-out
            infinite;

    }


    .mp-midpoint-shield svg {

        width:
            39px;

        height:
            39px;

    }


    @keyframes midpointShieldFloat {

        0%,
        100% {

            transform:
                translateY(0);

        }


        50% {

            transform:
                translateY(-6px);

        }

    }


    .mp-midpoint-core strong {

        margin-top:
            15px;

        color:
            #FFFFFF;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            20px;

        font-weight:
            800;

    }


    /*
    |--------------------------------------------------------------------------
    | Moving Money
    |--------------------------------------------------------------------------
    */

    .mp-money-packet {

        position:
            absolute;

        z-index:
            20;

        top:
            calc(
                50%
                -
                19px
            );

        left:
            14%;

        width:
            38px;

        height:
            38px;

        display:
            grid;

        place-items:
            center;

        border:
            2px solid
            rgba(
                255,
                255,
                255,
                .82
            );

        border-radius:
            50%;

        color:
            #07432F;

        background:
            #7EF0B6;

        box-shadow:

            0 0 0 7px
            rgba(
                126,
                240,
                182,
                .10
            ),

            0 10px 25px
            rgba(
                0,
                0,
                0,
                .20
            );

        font-size:
            15px;

        font-weight:
            900;

        animation:
            midpointMoneyJourney
            8s
            ease-in-out
            infinite;

    }


    @keyframes midpointMoneyJourney {

        0% {

            left:
                14%;

            opacity:
                0;

            transform:
                scale(.55);

            background:
                #7EF0B6;

            color:
                #07432F;

        }


        5% {

            opacity:
                1;

            transform:
                scale(1);

        }


        27% {

            left:
                calc(
                    50%
                    -
                    19px
                );

            opacity:
                1;

            transform:
                scale(1);

        }


        33% {

            left:
                calc(
                    50%
                    -
                    19px
                );

            opacity:
                0;

            transform:
                scale(.25);

        }


        34%,
        69% {

            left:
                calc(
                    50%
                    -
                    19px
                );

            opacity:
                0;

            transform:
                scale(.25);

        }


        73% {

            left:
                calc(
                    50%
                    +
                    18px
                );

            opacity:
                1;

            transform:
                scale(.85);

            color:
                #FFFFFF;

            background:
                #7A5AF8;

        }


        94% {

            left:
                calc(
                    86%
                    -
                    19px
                );

            opacity:
                1;

            transform:
                scale(1);

            color:
                #FFFFFF;

            background:
                #7A5AF8;

        }


        100% {

            left:
                calc(
                    86%
                    -
                    19px
                );

            opacity:
                0;

            transform:
                scale(.4);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Moving Product
    |--------------------------------------------------------------------------
    */

    .mp-product-packet {

        position:
            absolute;

        z-index:
            19;

        top:
            calc(
                50%
                +
                42px
            );

        left:
            calc(
                86%
                -
                22px
            );

        width:
            44px;

        height:
            44px;

        display:
            grid;

        place-items:
            center;

        border:
            1px solid
            rgba(
                255,
                255,
                255,
                .65
            );

        border-radius:
            14px;

        color:
            #6544E4;

        background:
            #F1EDFE;

        box-shadow:

            0 11px 28px
            rgba(
                0,
                0,
                0,
                .19
            );

        opacity:
            0;

        animation:
            midpointProductJourney
            8s
            ease-in-out
            infinite;

    }


    .mp-product-packet svg {

        width:
            23px;

        height:
            23px;

    }


    @keyframes midpointProductJourney {

        0%,
        34% {

            left:
                calc(
                    86%
                    -
                    22px
                );

            opacity:
                0;

            transform:
                scale(.55);

        }


        39% {

            left:
                calc(
                    84%
                    -
                    22px
                );

            opacity:
                1;

            transform:
                scale(1);

        }


        63% {

            left:
                calc(
                    16%
                    -
                    22px
                );

            opacity:
                1;

            transform:
                scale(1);

        }


        69% {

            left:
                calc(
                    14%
                    -
                    22px
                );

            opacity:
                0;

            transform:
                scale(.45);

        }


        100% {

            left:
                calc(
                    14%
                    -
                    22px
                );

            opacity:
                0;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Held Indicator
    |--------------------------------------------------------------------------
    */

    .mp-held-indicator {

        position:
            absolute;

        z-index:
            25;

        left:
            50%;

        top:
            50%;

        width:
            13px;

        height:
            13px;

        transform:
            translate(
                -50%,
                -50%
            );

        border-radius:
            50%;

        background:
            #7EF0B6;

        box-shadow:

            0 0 0 7px
            rgba(
                126,
                240,
                182,
                .10
            );

        opacity:
            0;

        animation:
            midpointHeldIndicator
            8s
            ease-in-out
            infinite;

    }


    @keyframes midpointHeldIndicator {

        0%,
        27% {

            opacity:
                0;

            transform:
                translate(
                    -50%,
                    -50%
                )
                scale(.4);

        }


        34%,
        68% {

            opacity:
                1;

            transform:
                translate(
                    -50%,
                    -50%
                )
                scale(1);

        }


        72%,
        100% {

            opacity:
                0;

            transform:
                translate(
                    -50%,
                    -50%
                )
                scale(.4);

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

        .mp-platform-visual {

            max-width:
                620px;

            margin:
                0 auto;

        }


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


        .mp-platform-visual {

            height:
                365px;

        }


        .mp-side-card {

            width:
                90px;

            height:
                110px;

            border-radius:
                19px;

        }


        .mp-side-icon {

            width:
                44px;

            height:
                44px;

            border-radius:
                14px;

        }


        .mp-side-icon svg {

            width:
                22px;

            height:
                22px;

        }


        .mp-side-card strong {

            margin-top:
                8px;

            font-size:
                12px;

        }


        .mp-midpoint-core {

            width:
                128px;

            height:
                140px;

            border-radius:
                30px;

        }


        .mp-midpoint-core::before,
        .mp-midpoint-core::after {

            width:
                145px;

            height:
                145px;

        }


        .mp-midpoint-shield {

            width:
                54px;

            height:
                54px;

            border-radius:
                17px;

        }


        .mp-midpoint-shield svg {

            width:
                28px;

            height:
                28px;

        }


        .mp-midpoint-core strong {

            margin-top:
                9px;

            font-size:
                14px;

        }


        .mp-money-packet {

            width:
                30px;

            height:
                30px;

            top:
                calc(
                    50%
                    -
                    15px
                );

            font-size:
                11px;

        }


        .mp-product-packet {

            width:
                35px;

            height:
                35px;

            top:
                calc(
                    50%
                    +
                    34px
                );

        }


        .mp-product-packet svg {

            width:
                18px;

            height:
                18px;

        }


        .mp-buyer-node {

            left:
                13%;

        }


        .mp-seller-node {

            left:
                87%;

        }


        .mp-platform-line {

            left:
                13%;

            right:
                13%;

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

        .mp-platform-visual *,
        .mp-platform-visual *::before,
        .mp-platform-visual *::after {

            animation-duration:
                .001ms !important;

            animation-iteration-count:
                1 !important;

        }


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

            <div>


                <div
                    class="
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
                                    text-[22px]
                                    font-extrabold
                                    sm:text-[24px]
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
                BUYER → MIDPOINT → SELLER VISUAL
            ====================================================== --}}

            <div
                class="mp-platform-visual"

                aria-label="
                    MidPoint protects transactions
                    between buyers and sellers.
                "
            >


                <div
                    class="mp-platform-line"
                    aria-hidden="true"
                ></div>



                {{-- Buyer --}}

                <div
                    class="
                        mp-platform-node
                        mp-buyer-node
                    "
                >


                    <div class="mp-side-card">


                        <div class="mp-side-icon">


                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <circle
                                    cx="12"
                                    cy="8"
                                    r="4"
                                ></circle>


                                <path
                                    d="
                                        M4.5 21
                                        a7.5 7.5
                                        0 0 1
                                        15 0
                                    "
                                ></path>

                            </svg>


                        </div>


                        <strong>
                            Buyer
                        </strong>


                    </div>


                </div>



                {{-- MidPoint --}}

                <div
                    class="
                        mp-platform-node
                        mp-midpoint-node
                    "
                >


                    <div class="mp-midpoint-core">


                        <div class="mp-midpoint-shield">


                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <path
                                    d="
                                        M12 3
                                        20 6
                                        v5
                                        c0 5.1
                                        -3.3 8.4
                                        -8 10
                                        -4.7-1.6
                                        -8-4.9
                                        -8-10
                                        V6
                                        l8-3Z
                                    "
                                ></path>


                                <path
                                    d="
                                        m8.8 12
                                        2 2
                                        4.5-4.5
                                    "
                                ></path>

                            </svg>


                        </div>


                        <strong>
                            MidPoint
                        </strong>


                    </div>


                </div>



                {{-- Seller --}}

                <div
                    class="
                        mp-platform-node
                        mp-seller-node
                    "
                >


                    <div class="mp-side-card">


                        <div class="mp-side-icon">


                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <path
                                    d="
                                        M4 10
                                        v10
                                        h16
                                        V10
                                    "
                                ></path>


                                <path
                                    d="
                                        M3 10
                                        5 4
                                        h14
                                        l2 6
                                    "
                                ></path>


                                <path
                                    d="
                                        M8 20
                                        v-6
                                        h8
                                        v6
                                    "
                                ></path>

                            </svg>


                        </div>


                        <strong>
                            Seller
                        </strong>


                    </div>


                </div>



                {{-- Money --}}

                <div
                    class="mp-money-packet"
                    aria-hidden="true"
                >
                    ₦
                </div>



                {{-- Product --}}

                <div
                    class="mp-product-packet"
                    aria-hidden="true"
                >


                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >

                        <path
                            d="
                                M4 7
                                12 3
                                20 7
                                12 11
                                4 7Z
                            "
                        ></path>


                        <path
                            d="
                                M4 7
                                v10
                                l8 4
                                8-4
                                V7
                            "
                        ></path>


                        <path
                            d="
                                M12 11
                                v10
                            "
                        ></path>

                    </svg>


                </div>



                <div
                    class="mp-held-indicator"
                    aria-hidden="true"
                ></div>


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
                                    fn ($word) =>
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
                                            fn ($word) =>
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