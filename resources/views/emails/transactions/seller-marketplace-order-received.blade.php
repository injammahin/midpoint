<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        New Marketplace Order
    </title>
</head>


<body
    style="
        margin:0;
        padding:0;
        background:#F2F6F4;
        font-family:Arial,Helvetica,sans-serif;
        color:#17251F;
    "
>

@php

    $buyerName =
        $transaction->buyer?->name
        ?:
        $transaction->buyer_email;


    $totalPaid =
        (float) (
            $transaction->paid_amount
            ?:
            $transaction->total_amount
        );

@endphp


<table
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    role="presentation"
>

<tr>

<td
    align="center"
    style="padding:38px 15px;"
>


<table
    width="600"
    cellspacing="0"
    cellpadding="0"
    border="0"
    role="presentation"
    style="
        width:100%;
        max-width:600px;
        background:#FFFFFF;
        border:1px solid #DCE5E0;
        border-radius:16px;
        overflow:hidden;
    "
>


{{-- =========================================================
    HEADER
========================================================= --}}

<tr>

<td
    style="
        padding:26px 30px;
        background:#0B3D2E;
        color:#FFFFFF;
    "
>

    <div
        style="
            font-size:23px;
            font-weight:700;
        "
    >

        Mid<span style="color:#9B87FF;">Point</span>

    </div>


    <div
        style="
            margin-top:5px;
            color:#A9C9BD;
            font-size:12px;
        "
    >

        Marketplace order

    </div>

</td>

</tr>


{{-- =========================================================
    BODY
========================================================= --}}

<tr>

<td
    style="
        padding:32px 30px;
    "
>


    <div
        style="
            display:inline-block;
            padding:7px 11px;
            border-radius:100px;
            background:#ECFDF3;
            color:#067647;
            font-size:11px;
            font-weight:700;
        "
    >

        NEW ORDER · PAYMENT SECURED

    </div>


    <h1
        style="
            margin:17px 0 8px;
            font-size:25px;
            line-height:1.3;
        "
    >

        You got a new order from {{ $buyerName }}.

    </h1>


    <p
        style="
            margin:0;
            color:#637169;
            font-size:14px;
            line-height:1.7;
        "
    >

        A buyer purchased your listed product

        <strong>
            {{ $transaction->title }}
        </strong>

        through Midpoint.

        The payment has been verified and secured.

    </p>


    {{-- =====================================================
        TOTAL PAYMENT
    ====================================================== --}}

    <table
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            margin-top:23px;
            background:#F2FCF6;
            border-radius:12px;
        "
    >

        <tr>

            <td
                align="center"
                style="padding:20px;"
            >

                <div
                    style="
                        color:#64746C;
                        font-size:11px;
                    "
                >

                    Total payment secured

                </div>


                <div
                    style="
                        margin-top:5px;
                        color:#0B3D2E;
                        font-size:28px;
                        font-weight:800;
                    "
                >

                    ₦{{ number_format(
                        $totalPaid,
                        2
                    ) }}

                </div>

            </td>

        </tr>

    </table>


    {{-- =====================================================
        ORDER DETAILS TITLE
    ====================================================== --}}

    <div
        style="
            margin-top:24px;
            font-size:15px;
            font-weight:800;
        "
    >

        Order details

    </div>


    {{-- =====================================================
        ORDER DETAILS TABLE
    ====================================================== --}}

    <table
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="margin-top:8px;"
    >


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Order / transaction

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                {{ $transaction->reference }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Item

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                {{ $transaction->title }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Quantity

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                {{ number_format(
                    (int) $transaction->quantity
                ) }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Unit price

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                ₦{{ number_format(
                    (float) $transaction->unit_price,
                    2
                ) }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Product subtotal

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                ₦{{ number_format(
                    (float) $transaction->subtotal,
                    2
                ) }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Delivery

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                ₦{{ number_format(
                    (float) $transaction->delivery_fee,
                    2
                ) }}

            </td>

        </tr>

    </table>


    {{-- =====================================================
        BUYER
    ====================================================== --}}

    <div
        style="
            margin-top:22px;
            padding-top:20px;
            border-top:1px solid #E4EAE7;
            font-size:15px;
            font-weight:800;
        "
    >

        Buyer & delivery

    </div>


    <table
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="margin-top:8px;"
    >


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Buyer

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                {{ $buyerName }}

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Buyer email

            </td>


            <td
                align="right"
                style="
                    padding:8px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >

                {{ $transaction->buyer_email }}

            </td>

        </tr>


        @if($transaction->buyer_phone)

            <tr>

                <td
                    style="
                        padding:8px 0;
                        color:#78857E;
                        font-size:12px;
                    "
                >

                    Delivery phone

                </td>


                <td
                    align="right"
                    style="
                        padding:8px 0;
                        font-size:12px;
                        font-weight:700;
                    "
                >

                    {{ $transaction->buyer_phone }}

                </td>

            </tr>

        @endif


        <tr>

            <td
                valign="top"
                style="
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Delivery address

            </td>


            <td
                align="right"
                style="
                    padding:8px 0 8px 18px;
                    font-size:12px;
                    font-weight:700;
                    line-height:1.55;
                "
            >

                {{
                    $transaction->delivery_note
                    ?:
                    'Not provided'
                }}

            </td>

        </tr>

    </table>


    {{-- =====================================================
        IMPORTANT INFO
    ====================================================== --}}

    <div
        style="
            margin-top:23px;
            padding:15px;
            border-radius:10px;
            background:#FFF8EB;
            color:#775A20;
            font-size:12px;
            line-height:1.65;
        "
    >

        This is a marketplace order placed directly from one of
        your listed products.

        The buyer's payment is secured.

        Prepare the exact item and quantity shown above, then
        update the transaction status as you fulfil the order.

    </div>


    {{-- =====================================================
        BUTTON
    ====================================================== --}}

    <div
        style="
            margin-top:26px;
            text-align:center;
        "
    >

        <a
            href="{{
                route(
                    'seller.transactions.show',
                    [
                        'secureTransaction' =>
                            $transaction->public_token,
                    ]
                )
            }}"
            style="
                display:inline-block;
                padding:14px 26px;
                border-radius:10px;
                background:#12B76A;
                color:#FFFFFF;
                font-size:14px;
                font-weight:700;
                text-decoration:none;
            "
        >

            View order details

        </a>

    </div>


</td>

</tr>


{{-- =========================================================
    FOOTER
========================================================= --}}

<tr>

<td
    align="center"
    style="
        padding:20px;
        border-top:1px solid #E4EAE7;
        color:#849189;
        font-size:11px;
    "
>

    Midpoint secure transaction platform

</td>

</tr>


</table>

</td>

</tr>

</table>


</body>

</html>