@extends('admin.layouts.app')

@section('title', 'Purchased Packages')

@section('page-title', 'Purchased Packages')


@section('content')

<div class="plan-admin-page">


    <div class="plan-admin-heading">

        <h2>
            Purchased Seller Packages
        </h2>


        <p>
            Complete seller package purchase history,
            including renewals, upgrades and plan changes.
        </p>

    </div>



    {{-- =========================================================
        STATS
    ========================================================== --}}

    <div class="plan-admin-stats">

        <div class="admin-card plan-admin-stat">

            <span>
                Total purchases
            </span>

            <strong>
                {{ number_format($stats['total']) }}
            </strong>

        </div>


        <div class="admin-card plan-admin-stat">

            <span>
                Active
            </span>

            <strong class="green">
                {{ number_format($stats['active']) }}
            </strong>

        </div>


        <div class="admin-card plan-admin-stat">

            <span>
                Expired
            </span>

            <strong>
                {{ number_format($stats['expired']) }}
            </strong>

        </div>


        <div class="admin-card plan-admin-stat">

            <span>
                Renewals
            </span>

            <strong class="purple">
                {{ number_format($stats['renewals']) }}
            </strong>

        </div>


        <div class="admin-card plan-admin-stat">

            <span>
                Upgrades / changes
            </span>

            <strong class="blue">
                {{ number_format($stats['plan_changes']) }}
            </strong>

        </div>


        <div class="admin-card plan-admin-stat">

            <span>
                Expiring within 7 days
            </span>

            <strong class="orange">
                {{ number_format($stats['expiring_7']) }}
            </strong>

        </div>

    </div>



    {{-- =========================================================
        FILTER
    ========================================================== --}}

    <div class="admin-card plan-filter-card">

        <form
            method="GET"
            action="{{ route('admin.billing.subscriptions.index') }}"
            class="plan-filters"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="User, email, business, invoice, payment ref..."
            >


            <select name="status">

                <option value="">
                    All statuses
                </option>


                <option
                    value="active"
                    {{ request('status') === 'active' ? 'selected' : '' }}
                >
                    Active
                </option>


                <option
                    value="expired"
                    {{ request('status') === 'expired' ? 'selected' : '' }}
                >
                    Expired
                </option>

            </select>


            <select name="purchase_type">

                <option value="">
                    All purchase types
                </option>


                <option
                    value="initial"
                    {{ request('purchase_type') === 'initial' ? 'selected' : '' }}
                >
                    Initial
                </option>


                <option
                    value="renewal"
                    {{ request('purchase_type') === 'renewal' ? 'selected' : '' }}
                >
                    Renewal
                </option>


                <option
                    value="upgrade"
                    {{ request('purchase_type') === 'upgrade' ? 'selected' : '' }}
                >
                    Upgrade
                </option>


                <option
                    value="downgrade"
                    {{ request('purchase_type') === 'downgrade' ? 'selected' : '' }}
                >
                    Plan change
                </option>

            </select>


            <select name="package_id">

                <option value="">
                    All packages
                </option>


                @foreach($packages as $package)

                    <option
                        value="{{ $package->id }}"
                        {{
                            (string)
                            request('package_id')
                            ===
                            (string)
                            $package->id
                                ? 'selected'
                                : ''
                        }}
                    >

                        {{ $package->name }}

                    </option>

                @endforeach

            </select>


            <select name="expiring">

                <option value="">
                    Any expiry
                </option>


                <option
                    value="7"
                    {{ request('expiring') === '7' ? 'selected' : '' }}
                >
                    Expiring in 7 days
                </option>


                <option
                    value="30"
                    {{ request('expiring') === '30' ? 'selected' : '' }}
                >
                    Expiring in 30 days
                </option>

            </select>


            <button
                type="submit"
                class="plan-filter-button"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            <a
                href="{{ route('admin.billing.subscriptions.index') }}"
                class="plan-clear-button"
            >

                Clear

            </a>

        </form>

    </div>



    {{-- =========================================================
        TABLE
    ========================================================== --}}

    <div class="admin-card plan-table-card">

        <div class="plan-table-scroll">

            <table class="plan-table">

                <thead>

                    <tr>

                        <th>
                            Customer
                        </th>

                        <th>
                            Business
                        </th>

                        <th>
                            Purchase
                        </th>

                        <th>
                            Package
                        </th>

                        <th>
                            Product Limit
                        </th>

                        <th>
                            Started
                        </th>

                        <th>
                            Expires
                        </th>

                        <th>
                            Time Left
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($sellerSubscriptions as $subscription)

                        <tr>

                            <td>

                                <strong>
                                    {{ $subscription->user?->name ?? 'Unknown' }}
                                </strong>

                                <small>
                                    {{ $subscription->user?->email ?? '' }}
                                </small>

                            </td>


                            <td>

                                <strong>
                                    {{ $subscription->application?->business_name ?? 'N/A' }}
                                </strong>

                            </td>


                            <td>

                                <span
                                    class="
                                        purchase-type
                                        {{ $subscription->purchase_type ?: 'initial' }}
                                    "
                                >

                                    {{ $subscription->purchase_type_label }}

                                </span>


                                <small>

                                    Purchase
                                    #{{ number_format($subscription->renewal_sequence ?: 1) }}

                                </small>


                                @if($subscription->invoice)

                                    <small>
                                        {{ $subscription->invoice->invoice_number }}
                                    </small>

                                @endif

                            </td>


                            <td>

                                <strong>
                                    {{ $subscription->package_name }}
                                </strong>


                                <small>

                                    ₦{{
                                        number_format(
                                            (float)
                                            $subscription->price,
                                            0
                                        )
                                    }}

                                    /{{ $subscription->billing_period }}

                                </small>


                                @if($subscription->renewedFrom)

                                    <small>

                                        Previous:
                                        {{ $subscription->renewedFrom->package_name }}

                                    </small>

                                @endif

                            </td>


                            <td>

                                {{ number_format($subscription->product_limit) }}

                            </td>


                            <td>

                                @if($subscription->started_at)

                                    {{
                                        $subscription
                                            ->started_at
                                            ->format('d M Y')
                                    }}

                                    <small>

                                        {{
                                            $subscription
                                                ->started_at
                                                ->format('h:i A')
                                        }}

                                    </small>

                                @else

                                    N/A

                                @endif

                            </td>


                            <td>

                                @if($subscription->expires_at)

                                    {{
                                        $subscription
                                            ->expires_at
                                            ->format('d M Y')
                                    }}

                                    <small>

                                        {{
                                            $subscription
                                                ->expires_at
                                                ->format('h:i A')
                                        }}

                                    </small>

                                @else

                                    No expiry

                                @endif

                            </td>


                            <td>

                                @if($subscription->isCurrentlyActive())

                                    <strong
                                        class="{{
                                            $subscription->days_left <= 7
                                                ? 'plan-days-warning'
                                                : 'plan-days-good'
                                        }}"
                                    >

                                        {{ $subscription->days_left }}

                                        days left

                                    </strong>


                                    <small>
                                        {{ $subscription->remaining_time }}
                                    </small>

                                @else

                                    <strong class="plan-expired-text">
                                        Expired
                                    </strong>

                                @endif

                            </td>


                            <td>

                                <span
                                    class="
                                        plan-status
                                        {{
                                            $subscription->isCurrentlyActive()
                                                ? 'active'
                                                : 'expired'
                                        }}
                                    "
                                >

                                    {{
                                        $subscription->isCurrentlyActive()
                                            ? 'ACTIVE'
                                            : 'EXPIRED'
                                    }}

                                </span>

                            </td>


                            <td>

                                @if($subscription->application)

                                    <a
                                        href="{{
                                            route(
                                                'admin.website-settings.seller-applications.show',
                                                $subscription->application
                                            )
                                        }}"
                                        class="plan-view-button"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        View

                                    </a>

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="10"
                                class="plan-empty"
                            >

                                No purchased packages found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($sellerSubscriptions->hasPages())

            <div class="plan-pagination">

                {{ $sellerSubscriptions->links() }}

            </div>

        @endif

    </div>

</div>


@push('styles')

<style>

.plan-admin-heading {
    margin-bottom:18px;
}

.plan-admin-heading h2 {
    margin:0;
    color:var(--admin-heading);
    font-size:20px;
}

.plan-admin-heading p {
    margin:4px 0 0;
    color:var(--admin-muted);
    font-size:10px;
}


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

.plan-admin-stats {
    display:grid;
    grid-template-columns:repeat(6,minmax(0,1fr));
    gap:12px;
    margin-bottom:16px;
}

.plan-admin-stat {
    padding:18px;
}

.plan-admin-stat span {
    display:block;
    color:var(--admin-muted);
    font-size:10px;
}

.plan-admin-stat strong {
    display:block;
    margin-top:6px;
    color:var(--admin-heading);
    font-size:22px;
}

.plan-admin-stat strong.green {
    color:#12B76A;
}

.plan-admin-stat strong.orange {
    color:#F79009;
}

.plan-admin-stat strong.purple {
    color:#7A5AF8;
}

.plan-admin-stat strong.blue {
    color:#2E90FA;
}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

.plan-filter-card {
    padding:14px;
    margin-bottom:16px;
}

.plan-filters {
    display:grid;

    grid-template-columns:
        1.6fr
        .8fr
        .9fr
        .9fr
        .9fr
        auto
        auto;

    gap:9px;
}

.plan-filters input,
.plan-filters select {
    width:100%;
    height:38px;
    padding:0 10px;

    border:1px solid var(--admin-border);
    border-radius:8px;

    background:var(--admin-surface-soft);
    color:var(--admin-text);

    font-family:inherit;
    font-size:10px;

    outline:none;
}

.plan-filter-button,
.plan-clear-button,
.plan-view-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;

    gap:6px;

    height:38px;

    padding:0 13px;

    border-radius:8px;

    font-size:10px;
    font-weight:700;

    text-decoration:none;
    white-space:nowrap;
}

.plan-filter-button {
    border:0;

    background:var(--admin-accent);
    color:#fff;

    cursor:pointer;
}

.plan-clear-button {
    border:1px solid var(--admin-border);

    color:var(--admin-heading);
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.plan-table-card {
    overflow:hidden;
}

.plan-table-scroll {
    overflow-x:auto;
}

.plan-table {
    width:100%;
    min-width:1350px;

    border-collapse:collapse;
}

.plan-table th {
    padding:11px 13px;

    border-bottom:1px solid var(--admin-border);

    background:var(--admin-surface-soft);
    color:var(--admin-muted);

    font-size:9px;

    text-align:left;
}

.plan-table td {
    padding:12px 13px;

    border-bottom:1px solid var(--admin-border);

    color:var(--admin-text);

    font-size:10px;
}

.plan-table td strong {
    display:block;

    color:var(--admin-heading);
}

.plan-table td small {
    display:block;

    margin-top:3px;

    color:var(--admin-muted);

    font-size:9px;
}


/*
|--------------------------------------------------------------------------
| Purchase Type
|--------------------------------------------------------------------------
*/

.purchase-type {
    display:inline-flex;

    align-items:center;

    padding:5px 8px;

    border-radius:999px;

    font-size:8px;
    font-weight:800;

    text-transform:uppercase;
}

.purchase-type.initial {
    background:var(--admin-surface-hover);
    color:var(--admin-muted);
}

.purchase-type.renewal {
    background:rgba(18,183,106,.12);
    color:#12B76A;
}

.purchase-type.upgrade {
    background:rgba(122,90,248,.12);
    color:#7A5AF8;
}

.purchase-type.downgrade {
    background:rgba(46,144,250,.12);
    color:#2E90FA;
}


/*
|--------------------------------------------------------------------------
| Remaining
|--------------------------------------------------------------------------
*/

.plan-days-good {
    color:#12B76A !important;
}

.plan-days-warning {
    color:#F79009 !important;
}

.plan-expired-text {
    color:var(--admin-muted) !important;
}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

.plan-status {
    display:inline-flex;

    padding:5px 8px;

    border-radius:999px;

    font-size:9px;
    font-weight:700;
}

.plan-status.active {
    background:rgba(18,183,106,.12);
    color:#12B76A;
}

.plan-status.expired {
    background:var(--admin-surface-hover);
    color:var(--admin-muted);
}


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

.plan-view-button {
    height:30px;

    border:1px solid var(--admin-border);

    color:var(--admin-heading);
}

.plan-empty {
    padding:50px !important;

    text-align:center;

    color:var(--admin-muted) !important;
}

.plan-pagination {
    padding:14px;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width:1350px) {

    .plan-admin-stats {
        grid-template-columns:
            repeat(
                3,
                minmax(0,1fr)
            );
    }

}


@media(max-width:1100px) {

    .plan-filters {
        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );
    }

}


@media(max-width:650px) {

    .plan-admin-stats,
    .plan-filters {
        grid-template-columns:1fr;
    }

}

</style>

@endpush


@endsection