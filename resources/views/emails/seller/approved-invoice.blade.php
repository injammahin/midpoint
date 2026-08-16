<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Seller Application Approved
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#F2F6F4;
        font-family:Arial,Helvetica,sans-serif;
        color:#17211D;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        padding:40px 15px;
        background:#F2F6F4;
    "
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        max-width:640px;
        overflow:hidden;
        border-radius:20px;
        background:#FFFFFF;
        box-shadow:0 12px 35px rgba(11,61,46,.08);
    "
>


    {{-- Header --}}
    <tr>

        <td
            style="
                padding:30px 38px;
                background:#0B3D2E;
            "
        >

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
            >

                <tr>

                    <td>

                        <div
                            style="
                                color:#FFFFFF;
                                font-size:25px;
                                font-weight:800;
                            "
                        >

                            Mid<span style="color:#8066FF;">Point</span>

                        </div>


                        <div
                            style="
                                margin-top:5px;
                                color:#9CEAC6;
                                font-size:12px;
                            "
                        >

                            Verified Seller Program

                        </div>

                    </td>


                    <td align="right">

                        <span
                            style="
                                display:inline-block;
                                padding:8px 12px;
                                border-radius:999px;
                                background:#DDF8E9;
                                color:#087443;
                                font-size:11px;
                                font-weight:700;
                            "
                        >

                            APPROVED

                        </span>

                    </td>

                </tr>

            </table>

        </td>

    </tr>



    {{-- Body --}}
    <tr>

        <td
            style="
                padding:38px;
            "
        >

            <div
                style="
                    width:58px;
                    height:58px;
                    line-height:58px;
                    border-radius:50%;
                    background:#E8F7EF;
                    color:#087443;
                    text-align:center;
                    font-size:26px;
                    font-weight:800;
                "
            >
                ✓
            </div>


            <h1
                style="
                    margin:22px 0 10px;
                    color:#101915;
                    font-size:25px;
                "
            >

                Your seller application is approved!

            </h1>


            <p
                style="
                    margin:0 0 14px;
                    color:#5A6660;
                    font-size:15px;
                    line-height:1.7;
                "
            >

                Hi {{ $user->name }},

            </p>


            <p
                style="
                    margin:0 0 26px;
                    color:#5A6660;
                    font-size:14px;
                    line-height:1.8;
                "
            >

                Great news — your application to become a
                Midpoint Verified Seller has been approved.

                Your seller package invoice is now ready.
                Complete payment to activate your seller account.

            </p>



            {{-- Application --}}
            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    border:1px solid #E3EAE6;
                    border-radius:14px;
                    background:#F9FBFA;
                "
            >

                <tr>

                    <td
                        style="
                            padding:15px 18px 8px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Application
                    </td>


                    <td
                        align="right"
                        style="
                            padding:15px 18px 8px;
                            font-size:12px;
                            font-weight:700;
                        "
                    >

                        {{ $application->reference }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:9px 18px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Business
                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px;
                            font-size:12px;
                            font-weight:700;
                        "
                    >

                        {{ $application->business_name }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:9px 18px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Package
                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px;
                            font-size:12px;
                            font-weight:700;
                        "
                    >

                        {{ $application->package_name }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:9px 18px 15px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Product allowance
                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px 15px;
                            font-size:12px;
                            font-weight:700;
                        "
                    >

                        {{
                            number_format(
                                $application->product_limit
                            )
                        }}

                        products

                    </td>

                </tr>

            </table>



            {{-- =================================================
                INVOICE
            ================================================== --}}

            @if($invoice)

                <div
                    style="
                        margin-top:25px;
                        padding:24px;
                        border:1px solid #ABEFC6;
                        border-radius:16px;
                        background:#F3FFF8;
                    "
                >

                    <table
                        width="100%"
                        cellpadding="0"
                        cellspacing="0"
                    >

                        <tr>

                            <td>

                                <div
                                    style="
                                        color:#067647;
                                        font-size:12px;
                                        font-weight:700;
                                        text-transform:uppercase;
                                    "
                                >

                                    Seller Package Invoice

                                </div>

                            </td>


                            <td
                                align="right"
                            >

                                <span
                                    style="
                                        display:inline-block;
                                        padding:6px 10px;
                                        border-radius:999px;
                                        background:#FFF2D9;
                                        color:#B54708;
                                        font-size:10px;
                                        font-weight:700;
                                    "
                                >

                                    UNPAID

                                </span>

                            </td>

                        </tr>

                    </table>



                    <div
                        style="
                            margin-top:17px;
                            color:#0B3D2E;
                            font-size:34px;
                            font-weight:800;
                        "
                    >

                        ₦{{
                            number_format(
                                (float) $invoice->amount,
                                0
                            )
                        }}

                    </div>



                    <div
                        style="
                            margin-top:4px;
                            color:#617169;
                            font-size:12px;
                        "
                    >

                        {{ $application->package_name }}
                        seller package

                    </div>



                    <table
                        width="100%"
                        cellpadding="0"
                        cellspacing="0"
                        style="
                            margin-top:20px;
                            border-top:1px solid #D5EEE0;
                        "
                    >

                        <tr>

                            <td
                                style="
                                    padding-top:14px;
                                    color:#6F7C75;
                                    font-size:11px;
                                "
                            >
                                Invoice number
                            </td>


                            <td
                                align="right"
                                style="
                                    padding-top:14px;
                                    color:#17211D;
                                    font-size:11px;
                                    font-weight:700;
                                "
                            >

                                {{ $invoice->invoice_number }}

                            </td>

                        </tr>


                        <tr>

                            <td
                                style="
                                    padding-top:9px;
                                    color:#6F7C75;
                                    font-size:11px;
                                "
                            >
                                Currency
                            </td>


                            <td
                                align="right"
                                style="
                                    padding-top:9px;
                                    font-size:11px;
                                    font-weight:700;
                                "
                            >

                                {{ $invoice->currency }}

                            </td>

                        </tr>


                        @if($invoice->issued_at)

                            <tr>

                                <td
                                    style="
                                        padding-top:9px;
                                        color:#6F7C75;
                                        font-size:11px;
                                    "
                                >
                                    Issued
                                </td>


                                <td
                                    align="right"
                                    style="
                                        padding-top:9px;
                                        font-size:11px;
                                        font-weight:700;
                                    "
                                >

                                    {{
                                        $invoice
                                            ->issued_at
                                            ->format('d M Y')
                                    }}

                                </td>

                            </tr>

                        @endif


                        @if($invoice->due_at)

                            <tr>

                                <td
                                    style="
                                        padding-top:9px;
                                        color:#6F7C75;
                                        font-size:11px;
                                    "
                                >
                                    Payment due
                                </td>


                                <td
                                    align="right"
                                    style="
                                        padding-top:9px;
                                        color:#B54708;
                                        font-size:11px;
                                        font-weight:700;
                                    "
                                >

                                    {{
                                        $invoice
                                            ->due_at
                                            ->format('d M Y')
                                    }}

                                </td>

                            </tr>

                        @endif

                    </table>

                </div>

            @endif



            {{-- Pay --}}
            <table
                cellpadding="0"
                cellspacing="0"
                style="
                    margin-top:27px;
                "
            >

                <tr>

                    <td
                        style="
                            border-radius:10px;
                            background:#0B3D2E;
                        "
                    >

                        <a
                            href="{{ route('verified-sellers') }}#seller-invoice"
                            style="
                                display:inline-block;
                                padding:15px 26px;
                                color:#FFFFFF;
                                font-size:13px;
                                font-weight:700;
                                text-decoration:none;
                            "
                        >

                            Pay Seller Invoice →

                        </a>

                    </td>

                </tr>

            </table>



            <p
                style="
                    margin:23px 0 0;
                    color:#7A8680;
                    font-size:12px;
                    line-height:1.7;
                "
            >

                Your seller account will become active immediately
                after your payment is successfully confirmed.

            </p>

        </td>

    </tr>



    {{-- Footer --}}
    <tr>

        <td
            style="
                padding:22px 38px;
                border-top:1px solid #EDF1EF;
                background:#FBFCFB;
                color:#8A958F;
                font-size:11px;
                line-height:1.7;
                text-align:center;
            "
        >

            Midpoint Verified Seller Program

            <br>

            Sent to
            <strong>{{ $user->email }}</strong>

            <br>

            © {{ date('Y') }} Midpoint

        </td>

    </tr>

</table>


</td>

</tr>

</table>

</body>

</html>