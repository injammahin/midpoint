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
        {{ $emailTitle }}
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

            .detail-label {
                width:
                    40% !important;
            }

            .detail-cell {
                padding:
                    12px 13px !important;
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
        color: #17211D;
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
        {{ $emailTitle }}. {{ $emailMessage }}
    </div>


    {{-- =========================================================
        EMAIL BACKGROUND
    ========================================================== --}}

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
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
                    cellpadding="0"
                    cellspacing="0"
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
                                cellpadding="0"
                                cellspacing="0"
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
                                            cellpadding="0"
                                            cellspacing="0"
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
                                                color: #87938D;
                                                font-size: 11px;
                                                line-height: 17px;
                                            "
                                        >
                                            Verified Seller Program
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
                                            STATUS ICON
                                        ============================== --}}

                                        <table
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            role="presentation"
                                        >

                                            <tr>

                                                <td
                                                    align="center"
                                                    width="52"
                                                    height="52"
                                                    style="
                                                        width: 52px;
                                                        height: 52px;
                                                        border-radius: 50%;
                                                        background-color: {{ $accentBackground }};
                                                        color: {{ $accentColor }};
                                                        font-size: 22px;
                                                        font-weight: 800;
                                                        line-height: 52px;
                                                        text-align: center;
                                                    "
                                                >

                                                    @if($type === 'revision_required')
                                                        !
                                                    @else
                                                        ✓
                                                    @endif

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
                                                    19px 0 0;
                                                color: #17251F;
                                                font-size: 28px;
                                                font-weight: 700;
                                                line-height: 36px;
                                                text-align: center;
                                            "
                                        >
                                            {{ $emailTitle }}
                                        </h1>


                                        {{-- =============================
                                            GREETING + MESSAGE
                                        ============================== --}}

                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    16px 0 0;
                                                color: #596760;
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
                                                {{ $user->name }},
                                            </strong>
                                        </p>


                                        <p
                                            class="email-copy"
                                            style="
                                                margin:
                                                    8px 0 0;
                                                color: #596760;
                                                font-size: 15px;
                                                line-height: 24px;
                                                text-align: center;
                                            "
                                        >
                                            {{ $emailMessage }}
                                        </p>


                                        {{-- =============================
                                            APPLICATION DETAILS HEADING
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            role="presentation"
                                            style="
                                                width: 100%;
                                                margin-top: 27px;
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
                                                    Application details
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            APPLICATION DETAILS CARD
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
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

                                            {{-- Application reference --}}

                                            <tr>

                                                <td
                                                    width="40%"
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
                                                    Application reference
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
                                                    {{ $application->reference }}
                                                </td>

                                            </tr>


                                            {{-- Business --}}

                                            <tr>

                                                <td
                                                    width="40%"
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
                                                    Business
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
                                                    {{ $application->business_name }}
                                                </td>

                                            </tr>


                                            {{-- Package --}}

                                            <tr>

                                                <td
                                                    width="40%"
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
                                                    Package
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
                                                    {{ $application->package_name }}
                                                </td>

                                            </tr>


                                            {{-- Package Price --}}

                                            <tr>

                                                <td
                                                    width="40%"
                                                    class="detail-label detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        color: #748078;
                                                        font-size: 12px;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    Package price
                                                </td>


                                                <td
                                                    align="right"
                                                    class="detail-cell"
                                                    style="
                                                        padding:
                                                            13px 16px;
                                                        color: #0B3D2E;
                                                        font-size: 12px;
                                                        font-weight: 800;
                                                        line-height: 18px;
                                                    "
                                                >
                                                    ₦{{
                                                        number_format(
                                                            (float) $application->package_price,
                                                            0
                                                        )
                                                    }}
                                                    /{{ $application->billing_period }}
                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =================================================
                                            REVISION NOTE
                                        ================================================== --}}

                                        @if(
                                            $type === 'revision_required'
                                            &&
                                            $application->revision_note
                                        )

                                            <table
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                                role="presentation"
                                                style="
                                                    width: 100%;
                                                    margin-top: 20px;
                                                    border:
                                                        1px solid #FEDF89;
                                                    border-radius: 10px;
                                                    background-color: #FFF9EA;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        align="left"
                                                        style="
                                                            padding:
                                                                15px 16px;
                                                        "
                                                    >

                                                        <strong
                                                            style="
                                                                display: block;
                                                                margin-bottom: 5px;
                                                                color: #9A5006;
                                                                font-size: 12px;
                                                                line-height: 18px;
                                                            "
                                                        >
                                                            What you need to correct
                                                        </strong>


                                                        <div
                                                            style="
                                                                color: #795417;
                                                                font-size: 12px;
                                                                line-height: 19px;
                                                                word-break: break-word;
                                                            "
                                                        >
                                                            {{ $application->revision_note }}
                                                        </div>

                                                    </td>

                                                </tr>

                                            </table>

                                        @endif


                                        {{-- =================================================
                                            APPROVAL INVOICE
                                        ================================================== --}}

                                        @if(
                                            $type === 'approved'
                                            &&
                                            $application->invoice
                                        )

                                            <table
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                                role="presentation"
                                                style="
                                                    width: 100%;
                                                    margin-top: 20px;
                                                    border:
                                                        1px solid #ABEFC6;
                                                    border-radius: 11px;
                                                    background-color: #ECFDF3;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        align="center"
                                                        style="
                                                            padding:
                                                                20px 18px;
                                                        "
                                                    >

                                                        <div
                                                            style="
                                                                color: #067647;
                                                                font-size: 11px;
                                                                font-weight: 700;
                                                                letter-spacing: 0.4px;
                                                                line-height: 16px;
                                                            "
                                                        >
                                                            SELLER PACKAGE INVOICE
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
                                                            ₦{{
                                                                number_format(
                                                                    (float) $application->invoice->amount,
                                                                    0
                                                                )
                                                            }}
                                                        </div>


                                                        <table
                                                            width="100%"
                                                            cellpadding="0"
                                                            cellspacing="0"
                                                            border="0"
                                                            role="presentation"
                                                            style="
                                                                width: 100%;
                                                                margin-top: 13px;
                                                            "
                                                        >

                                                            <tr>

                                                                <td
                                                                    style="
                                                                        padding:
                                                                            8px 0;
                                                                        border-top:
                                                                            1px solid #D0ECDB;
                                                                        color: #597066;
                                                                        font-size: 11px;
                                                                        line-height: 17px;
                                                                    "
                                                                >
                                                                    Invoice
                                                                </td>


                                                                <td
                                                                    align="right"
                                                                    style="
                                                                        padding:
                                                                            8px 0;
                                                                        border-top:
                                                                            1px solid #D0ECDB;
                                                                        color: #28473A;
                                                                        font-size: 11px;
                                                                        font-weight: 700;
                                                                        line-height: 17px;
                                                                        word-break: break-word;
                                                                    "
                                                                >
                                                                    {{ $application->invoice->invoice_number }}
                                                                </td>

                                                            </tr>


                                                            <tr>

                                                                <td
                                                                    style="
                                                                        padding:
                                                                            6px 0 0;
                                                                        color: #597066;
                                                                        font-size: 11px;
                                                                        line-height: 17px;
                                                                    "
                                                                >
                                                                    Status
                                                                </td>


                                                                <td
                                                                    align="right"
                                                                    style="
                                                                        padding:
                                                                            6px 0 0;
                                                                        color: #B54708;
                                                                        font-size: 11px;
                                                                        font-weight: 700;
                                                                        line-height: 17px;
                                                                    "
                                                                >
                                                                    Unpaid
                                                                </td>

                                                            </tr>

                                                        </table>

                                                    </td>

                                                </tr>

                                            </table>

                                        @endif


                                        {{-- =================================================
                                            ACTIVATION DETAILS
                                        ================================================== --}}

                                        @if($type === 'payment_successful')

                                            <table
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                                role="presentation"
                                                style="
                                                    width: 100%;
                                                    margin-top: 20px;
                                                    border:
                                                        1px solid #ABEFC6;
                                                    border-radius: 11px;
                                                    background-color: #ECFDF3;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        align="left"
                                                        style="
                                                            padding:
                                                                16px;
                                                        "
                                                    >

                                                        <strong
                                                            style="
                                                                display: block;
                                                                color: #067647;
                                                                font-size: 13px;
                                                                line-height: 19px;
                                                            "
                                                        >
                                                            {{ $application->package_name }}
                                                            package activated
                                                        </strong>


                                                        <div
                                                            style="
                                                                margin-top: 6px;
                                                                color: #52645A;
                                                                font-size: 12px;
                                                                line-height: 19px;
                                                            "
                                                        >
                                                            Product limit:

                                                            <strong
                                                                style="
                                                                    color: #28473A;
                                                                "
                                                            >
                                                                {{ $application->product_limit }}
                                                                products
                                                            </strong>
                                                        </div>

                                                    </td>

                                                </tr>

                                            </table>

                                        @endif


                                        {{-- =============================
                                            ACTION BUTTON
                                        ============================== --}}

                                        <table
                                            width="100%"
                                            cellpadding="0"
                                            cellspacing="0"
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
                                                        href="{{ $buttonUrl }}"
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
                                                        {{ $buttonText }}
                                                    </a>

                                                </td>

                                            </tr>

                                        </table>


                                        {{-- =============================
                                            BUTTON HELPER
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
                                            Open Midpoint to review your
                                            seller application and next steps.
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
                                Verified Seller Program
                            </p>


                            <p
                                style="
                                    margin:
                                        8px 0 0;
                                    color: #A3ACA7;
                                    font-size: 10px;
                                    line-height: 17px;
                                    text-align: center;
                                    word-break: break-all;
                                "
                            >
                                This email was sent to
                                {{ $user->email }}
                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>