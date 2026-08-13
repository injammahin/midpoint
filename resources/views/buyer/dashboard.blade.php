@extends('buyer.layouts.app')

@section('title', 'Buyer Dashboard')

@php
    $dashboardRole = 'buyer';

    $firstName =
        explode(
            ' ',
            trim($user->name)
        )[0];

    $featuredSellerName =
        $featuredTransaction
            ? (
                $featuredTransaction
                    ->seller
                    ?->activeSellerSubscription
                    ?->application
                    ?->business_name
                ?: $featuredTransaction->seller?->name
                ?: 'Seller'
            )
            : null;

    $featuredDispute =
        $featuredTransaction
            ? $featuredTransaction->dispute
            : null;

    $hasActiveDispute =
        $featuredDispute
        &&
        $featuredDispute->status
            !==
            \App\Models\TransactionDispute::STATUS_RESOLVED;

    $featuredBadge =
        $featuredTransaction
            ? match($featuredTransaction->status) {
                \App\Models\SecureTransaction::STATUS_DELIVERED =>
                    'Action needed · Confirm receipt',

                \App\Models\SecureTransaction::STATUS_INSPECTION =>
                    'Inspection in progress',

                \App\Models\SecureTransaction::STATUS_DISPUTED =>
                    'Dispute open · MidPoint review',

                \App\Models\SecureTransaction::STATUS_PAYMENT_SECURED =>
                    'Payment protected · Seller preparing order',

                \App\Models\SecureTransaction::STATUS_PREPARING_ITEM =>
                    'Seller preparing your item',

                \App\Models\SecureTransaction::STATUS_DISPATCHED =>
                    'Item dispatched',

                \App\Models\SecureTransaction::STATUS_IN_TRANSIT =>
                    'Order in transit',

                \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED =>
                    'Release approved',

                \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING =>
                    'Seller payout processing',

                default => $featuredTransaction->status_label,
            }
            : null;

    $featuredBadgeClass =
        $featuredTransaction
            ? match($featuredTransaction->status) {
                \App\Models\SecureTransaction::STATUS_DISPUTED => 'red',
                \App\Models\SecureTransaction::STATUS_DELIVERED,
                \App\Models\SecureTransaction::STATUS_INSPECTION => 'purple',
                \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED,
                \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING => 'green',
                default => 'amber',
            }
            : 'purple';

    $featuredPaidAmount =
        $featuredTransaction
            ? (float) (
                $featuredTransaction->paid_amount
                ?: $featuredTransaction->total_amount
            )
            : 0;
@endphp

@section('content')

{{-- =========================================================
    PAGE HEADER
========================================================== --}}

<div class="dashboard-page-header buyer-page-header">

    <div>

        <h1 class="dashboard-page-title">
            Hi {{ $firstName }}

            <span class="dashboard-wave">
                👋
            </span>
        </h1>

        <p class="dashboard-page-subtitle">
            Buyer account
            <span>·</span>
            {{ $buyer['location'] }}
        </p>

    </div>

</div>


{{-- =========================================================
    BUYER SUMMARY
========================================================== --}}

<div class="buyer-summary-grid">

    <section class="buyer-protection-card">

        <span class="buyer-protection-label">
            Your money protected in escrow
        </span>

        <strong class="buyer-protection-amount">
            {{ $statistics['escrow'] }}
        </strong>

        <p>
            {{ $statistics['purchases_in_progress'] }}
            {{ $statistics['purchases_in_progress'] === 1 ? 'purchase' : 'purchases' }}
            in progress · Funds stay protected until the transaction reaches a valid release stage.
        </p>

        <div class="buyer-protection-actions">

            <a
                href="{{ route('featured-businesses') }}"
                class="buyer-white-button"
            >
                <i class="fa-solid fa-shield-halved"></i>
                Buy securely
            </a>

            <a
                href="{{ route('buyer.seller-invite') }}"
                class="buyer-purple-button"
            >
                <i class="fa-solid fa-link"></i>
                Open a seller invite
            </a>

        </div>

    </section>


    <div class="buyer-summary-stats">

        <div class="buyer-small-stats">

            <article class="dashboard-card buyer-stat-card">
                <span>
                    Trust score
                </span>

                <strong>
                    <i class="fa-regular fa-shield"></i>
                    {{ $statistics['trust_score'] }}

                    @if($statistics['trust_score_suffix'])
                        <small class="buyer-trust-suffix">
                            {{ $statistics['trust_score_suffix'] }}
                        </small>
                    @endif
                </strong>
            </article>

            <article class="dashboard-card buyer-stat-card">
                <span>
                    Purchases
                </span>

                <strong>
                    {{ $statistics['purchases'] }}
                </strong>
            </article>

        </div>

        <article class="dashboard-card buyer-lifetime-card">
            <span>
                Protected from risk (lifetime)
            </span>

            <strong>
                {{ $statistics['protected_lifetime'] }}
            </strong>

            <p>
                Total successfully paid transactions protected by MidPoint.
            </p>
        </article>

    </div>

</div>


{{-- =========================================================
    FEATURED ACTIVE TRANSACTION
========================================================== --}}

@if($featuredTransaction)

    <section class="buyer-action-card {{ $featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DISPUTED ? 'is-disputed' : '' }}">

        <div class="buyer-action-content">

            <div>

                <span class="dashboard-badge {{ $featuredBadgeClass }}">
                    {{ $featuredBadge }}
                </span>

                <h2>
                    {{ $featuredTransaction->title }}
                    ·
                    ₦{{ number_format((float) $featuredTransaction->total_amount, 0) }}
                </h2>

                <p class="dashboard-muted">
                    Seller:
                    {{ $featuredSellerName }}

                    <span>·</span>

                    {{ $featuredTransaction->delivery_note ?: 'Seller-arranged delivery' }}

                    <span>·</span>

                    {{ $featuredTransaction->reference }}
                </p>

                <p class="buyer-action-description">
                    @if($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DELIVERED)
                        Your seller marked this order as delivered. Confirm receipt only if the item has actually arrived, then accept it immediately or start your {{ $featuredTransaction->inspection_hours ?: config('secure_transactions.inspection_hours', 8) }}-hour inspection period.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_INSPECTION)
                        Your inspection period is active. Accept the item when satisfied, or open a dispute before the inspection deadline if there is a genuine problem.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DISPUTED)
                        This transaction is under the MidPoint dispute process. Seller payout stays paused until the dispute reaches a valid resolution.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_IN_TRANSIT)
                        Your item is in transit. MidPoint continues holding the payment until the order reaches the release stage.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DISPATCHED)
                        The seller has marked your item as dispatched. Follow the transaction for further delivery updates.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED || $featuredTransaction->status === \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING)
                        You have completed your buyer action. MidPoint is processing the seller payout and will complete the transaction when the payout finishes.

                    @else
                        Your payment is secured. The seller is preparing the order and MidPoint will keep the funds protected throughout fulfilment.
                    @endif
                </p>

                <div class="dashboard-action-row">

                    @if(
                        $featuredTransaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_DELIVERED
                        &&
                        !$hasActiveDispute
                    )

                        <button
                            type="button"
                            class="dashboard-green-button"
                            id="openBuyerReceivedModal"
                        >
                            <i class="fa-solid fa-box"></i>
                            Order received
                        </button>

                        @if(!$featuredDispute)
                            <a
                                href="{{ route('buyer.transactions.dispute.create', $featuredTransaction) }}"
                                class="dashboard-danger-button dashboard-danger-link"
                            >
                                <i class="fa-solid fa-scale-balanced"></i>
                                Dispute
                            </a>
                        @endif

                    @elseif(
                        $featuredTransaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_INSPECTION
                        &&
                        !$hasActiveDispute
                    )

                        <form
                            method="POST"
                            action="{{ route('buyer.transactions.accept', $featuredTransaction) }}"
                            class="dashboard-inline-form"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="dashboard-green-button"
                            >
                                <i class="fa-solid fa-check"></i>
                                Accept & release funds
                            </button>
                        </form>

                        @if(!$featuredDispute)
                            <a
                                href="{{ route('buyer.transactions.dispute.create', $featuredTransaction) }}"
                                class="dashboard-danger-button dashboard-danger-link"
                            >
                                <i class="fa-solid fa-scale-balanced"></i>
                                Dispute
                            </a>
                        @endif

                    @endif

                    <a
                        href="{{ route('buyer.transactions.show', $featuredTransaction) }}"
                        class="dashboard-text-button"
                    >
                        View details
                    </a>

                </div>

            </div>


            <div class="buyer-escrow-side">

                <span class="dashboard-muted">
                    {{
                        in_array(
                            $featuredTransaction->status,
                            [
                                \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED,
                                \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING,
                            ],
                            true
                        )
                            ? 'Release approved'
                            : 'Held safely in escrow'
                    }}
                </span>

                <div class="buyer-escrow-box">
                    <strong>
                        ₦{{ number_format($featuredPaidAmount, 0) }}
                    </strong>

                    <span>
                        @if($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_PAYOUT_PENDING)
                            Seller payout processing
                        @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_RELEASE_APPROVED)
                            Approved for seller payout
                        @else
                            Protected by MidPoint
                        @endif
                    </span>
                </div>

                <p>
                    @if(
                        $featuredTransaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_INSPECTION
                        &&
                        $featuredTransaction->inspection_ends_at
                    )
                        Inspection ends {{ $featuredTransaction->inspection_ends_at->diffForHumans() }}.

                    @elseif(
                        $featuredTransaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_DELIVERED
                    )
                        No inspection countdown begins until you confirm receipt.

                    @elseif($featuredTransaction->status === \App\Models\SecureTransaction::STATUS_DISPUTED)
                        Automatic release is paused while the dispute is active.

                    @else
                        Open the transaction to view the complete delivery and payment timeline.
                    @endif
                </p>

            </div>

        </div>

    </section>

@else

    <section class="buyer-action-card buyer-action-empty">

        <div class="dashboard-empty-icon buyer-empty-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div>
            <strong>
                No active purchases right now
            </strong>

            <p>
                Your next funded transaction will appear here with its current delivery, inspection, dispute, or payout status.
            </p>
        </div>

        <a
            href="{{ route('featured-businesses') }}"
            class="dashboard-outline-button"
        >
            Browse verified businesses
        </a>

    </section>

@endif


{{-- =========================================================
    LOWER AREA
========================================================== --}}

<div class="buyer-dashboard-grid">

    {{-- Transactions --}}
    <section class="dashboard-card dashboard-table-card">

        <div class="dashboard-card-header">
            <strong>
                Your transactions
            </strong>

            <a href="{{ route('buyer.transactions') }}">
                View all
            </a>
        </div>

        @if($transactions->isNotEmpty())

            <div class="dashboard-table-scroll">

                <table class="dashboard-table">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Seller</th>
                            <th>You paid</th>
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
                                    {{ $transaction['seller'] }}
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
                <i class="fa-solid fa-bag-shopping"></i>

                <strong>
                    No paid purchases yet
                </strong>

                <span>
                    Buy through a verified MidPoint seller and your protected transactions will appear here.
                </span>
            </div>

        @endif

    </section>


    <div class="buyer-dashboard-side">

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
                href="{{ route('buyer.notifications') }}"
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
                    href="{{ route('featured-businesses') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-store"></i>
                    Buy from a verified business
                </a>

                <a
                    href="{{ route('buyer.seller-invite') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-link"></i>
                    Open a seller invite
                </a>

                <a
                    href="{{ route('buyer.transactions') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-file-lines"></i>
                    Purchase history
                </a>

                <a
                    href="{{ route('buyer.profile-settings') }}"
                    class="dashboard-outline-button"
                >
                    <i class="fa-solid fa-gear"></i>
                    Profile settings
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


        {{-- Featured businesses --}}
        <section class="dashboard-card dashboard-side-card">

            <strong class="dashboard-side-title">
                Featured businesses
            </strong>

            <p class="dashboard-side-description">
                Active verified sellers you can trade with safely.
            </p>

            <div class="buyer-business-list">

                @forelse($businesses as $business)

                    <a
                        href="{{ $business['url'] }}"
                        class="buyer-business"
                    >
                        <span class="buyer-business-avatar {{ $business['style'] }}">
                            {{ $business['initials'] }}
                        </span>

                        <span class="buyer-business-info">
                            <strong>
                                {{ $business['name'] }}
                            </strong>

                            <small>
                                {{ $business['category'] }}
                            </small>
                        </span>

                        <span class="buyer-business-score">
                            <i class="fa-regular fa-shield"></i>
                            {{ $business['trust'] }}
                        </span>
                    </a>

                @empty

                    <div class="dashboard-mini-empty">
                        <i class="fa-solid fa-store"></i>
                        <span>No featured businesses are available right now.</span>
                    </div>

                @endforelse

            </div>

            <a
                href="{{ route('featured-businesses') }}"
                class="dashboard-outline-button full"
            >
                See all
            </a>

        </section>

    </div>

</div>


{{-- =========================================================
    ORDER RECEIVED MODAL
========================================================== --}}

@if(
    $featuredTransaction
    &&
    $featuredTransaction->status
        ===
        \App\Models\SecureTransaction::STATUS_DELIVERED
    &&
    !$hasActiveDispute
)

    <div
        class="buyer-dashboard-modal"
        id="buyerReceivedModal"
        hidden
        aria-hidden="true"
    >

        <div
            class="buyer-dashboard-modal-backdrop"
            data-close-buyer-received
        ></div>

        <div
            class="buyer-dashboard-modal-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="buyerReceivedModalTitle"
        >

            <button
                type="button"
                class="buyer-dashboard-modal-close"
                data-close-buyer-received
                aria-label="Close"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="buyer-dashboard-modal-icon">
                📦
            </div>

            <h2 id="buyerReceivedModalTitle">
                Order received?
            </h2>

            <p>
                Confirm only if the item has actually arrived. You can accept it now or start the protected inspection period before funds are released.
            </p>

            <form
                method="POST"
                action="{{ route('buyer.transactions.accept', $featuredTransaction) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="buyer-dashboard-modal-option"
                >
                    <span class="buyer-dashboard-option-icon success">
                        <i class="fa-solid fa-check"></i>
                    </span>

                    <span>
                        <strong>
                            Accept item & release funds
                        </strong>

                        <small>
                            Choose this only when you are satisfied with the item. Seller payout will begin immediately.
                        </small>
                    </span>
                </button>
            </form>

            <form
                method="POST"
                action="{{ route('buyer.transactions.inspection', $featuredTransaction) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="buyer-dashboard-modal-option"
                >
                    <span class="buyer-dashboard-option-icon inspection">
                        <i class="fa-solid fa-stopwatch"></i>
                    </span>

                    <span>
                        <strong>
                            Start {{ $featuredTransaction->inspection_hours ?: config('secure_transactions.inspection_hours', 8) }}-hour inspection
                        </strong>

                        <small>
                            Test or inspect the item while MidPoint continues holding the funds before automatic release.
                        </small>
                    </span>
                </button>
            </form>

            <button
                type="button"
                class="buyer-dashboard-modal-cancel"
                data-close-buyer-received
            >
                Cancel — my order has not arrived yet
            </button>

        </div>

    </div>

@endif

@endsection


@push('styles')
<style>
    /* =========================================================
       DASHBOARD FUNCTIONAL ADDITIONS
    ========================================================= */

    .dashboard-inline-form {
        margin: 0;
    }

    .dashboard-danger-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .dashboard-danger-link:hover {
        color: var(--mp-red);
        filter: brightness(.98);
    }

    .dashboard-badge.red {
        background: var(--mp-red-bg);
        color: var(--mp-red);
    }

    .buyer-action-card.is-disputed {
        border-color: var(--mp-red);
    }

    .buyer-trust-suffix {
        color: var(--mp-slate);
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-table-link {
        color: inherit;
        display: inline-block;
    }

    .dashboard-table-link:hover strong {
        color: var(--mp-purple);
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

    .buyer-action-empty {
        display: flex;
        align-items: center;
        gap: 14px;
        border-color: var(--mp-line);
    }

    .buyer-action-empty > div:nth-child(2) {
        min-width: 0;
        flex: 1;
    }

    .buyer-action-empty strong {
        display: block;
        color: var(--mp-ink);
        font-size: 14px;
    }

    .buyer-action-empty p {
        margin: 4px 0 0;
        color: var(--mp-slate);
        font-size: 12px;
        line-height: 1.5;
    }

    .buyer-empty-icon {
        background: var(--mp-lavender);
        color: var(--mp-purple);
    }

    .dashboard-empty-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
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
        background: #F6F3FF;
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
       ORDER RECEIVED MODAL
    ========================================================= */

    .buyer-dashboard-modal[hidden] {
        display: none !important;
    }

    .buyer-dashboard-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        padding: 18px;
        display: grid;
        place-items: center;
    }

    .buyer-dashboard-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(13, 18, 15, .58);
        backdrop-filter: blur(4px);
    }

    .buyer-dashboard-modal-card {
        position: relative;
        z-index: 1;
        width: min(520px, 100%);
        max-height: calc(100dvh - 36px);
        overflow-y: auto;
        padding: 26px;
        border: 1px solid var(--mp-line);
        border-radius: 20px;
        background: #FFFFFF;
        box-shadow: 0 28px 85px rgba(0, 0, 0, .22);
    }

    .buyer-dashboard-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid var(--mp-line);
        border-radius: 9px;
        background: #FFFFFF;
        color: var(--mp-slate);
        cursor: pointer;
    }

    .buyer-dashboard-modal-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 14px;
        background: var(--mp-lavender);
        font-size: 23px;
    }

    .buyer-dashboard-modal-card h2 {
        margin: 0;
        font-family: "Bricolage Grotesque", sans-serif;
        font-size: 20px;
    }

    .buyer-dashboard-modal-card > p {
        margin: 7px 0 18px;
        color: var(--mp-slate);
        font-size: 12px;
        line-height: 1.6;
    }

    .buyer-dashboard-modal-card form + form {
        margin-top: 9px;
    }

    .buyer-dashboard-modal-option {
        width: 100%;
        padding: 13px;
        border: 1px solid var(--mp-line);
        border-radius: 13px;
        background: #FFFFFF;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        text-align: left;
        cursor: pointer;
        transition:
            border-color .18s ease,
            background .18s ease,
            transform .18s ease;
    }

    .buyer-dashboard-modal-option:hover {
        border-color: var(--mp-purple);
        background: #FCFBFF;
        transform: translateY(-1px);
    }

    .buyer-dashboard-option-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: grid;
        place-items: center;
        border-radius: 10px;
    }

    .buyer-dashboard-option-icon.success {
        background: var(--mp-mint);
        color: var(--mp-forest);
    }

    .buyer-dashboard-option-icon.inspection {
        background: var(--mp-lavender);
        color: var(--mp-purple);
    }

    .buyer-dashboard-modal-option > span:last-child {
        min-width: 0;
        flex: 1;
    }

    .buyer-dashboard-modal-option strong,
    .buyer-dashboard-modal-option small {
        display: block;
    }

    .buyer-dashboard-modal-option strong {
        color: var(--mp-ink);
        font-size: 12px;
    }

    .buyer-dashboard-modal-option small {
        margin-top: 3px;
        color: var(--mp-slate);
        font-size: 11px;
        line-height: 1.5;
    }

    .buyer-dashboard-modal-cancel {
        width: 100%;
        margin-top: 14px;
        padding: 9px 12px;
        border: 0;
        background: transparent;
        color: var(--mp-slate);
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .buyer-action-empty {
            align-items: stretch;
            flex-direction: column;
        }

        .buyer-action-empty .dashboard-outline-button {
            width: 100%;
        }

        .buyer-dashboard-modal {
            padding: 10px;
        }

        .buyer-dashboard-modal-card {
            max-height: calc(100dvh - 20px);
            padding: 22px 16px 18px;
            border-radius: 16px;
        }
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal =
        document.getElementById('buyerReceivedModal');

    const openButton =
        document.getElementById('openBuyerReceivedModal');

    if (!modal || !openButton) {
        return;
    }

    const closeButtons =
        modal.querySelectorAll('[data-close-buyer-received]');

    function openModal() {
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const firstAction =
            modal.querySelector('.buyer-dashboard-modal-option');

        if (firstAction) {
            firstAction.focus();
        }
    }

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape'
            &&
            !modal.hidden
        ) {
            closeModal();
        }
    });
});
</script>
@endpush
