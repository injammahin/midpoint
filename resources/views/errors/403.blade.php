<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <title>403 | Access Restricted | Midpoint</title>


    {{-- =========================================================
        GOOGLE FONT
    ========================================================== --}}

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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
        FONT AWESOME
    ========================================================== --}}

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        html,
        body {
            width: 100%;
            min-height: 100%;
        }


        body {
            font-family: 'Inter', sans-serif;

            background:
                radial-gradient(
                    circle at 15% 20%,
                    rgba(18, 183, 106, .10),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 85% 75%,
                    rgba(122, 90, 248, .10),
                    transparent 34%
                ),
                #f7faf8;

            color: #172033;

            overflow-x: hidden;
        }


        a {
            text-decoration: none;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .mp-error-page {
            position: relative;

            min-height: 100vh;

            display: flex;
            flex-direction: column;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | BACKGROUND GRID
        |--------------------------------------------------------------------------
        */

        .mp-grid {
            position: absolute;
            inset: 0;

            pointer-events: none;

            opacity: .5;

            background-image:
                linear-gradient(
                    rgba(11, 61, 46, .045) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(11, 61, 46, .045) 1px,
                    transparent 1px
                );

            background-size: 42px 42px;

            mask-image:
                linear-gradient(
                    to bottom,
                    black,
                    transparent 90%
                );
        }


        /*
        |--------------------------------------------------------------------------
        | FLOATING ORBS
        |--------------------------------------------------------------------------
        */

        .orb {
            position: absolute;

            border-radius: 50%;

            filter: blur(4px);

            pointer-events: none;

            animation:
                floatOrb 8s ease-in-out infinite;
        }


        .orb.one {
            width: 180px;
            height: 180px;

            left: -70px;
            top: 18%;

            background:
                rgba(18, 183, 106, .10);
        }


        .orb.two {
            width: 230px;
            height: 230px;

            right: -90px;
            bottom: 10%;

            background:
                rgba(122, 90, 248, .10);

            animation-delay: -3s;
        }


        @keyframes floatOrb {

            0%,
            100% {
                transform:
                    translate3d(
                        0,
                        0,
                        0
                    );
            }

            50% {
                transform:
                    translate3d(
                        0,
                        -24px,
                        0
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .mp-error-header {
            position: relative;
            z-index: 5;

            width: min(
                1160px,
                calc(100% - 36px)
            );

            margin: 0 auto;

            height: 80px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }


        .mp-brand {
            display: inline-flex;
            align-items: center;

            gap: 10px;

            color: #172033;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 21px;
            font-weight: 800;
        }


        .mp-brand-mark {
            width: 38px;
            height: 38px;

            display: grid;
            place-items: center;

            border-radius: 11px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #0b3d2e,
                    #12b76a
                );

            box-shadow:
                0 8px 22px
                rgba(11, 61, 46, .18);
        }


        .mp-brand .mid {
            color: #0b3d2e;
        }


        .mp-brand .point {
            color: #7a5af8;
        }


        .mp-error-code-mini {
            display: inline-flex;
            align-items: center;

            gap: 7px;

            padding: 8px 12px;

            border:
                1px solid #dce7e1;

            border-radius: 999px;

            background:
                rgba(255, 255, 255, .72);

            color: #64748b;

            font-size: 11px;
            font-weight: 700;

            backdrop-filter:
                blur(10px);
        }


        .mp-error-code-mini span {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #ef4444;

            box-shadow:
                0 0 0 4px
                rgba(239, 68, 68, .10);
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN
        |--------------------------------------------------------------------------
        */

        .mp-error-main {
            position: relative;
            z-index: 3;

            flex: 1;

            width: min(
                1180px,
                calc(100% - 32px)
            );

            margin: 0 auto;

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                minmax(420px, .95fr);

            align-items: center;

            gap: 60px;

            padding:
                38px 0
                80px;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .mp-error-copy {
            max-width: 600px;
        }


        .mp-error-eyebrow {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            margin-bottom: 18px;

            padding:
                7px 10px;

            border:
                1px solid #cfe9dc;

            border-radius: 999px;

            background: #effaf4;

            color: #087a50;

            font-size: 11px;
            font-weight: 800;

            letter-spacing: .07em;

            text-transform: uppercase;
        }


        .mp-error-eyebrow i {
            font-size: 10px;
        }


        .mp-error-number {
            margin-bottom: 2px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size:
                clamp(
                    72px,
                    11vw,
                    130px
                );

            line-height: .88;

            font-weight: 800;

            letter-spacing: -.06em;

            color: #0b3d2e;

            text-shadow:
                0 10px 35px
                rgba(11, 61, 46, .08);
        }


        .mp-error-copy h1 {
            margin-top: 19px;

            max-width: 560px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size:
                clamp(
                    30px,
                    4vw,
                    47px
                );

            line-height: 1.05;

            letter-spacing: -.035em;

            color: #172033;
        }


        .mp-error-copy h1 span {
            color: #7a5af8;
        }


        .mp-error-description {
            max-width: 530px;

            margin-top: 17px;

            color: #64748b;

            font-size: 15px;
            line-height: 1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | REASON CARD
        |--------------------------------------------------------------------------
        */

        .mp-reason {
            margin-top: 23px;

            display: flex;
            align-items: flex-start;

            gap: 12px;

            max-width: 520px;

            padding:
                14px 15px;

            border:
                1px solid #e6ebe8;

            border-radius: 14px;

            background:
                rgba(255, 255, 255, .68);

            backdrop-filter:
                blur(10px);
        }


        .mp-reason-icon {
            width: 34px;
            height: 34px;

            flex:
                0 0
                34px;

            display: grid;
            place-items: center;

            border-radius: 9px;

            background: #fff4e8;
            color: #d97706;
        }


        .mp-reason strong {
            display: block;

            margin-bottom: 3px;

            color: #334155;

            font-size: 12px;
        }


        .mp-reason span {
            display: block;

            color: #7b8794;

            font-size: 11px;
            line-height: 1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .mp-error-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 10px;

            margin-top: 25px;
        }


        .mp-btn {
            min-height: 46px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            padding:
                0 17px;

            border-radius: 11px;

            font-size: 13px;
            font-weight: 800;

            transition:
                transform .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }


        .mp-btn:hover {
            transform:
                translateY(-2px);
        }


        .mp-btn-primary {
            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #0b3d2e,
                    #0e7a4c
                );

            box-shadow:
                0 10px 24px
                rgba(11, 61, 46, .18);
        }


        .mp-btn-primary:hover {
            box-shadow:
                0 14px 32px
                rgba(11, 61, 46, .24);
        }


        .mp-btn-secondary {
            color: #475569;

            border:
                1px solid #dae3de;

            background:
                rgba(255, 255, 255, .78);
        }


        /*
        |--------------------------------------------------------------------------
        | VISUAL
        |--------------------------------------------------------------------------
        */

        .mp-visual {
            position: relative;

            min-height: 500px;

            display: grid;
            place-items: center;
        }


        .transaction-stage {
            position: relative;

            width: min(
                100%,
                520px
            );

            height: 410px;
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION PARTIES
        |--------------------------------------------------------------------------
        */

        .party {
            position: absolute;

            top: 50%;

            width: 145px;

            padding:
                17px 14px;

            border:
                1px solid #e0e8e4;

            border-radius: 17px;

            background:
                rgba(255, 255, 255, .90);

            box-shadow:
                0 16px 40px
                rgba(15, 23, 42, .08);

            text-align: center;

            transform:
                translateY(-50%);

            backdrop-filter:
                blur(14px);
        }


        .party.buyer {
            left: 0;

            animation:
                partyLeft
                4.2s ease-in-out
                infinite;
        }


        .party.seller {
            right: 0;

            animation:
                partyRight
                4.2s ease-in-out
                infinite;
        }


        @keyframes partyLeft {

            0%,
            100% {
                transform:
                    translateY(-50%)
                    translateX(0);
            }

            50% {
                transform:
                    translateY(-50%)
                    translateX(7px);
            }
        }


        @keyframes partyRight {

            0%,
            100% {
                transform:
                    translateY(-50%)
                    translateX(0);
            }

            50% {
                transform:
                    translateY(-50%)
                    translateX(-7px);
            }
        }


        .party-icon {
            width: 46px;
            height: 46px;

            margin:
                0 auto 10px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            font-size: 17px;
        }


        .buyer .party-icon {
            background: #eff6ff;
            color: #2563eb;
        }


        .seller .party-icon {
            background: #f5f3ff;
            color: #7c3aed;
        }


        .party strong {
            display: block;

            color: #26313c;

            font-size: 12px;
        }


        .party span {
            display: block;

            margin-top: 4px;

            color: #94a3b8;

            font-size: 9px;
        }


        /*
        |--------------------------------------------------------------------------
        | Midpoint CORE
        |--------------------------------------------------------------------------
        */

        .Midpoint-core {
            position: absolute;

            left: 50%;
            top: 50%;

            width: 138px;
            height: 138px;

            border-radius: 50%;

            display: grid;
            place-items: center;

            transform:
                translate(-50%, -50%);

            background:
                linear-gradient(
                    145deg,
                    #0b3d2e,
                    #12b76a
                );

            box-shadow:
                0 25px 55px
                rgba(11, 61, 46, .25);

            z-index: 5;

            animation:
                corePulse
                3s ease-in-out
                infinite;
        }


        .Midpoint-core::before {
            content: "";

            position: absolute;

            inset: -13px;

            border:
                1px solid
                rgba(18, 183, 106, .24);

            border-radius: 50%;

            animation:
                ringPulse
                2.5s ease-out
                infinite;
        }


        .Midpoint-core::after {
            content: "";

            position: absolute;

            inset: -28px;

            border:
                1px dashed
                rgba(122, 90, 248, .19);

            border-radius: 50%;

            animation:
                rotateRing
                18s linear
                infinite;
        }


        @keyframes corePulse {

            0%,
            100% {
                transform:
                    translate(-50%, -50%)
                    scale(1);
            }

            50% {
                transform:
                    translate(-50%, -50%)
                    scale(1.035);
            }
        }


        @keyframes ringPulse {

            0% {
                opacity: 0;
                transform: scale(.88);
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: scale(1.22);
            }
        }


        @keyframes rotateRing {

            to {
                transform:
                    rotate(360deg);
            }
        }


        .core-content {
            position: relative;
            z-index: 3;

            text-align: center;

            color: white;
        }


        .core-content i {
            display: block;

            margin-bottom: 7px;

            font-size: 30px;
        }


        .core-content strong {
            display: block;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 16px;
        }


        .core-content span {
            display: block;

            margin-top: 3px;

            font-size: 8px;

            opacity: .75;

            text-transform: uppercase;

            letter-spacing: .11em;
        }


        /*
        |--------------------------------------------------------------------------
        | CONNECTION LINES
        |--------------------------------------------------------------------------
        */

        .connection {
            position: absolute;

            top: 50%;

            height: 2px;

            overflow: visible;

            background:
                linear-gradient(
                    90deg,
                    rgba(18,183,106,.13),
                    rgba(18,183,106,.55)
                );

            transform:
                translateY(-50%);

            z-index: 1;
        }


        .connection.left {
            left: 127px;

            width:
                calc(
                    50% - 185px
                );
        }


        .connection.right {
            right: 127px;

            width:
                calc(
                    50% - 185px
                );

            background:
                linear-gradient(
                    90deg,
                    rgba(122,90,248,.55),
                    rgba(122,90,248,.13)
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ANIMATED TRANSACTION DOTS
        |--------------------------------------------------------------------------
        */

        .money-dot {
            position: absolute;

            top: 50%;

            width: 11px;
            height: 11px;

            border-radius: 50%;

            z-index: 3;

            transform:
                translateY(-50%);
        }


        .money-dot.buyer-to-middle {
            left: 140px;

            background: #12b76a;

            box-shadow:
                0 0 0 5px
                rgba(18, 183, 106, .10);

            animation:
                buyerTransaction
                2.8s ease-in-out
                infinite;
        }


        .money-dot.middle-to-seller {
            right: 140px;

            background: #7a5af8;

            box-shadow:
                0 0 0 5px
                rgba(122, 90, 248, .10);

            animation:
                sellerTransaction
                2.8s ease-in-out
                infinite;

            animation-delay: 1.4s;
        }


        @keyframes buyerTransaction {

            0% {
                left: 140px;
                opacity: 0;
            }

            15% {
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                left:
                    calc(
                        50% - 10px
                    );

                opacity: 0;
            }
        }


        @keyframes sellerTransaction {

            0% {
                right:
                    calc(
                        50% - 10px
                    );

                opacity: 0;
            }

            15% {
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                right: 140px;
                opacity: 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ACCESS BLOCK
        |--------------------------------------------------------------------------
        */

        .access-block {
            position: absolute;

            left: 50%;
            bottom: 0;

            width: 250px;

            transform:
                translateX(-50%);

            display: flex;
            align-items: center;

            gap: 11px;

            padding:
                12px 14px;

            border:
                1px solid #fee2e2;

            border-radius: 14px;

            background:
                rgba(255,255,255,.92);

            box-shadow:
                0 14px 35px
                rgba(15,23,42,.07);
        }


        .access-block-icon {
            width: 36px;
            height: 36px;

            flex:
                0 0 36px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            background: #fff1f2;
            color: #dc2626;

            animation:
                lockShake
                4s ease-in-out
                infinite;
        }


        @keyframes lockShake {

            0%,
            88%,
            100% {
                transform:
                    rotate(0);
            }

            91% {
                transform:
                    rotate(-7deg);
            }

            94% {
                transform:
                    rotate(7deg);
            }

            97% {
                transform:
                    rotate(-4deg);
            }
        }


        .access-block strong {
            display: block;

            color: #7f1d1d;

            font-size: 10px;
        }


        .access-block span {
            display: block;

            margin-top: 2px;

            color: #a68b8b;

            font-size: 8px;
        }


        /*
        |--------------------------------------------------------------------------
        | SMALL SECURITY ITEMS
        |--------------------------------------------------------------------------
        */

        .security-chip {
            position: absolute;

            display: inline-flex;
            align-items: center;

            gap: 6px;

            padding:
                7px 9px;

            border:
                1px solid #dce7e1;

            border-radius: 999px;

            background:
                rgba(255,255,255,.85);

            color: #64748b;

            font-size: 8px;
            font-weight: 700;

            box-shadow:
                0 8px 20px
                rgba(15,23,42,.05);

            animation:
                chipFloat
                4s ease-in-out
                infinite;
        }


        .security-chip.one {
            left: 50%;
            top: 18px;

            transform:
                translateX(-50%);
        }


        .security-chip.two {
            left: 65px;
            bottom: 56px;

            animation-delay: -1.3s;
        }


        .security-chip.three {
            right: 58px;
            bottom: 56px;

            animation-delay: -2.2s;
        }


        @keyframes chipFloat {

            0%,
            100% {
                margin-top: 0;
            }

            50% {
                margin-top: -7px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .mp-error-footer {
            position: relative;
            z-index: 3;

            width: min(
                1160px,
                calc(100% - 36px)
            );

            margin: 0 auto;

            padding:
                0 0 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            color: #94a3b8;

            font-size: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media(max-width: 950px) {

            .mp-error-main {
                grid-template-columns: 1fr;

                gap: 10px;

                padding-top: 35px;
            }


            .mp-error-copy {
                margin: 0 auto;

                text-align: center;
            }


            .mp-error-description,
            .mp-reason {
                margin-left: auto;
                margin-right: auto;
            }


            .mp-error-actions {
                justify-content: center;
            }


            .mp-visual {
                min-height: 450px;
            }

        }


        @media(max-width: 570px) {

            .mp-error-header {
                height: 68px;
            }


            .mp-error-code-mini {
                display: none;
            }


            .mp-error-main {
                width:
                    min(
                        100% - 24px,
                        520px
                    );

                padding-top: 20px;
            }


            .mp-error-number {
                font-size: 78px;
            }


            .mp-error-copy h1 {
                font-size: 31px;
            }


            .mp-error-description {
                font-size: 13px;
            }


            .mp-visual {
                min-height: 380px;
            }


            .transaction-stage {
                height: 340px;
            }


            .party {
                width: 105px;

                padding:
                    13px 8px;
            }


            .party-icon {
                width: 37px;
                height: 37px;

                font-size: 13px;
            }


            .Midpoint-core {
                width: 110px;
                height: 110px;
            }


            .core-content i {
                font-size: 23px;
            }


            .core-content strong {
                font-size: 13px;
            }


            .connection.left {
                left: 98px;

                width:
                    calc(
                        50% - 145px
                    );
            }


            .connection.right {
                right: 98px;

                width:
                    calc(
                        50% - 145px
                    );
            }


            .money-dot.buyer-to-middle {
                left: 100px;
            }


            .money-dot.middle-to-seller {
                right: 100px;
            }


            @keyframes buyerTransaction {

                0% {
                    left: 100px;
                    opacity: 0;
                }

                15% {
                    opacity: 1;
                }

                70% {
                    opacity: 1;
                }

                100% {
                    left:
                        calc(
                            50% - 8px
                        );

                    opacity: 0;
                }
            }


            @keyframes sellerTransaction {

                0% {
                    right:
                        calc(
                            50% - 8px
                        );

                    opacity: 0;
                }

                15% {
                    opacity: 1;
                }

                70% {
                    opacity: 1;
                }

                100% {
                    right: 100px;
                    opacity: 0;
                }
            }


            .security-chip.two,
            .security-chip.three {
                display: none;
            }


            .access-block {
                width: 220px;
            }


            .mp-error-footer {
                flex-direction: column;

                text-align: center;
            }

        }

    </style>

</head>


<body>

<div class="mp-error-page">


    {{-- =========================================================
        BACKGROUND
    ========================================================== --}}

    <div class="mp-grid"></div>

    <div class="orb one"></div>
    <div class="orb two"></div>


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <header class="mp-error-header">


        <a
            href="{{ route('home') }}"
            class="mp-brand"
        >

            <span class="mp-brand-mark">
                M
            </span>


            <span>

                <span class="mid">
                    Mid
                </span><span class="point">Point</span>

            </span>

        </a>


        <div class="mp-error-code-mini">

            <span></span>

            Restricted area · HTTP 403

        </div>

    </header>


    {{-- =========================================================
        MAIN
    ========================================================== --}}

    <main class="mp-error-main">


        {{-- =====================================================
            LEFT CONTENT
        ====================================================== --}}

        <section class="mp-error-copy">


            <div class="mp-error-eyebrow">

                <i class="fa-solid fa-shield-halved"></i>

                Secure access control

            </div>


            <div class="mp-error-number">
                403
            </div>


            <h1>

                This side of the

                <span>
                    transaction
                </span>

                isn't assigned to you.

            </h1>


            <p class="mp-error-description">

                Midpoint keeps buyers, sellers and support teams
                safely separated while protecting every transaction.
                Your account is authenticated, but it doesn't have
                permission to access this particular area.

            </p>


            {{-- =================================================
                REASON
            ================================================== --}}

            <div class="mp-reason">

                <span class="mp-reason-icon">

                    <i class="fa-solid fa-lock"></i>

                </span>


                <div>

                    <strong>
                        Access safely blocked
                    </strong>


                    <span>

                        Nothing is wrong with your account.
                        This module may require a different role or
                        administrator permission.

                    </span>

                </div>

            </div>


            {{-- =================================================
                ACTIONS
            ================================================== --}}

            <div class="mp-error-actions">


                @auth

                    <a
                        href="{{ route('dashboard') }}"
                        class="mp-btn mp-btn-primary"
                    >

                        <i class="fa-solid fa-gauge-high"></i>

                        Back to my dashboard

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                @else

                    <a
                        href="{{ route('login') }}"
                        class="mp-btn mp-btn-primary"
                    >

                        <i class="fa-solid fa-right-to-bracket"></i>

                        Log in

                    </a>

                @endauth


                <a
                    href="{{ route('home') }}"
                    class="mp-btn mp-btn-secondary"
                >

                    <i class="fa-solid fa-house"></i>

                    Go to homepage

                </a>

            </div>

        </section>


        {{-- =====================================================
            RIGHT ANIMATED VISUAL
        ====================================================== --}}

        <section
            class="mp-visual"
            aria-hidden="true"
        >

            <div class="transaction-stage">


                {{-- =============================================
                    SECURITY CHIPS
                ============================================== --}}

                <div class="security-chip one">

                    <i class="fa-solid fa-shield-halved"></i>

                    Secure middle layer

                </div>


                <div class="security-chip two">

                    <i class="fa-solid fa-lock"></i>

                    Buyer protected

                </div>


                <div class="security-chip three">

                    <i class="fa-solid fa-check-double"></i>

                    Seller protected

                </div>


                {{-- =============================================
                    BUYER
                ============================================== --}}

                <div class="party buyer">

                    <div class="party-icon">

                        <i class="fa-solid fa-cart-shopping"></i>

                    </div>


                    <strong>
                        Buyer
                    </strong>


                    <span>
                        Funds securely sent
                    </span>

                </div>


                {{-- =============================================
                    SELLER
                ============================================== --}}

                <div class="party seller">

                    <div class="party-icon">

                        <i class="fa-solid fa-store"></i>

                    </div>


                    <strong>
                        Seller
                    </strong>


                    <span>
                        Payment safely released
                    </span>

                </div>


                {{-- =============================================
                    CONNECTION
                ============================================== --}}

                <div class="connection left"></div>
                <div class="connection right"></div>


                {{-- =============================================
                    ANIMATED TRANSACTION
                ============================================== --}}

                <span
                    class="
                        money-dot
                        buyer-to-middle
                    "
                ></span>


                <span
                    class="
                        money-dot
                        middle-to-seller
                    "
                ></span>


                {{-- =============================================
                    Midpoint
                ============================================== --}}

                <div class="Midpoint-core">

                    <div class="core-content">

                        <i class="fa-solid fa-shield-halved"></i>

                        <strong>
                            Midpoint
                        </strong>

                        <span>
                            Protected middle
                        </span>

                    </div>

                </div>


                {{-- =============================================
                    ACCESS BLOCK
                ============================================== --}}

                <div class="access-block">

                    <span class="access-block-icon">

                        <i class="fa-solid fa-lock"></i>

                    </span>


                    <div>

                        <strong>
                            Permission boundary active
                        </strong>


                        <span>
                            This route is outside your assigned access.
                        </span>

                    </div>

                </div>

            </div>

        </section>

    </main>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}

    <footer class="mp-error-footer">

        <span>

            <i class="fa-solid fa-shield-halved"></i>

            Midpoint keeps every side of the transaction protected.

        </span>


        <span>
            Error reference: 403 · Forbidden
        </span>

    </footer>

</div>

</body>

</html>