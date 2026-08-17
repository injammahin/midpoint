@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Formatting
    |--------------------------------------------------------------------------
    */

    $moneyFull = function ($amount, $decimals = 0) {

        return
            '₦'
            .
            number_format(
                (float) $amount,
                $decimals
            );
    };


    $moneyCompact = function ($amount) {

        $amount =
            (float) $amount;


        if (abs($amount) >= 1000000000) {

            return
                '₦'
                .
                number_format(
                    $amount / 1000000000,
                    1
                )
                .
                'B';
        }


        if (abs($amount) >= 1000000) {

            return
                '₦'
                .
                number_format(
                    $amount / 1000000,
                    1
                )
                .
                'M';
        }


        if (abs($amount) >= 1000) {

            return
                '₦'
                .
                number_format(
                    $amount / 1000,
                    1
                )
                .
                'K';
        }


        return
            '₦'
            .
            number_format(
                $amount,
                0
            );
    };


    /*
    |--------------------------------------------------------------------------
    | Growth
    |--------------------------------------------------------------------------
    */

    $buyerGrowthPositive =
        ($money['buyer_paid_growth'] ?? 0) >= 0;


    $profitGrowthPositive =
        ($money['profit_growth'] ?? 0) >= 0;


    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    $hasWithdrawalRoute =
        \Illuminate\Support\Facades\Route::has(
            'admin.withdrawals.index'
        );


    /*
    |--------------------------------------------------------------------------
    | Chart Data
    |--------------------------------------------------------------------------
    */

    $dashboardChartData = [

        'chart' =>
            $chart,

        'transactionStatus' =>
            $transactionStatusChart,

        'withdrawalStatus' =>
            $withdrawalStatusChart,

        'profitComposition' =>
            $profitCompositionChart,

        'profitMargin' =>
            $money['gross_profit_margin'],
    ];

@endphp



<div class="mp-dashboard">


    {{-- ============================================================
        HERO
    ============================================================= --}}

    <section class="admin-card mp-dashboard-hero">

        <div>

            <div class="mp-dashboard-kicker">

                <span class="mp-live-dot"></span>

                Live financial overview

            </div>


            <h2>
                Midpoint Command Center
            </h2>


            <p>
                Track buyer payments, protected funds,
                seller wallet releases, bank withdrawals,
                platform revenue and marketplace activity.
            </p>


            <div class="mp-dashboard-meta">

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



    {{-- ============================================================
        PRIMARY FINANCIAL FLOW
    ============================================================= --}}

    <section class="mp-kpi-grid">


        {{-- Buyer Payments --}}

        <article
            class="admin-card mp-kpi-card"
            title="Total successfully verified buyer payments."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon blue">

                    <i class="fa-solid fa-money-bill-transfer"></i>

                </span>


                <span
                    class="mp-trend {{ $buyerGrowthPositive ? 'positive' : 'negative' }}"
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
                {{ number_format($stats['paid_transactions']) }}
                verified paid transactions
            </small>

        </article>



        {{-- Protected Escrow --}}

        <article
            class="admin-card mp-kpi-card"
            title="Buyer-paid funds that have not yet been released into a seller wallet."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon amber">

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

                {{ number_format($stats['protected_transaction_count']) }}

                transactions awaiting wallet release

            </small>

        </article>



        {{-- Released To Seller Wallet --}}

        <article
            class="admin-card mp-kpi-card"
            title="Seller funds that completed escrow and were credited into seller Midpoint wallets."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon purple">

                    <i class="fa-solid fa-wallet"></i>

                </span>

            </div>


            <span class="mp-kpi-label">
                Released to seller wallets
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['seller_wallet_released']) }}
            </strong>


            <small>

                {{ number_format($stats['wallet_release_count']) }}

                successful wallet releases

            </small>

        </article>



        {{-- Withdrawn To Bank --}}

        <article
            class="admin-card mp-kpi-card"
            title="Seller withdrawals that completed successfully and were marked as paid to the seller bank."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon green">

                    <i class="fa-solid fa-building-columns"></i>

                </span>

            </div>


            <span class="mp-kpi-label">
                Withdrawn to seller banks
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['seller_withdrawn']) }}
            </strong>


            <small>

                {{ number_format($stats['successful_withdrawal_count']) }}

                successful withdrawals

            </small>

        </article>



        {{-- Seller Liability --}}

        <article
            class="admin-card mp-kpi-card liability"
            title="Total money still owed to sellers: available seller wallet balances plus withdrawals currently being processed."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon teal">

                    <i class="fa-solid fa-scale-balanced"></i>

                </span>

            </div>


            <span class="mp-kpi-label">
                Seller wallet liability
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['seller_wallet_liability']) }}
            </strong>


            <small>

                Available:
                {{ $moneyFull($money['seller_available_balance']) }}

                ·

                Pending:
                {{ $moneyFull($money['seller_pending_wallet_balance']) }}

            </small>

        </article>



        {{-- Pending Withdrawals --}}

        <article
            class="admin-card mp-kpi-card"
            title="Withdrawal requests currently reserved from seller wallets and still awaiting final payout status."
        >

            <div class="mp-kpi-head">

                <span class="mp-kpi-icon orange">

                    <i class="fa-solid fa-hourglass-half"></i>

                </span>


                @if($stats['pending_withdrawal_count'] > 0)

                    <span class="mp-alert-dot">
                        {{ $stats['pending_withdrawal_count'] }}
                    </span>

                @endif

            </div>


            <span class="mp-kpi-label">
                Pending withdrawals
            </span>


            <strong class="mp-kpi-value">
                {{ $moneyCompact($money['pending_withdrawals']) }}
            </strong>


            <small>

                {{ number_format($stats['pending_withdrawal_count']) }}

                payout requests processing

            </small>

        </article>

    </section>



    {{-- ============================================================
        PLATFORM REVENUE
    ============================================================= --}}

    <section class="mp-revenue-grid">


        {{-- Gross Profit --}}

        <article class="admin-card mp-revenue-card profit">

            <span class="mp-revenue-icon">

                <i class="fa-solid fa-chart-line"></i>

            </span>


            <div>

                <span>
                    Gross platform profit
                </span>


                <strong>
                    {{ $moneyCompact($money['gross_platform_profit']) }}
                </strong>


                <small>
                    Service fees + seller package revenue
                </small>

            </div>


            <span
                class="mp-revenue-trend {{ $profitGrowthPositive ? 'positive' : 'negative' }}"
            >

                {{ $profitGrowthPositive ? '+' : '' }}{{ number_format($money['profit_growth'], 1) }}%

            </span>

        </article>



        {{-- Service Fees --}}

        <article class="admin-card mp-revenue-card">

            <span class="mp-revenue-icon green">

                <i class="fa-solid fa-percent"></i>

            </span>


            <div>

                <span>
                    Service fee revenue
                </span>


                <strong>
                    {{ $moneyCompact($money['service_fee_revenue']) }}
                </strong>


                <small>
                    VAT collected:
                    {{ $moneyFull($money['vat_collected']) }}
                </small>

            </div>

        </article>



        {{-- Packages --}}

        <article class="admin-card mp-revenue-card">

            <span class="mp-revenue-icon purple">

                <i class="fa-solid fa-crown"></i>

            </span>


            <div>

                <span>
                    Seller package revenue
                </span>


                <strong>
                    {{ $moneyCompact($money['package_revenue']) }}
                </strong>


                <small>

                    {{ number_format($stats['package_purchases']) }}

                    purchases

                    ·

                    {{ number_format($stats['package_customers']) }}

                    customers

                </small>

            </div>

        </article>

    </section>



    {{-- ============================================================
        MARKETPLACE SNAPSHOT
    ============================================================= --}}

    <section class="mp-mini-grid">


        <a
            href="{{ route('admin.users.index') }}"
            class="admin-card mp-mini-card"
        >

            <span class="mp-mini-icon">

                <i class="fa-solid fa-users"></i>

            </span>


            <div>

                <span>
                    Total users
                </span>


                <small>
                    {{ number_format($stats['active_users']) }} active
                </small>

            </div>


            <strong>
                {{ number_format($stats['users']) }}
            </strong>

        </a>



        <a
            href="{{ route('admin.billing.subscriptions.index') }}"
            class="admin-card mp-mini-card"
        >

            <span class="mp-mini-icon green">

                <i class="fa-solid fa-store"></i>

            </span>


            <div>

                <span>
                    Active sellers
                </span>


                <small>
                    Paid active subscriptions
                </small>

            </div>


            <strong>
                {{ number_format($stats['active_sellers']) }}
            </strong>

        </a>



        <div class="admin-card mp-mini-card">

            <span class="mp-mini-icon purple">

                <i class="fa-solid fa-cart-shopping"></i>

            </span>


            <div>

                <span>
                    Paying buyers
                </span>


                <small>
                    Unique buyers with paid orders
                </small>

            </div>


            <strong>
                {{ number_format($stats['paid_buyers']) }}
            </strong>

        </div>



        <a
            href="{{ route('admin.website-settings.seller-applications.index') }}"
            class="admin-card mp-mini-card"
        >

            <span class="mp-mini-icon amber">

                <i class="fa-solid fa-user-check"></i>

            </span>


            <div>

                <span>
                    Seller applications
                </span>


                <small>
                    Waiting for admin review
                </small>

            </div>


            <strong>
                {{ number_format($stats['pending_seller_applications']) }}
            </strong>

        </a>

    </section>



    {{-- ============================================================
        MONEY FLOW
    ============================================================= --}}

    <section class="mp-main-grid">


        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading">

                <div>

                    <span class="mp-eyebrow">
                        Financial flow
                    </span>


                    <h3>
                        Payments, wallet releases & withdrawals
                    </h3>


                    <p>
                        The chart separates escrow release from actual bank withdrawal.
                    </p>

                </div>


                <div class="mp-chart-legend">

                    <span class="buyer">
                        Buyer paid
                    </span>

                    <span class="released">
                        Wallet released
                    </span>

                    <span class="withdrawn">
                        Bank withdrawn
                    </span>

                    <span class="profit">
                        Platform profit
                    </span>

                </div>

            </div>


            <div
                id="moneyFlowChart"
                class="mp-money-chart"
            ></div>

        </article>



        {{-- ========================================================
            PROFIT MARGIN
        ========================================================= --}}

        <article class="admin-card mp-chart-card margin-card">

            <div class="mp-card-heading">

                <div>

                    <span class="mp-eyebrow">
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


            <div class="mp-margin-values">

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


            <p class="mp-note">

                Gross platform profit excludes VAT
                and operating expenses.

            </p>

        </article>

    </section>



    {{-- ============================================================
        STATUS CHARTS
    ============================================================= --}}

    <section class="mp-three-grid">


        {{-- Transaction Status --}}

        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-eyebrow">
                        Transaction health
                    </span>


                    <h3>
                        Paid transactions
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



        {{-- Withdrawal Status --}}

        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-eyebrow">
                        Seller payouts
                    </span>


                    <h3>
                        Withdrawal status
                    </h3>

                </div>


                @if($hasWithdrawalRoute)

                    <a href="{{ route('admin.withdrawals.index') }}">
                        View all
                    </a>

                @endif

            </div>


            <div
                id="withdrawalStatusChart"
                class="mp-donut-chart"
            ></div>

        </article>



        {{-- Profit Mix --}}

        <article class="admin-card mp-chart-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-eyebrow">
                        Revenue mix
                    </span>


                    <h3>
                        Platform profit sources
                    </h3>

                </div>

            </div>


            <div
                id="profitCompositionChart"
                class="mp-donut-chart"
            ></div>


            <div class="mp-profit-values">

                <div>

                    <span class="dot service"></span>

                    Service fees

                    <strong>
                        {{ $moneyFull($money['service_fee_revenue']) }}
                    </strong>

                </div>


                <div>

                    <span class="dot packages"></span>

                    Seller packages

                    <strong>
                        {{ $moneyFull($money['package_revenue']) }}
                    </strong>

                </div>

            </div>

        </article>

    </section>



    {{-- ============================================================
        RECENT DATA
    ============================================================= --}}

    <section class="mp-two-grid">


        {{-- Recent Transactions --}}

        <article class="admin-card mp-table-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-eyebrow">
                        Recent activity
                    </span>


                    <h3>
                        Buyer transactions
                    </h3>

                </div>


                <a href="{{ route('admin.transactions.index') }}">
                    View all
                </a>

            </div>


            @if($recentTransactions->count())

                <div class="mp-table-scroll">

                    <table class="mp-table">

                        <thead>

                            <tr>

                                <th>
                                    Transaction
                                </th>

                                <th>
                                    Seller
                                </th>

                                <th>
                                    Buyer paid
                                </th>

                                <th>
                                    Funds state
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($recentTransactions as $transaction)

                                @php

                                    $walletReleased =
                                        $transaction->walletRelease
                                        &&
                                        $transaction->walletRelease->status
                                        ===
                                        \App\Models\SellerWalletTransaction::STATUS_POSTED;

                                @endphp


                                <tr>

                                    <td>

                                        <strong>
                                            {{ $transaction->reference }}
                                        </strong>


                                        <small>
                                            {{ $transaction->title }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $transaction->seller?->name ?? 'Seller' }}
                                        </strong>


                                        <small>
                                            {{ $transaction->seller?->email }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong>

                                            {{ $moneyFull(
                                                $transaction->paid_amount
                                                ?: $transaction->total_amount,
                                                2
                                            ) }}

                                        </strong>

                                    </td>


                                    <td>

                                        @if($walletReleased)

                                            <span class="mp-badge success">

                                                <i class="fa-solid fa-wallet"></i>

                                                Wallet released

                                            </span>

                                        @elseif($transaction->status === \App\Models\SecureTransaction::STATUS_DISPUTED)

                                            <span class="mp-badge danger">

                                                Disputed

                                            </span>

                                        @else

                                            <span class="mp-badge warning">

                                                Protected

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="mp-empty">

                    No paid transactions yet.

                </div>

            @endif

        </article>



        {{-- Recent Withdrawals --}}

        <article class="admin-card mp-table-card">

            <div class="mp-card-heading compact">

                <div>

                    <span class="mp-eyebrow">
                        Seller banking
                    </span>


                    <h3>
                        Recent withdrawals
                    </h3>

                </div>


                @if($hasWithdrawalRoute)

                    <a href="{{ route('admin.withdrawals.index') }}">
                        View all
                    </a>

                @endif

            </div>


            @if($recentWithdrawals->count())

                <div class="mp-table-scroll">

                    <table class="mp-table">

                        <thead>

                            <tr>

                                <th>
                                    Seller
                                </th>

                                <th>
                                    Bank
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($recentWithdrawals as $withdrawal)

                                <tr>

                                    <td>

                                        <strong>
                                            {{ $withdrawal->seller?->name ?? 'Seller' }}
                                        </strong>


                                        <small>
                                            {{ $withdrawal->reference }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $withdrawal->bank_name }}
                                        </strong>


                                        <small>

                                            {{ $withdrawal->account_name }}

                                            ·

                                            ••••{{ $withdrawal->account_number_last4 }}

                                        </small>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $moneyFull($withdrawal->amount, 2) }}
                                        </strong>

                                    </td>


                                    <td>

                                        @if($withdrawal->status === \App\Models\SellerWithdrawal::STATUS_SUCCESSFUL)

                                            <span class="mp-badge success">
                                                Successful
                                            </span>

                                        @elseif(in_array($withdrawal->status, [
                                            \App\Models\SellerWithdrawal::STATUS_PENDING,
                                            \App\Models\SellerWithdrawal::STATUS_PROCESSING,
                                            \App\Models\SellerWithdrawal::STATUS_OTP,
                                        ], true))

                                            <span class="mp-badge processing">
                                                Processing
                                            </span>

                                        @elseif($withdrawal->status === \App\Models\SellerWithdrawal::STATUS_REVERSED)

                                            <span class="mp-badge danger">
                                                Reversed
                                            </span>

                                        @else

                                            <span class="mp-badge danger">
                                                Failed
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="mp-empty">

                    No seller withdrawals yet.

                </div>

            @endif

        </article>

    </section>



    {{-- ============================================================
        TOP SELLERS
    ============================================================= --}}

    <section class="admin-card mp-table-card">

        <div class="mp-card-heading compact">

            <div>

                <span class="mp-eyebrow">
                    Seller performance
                </span>


                <h3>
                    Top sellers by wallet releases
                </h3>


                <p>
                    Shows how much completed transaction value has been credited
                    to each seller wallet and how much has already been withdrawn.
                </p>

            </div>

        </div>


        @if($topSellers->count())

            <div class="mp-table-scroll">

                <table class="mp-table">

                    <thead>

                        <tr>

                            <th>
                                Seller
                            </th>

                            <th>
                                Released transactions
                            </th>

                            <th>
                                Wallet released
                            </th>

                            <th>
                                Bank withdrawn
                            </th>

                            <th>
                                Remaining released value
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($topSellers as $seller)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $seller['name'] }}
                                    </strong>


                                    <small>
                                        {{ $seller['email'] }}
                                    </small>

                                </td>


                                <td>

                                    <strong>
                                        {{ number_format($seller['transactions']) }}
                                    </strong>

                                </td>


                                <td>

                                    <strong>
                                        {{ $moneyFull($seller['wallet_released'], 2) }}
                                    </strong>

                                </td>


                                <td>

                                    <strong>
                                        {{ $moneyFull($seller['withdrawn'], 2) }}
                                    </strong>

                                </td>


                                <td>

                                    <strong>

                                        {{ $moneyFull(
                                            max(
                                                0,
                                                $seller['wallet_released']
                                                -
                                                $seller['withdrawn']
                                            ),
                                            2
                                        ) }}

                                    </strong>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="mp-empty">

                Seller wallet releases will appear here after transactions
                are successfully released from escrow.

            </div>

        @endif

    </section>



    {{-- ============================================================
        ACCOUNTING EXPLANATION
    ============================================================= --}}

    <section class="admin-card mp-flow-explanation">

        <div class="mp-flow-title">

            <i class="fa-solid fa-circle-info"></i>

            How Midpoint money is represented

        </div>


        <div class="mp-flow-steps">


            <div>

                <span class="number">
                    1
                </span>

                <strong>
                    Buyer pays
                </strong>

                <small>
                    Recorded as buyer payment received.
                </small>

            </div>



            <span class="arrow">
                →
            </span>



            <div>

                <span class="number">
                    2
                </span>

                <strong>
                    Protected
                </strong>

                <small>
                    Midpoint holds the transaction during fulfilment.
                </small>

            </div>



            <span class="arrow">
                →
            </span>



            <div>

                <span class="number">
                    3
                </span>

                <strong>
                    Seller wallet
                </strong>

                <small>
                    Completed transaction funds are credited to seller wallet.
                </small>

            </div>



            <span class="arrow">
                →
            </span>



            <div>

                <span class="number">
                    4
                </span>

                <strong>
                    Withdrawal
                </strong>

                <small>
                    Seller requests money from available wallet balance.
                </small>

            </div>



            <span class="arrow">
                →
            </span>



            <div>

                <span class="number">
                    5
                </span>

                <strong>
                    Seller bank
                </strong>

                <small>
                    Successful withdrawal becomes withdrawn-to-bank value.
                </small>

            </div>

        </div>

    </section>


</div>

@endsection



{{-- ========================================================================
    STYLES
========================================================================= --}}

@push('styles')

<style>

    .mp-dashboard {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }


    .mp-dashboard *,
    .mp-dashboard *::before,
    .mp-dashboard *::after {
        box-sizing: border-box;
    }


    /*
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    */

    .mp-dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;

        padding: 20px 22px;
    }


    .mp-dashboard-kicker {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-bottom: 5px;

        color: #078967;

        font-size: 11px;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: .12em;
    }


    .mp-live-dot {
        width: 7px;
        height: 7px;

        border-radius: 999px;

        background: #15b78d;

        box-shadow: 0 0 0 4px rgba(21, 183, 141, .10);
    }


    .mp-dashboard-hero h2 {
        margin: 0;

        color: #08253d;

        font-size: 21px;
        font-weight: 800;
    }


    .mp-dashboard-hero p {
        max-width: 730px;

        margin: 6px 0 10px;

        color: #647c93;

        font-size: 13px;
        line-height: 1.65;
    }


    .mp-dashboard-meta {
        display: flex;
        align-items: center;
        gap: 15px;

        color: #667b91;

        font-size: 11px;
    }


    .mp-dashboard-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }


    .mp-period-control {
        flex: 0 0 auto;

        display: flex;
        flex-direction: column;
        gap: 5px;
    }


    .mp-period-control label {
        color: #708398;

        font-size: 10px;
        font-weight: 700;
    }


    .mp-period-control select {
        padding: 9px 32px 9px 11px;

        border: 1px solid #dbe5eb;
        border-radius: 9px;

        outline: none;

        background: var(--admin-card-bg, #fff);

        color: #263e55;

        font-size: 12px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | KPI
    |--------------------------------------------------------------------------
    */

    .mp-kpi-grid {
        display: grid;

        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );

        gap: 11px;
    }


    .mp-kpi-card {
        min-height: 137px;

        padding: 16px;
    }


    .mp-kpi-card.liability {
        background:
            linear-gradient(
                135deg,
                rgba(11, 150, 121, .06),
                rgba(255, 255, 255, 0)
            ),
            var(--admin-card-bg, #fff);
    }


    .mp-kpi-head {
        min-height: 31px;

        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        margin-bottom: 10px;
    }


    .mp-kpi-icon,
    .mp-revenue-icon,
    .mp-mini-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 32px;
        height: 32px;

        border-radius: 10px;

        background: #e8f7f2;

        color: #078967;
    }


    .mp-kpi-icon.blue,
    .mp-mini-icon.blue {
        background: #edf3ff;
        color: #376ad5;
    }


    .mp-kpi-icon.purple,
    .mp-mini-icon.purple,
    .mp-revenue-icon.purple {
        background: #f3efff;
        color: #7755df;
    }


    .mp-kpi-icon.amber,
    .mp-mini-icon.amber {
        background: #fff5e5;
        color: #c97510;
    }


    .mp-kpi-icon.green,
    .mp-revenue-icon.green,
    .mp-mini-icon.green {
        background: #e9faef;
        color: #078a49;
    }


    .mp-kpi-icon.orange {
        background: #fff1e9;
        color: #d46221;
    }


    .mp-kpi-icon.teal {
        background: #e5f8f6;
        color: #078a7b;
    }


    .mp-kpi-label {
        display: block;

        margin-bottom: 6px;

        color: #617997;

        font-size: 11px;
        font-weight: 700;
    }


    .mp-kpi-value {
        display: block;

        margin-bottom: 7px;

        color: #09233c;

        font-size: 23px;
        font-weight: 800;
        line-height: 1;
    }


    .mp-kpi-card small {
        color: #70859c;

        font-size: 8.5px;
        line-height: 1.5;
    }


    .mp-trend,
    .mp-revenue-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;

        padding: 4px 7px;

        border-radius: 999px;

        font-size: 10px;
        font-weight: 800;
    }


    .mp-trend.positive,
    .mp-revenue-trend.positive {
        background: #eafaf0;
        color: #07854a;
    }


    .mp-trend.negative,
    .mp-revenue-trend.negative {
        background: #fff0f0;
        color: #c24545;
    }


    .mp-alert-dot {
        min-width: 22px;
        height: 22px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 6px;

        border-radius: 999px;

        background: #fff1e7;

        color: #cb5f22;

        font-size: 10px;
        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    */

    .mp-revenue-grid {
        display: grid;

        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );

        gap: 11px;
    }


    .mp-revenue-card {
        position: relative;

        display: grid;

        grid-template-columns:
            auto 1fr auto;

        align-items: center;

        gap: 12px;

        padding: 15px;
    }


    .mp-revenue-card.profit {
        background:
            linear-gradient(
                135deg,
                rgba(19, 160, 126, .08),
                transparent 65%
            ),
            var(--admin-card-bg, #fff);
    }


    .mp-revenue-card > div > span {
        display: block;

        margin-bottom: 4px;

        color: #6a8097;

        font-size: 11px;
        font-weight: 700;
    }


    .mp-revenue-card strong {
        display: block;

        margin-bottom: 4px;

        color: #0b2943;

        font-size: 17px;
        font-weight: 800;
    }


    .mp-revenue-card small {
        color: #7d8fa0;

        font-size: 10px;
    }


    /*
    |--------------------------------------------------------------------------
    | Mini Stats
    |--------------------------------------------------------------------------
    */

    .mp-mini-grid {
        display: grid;

        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );

        gap: 11px;
    }


    .mp-mini-card {
        display: grid;

        grid-template-columns:
            auto 1fr auto;

        gap: 10px;

        align-items: center;

        padding: 12px 14px;

        color: inherit;

        text-decoration: none;
    }


    .mp-mini-icon {
        width: 29px;
        height: 29px;

        border-radius: 9px;

        font-size: 13px;
    }


    .mp-mini-card div span {
        display: block;

        margin-bottom: 3px;

        color: #516d8b;

        font-size: 11px;
        font-weight: 700;
    }


    .mp-mini-card div small {
        display: block;

        color: #8495a5;

        font-size: 7.5px;
    }


    .mp-mini-card > strong {
        color: #092740;

        font-size: 17px;
        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Charts
    |--------------------------------------------------------------------------
    */

    .mp-main-grid {
        display: grid;

        grid-template-columns:
            minmax(0, 2.7fr)
            minmax(280px, .85fr);

        gap: 11px;
    }


    .mp-three-grid {
        display: grid;

        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );

        gap: 11px;
    }


    .mp-two-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 11px;
    }


    .mp-chart-card,
    .mp-table-card {
        padding: 17px;
    }


    .mp-card-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 16px;

        margin-bottom: 12px;
    }


    .mp-card-heading.compact {
        align-items: center;
    }


    .mp-eyebrow {
        display: block;

        margin-bottom: 4px;

        color: #078967;

        font-size: 7.5px;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: .12em;
    }


    .mp-card-heading h3 {
        margin: 0;

        color: #0b2841;

        font-size: 13px;
        font-weight: 800;
    }


    .mp-card-heading p {
        margin: 5px 0 0;

        color: #8293a3;

        font-size: 10px;
        line-height: 1.45;
    }


    .mp-card-heading a {
        flex: 0 0 auto;

        color: #0b8b6d;

        font-size: 10px;
        font-weight: 800;

        text-decoration: none;
    }


    .mp-chart-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 12px;

        color: #667e94;

        font-size: 10px;
    }


    .mp-chart-legend span {
        position: relative;

        padding-left: 11px;
    }


    .mp-chart-legend span::before {
        content: '';

        position: absolute;

        left: 0;
        top: 50%;

        width: 6px;
        height: 6px;

        transform: translateY(-50%);

        border-radius: 999px;
    }


    .mp-chart-legend .buyer::before {
        background: #10a98b;
    }


    .mp-chart-legend .released::before {
        background: #7358dc;
    }


    .mp-chart-legend .withdrawn::before {
        background: #3776d7;
    }


    .mp-chart-legend .profit::before {
        background: #e78623;
    }


    .mp-money-chart {
        min-height: 350px;
    }


    .mp-margin-chart {
        min-height: 230px;
    }


    .mp-donut-chart {
        min-height: 255px;
    }


    .mp-margin-values {
        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 8px;

        margin-top: 4px;
    }


    .mp-margin-values > div {
        padding: 10px;

        border-radius: 9px;

        background: #f3f7f8;
    }


    .mp-margin-values span {
        display: block;

        margin-bottom: 4px;

        color: #71869a;

        font-size: 7.5px;
    }


    .mp-margin-values strong {
        color: #16334a;

        font-size: 12px;
    }


    .mp-note {
        margin: 9px 0 0;

        color: #8a9aa8;

        font-size: 7.5px;
        line-height: 1.45;
    }


    .mp-profit-values {
        display: flex;
        flex-direction: column;

        gap: 7px;

        margin-top: 5px;
    }


    .mp-profit-values > div {
        display: flex;
        align-items: center;

        gap: 6px;

        color: #667d91;

        font-size: 10px;
    }


    .mp-profit-values strong {
        margin-left: auto;

        color: #19364c;
    }


    .mp-profit-values .dot {
        width: 6px;
        height: 6px;

        border-radius: 50%;
    }


    .mp-profit-values .dot.service {
        background: #13aa88;
    }


    .mp-profit-values .dot.packages {
        background: #745bd9;
    }


    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */

    .mp-table-scroll {
        overflow-x: auto;
    }


    .mp-table {
        width: 100%;

        border-collapse: collapse;
    }


    .mp-table th {
        padding: 9px 8px;

        border-bottom: 1px solid #e8eef1;

        color: #8998a7;

        font-size: 9px;
        font-weight: 800;

        text-align: left;

        text-transform: uppercase;
        letter-spacing: .08em;
    }


    .mp-table td {
        padding: 10px 8px;

        border-bottom: 1px solid #eef2f4;

        color: #61758a;

        font-size: 10px;

        vertical-align: middle;
    }


    .mp-table tbody tr:last-child td {
        border-bottom: 0;
    }


    .mp-table td strong {
        display: block;

        color: #1b344b;

        font-size: 8.5px;
        font-weight: 700;
    }


    .mp-table td small {
        display: block;

        max-width: 220px;

        margin-top: 3px;

        color: #8b9aa8;

        font-size: 9px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .mp-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;

        padding: 5px 7px;

        border-radius: 999px;

        font-size: 9px;
        font-weight: 800;

        white-space: nowrap;
    }


    .mp-badge.success {
        background: #e8f9ef;

        color: #087749;
    }


    .mp-badge.warning {
        background: #fff5df;

        color: #9b620d;
    }


    .mp-badge.processing {
        background: #eaf3ff;

        color: #3268b6;
    }


    .mp-badge.danger {
        background: #fff0f0;

        color: #bf3e3e;
    }


    .mp-empty {
        padding: 30px 15px;

        color: #8395a4;

        font-size: 11px;

        text-align: center;
    }


    /*
    |--------------------------------------------------------------------------
    | Flow Explanation
    |--------------------------------------------------------------------------
    */

    .mp-flow-explanation {
        padding: 17px;
    }


    .mp-flow-title {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-bottom: 15px;

        color: #16344c;

        font-size: 12px;
        font-weight: 800;
    }


    .mp-flow-title i {
        color: #0b9876;
    }


    .mp-flow-steps {
        display: flex;
        align-items: stretch;

        gap: 8px;
    }


    .mp-flow-steps > div {
        flex: 1;

        min-width: 0;

        padding: 12px;

        border-radius: 10px;

        background: #f5f8f9;
    }


    .mp-flow-steps .number {
        width: 22px;
        height: 22px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        margin-bottom: 8px;

        border-radius: 7px;

        background: #e4f7f1;

        color: #078a68;

        font-size: 10px;
        font-weight: 800;
    }


    .mp-flow-steps strong {
        display: block;

        margin-bottom: 4px;

        color: #19374d;

        font-size: 8.5px;
    }


    .mp-flow-steps small {
        display: block;

        color: #8393a0;

        font-size: 9px;
        line-height: 1.45;
    }


    .mp-flow-steps .arrow {
        display: flex;
        align-items: center;

        color: #9aa9b5;

        font-size: 14px;
    }


    /*
    |--------------------------------------------------------------------------
    | Dark Theme
    |--------------------------------------------------------------------------
    */

    html[data-admin-theme="dark"] .mp-kpi-card,
    html[data-admin-theme="dark"] .mp-revenue-card {
        color: inherit;
    }


    html[data-admin-theme="dark"] .mp-kpi-value,
    html[data-admin-theme="dark"] .mp-dashboard-hero h2,
    html[data-admin-theme="dark"] .mp-card-heading h3,
    html[data-admin-theme="dark"] .mp-mini-card > strong,
    html[data-admin-theme="dark"] .mp-table td strong,
    html[data-admin-theme="dark"] .mp-flow-title,
    html[data-admin-theme="dark"] .mp-flow-steps strong {
        color: var(--admin-text, #edf4f7);
    }


    html[data-admin-theme="dark"] .mp-margin-values > div,
    html[data-admin-theme="dark"] .mp-flow-steps > div {
        background: rgba(255, 255, 255, .045);
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 1180px) {

        .mp-kpi-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }


        .mp-mini-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }


        .mp-main-grid {
            grid-template-columns: 1fr;
        }


        .mp-three-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }

    }


    @media(max-width: 850px) {

        .mp-revenue-grid,
        .mp-two-grid,
        .mp-three-grid {
            grid-template-columns: 1fr;
        }


        .mp-dashboard-hero {
            align-items: flex-start;
            flex-direction: column;
        }


        .mp-chart-legend {
            justify-content: flex-start;
        }


        .mp-card-heading {
            flex-direction: column;
        }


        .mp-flow-steps {
            flex-direction: column;
        }


        .mp-flow-steps .arrow {
            display: none;
        }

    }


    @media(max-width: 590px) {

        .mp-kpi-grid,
        .mp-mini-grid,
        .mp-margin-values {
            grid-template-columns: 1fr;
        }


        .mp-dashboard-meta {
            align-items: flex-start;
            flex-direction: column;

            gap: 6px;
        }


        .mp-revenue-card {
            grid-template-columns:
                auto 1fr;
        }


        .mp-revenue-trend {
            grid-column: 2;
        }

    }

</style>

@endpush



{{-- ========================================================================
    CHARTS
========================================================================= --}}

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Data
        |--------------------------------------------------------------------------
        */

        const dashboardData =
            @json($dashboardChartData);


        const safeNumber =
            function (value) {

                const number =
                    Number(value);

                return Number.isFinite(number)
                    ? number
                    : 0;
            };


        /*
        |--------------------------------------------------------------------------
        | Money Formatter
        |--------------------------------------------------------------------------
        */

        const moneyFormatter =
            function (value) {

                value =
                    safeNumber(value);


                if (
                    Math.abs(value)
                    >=
                    1000000000
                ) {

                    return '₦'
                        +
                        (
                            value
                            /
                            1000000000
                        )
                        .toFixed(1)
                        +
                        'B';
                }


                if (
                    Math.abs(value)
                    >=
                    1000000
                ) {

                    return '₦'
                        +
                        (
                            value
                            /
                            1000000
                        )
                        .toFixed(1)
                        +
                        'M';
                }


                if (
                    Math.abs(value)
                    >=
                    1000
                ) {

                    return '₦'
                        +
                        (
                            value
                            /
                            1000
                        )
                        .toFixed(1)
                        +
                        'K';
                }


                return '₦'
                    +
                    Math.round(
                        value
                    )
                    .toLocaleString();
            };


        /*
        |--------------------------------------------------------------------------
        | Theme
        |--------------------------------------------------------------------------
        */

        const isDark =
            document
                .documentElement
                .getAttribute(
                    'data-admin-theme'
                )
            ===
            'dark';


        const textColor =
            isDark
                ? '#aebdcc'
                : '#6f8295';


        const gridColor =
            isDark
                ? 'rgba(255,255,255,.08)'
                : '#edf1f4';


        /*
        |--------------------------------------------------------------------------
        | Money Flow
        |--------------------------------------------------------------------------
        */

        const moneyFlowElement =
            document.getElementById(
                'moneyFlowChart'
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
                                show: false
                            },
                            zoom: {
                                enabled: false
                            },
                            fontFamily: 'Inter, sans-serif'
                        },


                        series: [

                            {
                                name: 'Buyer paid',

                                data:
                                    dashboardData
                                        .chart
                                        .buyer_paid
                                        .map(
                                            safeNumber
                                        )
                            },

                            {
                                name: 'Released to seller wallet',

                                data:
                                    dashboardData
                                        .chart
                                        .seller_wallet_released
                                        .map(
                                            safeNumber
                                        )
                            },

                            {
                                name: 'Withdrawn to seller bank',

                                data:
                                    dashboardData
                                        .chart
                                        .seller_withdrawn
                                        .map(
                                            safeNumber
                                        )
                            },

                            {
                                name: 'Platform profit',

                                data:
                                    dashboardData
                                        .chart
                                        .platform_profit
                                        .map(
                                            safeNumber
                                        )
                            }
                        ],


                        colors: [
                            '#12a98b',
                            '#765ada',
                            '#3776d7',
                            '#e88927'
                        ],


                        stroke: {
                            width: [
                                2.5,
                                2,
                                2,
                                2
                            ],

                            curve: 'smooth'
                        },


                        fill: {
                            type: 'gradient',

                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: .18,
                                opacityTo: .015,
                                stops: [
                                    0,
                                    90,
                                    100
                                ]
                            }
                        },


                        dataLabels: {
                            enabled: false
                        },


                        markers: {
                            size: 0,

                            hover: {
                                size: 5
                            }
                        },


                        xaxis: {

                            categories:
                                dashboardData
                                    .chart
                                    .labels,

                            labels: {
                                style: {
                                    colors:
                                        textColor,

                                    fontSize:
                                        '8px'
                                }
                            },

                            axisBorder: {
                                show: false
                            },

                            axisTicks: {
                                show: false
                            }
                        },


                        yaxis: {

                            labels: {

                                formatter:
                                    moneyFormatter,

                                style: {
                                    colors:
                                        textColor,

                                    fontSize:
                                        '8px'
                                }
                            }
                        },


                        grid: {

                            borderColor:
                                gridColor,

                            strokeDashArray:
                                3
                        },


                        tooltip: {

                            shared: true,

                            intersect: false,

                            y: {

                                formatter:
                                    function (value) {

                                        return '₦'
                                            +
                                            safeNumber(
                                                value
                                            )
                                            .toLocaleString(
                                                undefined,
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                }
                                            );
                                    }
                            }
                        },


                        legend: {
                            show: false
                        }
                    }
                );


            moneyFlowChart
                .render();
        }


        /*
        |--------------------------------------------------------------------------
        | Profit Margin
        |--------------------------------------------------------------------------
        */

        const marginElement =
            document.getElementById(
                'profitMarginChart'
            );


        if (
            marginElement
            &&
            window.ApexCharts
        ) {

            const margin =
                Math.min(
                    100,
                    Math.max(
                        0,
                        safeNumber(
                            dashboardData
                                .profitMargin
                        )
                    )
                );


            const marginChart =
                new ApexCharts(
                    marginElement,
                    {

                        chart: {
                            type: 'radialBar',
                            height: 230,

                            sparkline: {
                                enabled: true
                            }
                        },


                        series: [
                            margin
                        ],


                        colors: [
                            '#18a98b'
                        ],


                        plotOptions: {

                            radialBar: {

                                hollow: {
                                    size: '66%'
                                },

                                track: {
                                    background:
                                        isDark
                                            ? 'rgba(255,255,255,.08)'
                                            : '#e9eff2'
                                },

                                dataLabels: {

                                    name: {
                                        show: true,

                                        offsetY: 22,

                                        color:
                                            textColor,

                                        fontSize:
                                            '8px'
                                    },

                                    value: {
                                        offsetY: -8,

                                        color:
                                            isDark
                                                ? '#ffffff'
                                                : '#0b2943',

                                        fontSize:
                                            '21px',

                                        fontWeight:
                                            800,

                                        formatter:
                                            function (value) {

                                                return safeNumber(
                                                    value
                                                )
                                                .toFixed(
                                                    1
                                                )
                                                +
                                                '%';
                                            }
                                    }
                                }
                            }
                        },


                        labels: [
                            'Gross platform margin'
                        ]
                    }
                );


            marginChart
                .render();
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction Status
        |--------------------------------------------------------------------------
        */

        const transactionElement =
            document.getElementById(
                'transactionStatusChart'
            );


        if (
            transactionElement
            &&
            window.ApexCharts
        ) {

            const transactionChart =
                new ApexCharts(
                    transactionElement,
                    {

                        chart: {
                            type: 'donut',
                            height: 255
                        },


                        series:
                            dashboardData
                                .transactionStatus
                                .series
                                .map(
                                    safeNumber
                                ),


                        labels:
                            dashboardData
                                .transactionStatus
                                .labels,


                        colors: [
                            '#3776d7',
                            '#14a879',
                            '#e28b29',
                            '#d65353'
                        ],


                        stroke: {
                            width: 2,

                            colors: [
                                isDark
                                    ? '#17212b'
                                    : '#ffffff'
                            ]
                        },


                        dataLabels: {
                            enabled: false
                        },


                        legend: {

                            position: 'bottom',

                            labels: {
                                colors:
                                    textColor
                            },

                            fontSize:
                                '8px'
                        },


                        plotOptions: {

                            pie: {

                                donut: {
                                    size: '70%'
                                }
                            }
                        },


                        noData: {
                            text: 'No transaction data'
                        }
                    }
                );


            transactionChart
                .render();
        }


        /*
        |--------------------------------------------------------------------------
        | Withdrawal Status
        |--------------------------------------------------------------------------
        */

        const withdrawalElement =
            document.getElementById(
                'withdrawalStatusChart'
            );


        if (
            withdrawalElement
            &&
            window.ApexCharts
        ) {

            const withdrawalChart =
                new ApexCharts(
                    withdrawalElement,
                    {

                        chart: {
                            type: 'donut',
                            height: 255
                        },


                        series:
                            dashboardData
                                .withdrawalStatus
                                .series
                                .map(
                                    safeNumber
                                ),


                        labels:
                            dashboardData
                                .withdrawalStatus
                                .labels,


                        colors: [
                            '#13a879',
                            '#3878d6',
                            '#d45151'
                        ],


                        stroke: {

                            width: 2,

                            colors: [
                                isDark
                                    ? '#17212b'
                                    : '#ffffff'
                            ]
                        },


                        dataLabels: {
                            enabled: false
                        },


                        legend: {

                            position:
                                'bottom',

                            labels: {
                                colors:
                                    textColor
                            },

                            fontSize:
                                '8px'
                        },


                        plotOptions: {

                            pie: {

                                donut: {
                                    size:
                                        '70%'
                                }
                            }
                        },


                        noData: {
                            text:
                                'No withdrawal data'
                        }
                    }
                );


            withdrawalChart
                .render();
        }


        /*
        |--------------------------------------------------------------------------
        | Profit Composition
        |--------------------------------------------------------------------------
        */

        const profitElement =
            document.getElementById(
                'profitCompositionChart'
            );


        if (
            profitElement
            &&
            window.ApexCharts
        ) {

            const profitChart =
                new ApexCharts(
                    profitElement,
                    {

                        chart: {
                            type: 'donut',
                            height: 255
                        },


                        series:
                            dashboardData
                                .profitComposition
                                .series
                                .map(
                                    safeNumber
                                ),


                        labels:
                            dashboardData
                                .profitComposition
                                .labels,


                        colors: [
                            '#13a98a',
                            '#775bdc'
                        ],


                        stroke: {

                            width:
                                2,

                            colors: [
                                isDark
                                    ? '#17212b'
                                    : '#ffffff'
                            ]
                        },


                        dataLabels: {
                            enabled:
                                false
                        },


                        legend: {

                            position:
                                'bottom',

                            labels: {
                                colors:
                                    textColor
                            },

                            fontSize:
                                '8px'
                        },


                        plotOptions: {

                            pie: {

                                donut: {
                                    size:
                                        '70%'
                                }
                            }
                        },


                        noData: {
                            text:
                                'No platform revenue yet'
                        }
                    }
                );


            profitChart
                .render();
        }

    }
);

</script>

@endpush