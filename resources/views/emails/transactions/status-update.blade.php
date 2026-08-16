<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    {{ $heading }}
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
    style="
        padding:38px 15px;
    "
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
        padding:25px 30px;
        background:#0B3D2E;
        color:#FFFFFF;
    "
>

    <div
        style="
            font-size:23px;
            font-weight:700;
            letter-spacing:-0.4px;
        "
    >

        Mid<span style="color:#9B87FF;">Point</span>

    </div>

    <div
        style="
            margin-top:5px;
            color:#ACCCC0;
            font-size:12px;
        "
    >

        Secure transaction update

    </div>

</td>

</tr>



<tr>

<td
    style="
        padding:32px 30px 30px;
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

        {{
    $badgeText
        ? strtoupper($badgeText)
        : strtoupper($transaction->status_label)
}}

    </div>


    <h1
        style="
            margin:17px 0 9px;
            color:#101915;
            font-size:24px;
            line-height:1.3;
        "
    >

        {{ $heading }}

    </h1>


    <p
        style="
            margin:0;
            color:#617069;
            font-size:14px;
            line-height:1.7;
        "
    >

        {{ $statusMessage }}

    </p>



    <table
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        role="presentation"
        style="
            margin-top:24px;
            background:#F7F9F8;
            border-radius:11px;
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
                        color:#819087;
                        font-size:11px;
                    "
                >

                    Transaction reference

                </div>

                <div
                    style="
                        margin-top:4px;
                        color:#17251F;
                        font-size:14px;
                        font-weight:700;
                    "
                >

                    {{ $transaction->reference }}

                </div>

            </td>

        </tr>


        <tr>

            <td
                style="
                    padding:0 18px 18px;
                "
            >

                <div
                    style="
                        color:#819087;
                        font-size:11px;
                    "
                >

                    Item

                </div>

                <div
                    style="
                        margin-top:4px;
                        color:#17251F;
                        font-size:14px;
                        font-weight:700;
                    "
                >

                    {{ $transaction->title }}

                </div>

            </td>

        </tr>

    </table>



    <div
        style="
            margin-top:27px;
            text-align:center;
        "
    >

        <a
            href="{{ $actionUrl }}"
            style="
                display:inline-block;
                padding:14px 27px;
                border-radius:10px;
                background:#12B76A;
                color:#FFFFFF;
                font-size:14px;
                font-weight:700;
                text-decoration:none;
            "
        >

            {{ $actionText }}

        </a>

    </div>

</td>

</tr>



<tr>

<td
    align="center"
    style="
        padding:20px 30px;
        border-top:1px solid #E4EAE7;
        color:#87948D;
        font-size:11px;
    "
>

    Midpoint · Secure transactions between buyers and sellers

</td>

</tr>

</table>

</td>

</tr>

</table>

</body>

</html>