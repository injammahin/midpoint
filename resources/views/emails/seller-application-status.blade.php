<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $emailTitle }}
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#F4F7F6;
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
        padding:35px 15px;
        background:#F4F7F6;
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
        max-width:620px;
        background:#FFFFFF;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 10px 35px rgba(11,61,46,.08);
    "
>


    {{-- =====================================================
        HEADER
    ====================================================== --}}

    <tr>

        <td
            style="
                padding:28px 34px;
                background:#0B3D2E;
            "
        >

            <div
                style="
                    color:#FFFFFF;
                    font-size:24px;
                    font-weight:800;
                "
            >

                Mid<span style="color:#7B61FF;">Point</span>

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

    </tr>



    {{-- =====================================================
        BODY
    ====================================================== --}}

    <tr>

        <td style="padding:34px;">


            {{-- Status Icon --}}
            <div
                style="
                    width:50px;
                    height:50px;
                    line-height:50px;
                    border-radius:50%;
                    background:{{ $accentBackground }};
                    color:{{ $accentColor }};
                    text-align:center;
                    font-size:22px;
                    font-weight:bold;
                "
            >

                @if($type === 'revision_required')

                    !

                @else

                    ✓

                @endif

            </div>



            {{-- Title --}}
            <h1
                style="
                    margin:22px 0 10px;
                    color:#101915;
                    font-size:24px;
                    line-height:1.3;
                "
            >

                {{ $emailTitle }}

            </h1>



            {{-- Greeting --}}
            <p
                style="
                    margin:0 0 12px;
                    color:#5A6660;
                    font-size:15px;
                    line-height:1.7;
                "
            >

                Hi {{ $user->name }},

            </p>



            {{-- Message --}}
            <p
                style="
                    margin:0 0 24px;
                    color:#5A6660;
                    font-size:15px;
                    line-height:1.7;
                "
            >

                {{ $emailMessage }}

            </p>



            {{-- =================================================
                APPLICATION DETAILS
            ================================================== --}}

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    border:1px solid #E1E8E4;
                    border-radius:12px;
                    background:#FAFCFB;
                "
            >


                <tr>

                    <td
                        style="
                            padding:14px 18px 8px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >

                        Application reference

                    </td>


                    <td
                        align="right"
                        style="
                            padding:14px 18px 8px;
                            color:#17211D;
                            font-size:13px;
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
                            color:#65726B;
                            font-size:13px;
                        "
                    >

                        Business

                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px;
                            color:#17211D;
                            font-size:13px;
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
                            color:#65726B;
                            font-size:13px;
                        "
                    >

                        Package

                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px;
                            color:#17211D;
                            font-size:13px;
                            font-weight:700;
                        "
                    >

                        {{ $application->package_name }}

                    </td>

                </tr>



                <tr>

                    <td
                        style="
                            padding:9px 18px 14px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >

                        Package price

                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px 14px;
                            color:#17211D;
                            font-size:13px;
                            font-weight:700;
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

                <div
                    style="
                        margin-top:24px;
                        padding:17px;
                        border:1px solid #FEDF89;
                        border-radius:12px;
                        background:#FFF7E8;
                    "
                >

                    <strong
                        style="
                            display:block;
                            margin-bottom:7px;
                            color:#B54708;
                            font-size:13px;
                        "
                    >

                        What you need to correct

                    </strong>


                    <div
                        style="
                            color:#7A5B28;
                            font-size:14px;
                            line-height:1.7;
                        "
                    >

                        {{ $application->revision_note }}

                    </div>

                </div>

            @endif



            {{-- =================================================
                APPROVAL INVOICE
            ================================================== --}}

            @if(
                $type === 'approved'
                &&
                $application->invoice
            )

                <div
                    style="
                        margin-top:24px;
                        padding:18px;
                        border:1px solid #ABEFC6;
                        border-radius:12px;
                        background:#ECFDF3;
                    "
                >

                    <strong
                        style="
                            display:block;
                            color:#067647;
                            font-size:13px;
                        "
                    >

                        Seller Package Invoice

                    </strong>


                    <div
                        style="
                            margin-top:10px;
                            color:#0B3D2E;
                            font-size:25px;
                            font-weight:800;
                        "
                    >

                        ₦{{
                            number_format(
                                (float) $application->invoice->amount,
                                0
                            )
                        }}

                    </div>


                    <div
                        style="
                            margin-top:7px;
                            color:#5A6660;
                            font-size:12px;
                        "
                    >

                        Invoice:
                        {{ $application->invoice->invoice_number }}

                    </div>


                    <div
                        style="
                            margin-top:4px;
                            color:#5A6660;
                            font-size:12px;
                        "
                    >

                        Status:
                        Unpaid

                    </div>

                </div>

            @endif



            {{-- =================================================
                ACTIVATION DETAILS
            ================================================== --}}

            @if($type === 'payment_successful')

                <div
                    style="
                        margin-top:24px;
                        padding:18px;
                        border:1px solid #ABEFC6;
                        border-radius:12px;
                        background:#ECFDF3;
                    "
                >

                    <strong
                        style="
                            color:#067647;
                        "
                    >

                        {{ $application->package_name }}
                        package activated

                    </strong>


                    <div
                        style="
                            margin-top:8px;
                            color:#5A6660;
                            font-size:13px;
                        "
                    >

                        Product limit:

                        <strong>

                            {{ $application->product_limit }}

                            products

                        </strong>

                    </div>

                </div>

            @endif



            {{-- =================================================
                BUTTON
            ================================================== --}}

            <a
                href="{{ $buttonUrl }}"
                style="
                    display:inline-block;
                    margin-top:25px;
                    padding:14px 22px;
                    border-radius:10px;
                    background:#0B3D2E;
                    color:#FFFFFF;
                    text-decoration:none;
                    font-size:14px;
                    font-weight:700;
                "
            >

                {{ $buttonText }}

            </a>


        </td>

    </tr>



    {{-- =====================================================
        FOOTER
    ====================================================== --}}

    <tr>

        <td
            style="
                padding:20px 34px;
                border-top:1px solid #EDF1EF;
                color:#8A958F;
                font-size:11px;
                line-height:1.6;
                text-align:center;
            "
        >

            © {{ date('Y') }} MidPoint

            <br>

            This email was sent to

            {{ $user->email }}

        </td>

    </tr>

</table>


</td>

</tr>

</table>

</body>

</html>