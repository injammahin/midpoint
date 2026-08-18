<!DOCTYPE html>
<html lang="en">

<head>
    <meta
        http-equiv="Content-Type"
        content="text/html; charset=UTF-8"
    >

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
        Secure Midpoint Transaction
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

            .section-padding {
                padding:
                    0 20px !important;
            }

            .product-card {
                padding:
                    17px !important;
            }

            .total-amount {
                font-size:
                    25px !important;

                line-height:
                    32px !important;
            }

            .footer-content {
                padding-right:
                    16px !important;

                padding-left:
                    16px !important;
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
        {{ $sellerName }} created a secure Midpoint transaction
        for {{ $transaction->title }}.
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
                    role="presentation"
                    width="600"
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
                                                color: #89958F;
                                                font-size: 11px;
                                                line-height: 17px;
                                            "
                                        >
                                            Secure transaction invitation
                                        </div>

                                    </td>

                                </tr>


                                {{-- =================================
                                    INTRODUCTION
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="email-content"
                                        style="
                                            padding:
                                                4px 48px 29px;
                                        "
                                    >

                                        {{-- PAYMENT REQUEST BADGE --}}

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
                                                    SECURE PAYMENT REQUEST
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- TITLE --}}

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
                                            You've received a secure
                                            transaction invitation
                                        </h1>


                                        {{-- INTRO COPY --}}

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

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $sellerName }}
                                            </strong>

                                            created a protected Midpoint
                                            transaction for you.

                                        </p>

                                    </td>

                                </tr>


                                {{-- =================================
                                    TRANSACTION REFERENCE
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                0 48px;
                                        "
                                    >

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 11px;
                                                background-color: #F8FAF9;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    style="
                                                        padding:
                                                            14px 16px;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            color: #7C8982;
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
                                                            font-size: 14px;
                                                            font-weight: 700;
                                                            line-height: 20px;
                                                            word-break: break-word;
                                                        "
                                                    >
                                                        {{ $transaction->reference }}
                                                    </div>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>


                                {{-- =================================
                                    ORDER SUMMARY HEADING
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                25px 48px 10px;
                                        "
                                    >

                                        <div
                                            style="
                                                color: #26352E;
                                                font-size: 15px;
                                                font-weight: 700;
                                                line-height: 21px;
                                            "
                                        >
                                            Transaction summary
                                        </div>

                                    </td>

                                </tr>


                                {{-- =================================
                                    PRODUCT / PAYMENT SUMMARY
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                0 48px;
                                        "
                                    >

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 12px;
                                                background-color: #FFFFFF;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    class="product-card"
                                                    style="
                                                        padding: 20px;
                                                    "
                                                >

                                                    {{-- ITEM --}}

                                                    <div
                                                        style="
                                                            color: #7A8780;
                                                            font-size: 11px;
                                                            line-height: 16px;
                                                        "
                                                    >
                                                        Item
                                                    </div>

                                                    <div
                                                        style="
                                                            margin-top: 4px;
                                                            margin-bottom: 17px;
                                                            color: #17251F;
                                                            font-size: 17px;
                                                            font-weight: 700;
                                                            line-height: 23px;
                                                            word-break: break-word;
                                                        "
                                                    >
                                                        {{ $transaction->title }}
                                                    </div>


                                                    {{-- PRICE TABLE --}}

                                                    <table
                                                        role="presentation"
                                                        width="100%"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                    >

                                                        {{-- UNIT PRICE --}}

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #69766F;
                                                                    font-size: 12px;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                Unit price
                                                            </td>


                                                            <td
                                                                align="right"
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #26352E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                ₦{{ number_format((float) $transaction->unit_price, 2) }}
                                                            </td>

                                                        </tr>


                                                        {{-- QUANTITY --}}

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #69766F;
                                                                    font-size: 12px;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                Quantity
                                                            </td>


                                                            <td
                                                                align="right"
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #26352E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                {{ number_format($transaction->quantity) }}
                                                            </td>

                                                        </tr>


                                                        {{-- SUBTOTAL --}}

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #69766F;
                                                                    font-size: 12px;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                Subtotal
                                                            </td>


                                                            <td
                                                                align="right"
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #26352E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                ₦{{ number_format((float) $transaction->subtotal, 2) }}
                                                            </td>

                                                        </tr>


                                                        {{-- DELIVERY --}}

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #69766F;
                                                                    font-size: 12px;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                Delivery
                                                            </td>


                                                            <td
                                                                align="right"
                                                                style="
                                                                    padding:
                                                                        8px 0;
                                                                    color: #26352E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                ₦{{ number_format((float) $transaction->delivery_fee, 2) }}
                                                            </td>

                                                        </tr>


                                                        {{-- SEPARATOR --}}

                                                        <tr>

                                                            <td
                                                                colspan="2"
                                                                style="
                                                                    padding-top: 10px;
                                                                "
                                                            >
                                                                <div
                                                                    style="
                                                                        height: 1px;
                                                                        background-color: #E5EAE7;
                                                                        font-size: 0;
                                                                        line-height: 0;
                                                                    "
                                                                >
                                                                    &nbsp;
                                                                </div>
                                                            </td>

                                                        </tr>


                                                        {{-- TOTAL --}}

                                                        <tr>

                                                            <td
                                                                style="
                                                                    padding:
                                                                        14px 0 2px;
                                                                    color: #26352E;
                                                                    font-size: 13px;
                                                                    font-weight: 700;
                                                                    line-height: 20px;
                                                                    vertical-align: middle;
                                                                "
                                                            >
                                                                Total to pay
                                                            </td>


                                                            <td
                                                                align="right"
                                                                class="total-amount"
                                                                style="
                                                                    padding:
                                                                        14px 0 2px;
                                                                    color: #0B3D2E;
                                                                    font-size: 28px;
                                                                    font-weight: 800;
                                                                    line-height: 34px;
                                                                    vertical-align: middle;
                                                                "
                                                            >
                                                                ₦{{ number_format((float) $transaction->total_amount, 2) }}
                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>


                                {{-- =================================
                                    DELIVERY ARRANGEMENT
                                ================================== --}}

                                @if ($transaction->delivery_note)

                                    <tr>

                                        <td
                                            class="section-padding"
                                            style="
                                                padding:
                                                    18px 48px 0;
                                            "
                                        >

                                            <table
                                                role="presentation"
                                                width="100%"
                                                cellspacing="0"
                                                cellpadding="0"
                                                border="0"
                                                style="
                                                    width: 100%;
                                                    border:
                                                        1px solid #D9E9E0;
                                                    border-radius: 10px;
                                                    background-color: #F4FAF7;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        style="
                                                            padding:
                                                                14px 16px;
                                                        "
                                                    >

                                                        <div
                                                            style="
                                                                color: #0B3D2E;
                                                                font-size: 12px;
                                                                font-weight: 700;
                                                                line-height: 18px;
                                                            "
                                                        >
                                                            Delivery arrangement
                                                        </div>


                                                        <div
                                                            style="
                                                                margin-top: 4px;
                                                                color: #596A61;
                                                                font-size: 12px;
                                                                line-height: 19px;
                                                                word-break: break-word;
                                                            "
                                                        >
                                                            {{ $transaction->delivery_note }}
                                                        </div>

                                                    </td>

                                                </tr>

                                            </table>

                                        </td>

                                    </tr>

                                @endif


                                {{-- =================================
                                    PRIMARY BUTTON
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                25px 48px 0;
                                        "
                                    >

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
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
                                                        href="{{ $secureUrl }}"
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
                                                        Open secure transaction
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>


                                        <p
                                            style="
                                                margin:
                                                    10px 0 0;
                                                color: #88938D;
                                                font-size: 10px;
                                                line-height: 16px;
                                                text-align: center;
                                            "
                                        >
                                            Review the transaction details
                                            before continuing to payment.
                                        </p>

                                    </td>

                                </tr>


                                {{-- =================================
                                    BUYER IDENTITY
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                21px 48px 0;
                                        "
                                    >

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #CEEADC;
                                                border-radius: 10px;
                                                background-color: #ECFDF3;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    style="
                                                        padding:
                                                            15px 16px;
                                                        color: #506D60;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >

                                                    <strong
                                                        style="
                                                            display: block;
                                                            margin-bottom: 4px;
                                                            color: #067647;
                                                        "
                                                    >
                                                        Buyer identity protection
                                                    </strong>

                                                    This transaction is reserved for

                                                    <strong
                                                        style="
                                                            color: #254A3A;
                                                            word-break: break-all;
                                                        "
                                                    >
                                                        {{ $transaction->buyer_email }}
                                                    </strong>.

                                                    You will need to log in or
                                                    create a Midpoint account
                                                    using this email address
                                                    before continuing to payment.

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>


                                {{-- =================================
                                    PROTECTION SECTION
                                ================================== --}}

                                <tr>

                                    <td
                                        class="section-padding"
                                        style="
                                            padding:
                                                25px 48px 0;
                                        "
                                    >

                                        <div
                                            style="
                                                margin-bottom: 10px;
                                                color: #26352E;
                                                font-size: 14px;
                                                font-weight: 700;
                                                line-height: 20px;
                                            "
                                        >
                                            How Midpoint protects this transaction
                                        </div>


                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 10px;
                                                background-color: #F9FBFA;
                                            "
                                        >

                                            {{-- PROTECTION 1 --}}

                                            <tr>

                                                <td
                                                    width="38"
                                                    valign="top"
                                                    style="
                                                        padding:
                                                            14px 0 11px 15px;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        width="22"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                    >

                                                        <tr>

                                                            <td
                                                                align="center"
                                                                width="22"
                                                                height="22"
                                                                style="
                                                                    width: 22px;
                                                                    height: 22px;
                                                                    border-radius: 50%;
                                                                    background-color: #E6F7ED;
                                                                    color: #067647;
                                                                    font-size: 11px;
                                                                    font-weight: 700;
                                                                    line-height: 22px;
                                                                "
                                                            >
                                                                ✓
                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>


                                                <td
                                                    style="
                                                        padding:
                                                            14px 15px 11px 7px;
                                                        color: #617068;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >
                                                    Secure Midpoint account
                                                    login required
                                                </td>

                                            </tr>


                                            {{-- DIVIDER --}}

                                            <tr>

                                                <td
                                                    colspan="2"
                                                    style="
                                                        padding:
                                                            0 15px;
                                                    "
                                                >
                                                    <div
                                                        style="
                                                            height: 1px;
                                                            background-color: #E8EDEA;
                                                            font-size: 0;
                                                            line-height: 0;
                                                        "
                                                    >
                                                        &nbsp;
                                                    </div>
                                                </td>

                                            </tr>


                                            {{-- PROTECTION 2 --}}

                                            <tr>

                                                <td
                                                    width="38"
                                                    valign="top"
                                                    style="
                                                        padding:
                                                            11px 0 11px 15px;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        width="22"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                    >

                                                        <tr>

                                                            <td
                                                                align="center"
                                                                width="22"
                                                                height="22"
                                                                style="
                                                                    width: 22px;
                                                                    height: 22px;
                                                                    border-radius: 50%;
                                                                    background-color: #E6F7ED;
                                                                    color: #067647;
                                                                    font-size: 11px;
                                                                    font-weight: 700;
                                                                    line-height: 22px;
                                                                "
                                                            >
                                                                ✓
                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>


                                                <td
                                                    style="
                                                        padding:
                                                            11px 15px 11px 7px;
                                                        color: #617068;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >
                                                    Transaction amount is fixed
                                                    by the seller
                                                </td>

                                            </tr>


                                            {{-- DIVIDER --}}

                                            <tr>

                                                <td
                                                    colspan="2"
                                                    style="
                                                        padding:
                                                            0 15px;
                                                    "
                                                >
                                                    <div
                                                        style="
                                                            height: 1px;
                                                            background-color: #E8EDEA;
                                                            font-size: 0;
                                                            line-height: 0;
                                                        "
                                                    >
                                                        &nbsp;
                                                    </div>
                                                </td>

                                            </tr>


                                            {{-- PROTECTION 3 --}}

                                            <tr>

                                                <td
                                                    width="38"
                                                    valign="top"
                                                    style="
                                                        padding:
                                                            11px 0 14px 15px;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        width="22"
                                                        cellspacing="0"
                                                        cellpadding="0"
                                                        border="0"
                                                    >

                                                        <tr>

                                                            <td
                                                                align="center"
                                                                width="22"
                                                                height="22"
                                                                style="
                                                                    width: 22px;
                                                                    height: 22px;
                                                                    border-radius: 50%;
                                                                    background-color: #E6F7ED;
                                                                    color: #067647;
                                                                    font-size: 11px;
                                                                    font-weight: 700;
                                                                    line-height: 22px;
                                                                "
                                                            >
                                                                ✓
                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>


                                                <td
                                                    style="
                                                        padding:
                                                            11px 15px 14px 7px;
                                                        color: #617068;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >
                                                    {{ $transaction->inspection_hours }}
                                                    hour buyer inspection period
                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>


                                {{-- =================================
                                    EXPIRATION
                                ================================== --}}

                                @if ($transaction->link_expires_at)

                                    <tr>

                                        <td
                                            class="section-padding"
                                            style="
                                                padding:
                                                    18px 48px 0;
                                            "
                                        >

                                            <table
                                                role="presentation"
                                                width="100%"
                                                cellspacing="0"
                                                cellpadding="0"
                                                border="0"
                                                style="
                                                    width: 100%;
                                                    border:
                                                        1px solid #F1DCA8;
                                                    border-radius: 9px;
                                                    background-color: #FFF9EA;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        style="
                                                            padding:
                                                                13px 15px;
                                                            color: #795417;
                                                            font-size: 11px;
                                                            line-height: 18px;
                                                        "
                                                    >

                                                        <strong
                                                            style="
                                                                display: block;
                                                                margin-bottom: 3px;
                                                                color: #62420E;
                                                                font-size: 12px;
                                                            "
                                                        >
                                                            Secure link expiration
                                                        </strong>

                                                        This secure link expires on

                                                        <strong>
                                                            {{ $transaction->link_expires_at->format('d M Y, h:i A') }}
                                                        </strong>.

                                                    </td>

                                                </tr>

                                            </table>

                                        </td>

                                    </tr>

                                @endif


                                {{-- =================================
                                    MANUAL URL
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="section-padding"
                                        style="
                                            padding:
                                                24px 48px 0;
                                        "
                                    >

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
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
                                                href="{{ $secureUrl }}"
                                                style="
                                                    color: #0E8A5D;
                                                    text-decoration: underline;
                                                "
                                            >
                                                {{ $secureUrl }}
                                            </a>

                                        </p>

                                    </td>

                                </tr>


                                {{-- =================================
                                    SAFETY MESSAGE
                                ================================== --}}

                                <tr>

                                    <td
                                        align="center"
                                        class="section-padding"
                                        style="
                                            padding:
                                                22px 48px 36px;
                                        "
                                    >

                                        <p
                                            style="
                                                margin: 0;
                                                color: #7E8983;
                                                font-size: 11px;
                                                line-height: 18px;
                                                text-align: center;
                                            "
                                        >
                                            If you were not expecting this
                                            transaction, do not make payment
                                            and contact Midpoint support.
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
                                    color: #98A29D;
                                    font-size: 11px;
                                    line-height: 18px;
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