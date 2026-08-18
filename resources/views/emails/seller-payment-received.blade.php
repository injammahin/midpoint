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
        Payment Received
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
                    28px 22px 20px !important;
            }

            .email-content {
                padding:
                    4px 20px 30px !important;
            }

            .email-title {
                font-size:
                    24px !important;

                line-height:
                    31px !important;
            }

            .email-copy {
                font-size:
                    14px !important;

                line-height:
                    22px !important;
            }

            .detail-label {
                width:
                    35% !important;
            }

            .detail-cell {
                padding:
                    12px 13px !important;
            }

            .amount-value {
                font-size:
                    27px !important;

                line-height:
                    34px !important;
            }

            .footer-content {
                padding-right:
                    15px !important;

                padding-left:
                    15px !important;
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
        Payment for {{ $transaction->title }}
        has been successfully verified and secured.
    </div>


    {{-- =========================================================
        EMAIL BACKGROUND
    ========================================================== --}}

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
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
                    width="600"
                    role="presentation"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
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
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
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
                                            role="presentation"
                                            cellspacing="0"
                                            cellpadding="0"
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


                                        <div
                                            style="
                                                margin-top: 10px;
                                                color: #87938D;
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
                                                4px 48px 40px;
                                        "
                                    >

                                        {{-- =============================
                                            STATUS BADGE
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
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
                                                        letter-spacing: 0.6px;
                                                        line-height: 14px;
                                                    "
                                                >
                                                    PAYMENT SECURED
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
                                            Your buyer has completed payment.
                                        </h1>


                                        {{-- =============================
                                            MESSAGE
                                        ============================== --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    16px 0 0;
                                                color: #58665F;
                                                font-size: 15px;
                                                line-height: 24px;
                                                text-align: center;
                                            "
                                        >
                                            Midpoint has successfully verified
                                            the payment for

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $transaction->title }}
                                            </strong>.
                                        </p>


                                        {{-- =============================
                                            AMOUNT SECURED
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 25px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    style="
                                                        padding:
                                                            22px 18px;
                                                        border:
                                                            1px solid #D5EADD;
                                                        border-radius: 12px;
                                                        background-color: #F2FCF6;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            color: #68776F;
                                                            font-size: 11px;
                                                            font-weight: 600;
                                                            line-height: 16px;
                                                            letter-spacing: 0.2px;
                                                        "
                                                    >
                                                        AMOUNT SECURED
                                                    </div>


                                                    <div
                                                        class="amount-value"
                                                        style="
                                                            margin-top: 5px;
                                                            color: #0B3D2E;
                                                            font-size: 30px;
                                                            font-weight: 800;
                                                            line-height: 38px;
                                                        "
                                                    >
                                                        ₦{{ number_format((float) ($transaction->paid_amount ?: $transaction->total_amount), 2) }}
                                                    </div>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            TRANSACTION DETAILS HEADING
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
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
                                                        font-size: 15px;
                                                        font-weight: 700;
                                                        line-height: 21px;
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
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 10px;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 11px;
                                                background-color: #FFFFFF;
                                            "
                                        >

                                            {{-- Transaction --}}

                                            <tr>

                                                <td
                                                    width="35%"
                                                    class="detail-label detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Transaction
                                                </td>


                                                <td
                                                    align="right"
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $transaction->reference }}
                                                </td>

                                            </tr>


                                            {{-- Item --}}

                                            <tr>

                                                <td
                                                    width="35%"
                                                    class="detail-label detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Item
                                                </td>


                                                <td
                                                    align="right"
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $transaction->title }}
                                                </td>

                                            </tr>


                                            {{-- Buyer --}}

                                            <tr>

                                                <td
                                                    width="35%"
                                                    class="detail-label detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Buyer
                                                </td>


                                                <td
                                                    align="right"
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $transaction->buyer?->name ?: $transaction->buyer_email }}
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            PAYMENT NOTICE
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 18px;
                                                border:
                                                    1px solid #D9E9E0;
                                                border-radius: 10px;
                                                background-color: #F4FAF7;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    align="left"
                                                    style="
                                                        padding:
                                                            15px 16px;
                                                        color: #52645A;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >

                                                    <strong
                                                        style="
                                                            display: block;
                                                            margin-bottom: 4px;
                                                            color: #0B3D2E;
                                                            font-size: 12px;
                                                        "
                                                    >
                                                        Payment secured
                                                    </strong>

                                                    The buyer's payment is secured.
                                                    You can now prepare the item
                                                    for fulfilment. Do not request
                                                    another payment from the buyer.

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            ACTION BUTTON
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
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
                                                        href="{{ route('seller.transactions.show', $transaction) }}"
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
                                                        View transaction
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>

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
                                Midpoint secure transaction platform
                            </p>

                            <p
                                style="
                                    margin:
                                        5px 0 0;
                                    color: #98A29D;
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