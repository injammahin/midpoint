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


    <title>
        Secure MidPoint Transaction
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#F4F7F5;
        font-family:Arial, Helvetica, sans-serif;
        color:#17251F;
    "
>


<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        width:100%;
        background:#F4F7F5;
    "
>

    <tr>

        <td
            align="center"
            style="
                padding:35px 15px;
            "
        >


            {{-- =====================================================
                EMAIL CONTAINER
            ====================================================== --}}

            <table
                role="presentation"
                width="600"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="
                    width:100%;
                    max-width:600px;
                    background:#FFFFFF;
                    border-radius:18px;
                    overflow:hidden;
                    border:1px solid #E0E8E3;
                "
            >


                {{-- =================================================
                    HEADER
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:28px 32px;
                            background:#0B3D2E;
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

                                <td>

                                    <table
                                        role="presentation"
                                        cellspacing="0"
                                        cellpadding="0"
                                        border="0"
                                    >

                                        <tr>

                                            <td
                                                style="
                                                    width:38px;
                                                    height:38px;
                                                    text-align:center;
                                                    vertical-align:middle;
                                                    border-radius:11px;
                                                    background:#12B76A;
                                                    color:#FFFFFF;
                                                    font-size:17px;
                                                    font-weight:700;
                                                "
                                            >
                                                M
                                            </td>


                                            <td
                                                style="
                                                    padding-left:10px;
                                                    color:#FFFFFF;
                                                    font-size:21px;
                                                    font-weight:700;
                                                "
                                            >

                                                Mid<span
                                                    style="
                                                        color:#B7A7FF;
                                                    "
                                                >Point</span>

                                            </td>

                                        </tr>

                                    </table>

                                </td>


                                <td
                                    align="right"
                                    style="
                                        color:#A9C9BD;
                                        font-size:11px;
                                    "
                                >
                                    Secure transaction
                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>



                {{-- =================================================
                    CONTENT
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:34px 32px 10px;
                        "
                    >

                        <div
                            style="
                                display:inline-block;
                                padding:6px 10px;
                                border-radius:20px;
                                background:#ECFDF3;
                                color:#067647;
                                font-size:11px;
                                font-weight:700;
                            "
                        >
                            PAYMENT REQUEST
                        </div>


                        <h1
                            style="
                                margin:16px 0 8px;
                                font-size:25px;
                                line-height:1.25;
                                color:#101915;
                            "
                        >
                            You've received a secure transaction invitation.
                        </h1>


                        <p
                            style="
                                margin:0;
                                color:#647169;
                                font-size:14px;
                                line-height:1.7;
                            "
                        >

                            <strong
                                style="
                                    color:#17251F;
                                "
                            >
                                {{ $sellerName }}
                            </strong>

                            created a protected MidPoint transaction
                            for you.

                        </p>

                    </td>

                </tr>



                {{-- =================================================
                    REFERENCE
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:20px 32px 0;
                        "
                    >

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                background:#F7F9F8;
                                border-radius:12px;
                            "
                        >

                            <tr>

                                <td
                                    style="
                                        padding:16px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom:5px;
                                            color:#7B8781;
                                            font-size:11px;
                                        "
                                    >
                                        Transaction reference
                                    </div>


                                    <div
                                        style="
                                            color:#17251F;
                                            font-size:15px;
                                            font-weight:700;
                                        "
                                    >
                                        {{ $transaction->reference }}
                                    </div>

                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>



                {{-- =================================================
                    PRODUCT
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:18px 32px 0;
                        "
                    >

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                border:1px solid #E1E8E4;
                                border-radius:14px;
                            "
                        >

                            <tr>

                                <td
                                    style="
                                        padding:20px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom:5px;
                                            color:#7A8780;
                                            font-size:11px;
                                        "
                                    >
                                        Item
                                    </div>


                                    <div
                                        style="
                                            margin-bottom:13px;
                                            color:#17251F;
                                            font-size:17px;
                                            font-weight:700;
                                        "
                                    >
                                        {{ $transaction->title }}
                                    </div>



                                    <table
                                        role="presentation"
                                        width="100%"
                                        cellspacing="0"
                                        cellpadding="0"
                                        border="0"
                                    >

                                        <tr>

                                            <td
                                                style="
                                                    padding:7px 0;
                                                    color:#69766F;
                                                    font-size:12px;
                                                "
                                            >
                                                Unit price
                                            </td>


                                            <td
                                                align="right"
                                                style="
                                                    padding:7px 0;
                                                    color:#17251F;
                                                    font-size:12px;
                                                    font-weight:700;
                                                "
                                            >
                                                ₦{{ number_format((float) $transaction->unit_price, 2) }}
                                            </td>

                                        </tr>



                                        <tr>

                                            <td
                                                style="
                                                    padding:7px 0;
                                                    color:#69766F;
                                                    font-size:12px;
                                                "
                                            >
                                                Quantity
                                            </td>


                                            <td
                                                align="right"
                                                style="
                                                    padding:7px 0;
                                                    color:#17251F;
                                                    font-size:12px;
                                                    font-weight:700;
                                                "
                                            >
                                                {{ number_format($transaction->quantity) }}
                                            </td>

                                        </tr>



                                        <tr>

                                            <td
                                                style="
                                                    padding:7px 0;
                                                    color:#69766F;
                                                    font-size:12px;
                                                "
                                            >
                                                Subtotal
                                            </td>


                                            <td
                                                align="right"
                                                style="
                                                    padding:7px 0;
                                                    color:#17251F;
                                                    font-size:12px;
                                                    font-weight:700;
                                                "
                                            >
                                                ₦{{ number_format((float) $transaction->subtotal, 2) }}
                                            </td>

                                        </tr>



                                        <tr>

                                            <td
                                                style="
                                                    padding:7px 0;
                                                    color:#69766F;
                                                    font-size:12px;
                                                "
                                            >
                                                Delivery
                                            </td>


                                            <td
                                                align="right"
                                                style="
                                                    padding:7px 0;
                                                    color:#17251F;
                                                    font-size:12px;
                                                    font-weight:700;
                                                "
                                            >
                                                ₦{{ number_format((float) $transaction->delivery_fee, 2) }}
                                            </td>

                                        </tr>



                                        <tr>

                                            <td
                                                colspan="2"
                                                style="
                                                    padding-top:10px;
                                                    border-top:1px solid #E5EAE7;
                                                "
                                            >
                                            </td>

                                        </tr>



                                        <tr>

                                            <td
                                                style="
                                                    padding:5px 0;
                                                    color:#344139;
                                                    font-size:13px;
                                                    font-weight:700;
                                                "
                                            >
                                                Total to pay
                                            </td>


                                            <td
                                                align="right"
                                                style="
                                                    padding:5px 0;
                                                    color:#0B3D2E;
                                                    font-size:22px;
                                                    font-weight:800;
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



                {{-- =================================================
                    DELIVERY
                ================================================== --}}

                @if ($transaction->delivery_note)

                    <tr>

                        <td
                            style="
                                padding:18px 32px 0;
                            "
                        >

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    background:#F7F9F8;
                                    border-radius:12px;
                                "
                            >

                                <tr>

                                    <td
                                        style="
                                            padding:16px;
                                        "
                                    >

                                        <div
                                            style="
                                                margin-bottom:5px;
                                                color:#344139;
                                                font-size:12px;
                                                font-weight:700;
                                            "
                                        >
                                            Delivery arrangement
                                        </div>


                                        <div
                                            style="
                                                color:#69766F;
                                                font-size:12px;
                                                line-height:1.6;
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



                {{-- =================================================
                    BUTTON
                ================================================== --}}

                <tr>

                    <td
                        align="center"
                        style="
                            padding:27px 32px 10px;
                        "
                    >

                        <a
                            href="{{ $secureUrl }}"
                            style="
                                display:inline-block;
                                padding:14px 27px;
                                border-radius:11px;
                                background:#12B76A;
                                color:#FFFFFF;
                                font-size:14px;
                                font-weight:700;
                                text-decoration:none;
                            "
                        >

                            Open secure transaction

                        </a>

                    </td>

                </tr>



                {{-- =================================================
                    LOGIN INFORMATION
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:13px 32px 0;
                        "
                    >

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                background:#ECFDF3;
                                border-radius:12px;
                            "
                        >

                            <tr>

                                <td
                                    style="
                                        padding:16px;
                                        color:#506D60;
                                        font-size:12px;
                                        line-height:1.65;
                                    "
                                >

                                    <strong
                                        style="
                                            display:block;
                                            margin-bottom:4px;
                                            color:#067647;
                                        "
                                    >
                                        Buyer identity protection
                                    </strong>


                                    This transaction is reserved for

                                    <strong>
                                        {{ $transaction->buyer_email }}
                                    </strong>.

                                    You will need to log in or create a MidPoint
                                    account using this email address before
                                    continuing to payment.

                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>



                {{-- =================================================
                    PROTECTION
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:22px 32px;
                        "
                    >

                        <div
                            style="
                                margin-bottom:10px;
                                color:#17251F;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            How MidPoint protects this transaction
                        </div>


                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                        >

                            <tr>

                                <td
                                    style="
                                        padding:5px 0;
                                        color:#647169;
                                        font-size:12px;
                                    "
                                >
                                    ✓ Secure MidPoint account login required
                                </td>

                            </tr>


                            <tr>

                                <td
                                    style="
                                        padding:5px 0;
                                        color:#647169;
                                        font-size:12px;
                                    "
                                >
                                    ✓ Transaction amount is fixed by the seller
                                </td>

                            </tr>


                            <tr>

                                <td
                                    style="
                                        padding:5px 0;
                                        color:#647169;
                                        font-size:12px;
                                    "
                                >

                                    ✓
                                    {{ $transaction->inspection_hours }}
                                    hour buyer inspection period

                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>



                {{-- =================================================
                    EXPIRY
                ================================================== --}}

                @if ($transaction->link_expires_at)

                    <tr>

                        <td
                            style="
                                padding:0 32px 24px;
                            "
                        >

                            <div
                                style="
                                    padding:13px;
                                    border-radius:10px;
                                    background:#FFF7E8;
                                    color:#8A4B08;
                                    font-size:11px;
                                    line-height:1.6;
                                "
                            >

                                This secure link expires on

                                <strong>
                                    {{ $transaction->link_expires_at->format('d M Y, h:i A') }}
                                </strong>.

                            </div>

                        </td>

                    </tr>

                @endif



                {{-- =================================================
                    RAW LINK
                ================================================== --}}

                <tr>

                    <td
                        style="
                            padding:20px 32px;
                            border-top:1px solid #E7ECE9;
                            background:#FAFCFB;
                        "
                    >

                        <div
                            style="
                                margin-bottom:6px;
                                color:#78857E;
                                font-size:10px;
                            "
                        >
                            If the button does not work, copy and paste this link:
                        </div>


                        <div
                            style="
                                overflow-wrap:anywhere;
                                color:#087443;
                                font-size:10px;
                                line-height:1.6;
                            "
                        >
                            {{ $secureUrl }}
                        </div>

                    </td>

                </tr>



                {{-- =================================================
                    FOOTER
                ================================================== --}}

                <tr>

                    <td
                        align="center"
                        style="
                            padding:24px 32px;
                            background:#0B3D2E;
                        "
                    >

                        <div
                            style="
                                margin-bottom:5px;
                                color:#FFFFFF;
                                font-size:14px;
                                font-weight:700;
                            "
                        >
                            MidPoint
                        </div>


                        <div
                            style="
                                color:#9DBBAF;
                                font-size:10px;
                                line-height:1.6;
                            "
                        >
                            Buy with confidence. Sell with confidence.
                        </div>


                        <div
                            style="
                                margin-top:10px;
                                color:#7FA398;
                                font-size:9px;
                                line-height:1.6;
                            "
                        >
                            If you were not expecting this transaction,
                            do not make payment and contact MidPoint support.
                        </div>

                    </td>

                </tr>

            </table>

        </td>

    </tr>

</table>


</body>

</html>