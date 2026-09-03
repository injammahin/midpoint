@php
    /*
    |--------------------------------------------------------------------------
    | Uploaded Midpoint logo
    |--------------------------------------------------------------------------
    */
    $configuredLogoPath = trim(
        (string) config('midpoint.logo_path', '')
    );

    $relativeLogoPath = ltrim(
        str_replace('\\', '/', $configuredLogoPath),
        '/'
    );

    $logoUrl = null;

    if ($relativeLogoPath !== '') {
        $absoluteLogoPath = public_path($relativeLogoPath);

        if (
            is_file($absoluteLogoPath)
            && is_readable($absoluteLogoPath)
        ) {
            /*
            | Keep the logo as a public URL. Using $message->embed() can make
            | Gmail display the logo as a separate attachment.
            */
            $logoUrl = asset($relativeLogoPath);
        }
    }
@endphp

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



    {{-- HEADER --}}
    <tr>
        <td
            height="5"
            style="
                height:5px;
                background:#12B76A;
                font-size:0;
                line-height:0;
            "
        >
            &nbsp;
        </td>
    </tr>

    <tr>
        <td
            align="center"
            style="
                padding:30px 38px 26px;
                background:#FFFFFF;
                border-bottom:1px solid #E7ECE9;
            "
        >
            <a
                href="{{ route('home') }}"
                aria-label="Visit Midpoint"
                style="display:inline-block;text-decoration:none;"
            >
                @if($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="Midpoint"
                        width="190"
                        style="
                            display:block;
                            width:auto;
                            height:auto;
                            max-width:190px;
                            max-height:58px;
                            border:0;
                            outline:none;
                        "
                    >
                @else
                    <table
                        role="presentation"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                    >
                        <tr>
                            <td
                                align="center"
                                width="38"
                                height="38"
                                style="
                                    width:38px;
                                    height:38px;
                                    border-radius:11px;
                                    background:#0B3D2E;
                                    color:#FFFFFF;
                                    font-size:18px;
                                    font-weight:700;
                                    line-height:38px;
                                    text-align:center;
                                "
                            >
                                M
                            </td>

                            <td
                                style="
                                    padding-left:10px;
                                    color:#0B3D2E;
                                    font-size:24px;
                                    font-weight:700;
                                    line-height:30px;
                                    vertical-align:middle;
                                "
                            >
                                Mid<span style="color:#7A5AF8;">Point</span>
                            </td>
                        </tr>
                    </table>
                @endif
            </a>

            <div
                style="
                    margin-top:10px;
                    color:#87938D;
                    font-size:11px;
                    line-height:17px;
                "
            >
                Verified Seller Program
            </div>

            <div
                style="
                    display:inline-block;
                    margin-top:14px;
                    padding:7px 12px;
                    border:1px solid #FED7AA;
                    border-radius:999px;
                    background:#FFF7ED;
                    color:#B54708;
                    font-size:10px;
                    font-weight:700;
                    letter-spacing:.5px;
                    line-height:14px;
                "
            >
                REVISION REQUIRED
            </div>
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

                Midpoint seller application.

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

            Midpoint Verified Seller Program

            <br>

            This email was sent to

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