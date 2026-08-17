@extends('seller.layouts.app')

@section('title', 'Seller Dashboard')

@php
    $dashboardRole = 'seller';

    $firstName =
        explode(
            ' ',
            trim($user->name)
        )[0];

    $hour = now()->hour;

    $greeting =
        $hour < 12
            ? 'Good morning'
            : (
                $hour < 17
                    ? 'Good afternoon'
                    : 'Good evening'
            );

    $featuredNextStatus =
        $featuredTransaction
            ? $featuredTransaction->nextSellerStatus()
            : null;

    $featuredAction =
        match($featuredNextStatus) {
            \App\Models\SecureTransaction::STATUS_PREPARING_ITEM => [
                'label' => 'Start preparing',
                'icon' => 'fa-box-open',
            ],

            \App\Models\SecureTransaction::STATUS_DISPATCHED => [
                'label' => 'Item dispatched',
                'icon' => 'fa-box',
            ],

            \App\Models\SecureTransaction::STATUS_IN_TRANSIT => [
                'label' => 'Mark in transit',
                'icon' => 'fa-truck-fast',
            ],

            \App\Models\SecureTransaction::STATUS_DELIVERED => [
                'label' => 'Mark delivered',
                'icon' => 'fa-box-circle-check',
            ],

            default => null,
        };

    $featuredBadge =
        $featuredTransaction
            ? match($featuredTransaction->status) {
                \App\Models\SecureTransaction::STATUS_PAYMENT_SECURED =>
                    'Payment received · Ready to prepare',

                \App\Models\SecureTransaction::STATUS_PREPARING_ITEM =>
                    'Preparing item · Dispatch when ready',

                \App\Models\SecureTransaction::STATUS_DISPATCHED =>
                    'Item dispatched · Update transit status',

                \App\Models\SecureTransaction::STATUS_IN_TRANSIT =>
                    'In transit · Mark delivered when it arrives',

                \App\Models\SecureTransaction::STATUS_DELIVERED =>
                    'Delivered · Waiting for buyer',

                \App\Models\SecureTransaction::STATUS_INSPECTION =>
                    'Inspection in progress',

                \App\Models\SecureTransaction::STATUS_DISPUTED =>
                    'Dispute open · Payout paused',

                \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED =>
                    'Funds release approved',

                \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING =>
                    'Payout processing',

                default => $featuredTransaction->status_label,
            }
            : null;

    $featuredBadgeClass =
        $featuredTransaction
            ? match($featuredTransaction->status) {
                \App\Models\SecureTransaction::STATUS_DISPUTED => 'red',
                \App\Models\SecureTransaction::STATUS_INSPECTION => 'purple',
                \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED,
                \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING => 'green',
                default => 'green',
            }
            : 'green';

    $featuredPayout =
        $featuredTransaction
            ? (
                (float) $featuredTransaction->seller_net_amount > 0
                    ? (float) $featuredTransaction->seller_net_amount
                    : (float) $featuredTransaction->total_amount
            )
            : 0;

    $serviceFeeRate =
        $featuredTransaction
            ? rtrim(
                rtrim(
                    number_format(
                        (float) ($featuredTransaction->service_fee_rate ?: 5),
                        2
                    ),
                    '0'
                ),
                '.'
            )
            : '5';

    $vatRate =
        $featuredTransaction
            ? rtrim(
                rtrim(
                    number_format(
                        (float) ($featuredTransaction->vat_rate ?: 7.5),
                        2
                    ),
                    '0'
                ),
                '.'
            )
            : '7.5';
@endphp

@section('content')

{{-- =========================================================
    DASHBOARD HEADER
========================================================== --}}

<div class="dashboard-page-header">

    <div>

        <h1 class="dashboard-page-title">
            {{ $greeting }}, {{ $firstName }}

            <span class="dashboard-wave">
                👋
            </span>
        </h1>

        <p class="dashboard-page-subtitle">
            {{ $seller['business_name'] }}
            <span>·</span>
            Seller account
            <span>·</span>
            {{ $seller['location'] }}
        </p>

    </div>
    <section class="seller-wallet-overview dashboard-card">

        <div class="seller-wallet-overview-left">

            <div class="seller-wallet-overview-icon">

                <i class="fa-solid fa-wallet"></i>

            </div>


            <div>

                <span class="seller-wallet-eyebrow">
                    Available Midpoint balance
                </span>


                <strong class="seller-wallet-amount">
                    {{ $walletSummary['formatted_available_balance'] }}
                </strong>


                <p>
                    Completed transaction funds collect here.
                    They are not sent automatically to your bank account.
                </p>

            </div>

        </div>


        <div class="seller-wallet-overview-right">

            <div class="seller-wallet-mini-stat">

                <span>
                    Total released to wallet
                </span>


                <strong>
                    {{ $walletSummary['formatted_total_credited'] }}
                </strong>

            </div>


            <a
                href="{{ route('seller.wallet') }}"
                class="seller-wallet-coming-soon"
                style="text-decoration:none;"
            >

                <i class="fa-solid fa-money-bill-transfer"></i>

                <span>
                    Manage bank accounts, KYC and withdraw funds →
                </span>

            </a>

        </div>

    </section>
    <a
        href="{{ route('seller.transactions.create') }}"
        class="dashboard-primary-button"
    >
        <i class="fa-solid fa-plus"></i>
        Create transaction
    </a>

</div>


{{-- =========================================================
    FEATURED ACTIVE TRANSACTION
========================================================== --}}

@if($featuredTransaction)

    <section class="seller-highlight-card">

        <div class="seller-highlight-content">

            <div class="seller-highlight-left">

                <span class="dashboard-badge {{ $featuredBadgeClass }}">
                    <i class="fa-solid fa-sack-dollar"></i>
                    {{ $featuredBadge }}
                </span>

                <h2>
                    {{ $featuredTransaction->title }}
                    ·
                    ₦{{ number_format((float) $featuredTransaction->total_amount, 0) }} held
                </h2>

                <p class="dashboard-muted">
                    Buyer:
                    {{ $featuredTransaction->buyer?->name ?: $featuredTransaction->buyer_email }}

                    <span>·</span>

                    {{ $featuredTransaction->reference }}

                    @if($featuredTransaction->paid_at)
                        <span>·</span>
                        Paid {{ $featuredTransaction->paid_at->diffForHumans() }}
                    @endif
                </p>

                <div class="seller-delivery-box">

                    <strong>
                        Delivery details
                    </strong>

                    <span>
                        {{ $featuredTransaction->delivery_note ?: 'Seller-arranged delivery. No extra delivery note was provided.' }}
                    </span>

                    <span>
                        {{ $featuredTransaction->buyer_phone ?: 'No buyer phone provided' }}
                        ·
                        {{ $featuredTransaction->buyer?->name ?: $featuredTransaction->buyer_email }}
                    </span>

                </div>

                <p class="seller-instruction">
                    @if($featuredAction)
                        The buyer's payment is secured. Complete the next fulfilment step only after it has actually happened.
                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DISPUTED)
                        Seller payout is paused while Midpoint reviews this dispute. Open the transaction for the latest dispute status.
                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_INSPECTION)
                        The buyer is inspecting the item. Funds remain protected until the buyer accepts or the inspection period completes.
                    @else
                        No seller fulfilment action is required right now. Open the transaction to see the latest status.
                    @endif
                </p>

                <div class="dashboard-action-row">

                    @if($featuredAction)

                        <form
                            method="POST"
                            action="{{ route('seller.transactions.status.update', $featuredTransaction) }}"
                            class="dashboard-inline-form"
                        >
                            @csrf
                            @method('PATCH')

                            <input
                                type="hidden"
                                name="status"
                                value="{{ $featuredNextStatus }}"
                            >

                            <button
                                type="submit"
                                class="dashboard-green-button"
                            >
                                <i class="fa-solid {{ $featuredAction['icon'] }}"></i>
                                {{ $featuredAction['label'] }}
                            </button>
                        </form>

                    @endif

                    <a
                        href="{{ route('seller.transactions.show', $featuredTransaction) }}"
                        class="dashboard-text-button"
                    >
                        View transaction
                    </a>

                </div>

            </div>

            <div class="seller-highlight-payout">

                <span class="dashboard-muted">
                    Your Midpoint balance credit
                </span>

                <div class="seller-payout-box">
                    <strong>
                        ₦{{ number_format($featuredPayout, 0) }}
                    </strong>

                    <span>
                        after {{ $serviceFeeRate }}% fee + {{ $vatRate }}% VAT on fee
                    </span>
                </div>

                <p>
                    Released after buyer acceptance, automatic completion, or a resolved dispute that approves seller payout.
                    Added to your Midpoint balance after buyer acceptance,
                    automatic completion, or an eligible resolved dispute.
                </p>

            </div>

        </div>

    </section>

@else

    <section class="seller-highlight-card dashboard-empty-highlight">

        <div class="dashboard-empty-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div>
            <strong>
                No funded transaction needs your attention
            </strong>

            <p>
                When a buyer completes payment, the transaction will appear here with the exact next fulfilment action.
            </p>
        </div>

        <a
            href="{{ route('seller.transactions.create') }}"
            class="dashboard-outline-button"
        >
            <i class="fa-solid fa-plus"></i>
            Create transaction
        </a>

    </section>

@endif


{{-- =========================================================
    STATISTICS
========================================================== --}}

<div class="seller-stat-grid">

    @foreach($statistics as $stat)

        <article class="dashboard-card dashboard-stat-card">

            <span class="dashboard-stat-label">
                {{ $stat['label'] }}
            </span>

            <strong class="dashboard-stat-value">
                {{ $stat['value'] }}

                @if(!empty($stat['suffix']))
                    <small>
                        {{ $stat['suffix'] }}
                    </small>
                @endif
            </strong>

            <span class="dashboard-stat-note {{ $stat['class'] ?? '' }}">
                {{ $stat['note'] }}
            </span>

        </article>

    @endforeach

</div>


{{-- =========================================================
    LOWER DASHBOARD
========================================================== --}}

<div class="seller-dashboard-grid">

    {{-- Recent transactions --}}
    <section class="dashboard-card dashboard-table-card">

        <div class="dashboard-card-header">
            <strong>
                Recent transactions
            </strong>

            <a href="{{ route('seller.transactions') }}">
                View all
            </a>
        </div>

        @if($transactions->isNotEmpty())

            <div class="dashboard-table-scroll">

                <table class="dashboard-table">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($transactions as $transaction)

                            <tr>

                                <td>
                                    <a
                                        href="{{ $transaction['url'] }}"
                                        class="dashboard-table-link"
                                    >
                                        <strong>
                                            {{ $transaction['product'] }}
                                        </strong>

                                        <small>
                                            {{ $transaction['reference'] }}
                                        </small>
                                    </a>
                                </td>

                                <td>
                                    {{ $transaction['buyer'] }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $transaction['amount'] }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="dashboard-badge {{ $transaction['status_class'] }}">
                                        {{ $transaction['status'] }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="dashboard-empty-table">
                <i class="fa-regular fa-file-lines"></i>

                <strong>
                    No transactions yet
                </strong>

                <span>
                    Create your first transaction to start selling securely through Midpoint.
                </span>
            </div>

        @endif

    </section>


    {{-- Right column --}}
    <div class="seller-dashboard-side">

        {{-- Revenue chart --}}
        <section class="dashboard-card dashboard-side-card dashboard-revenue-card">

            <strong class="dashboard-side-title">
                Revenue summary · {{ $revenueSummary['month'] }}
            </strong>

            <div
                class="revenue-chart dashboard-revenue-chart"
                aria-label="Seller net payout chart for {{ $revenueSummary['month'] }}"
            >

                @foreach($revenueSummary['bars'] as $bar)

                    <div
                        class="revenue-bar-slot"
                        tabindex="0"
                        aria-label="{{ $bar['label'] }}: {{ $bar['formatted'] }}"
                    >

                        <div
                            class="revenue-tooltip"
                            role="tooltip"
                        >
                            <span>
                                {{ $bar['label'] }}
                            </span>

                            <strong>
                                {{ $bar['formatted'] }}
                            </strong>
                        </div>

                        <div
                            class="revenue-bar {{ $bar['strong'] ? 'strong' : 'light' }} {{ $bar['value'] <= 0 ? 'is-zero' : '' }}"
                            style="height: {{ $bar['height'] }}%;"
                            aria-hidden="true"
                        ></div>

                    </div>

                @endforeach

            </div>

            <div class="revenue-labels dashboard-revenue-labels">
                @foreach($revenueSummary['bars'] as $bar)
                    <span>
                        {{ $bar['label'] }}
                    </span>
                @endforeach
            </div>

            <div class="revenue-total">
                <span>
                    Net payouts after fees + VAT
                </span>

                <strong>
                    {{ $revenueSummary['formatted_total'] }}
                </strong>
            </div>

        </section>


        {{-- Notifications --}}
        <section class="dashboard-card dashboard-side-card">

            <div class="dashboard-side-heading">
                <strong>
                    Notifications
                </strong>

                @if($unreadNotificationCount > 0)
                    <span class="dashboard-notification-count">
                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                    </span>
                @endif
            </div>

            <div class="dashboard-notification-list">

                @forelse($notifications as $notification)

                    <a
                        href="{{ $notification['url'] }}"
                        class="dashboard-notification-item dashboard-notification-link {{ $notification['unread'] ? 'is-unread' : '' }}"
                    >
                        <i class="fa-solid {{ $notification['icon'] }}"></i>

                        <p>
                            <strong>
                                {{ $notification['title'] }}
                            </strong>

                            —

                            {{ $notification['message'] }}
                        </p>
                    </a>

                @empty

                    <div class="dashboard-mini-empty">
                        <i class="fa-regular fa-bell"></i>
                        <span>No transaction notifications yet.</span>
                    </div>

                @endforelse

            </div>

            <a
                href="{{ route('seller.notifications') }}"
                class="dashboard-outline-button full"
            >
                Open notification centre
            </a>

        </section>


        {{-- Quick actions --}}
        <section class="dashboard-card dashboard-side-card">

            <strong class="dashboard-side-title">
                Quick actions
            </strong>

            <div class="dashboard-quick-actions">

                <a
                    href="{{ route('seller.transactions.create') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-plus purple-icon"></i>
                    New transaction
                </a>

                @if($featuredTransaction)
                    <a
                        href="{{ route('seller.transactions.show', $featuredTransaction) }}"
                        class="dashboard-outline-button"
                    >
                        <i class="fa-solid fa-box"></i>
                        Continue active order
                    </a>
                @else
                    <a
                        href="{{ route('seller.transactions') }}"
                        class="dashboard-outline-button"
                    >
                        <i class="fa-solid fa-box"></i>
                        View transactions
                    </a>
                @endif

                <a
                    href="{{ route('seller.profile-settings') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-building-columns"></i>
                    Update payout bank
                </a>

                <a
                    href="{{ route('seller.business-profile') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-store"></i>
                    Edit business profile
                </a>

                <a
                    href="{{ route('verified-sellers') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-star"></i>
                    Manage verified package
                </a>

                <a
                    href="{{ route('support') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-regular fa-comments"></i>
                    Contact support
                </a>

            </div>

        </section>

    </div>

</div>

@endsection


@push('styles')
<style>
    /* =========================================================
       DASHBOARD FUNCTIONAL ADDITIONS
    ========================================================= */
.seller-wallet-overview {
    margin-bottom: 20px;

    padding:
        20px
        22px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 24px;

    border-color:
        var(--mp-mint-2);
}


.seller-wallet-overview-left {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 16px;
}


.seller-wallet-overview-icon {
    width: 52px;

    height: 52px;

    flex: 0 0 52px;

    display: grid;

    place-items: center;

    border-radius: 15px;

    background:
        var(--mp-mint);

    color:
        var(--mp-forest);

    font-size: 20px;
}


.seller-wallet-eyebrow {
    display: block;

    color:
        var(--mp-slate);

    font-size: 12px;

    font-weight: 700;
}


.seller-wallet-amount {
    display: block;

    margin-top: 3px;

    color:
        var(--mp-ink);

    font-family:
        'Bricolage Grotesque',
        sans-serif;

    font-size: 30px;

    line-height: 1.1;
}


.seller-wallet-overview-left p {
    margin:
        5px
        0
        0;

    color:
        var(--mp-slate);

    font-size: 12px;

    line-height: 1.5;
}


.seller-wallet-overview-right {
    width: 290px;

    min-width: 290px;

    display: flex;

    flex-direction: column;

    gap: 9px;
}


.seller-wallet-mini-stat {
    padding:
        10px
        12px;

    border:
        1px
        solid
        var(--mp-line);

    border-radius: 11px;

    background:
        var(--mp-paper);

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    font-size: 11px;

    color:
        var(--mp-slate);
}


.seller-wallet-mini-stat strong {
    color:
        var(--mp-forest);

    font-size: 13px;
}


.seller-wallet-coming-soon {
    display: flex;

    align-items: flex-start;

    gap: 7px;

    color:
        var(--mp-slate);

    font-size: 11px;

    line-height: 1.45;
}


.seller-wallet-coming-soon i {
    margin-top: 2px;

    color:
        var(--mp-emerald);
}


@media (max-width: 760px) {

    .seller-wallet-overview {
        align-items: stretch;

        flex-direction: column;
    }


    .seller-wallet-overview-right {
        width: 100%;

        min-width: 0;
    }
}
    .dashboard-inline-form {
        margin: 0;
    }

    .dashboard-badge.red {
        background: var(--mp-red-bg);
        color: var(--mp-red);
    }

    .dashboard-stat-note.negative {
        color: var(--mp-red);
    }

    .dashboard-table-link {
        color: inherit;
        display: inline-block;
    }

    .dashboard-table-link:hover strong {
        color: var(--mp-emerald);
    }

    .dashboard-empty-highlight {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .dashboard-empty-highlight > div:nth-child(2) {
        min-width: 0;
        flex: 1;
    }

    .dashboard-empty-highlight strong {
        display: block;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .dashboard-empty-highlight p {
        margin: 0;
        color: var(--mp-slate);
        font-size: 12px;
        line-height: 1.55;
    }

    .dashboard-empty-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--mp-mint);
        color: var(--mp-forest);
    }

    .dashboard-empty-table {
        min-height: 190px;
        padding: 26px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--mp-slate);
    }

    .dashboard-empty-table > i {
        margin-bottom: 9px;
        color: var(--mp-muted);
        font-size: 24px;
    }

    .dashboard-empty-table strong {
        color: var(--mp-ink);
        font-size: 13px;
    }

    .dashboard-empty-table span {
        max-width: 360px;
        margin-top: 4px;
        font-size: 11px;
        line-height: 1.5;
    }

    .dashboard-notification-link {
        padding: 7px 8px;
        margin: 0 -8px;
        border-radius: 9px;
        color: var(--mp-ink);
        transition: background .18s ease;
    }

    .dashboard-notification-link:hover {
        background: #F8FAF9;
        color: var(--mp-ink);
    }

    .dashboard-notification-link.is-unread {
        background: #F4FBF7;
    }

    .dashboard-mini-empty {
        min-height: 72px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        color: var(--mp-slate);
        font-size: 11px;
        text-align: center;
    }

    /* =========================================================
       REVENUE TOOLTIP CHART
    ========================================================= */

    .dashboard-revenue-card {
        overflow: visible;
    }

    .dashboard-revenue-chart {
        position: relative;
        overflow: visible;
        align-items: stretch;
    }

    .revenue-bar-slot {
        position: relative;
        min-width: 0;
        height: 100%;
        flex: 1;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        outline: none;
        cursor: default;
    }

    .revenue-bar-slot .revenue-bar {
        width: 100%;
        flex: none;
        min-height: 0;
        transition:
            height .25s ease,
            filter .18s ease,
            transform .18s ease;
    }

    .revenue-bar-slot:hover .revenue-bar,
    .revenue-bar-slot:focus .revenue-bar {
        filter: brightness(.97);
        transform: translateY(-2px);
    }

    .revenue-bar-slot .revenue-bar.is-zero {
        height: 3px !important;
        background: #E7ECE9;
    }

    .revenue-tooltip {
        position: absolute;
        left: 50%;
        top: -7px;
        z-index: 20;
        min-width: 102px;
        padding: 8px 9px;
        border-radius: 9px;
        background: #101915;
        color: #FFFFFF;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .16);
        text-align: center;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, -100%) translateY(5px);
        transition:
            opacity .16s ease,
            transform .16s ease,
            visibility .16s ease;
    }

    .revenue-tooltip::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 100%;
        border: 5px solid transparent;
        border-top-color: #101915;
        transform: translateX(-50%);
    }

    .revenue-tooltip span,
    .revenue-tooltip strong {
        display: block;
    }

    .revenue-tooltip span {
        margin-bottom: 2px;
        color: #C9D2CD;
        font-size: 10px;
    }

    .revenue-tooltip strong {
        color: #FFFFFF;
        font-size: 12px;
        white-space: nowrap;
    }

    .revenue-bar-slot:hover .revenue-tooltip,
    .revenue-bar-slot:focus .revenue-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -100%) translateY(0);
    }

    .dashboard-revenue-labels {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        text-align: center;
    }

    @media (max-width: 640px) {
        .dashboard-empty-highlight {
            align-items: stretch;
            flex-direction: column;
        }

        .dashboard-empty-highlight .dashboard-outline-button {
            width: 100%;
        }

        .dashboard-revenue-chart {
            height: 130px;
        }

        .revenue-tooltip {
            min-width: 92px;
        }
    }

    @media (hover: none) {
        .revenue-bar-slot:focus .revenue-tooltip {
            opacity: 1;
            visibility: visible;
        }
    }
</style>
@endpush
