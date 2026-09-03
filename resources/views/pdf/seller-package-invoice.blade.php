@php
    $packageName = $invoice->effective_package_name;
    $packageFullPrice = (float) $invoice->effective_package_price;
    $prorationCredit = (float) $invoice->proration_credit;
    $billingPeriod = (string) $invoice->effective_billing_period;
    $productLimit = (int) $invoice->effective_product_limit;

    $invoiceTitle = match ($invoice->purchase_type) {
        \App\Models\SellerInvoice::TYPE_RENEWAL =>
            'Seller Package Renewal Invoice',
        \App\Models\SellerInvoice::TYPE_UPGRADE =>
            'Seller Package Upgrade Invoice',
        \App\Models\SellerInvoice::TYPE_DOWNGRADE =>
            'Seller Package Change Invoice',
        default => 'Seller Package Payment Invoice',
    };

    $configuredLogoPath = trim(
        (string) config('midpoint.logo_path', '')
    );

    $relativeLogoPath = ltrim(
        str_replace('\\', '/', $configuredLogoPath),
        '/'
    );

    $absoluteLogoPath = $relativeLogoPath !== ''
        ? public_path($relativeLogoPath)
        : null;

    $pdfLogoSource = null;

    if (
        $absoluteLogoPath
        && is_file($absoluteLogoPath)
        && is_readable($absoluteLogoPath)
    ) {
        $extension = strtolower(
            pathinfo($absoluteLogoPath, PATHINFO_EXTENSION)
        );

        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        $logoBytes = file_get_contents($absoluteLogoPath);

        if ($logoBytes !== false) {
            $pdfLogoSource =
                'data:'
                .$mimeType
                .';base64,'
                .base64_encode($logoBytes);
        }
    }
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 32px 40px 46px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #18211D;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 70%;
        }

        .brand-logo {
            display: block;
            max-width: 165px;
            max-height: 48px;
        }

        .brand-fallback {
            color: #0B3D2E;
            font-size: 23px;
            font-weight: bold;
            letter-spacing: -.4px;
        }

        .brand-fallback span {
            color: #0F8A61;
        }

        .brand-subtitle {
            margin-top: 4px;
            color: #66736D;
            font-size: 8.5px;
            letter-spacing: .25px;
        }

        .status-cell {
            width: 30%;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #9DD9C2;
            background-color: #F1FAF6;
            color: #0B6B4F;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .8px;
        }

        .brand-rule {
            margin-top: 14px;
            border-top: 3px solid #0F8A61;
        }

        .document-header {
            margin-top: 19px;
        }

        .document-header td {
            vertical-align: bottom;
        }

        .document-title {
            margin: 0;
            color: #111815;
            font-size: 18px;
            line-height: 1.25;
        }

        .document-subtitle {
            margin-top: 5px;
            color: #6A756F;
            font-size: 9px;
        }

        .invoice-number-box {
            text-align: right;
        }

        .eyebrow {
            color: #78827D;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .invoice-number {
            margin-top: 4px;
            color: #18211D;
            font-size: 12px;
            font-weight: bold;
        }

        .party-table {
            margin-top: 20px;
            border: 1px solid #DDE3E0;
        }

        .party-table td {
            width: 50%;
            padding: 12px 14px;
            vertical-align: top;
        }

        .party-table td + td {
            border-left: 1px solid #DDE3E0;
        }

        .section-label {
            margin-bottom: 7px;
            color: #0B3D2E;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .primary-value {
            color: #18211D;
            font-size: 10.5px;
            font-weight: bold;
        }

        .muted {
            margin-top: 2px;
            color: #68746E;
        }

        .meta-row {
            margin-top: 3px;
        }

        .meta-label {
            color: #7A8580;
        }

        .meta-value {
            color: #28332E;
            font-weight: bold;
        }

        .summary-heading {
            margin: 20px 0 7px;
            color: #18211D;
            font-size: 11px;
            font-weight: bold;
        }

        .summary-table {
            border: 1px solid #DDE3E0;
        }

        .summary-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #E5EAE7;
        }

        .summary-table tr:last-child td {
            border-bottom: 0;
        }

        .summary-label {
            width: 58%;
            color: #66716B;
        }

        .summary-value {
            width: 42%;
            color: #202A25;
            font-weight: bold;
            text-align: right;
        }

        .credit-row td {
            color: #0B6B4F;
        }

        .total-row td {
            padding-top: 11px;
            padding-bottom: 11px;
            border-top: 2px solid #0F8A61;
            background-color: #F4F9F6;
            color: #0B3D2E;
            font-weight: bold;
        }

        .total-row .summary-label {
            font-size: 11px;
        }

        .total-row .summary-value {
            font-size: 16px;
        }

        .payment-box {
            margin-top: 16px;
            padding: 11px 13px;
            border-left: 4px solid #0F8A61;
            background-color: #F5F8F6;
        }

        .payment-title {
            color: #0B6B4F;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .35px;
        }

        .payment-table {
            margin-top: 7px;
        }

        .payment-table td {
            padding: 2px 0;
        }

        .payment-label {
            color: #6A756F;
        }

        .payment-value {
            color: #202A25;
            font-weight: bold;
            text-align: right;
        }

        .note-box {
            margin-top: 15px;
            padding: 10px 12px;
            border: 1px solid #E2E7E4;
            color: #626D67;
            font-size: 8.8px;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -28px;
            left: 0;
            padding-top: 7px;
            border-top: 1px solid #DDE3E0;
            color: #7A8580;
            font-size: 8px;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($pdfLogoSource)
                    <img
                        src="{{ $pdfLogoSource }}"
                        alt="Midpoint"
                        class="brand-logo"
                    >
                @else
                    <div class="brand-fallback">
                        Mid<span>Point</span>
                    </div>
                @endif

                <div class="brand-subtitle">
                    VERIFIED SELLER PROGRAM
                </div>
            </td>

            <td class="status-cell">
                <span class="status-badge">PAID</span>
            </td>
        </tr>
    </table>

    <div class="brand-rule"></div>

    <table class="document-header">
        <tr>
            <td width="68%">
                <h1 class="document-title">
                    {{ $invoiceTitle }}
                </h1>

                <div class="document-subtitle">
                    Official payment confirmation for
                    {{ $application->business_name }}
                </div>
            </td>

            <td width="32%" class="invoice-number-box">
                <div class="eyebrow">Invoice number</div>

                <div class="invoice-number">
                    {{ $invoice->invoice_number }}
                </div>
            </td>
        </tr>
    </table>

    <table class="party-table">
        <tr>
            <td>
                <div class="section-label">Billed to</div>

                <div class="primary-value">{{ $user->name }}</div>
                <div class="muted">{{ $user->email }}</div>

                @if(!empty($application->phone))
                    <div class="muted">{{ $application->phone }}</div>
                @endif
            </td>

            <td>
                <div class="section-label">Invoice details</div>

                @if($invoice->issued_at)
                    <div class="meta-row">
                        <span class="meta-label">Issued:</span>
                        <span class="meta-value">
                            {{ $invoice->issued_at->format('d M Y') }}
                        </span>
                    </div>
                @endif

                @if($invoice->paid_at)
                    <div class="meta-row">
                        <span class="meta-label">Paid:</span>
                        <span class="meta-value">
                            {{ $invoice->paid_at->format('d M Y, h:i A') }}
                        </span>
                    </div>
                @endif

                <div class="meta-row">
                    <span class="meta-label">Currency:</span>
                    <span class="meta-value">{{ $invoice->currency }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="summary-heading">Package summary</div>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Business</td>
            <td class="summary-value">
                {{ $application->business_name }}
            </td>
        </tr>

        <tr>
            <td class="summary-label">Seller package</td>
            <td class="summary-value">{{ $packageName }}</td>
        </tr>

        <tr>
            <td class="summary-label">Billing period</td>
            <td class="summary-value">{{ ucfirst($billingPeriod) }}</td>
        </tr>

        <tr>
            <td class="summary-label">Product allowance</td>
            <td class="summary-value">
                {{ number_format($productLimit) }} products
            </td>
        </tr>

        <tr>
            <td class="summary-label">Purchase type</td>
            <td class="summary-value">
                {{ $invoice->purchase_type_label }}
            </td>
        </tr>

        @if($prorationCredit > 0)
            <tr>
                <td class="summary-label">Full package price</td>
                <td class="summary-value">
                    ₦{{ number_format($packageFullPrice, 2) }}
                </td>
            </tr>

            <tr class="credit-row">
                <td class="summary-label">Unused current-plan credit</td>
                <td class="summary-value">
                    -₦{{ number_format($prorationCredit, 2) }}
                </td>
            </tr>
        @endif

        <tr class="total-row">
            <td class="summary-label">Amount paid</td>
            <td class="summary-value">
                ₦{{ number_format((float) $invoice->amount, 2) }}
            </td>
        </tr>
    </table>

    <div class="payment-box">
        <div class="payment-title">PAYMENT VERIFIED</div>

        <table class="payment-table">
            @if($invoice->payment_reference)
                <tr>
                    <td class="payment-label">Payment reference</td>
                    <td class="payment-value">
                        {{ $invoice->payment_reference }}
                    </td>
                </tr>
            @endif

            @if($invoice->payment_method)
                <tr>
                    <td class="payment-label">Payment method</td>
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
                <td class="payment-label">Invoice status</td>
                <td class="payment-value">PAID</td>
            </tr>
        </table>
    </div>

    <div class="note-box">
        This invoice confirms that Midpoint received payment for the seller
        package shown above. The corresponding Verified Seller subscription
        became active after payment verification. Keep this PDF for your
        records.
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>Midpoint - Verified Seller Program</td>
                <td class="right">
                    {{ $invoice->invoice_number }} - Generated
                    {{ now()->format('d M Y') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
