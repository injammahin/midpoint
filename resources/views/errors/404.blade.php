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

    <title>404 | Route Not Found | MidPoint</title>


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
        | THEME
        |--------------------------------------------------------------------------
        */

        :root {

            --green-dark:
                #0b3d2e;

            --green:
                #12b76a;

            --green-soft:
                #e8f7ef;

            --purple:
                #7a5af8;

            --purple-soft:
                #f1edfe;

            --text:
                #172033;

            --muted:
                #64748b;

            --border:
                #dce7e1;

            --background:
                #f7faf8;

            --white:
                #ffffff;

        }


        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {

            box-sizing:
                border-box;

            margin:
                0;

            padding:
                0;

        }


        html,
        body {

            width:
                100%;

            min-height:
                100%;

        }


        body {

            font-family:
                'Inter',
                sans-serif;

            color:
                var(--text);

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

                var(--background);

            overflow-x:
                hidden;

        }


        a {

            color:
                inherit;

            text-decoration:
                none;

        }


        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .mp-error-page {

            position:
                relative;

            min-height:
                100vh;

            display:
                flex;

            flex-direction:
                column;

            overflow:
                hidden;

        }


        /*
        |--------------------------------------------------------------------------
        | BACKGROUND GRID
        |--------------------------------------------------------------------------
        */

        .mp-grid {

            position:
                absolute;

            inset:
                0;

            pointer-events:
                none;

            opacity:
                .5;

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

            background-size:
                42px 42px;

            -webkit-mask-image:
                linear-gradient(
                    to bottom,
                    black,
                    transparent 92%
                );

            mask-image:
                linear-gradient(
                    to bottom,
                    black,
                    transparent 92%
                );

        }


        /*
        |--------------------------------------------------------------------------
        | FLOATING BACKGROUND ORBS
        |--------------------------------------------------------------------------
        */

        .orb {

            position:
                absolute;

            border-radius:
                50%;

            pointer-events:
                none;

            filter:
                blur(4px);

            animation:
                orbFloat
                8s
                ease-in-out
                infinite;

        }


        .orb.one {

            width:
                180px;

            height:
                180px;

            left:
                -70px;

            top:
                18%;

            background:
                rgba(
                    18,
                    183,
                    106,
                    .10
                );

        }


        .orb.two {

            width:
                230px;

            height:
                230px;

            right:
                -90px;

            bottom:
                9%;

            background:
                rgba(
                    122,
                    90,
                    248,
                    .10
                );

            animation-delay:
                -3s;

        }


        @keyframes orbFloat {

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

            position:
                relative;

            z-index:
                10;

            width:
                min(
                    1160px,
                    calc(100% - 36px)
                );

            height:
                80px;

            margin:
                0 auto;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

        }


        /*
        |--------------------------------------------------------------------------
        | BRAND
        |--------------------------------------------------------------------------
        */

        .mp-brand {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                10px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size:
                21px;

            font-weight:
                800;

        }


        .mp-brand-mark {

            width:
                38px;

            height:
                38px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                11px;

            color:
                white;

            background:

                linear-gradient(
                    135deg,
                    var(--green-dark),
                    var(--green)
                );

            box-shadow:

                0 8px 22px
                rgba(
                    11,
                    61,
                    46,
                    .18
                );

        }


        .mp-brand .mid {

            color:
                var(--green-dark);

        }


        .mp-brand .point {

            color:
                var(--purple);

        }


        /*
        |--------------------------------------------------------------------------
        | MINI ERROR STATUS
        |--------------------------------------------------------------------------
        */

        .mp-error-code-mini {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                8px 12px;

            border:
                1px solid
                var(--border);

            border-radius:
                999px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .75
                );

            color:
                var(--muted);

            font-size:
                11px;

            font-weight:
                700;

            backdrop-filter:
                blur(10px);

        }


        .mp-error-code-mini span {

            width:
                7px;

            height:
                7px;

            border-radius:
                50%;

            background:
                var(--purple);

            box-shadow:

                0 0 0 4px
                rgba(
                    122,
                    90,
                    248,
                    .10
                );

            animation:
                statusBlink
                1.8s
                ease-in-out
                infinite;

        }


        @keyframes statusBlink {

            0%,
            100% {

                opacity:
                    .45;

                transform:
                    scale(.85);

            }

            50% {

                opacity:
                    1;

                transform:
                    scale(1);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | MAIN
        |--------------------------------------------------------------------------
        */

        .mp-error-main {

            position:
                relative;

            z-index:
                3;

            flex:
                1;

            width:
                min(
                    1180px,
                    calc(100% - 32px)
                );

            margin:
                0 auto;

            display:
                grid;

            grid-template-columns:

                minmax(
                    390px,
                    .82fr
                )

                minmax(
                    520px,
                    1.18fr
                );

            align-items:
                center;

            gap:
                58px;

            padding:
                36px 0 72px;

        }


        /*
        |--------------------------------------------------------------------------
        | LEFT CONTENT
        |--------------------------------------------------------------------------
        */

        .mp-error-copy {

            max-width:
                530px;

        }


        .mp-error-eyebrow {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                8px 11px;

            border:
                1px solid
                #cfe9dc;

            border-radius:
                999px;

            background:
                #effaf4;

            color:
                #087a50;

            font-size:
                10px;

            font-weight:
                800;

            letter-spacing:
                .08em;

            text-transform:
                uppercase;

        }


        /*
        |--------------------------------------------------------------------------
        | 404
        |--------------------------------------------------------------------------
        */

        .mp-error-number {

            margin-top:
                18px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size:
                clamp(
                    78px,
                    9vw,
                    128px
                );

            line-height:
                .84;

            font-weight:
                800;

            letter-spacing:
                -.075em;

            color:
                var(--green-dark);

        }


        .mp-error-number .zero {

            display:
                inline-block;

            color:
                var(--purple);

            animation:
                zeroPulse
                2.8s
                ease-in-out
                infinite;

        }


        @keyframes zeroPulse {

            0%,
            100% {

                transform:
                    translateY(0);

                opacity:
                    1;

            }

            50% {

                transform:
                    translateY(-4px);

                opacity:
                    .72;

            }

        }


        .mp-error-copy h1 {

            max-width:
                500px;

            margin-top:
                20px;

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size:
                clamp(
                    31px,
                    4vw,
                    48px
                );

            line-height:
                1.04;

            letter-spacing:
                -.045em;

        }


        .mp-error-copy h1 span {

            color:
                var(--purple);

        }


        .mp-error-description {

            max-width:
                500px;

            margin-top:
                18px;

            color:
                var(--muted);

            font-size:
                14px;

            line-height:
                1.75;

        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT URL
        |--------------------------------------------------------------------------
        */

        .mp-current-route {

            width:
                min(
                    100%,
                    500px
                );

            margin-top:
                20px;

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            padding:
                11px 13px;

            border:
                1px solid
                var(--border);

            border-radius:
                13px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .76
                );

            color:
                #475569;

            box-shadow:

                0 10px 28px
                rgba(
                    11,
                    61,
                    46,
                    .05
                );

        }


        .mp-current-route i {

            flex:
                0 0 auto;

            color:
                var(--purple);

        }


        .mp-current-route code {

            min-width:
                0;

            overflow:
                hidden;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;

            font-family:

                ui-monospace,
                SFMono-Regular,
                Menlo,
                Monaco,
                Consolas,
                monospace;

            font-size:
                11px;

        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .mp-error-actions {

            margin-top:
                25px;

            display:
                flex;

            flex-wrap:
                wrap;

            gap:
                11px;

        }


        .mp-btn {

            min-height:
                46px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                9px;

            padding:
                0 18px;

            border-radius:
                12px;

            font-size:
                12px;

            font-weight:
                800;

            transition:

                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;

        }


        .mp-btn:hover {

            transform:
                translateY(-2px);

        }


        .mp-btn-primary {

            color:
                white;

            background:

                linear-gradient(
                    135deg,
                    var(--green-dark),
                    #0e7a4c
                );

            box-shadow:

                0 12px 28px
                rgba(
                    11,
                    61,
                    46,
                    .18
                );

        }


        .mp-btn-primary:hover {

            box-shadow:

                0 16px 32px
                rgba(
                    11,
                    61,
                    46,
                    .23
                );

        }


        .mp-btn-secondary {

            color:
                #475569;

            border:
                1px solid
                #dae3de;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .82
                );

        }


        /*
        |--------------------------------------------------------------------------
        | RIGHT VISUAL
        |--------------------------------------------------------------------------
        */

        .mp-visual {

            position:
                relative;

            min-height:
                520px;

            display:
                grid;

            place-items:
                center;

        }


        /*
        |--------------------------------------------------------------------------
        | ROUTE LAB
        |--------------------------------------------------------------------------
        */

        .route-lab {

            position:
                relative;

            width:
                min(
                    100%,
                    620px
                );

            height:
                455px;

            overflow:
                hidden;

            border:
                1px solid
                rgba(
                    220,
                    231,
                    225,
                    .95
                );

            border-radius:
                30px;

            background:

                radial-gradient(
                    circle at 50% 42%,
                    rgba(
                        18,
                        183,
                        106,
                        .08
                    ),
                    transparent 34%
                ),

                rgba(
                    255,
                    255,
                    255,
                    .75
                );

            box-shadow:

                0 28px 80px
                rgba(
                    11,
                    61,
                    46,
                    .10
                );

            backdrop-filter:
                blur(15px);

        }


        .route-lab::before {

            content:
                '';

            position:
                absolute;

            inset:
                0;

            background-image:

                linear-gradient(
                    rgba(
                        11,
                        61,
                        46,
                        .035
                    ) 1px,
                    transparent 1px
                ),

                linear-gradient(
                    90deg,
                    rgba(
                        11,
                        61,
                        46,
                        .035
                    ) 1px,
                    transparent 1px
                );

            background-size:
                30px 30px;

            pointer-events:
                none;

        }


        /*
        |--------------------------------------------------------------------------
        | BROWSER TOP BAR
        |--------------------------------------------------------------------------
        */

        .lab-topbar {

            position:
                absolute;

            top:
                18px;

            left:
                18px;

            right:
                18px;

            z-index:
                6;

            height:
                48px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                0 14px;

            border:
                1px solid
                #e6ebe8;

            border-radius:
                14px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .90
                );

        }


        .browser-dots {

            display:
                flex;

            gap:
                5px;

        }


        .browser-dots span {

            width:
                7px;

            height:
                7px;

            border-radius:
                50%;

            background:
                #d9e2dd;

        }


        .browser-dots span:first-child {

            background:
                #fda4af;

        }


        .browser-dots span:nth-child(2) {

            background:
                #fde68a;

        }


        .browser-dots span:nth-child(3) {

            background:
                #86efac;

        }


        .route-address {

            flex:
                1;

            min-width:
                0;

            height:
                28px;

            display:
                flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                0 10px;

            border-radius:
                8px;

            background:
                #f7faf8;

            color:
                #94a3b8;

            font-size:
                9px;

            font-weight:
                700;

        }


        .route-address i {

            color:
                var(--green);

        }


        .route-address span {

            overflow:
                hidden;

            white-space:
                nowrap;

            text-overflow:
                ellipsis;

        }


        .scanner-status {

            width:
                28px;

            height:
                28px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                8px;

            color:
                var(--purple);

            background:
                var(--purple-soft);

            animation:
                scannerRotate
                2.4s
                linear
                infinite;

        }


        @keyframes scannerRotate {

            to {

                transform:
                    rotate(360deg);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ROUTE WORLD
        |--------------------------------------------------------------------------
        */

        .route-world {

            position:
                absolute;

            inset:
                78px 22px 24px;

        }


        /*
        |--------------------------------------------------------------------------
        | ROUTE CONNECTION
        |--------------------------------------------------------------------------
        */

        .route-line {

            position:
                absolute;

            top:
                49%;

            left:
                11%;

            right:
                11%;

            height:
                2px;

            transform:
                translateY(-50%);

            background:

                repeating-linear-gradient(
                    90deg,
                    rgba(
                        18,
                        183,
                        106,
                        .55
                    ) 0 8px,
                    transparent 8px 16px
                );

        }


        .route-line.missing-segment {

            left:
                57%;

            right:
                11%;

            background:

                repeating-linear-gradient(
                    90deg,
                    rgba(
                        122,
                        90,
                        248,
                        .50
                    ) 0 8px,
                    transparent 8px 16px
                );

            animation:
                missingLine
                1.6s
                ease-in-out
                infinite;

        }


        @keyframes missingLine {

            0%,
            100% {

                opacity:
                    .18;

            }

            50% {

                opacity:
                    .9;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | NODES
        |--------------------------------------------------------------------------
        */

        .route-node {

            position:
                absolute;

            top:
                49%;

            z-index:
                4;

            transform:
                translate(
                    -50%,
                    -50%
                );

        }


        .route-node.origin {

            left:
                13%;

        }


        .route-node.midpoint {

            left:
                50%;

        }


        .route-node.destination {

            left:
                87%;

        }


        /*
        |--------------------------------------------------------------------------
        | NODE CARD
        |--------------------------------------------------------------------------
        */

        .node-card {

            position:
                relative;

            width:
                92px;

            padding:
                13px 9px 11px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid
                #e0e8e4;

            border-radius:
                17px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .96
                );

            box-shadow:

                0 12px 30px
                rgba(
                    11,
                    61,
                    46,
                    .09
                );

        }


        .node-icon {

            width:
                38px;

            height:
                38px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                12px;

            color:
                var(--green-dark);

            background:
                var(--green-soft);

            font-size:
                14px;

        }


        .node-card strong {

            margin-top:
                8px;

            color:
                #334155;

            font-size:
                9px;

        }


        .node-card small {

            margin-top:
                3px;

            color:
                #94a3b8;

            font-size:
                7px;

            white-space:
                nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | MIDPOINT NODE
        |--------------------------------------------------------------------------
        */

        .route-node.midpoint .node-card {

            width:
                112px;

            padding:
                15px 10px 13px;

            border-color:
                #cfe9dc;

            box-shadow:

                0 16px 34px
                rgba(
                    18,
                    183,
                    106,
                    .14
                );

        }


        .route-node.midpoint .node-icon {

            width:
                46px;

            height:
                46px;

            color:
                white;

            background:

                linear-gradient(
                    135deg,
                    var(--green-dark),
                    var(--green)
                );

        }


        /*
        |--------------------------------------------------------------------------
        | MIDPOINT RADAR
        |--------------------------------------------------------------------------
        */

        .route-node.midpoint::before,
        .route-node.midpoint::after {

            content:
                '';

            position:
                absolute;

            top:
                50%;

            left:
                50%;

            width:
                135px;

            height:
                135px;

            border:
                1px solid
                rgba(
                    18,
                    183,
                    106,
                    .20
                );

            border-radius:
                50%;

            pointer-events:
                none;

            animation:
                midpointRadar
                2.6s
                ease-out
                infinite;

        }


        .route-node.midpoint::after {

            animation-delay:
                1.3s;

        }


        @keyframes midpointRadar {

            0% {

                transform:
                    translate(
                        -50%,
                        -50%
                    )
                    scale(.45);

                opacity:
                    .9;

            }

            100% {

                transform:
                    translate(
                        -50%,
                        -50%
                    )
                    scale(1.25);

                opacity:
                    0;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | MISSING DESTINATION PAGE
        |--------------------------------------------------------------------------
        */

        .destination-shell {

            position:
                relative;

            width:
                92px;

            height:
                88px;

        }


        .ghost-page {

            position:
                absolute;

            inset:
                0;

            padding:
                12px;

            border:
                1px dashed
                rgba(
                    122,
                    90,
                    248,
                    .58
                );

            border-radius:
                17px;

            background:
                rgba(
                    245,
                    243,
                    255,
                    .68
                );

            box-shadow:

                0 14px 28px
                rgba(
                    122,
                    90,
                    248,
                    .10
                );

            animation:
                pageDisappear
                3.5s
                ease-in-out
                infinite;

        }


        /*
        |--------------------------------------------------------------------------
        | PAGE FOLD
        |--------------------------------------------------------------------------
        */

        .ghost-page::before {

            content:
                '';

            position:
                absolute;

            top:
                -1px;

            right:
                -1px;

            width:
                25px;

            height:
                25px;

            border-left:
                1px dashed
                rgba(
                    122,
                    90,
                    248,
                    .46
                );

            border-bottom:
                1px dashed
                rgba(
                    122,
                    90,
                    248,
                    .46
                );

            border-radius:
                0 17px 0 8px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .85
                );

        }


        .ghost-row {

            height:
                5px;

            margin-bottom:
                7px;

            border-radius:
                99px;

            background:
                rgba(
                    122,
                    90,
                    248,
                    .20
                );

        }


        .ghost-row:nth-child(2) {

            width:
                74%;

        }


        .ghost-row:nth-child(3) {

            width:
                52%;

        }


        /*
        |--------------------------------------------------------------------------
        | PAGE X
        |--------------------------------------------------------------------------
        */

        .ghost-x {

            position:
                absolute;

            left:
                50%;

            bottom:
                -15px;

            width:
                30px;

            height:
                30px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                50%;

            color:
                white;

            background:
                var(--purple);

            box-shadow:

                0 8px 20px
                rgba(
                    122,
                    90,
                    248,
                    .28
                );

            font-size:
                10px;

            animation:
                xPop
                3.5s
                ease-in-out
                infinite;

        }


        @keyframes pageDisappear {

            0%,
            18% {

                transform:
                    translateY(0)
                    scale(1);

                opacity:
                    .95;

                filter:
                    blur(0);

            }

            42% {

                transform:
                    translateY(-4px)
                    scale(.98);

                opacity:
                    .25;

                filter:
                    blur(1px);

            }

            55%,
            70% {

                transform:
                    translateY(4px)
                    scale(.84);

                opacity:
                    .06;

                filter:
                    blur(2px);

            }

            100% {

                transform:
                    translateY(0)
                    scale(1);

                opacity:
                    .95;

                filter:
                    blur(0);

            }

        }


        @keyframes xPop {

            0%,
            25%,
            100% {

                transform:
                    translateX(-50%)
                    scale(.5);

                opacity:
                    0;

            }

            45%,
            72% {

                transform:
                    translateX(-50%)
                    scale(1);

                opacity:
                    1;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | MOVING REQUEST PACKET
        |--------------------------------------------------------------------------
        */

        .packet {

            position:
                absolute;

            top:
                calc(49% - 8px);

            left:
                13%;

            z-index:
                5;

            width:
                16px;

            height:
                16px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                50%;

            color:
                white;

            background:
                var(--green);

            box-shadow:

                0 0 0 6px
                rgba(
                    18,
                    183,
                    106,
                    .10
                );

            font-size:
                6px;

            animation:
                packetSearch
                4s
                cubic-bezier(
                    .55,
                    .08,
                    .33,
                    .99
                )
                infinite;

        }


        @keyframes packetSearch {

            0% {

                left:
                    13%;

                opacity:
                    0;

                transform:
                    scale(.6);

            }

            8% {

                opacity:
                    1;

                transform:
                    scale(1);

            }

            43% {

                left:
                    50%;

                background:
                    var(--green);

            }

            70% {

                left:
                    82%;

                opacity:
                    1;

                background:
                    var(--purple);

            }

            79% {

                left:
                    82%;

                transform:
                    scale(1.2);

                opacity:
                    .35;

            }

            90%,
            100% {

                left:
                    82%;

                transform:
                    scale(.3);

                opacity:
                    0;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | DESTINATION SEARCH RING
        |--------------------------------------------------------------------------
        */

        .search-ring {

            position:
                absolute;

            top:
                49%;

            left:
                87%;

            width:
                145px;

            height:
                145px;

            border:
                1px dashed
                rgba(
                    122,
                    90,
                    248,
                    .22
                );

            border-radius:
                50%;

            animation:
                ringSpin
                10s
                linear
                infinite;

        }


        .search-ring::before,
        .search-ring::after {

            content:
                '';

            position:
                absolute;

            border-radius:
                50%;

        }


        .search-ring::before {

            inset:
                13px;

            border:
                1px dashed
                rgba(
                    122,
                    90,
                    248,
                    .15
                );

        }


        .search-ring::after {

            width:
                7px;

            height:
                7px;

            top:
                11px;

            left:
                50%;

            background:
                var(--purple);

            box-shadow:

                0 0 0 5px
                rgba(
                    122,
                    90,
                    248,
                    .10
                );

        }


        @keyframes ringSpin {

            to {

                transform:
                    rotate(360deg);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SCAN BEAM
        |--------------------------------------------------------------------------
        */

        .scan-beam {

            position:
                absolute;

            left:
                50%;

            bottom:
                26px;

            width:
                82%;

            height:
                82px;

            overflow:
                hidden;

            opacity:
                .6;

            transform:
                translateX(-50%);

        }


        .scan-beam::before {

            content:
                '';

            position:
                absolute;

            left:
                0;

            top:
                50%;

            width:
                34%;

            height:
                1px;

            background:

                linear-gradient(
                    90deg,
                    transparent,
                    var(--green),
                    transparent
                );

            box-shadow:

                0 0 14px
                rgba(
                    18,
                    183,
                    106,
                    .35
                );

            animation:
                scanSweep
                3.2s
                ease-in-out
                infinite;

        }


        @keyframes scanSweep {

            0%,
            100% {

                left:
                    -20%;

                opacity:
                    0;

            }

            18% {

                opacity:
                    1;

            }

            82% {

                opacity:
                    1;

            }

            100% {

                left:
                    90%;

                opacity:
                    0;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | FLOATING STATUS CHIPS
        |--------------------------------------------------------------------------
        */

        .float-chip {

            position:
                absolute;

            z-index:
                7;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                8px 10px;

            border:
                1px solid
                #e6ebe8;

            border-radius:
                11px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .92
                );

            box-shadow:

                0 9px 24px
                rgba(
                    11,
                    61,
                    46,
                    .08
                );

            color:
                var(--muted);

            font-size:
                8px;

            font-weight:
                800;

            animation:
                chipFloat
                4.2s
                ease-in-out
                infinite;

        }


        .float-chip i {

            color:
                var(--green);

        }


        .float-chip.one {

            top:
                88px;

            left:
                30px;

        }


        .float-chip.two {

            top:
                95px;

            right:
                28px;

            animation-delay:
                -1.4s;

        }


        .float-chip.two i {

            color:
                var(--purple);

        }


        @keyframes chipFloat {

            0%,
            100% {

                transform:
                    translateY(0);

            }

            50% {

                transform:
                    translateY(-7px);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | VISUAL STATUS
        |--------------------------------------------------------------------------
        */

        .visual-label {

            position:
                absolute;

            left:
                50%;

            bottom:
                27px;

            z-index:
                8;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                8px 11px;

            border:
                1px solid
                #e0e8e4;

            border-radius:
                999px;

            transform:
                translateX(-50%);

            background:
                rgba(
                    255,
                    255,
                    255,
                    .90
                );

            color:
                var(--muted);

            font-size:
                9px;

            font-weight:
                800;

            white-space:
                nowrap;

        }


        .visual-label i {

            color:
                var(--purple);

            animation:
                lensPulse
                1.8s
                ease-in-out
                infinite;

        }


        @keyframes lensPulse {

            0%,
            100% {

                transform:
                    scale(1);

            }

            50% {

                transform:
                    scale(1.18);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .mp-error-footer {

            position:
                relative;

            z-index:
                5;

            width:
                min(
                    1160px,
                    calc(100% - 36px)
                );

            min-height:
                58px;

            margin:
                0 auto;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                18px;

            border-top:
                1px solid
                rgba(
                    220,
                    231,
                    225,
                    .85
                );

            color:
                #94a3b8;

            font-size:
                10px;

            font-weight:
                600;

        }


        .mp-error-footer span:first-child {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

        }


        .mp-error-footer i {

            color:
                var(--green);

        }


        /*
        |--------------------------------------------------------------------------
        | TABLET
        |--------------------------------------------------------------------------
        */

        @media(max-width: 980px) {

            .mp-error-main {

                grid-template-columns:
                    1fr;

                gap:
                    18px;

                padding-top:
                    24px;

            }


            .mp-error-copy {

                max-width:
                    640px;

                margin:
                    0 auto;

                text-align:
                    center;

            }


            .mp-error-copy h1,
            .mp-error-description,
            .mp-current-route {

                margin-left:
                    auto;

                margin-right:
                    auto;

            }


            .mp-error-actions {

                justify-content:
                    center;

            }


            .mp-visual {

                min-height:
                    475px;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media(max-width: 620px) {

            .mp-error-header {

                height:
                    68px;

            }


            .mp-error-code-mini {

                display:
                    none;

            }


            .mp-error-main {

                width:
                    min(
                        100% - 24px,
                        530px
                    );

                padding:
                    18px 0 48px;

            }


            .mp-error-copy h1 {

                font-size:
                    31px;

            }


            .mp-error-description {

                font-size:
                    13px;

            }


            .mp-btn {

                width:
                    100%;

            }


            .mp-visual {

                min-height:
                    400px;

            }


            .route-lab {

                height:
                    385px;

                border-radius:
                    24px;

            }


            .route-world {

                inset:
                    72px 8px 18px;

            }


            .float-chip {

                display:
                    none;

            }


            .route-node.origin {

                left:
                    14%;

            }


            .route-node.midpoint {

                left:
                    49%;

            }


            .route-node.destination {

                left:
                    84%;

            }


            .node-card {

                width:
                    72px;

                padding:
                    10px 5px 9px;

            }


            .node-icon {

                width:
                    32px;

                height:
                    32px;

            }


            .route-node.midpoint
            .node-card {

                width:
                    84px;

                padding:
                    11px 6px 10px;

            }


            .route-node.midpoint
            .node-icon {

                width:
                    38px;

                height:
                    38px;

            }


            .destination-shell {

                width:
                    72px;

                height:
                    74px;

            }


            .route-line {

                left:
                    14%;

                right:
                    16%;

            }


            .route-line.missing-segment {

                left:
                    49%;

                right:
                    16%;

            }


            .search-ring {

                left:
                    84%;

                width:
                    112px;

                height:
                    112px;

            }


            .visual-label {

                bottom:
                    18px;

            }


            @keyframes packetSearch {

                0% {

                    left:
                        14%;

                    opacity:
                        0;

                    transform:
                        scale(.6);

                }

                8% {

                    opacity:
                        1;

                    transform:
                        scale(1);

                }

                43% {

                    left:
                        49%;

                }

                70% {

                    left:
                        78%;

                    opacity:
                        1;

                    background:
                        var(--purple);

                }

                79% {

                    left:
                        78%;

                    opacity:
                        .35;

                    transform:
                        scale(1.2);

                }

                90%,
                100% {

                    left:
                        78%;

                    opacity:
                        0;

                    transform:
                        scale(.3);

                }

            }


            .mp-error-footer {

                padding:
                    16px 0;

                flex-direction:
                    column;

                justify-content:
                    center;

                text-align:
                    center;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ACCESSIBILITY
        |--------------------------------------------------------------------------
        */

        @media(prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {

                animation-duration:
                    .001ms !important;

                animation-iteration-count:
                    1 !important;

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

            Route scan · HTTP 404

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

                <i class="fa-solid fa-route"></i>

                Route interrupted

            </div>


            <div class="mp-error-number">

                4<span class="zero">0</span>4

            </div>


            <h1>

                This route has no

                <span>
                    destination.
                </span>

            </h1>


            <p class="mp-error-description">

                The address reached MidPoint, but there is no page
                connected to this route. It may have moved, expired,
                or the URL may be incomplete.

            </p>


            {{-- =================================================
                CURRENT REQUESTED URL
            ================================================== --}}

            <div
                class="mp-current-route"
                title="{{ url()->current() }}"
            >

                <i class="fa-solid fa-link-slash"></i>

                <code>
                    {{ url()->current() }}
                </code>

            </div>


            {{-- =================================================
                ACTION BUTTONS
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
            RIGHT SIDE ANIMATED 404 VISUALIZATION
        ====================================================== --}}

        <section
            class="mp-visual"
            aria-label="Animated route visualization showing that the requested page destination cannot be found."
        >


            <div class="route-lab">


                {{-- =============================================
                    MINI BROWSER
                ============================================== --}}

                <div class="lab-topbar">


                    <div
                        class="browser-dots"
                        aria-hidden="true"
                    >

                        <span></span>

                        <span></span>

                        <span></span>

                    </div>


                    <div class="route-address">

                        <i class="fa-solid fa-lock"></i>

                        <span>
                            {{ url()->current() }}
                        </span>

                    </div>


                    <div
                        class="scanner-status"
                        aria-hidden="true"
                    >

                        <i class="fa-solid fa-arrows-rotate"></i>

                    </div>


                </div>


                {{-- =============================================
                    FLOATING STATUS
                ============================================== --}}

                <div
                    class="float-chip one"
                    aria-hidden="true"
                >

                    <i class="fa-solid fa-shield-halved"></i>

                    MidPoint reached

                </div>


                <div
                    class="float-chip two"
                    aria-hidden="true"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Searching endpoint

                </div>


                {{-- =============================================
                    ROUTE NETWORK
                ============================================== --}}

                <div
                    class="route-world"
                    aria-hidden="true"
                >


                    {{-- =========================================
                        ROUTE LINES
                    ========================================== --}}

                    <div class="route-line"></div>

                    <div
                        class="
                            route-line
                            missing-segment
                        "
                    ></div>


                    {{-- =========================================
                        REQUEST ORIGIN
                    ========================================== --}}

                    <div
                        class="
                            route-node
                            origin
                        "
                    >


                        <div class="node-card">


                            <span class="node-icon">

                                <i class="fa-solid fa-user"></i>

                            </span>


                            <strong>
                                Your request
                            </strong>


                            <small>
                                Route started
                            </small>


                        </div>


                    </div>


                    {{-- =========================================
                        MIDPOINT
                    ========================================== --}}

                    <div
                        class="
                            route-node
                            midpoint
                        "
                    >


                        <div class="node-card">


                            <span class="node-icon">

                                <i class="fa-solid fa-shield-halved"></i>

                            </span>


                            <strong>
                                MidPoint
                            </strong>


                            <small>
                                Route resolver
                            </small>


                        </div>


                    </div>


                    {{-- =========================================
                        SEARCH RING
                    ========================================== --}}

                    <div class="search-ring"></div>


                    {{-- =========================================
                        MISSING PAGE
                    ========================================== --}}

                    <div
                        class="
                            route-node
                            destination
                        "
                    >


                        <div class="destination-shell">


                            <div class="ghost-page">


                                <div class="ghost-row"></div>

                                <div class="ghost-row"></div>

                                <div class="ghost-row"></div>


                                <span class="ghost-x">

                                    <i class="fa-solid fa-xmark"></i>

                                </span>


                            </div>


                        </div>


                    </div>


                    {{-- =========================================
                        MOVING REQUEST PACKET
                    ========================================== --}}

                    <span class="packet">

                        <i class="fa-solid fa-arrow-right"></i>

                    </span>


                    {{-- =========================================
                        SEARCH SCANNER
                    ========================================== --}}

                    <div class="scan-beam"></div>


                </div>


                {{-- =============================================
                    VISUAL RESULT
                ============================================== --}}

                <div class="visual-label">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Destination node not detected

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

            MidPoint safely returned you from an unknown route.

        </span>


        <span>

            Error reference:
            404 · Not Found

        </span>


    </footer>


</div>


</body>

</html>