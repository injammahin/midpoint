@php
    /*
    |--------------------------------------------------------------------------
    | Uploaded MidPoint Logo
    |--------------------------------------------------------------------------
    */

    $configuredLogoPath = trim(
        (string) config('midpoint.logo_path', '')
    );

    $relativeLogoPath = ltrim(
        $configuredLogoPath,
        '/'
    );

    $logoUrl = null;

    if ($relativeLogoPath !== '') {
        $absoluteLogoPath = public_path(
            $relativeLogoPath
        );

        if (is_file($absoluteLogoPath)) {
            /*
            |--------------------------------------------------------------------------
            | Public Logo URL
            |--------------------------------------------------------------------------
            |
            | Do not use $message->embed() here because Gmail may display
            | the embedded logo as a separate attachment.
            |
            */

            $logoUrl = asset(
                $relativeLogoPath
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Recipient Name
    |--------------------------------------------------------------------------
    */

    $recipientName = trim(
        (string) ($user->name ?? '')
    );

    $recipientName =
        $recipientName !== ''
            ? $recipientName
            : 'there';
@endphp
<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="color-scheme"
        content="light"
    >

    <meta
        name="supported-color-schemes"
        content="light"
    >

    <title>
        Verify your Midpoint email address
    </title>

    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #F2F5F3;
        }

        body,
        table,
        td,
        a {
            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }

        table,
        td {
            border-collapse: collapse !important;
        }

        img {
            display: block;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        a {
            text-decoration: none;
        }

        @media only screen and (max-width: 620px) {
            .email-shell {
                width: 100% !important;
            }

            .mobile-page-padding {
                padding:
                    20px 12px !important;
            }

            .email-card {
                border-radius:
                    14px !important;
            }

            .email-header {
                padding:
                    30px 22px 20px !important;
            }

            .email-content {
                padding:
                    4px 22px 30px !important;
            }

            .email-title {
                font-size:
                    25px !important;

                line-height:
                    32px !important;
            }

            .email-copy {
                font-size:
                    14px !important;

                line-height:
                    22px !important;
            }

            .footer-content {
                padding-right:
                    14px !important;

                padding-left:
                    14px !important;
            }
        }
    </style>

</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #F2F5F3;
    "
>

    {{-- =========================================================
        HIDDEN EMAIL PREVIEW
    ========================================================== --}}

    <div
        style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
            line-height: 1px;
            mso-hide: all;
        "
    >
        Verify your Midpoint email address to activate your account.
    </div>


    {{-- =========================================================
        EMAIL BACKGROUND
    ========================================================== --}}

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width: 100%;
            background-color: #F2F5F3;
        "
    >

        <tr>

            <td
                align="center"
                class="mobile-page-padding"
                style="
                    padding: 42px 16px;
                "
            >

                {{-- =================================================
                    EMAIL WRAPPER
                ================================================== --}}

                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    class="email-shell"
                    style="
                        width: 600px;
                        max-width: 600px;
                    "
                >

                    {{-- =============================================
                        MAIN WHITE CARD
                    ============================================== --}}

                    <tr>

                        <td
                            class="email-card"
                            style="
                                overflow: hidden;
                                border: 1px solid #DFE7E2;
                                border-radius: 18px;
                                background-color: #FFFFFF;
                                box-shadow:
                                    0 14px 40px
                                    rgba(11, 61, 46, 0.08);
                            "
                        >

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >

                                {{-- =================================
                                    TOP BRAND ACCENT
                                ================================== --}}

                                <tr>

                                    <td
                                        height="5"
                                        style="
                                            height: 5px;
                                            background-color: #12B76A;
                                            font-size: 0;
                                            line-height: 0;
                                        "
                                    >
                                        &nbsp;
                                    </td>

                                </tr>


                                {{-- =================================
                                    UPLOADED LOGO
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="email-header"
                                        style="
                                            padding:
                                                34px 40px 22px;
                                        "
                                    >

                                        <a
                                            href="{{ route('home') }}"
                                            aria-label="Visit MidPoint"
                                            style="
                                                display: inline-block;
                                            "
                                        >

                                            @if($logoUrl)

                                                <img
                                                    src="{{ $logoUrl }}"
                                                    alt="MidPoint"
                                                    width="190"
                                                    style="
                                                        display: block;
                                                        width: auto;
                                                        height: auto;
                                                        max-width: 190px;
                                                        max-height: 58px;
                                                    "
                                                >

                                            @else

                                                {{-- =====================
                                                    FALLBACK LOGO
                                                ====================== --}}

                                                <table
                                                    role="presentation"
                                                    cellpadding="0"
                                                    cellspacing="0"
                                                    border="0"
                                                >

                                                    <tr>

                                                        <td
                                                            align="center"
                                                            width="38"
                                                            height="38"
                                                            style="
                                                                width: 38px;
                                                                height: 38px;
                                                                border-radius: 11px;
                                                                background-color: #0B3D2E;
                                                                color: #FFFFFF;
                                                                font-size: 18px;
                                                                font-weight: 700;
                                                                line-height: 38px;
                                                                text-align: center;
                                                            "
                                                        >
                                                            M
                                                        </td>

                                                        <td
                                                            style="
                                                                padding-left: 10px;
                                                                color: #0B3D2E;
                                                                font-size: 24px;
                                                                font-weight: 700;
                                                                line-height: 30px;
                                                                vertical-align: middle;
                                                            "
                                                        >
                                                            Mid<span
                                                                style="
                                                                    color: #7A5AF8;
                                                                "
                                                            >Point</span>
                                                        </td>

                                                    </tr>

                                                </table>

                                            @endif

                                        </a>

                                    </td>

                                </tr>


                                {{-- =================================
                                    EMAIL CONTENT
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="email-content"
                                        style="
                                            padding:
                                                4px 48px 40px;
                                        "
                                    >

                                        {{-- =============================
                                            TITLE
                                        ============================== --}}

                                        <h1
                                            class="email-title"
                                            style="
                                                margin: 0;
                                                color: #17251F;
                                                font-size: 29px;
                                                font-weight: 700;
                                                line-height: 37px;
                                                text-align: center;
                                            "
                                        >
                                            Verify your email address
                                        </h1>


                                        {{-- =============================
                                            MESSAGE
                                        ============================== --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    18px 0 0;
                                                color: #53615A;
                                                font-size: 15px;
                                                line-height: 24px;
                                                text-align: center;
                                            "
                                        >
                                            Hi

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $recipientName }},
                                            </strong>

                                            please confirm that you want to
                                            use this email address for your
                                            Midpoint account. Once verified,
                                            you can securely access your
                                            account.
                                        </p>


                                        {{-- =============================
                                            VERIFICATION BUTTON
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 28px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    bgcolor="#0B3D2E"
                                                    style="
                                                        border-radius: 10px;
                                                    "
                                                >

                                                    <a
                                                        href="{{ $verificationUrl }}"
                                                        style="
                                                            display: block;
                                                            padding:
                                                                16px 24px;
                                                            border:
                                                                1px solid #0B3D2E;
                                                            border-radius: 10px;
                                                            background-color: #0B3D2E;
                                                            color: #FFFFFF;
                                                            font-size: 15px;
                                                            font-weight: 700;
                                                            line-height: 20px;
                                                            text-align: center;
                                                        "
                                                    >
                                                        Verify my email
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            EXPIRATION NOTICE
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 22px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border:
                                                            1px solid #D9E9E0;
                                                        border-radius: 9px;
                                                        background-color: #F4FAF7;
                                                        color: #52645A;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    For your security, this
                                                    verification link expires in

                                                    <strong
                                                        style="
                                                            color: #0B3D2E;
                                                        "
                                                    >
                                                        {{ $expireMinutes }}
                                                        minutes
                                                    </strong>.
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            MANUAL VERIFICATION URL
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    27px 0 0;
                                                color: #7B8781;
                                                font-size: 12px;
                                                line-height: 19px;
                                                text-align: center;
                                            "
                                        >
                                            Or copy and paste this link into
                                            your browser:
                                        </p>

                                        <p
                                            style="
                                                margin:
                                                    7px 0 0;
                                                color: #0E8A5D;
                                                font-size: 11px;
                                                line-height: 18px;
                                                text-align: center;
                                                word-break: break-all;
                                            "
                                        >

                                            <a
                                                href="{{ $verificationUrl }}"
                                                style="
                                                    color: #0E8A5D;
                                                    text-decoration: underline;
                                                "
                                            >
                                                {{ $verificationUrl }}
                                            </a>

                                        </p>


                                        {{-- =============================
                                            SEPARATOR
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 28px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    height="1"
                                                    style="
                                                        height: 1px;
                                                        background-color: #E7ECE9;
                                                        font-size: 0;
                                                        line-height: 0;
                                                    "
                                                >
                                                    &nbsp;
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            SECURITY MESSAGE
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    20px 0 0;
                                                color: #748078;
                                                font-size: 12px;
                                                line-height: 19px;
                                                text-align: center;
                                            "
                                        >
                                            If you did not create a MidPoint
                                            account, you can safely ignore
                                            this email.

                                            If you request another
                                            verification email, this link
                                            will automatically stop working.
                                        </p>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>


                    {{-- =============================================
                        EMAIL FOOTER
                    ============================================== --}}

                    <tr>

                        <td
                            align="center"
                            class="footer-content"
                            style="
                                padding:
                                    24px 24px 0;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    color: #7C8881;
                                    font-size: 11px;
                                    line-height: 18px;
                                    text-align: center;
                                "
                            >
                                &copy; {{ date('Y') }}
                                Midpoint Technologies Ltd.
                                All rights reserved.
                            </p>

                            <p
                                style="
                                    margin:
                                        5px 0 0;
                                    color: #98A29D;
                                    font-size: 11px;
                                    line-height: 18px;
                                    text-align: center;
                                "
                            >
                                Buy with confidence.
                                Sell with confidence.
                            </p>

                            <p
                                style="
                                    margin:
                                        12px 0 0;
                                    color: #A3ACA7;
                                    font-size: 11px;
                                    line-height: 18px;
                                    text-align: center;
                                "
                            >

                                <a
                                    href="{{ route('privacy-policy') }}"
                                    style="
                                        color: #607068;
                                    "
                                >
                                    Privacy Policy
                                </a>

                                <span
                                    style="
                                        padding:
                                            0 7px;
                                        color: #B8C0BC;
                                    "
                                >
                                    &bull;
                                </span>

                                <a
                                    href="{{ route('terms-and-conditions') }}"
                                    style="
                                        color: #607068;
                                    "
                                >
                                    Terms
                                </a>

                                <span
                                    style="
                                        padding:
                                            0 7px;
                                        color: #B8C0BC;
                                    "
                                >
                                    &bull;
                                </span>

                                <a
                                    href="{{ route('support') }}"
                                    style="
                                        color: #607068;
                                    "
                                >
                                    Support
                                </a>

                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>