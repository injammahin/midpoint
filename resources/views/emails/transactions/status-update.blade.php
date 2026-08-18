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
        {{ $heading }}
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

        a {
            text-decoration: none;
        }

        @media only screen and (max-width: 620px) {

            .email-shell {
                width: 100% !important;
            }

            .page-padding {
                padding:
                    20px 12px !important;
            }

            .email-card {
                border-radius:
                    14px !important;
            }

            .email-header {
                padding:
                    27px 22px 20px !important;
            }

            .email-content {
                padding:
                    28px 22px 30px !important;
            }

            .email-title {
                font-size:
                    23px !important;

                line-height:
                    30px !important;
            }

            .email-copy {
                font-size:
                    14px !important;

                line-height:
                    22px !important;
            }

            .detail-cell {
                padding:
                    13px 14px !important;
            }

            .footer-content {
                padding:
                    20px 16px !important;
            }
        }
    </style>
</head>


<body
    style="
        margin: 0;
        padding: 0;
        background-color: #F2F5F3;
        color: #17251F;
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
        {{ $heading }}. {{ $statusMessage }}
    </div>


    {{-- =========================================================
        EMAIL BACKGROUND
    ========================================================== --}}

    <table
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        role="presentation"
        style="
            width: 100%;
            background-color: #F2F5F3;
        "
    >

        <tr>

            <td
                align="center"
                class="page-padding"
                style="
                    padding: 42px 16px;
                "
            >

                {{-- =================================================
                    EMAIL WRAPPER
                ================================================== --}}

                <table
                    width="600"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    role="presentation"
                    class="email-shell"
                    style="
                        width: 600px;
                        max-width: 600px;
                    "
                >

                    {{-- =============================================
                        MAIN CARD
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
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                role="presentation"
                            >

                                {{-- =================================
                                    TOP ACCENT
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
                                    BRAND HEADER
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="email-header"
                                        style="
                                            padding:
                                                32px 40px 22px;
                                        "
                                    >

                                        <table
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
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


                                        <div
                                            style="
                                                margin-top: 10px;
                                                color: #85928B;
                                                font-size: 11px;
                                                line-height: 17px;
                                            "
                                        >
                                            Secure transaction update
                                        </div>

                                    </td>

                                </tr>


                                {{-- =================================
                                    MAIN CONTENT
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="email-content"
                                        style="
                                            padding:
                                                6px 48px 40px;
                                        "
                                    >

                                        {{-- =============================
                                            STATUS BADGE
                                        ============================== --}}

                                        <table
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    style="
                                                        padding:
                                                            7px 12px;
                                                        border:
                                                            1px solid #CDEEDD;
                                                        border-radius: 999px;
                                                        background-color: #ECFDF3;
                                                        color: #067647;
                                                        font-size: 10px;
                                                        font-weight: 700;
                                                        letter-spacing: 0.5px;
                                                        line-height: 14px;
                                                    "
                                                >
                                                    {{
                                                        $badgeText
                                                            ? strtoupper($badgeText)
                                                            : strtoupper($transaction->status_label)
                                                    }}
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            TITLE
                                        ============================== --}}

                                        <h1
                                            class="email-title"
                                            style="
                                                margin:
                                                    18px 0 0;
                                                color: #17251F;
                                                font-size: 28px;
                                                font-weight: 700;
                                                line-height: 36px;
                                                text-align: center;
                                            "
                                        >
                                            {{ $heading }}
                                        </h1>


                                        {{-- =============================
                                            MESSAGE
                                        ============================== --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    15px 0 0;
                                                color: #5B6962;
                                                font-size: 15px;
                                                line-height: 24px;
                                                text-align: center;
                                            "
                                        >
                                            {{ $statusMessage }}
                                        </p>


                                        {{-- =============================
                                            DETAILS TITLE
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 26px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="left"
                                                    style="
                                                        color: #26352E;
                                                        font-size: 14px;
                                                        font-weight: 700;
                                                        line-height: 20px;
                                                    "
                                                >
                                                    Transaction details
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            TRANSACTION DETAILS CARD
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 10px;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 11px;
                                                background-color: #FFFFFF;
                                            "
                                        >

                                            {{-- Transaction Reference --}}

                                            <tr>

                                                <td
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            14px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            color: #819087;
                                                            font-size: 11px;
                                                            line-height: 16px;
                                                        "
                                                    >
                                                        Transaction reference
                                                    </div>

                                                    <div
                                                        style="
                                                            margin-top: 4px;
                                                            color: #17251F;
                                                            font-size: 13px;
                                                            font-weight: 700;
                                                            line-height: 19px;
                                                            word-break: break-word;
                                                        "
                                                    >
                                                        {{ $transaction->reference }}
                                                    </div>

                                                </td>

                                            </tr>


                                            {{-- Item --}}

                                            <tr>

                                                <td
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            14px 16px;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            color: #819087;
                                                            font-size: 11px;
                                                            line-height: 16px;
                                                        "
                                                    >
                                                        Item
                                                    </div>

                                                    <div
                                                        style="
                                                            margin-top: 4px;
                                                            color: #17251F;
                                                            font-size: 13px;
                                                            font-weight: 700;
                                                            line-height: 19px;
                                                            word-break: break-word;
                                                        "
                                                    >
                                                        {{ $transaction->title }}
                                                    </div>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            SECURITY NOTICE
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 18px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="left"
                                                    style="
                                                        padding:
                                                            13px 15px;
                                                        border:
                                                            1px solid #D9E9E0;
                                                        border-radius: 9px;
                                                        background-color: #F4FAF7;
                                                        color: #52645A;
                                                        font-size: 11px;
                                                        line-height: 18px;
                                                    "
                                                >

                                                    <strong
                                                        style="
                                                            color: #0B3D2E;
                                                        "
                                                    >
                                                        Secure transaction update
                                                    </strong>

                                                    <br>

                                                    This notification reflects
                                                    the latest status of your
                                                    Midpoint transaction.

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            ACTION BUTTON
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 25px;
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
                                                        href="{{ $actionUrl }}"
                                                        style="
                                                            display: block;
                                                            padding:
                                                                16px 24px;
                                                            border:
                                                                1px solid #0B3D2E;
                                                            border-radius: 10px;
                                                            background-color: #0B3D2E;
                                                            color: #FFFFFF;
                                                            font-size: 14px;
                                                            font-weight: 700;
                                                            line-height: 20px;
                                                            text-align: center;
                                                        "
                                                    >
                                                        {{ $actionText }}
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            ACTION HELPER
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    11px 0 0;
                                                color: #8B9690;
                                                font-size: 10px;
                                                line-height: 16px;
                                                text-align: center;
                                            "
                                        >
                                            Open Midpoint to view the latest
                                            transaction information.
                                        </p>


                                        {{-- =============================
                                            SEPARATOR
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 24px;
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
                                            MANUAL LINK
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    18px 0 0;
                                                color: #7B8781;
                                                font-size: 11px;
                                                line-height: 18px;
                                                text-align: center;
                                            "
                                        >
                                            If the button does not work,
                                            copy and paste this link into
                                            your browser:
                                        </p>

                                        <p
                                            style="
                                                margin:
                                                    6px 0 0;
                                                color: #0E8A5D;
                                                font-size: 10px;
                                                line-height: 17px;
                                                text-align: center;
                                                word-break: break-all;
                                            "
                                        >

                                            <a
                                                href="{{ $actionUrl }}"
                                                style="
                                                    color: #0E8A5D;
                                                    text-decoration: underline;
                                                "
                                            >
                                                {{ $actionUrl }}
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
                                Midpoint &bull;
                                Secure transactions between buyers and sellers
                            </p>

                            <p
                                style="
                                    margin:
                                        5px 0 0;
                                    color: #9BA49F;
                                    font-size: 10px;
                                    line-height: 17px;
                                    text-align: center;
                                "
                            >
                                Buy with confidence.
                                Sell with confidence.
                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>