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

    line-height: 1.45;
}

table {
    border-collapse: collapse;
}

.logo-table {
    width: 100%;
}

.logo-mark {
    width: 34px;
    height: 34px;

    background: #0B7A50;

    color: #FFFFFF;

    font-size: 18px;
    font-weight: bold;

    text-align: center;

    vertical-align: middle;
}

.brand {
    padding-left: 9px;

    color: #0B3D2E;

    font-size: 21px;
    font-weight: bold;
}

.brand-purple {
    color: #7557FF;
}

.brand-subtitle {
    padding-left: 9px;

    color: #748079;

    font-size: 10px;
}

.top-line {
    margin-top: 18px;

    border-top: 1px solid #D8DFDB;
}

.info-table {
    width: 100%;

    margin-top: 16px;
}

.info-table td {
    vertical-align: top;
}

.bill-title {
    margin-bottom: 4px;

    color: #17251F;

    font-size: 11px;
    font-weight: bold;
}

.muted {
    color: #68756E;
}

.invoice-meta {
    text-align: right;
}

.invoice-meta table {
    margin-left: auto;
}

.invoice-meta td {
    padding: 2px 0 2px 14px;
}

.invoice-meta-label {
    color: #748079;
}

.invoice-meta-value {
    color: #17251F;

    font-weight: bold;
}

.invoice-title {
    margin-top: 25px;

    text-align: center;

    color: #101915;

    font-size: 19px;
    font-weight: bold;
}

.invoice-reference {
    margin-top: 4px;

    text-align: center;

    color: #748079;

    font-size: 10px;
}

.items-table {
    width: 100%;

    margin-top: 22px;
}

.items-table th {
    padding: 8px 7px;

    border-top: 1px solid #BBC7C0;
    border-bottom: 1px solid #BBC7C0;

    color: #26332C;

    font-size: 10px;
    font-weight: bold;

    text-align: left;
}

.items-table td {
    padding: 9px 7px;

    border-bottom: 1px solid #E3E8E5;

    vertical-align: top;
}

.items-table .qty {
    width: 60px;

    text-align: center;
}

.items-table .price {
    width: 115px;

    text-align: right;
}

.items-table .subtotal {
    width: 120px;

    text-align: right;
}

.item-name {
    font-weight: bold;
}

.item-description {
    margin-top: 3px;

    color: #748079;

    font-size: 10px;
}

.total-layout {
    width: 100%;

    margin-top: 17px;
}

.total-note {
    width: 52%;

    vertical-align: top;

    color: #7A8780;

    font-size: 10px;
}

.totals-cell {
    width: 48%;

    vertical-align: top;
}

.totals-table {
    width: 100%;
}

.totals-table td {
    padding: 4px 0;
}

.totals-label {
    color: #65726B;
}

.totals-amount {
    text-align: right;

    color: #17251F;

    font-weight: bold;
}

.total-separator td {
    padding-top: 8px;

    border-top: 1px solid #BCC7C1;
}

.grand-total-label {
    padding-top: 8px !important;

    color: #101915;

    font-size: 12px;
    font-weight: bold;
}

.grand-total {
    padding-top: 8px !important;

    color: #0B3D2E;

    font-size: 15px;
    font-weight: bold;

    text-align: right;
}

.payment-status {
    margin-top: 22px;

    padding: 11px 13px;

    border: 1px solid #ABEFC6;

    background: #ECFDF3;

    color: #067647;

    font-size: 11px;
    font-weight: bold;
}

.payment-details {
    width: 100%;

    margin-top: 22px;
}

.payment-details td {
    vertical-align: top;
}

.detail-block {
    padding-right: 15px;
}

.detail-title {
    margin-bottom: 5px;

    color: #17251F;

    font-size: 11px;
    font-weight: bold;
}

.detail-label {
    color: #7B8781;

    font-size: 10px;
}

.detail-value {
    margin-top: 2px;

    color: #26332C;

    font-size: 10px;
    font-weight: bold;
}

.note-box {
    margin-top: 21px;

    padding: 11px 13px;

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

    border-top: 1px solid #D9E0DC;

    padding-top: 9px;

    color: #849189;

    font-size: 10px;
}

.footer-table {
    width: 100%;
}

.footer-right {
    text-align: right;
}

</style>

</head>


<body>


<table
    class="logo-table"
    cellspacing="0"
    cellpadding="0"
>

<tr>

<td
    width="34"
    class="logo-mark"
>
    M
</td>


<td>

    <div class="brand">

        Mid<span class="brand-purple">Point</span>

    </div>

    <div class="brand-subtitle">

        Secure transaction platform

    </div>

</td>

</tr>

</table>


<div class="top-line"></div>



<table
    class="info-table"
    cellspacing="0"
    cellpadding="0"
>

<tr>

<td width="55%">

    <div class="bill-title">
        Bill To:
    </div>


    <div>

        <strong>

            {{
                $transaction->buyer?->name
                ?:
                'Midpoint Buyer'
            }}

        </strong>

    </div>


    <div class="muted">

        {{ $transaction->buyer_email }}

    </div>


    @if($transaction->buyer_phone)

        <div class="muted">

            {{ $transaction->buyer_phone }}

        </div>

    @endif

</td>



<td
    width="45%"
    class="invoice-meta"
>

    <table
        cellspacing="0"
        cellpadding="0"
    >

        <tr>

            <td class="invoice-meta-label">
                Invoice Date:
            </td>

            <td class="invoice-meta-value">

                {{
                    optional(
                        $transaction->paid_at
                    )->format('d/m/Y')
                }}

            </td>

        </tr>


        <tr>

            <td class="invoice-meta-label">
                Payment Status:
            </td>

            <td class="invoice-meta-value">

                Paid

            </td>

        </tr>


        <tr>

            <td class="invoice-meta-label">
                Currency:
            </td>

            <td class="invoice-meta-value">

                {{ $transaction->currency ?: 'NGN' }}

            </td>

        </tr>

    </table>

</td>

</tr>

</table>



<div class="invoice-title">

    Invoice # {{ $transaction->invoice_number }}

</div>


<div class="invoice-reference">

    Transaction {{ $transaction->reference }}

</div>



<table
    class="items-table"
    cellspacing="0"
    cellpadding="0"
>

<thead>

<tr>

    <th>
        Description
    </th>

    <th class="qty">
        Qty
    </th>

    <th class="price">
        Unit Price
    </th>

    <th class="subtotal">
        Subtotal
    </th>

</tr>

</thead>


<tbody>


<tr>

<td>

    <div class="item-name">

        {{ $transaction->title }}

    </div>


    @if($transaction->description)

        <div class="item-description">

            {{
                \Illuminate\Support\Str::limit(
                    strip_tags(
                        $transaction->description
                    ),
                    160
                )
            }}

        </div>

    @endif

</td>


<td class="qty">

    {{ $transaction->quantity }}

</td>


<td class="price">

    ₦{{ number_format(
        (float) $transaction->unit_price,
        2
    ) }}

</td>


<td class="subtotal">

    ₦{{ number_format(
        (float) $transaction->subtotal,
        2
    ) }}

</td>

</tr>



@if((float) $transaction->delivery_fee > 0)

<tr>

<td>

    <div class="item-name">

        Delivery

    </div>


    @if($transaction->delivery_note)

        <div class="item-description">

            {{
                \Illuminate\Support\Str::limit(
                    $transaction->delivery_note,
                    120
                )
            }}

        </div>

    @endif

</td>


<td class="qty">
    1
</td>


<td class="price">

    ₦{{ number_format(
        (float) $transaction->delivery_fee,
        2
    ) }}

</td>


<td class="subtotal">

    ₦{{ number_format(
        (float) $transaction->delivery_fee,
        2
    ) }}

</td>

</tr>

@endif


</tbody>

</table>



<table
    class="total-layout"
    cellspacing="0"
    cellpadding="0"
>

<tr>

<td class="total-note">

    Payment was processed securely through Midpoint using Paystack.

    <br><br>

    This invoice confirms the buyer's payment only.
    Seller payout is handled separately according to the
    Midpoint transaction protection process.

</td>


<td class="totals-cell">

    <table
        class="totals-table"
        cellspacing="0"
        cellpadding="0"
    >

        <tr>

            <td class="totals-label">

                Product Subtotal:

            </td>

            <td class="totals-amount">

                ₦{{ number_format(
                    (float) $transaction->subtotal,
                    2
                ) }}

            </td>

        </tr>


        @if((float) $transaction->delivery_fee > 0)

            <tr>

                <td class="totals-label">

                    Delivery:

                </td>

                <td class="totals-amount">

                    ₦{{ number_format(
                        (float) $transaction->delivery_fee,
                        2
                    ) }}

                </td>

            </tr>

        @endif


        <tr class="total-separator">

            <td class="grand-total-label">

                Total Paid:

            </td>

            <td class="grand-total">

                ₦{{ number_format(
                    (float) (
                        $transaction->paid_amount
                        ?:
                        $transaction->total_amount
                    ),
                    2
                ) }}

            </td>

        </tr>

    </table>

</td>

</tr>

</table>



<div class="payment-status">

    ✓ PAYMENT VERIFIED AND SECURED BY Midpoint

</div>



<table
    class="payment-details"
    cellspacing="0"
    cellpadding="0"
>

<tr>

<td
    width="50%"
    class="detail-block"
>

    <div class="detail-title">

        Seller

    </div>


    <div class="detail-label">

        Seller name

    </div>


    <div class="detail-value">

        {{
            $transaction->seller?->name
            ?:
            'Midpoint Seller'
        }}

    </div>

</td>



<td
    width="50%"
    class="detail-block"
>

    <div class="detail-title">

        Payment Details

    </div>


    <div class="detail-label">

        Paystack Reference

    </div>


    <div class="detail-value">

        {{ $transaction->paystack_reference }}

    </div>


    <div
        class="detail-label"
        style="margin-top:7px;"
    >

        Payment Date

    </div>


    <div class="detail-value">

        {{
            optional(
                $transaction->paid_at
            )->format(
                'd M Y, h:i A'
            )
        }}

    </div>

</td>

</tr>

</table>



<div class="note-box">

    <strong>Important:</strong>

    This invoice is automatically generated by Midpoint after successful
    payment verification. It is a payment receipt for the buyer and does
    not confirm that seller funds have been released. The transaction
    remains protected until the applicable acceptance, inspection,
    dispute, or automatic release process is completed.

</div>



<div class="footer">

<table
    class="footer-table"
    cellspacing="0"
    cellpadding="0"
>

<tr>

<td>

    Midpoint · Secure Payment Invoice

</td>


<td class="footer-right">

    {{ $transaction->invoice_number }}

</td>

</tr>

</table>

</div>


</body>

</html>