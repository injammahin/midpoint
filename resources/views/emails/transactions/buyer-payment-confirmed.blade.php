<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Payment Confirmed
    </title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#F4F7F5;
        font-family:Arial,Helvetica,sans-serif;
        color:#17251F;
    "
>

<table
    width="100%"
    role="presentation"
    cellspacing="0"
    cellpadding="0"
    border="0"
>

<tr>

<td
    align="center"
    style="padding:35px 15px;"
>

<table
    width="600"
    role="presentation"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        width:100%;
        max-width:600px;
        background:#FFFFFF;
        border:1px solid #DCE5E0;
        border-radius:18px;
        overflow:hidden;
    "
>

<tr>

<td
    style="
        padding:27px 30px;
        background:#0B3D2E;
        color:#FFFFFF;
    "
>

    <div
        style="
            font-size:21px;
            font-weight:700;
        "
    >
        Mid<span style="color:#B7A7FF;">Point</span>
    </div>

</td>

</tr>

<tr>

<td style="padding:32px 30px;">

    <div
        style="
            display:inline-block;
            padding:6px 10px;
            background:#ECFDF3;
            color:#067647;
            border-radius:999px;
            font-size:10px;
            font-weight:700;
        "
    >
        PAYMENT CONFIRMED
    </div>

    <h1
        style="
            margin:15px 0 8px;
            font-size:24px;
        "
    >
        Your payment is secured.
    </h1>

    <p
        style="
            margin:0;
            color:#647169;
            font-size:13px;
            line-height:1.7;
        "
    >
        Your payment for
        <strong>{{ $transaction->title }}</strong>
        has been successfully verified by MidPoint.
    </p>

    <div
        style="
            margin-top:22px;
            padding:20px;
            border-radius:12px;
            background:#F2FCF6;
            text-align:center;
        "
    >

        <div
            style="
                color:#637069;
                font-size:11px;
            "
        >
            Amount secured
        </div>

        <div
            style="
                margin-top:5px;
                color:#0B3D2E;
                font-size:27px;
                font-weight:800;
            "
        >
            ₦{{ number_format((float) ($transaction->paid_amount ?: $transaction->total_amount), 2) }}
        </div>

    </div>

    <table
        width="100%"
        role="presentation"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            margin-top:20px;
        "
    >

        <tr>

            <td
                style="
                    padding:7px 0;
                    color:#69766F;
                    font-size:12px;
                "
            >
                Transaction
            </td>

            <td
                align="right"
                style="
                    padding:7px 0;
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
                    padding:7px 0;
                    color:#69766F;
                    font-size:12px;
                "
            >
                Invoice
            </td>

            <td
                align="right"
                style="
                    padding:7px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >
                {{ $transaction->invoice_number }}
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
                Seller
            </td>

            <td
                align="right"
                style="
                    padding:7px 0;
                    font-size:12px;
                    font-weight:700;
                "
            >
                {{ $transaction->seller?->name }}
            </td>

        </tr>

    </table>

    <div
        style="
            margin-top:20px;
            padding:15px;
            border-radius:11px;
            background:#EFF8FF;
            color:#4774A9;
            font-size:12px;
            line-height:1.65;
        "
    >
        The seller has been notified and can now prepare your order.
        Your payment invoice is attached to this email.
    </div>

    <div style="margin-top:25px;text-align:center;">

        <a
            href="{{ route('buyer.transactions.show', $transaction) }}"
            style="
                display:inline-block;
                padding:14px 25px;
                border-radius:10px;
                background:#12B76A;
                color:#FFFFFF;
                font-size:13px;
                font-weight:700;
                text-decoration:none;
            "
        >
            Track transaction
        </a>

    </div>

</td>

</tr>

<tr>

<td
    align="center"
    style="
        padding:22px;
        background:#0B3D2E;
        color:#9DBBAF;
        font-size:10px;
    "
>
    MidPoint secure transaction platform
</td>

</tr>

</table>

</td>

</tr>

</table>

</body>
</html>