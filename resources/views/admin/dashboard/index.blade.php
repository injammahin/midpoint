@extends('admin.layouts.app')


@section('title', 'Dashboard')


@section('page-title', 'Dashboard')


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Local Formatting Helpers
    |--------------------------------------------------------------------------
    */

    $moneyFull =
        function ($amount, $decimals = 0) {

            return '₦'
                .
                number_format(
                    (float) $amount,
                    $decimals
                );
        };


    $moneyCompact =
        function ($amount) {

            $amount =
                (float) $amount;


            if (abs($amount) >= 1000000000) {

                return '₦'
                    .
                    number_format(
                        $amount / 1000000000,
                        1
                    )
                    .
                    'B';
            }


            if (abs($amount) >= 1000000) {

                return '₦'
                    .
                    number_format(
                        $amount / 1000000,
                        1
                    )
                    .
                    'M';
            }


            if (abs($amount) >= 1000) {

                return '₦'
                    .
                    number_format(
                        $amount / 1000,
                        1
                    )
                    .
                    'K';
            }


            return '₦'
                .
                number_format(
                    $amount,
                    0
                );
        };


    $buyerGrowthPositive =
        ($money['buyer_paid_growth'] ?? 0) >= 0;


    $profitGrowthPositive =
        ($money['profit_growth'] ?? 0) >= 0;


    $dashboardChartData = [
        'chart' => $chart,
        'transactionStatus' => $transactionStatusChart,
        'profitComposition' => $profitCompositionChart,
        'packageChart' => $packageChart,
        'profitMargin' => $money['gross_profit_margin'],
        'grossProfit' => $money['gross_platform_profit'],
    ];

@endphp


<div class="mp-admin-dashboard">


    {{-- =========================================================
        HERO / PAGE INTRO
    ========================================================== --}}

    <section class="admin-card mp-dashboard-hero">

        <div class="mp-dashboard-hero-copy">

            <div class="mp-dashboard-kicker">

                <span class="mp-live-dot"></span>

                Live business overview

            </div>


            <h2>
                MidPoint Command Center
            </h2>


            <p>
                Monitor payments, seller payouts, platform revenue,
                subscriptions, disputes and marketplace activity from one place.
            </p>


            <div class="mp-dashboard-hero-meta">

                <span>

                    <i class="fa-regular fa-calendar"></i>

                    {{ now()->format('l, d F Y') }}

                </span>


                <span>

                    <i class="fa-regular fa-clock"></i>

                    Updated {{ now()->format('h:i A') }}

                </span>

            </div>

        </div>


        <form
            method="GET"
            action="{{ route('admin.dashboard') }}"
            class="mp-period-control"
        >

            <label for="dashboardPeriod">
                Chart period
            </label>


            <select
                id="dashboardPeriod"
                name="period"
                onchange="this.form.submit()"
            >

                <option
                    value="6"
                    {{ $period === 6 ? 'selected' : '' }}
                >
                    Last 6 months
                </option>


                <option
                    value="12"
                    {{ $period === 12 ? 'selected' : '' }}
                >
                    Last 12 months
                </option>

            </select>

        </form>

    </section>


    {{-- =========================================================
        PRIMARY FINANCIAL KPIs
    ========================================================== --}}

    <section class="mp-kpi-grid">


        {{-- Gross Platform Profit --}}
        <article
            class="admin-card mp-kpi-card is-profit"
            title="Gross platform revenue = realized transaction service fees + paid seller package revenue. VAT and operating expenses are excluded."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </span>


                <span
                    class="mp-kpi-trend {{ $profitGrowthPositive ? 'up' : 'down' }}"
                >

                    <i
                        class="fa-solid {{ $profitGrowthPositive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"
                    ></i>

                    {{ $profitGrowthPositive ? '+' : '' }}{{ number_format($money['profit_growth'], 1) }}%

                </span>

            </div>


            <span class="mp-kpi-label">
                Gross platform profit
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['gross_platform_profit']) }}
            </strong>


            <small>
                Service fees + seller package revenue
            </small>

        </article>


        {{-- Buyer Payments --}}
        <article
            class="admin-card mp-kpi-card"
            title="Total value of all successfully verified buyer payments across paid secure transactions."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon is-blue">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </span>


                <span
                    class="mp-kpi-trend {{ $buyerGrowthPositive ? 'up' : 'down' }}"
                >

                    <i
                        class="fa-solid {{ $buyerGrowthPositive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"
                    ></i>

                    {{ $buyerGrowthPositive ? '+' : '' }}{{ number_format($money['buyer_paid_growth'], 1) }}%

                </span>

            </div>


            <span class="mp-kpi-label">
                Buyer payments received
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['buyer_paid']) }}
            </strong>


            <small>
                {{ number_format($stats['paid_transactions']) }} verified paid transactions
            </small>

        </article>


        {{-- Seller Released --}}
        <article
            class="admin-card mp-kpi-card"
            title="Net seller funds for transactions whose payout completed successfully. Platform service fee and VAT are excluded from this amount."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon is-purple">
                    <i class="fa-solid fa-building-columns"></i>
                </span>

            </div>


            <span class="mp-kpi-label">
                Released to sellers
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['seller_released']) }}
            </strong>


            <small>
                Successfully completed seller payouts
            </small>

        </article>


        {{-- Escrow --}}
        <article
            class="admin-card mp-kpi-card"
            title="Buyer-paid value still associated with transactions whose seller payout has not completed successfully."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon is-amber">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

            </div>


            <span class="mp-kpi-label">
                Currently protected
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['escrow_protected']) }}
            </strong>


            <small>
                Buyer funds awaiting final settlement
            </small>

        </article>


        {{-- Service Fee --}}
        <article
            class="admin-card mp-kpi-card"
            title="Realized MidPoint service fees from transactions where seller payout completed. VAT is shown separately and is not included as platform profit."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon is-green">
                    <i class="fa-solid fa-percent"></i>
                </span>

            </div>


            <span class="mp-kpi-label">
                Service fee revenue
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['service_fee_revenue']) }}
            </strong>


            <small>
                VAT collected: {{ $moneyFull($money['vat_collected']) }}
            </small>

        </article>


        {{-- Package Revenue --}}
        <article
            class="admin-card mp-kpi-card"
            title="Revenue from seller package invoices whose status is paid."
        >

            <div class="mp-kpi-top">

                <span class="mp-kpi-icon is-violet">
                    <i class="fa-solid fa-crown"></i>
                </span>

            </div>


            <span class="mp-kpi-label">
                Seller package revenue
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['package_revenue']) }}
            </strong>


            <small>
                {{ number_format($stats['package_purchases']) }} purchases · {{ number_format($stats['package_customers']) }} customers
            </small>

        </article>

    </section>


    {{-- =========================================================
        PEOPLE / MARKETPLACE SNAPSHOT
    ========================================================== --}}

    <section class="mp-mini-stats-grid">

        <a
            href="{{ route('admin.users.index') }}"
            class="admin-card mp-mini-stat"
        >

            <span class="mp-mini-stat-icon">
                <i class="fa-solid fa-users"></i>
            </span>


            <span>
                Total users
            </span>


            <strong>
                {{ number_format($stats['users']) }}
            </strong>


            <small>
                {{ number_format($stats['active_users']) }} active
            </small>

        </a>


        <a
            href="{{ route('admin.billing.subscriptions.index') }}"
            class="admin-card mp-mini-stat"
        >

            <span class="mp-mini-stat-icon is-green">
                <i class="fa-solid fa-store"></i>
            </span>


            <span>
                Active sellers
            </span>


            <strong>
                {{ number_format($stats['active_sellers']) }}
            </strong>


            <small>
                Paid active subscriptions
            </small>

        </a>


        <div class="admin-card mp-mini-stat">

            <span class="mp-mini-stat-icon is-purple">
                <i class="fa-solid fa-cart-shopping"></i>
            </span>


            <span>
                Paying buyers
            </span>


            <strong>
                {{ number_format($stats['paid_buyers']) }}
            </strong>


            <small>
                Unique buyers with paid orders
            </small>

        </div>


        <a
            href="{{ route('admin.website-settings.seller-applications.index') }}"
            class="admin-card mp-mini-stat"
        >

            <span class="mp-mini-stat-icon is-amber">
                <i class="fa-solid fa-user-check"></i>
            </span>


            <span>
                Seller applications
            </span>


            <strong>
                {{ number_format($stats['pending_seller_applications']) }}
            </strong>


            <small>
                Waiting for admin review
            </small>

        </a>

    </section>


    {{-- =========================================================
        MAIN MONEY FLOW CHART + PROFIT MARGIN
    ========================================================== --}}

    <section class="mp-dashboard-main-grid">


        {{-- Money Flow --}}
        <article class="admin-card mp-chart-card mp-money-flow-card">

            <div class="mp-card-heading">

                <div>

                    <span class="mp-card-eyebrow">
                        Financial flow
                    </span>


                    <h3>
                        Payments, payouts & platform profit
                    </h3>


                    <p>
                        Hover over any point to see the exact monthly value.
                    </p>

                </div>


                <div class="mp-chart-legend">

                    <span class="is-paid">
                        Buyer paid
                    </span>

                    <span class="is-released">
                        Seller released
                    </span>

                    <span class="is-profit">
                        Platform profit
                    </span>

                </div>

            </div>


            <div
                id="moneyFlowChart"
                class="mp-apex-chart"
            ></div>

        </article>


        {{-- Profit Margin --}}
        <article class="admin-card mp-chart-card mp-margin-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Gross margin
                    </span>


                    <h3>
                        Platform profit margin
                    </h3>

                </div>

            </div>


            <div
                id="profitMarginChart"
                class="mp-margin-chart"
            ></div>


            <div class="mp-margin-breakdown">

                <div>

                    <span>
                        Gross business volume
                    </span>

                    <strong>
                        {{ $moneyFull($money['gross_business_volume']) }}
                    </strong>

                </div>


                <div>

                    <span>
                        Gross platform profit
                    </span>

                    <strong>
                        {{ $moneyFull($money['gross_platform_profit']) }}
                    </strong>

                </div>

            </div>


            <p class="mp-accounting-note">
                Gross platform profit excludes VAT and operating expenses.
                It is not accounting net profit.
            </p>

        </article>

    </section>


    {{-- =========================================================
        DONUTS / BUSINESS COMPOSITION
    ========================================================== --}}

    <section class="mp-dashboard-three-grid">


        {{-- Transaction Status --}}
        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Transaction health
                    </span>


                    <h3>
                        Paid transaction status
                    </h3>

                </div>


                <a href="{{ route('admin.transactions.index') }}">
                    View all
                </a>

            </div>


            <div
                id="transactionStatusChart"
                class="mp-donut-chart"
            ></div>

        </article>


        {{-- Profit Composition --}}
        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Revenue mix
                    </span>


                    <h3>
                        Where platform profit comes from
                    </h3>

                </div>

            </div>


            <div
                id="profitCompositionChart"
                class="mp-donut-chart"
            ></div>


            <div class="mp-inline-values">

                <div>

                    <span class="dot service"></span>

                    Service fees

                    <strong>
                        {{ $moneyFull($money['service_fee_revenue']) }}
                    </strong>

                </div>


                <div>

                    <span class="dot package"></span>

                    Packages

                    <strong>
                        {{ $moneyFull($money['package_revenue']) }}
                    </strong>

                </div>

            </div>

        </article>


        {{-- Package Mix --}}
        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Seller subscriptions
                    </span>


                    <h3>
                        Package purchase mix
                    </h3>

                </div>


                <a href="{{ route('admin.billing.invoices.index') }}">
                    Invoices
                </a>

            </div>


            <div
                id="packageMixChart"
                class="mp-donut-chart"
            ></div>

        </article>

    </section>


    {{-- =========================================================
        TRANSACTION OPERATIONS
    ========================================================== --}}

    <section class="admin-card mp-operations-card">

        <div class="mp-card-heading">

            <div>

                <span class="mp-card-eyebrow">
                    Marketplace operations
                </span>


                <h3>
                    Transaction lifecycle snapshot
                </h3>


                <p>
                    Current workload across MidPoint's secure transaction pipeline.
                </p>

            </div>

        </div>


        <div class="mp-operations-grid">

            <a
                href="{{ route('admin.transactions.index') }}"
                class="mp-operation-item is-running"
                title="Buyer-paid transactions that are still progressing through the transaction lifecycle."
            >

                <span class="mp-operation-icon">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </span>


                <div>

                    <strong>
                        {{ number_format($stats['running_transactions']) }}
                    </strong>

                    <span>
                        Running
                    </span>

                </div>

            </a>


            <a
                href="{{ route('admin.transactions.index', ['status' => 'completed']) }}"
                class="mp-operation-item is-completed"
            >

                <span class="mp-operation-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </span>


                <div>

                    <strong>
                        {{ number_format($stats['completed_transactions']) }}
                    </strong>

                    <span>
                        Completed
                    </span>

                </div>

            </a>


            <a
                href="{{ route('admin.disputes.index') }}"
                class="mp-operation-item is-disputed"
            >

                <span class="mp-operation-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>


                <div>

                    <strong>
                        {{ number_format($stats['disputed_transactions']) }}
                    </strong>

                    <span>
                        Disputed
                    </span>

                </div>

            </a>


            <a
                href="{{ route('admin.transactions.index', ['payout_status' => 'pending']) }}"
                class="mp-operation-item is-payout"
            >

                <span class="mp-operation-icon">
                    <i class="fa-solid fa-building-columns"></i>
                </span>


                <div>

                    <strong>
                        {{ number_format($stats['awaiting_payout_transactions']) }}
                    </strong>

                    <span>
                        Payout attention
                    </span>

                </div>

            </a>


            <div
                class="mp-operation-item is-unpaid"
                title="Seller-generated secure transaction links that have not yet received a verified buyer payment."
            >

                <span class="mp-operation-icon">
                    <i class="fa-solid fa-link"></i>
                </span>


                <div>

                    <strong>
                        {{ number_format($stats['unpaid_generated_links']) }}
                    </strong>

                    <span>
                        Unpaid links
                    </span>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        ATTENTION QUEUE + DISPUTES
    ========================================================== --}}

    <section class="mp-dashboard-two-grid">


        {{-- Attention Queue --}}
        <article class="admin-card mp-list-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Needs attention
                    </span>


                    <h3>
                        Admin action queue
                    </h3>

                </div>

            </div>


            <div class="mp-action-queue">

                <a
                    href="{{ route('admin.disputes.index', ['status' => 'open']) }}"
                    class="mp-queue-row danger"
                >

                    <span class="mp-queue-icon">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </span>


                    <div>

                        <strong>
                            New disputes
                        </strong>

                        <small>
                            Waiting for initial admin review
                        </small>

                    </div>


                    <b>
                        {{ number_format($stats['open_disputes']) }}
                    </b>

                </a>


                <a
                    href="{{ route('admin.website-settings.seller-applications.index') }}"
                    class="mp-queue-row warning"
                >

                    <span class="mp-queue-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </span>


                    <div>

                        <strong>
                            Seller applications
                        </strong>

                        <small>
                            Submitted and waiting for review
                        </small>

                    </div>


                    <b>
                        {{ number_format($stats['pending_seller_applications']) }}
                    </b>

                </a>


                <a
                    href="{{ route('admin.support-inquiries.contacts') }}"
                    class="mp-queue-row info"
                >

                    <span class="mp-queue-icon">
                        <i class="fa-solid fa-headset"></i>
                    </span>


                    <div>

                        <strong>
                            Unread inquiries
                        </strong>

                        <small>
                            Contact messages not yet opened
                        </small>

                    </div>


                    <b>
                        {{ number_format($stats['unread_inquiries']) }}
                    </b>

                </a>


                <a
                    href="{{ route('admin.transactions.index', ['payout_status' => 'pending']) }}"
                    class="mp-queue-row purple"
                >

                    <span class="mp-queue-icon">
                        <i class="fa-solid fa-money-check-dollar"></i>
                    </span>


                    <div>

                        <strong>
                            Payout attention
                        </strong>

                        <small>
                            Pending, initializing or failed payouts
                        </small>

                    </div>


                    <b>
                        {{ number_format($stats['awaiting_payout_transactions']) }}
                    </b>

                </a>

            </div>

        </article>


        {{-- Dispute Workflow --}}
        <article class="admin-card mp-list-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Risk monitoring
                    </span>


                    <h3>
                        Dispute workflow
                    </h3>

                </div>


                <a href="{{ route('admin.disputes.index') }}">
                    Manage
                </a>

            </div>


            <div class="mp-dispute-flow">

                @foreach([
                    [
                        'label' => 'Open',
                        'value' => $stats['open_disputes'],
                        'class' => 'open',
                    ],
                    [
                        'label' => 'Under review',
                        'value' => $stats['under_review_disputes'],
                        'class' => 'review',
                    ],
                    [
                        'label' => 'Awaiting buyer',
                        'value' => $stats['awaiting_buyer_disputes'],
                        'class' => 'buyer',
                    ],
                    [
                        'label' => 'Awaiting seller',
                        'value' => $stats['awaiting_seller_disputes'],
                        'class' => 'seller',
                    ],
                    [
                        'label' => 'Resolved',
                        'value' => $stats['resolved_disputes'],
                        'class' => 'resolved',
                    ],
                ] as $disputeState)

                    <div class="mp-dispute-state">

                        <span class="mp-dispute-dot {{ $disputeState['class'] }}"></span>


                        <span>
                            {{ $disputeState['label'] }}
                        </span>


                        <strong>
                            {{ number_format($disputeState['value']) }}
                        </strong>

                    </div>

                @endforeach

            </div>

        </article>

    </section>


    {{-- =========================================================
        RECENT TRANSACTIONS + TOP SELLERS
    ========================================================== --}}

    <section class="mp-dashboard-bottom-grid">


        {{-- Recent Transactions --}}
        <article class="admin-card mp-table-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Live payments
                    </span>


                    <h3>
                        Recent paid transactions
                    </h3>

                </div>


                <a href="{{ route('admin.transactions.index') }}">
                    View all
                </a>

            </div>


            @if($recentTransactions->isNotEmpty())

                <div class="mp-table-scroll">

                    <table class="mp-dashboard-table">

                        <thead>

                            <tr>

                                <th>
                                    Transaction
                                </th>

                                <th>
                                    Buyer / Seller
                                </th>

                                <th>
                                    Paid
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Time
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($recentTransactions as $transaction)

                                @php

                                    $transactionBadge =
                                        match($transaction->status) {

                                            'completed' =>
                                                'green',

                                            'disputed' =>
                                                'red',

                                            'release_approved',
                                            'payout_pending' =>
                                                'purple',

                                            'delivered',
                                            'inspection' =>
                                                'blue',

                                            default =>
                                                'amber',
                                        };

                                @endphp


                                <tr>

                                    <td>

                                        <a
                                            href="{{ route('admin.transactions.show', $transaction) }}"
                                            class="mp-table-main-link"
                                        >
                                            {{ $transaction->reference }}
                                        </a>


                                        <small>
                                            {{ $transaction->title }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $transaction->buyer?->name ?? 'Buyer' }}
                                        </strong>


                                        <small>
                                            → {{ $transaction->seller?->name ?? 'Seller' }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong class="mp-money-cell">

                                            {{
                                                $moneyFull(
                                                    $transaction->paid_amount
                                                    ?:
                                                    $transaction->total_amount
                                                )
                                            }}

                                        </strong>

                                    </td>


                                    <td>

                                        <span class="mp-status-pill {{ $transactionBadge }}">
                                            {{ $transaction->status_label }}
                                        </span>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $transaction->paid_at?->format('d M') ?? '-' }}
                                        </strong>


                                        <small>
                                            {{ $transaction->paid_at?->format('h:i A') ?? '' }}
                                        </small>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


            @else

                <div class="mp-empty-state">

                    <i class="fa-solid fa-money-bill-transfer"></i>

                    <strong>
                        No paid transactions yet
                    </strong>

                    <span>
                        Verified buyer payments will appear here.
                    </span>

                </div>

            @endif

        </article>


        {{-- Top Sellers --}}
        <article class="admin-card mp-list-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-card-eyebrow">
                        Seller performance
                    </span>


                    <h3>
                        Top sellers
                    </h3>

                </div>

            </div>


            @if($topSellers->isNotEmpty())

                <div class="mp-top-seller-list">

                    @foreach($topSellers as $index => $seller)

                        <div class="mp-top-seller-row">

                            <span class="mp-rank">
                                {{ $index + 1 }}
                            </span>


                            <div class="mp-seller-avatar">

                                {{
                                    strtoupper(
                                        substr(
                                            $seller['name'],
                                            0,
                                            1
                                        )
                                    )
                                }}

                            </div>


                            <div class="mp-top-seller-info">

                                <strong>
                                    {{ $seller['name'] }}
                                </strong>


                                <small>
                                    {{ number_format($seller['transactions']) }} completed payouts
                                </small>

                            </div>


                            <div class="mp-top-seller-money">

                                <strong>
                                    {{ $moneyCompact($seller['gross_volume']) }}
                                </strong>


                                <small>
                                    gross volume
                                </small>

                            </div>

                        </div>

                    @endforeach

                </div>


            @else

                <div class="mp-empty-state compact">

                    <i class="fa-solid fa-store"></i>

                    <strong>
                        No seller payout data yet
                    </strong>

                </div>

            @endif

        </article>

    </section>


    {{-- =========================================================
        RECENT PACKAGE PAYMENTS
    ========================================================== --}}

    <section class="admin-card mp-package-payment-card">

        <div class="mp-card-heading compact">

            <div>

                <span class="mp-card-eyebrow">
                    Subscription income
                </span>


                <h3>
                    Recent seller package payments
                </h3>

            </div>


            <a href="{{ route('admin.billing.invoices.index') }}">
                View invoices
            </a>

        </div>


        @if($recentPackagePurchases->isNotEmpty())

            <div class="mp-package-payment-grid">

                @foreach($recentPackagePurchases as $invoice)

                    <article class="mp-package-payment-item">

                        <div class="mp-package-payment-icon">
                            <i class="fa-solid fa-crown"></i>
                        </div>


                        <div>

                            <strong>
                                {{ $invoice->application?->package_name ?? 'Seller Package' }}
                            </strong>


                            <span>
                                {{ $invoice->user?->name ?? 'Seller' }}
                            </span>


                            <small>
                                {{ $invoice->invoice_number }}
                            </small>

                        </div>


                        <div class="mp-package-payment-value">

                            <strong>
                                {{ $moneyFull($invoice->amount) }}
                            </strong>


                            <small>
                                {{ $invoice->paid_at?->format('d M Y') ?? '-' }}
                            </small>

                        </div>

                    </article>

                @endforeach

            </div>


        @else

            <div class="mp-empty-state compact">

                <i class="fa-solid fa-crown"></i>

                <strong>
                    No paid seller package invoices yet
                </strong>

            </div>

        @endif

    </section>

</div>

@endsection


{{-- =========================================================
    DASHBOARD STYLES
========================================================== --}}

@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Dashboard Base
    |--------------------------------------------------------------------------
    */

    .mp-admin-dashboard {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }


    .mp-admin-dashboard a {
        color: inherit;
    }


    /*
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    */

    .mp-dashboard-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        padding: 26px 28px;
    }


    .mp-dashboard-hero::after {
        content: '';
        position: absolute;
        width: 260px;
        height: 260px;
        top: -150px;
        right: -70px;
        border-radius: 50%;
        background: rgba(25, 168, 149, .08);
        pointer-events: none;
    }


    .mp-dashboard-kicker,
    .mp-card-eyebrow {
        color: var(--admin-accent-strong);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }


    .mp-dashboard-kicker {
        display: flex;
        align-items: center;
        gap: 7px;
    }


    .mp-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #12B76A;
        box-shadow: 0 0 0 5px rgba(18, 183, 106, .10);
    }


    .mp-dashboard-hero h2 {
        margin: 8px 0 6px;
        color: var(--admin-heading);
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 27px;
        line-height: 1.15;
    }


    .mp-dashboard-hero-copy > p {
        max-width: 730px;
        margin: 0;
        color: var(--admin-muted);
        font-size: 13px;
        line-height: 1.7;
    }


    .mp-dashboard-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 14px;
        color: var(--admin-muted);
        font-size: 11px;
    }


    .mp-dashboard-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }


    .mp-period-control {
        position: relative;
        z-index: 2;
        min-width: 170px;
    }


    .mp-period-control label {
        display: block;
        margin-bottom: 6px;
        color: var(--admin-muted);
        font-size: 11px;
        font-weight: 600;
    }


    .mp-period-control select {
        width: 100%;
        min-height: 40px;
        padding: 0 11px;
        border: 1px solid var(--admin-border);
        border-radius: 10px;
        outline: 0;
        background: var(--admin-surface);
        color: var(--admin-text);
        font: inherit;
        font-size: 12px;
        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | KPI Cards
    |--------------------------------------------------------------------------
    */

    .mp-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }


    .mp-kpi-card {
        position: relative;
        min-width: 0;
        padding: 20px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }


    .mp-kpi-card:hover {
        transform: translateY(-2px);
        border-color: rgba(25, 168, 149, .35);
        box-shadow: var(--admin-shadow);
    }


    .mp-kpi-card.is-profit {
        background:
            linear-gradient(
                135deg,
                color-mix(in srgb, var(--admin-surface) 88%, #12B76A 12%),
                var(--admin-surface)
            );
    }


    .mp-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
    }


    .mp-kpi-icon,
    .mp-mini-stat-icon {
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: rgba(18, 183, 106, .10);
        color: #079455;
    }


    .mp-kpi-icon {
        width: 39px;
        height: 39px;
        font-size: 15px;
    }


    .mp-kpi-icon.is-blue,
    .mp-mini-stat-icon.is-blue {
        background: #EEF4FF;
        color: #3538CD;
    }


    .mp-kpi-icon.is-purple,
    .mp-mini-stat-icon.is-purple {
        background: #F2F0FF;
        color: #6941C6;
    }


    .mp-kpi-icon.is-amber,
    .mp-mini-stat-icon.is-amber {
        background: #FFF7E8;
        color: #B54708;
    }


    .mp-kpi-icon.is-green,
    .mp-mini-stat-icon.is-green {
        background: #ECFDF3;
        color: #067647;
    }


    .mp-kpi-icon.is-violet {
        background: #F4F3FF;
        color: #7A5AF8;
    }


    .mp-kpi-trend {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }


    .mp-kpi-trend.up {
        background: #ECFDF3;
        color: #067647;
    }


    .mp-kpi-trend.down {
        background: #FFF1F2;
        color: #B42318;
    }


    .mp-kpi-label {
        display: block;
        color: var(--admin-muted);
        font-size: 12px;
        font-weight: 600;
    }


    .mp-kpi-value {
        display: block;
        margin-top: 6px;
        color: var(--admin-heading);
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: clamp(23px, 2.2vw, 31px);
        line-height: 1.15;
        white-space: nowrap;
    }


    .mp-kpi-card > small {
        display: block;
        margin-top: 8px;
        color: var(--admin-muted);
        font-size: 11px;
        line-height: 1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | Mini Stats
    |--------------------------------------------------------------------------
    */

    .mp-mini-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }


    .mp-mini-stat {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        grid-template-areas:
            "icon label value"
            "icon small value";
        align-items: center;
        column-gap: 11px;
        padding: 15px;
        text-decoration: none;
        transition: transform .15s ease, border-color .15s ease;
    }


    .mp-mini-stat:hover {
        transform: translateY(-1px);
        border-color: rgba(25, 168, 149, .35);
    }


    .mp-mini-stat-icon {
        grid-area: icon;
        width: 36px;
        height: 36px;
        font-size: 13px;
    }


    .mp-mini-stat > span:not(.mp-mini-stat-icon) {
        grid-area: label;
        color: var(--admin-muted);
        font-size: 11px;
        font-weight: 600;
    }


    .mp-mini-stat > strong {
        grid-area: value;
        color: var(--admin-heading);
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 21px;
    }


    .mp-mini-stat > small {
        grid-area: small;
        color: var(--admin-muted-2);
        font-size: 10px;
    }


    /*
    |--------------------------------------------------------------------------
    | Shared Chart Cards
    |--------------------------------------------------------------------------
    */

    .mp-dashboard-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.75fr) minmax(300px, .65fr);
        gap: 14px;
        align-items: stretch;
    }


    .mp-dashboard-three-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }


    .mp-chart-card,
    .mp-list-card,
    .mp-table-card,
    .mp-operations-card,
    .mp-package-payment-card {
        padding: 20px;
        min-width: 0;
    }


    .mp-card-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }


    .mp-card-heading.compact {
        margin-bottom: 10px;
    }


    .mp-card-heading h3 {
        margin: 4px 0 0;
        color: var(--admin-heading);
        font-size: 14px;
        font-weight: 700;
    }


    .mp-card-heading p {
        margin: 5px 0 0;
        color: var(--admin-muted);
        font-size: 11px;
        line-height: 1.6;
    }


    .mp-card-heading > a {
        color: var(--admin-accent-strong);
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }


    .mp-chart-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
        color: var(--admin-muted);
        font-size: 10px;
    }


    .mp-chart-legend span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }


    .mp-chart-legend span::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }


    .mp-chart-legend .is-paid::before {
        background: #18A897;
    }


    .mp-chart-legend .is-released::before {
        background: #7A5AF8;
    }


    .mp-chart-legend .is-profit::before {
        background: #F79009;
    }


    .mp-apex-chart {
        min-height: 345px;
    }


    .mp-donut-chart {
        min-height: 275px;
    }


    /*
    |--------------------------------------------------------------------------
    | Margin Card
    |--------------------------------------------------------------------------
    */

    .mp-margin-chart {
        min-height: 245px;
    }


    .mp-margin-breakdown {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }


    .mp-margin-breakdown > div {
        padding: 11px;
        border: 1px solid var(--admin-border-soft);
        border-radius: 10px;
        background: var(--admin-surface-soft);
    }


    .mp-margin-breakdown span,
    .mp-margin-breakdown strong {
        display: block;
    }


    .mp-margin-breakdown span {
        color: var(--admin-muted);
        font-size: 10px;
    }


    .mp-margin-breakdown strong {
        margin-top: 5px;
        color: var(--admin-heading);
        font-size: 13px;
    }


    .mp-accounting-note {
        margin: 10px 0 0;
        color: var(--admin-muted-2);
        font-size: 10px;
        line-height: 1.5;
        text-align: center;
    }


    /*
    |--------------------------------------------------------------------------
    | Profit Composition Labels
    |--------------------------------------------------------------------------
    */

    .mp-inline-values {
        display: grid;
        gap: 7px;
    }


    .mp-inline-values > div {
        display: grid;
        grid-template-columns: 8px minmax(0, 1fr) auto;
        align-items: center;
        gap: 7px;
        color: var(--admin-muted);
        font-size: 11px;
    }


    .mp-inline-values strong {
        color: var(--admin-heading);
        font-size: 11px;
    }


    .mp-inline-values .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }


    .mp-inline-values .dot.service {
        background: #18A897;
    }


    .mp-inline-values .dot.package {
        background: #7A5AF8;
    }


    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */

    .mp-operations-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }


    .mp-operation-item {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
        padding: 13px;
        border: 1px solid var(--admin-border-soft);
        border-radius: 11px;
        background: var(--admin-surface-soft);
        text-decoration: none;
        transition: transform .15s ease, border-color .15s ease;
    }


    .mp-operation-item:hover {
        transform: translateY(-1px);
        border-color: rgba(25, 168, 149, .35);
    }


    .mp-operation-icon {
        display: grid;
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        place-items: center;
        border-radius: 10px;
        background: var(--admin-surface);
        font-size: 14px;
    }


    .mp-operation-item strong,
    .mp-operation-item span {
        display: block;
    }


    .mp-operation-item strong {
        color: var(--admin-heading);
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 18px;
    }


    .mp-operation-item div > span {
        margin-top: 2px;
        color: var(--admin-muted);
        font-size: 10px;
    }


    .mp-operation-item.is-running .mp-operation-icon {
        color: #3538CD;
    }


    .mp-operation-item.is-completed .mp-operation-icon {
        color: #067647;
    }


    .mp-operation-item.is-disputed .mp-operation-icon {
        color: #B42318;
    }


    .mp-operation-item.is-payout .mp-operation-icon {
        color: #6941C6;
    }


    .mp-operation-item.is-unpaid .mp-operation-icon {
        color: #B54708;
    }


    /*
    |--------------------------------------------------------------------------
    | Two Column Lists
    |--------------------------------------------------------------------------
    */

    .mp-dashboard-two-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }


    .mp-action-queue,
    .mp-dispute-flow {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }


    .mp-queue-row {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 11px;
        border: 1px solid var(--admin-border-soft);
        border-radius: 10px;
        background: var(--admin-surface-soft);
        text-decoration: none;
    }


    .mp-queue-icon {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 9px;
        background: var(--admin-surface);
    }


    .mp-queue-row strong,
    .mp-queue-row small {
        display: block;
    }


    .mp-queue-row strong {
        color: var(--admin-heading);
        font-size: 11px;
    }


    .mp-queue-row small {
        margin-top: 3px;
        color: var(--admin-muted);
        font-size: 10px;
    }


    .mp-queue-row b {
        min-width: 28px;
        padding: 5px 7px;
        border-radius: 999px;
        text-align: center;
        font-size: 11px;
    }


    .mp-queue-row.danger .mp-queue-icon,
    .mp-queue-row.danger b {
        background: #FFF1F2;
        color: #B42318;
    }


    .mp-queue-row.warning .mp-queue-icon,
    .mp-queue-row.warning b {
        background: #FFF7E8;
        color: #B54708;
    }


    .mp-queue-row.info .mp-queue-icon,
    .mp-queue-row.info b {
        background: #EEF4FF;
        color: #3538CD;
    }


    .mp-queue-row.purple .mp-queue-icon,
    .mp-queue-row.purple b {
        background: #F2F0FF;
        color: #6941C6;
    }


    .mp-dispute-state {
        display: grid;
        grid-template-columns: 9px minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        padding: 10px 11px;
        border: 1px solid var(--admin-border-soft);
        border-radius: 10px;
        background: var(--admin-surface-soft);
        color: var(--admin-muted);
        font-size: 11px;
    }


    .mp-dispute-state strong {
        color: var(--admin-heading);
        font-size: 12px;
    }


    .mp-dispute-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--admin-muted-2);
    }


    .mp-dispute-dot.open {
        background: #F04438;
    }


    .mp-dispute-dot.review {
        background: #6172F3;
    }


    .mp-dispute-dot.buyer {
        background: #F79009;
    }


    .mp-dispute-dot.seller {
        background: #7A5AF8;
    }


    .mp-dispute-dot.resolved {
        background: #12B76A;
    }


    /*
    |--------------------------------------------------------------------------
    | Bottom Grid / Table
    |--------------------------------------------------------------------------
    */

    .mp-dashboard-bottom-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(300px, .7fr);
        gap: 14px;
        align-items: start;
    }


    .mp-table-scroll {
        overflow-x: auto;
    }


    .mp-dashboard-table {
        width: 100%;
        min-width: 780px;
        border-collapse: collapse;
    }


    .mp-dashboard-table th {
        padding: 10px 11px;
        border-bottom: 1px solid var(--admin-border);
        color: var(--admin-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-align: left;
        text-transform: uppercase;
    }


    .mp-dashboard-table td {
        padding: 12px 11px;
        border-bottom: 1px solid var(--admin-border-soft);
        color: var(--admin-text);
        font-size: 11px;
        vertical-align: middle;
    }


    .mp-dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }


    .mp-dashboard-table tbody tr:hover {
        background: rgba(25, 168, 149, .025);
    }


    .mp-dashboard-table td strong,
    .mp-dashboard-table td small {
        display: block;
    }


    .mp-dashboard-table td small {
        margin-top: 3px;
        color: var(--admin-muted);
        font-size: 10px;
    }


    .mp-table-main-link {
        color: var(--admin-heading) !important;
        font-weight: 700;
        text-decoration: none;
    }


    .mp-money-cell {
        color: var(--admin-heading);
    }


    .mp-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 7px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }


    .mp-status-pill.green {
        background: #ECFDF3;
        color: #067647;
    }


    .mp-status-pill.red {
        background: #FFF1F2;
        color: #B42318;
    }


    .mp-status-pill.blue {
        background: #EEF4FF;
        color: #3538CD;
    }


    .mp-status-pill.purple {
        background: #F2F0FF;
        color: #6941C6;
    }


    .mp-status-pill.amber {
        background: #FFF7E8;
        color: #B54708;
    }


    /*
    |--------------------------------------------------------------------------
    | Top Sellers
    |--------------------------------------------------------------------------
    */

    .mp-top-seller-list {
        display: flex;
        flex-direction: column;
    }


    .mp-top-seller-row {
        display: grid;
        grid-template-columns: 22px 34px minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        padding: 10px 0;
        border-bottom: 1px solid var(--admin-border-soft);
    }


    .mp-top-seller-row:last-child {
        border-bottom: 0;
    }


    .mp-rank {
        color: var(--admin-muted-2);
        font-size: 10px;
        font-weight: 700;
    }


    .mp-seller-avatar {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 9px;
        background: var(--admin-accent-soft);
        color: var(--admin-accent-strong);
        font-size: 12px;
        font-weight: 800;
    }


    .mp-top-seller-info strong,
    .mp-top-seller-info small,
    .mp-top-seller-money strong,
    .mp-top-seller-money small {
        display: block;
    }


    .mp-top-seller-info strong {
        color: var(--admin-heading);
        font-size: 11px;
    }


    .mp-top-seller-info small,
    .mp-top-seller-money small {
        margin-top: 3px;
        color: var(--admin-muted);
        font-size: 7px;
    }


    .mp-top-seller-money {
        text-align: right;
    }


    .mp-top-seller-money strong {
        color: var(--admin-heading);
        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Package Payments
    |--------------------------------------------------------------------------
    */

    .mp-package-payment-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }


    .mp-package-payment-item {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--admin-border-soft);
        border-radius: 11px;
        background: var(--admin-surface-soft);
    }


    .mp-package-payment-icon {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 9px;
        background: #F4F3FF;
        color: #7A5AF8;
        font-size: 14px;
    }


    .mp-package-payment-item > div:nth-child(2) strong,
    .mp-package-payment-item > div:nth-child(2) span,
    .mp-package-payment-item > div:nth-child(2) small,
    .mp-package-payment-value strong,
    .mp-package-payment-value small {
        display: block;
    }


    .mp-package-payment-item > div:nth-child(2) strong {
        color: var(--admin-heading);
        font-size: 11px;
    }


    .mp-package-payment-item > div:nth-child(2) span {
        margin-top: 3px;
        color: var(--admin-text);
        font-size: 10px;
    }


    .mp-package-payment-item > div:nth-child(2) small {
        margin-top: 3px;
        color: var(--admin-muted);
        font-size: 7px;
    }


    .mp-package-payment-value {
        grid-column: 1 / -1;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 8px;
        padding-top: 9px;
        border-top: 1px solid var(--admin-border-soft);
    }


    .mp-package-payment-value strong {
        color: #067647;
        font-size: 13px;
    }


    .mp-package-payment-value small {
        color: var(--admin-muted);
        font-size: 7px;
    }


    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    .mp-empty-state {
        display: flex;
        min-height: 190px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        color: var(--admin-muted);
        text-align: center;
    }


    .mp-empty-state.compact {
        min-height: 120px;
    }


    .mp-empty-state i {
        color: var(--admin-accent);
        font-size: 22px;
    }


    .mp-empty-state strong {
        color: var(--admin-heading);
        font-size: 12px;
    }


    .mp-empty-state span {
        font-size: 10px;
    }


    /*
    |--------------------------------------------------------------------------
    | Dark Mode Fixes For Hard-Coded Soft Colors
    |--------------------------------------------------------------------------
    */

    html[data-admin-theme="dark"] .mp-kpi-icon.is-blue,
    html[data-admin-theme="dark"] .mp-mini-stat-icon.is-blue,
    html[data-admin-theme="dark"] .mp-status-pill.blue,
    html[data-admin-theme="dark"] .mp-queue-row.info .mp-queue-icon,
    html[data-admin-theme="dark"] .mp-queue-row.info b {
        background: rgba(97, 114, 243, .16);
    }


    html[data-admin-theme="dark"] .mp-kpi-icon.is-purple,
    html[data-admin-theme="dark"] .mp-mini-stat-icon.is-purple,
    html[data-admin-theme="dark"] .mp-status-pill.purple,
    html[data-admin-theme="dark"] .mp-queue-row.purple .mp-queue-icon,
    html[data-admin-theme="dark"] .mp-queue-row.purple b,
    html[data-admin-theme="dark"] .mp-kpi-icon.is-violet,
    html[data-admin-theme="dark"] .mp-package-payment-icon {
        background: rgba(122, 90, 248, .17);
    }


    html[data-admin-theme="dark"] .mp-kpi-icon.is-amber,
    html[data-admin-theme="dark"] .mp-mini-stat-icon.is-amber,
    html[data-admin-theme="dark"] .mp-status-pill.amber,
    html[data-admin-theme="dark"] .mp-queue-row.warning .mp-queue-icon,
    html[data-admin-theme="dark"] .mp-queue-row.warning b {
        background: rgba(247, 144, 9, .15);
    }


    html[data-admin-theme="dark"] .mp-kpi-icon.is-green,
    html[data-admin-theme="dark"] .mp-mini-stat-icon.is-green,
    html[data-admin-theme="dark"] .mp-status-pill.green,
    html[data-admin-theme="dark"] .mp-kpi-trend.up {
        background: rgba(18, 183, 106, .15);
    }


    html[data-admin-theme="dark"] .mp-status-pill.red,
    html[data-admin-theme="dark"] .mp-queue-row.danger .mp-queue-icon,
    html[data-admin-theme="dark"] .mp-queue-row.danger b,
    html[data-admin-theme="dark"] .mp-kpi-trend.down {
        background: rgba(240, 68, 56, .15);
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 1280px) {

        .mp-package-payment-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }


        .mp-operations-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

    }


    @media(max-width: 1100px) {

        .mp-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }


        .mp-mini-stats-grid,
        .mp-dashboard-three-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }


        .mp-dashboard-main-grid,
        .mp-dashboard-bottom-grid {
            grid-template-columns: 1fr;
        }

    }


    @media(max-width: 780px) {

        .mp-dashboard-hero {
            align-items: flex-start;
            flex-direction: column;
        }


        .mp-period-control {
            width: 100%;
        }


        .mp-mini-stats-grid,
        .mp-dashboard-three-grid,
        .mp-dashboard-two-grid,
        .mp-operations-grid,
        .mp-package-payment-grid {
            grid-template-columns: 1fr;
        }

    }


    @media(max-width: 560px) {

        .mp-kpi-grid {
            grid-template-columns: 1fr;
        }


        .mp-dashboard-hero,
        .mp-chart-card,
        .mp-list-card,
        .mp-table-card,
        .mp-operations-card,
        .mp-package-payment-card {
            padding: 16px;
        }


        .mp-card-heading {
            flex-direction: column;
        }


        .mp-chart-legend {
            justify-content: flex-start;
        }

    }

</style>

@endpush


{{-- =========================================================
    APEX CHARTS
========================================================== --}}

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |------------------------------------------------------------------
            | Data From Laravel
            |------------------------------------------------------------------
            */

            const dashboardData = @json($dashboardChartData);


            /*
            |------------------------------------------------------------------
            | Theme
            |------------------------------------------------------------------
            */

            const isDark =
                document.documentElement
                    .getAttribute('data-admin-theme')
                ===
                'dark';


            const textColor =
                isDark
                    ? '#8FA3C1'
                    : '#6E82A0';


            const headingColor =
                isDark
                    ? '#FFFFFF'
                    : '#10284B';


            const gridColor =
                isDark
                    ? '#26334B'
                    : '#E6EDF3';


            /*
            |------------------------------------------------------------------
            | Formatters
            |------------------------------------------------------------------
            */

            const currency =
                new Intl.NumberFormat(
                    'en-NG',
                    {
                        style: 'currency',
                        currency: 'NGN',
                        maximumFractionDigits: 0,
                    }
                );


            const compactCurrency =
                new Intl.NumberFormat(
                    'en-NG',
                    {
                        style: 'currency',
                        currency: 'NGN',
                        notation: 'compact',
                        maximumFractionDigits: 1,
                    }
                );


            const safeNumber =
                function (value) {

                    const number =
                        Number(value || 0);

                    return Number.isFinite(number)
                        ? number
                        : 0;
                };


            /*
            |------------------------------------------------------------------
            | Money Flow Area Chart
            |------------------------------------------------------------------
            */

            const moneyFlowElement =
                document.querySelector(
                    '#moneyFlowChart'
                );


            if (
                moneyFlowElement
                &&
                window.ApexCharts
            ) {

                const moneyFlowChart =
                    new ApexCharts(
                        moneyFlowElement,
                        {
                            chart: {
                                type: 'area',
                                height: 350,
                                toolbar: {
                                    show: false,
                                },
                                zoom: {
                                    enabled: false,
                                },
                                fontFamily: 'Inter, sans-serif',
                            },

                            series: [
                                {
                                    name: 'Buyer payments',
                                    data: dashboardData.chart.buyer_paid,
                                },
                                {
                                    name: 'Seller payouts',
                                    data: dashboardData.chart.seller_released,
                                },
                                {
                                    name: 'Platform profit',
                                    data: dashboardData.chart.platform_profit,
                                },
                            ],

                            colors: [
                                '#18A897',
                                '#7A5AF8',
                                '#F79009',
                            ],

                            dataLabels: {
                                enabled: false,
                            },

                            stroke: {
                                curve: 'smooth',
                                width: [3, 3, 3],
                            },

                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: .22,
                                    opacityTo: .02,
                                    stops: [0, 90, 100],
                                },
                            },

                            markers: {
                                size: 0,
                                hover: {
                                    size: 6,
                                },
                            },

                            xaxis: {
                                categories: dashboardData.chart.labels,
                                labels: {
                                    style: {
                                        colors: textColor,
                                        fontSize: '9px',
                                    },
                                },
                                axisBorder: {
                                    show: false,
                                },
                                axisTicks: {
                                    show: false,
                                },
                            },

                            yaxis: {
                                labels: {
                                    formatter: function (value) {
                                        return compactCurrency.format(
                                            safeNumber(value)
                                        );
                                    },
                                    style: {
                                        colors: [textColor],
                                        fontSize: '9px',
                                    },
                                },
                            },

                            grid: {
                                borderColor: gridColor,
                                strokeDashArray: 4,
                                padding: {
                                    left: 8,
                                    right: 8,
                                },
                            },

                            legend: {
                                show: false,
                            },

                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: function (value) {
                                        return currency.format(
                                            safeNumber(value)
                                        );
                                    },
                                },
                                x: {
                                    formatter: function (
                                        value,
                                        context
                                    ) {

                                        const index =
                                            context.dataPointIndex;

                                        const transactionCount =
                                            dashboardData
                                                .chart
                                                .transaction_count[index]
                                            ||
                                            0;

                                        return dashboardData
                                            .chart
                                            .labels[index]
                                            +
                                            ' · '
                                            +
                                            transactionCount
                                            +
                                            ' paid transactions';
                                    },
                                },
                            },
                        }
                    );


                moneyFlowChart.render();
            }


            /*
            |------------------------------------------------------------------
            | Gross Profit Margin Radial Chart
            |------------------------------------------------------------------
            */

            const marginElement =
                document.querySelector(
                    '#profitMarginChart'
                );


            if (
                marginElement
                &&
                window.ApexCharts
            ) {

                const margin =
                    Math.max(
                        0,
                        Math.min(
                            100,
                            safeNumber(
                                dashboardData.profitMargin
                            )
                        )
                    );


                const profitMarginChart =
                    new ApexCharts(
                        marginElement,
                        {
                            chart: {
                                type: 'radialBar',
                                height: 250,
                                sparkline: {
                                    enabled: true,
                                },
                                fontFamily: 'Inter, sans-serif',
                            },

                            series: [margin],

                            colors: ['#18A897'],

                            plotOptions: {
                                radialBar: {
                                    startAngle: -135,
                                    endAngle: 135,
                                    hollow: {
                                        size: '63%',
                                    },
                                    track: {
                                        background: gridColor,
                                        strokeWidth: '100%',
                                    },
                                    dataLabels: {
                                        name: {
                                            show: true,
                                            offsetY: 21,
                                            color: textColor,
                                            fontSize: '9px',
                                        },
                                        value: {
                                            offsetY: -15,
                                            color: headingColor,
                                            fontSize: '28px',
                                            fontWeight: 700,
                                            formatter: function (value) {
                                                return Number(value)
                                                    .toFixed(1)
                                                    +
                                                    '%';
                                            },
                                        },
                                    },
                                },
                            },

                            labels: [
                                'Gross platform margin',
                            ],

                            tooltip: {
                                enabled: true,
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: function (value) {
                                        return Number(value)
                                            .toFixed(2)
                                            +
                                            '% of business volume';
                                    },
                                },
                            },
                        }
                    );


                profitMarginChart.render();
            }


            /*
            |------------------------------------------------------------------
            | Transaction Status Donut
            |------------------------------------------------------------------
            */

            const transactionStatusElement =
                document.querySelector(
                    '#transactionStatusChart'
                );


            if (
                transactionStatusElement
                &&
                window.ApexCharts
            ) {

                const transactionSeries =
                    dashboardData
                        .transactionStatus
                        .series
                        .map(safeNumber);


                const transactionTotal =
                    transactionSeries.reduce(
                        function (total, value) {
                            return total + value;
                        },
                        0
                    );


                const statusChart =
                    new ApexCharts(
                        transactionStatusElement,
                        {
                            chart: {
                                type: 'donut',
                                height: 280,
                                fontFamily: 'Inter, sans-serif',
                            },

                            series: transactionSeries,

                            labels:
                                dashboardData
                                    .transactionStatus
                                    .labels,

                            colors: [
                                '#6172F3',
                                '#12B76A',
                                '#F04438',
                                '#98A2B3',
                            ],

                            stroke: {
                                width: 2,
                                colors: [
                                    isDark
                                        ? '#111A2F'
                                        : '#FFFFFF',
                                ],
                            },

                            legend: {
                                position: 'bottom',
                                fontSize: '9px',
                                labels: {
                                    colors: textColor,
                                },
                            },

                            dataLabels: {
                                enabled: false,
                            },

                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '68%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                color: textColor,
                                                fontSize: '9px',
                                            },
                                            value: {
                                                show: true,
                                                color: headingColor,
                                                fontSize: '22px',
                                                fontWeight: 700,
                                            },
                                            total: {
                                                show: true,
                                                label: 'Paid',
                                                color: textColor,
                                                fontSize: '9px',
                                                formatter: function () {
                                                    return transactionTotal;
                                                },
                                            },
                                        },
                                    },
                                },
                            },

                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: function (
                                        value,
                                        context
                                    ) {

                                        const percentage =
                                            transactionTotal > 0
                                                ? (
                                                    safeNumber(value)
                                                    /
                                                    transactionTotal
                                                )
                                                *
                                                100
                                                : 0;

                                        return value
                                            +
                                            ' transactions · '
                                            +
                                            percentage.toFixed(1)
                                            +
                                            '%';
                                    },
                                },
                            },

                            noData: {
                                text: 'No paid transactions yet',
                                align: 'center',
                                verticalAlign: 'middle',
                                style: {
                                    color: textColor,
                                    fontSize: '10px',
                                },
                            },
                        }
                    );


                statusChart.render();
            }


            /*
            |------------------------------------------------------------------
            | Profit Composition Donut
            |------------------------------------------------------------------
            */

            const profitCompositionElement =
                document.querySelector(
                    '#profitCompositionChart'
                );


            if (
                profitCompositionElement
                &&
                window.ApexCharts
            ) {

                const profitSeries =
                    dashboardData
                        .profitComposition
                        .series
                        .map(safeNumber);


                const profitChart =
                    new ApexCharts(
                        profitCompositionElement,
                        {
                            chart: {
                                type: 'donut',
                                height: 270,
                                fontFamily: 'Inter, sans-serif',
                            },

                            series: profitSeries,

                            labels:
                                dashboardData
                                    .profitComposition
                                    .labels,

                            colors: [
                                '#18A897',
                                '#7A5AF8',
                            ],

                            stroke: {
                                width: 2,
                                colors: [
                                    isDark
                                        ? '#111A2F'
                                        : '#FFFFFF',
                                ],
                            },

                            dataLabels: {
                                enabled: false,
                            },

                            legend: {
                                show: false,
                            },

                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '70%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                color: textColor,
                                                fontSize: '8px',
                                            },
                                            value: {
                                                show: true,
                                                color: headingColor,
                                                fontSize: '16px',
                                                fontWeight: 700,
                                                formatter: function (value) {
                                                    return compactCurrency.format(
                                                        safeNumber(value)
                                                    );
                                                },
                                            },
                                            total: {
                                                show: true,
                                                label: 'Gross profit',
                                                color: textColor,
                                                fontSize: '8px',
                                                formatter: function () {
                                                    return compactCurrency.format(
                                                        safeNumber(
                                                            dashboardData.grossProfit
                                                        )
                                                    );
                                                },
                                            },
                                        },
                                    },
                                },
                            },

                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: function (value) {
                                        return currency.format(
                                            safeNumber(value)
                                        );
                                    },
                                },
                            },
                        }
                    );


                profitChart.render();
            }


            /*
            |------------------------------------------------------------------
            | Package Mix Donut
            |------------------------------------------------------------------
            */

            const packageElement =
                document.querySelector(
                    '#packageMixChart'
                );


            if (
                packageElement
                &&
                window.ApexCharts
            ) {

                const packageSeries =
                    dashboardData
                        .packageChart
                        .series
                        .map(safeNumber);


                const packageTotal =
                    packageSeries.reduce(
                        function (total, value) {
                            return total + value;
                        },
                        0
                    );


                const packageChart =
                    new ApexCharts(
                        packageElement,
                        {
                            chart: {
                                type: 'donut',
                                height: 280,
                                fontFamily: 'Inter, sans-serif',
                            },

                            series: packageSeries,

                            labels:
                                dashboardData
                                    .packageChart
                                    .labels,

                            colors: [
                                '#12B76A',
                                '#7A5AF8',
                                '#6172F3',
                                '#F79009',
                                '#06AED4',
                                '#EE46BC',
                            ],

                            dataLabels: {
                                enabled: false,
                            },

                            stroke: {
                                width: 2,
                                colors: [
                                    isDark
                                        ? '#111A2F'
                                        : '#FFFFFF',
                                ],
                            },

                            legend: {
                                position: 'bottom',
                                fontSize: '9px',
                                labels: {
                                    colors: textColor,
                                },
                            },

                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '67%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Purchases',
                                                color: textColor,
                                                fontSize: '9px',
                                                formatter: function () {
                                                    return packageTotal;
                                                },
                                            },
                                        },
                                    },
                                },
                            },

                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                custom: function ({
                                    series,
                                    seriesIndex,
                                    w,
                                }) {

                                    const label =
                                        w.globals.labels[seriesIndex]
                                        ||
                                        'Package';


                                    const purchases =
                                        series[seriesIndex]
                                        ||
                                        0;


                                    const revenue =
                                        dashboardData
                                            .packageChart
                                            .revenue[seriesIndex]
                                        ||
                                        0;


                                    const background =
                                        isDark
                                            ? '#111A2F'
                                            : '#FFFFFF';


                                    const color =
                                        isDark
                                            ? '#F4F7FB'
                                            : '#173052';


                                    return `
                                        <div style="padding:10px 12px;background:${background};color:${color};font-family:Inter,sans-serif;min-width:150px;">
                                            <strong style="display:block;font-size:11px;margin-bottom:5px;">${label}</strong>
                                            <span style="display:block;font-size:9px;margin-bottom:3px;">Purchases: <b>${purchases}</b></span>
                                            <span style="display:block;font-size:9px;">Revenue: <b>${currency.format(safeNumber(revenue))}</b></span>
                                        </div>
                                    `;
                                },
                            },

                            noData: {
                                text: 'No package purchases yet',
                                style: {
                                    color: textColor,
                                    fontSize: '10px',
                                },
                            },
                        }
                    );


                packageChart.render();
            }

        }
    );

</script>

@endpush
