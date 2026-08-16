<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Seller Payment Confirmed
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


    {{-- HEADER --}}
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

                            Seller Package Payment Confirmation

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

                            PAYMENT COMPLETE

                        </span>

                    </td>

                </tr>

            </table>

        </td>

    </tr>



    {{-- BODY --}}
    <tr>

        <td style="padding:38px;">


            <div
                style="
                    width:60px;
                    height:60px;
                    line-height:60px;
                    border-radius:50%;
                    background:#E6F8EE;
                    color:#087443;
                    text-align:center;
                    font-size:28px;
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

                Payment confirmed!

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
                    margin:0 0 24px;
                    color:#5A6660;
                    font-size:14px;
                    line-height:1.8;
                "
            >

                We successfully verified your seller package
                payment with Paystack.

                Your

                <strong>
                    {{ $application->package_name }}
                </strong>

                Verified Seller package is now active.

                Your official paid invoice is attached
                to this email as a PDF.

            </p>



            {{-- ACTIVE PACKAGE --}}
            <div
                style="
                    padding:18px;
                    border:1px solid #ABEFC6;
                    border-radius:14px;
                    background:#ECFDF3;
                "
            >

                <div
                    style="
                        color:#067647;
                        font-size:13px;
                        font-weight:700;
                    "
                >

                    ✓ Verified Seller Active

                </div>


                <div
                    style="
                        margin-top:7px;
                        color:#446054;
                        font-size:13px;
                        line-height:1.6;
                    "
                >

                    Your package allows you to list
                    up to

                    <strong>

                        {{
                            number_format(
                                $application->product_limit
                            )
                        }}

                        products.

                    </strong>

                </div>

            </div>



            {{-- RECEIPT --}}
            <div
                style="
                    margin-top:25px;
                    padding:24px;
                    border:1px solid #DDE7E1;
                    border-radius:16px;
                    background:#FAFCFB;
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
                                    color:#17211D;
                                    font-size:15px;
                                    font-weight:700;
                                "
                            >

                                Payment Receipt

                            </div>

                        </td>


                        <td align="right">

                            <span
                                style="
                                    display:inline-block;
                                    padding:6px 10px;
                                    border-radius:999px;
                                    background:#DDF8E9;
                                    color:#087443;
                                    font-size:10px;
                                    font-weight:700;
                                "
                            >

                                PAID

                            </span>

                        </td>

                    </tr>

                </table>



                <div
                    style="
                        margin-top:18px;
                        color:#0B3D2E;
                        font-size:36px;
                        font-weight:800;
                    "
                >

                    ₦{{
                        number_format(
                            (float) $invoice->amount,
                            2
                        )
                    }}

                </div>


                <div
                    style="
                        margin-top:3px;
                        color:#718078;
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
                        margin-top:21px;
                        border-top:1px solid #E4EBE7;
                    "
                >


                    <tr>

                        <td
                            style="
                                padding-top:14px;
                                color:#718078;
                                font-size:11px;
                            "
                        >

                            Invoice number

                        </td>


                        <td
                            align="right"
                            style="
                                padding-top:14px;
                                font-size:11px;
                                font-weight:700;
                            "
                        >

                            {{ $invoice->invoice_number }}

                        </td>

                    </tr>



                    @if($invoice->payment_reference)

                        <tr>

                            <td
                                style="
                                    padding-top:9px;
                                    color:#718078;
                                    font-size:11px;
                                "
                            >

                                Payment reference

                            </td>


                            <td
                                align="right"
                                style="
                                    padding-top:9px;
                                    font-size:11px;
                                    font-weight:700;
                                "
                            >

                                {{ $invoice->payment_reference }}

                            </td>

                        </tr>

                    @endif



                    @if($invoice->payment_method)

                        <tr>

                            <td
                                style="
                                    padding-top:9px;
                                    color:#718078;
                                    font-size:11px;
                                "
                            >

                                Payment method

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
                                    ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $invoice->payment_method
                                        )
                                    )
                                }}

                            </td>

                        </tr>

                    @endif



                    @if($invoice->paid_at)

                        <tr>

                            <td
                                style="
                                    padding-top:9px;
                                    color:#718078;
                                    font-size:11px;
                                "
                            >

                                Payment date

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
                                        ->paid_at
                                        ->format(
                                            'd M Y, h:i A'
                                        )
                                }}

                            </td>

                        </tr>

                    @endif



                    <tr>

                        <td
                            style="
                                padding-top:9px;
                                color:#718078;
                                font-size:11px;
                            "
                        >

                            Business

                        </td>


                        <td
                            align="right"
                            style="
                                padding-top:9px;
                                font-size:11px;
                                font-weight:700;
                            "
                        >

                            {{ $application->business_name }}

                        </td>

                    </tr>



                    <tr>

                        <td
                            style="
                                padding-top:9px;
                                color:#718078;
                                font-size:11px;
                            "
                        >

                            Package

                        </td>


                        <td
                            align="right"
                            style="
                                padding-top:9px;
                                font-size:11px;
                                font-weight:700;
                            "
                        >

                            {{ $application->package_name }}

                        </td>

                    </tr>

                </table>

            </div>



            {{-- ATTACHMENT NOTICE --}}
            <div
                style="
                    margin-top:18px;
                    padding:14px;
                    border-radius:12px;
                    background:#F5F7F6;
                    color:#5A6660;
                    font-size:12px;
                    line-height:1.7;
                "
            >

                <strong style="color:#17211D;">

                    PDF invoice attached:

                </strong>

                {{ $invoice->invoice_number }}.pdf

            </div>



            {{-- BUTTON --}}
            <table
                cellpadding="0"
                cellspacing="0"
                style="margin-top:28px;"
            >

                <tr>

                    <td
                        style="
                            border-radius:10px;
                            background:#0B3D2E;
                        "
                    >

                        <a
                            href="{{ route('seller.products') }}"
                            style="
                                display:inline-block;
                                padding:15px 26px;
                                color:#FFFFFF;
                                font-size:13px;
                                font-weight:700;
                                text-decoration:none;
                            "
                        >

                            Start Listing Products →

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

                Keep this email and the attached
                invoice for your payment records.

            </p>

        </td>

    </tr>



    {{-- FOOTER --}}
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

            Receipt sent to

            <strong>
                {{ $user->email }}
            </strong>

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