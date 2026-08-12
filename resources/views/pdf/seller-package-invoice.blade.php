<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <style>

        @page {
            margin: 38px 42px 35px;
        }


        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;

            color: #17251F;

            font-family:
                DejaVu Sans,
                sans-serif;

            font-size: 11px;

            line-height: 1.5;
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        .brand-table td {
            vertical-align: middle;
        }


        .logo-mark {
            width: 36px;

            height: 36px;

            background: #0B7A50;

            color: #FFFFFF;

            font-size: 18px;

            font-weight: bold;

            text-align: center;
        }


        .brand-name {
            padding-left: 10px;

            color: #0B3D2E;

            font-size: 22px;

            font-weight: bold;
        }


        .brand-purple {
            color: #7557FF;
        }


        .brand-subtitle {
            padding-left: 10px;

            padding-top: 3px;

            color: #748079;

            font-size: 10px;
        }


        .paid-badge {
            display: inline-block;

            padding: 7px 12px;

            border: 1px solid #ABEFC6;

            background: #ECFDF3;

            color: #067647;

            font-size: 10px;

            font-weight: bold;
        }


        .separator {
            margin-top: 18px;

            border-top:
                1px solid #D8DFDB;
        }


        .invoice-title {
            margin-top: 24px;

            color: #101915;

            font-size: 20px;

            font-weight: bold;
        }


        .invoice-subtitle {
            margin-top: 4px;

            color: #748079;

            font-size: 10px;
        }


        .meta-table {
            margin-top: 22px;
        }


        .meta-table td {
            width: 50%;

            vertical-align: top;
        }


        .section-title {
            margin-bottom: 7px;

            color: #17251F;

            font-size: 11px;

            font-weight: bold;
        }


        .muted {
            color: #68756E;
        }


        .right {
            text-align: right;
        }


        .summary-box {
            margin-top: 24px;

            border:
                1px solid #DCE5E0;

            background: #F8FBF9;
        }


        .summary-box td {
            padding: 10px 14px;

            border-bottom:
                1px solid #E4EBE7;
        }


        .summary-box tr:last-child td {
            border-bottom: 0;
        }


        .summary-label {
            color: #65726B;
        }


        .summary-value {
            color: #17251F;

            font-weight: bold;

            text-align: right;
        }


        .total-row td {
            padding-top: 14px;

            padding-bottom: 14px;

            background: #F1FBF5;
        }


        .total-label {
            color: #0B3D2E;

            font-size: 12px;

            font-weight: bold;
        }


        .total-value {
            color: #0B3D2E;

            font-size: 18px;

            font-weight: bold;

            text-align: right;
        }


        .payment-box {
            margin-top: 22px;

            padding: 14px 16px;

            border:
                1px solid #ABEFC6;

            background: #ECFDF3;
        }


        .payment-title {
            color: #067647;

            font-size: 11px;

            font-weight: bold;
        }


        .payment-grid {
            margin-top: 10px;
        }


        .payment-grid td {
            padding: 3px 0;
        }


        .payment-label {
            color: #617169;
        }


        .payment-value {
            color: #17251F;

            font-weight: bold;

            text-align: right;
        }


        .note-box {
            margin-top: 22px;

            padding: 12px 14px;

            background: #F7F9F8;

            color: #66756D;

            font-size: 10px;

            line-height: 1.6;
        }


        .footer {
            position: fixed;

            right: 0;

            bottom: -5px;

            left: 0;

            padding-top: 9px;

            border-top:
                1px solid #D9E0DC;

            color: #849189;

            font-size: 9px;
        }

    </style>

</head>


<body>


<table class="brand-table">

    <tr>

        <td style="width:46px;">

            <div class="logo-mark">

                M

            </div>

        </td>


        <td>

            <div class="brand-name">

                Mid<span class="brand-purple">Point</span>

            </div>


            <div class="brand-subtitle">

                Verified Seller Program

            </div>

        </td>


        <td class="right">

            <span class="paid-badge">

                PAID

            </span>

        </td>

    </tr>

</table>



<div class="separator"></div>



<div class="invoice-title">

    Seller Package Payment Invoice

</div>


<div class="invoice-subtitle">

    Official payment confirmation for

    {{ $application->business_name }}

</div>



<table class="meta-table">

    <tr>

        <td>

            <div class="section-title">

                Billed to

            </div>


            <div>

                <strong>

                    {{ $user->name }}

                </strong>

            </div>


            <div class="muted">

                {{ $user->email }}

            </div>


            @if(!empty($application->phone))

                <div class="muted">

                    {{ $application->phone }}

                </div>

            @endif

        </td>


        <td class="right">

            <div class="section-title">

                Invoice details

            </div>


            <div>

                <span class="muted">

                    Invoice:

                </span>


                <strong>

                    {{ $invoice->invoice_number }}

                </strong>

            </div>


            @if($invoice->issued_at)

                <div>

                    <span class="muted">

                        Issued:

                    </span>


                    <strong>

                        {{
                            $invoice
                                ->issued_at
                                ->format('d M Y')
                        }}

                    </strong>

                </div>

            @endif


            @if($invoice->paid_at)

                <div>

                    <span class="muted">

                        Paid:

                    </span>


                    <strong>

                        {{
                            $invoice
                                ->paid_at
                                ->format(
                                    'd M Y, h:i A'
                                )
                        }}

                    </strong>

                </div>

            @endif

        </td>

    </tr>

</table>



<table class="summary-box">

    <tr>

        <td class="summary-label">

            Business

        </td>


        <td class="summary-value">

            {{ $application->business_name }}

        </td>

    </tr>


    <tr>

        <td class="summary-label">

            Seller package

        </td>


        <td class="summary-value">

            {{ $application->package_name }}

        </td>

    </tr>


    <tr>

        <td class="summary-label">

            Billing period

        </td>


        <td class="summary-value">

            {{
                ucfirst(
                    $application->billing_period
                )
            }}

        </td>

    </tr>


    <tr>

        <td class="summary-label">

            Product allowance

        </td>


        <td class="summary-value">

            {{
                number_format(
                    $application->product_limit
                )
            }}

            products

        </td>

    </tr>


    <tr>

        <td class="summary-label">

            Currency

        </td>


        <td class="summary-value">

            {{ $invoice->currency }}

        </td>

    </tr>


    <tr class="total-row">

        <td class="total-label">

            Amount paid

        </td>


        <td class="total-value">

            ₦{{
                number_format(
                    (float) $invoice->amount,
                    2
                )
            }}

        </td>

    </tr>

</table>



<div class="payment-box">

    <div class="payment-title">

        ✓ Payment confirmed

    </div>


    <table class="payment-grid">


        @if($invoice->payment_reference)

            <tr>

                <td class="payment-label">

                    Payment reference

                </td>


                <td class="payment-value">

                    {{ $invoice->payment_reference }}

                </td>

            </tr>

        @endif



        @if($invoice->payment_method)

            <tr>

                <td class="payment-label">

                    Payment method

                </td>


                <td class="payment-value">

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


        <tr>

            <td class="payment-label">

                Invoice status

            </td>


            <td class="payment-value">

                PAID

            </td>

        </tr>

    </table>

</div>



<div class="note-box">

    This invoice confirms that MidPoint received payment
    for the seller package shown above.

    The corresponding Verified Seller subscription becomes
    active only after MidPoint verifies the payment with
    Paystack.

    Keep this PDF for your records.

</div>



<div class="footer">

    <table>

        <tr>

            <td>

                MidPoint Verified Seller Program

            </td>


            <td class="right">

                {{ $invoice->invoice_number }}

                · Generated

                {{ now()->format('d M Y') }}

            </td>

        </tr>

    </table>

</div>


</body>

</html>