<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Revision Required
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
        width:100%;
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
        width:100%;
        max-width:640px;
        overflow:hidden;
        border-radius:20px;
        background:#FFFFFF;
        box-shadow:0 12px 35px rgba(11,61,46,.08);
    "
>


    {{-- =====================================================
        HEADER
    ====================================================== --}}

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
                                background:#FFF2D9;
                                color:#B54708;
                                font-size:11px;
                                font-weight:700;
                            "
                        >

                            REVISION REQUIRED

                        </span>

                    </td>

                </tr>

            </table>

        </td>

    </tr>



    {{-- =====================================================
        CONTENT
    ====================================================== --}}

    <tr>

        <td
            style="
                padding:38px;
            "
        >

            {{-- Icon --}}
            <div
                style="
                    width:56px;
                    height:56px;
                    line-height:56px;
                    border-radius:50%;
                    background:#FFF3E0;
                    color:#F79009;
                    text-align:center;
                    font-size:26px;
                    font-weight:800;
                "
            >
                !
            </div>



            <h1
                style="
                    margin:22px 0 10px;
                    color:#101915;
                    font-size:25px;
                    line-height:1.3;
                "
            >

                Your application needs a few changes

            </h1>



            <p
                style="
                    margin:0 0 15px;
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

                Our verification team has reviewed your
                MidPoint seller application.

                Before we can approve it, we need you to
                correct or update the information described below.

            </p>



            {{-- =================================================
                APPLICATION INFO
            ================================================== --}}

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
                            padding:16px 18px 8px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Application reference
                    </td>


                    <td
                        align="right"
                        style="
                            padding:16px 18px 8px;
                            color:#17211D;
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
                            color:#17211D;
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
                            padding:9px 18px 16px;
                            color:#7A8680;
                            font-size:12px;
                        "
                    >
                        Package
                    </td>


                    <td
                        align="right"
                        style="
                            padding:9px 18px 16px;
                            color:#17211D;
                            font-size:12px;
                            font-weight:700;
                        "
                    >

                        {{ $application->package_name }}

                    </td>

                </tr>

            </table>



            {{-- =================================================
                REVISION NOTE
            ================================================== --}}

            <div
                style="
                    margin-top:24px;
                    padding:20px;
                    border:1px solid #FEDF89;
                    border-radius:14px;
                    background:#FFF9ED;
                "
            >

                <div
                    style="
                        margin-bottom:8px;
                        color:#B54708;
                        font-size:13px;
                        font-weight:700;
                    "
                >

                    What needs to be corrected

                </div>


                <div
                    style="
                        color:#6E562D;
                        font-size:14px;
                        line-height:1.8;
                    "
                >

                    {{ $application->revision_note }}

                </div>

            </div>



            <p
                style="
                    margin:23px 0 0;
                    color:#5A6660;
                    font-size:13px;
                    line-height:1.7;
                "
            >

                Please correct the requested information
                and submit a fresh seller application.

                Your previous application will remain in
                your application history.

            </p>



            {{-- Button --}}
            <table
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    margin-top:26px;
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
                            href="{{ route('verified-sellers') }}#verified-application"
                            style="
                                display:inline-block;
                                padding:14px 24px;
                                color:#FFFFFF;
                                font-size:13px;
                                font-weight:700;
                                text-decoration:none;
                            "
                        >

                            Re-apply as a Seller →

                        </a>

                    </td>

                </tr>

            </table>

        </td>

    </tr>



    {{-- =====================================================
        FOOTER
    ====================================================== --}}

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

            MidPoint Verified Seller Program

            <br>

            This email was sent to
            <strong>{{ $user->email }}</strong>

            <br>

            © {{ date('Y') }} MidPoint

        </td>

    </tr>

</table>


</td>

</tr>

</table>

</body>

</html>