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
    | Seller
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
    | Buyer
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
    | Order Amounts
    |--------------------------------------------------------------------------
    */

    $quantity = max(
        1,
        (int) $transaction->quantity
    );

    $unitPrice =
        (float) $transaction->unit_price;

    $subtotal =
        (float) $transaction->subtotal;

    $deliveryFee =
        (float) $transaction->delivery_fee;

    $totalPaid = (float) (
        $transaction->paid_amount
        ?: $transaction->total_amount
    );

    /*
    |--------------------------------------------------------------------------
    | Delivery Address
    |--------------------------------------------------------------------------
    */

    $deliveryAddress = trim(
        (string) ($transaction->delivery_note ?? '')
    );

    $deliveryAddress =
        $deliveryAddress !== ''
            ? $deliveryAddress
            : 'Not provided';

    /*
    |--------------------------------------------------------------------------
    | Seller Order URL
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
        New marketplace order
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

            .detail-label {
                width:
                    36% !important;
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
        {{ $buyerName }} ordered
        {{ $quantity }} ×
        {{ $transaction->title }}.
        Payment is secured.
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

                                {{-- Top accent --}}

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

                                                {{-- Fallback logo --}}

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

                                        {{-- Order status --}}

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
                                                            7px 12px;
                                                        border:
                                                            1px solid #CDEEDD;
                                                        border-radius: 999px;
                                                        background-color: #ECFDF3;
                                                        color: #067647;
                                                        font-size: 10px;
                                                        font-weight: 700;
                                                        letter-spacing: 0.4px;
                                                        line-height: 14px;
                                                    "
                                                >
                                                    NEW ORDER
                                                    &bull;
                                                    PAYMENT SECURED
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- Title --}}

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
                                            You received a new order
                                        </h1>


                                        {{-- Message --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    17px 0 0;
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

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $buyerName }}
                                            </strong>

                                            purchased

                                            {{ $quantity }} ×

                                            <strong
                                                style="
                                                    color: #26352E;
                                                "
                                            >
                                                {{ $transaction->title }}
                                            </strong>

                                            from your marketplace listing.

                                            Midpoint has verified and
                                            secured the payment.
                                        </p>


                                        {{-- =================================
                                            TOTAL PAYMENT
                                        ================================== --}}

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
                                                            21px 18px;
                                                        border:
                                                            1px solid #D7EADF;
                                                        border-radius: 11px;
                                                        background-color: #F2FCF6;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                            color: #68776F;
                                                            font-size: 11px;
                                                            line-height: 16px;
                                                        "
                                                    >
                                                        Total payment secured
                                                    </div>

                                                    <div
                                                        style="
                                                            margin-top: 5px;
                                                            color: #0B3D2E;
                                                            font-size: 29px;
                                                            font-weight: 800;
                                                            line-height: 36px;
                                                        "
                                                    >
                                                        &#8358;{{ number_format($totalPaid, 2) }}
                                                    </div>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================
                                            ORDER DETAILS HEADING
                                        ================================== --}}

                                        <h2
                                            style="
                                                margin:
                                                    25px 0 10px;
                                                color: #26352E;
                                                font-size: 15px;
                                                font-weight: 700;
                                                line-height: 21px;
                                                text-align: left;
                                            "
                                        >
                                            Order details
                                        </h2>


                                        {{-- =================================
                                            ORDER DETAILS TABLE
                                        ================================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 11px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Order reference
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding:
                                                            12px 16px;
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


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
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
                                                    style="
                                                        padding:
                                                            12px 16px;
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


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
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
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    {{ number_format($quantity) }}
                                                </td>

                                            </tr>


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
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
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    &#8358;{{ number_format($unitPrice, 2) }}
                                                </td>

                                            </tr>


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Product subtotal
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    &#8358;{{ number_format($subtotal, 2) }}
                                                </td>

                                            </tr>


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Delivery fee
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    &#8358;{{ number_format($deliveryFee, 2) }}
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================
                                            BUYER DETAILS HEADING
                                        ================================== --}}

                                        <h2
                                            style="
                                                margin:
                                                    25px 0 10px;
                                                color: #26352E;
                                                font-size: 15px;
                                                font-weight: 700;
                                                line-height: 21px;
                                                text-align: left;
                                            "
                                        >
                                            Buyer and delivery details
                                        </h2>


                                        {{-- =================================
                                            BUYER DETAILS TABLE
                                        ================================== --}}

                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                border:
                                                    1px solid #E0E7E3;
                                                border-radius: 11px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Buyer
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
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


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Buyer email
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        border-bottom:
                                                            1px solid #E7ECE9;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                        word-break: break-all;
                                                    "
                                                >
                                                    {{ $transaction->buyer_email }}
                                                </td>

                                            </tr>


                                            @if($transaction->buyer_phone)

                                                <tr>

                                                    <td
                                                        class="detail-label"
                                                        width="38%"
                                                        style="
                                                            padding:
                                                                12px 16px;
                                                            border-bottom:
                                                                1px solid #E7ECE9;
                                                            color: #748078;
                                                            font-size: 12px;
                                                            line-height: 18px;
                                                        "
                                                    >
                                                        Delivery phone
                                                    </td>

                                                    <td
                                                        align="right"
                                                        style="
                                                            padding:
                                                                12px 16px;
                                                            border-bottom:
                                                                1px solid #E7ECE9;
                                                            color: #26352E;
                                                            font-size: 12px;
                                                            font-weight: 700;
                                                            line-height: 18px;
                                                            word-break: break-word;
                                                        "
                                                    >
                                                        {{ $transaction->buyer_phone }}
                                                    </td>

                                                </tr>

                                            @endif


                                            <tr>

                                                <td
                                                    class="detail-label"
                                                    width="38%"
                                                    valign="top"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Delivery address
                                                </td>

                                                <td
                                                    align="right"
                                                    valign="top"
                                                    style="
                                                        padding:
                                                            12px 16px;
                                                        color: #26352E;
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                        line-height: 18px;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $deliveryAddress }}
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================
                                            MARKETPLACE INSTRUCTION
                                        ================================== --}}

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
                                                            1px solid #F3DDA8;
                                                        border-radius: 9px;
                                                        background-color: #FFF9EA;
                                                        color: #795417;
                                                        font-size: 12px;
                                                        line-height: 19px;
                                                    "
                                                >

                                                    <strong
                                                        style="
                                                            display: block;
                                                            margin-bottom: 3px;
                                                            color: #62420E;
                                                        "
                                                    >
                                                        Marketplace order
                                                    </strong>

                                                    Prepare the exact item and
                                                    quantity shown above,
                                                    then update the transaction
                                                    as you fulfil and dispatch
                                                    the order.

                                                    Do not request another
                                                    payment from the buyer.

                                                    The secured funds will be
                                                    released according to the
                                                    transaction process.

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================
                                            VIEW ORDER BUTTON
                                        ================================== --}}

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
                                                        View order details
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================
                                            MANUAL URL
                                        ================================== --}}

                                        <p
                                            style="
                                                margin:
                                                    23px 0 0;
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
                                Secure marketplace transactions
                                for buyers and sellers.
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