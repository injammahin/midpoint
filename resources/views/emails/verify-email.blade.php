<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
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
        Verify your MidPoint email address
    </title>


    <style>

        /*
        |--------------------------------------------------------------------------
        | Email Responsive Styles
        |--------------------------------------------------------------------------
        */

        body {
            margin: 0;
            padding: 0;
            background: #F3F7F5;
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            color: #17251F;
        }


        table {
            border-spacing: 0;
            border-collapse: collapse;
        }


        img {
            border: 0;
            display: block;
        }


        a {
            text-decoration: none;
        }


        @media only screen and (max-width: 620px) {

            .email-wrapper {
                width: 100% !important;
            }


            .email-card {
                border-radius: 0 !important;
            }


            .email-content {
                padding:
                    30px 22px !important;
            }


            .email-title {
                font-size:
                    25px !important;
            }


            .email-button {
                display: block !important;
                width: auto !important;
                text-align: center !important;
            }

        }

    </style>

</head>


<body>


    {{-- =========================================================
        OUTER BACKGROUND
    ========================================================== --}}
    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        style="
            width: 100%;
            background: #F3F7F5;
        "
    >

        <tr>

            <td
                align="center"
                style="
                    padding:
                        42px 16px;
                "
            >


                {{-- =================================================
                    EMAIL WIDTH
                ================================================== --}}
                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    class="email-wrapper"
                    style="
                        width: 600px;
                        max-width: 600px;
                    "
                >


                    {{-- =============================================
                        BRAND
                    ============================================== --}}
                    <tr>

                        <td
                            align="center"
                            style="
                                padding-bottom: 24px;
                            "
                        >

                            <a
                                href="{{ route('home') }}"
                                style="
                                    display: inline-block;
                                "
                            >

                                <table
                                    role="presentation"
                                    cellpadding="0"
                                    cellspacing="0"
                                >

                                    <tr>


                                        {{-- Icon --}}
                                        <td>

                                            <div
                                                style="
                                                    width: 40px;
                                                    height: 40px;
                                                    line-height: 40px;
                                                    text-align: center;
                                                    border-radius: 12px;
                                                    background: #0B7A53;
                                                    color: #FFFFFF;
                                                    font-size: 19px;
                                                    font-weight: 700;
                                                "
                                            >
                                                M
                                            </div>

                                        </td>


                                        {{-- Name --}}
                                        <td
                                            style="
                                                padding-left: 10px;
                                                font-size: 24px;
                                                font-weight: 700;
                                                color: #0B3D2E;
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

                            </a>

                        </td>

                    </tr>



                    {{-- =============================================
                        MAIN CARD
                    ============================================== --}}
                    <tr>

                        <td
                            class="email-card"
                            style="
                                overflow: hidden;
                                border:
                                    1px solid #E1E9E5;
                                border-radius: 18px;
                                background: #FFFFFF;
                                box-shadow:
                                    0 18px 45px rgba(
                                        11,
                                        61,
                                        46,
                                        0.08
                                    );
                            "
                        >


                            {{-- =========================================
                                TOP GREEN ACCENT
                            ========================================== --}}
                            <div
                                style="
                                    height: 5px;
                                    background:
                                        linear-gradient(
                                            90deg,
                                            #0B3D2E 0%,
                                            #12B76A 55%,
                                            #7A5AF8 100%
                                        );
                                "
                            ></div>



                            {{-- =========================================
                                CONTENT
                            ========================================== --}}
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                            >

                                <tr>

                                    <td
                                        class="email-content"
                                        style="
                                            padding:
                                                42px 44px 38px;
                                        "
                                    >


                                        {{-- =================================
                                            EMAIL ICON
                                        ================================== --}}
                                        <table
                                            role="presentation"
                                            cellpadding="0"
                                            cellspacing="0"
                                        >

                                            <tr>

                                                <td
                                                    style="
                                                        width: 54px;
                                                        height: 54px;
                                                        border-radius: 15px;
                                                        background: #E8F7EF;
                                                        color: #0B8B5A;
                                                        font-size: 26px;
                                                        text-align: center;
                                                        vertical-align: middle;
                                                    "
                                                >
                                                    ✉
                                                </td>

                                            </tr>

                                        </table>



                                        {{-- =================================
                                            TITLE
                                        ================================== --}}
                                        <h1
                                            class="email-title"
                                            style="
                                                margin:
                                                    25px 0 8px;
                                                color: #101915;
                                                font-size: 30px;
                                                line-height: 1.25;
                                                font-weight: 700;
                                            "
                                        >
                                            Verify your email address
                                        </h1>



                                        {{-- =================================
                                            GREETING
                                        ================================== --}}
                                        <p
                                            style="
                                                margin:
                                                    20px 0 0;
                                                color: #435149;
                                                font-size: 15px;
                                                line-height: 1.7;
                                            "
                                        >

                                            Hi

                                            <strong
                                                style="
                                                    color: #16261F;
                                                "
                                            >
                                                {{ $user->name }},
                                            </strong>

                                        </p>



                                        {{-- =================================
                                            MESSAGE
                                        ================================== --}}
                                        <p
                                            style="
                                                margin:
                                                    10px 0 0;
                                                color: #59665F;
                                                font-size: 15px;
                                                line-height: 1.75;
                                            "
                                        >
                                            Thanks for creating your
                                            MidPoint account. Please confirm
                                            that this email address belongs
                                            to you before you start using
                                            your account.
                                        </p>



                                        {{-- =================================
                                            BUTTON
                                        ================================== --}}
                                        <table
                                            role="presentation"
                                            cellpadding="0"
                                            cellspacing="0"
                                            style="
                                                margin-top: 28px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    bgcolor="#0B3D2E"
                                                    style="
                                                        border-radius: 11px;
                                                    "
                                                >

                                                    <a
                                                        href="{{ $verificationUrl }}"
                                                        class="email-button"
                                                        style="
                                                            display: inline-block;
                                                            padding:
                                                                15px 28px;
                                                            border-radius: 11px;
                                                            background: #0B3D2E;
                                                            color: #FFFFFF;
                                                            font-size: 14px;
                                                            font-weight: 700;
                                                        "
                                                    >
                                                        Verify email address
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>



                                        {{-- =================================
                                            EXPIRY
                                        ================================== --}}
                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            style="
                                                margin-top: 28px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    style="
                                                        padding: 14px 16px;
                                                        border:
                                                            1px solid #DCEAE3;
                                                        border-radius: 11px;
                                                        background: #F5FAF7;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        cellpadding="0"
                                                        cellspacing="0"
                                                    >

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding-right: 10px;
                                                                    font-size: 18px;
                                                                    vertical-align: top;
                                                                "
                                                            >
                                                                ⏱
                                                            </td>


                                                            <td>

                                                                <strong
                                                                    style="
                                                                        display: block;
                                                                        color: #0B3D2E;
                                                                        font-size: 13px;
                                                                    "
                                                                >
                                                                    Link expires in
                                                                    {{ $expireMinutes }}
                                                                    minutes
                                                                </strong>


                                                                <span
                                                                    style="
                                                                        display: block;
                                                                        margin-top: 4px;
                                                                        color: #718078;
                                                                        font-size: 12px;
                                                                        line-height: 1.5;
                                                                    "
                                                                >
                                                                    For your security,
                                                                    verification links are
                                                                    time-limited.
                                                                </span>

                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>

                                            </tr>

                                        </table>



                                        {{-- =================================
                                            NEW LINK WARNING
                                        ================================== --}}
                                        <p
                                            style="
                                                margin:
                                                    25px 0 0;
                                                color: #66746D;
                                                font-size: 13px;
                                                line-height: 1.7;
                                            "
                                        >
                                            If you request another
                                            verification email, this link
                                            will automatically stop working.
                                        </p>



                                        {{-- =================================
                                            SECURITY
                                        ================================== --}}
                                        <p
                                            style="
                                                margin:
                                                    10px 0 0;
                                                color: #66746D;
                                                font-size: 13px;
                                                line-height: 1.7;
                                            "
                                        >
                                            If you didn't create a MidPoint
                                            account, you can safely ignore
                                            this email.
                                        </p>



                                        {{-- =================================
                                            SEPARATOR
                                        ================================== --}}
                                        <div
                                            style="
                                                height: 1px;
                                                margin:
                                                    28px 0;
                                                background: #E7ECE9;
                                            "
                                        ></div>



                                        {{-- =================================
                                            MANUAL URL
                                        ================================== --}}
                                        <p
                                            style="
                                                margin: 0;
                                                color: #7A8680;
                                                font-size: 11px;
                                                line-height: 1.6;
                                            "
                                        >
                                            Having trouble with the button?
                                            Copy and paste this link into
                                            your browser:
                                        </p>


                                        <p
                                            style="
                                                margin:
                                                    8px 0 0;
                                                word-break: break-all;
                                                color: #0E8A5D;
                                                font-size: 11px;
                                                line-height: 1.6;
                                            "
                                        >
                                            <a
                                                href="{{ $verificationUrl }}"
                                                style="
                                                    color: #0E8A5D;
                                                "
                                            >
                                                {{ $verificationUrl }}
                                            </a>
                                        </p>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    {{-- =============================================
                        FOOTER
                    ============================================== --}}
                    <tr>

                        <td
                            align="center"
                            style="
                                padding:
                                    25px 15px 0;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    color: #7A8780;
                                    font-size: 12px;
                                    line-height: 1.6;
                                "
                            >
                                © {{ date('Y') }}
                                MidPoint Technologies Ltd.
                                All rights reserved.
                            </p>


                            <p
                                style="
                                    margin:
                                        7px 0 0;
                                    color: #9AA49F;
                                    font-size: 11px;
                                    line-height: 1.6;
                                "
                            >
                                Buy with confidence.
                                Sell with confidence.
                            </p>


                            <p
                                style="
                                    margin:
                                        13px 0 0;
                                    font-size: 11px;
                                "
                            >

                                <a
                                    href="{{ route('privacy-policy') }}"
                                    style="
                                        color: #5E6C65;
                                    "
                                >
                                    Privacy Policy
                                </a>

                                <span
                                    style="
                                        padding:
                                            0 6px;
                                        color: #B1BAB5;
                                    "
                                >
                                    •
                                </span>


                                <a
                                    href="{{ route('terms-and-conditions') }}"
                                    style="
                                        color: #5E6C65;
                                    "
                                >
                                    Terms
                                </a>

                                <span
                                    style="
                                        padding:
                                            0 6px;
                                        color: #B1BAB5;
                                    "
                                >
                                    •
                                </span>


                                <a
                                    href="{{ route('support') }}"
                                    style="
                                        color: #5E6C65;
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