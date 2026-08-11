<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Payment Received
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
    role="presentation"
    width="100%"
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

    <div
        style="
            margin-top:4px;
            color:#9DBBAF;
            font-size:11px;
        "
    >
        Secure transaction update
    </div>

</td>

</tr>

<tr>

<td style="padding:32px 30px;">

    <div
        style="
            display:inline-block;
            padding:6px 10px;
            border-radius:999px;
            background:#ECFDF3;
            color:#067647;
            font-size:10px;
            font-weight:700;
        "
    >
        PAYMENT SECURED
    </div>

    <h1
        style="
            margin:15px 0 8px;
            font-size:24px;
            color:#101915;
        "
    >
        Your buyer has completed payment.
    </h1>

    <p
        style="
            margin:0;
            color:#647169;
            font-size:13px;
            line-height:1.7;
        "
    >
        MidPoint has successfully verified the payment for
        <strong>{{ $transaction->title }}</strong>.
    </p>

    <table
        width="100%"
        role="presentation"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            margin-top:22px;
            border:1px solid #DDE5E1;
            border-radius:12px;
        "
    >

        <tr>
            <td
                style="
                    padding:18px;
                "
            >

                <div
                    style="
                        color:#7B8781;
                        font-size:10px;
                    "
                >
                    Transaction
                </div>

                <div
                    style="
                        margin-top:4px;
                        font-size:14px;
                        font-weight:700;
                    "
                >
                    {{ $transaction->reference }}
                </div>

            </td>
        </tr>

    </table>

    <table
        width="100%"
        role="presentation"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="margin-top:18px;"
    >

        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#69766F;
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
                    color:#69766F;
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
                {{ $transaction->buyer?->name ?: $transaction->buyer_email }}
            </td>

        </tr>

        <tr>

            <td
                style="
                    padding:8px 0;
                    color:#69766F;
                    font-size:12px;
                "
            >
                Amount secured
            </td>

            <td
                align="right"
                style="
                    padding:8px 0;
                    color:#067647;
                    font-size:18px;
                    font-weight:800;
                "
            >
                ₦{{ number_format((float) ($transaction->paid_amount ?: $transaction->total_amount), 2) }}
            </td>

        </tr>

    </table>

    <div
        style="
            margin-top:22px;
            padding:15px;
            border-radius:11px;
            background:#F2FCF6;
            color:#567166;
            font-size:12px;
            line-height:1.65;
        "
    >
        The buyer's payment is secured. You can now prepare the item
        for fulfilment. Do not request another payment from the buyer.
    </div>

    <div style="margin-top:25px;text-align:center;">

        <a
            href="{{ route('seller.transactions.show', $transaction) }}"
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
            View transaction
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