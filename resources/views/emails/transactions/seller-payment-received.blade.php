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
        background:#F2F6F4;
        font-family:Arial,Helvetica,sans-serif;
        color:#17251F;
    "
>


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

        Secure seller transaction

    </div>

</td>

</tr>



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

        PAYMENT SECURED

    </div>


    <h1
        style="
            margin:17px 0 8px;
            font-size:25px;
            line-height:1.3;
        "
    >

        Your buyer has completed payment.

    </h1>


    <p
        style="
            margin:0;
            color:#637169;
            font-size:14px;
            line-height:1.7;
        "
    >

        MidPoint successfully verified the buyer's payment for
        <strong>{{ $transaction->title }}</strong>.
        You can now start preparing the order.

    </p>



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
                style="
                    padding:20px;
                "
            >

                <div
                    style="
                        color:#64746C;
                        font-size:11px;
                    "
                >

                    Buyer paid

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
                        (float) (
                            $transaction->paid_amount
                            ?:
                            $transaction->total_amount
                        ),
                        2
                    ) }}

                </div>

            </td>

        </tr>

    </table>



    <table
        width="100%"
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
                    padding:8px 0;
                    color:#78857E;
                    font-size:12px;
                "
            >

                Transaction

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

                {{
                    $transaction->buyer?->name
                    ?:
                    $transaction->buyer_email
                }}

            </td>

        </tr>

    </table>



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

        The buyer's payment is secured. Do not ask the buyer to pay
        again. Update the transaction as you prepare and dispatch
        the order.

    </div>



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

            Manage transaction

        </a>

    </div>

</td>

</tr>



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

    MidPoint secure transaction platform

</td>

</tr>

</table>

</td>

</tr>

</table>

</body>

</html>