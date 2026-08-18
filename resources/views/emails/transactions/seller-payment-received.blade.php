@php
    /*
    |--------------------------------------------------------------------------
    | Uploaded Midpoint Logo
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
            | Do not use $message->embed(). Embedded images may appear as
            | separate attachments in some email applications.
            |
            */

            $logoUrl = asset(
                $relativeLogoPath
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Seller Name
    |--------------------------------------------------------------------------
    */

    $sellerName = trim(
        (string) ($transaction->seller?->name ?? '')
    );

    $sellerName =
        $sellerName !== ''
            ? $sellerName
            : 'there';

    /*
    |--------------------------------------------------------------------------
    | Buyer Name
    |--------------------------------------------------------------------------
    */

    $buyerName = trim(
        (string) (
            $transaction->buyer?->name
            ?: $transaction->buyer_email
            ?: 'Buyer'
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Secured Amount
    |--------------------------------------------------------------------------
    */

    $securedAmount = (float) (
        $transaction->paid_amount
        ?: $transaction->total_amount
    );

    /*
    |--------------------------------------------------------------------------
    | Seller Transaction URL
    |--------------------------------------------------------------------------
    */

    $transactionUrl = route(
        'seller.transactions.show',
        [
            'secureTransaction' =>
                $transaction->public_token,
        ]
    );
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
        Buyer payment secured
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
                    28px 22px 18px !important;
            }

            .email-content {
                padding:
                    3px 20px 28px !important;
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

            .amount-value {
                font-size:
                    27px !important;

                line-height:
                    34px !important;
            }

            .detail-label {
                width:
                    35% !important;
            }

            .detail-cell {
                padding:
                    12px 13px !important;
            }

            .footer-content {
                padding-right:
                    12px !important;

                padding-left:
                    12px !important;
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
        The buyer's payment for
        {{ $transaction->title }}
        has been verified and secured.
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
                                    LOGO
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
                                            aria-label="Visit Midpoint"
                                            style="
                                                display: inline-block;
                                            "
                                        >
                                            @if($logoUrl)

                                                <img
                                                    src="{{ $logoUrl }}"
                                                    alt="Midpoint"
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
                                                            >point</span>
                                                        </td>
                                                    </tr>
                                                </table>

                                            @endif
                                        </a>
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
                                            SUCCESS STATUS
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                        >
                                            <tr>
                                                <td
                                                    align="center"
                                                    style="
                                                        padding:
                                                            7px 13px;
                                                        border:
                                                            1px solid #CDEEDD;
                                                        border-radius: 999px;
                                                        background-color: #ECFDF3;
                                                        color: #067647;
                                                        font-size: 10px;
                                                        font-weight: 700;
                                                        letter-spacing: 0.6px;
                                                        line-height: 14px;
                                                        text-transform: uppercase;
                                                    "
                                                >
                                                    PAYMENT VERIFIED &bull; FUNDS SECURED
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
                                                font-size: 29px;
                                                font-weight: 700;
                                                line-height: 37px;
                                                text-align: center;
                                            "
                                        >
                                            Your buyer has completed payment
                                        </h1>


                                        {{-- =============================
                                            MESSAGE
                                        ============================== --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    16px 0 0;
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
                                                {{ $sellerName }},
                                            </strong>

                                            Midpoint has successfully verified
                                            and secured the buyer's payment for

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $transaction->title }}.
                                            </strong>
                                        </p>


                                        {{-- =============================
                                            SECURED AMOUNT CARD
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
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

                                                    <table
                                                        role="presentation"
                                                        cellpadding="0"
                                                        cellspacing="0"
                                                        border="0"
                                                        align="center"
                                                    >
                                                        <tr>
                                                            <td
                                                                align="center"
                                                                style="
                                                                    color: #68776F;
                                                                    font-size: 11px;
                                                                    font-weight: 600;
                                                                    line-height: 16px;
                                                                    letter-spacing: 0.2px;
                                                                "
                                                            >
                                                                BUYER PAYMENT SECURED
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td
                                                                align="center"
                                                                class="amount-value"
                                                                style="
                                                                    padding-top: 5px;
                                                                    color: #0B3D2E;
                                                                    font-size: 30px;
                                                                    font-weight: 800;
                                                                    line-height: 38px;
                                                                "
                                                            >
                                                                &#8358;{{ number_format($securedAmount, 2) }}
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td
                                                                align="center"
                                                                style="
                                                                    padding-top: 4px;
                                                                    color: #779087;
                                                                    font-size: 10px;
                                                                    line-height: 16px;
                                                                "
                                                            >
                                                                Verified and held securely by Midpoint
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>


                                        {{-- =============================
                                            TRANSACTION DETAILS TITLE
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
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
                                            cellpadding="0"
                                            cellspacing="0"
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

                                            {{-- Transaction Reference --}}

                                            <tr>
                                                <td
                                                    class="detail-label detail-cell"
                                                    width="36%"
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
                                                    class="detail-label detail-cell"
                                                    width="36%"
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
                                                    class="detail-label detail-cell"
                                                    width="36%"
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
                                                    {{ $buyerName }}
                                                </td>
                                            </tr>

                                        </table>


                                        {{-- =============================
                                            PAYMENT PROTECTION NOTICE
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
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
                                                            14px 16px;
                                                        border:
                                                            1px solid #D9E9E0;
                                                        border-radius: 9px;
                                                        background-color: #F4FAF7;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        width="100%"
                                                        cellpadding="0"
                                                        cellspacing="0"
                                                        border="0"
                                                    >
                                                        <tr>
                                                            <td
                                                                style="
                                                                    color: #0B3D2E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                Payment protected by Midpoint
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td
                                                                style="
                                                                    padding-top: 4px;
                                                                    color: #52645A;
                                                                    font-size: 12px;
                                                                    line-height: 19px;
                                                                "
                                                            >
                                                                The buyer's payment has already
                                                                been received and secured.
                                                                You do not need to request
                                                                another payment from the buyer.
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>


                                        {{-- =============================
                                            NEXT STEP
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 12px;
                                            "
                                        >
                                            <tr>
                                                <td
                                                    align="left"
                                                    style="
                                                        padding:
                                                            14px 16px;
                                                        border:
                                                            1px solid #F1DCA8;
                                                        border-radius: 9px;
                                                        background-color: #FFF9EA;
                                                    "
                                                >

                                                    <table
                                                        role="presentation"
                                                        width="100%"
                                                        cellpadding="0"
                                                        cellspacing="0"
                                                        border="0"
                                                    >
                                                        <tr>
                                                            <td
                                                                style="
                                                                    color: #62420E;
                                                                    font-size: 12px;
                                                                    font-weight: 700;
                                                                    line-height: 18px;
                                                                "
                                                            >
                                                                What happens next?
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td
                                                                style="
                                                                    padding-top: 4px;
                                                                    color: #795417;
                                                                    font-size: 12px;
                                                                    line-height: 19px;
                                                                "
                                                            >
                                                                Prepare the item and update
                                                                the transaction while you
                                                                fulfil and dispatch the order.
                                                                Do not request another payment
                                                                from the buyer. The secured
                                                                funds will be released according
                                                                to the transaction process.
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>


                                        {{-- =============================
                                            MANAGE TRANSACTION BUTTON
                                        ============================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
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
                                                        href="{{ $transactionUrl }}"
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
                                                        Manage transaction
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>


                                        {{-- =============================
                                            CTA HELPER
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    11px 0 0;
                                                color: #8A9690;
                                                font-size: 10px;
                                                line-height: 16px;
                                                text-align: center;
                                            "
                                        >
                                            Review the transaction and keep
                                            the order status up to date.
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
                                            MANUAL URL
                                        ============================== --}}

                                        <p
                                            style="
                                                margin:
                                                    19px 0 0;
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
                                                href="{{ $transactionUrl }}"
                                                style="
                                                    color: #0E8A5D;
                                                    text-decoration: underline;
                                                "
                                            >
                                                {{ $transactionUrl }}
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
                                Secure transactions for buyers and sellers.
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