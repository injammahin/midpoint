<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Seller Application Received
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#f4f7f6;
        font-family:Arial,Helvetica,sans-serif;
        color:#17211d;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        padding:35px 15px;
        background:#f4f7f6;
    "
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        max-width:620px;
        background:#ffffff;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 10px 35px rgba(11,61,46,.08);
    "
>


    {{-- Header --}}
    <tr>

        <td
            style="
                padding:28px 34px;
                background:#0B3D2E;
            "
        >

            <div
                style="
                    color:#ffffff;
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



    {{-- Content --}}
    <tr>

        <td style="padding:34px;">


            <div
                style="
                    width:50px;
                    height:50px;
                    line-height:50px;
                    border-radius:50%;
                    background:#E8F7EF;
                    color:#087443;
                    text-align:center;
                    font-size:22px;
                    font-weight:bold;
                "
            >
                ✓
            </div>


            <h1
                style="
                    margin:22px 0 10px;
                    color:#101915;
                    font-size:24px;
                "
            >

                Application received

            </h1>


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


            <p
                style="
                    margin:0 0 24px;
                    color:#5A6660;
                    font-size:15px;
                    line-height:1.7;
                "
            >

                Your application to become a Midpoint
                Verified Seller has been submitted successfully.

                Our team will now review your business
                information and verification documents.

            </p>



            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                style="
                    border:1px solid #E1E8E4;
                    border-radius:12px;
                    background:#FAFCFB;
                "
            >

                <tr>

                    <td
                        style="
                            padding:14px 18px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >
                        Reference
                    </td>


                    <td
                        align="right"
                        style="
                            padding:14px 18px;
                            font-size:13px;
                            font-weight:bold;
                        "
                    >

                        {{ $application->reference }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:10px 18px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >
                        Business
                    </td>


                    <td
                        align="right"
                        style="
                            padding:10px 18px;
                            font-size:13px;
                            font-weight:bold;
                        "
                    >

                        {{ $application->business_name }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:10px 18px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >
                        Package
                    </td>


                    <td
                        align="right"
                        style="
                            padding:10px 18px;
                            font-size:13px;
                            font-weight:bold;
                        "
                    >

                        {{ $application->package_name }}

                    </td>

                </tr>


                <tr>

                    <td
                        style="
                            padding:10px 18px 14px;
                            color:#65726B;
                            font-size:13px;
                        "
                    >
                        Status
                    </td>


                    <td
                        align="right"
                        style="
                            padding:10px 18px 14px;
                            color:#3538CD;
                            font-size:13px;
                            font-weight:bold;
                        "
                    >

                        Under Review

                    </td>

                </tr>

            </table>



            <div
                style="
                    margin-top:24px;
                    padding:15px;
                    border-radius:12px;
                    background:#F5F8FF;
                    color:#475467;
                    font-size:13px;
                    line-height:1.6;
                "
            >

                We will send another email to this address
                when your application is approved or when
                revisions are required.

            </div>



            <a
                href="{{ route('verified-sellers') }}"
                style="
                    display:inline-block;
                    margin-top:24px;
                    padding:14px 22px;
                    border-radius:10px;
                    background:#0B3D2E;
                    color:#ffffff;
                    text-decoration:none;
                    font-size:14px;
                    font-weight:bold;
                "
            >

                View Application Status

            </a>

        </td>

    </tr>



    <tr>

        <td
            style="
                padding:20px 34px;
                border-top:1px solid #EDF1EF;
                color:#8A958F;
                font-size:11px;
                text-align:center;
            "
        >

            © {{ date('Y') }} Midpoint

            <br>

            This message was sent to
            {{ $user->email }}

        </td>

    </tr>

</table>


</td>

</tr>

</table>

</body>

</html>