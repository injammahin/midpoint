@php
    $paidAmount = (float) (
        $transaction->paid_amount
        ?: $transaction->total_amount
    );

    $buyerName = trim(
        (string) ($transaction->buyer?->name ?? '')
    ) ?: 'Midpoint Buyer';

    $sellerName = trim(
        (string) ($transaction->seller?->name ?? '')
    ) ?: 'Midpoint Seller';

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

        .invoice-heading {
            margin-top: 19px;
        }

        .invoice-heading td {
            vertical-align: bottom;
        }

        .invoice-title {
            margin: 0;
            color: #111815;
            font-size: 18px;
            line-height: 1.25;
        }

        .invoice-reference {
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

        .info-table {
            margin-top: 20px;
            border: 1px solid #DDE3E0;
        }

        .info-table td {
            width: 50%;
            padding: 12px 14px;
            vertical-align: top;
        }

        .info-table td + td {
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

        .items-heading {
            margin: 20px 0 7px;
            color: #18211D;
            font-size: 11px;
            font-weight: bold;
        }

        .items-table {
            border: 1px solid #DDE3E0;
        }

        .items-table th {
            padding: 8px 9px;
            background-color: #F3F6F4;
            color: #46524C;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: .35px;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 9px;
            border-top: 1px solid #E3E8E5;
            vertical-align: top;
        }

        .items-table .qty {
            width: 55px;
            text-align: center;
        }

        .items-table .money {
            width: 105px;
            text-align: right;
        }

        .item-name {
            color: #202A25;
            font-weight: bold;
        }

        .item-description {
            margin-top: 3px;
            color: #6E7973;
            font-size: 8.5px;
        }

        .totals-layout {
            margin-top: 14px;
        }

        .payment-note-cell {
            width: 53%;
            padding: 4px 22px 0 0;
            color: #69746E;
            font-size: 8.8px;
            vertical-align: top;
        }

        .totals-cell {
            width: 47%;
            vertical-align: top;
        }

        .totals-table td {
            padding: 4px 0;
        }

        .totals-label {
            color: #66716B;
        }

        .totals-value {
            color: #202A25;
            font-weight: bold;
            text-align: right;
        }

        .grand-total td {
            padding-top: 9px;
            border-top: 2px solid #0F8A61;
            color: #0B3D2E;
            font-size: 13px;
            font-weight: bold;
        }

        .payment-status {
            margin-top: 17px;
            padding: 10px 12px;
            border-left: 4px solid #0F8A61;
            background-color: #F5F8F6;
            color: #0B6B4F;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: .35px;
        }

        .details-table {
            margin-top: 16px;
            border: 1px solid #DDE3E0;
        }

        .details-table td {
            width: 50%;
            padding: 11px 13px;
            vertical-align: top;
        }

        .details-table td + td {
            border-left: 1px solid #DDE3E0;
        }

        .detail-label {
            color: #7A8580;
            font-size: 8.5px;
        }

        .detail-value {
            margin-top: 2px;
            color: #28332E;
            font-weight: bold;
        }

        .detail-spacer {
            margin-top: 7px;
        }

        .note-box {
            margin-top: 14px;
            padding: 9px 11px;
            border: 1px solid #E2E7E4;
            color: #626D67;
            font-size: 8.5px;
            line-height: 1.45;
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
                    SECURE TRANSACTION PLATFORM
                </div>
            </td>

            <td class="status-cell">
                <span class="status-badge">PAID</span>
            </td>
        </tr>
    </table>

    <div class="brand-rule"></div>

    <table class="invoice-heading">
        <tr>
            <td width="68%">
                <h1 class="invoice-title">Payment Invoice</h1>

                <div class="invoice-reference">
                    Transaction {{ $transaction->reference }}
                </div>
            </td>

            <td width="32%" class="invoice-number-box">
                <div class="eyebrow">Invoice number</div>

                <div class="invoice-number">
                    {{ $transaction->invoice_number }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <div class="section-label">Billed to</div>

                <div class="primary-value">{{ $buyerName }}</div>
                <div class="muted">{{ $transaction->buyer_email }}</div>

                @if($transaction->buyer_phone)
                    <div class="muted">{{ $transaction->buyer_phone }}</div>
                @endif
            </td>

            <td>
                <div class="section-label">Payment details</div>

                <div class="meta-row">
                    <span class="meta-label">Invoice date:</span>
                    <span class="meta-value">
                        {{ optional($transaction->paid_at)->format('d M Y') }}
                    </span>
                </div>

                <div class="meta-row">
                    <span class="meta-label">Payment status:</span>
                    <span class="meta-value">PAID</span>
                </div>

                <div class="meta-row">
                    <span class="meta-label">Currency:</span>
                    <span class="meta-value">
                        {{ $transaction->currency ?: 'NGN' }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="items-heading">Invoice items</div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="qty">Qty</th>
                <th class="money">Unit price</th>
                <th class="money">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <div class="item-name">{{ $transaction->title }}</div>

                    @if($transaction->description)
                        <div class="item-description">
                            {{
                                \Illuminate\Support\Str::limit(
                                    strip_tags($transaction->description),
                                    160
                                )
                            }}
                        </div>
                    @endif
                </td>

                <td class="qty">{{ $transaction->quantity }}</td>

                <td class="money">
                    ₦{{
                        number_format(
                            (float) $transaction->unit_price,
                            2
                        )
                    }}
                </td>

                <td class="money">
                    ₦{{
                        number_format(
                            (float) $transaction->subtotal,
                            2
                        )
                    }}
                </td>
            </tr>

            @if((float) $transaction->delivery_fee > 0)
                <tr>
                    <td>
                        <div class="item-name">Delivery</div>

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

                    <td class="qty">1</td>

                    <td class="money">
                        ₦{{
                            number_format(
                                (float) $transaction->delivery_fee,
                                2
                            )
                        }}
                    </td>

                    <td class="money">
                        ₦{{
                            number_format(
                                (float) $transaction->delivery_fee,
                                2
                            )
                        }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals-layout">
        <tr>
            <td class="payment-note-cell">
                Payment was processed securely through Midpoint using
                Paystack. This invoice confirms the buyer's payment only;
                seller payout is handled separately under the Midpoint
                transaction-protection process.
            </td>

            <td class="totals-cell">
                <table class="totals-table">
                    <tr>
                        <td class="totals-label">Product subtotal</td>
                        <td class="totals-value">
                            ₦{{
                                number_format(
                                    (float) $transaction->subtotal,
                                    2
                                )
                            }}
                        </td>
                    </tr>

                    @if((float) $transaction->delivery_fee > 0)
                        <tr>
                            <td class="totals-label">Delivery</td>
                            <td class="totals-value">
                                ₦{{
                                    number_format(
                                        (float) $transaction->delivery_fee,
                                        2
                                    )
                                }}
                            </td>
                        </tr>
                    @endif

                    <tr class="grand-total">
                        <td>Total paid</td>
                        <td class="right">
                            ₦{{ number_format($paidAmount, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="payment-status">
        PAYMENT VERIFIED AND SECURED BY MIDPOINT
    </div>

    <table class="details-table">
        <tr>
            <td>
                <div class="section-label">Seller</div>
                <div class="detail-label">Seller name</div>
                <div class="detail-value">{{ $sellerName }}</div>
            </td>

            <td>
                <div class="section-label">Processor reference</div>
                <div class="detail-label">Paystack reference</div>
                <div class="detail-value">
                    {{ $transaction->paystack_reference }}
                </div>

                <div class="detail-label detail-spacer">Payment date</div>
                <div class="detail-value">
                    {{
                        optional($transaction->paid_at)
                            ->format('d M Y, h:i A')
                    }}
                </div>
            </td>
        </tr>
    </table>

    <div class="note-box">
        <strong>Important:</strong> This automatically generated invoice is
        a buyer payment receipt. It does not confirm that seller funds have
        been released. The transaction remains protected until the applicable
        acceptance, inspection, dispute, or automatic-release process is
        complete.
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>Midpoint - Secure Payment Invoice</td>
                <td class="right">{{ $transaction->invoice_number }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
