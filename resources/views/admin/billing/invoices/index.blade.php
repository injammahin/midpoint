@extends('admin.layouts.app')

@section('title', 'Seller Invoices')

@section('page-title', 'Seller Invoices')


@section('content')

<div class="billing-page">

    <div class="billing-heading">

        <div>

            <h2>
                Seller Invoices
            </h2>

            <p>
                View and filter seller package invoices and payments.
            </p>

        </div>

    </div>



    {{-- =====================================================
        STATS
    ====================================================== --}}

    <div class="billing-stats">

        <div class="admin-card billing-stat">

            <span>
                Total invoices
            </span>

            <strong>
                {{ number_format($stats['total']) }}
            </strong>

        </div>


        <div class="admin-card billing-stat">

            <span>
                Paid
            </span>

            <strong>
                {{ number_format($stats['paid']) }}
            </strong>

        </div>


        <div class="admin-card billing-stat">

            <span>
                Unpaid
            </span>

            <strong>
                {{ number_format($stats['unpaid']) }}
            </strong>

        </div>


        <div class="admin-card billing-stat">

            <span>
                Paid revenue
            </span>

            <strong>
                ₦{{ number_format((float) $stats['revenue'], 0) }}
            </strong>

        </div>

    </div>



    {{-- =====================================================
        FILTERS
    ====================================================== --}}

    <div class="admin-card billing-filter-card">

        <form
            method="GET"
            action="{{ route('admin.billing.invoices.index') }}"
            class="billing-filters"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Invoice, user, email, business..."
            >


            <select name="status">

                <option value="">
                    All statuses
                </option>

                <option
                    value="paid"
                    {{ request('status') === 'paid' ? 'selected' : '' }}
                >
                    Paid
                </option>

                <option
                    value="unpaid"
                    {{ request('status') === 'unpaid' ? 'selected' : '' }}
                >
                    Unpaid
                </option>

            </select>


            <select name="package_id">

                <option value="">
                    All packages
                </option>

                @foreach($packages as $package)

                    <option
                        value="{{ $package->id }}"
                        {{ (string) request('package_id') === (string) $package->id ? 'selected' : '' }}
                    >
                        {{ $package->name }}
                    </option>

                @endforeach

            </select>


            <input
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
            >


            <input
                type="date"
                name="date_to"
                value="{{ request('date_to') }}"
            >


            <button
                type="submit"
                class="billing-filter-button"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            <a
                href="{{ route('admin.billing.invoices.index') }}"
                class="billing-clear-button"
            >

                Clear

            </a>

        </form>

    </div>



    {{-- =====================================================
        TABLE
    ====================================================== --}}

    <div class="admin-card billing-table-card">

        <div class="billing-table-scroll">

            <table class="billing-table">

                <thead>

                    <tr>

                        <th>
                            Invoice
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Business / Package
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Payment
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @if($invoices->count() > 0)

                        @foreach($invoices as $invoice)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $invoice->invoice_number }}
                                    </strong>

                                    @if($invoice->payment_reference)

                                        <small>
                                            {{ $invoice->payment_reference }}
                                        </small>

                                    @endif

                                </td>


                                <td>

                                    <strong>
                                        {{ $invoice->user?->name ?? 'Unknown' }}
                                    </strong>

                                    <small>
                                        {{ $invoice->user?->email ?? '' }}
                                    </small>

                                </td>


                                <td>

                                    <strong>
                                        {{ $invoice->application?->business_name ?? 'N/A' }}
                                    </strong>

                                    <small>
                                        {{ $invoice->application?->package_name ?? 'N/A' }}
                                    </small>

                                </td>


                                <td>

                                    <strong>
                                        ₦{{ number_format((float) $invoice->amount, 0) }}
                                    </strong>

                                    <small>
                                        {{ $invoice->currency }}
                                    </small>

                                </td>


                                <td>

                                    <span
                                        class="
                                            billing-status
                                            {{ $invoice->status === 'paid' ? 'paid' : 'unpaid' }}
                                        "
                                    >

                                        {{ strtoupper($invoice->status) }}

                                    </span>

                                </td>


                                <td>

                                    @if($invoice->status === 'paid')

                                        <strong>
                                            {{ ucwords(str_replace('_', ' ', $invoice->payment_method ?? '')) }}
                                        </strong>

                                        <small>

                                            {{
                                                $invoice->paid_at
                                                    ? $invoice->paid_at->format('d M Y, h:i A')
                                                    : ''
                                            }}

                                        </small>

                                    @else

                                        <span class="billing-muted">
                                            Waiting for payment
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    {{
                                        $invoice->issued_at
                                            ? $invoice->issued_at->format('d M Y')
                                            : $invoice->created_at->format('d M Y')
                                    }}

                                </td>


                                <td>

                                    @if($invoice->application)

                                        <a
                                            href="{{ route('admin.website-settings.seller-applications.show', $invoice->application) }}"
                                            class="billing-view-button"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                            View

                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @endforeach


                    @else

                        <tr>

                            <td
                                colspan="8"
                                class="billing-empty"
                            >

                                No invoices found.

                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>


        @if($invoices->hasPages())

            <div class="billing-pagination">

                {{ $invoices->links() }}

            </div>

        @endif

    </div>

</div>


@push('styles')

<style>

.billing-heading {
    margin-bottom:18px;
}

.billing-heading h2 {
    margin:0;
    color:var(--admin-heading);
    font-size:20px;
}

.billing-heading p {
    margin:4px 0 0;
    color:var(--admin-muted);
    font-size:10px;
}

.billing-stats {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-bottom:16px;
}

.billing-stat {
    padding:18px;
}

.billing-stat span {
    display:block;
    color:var(--admin-muted);
    font-size:9px;
}

.billing-stat strong {
    display:block;
    margin-top:6px;
    color:var(--admin-heading);
    font-size:22px;
}

.billing-filter-card {
    padding:14px;
    margin-bottom:16px;
}

.billing-filters {
    display:grid;
    grid-template-columns:1.6fr 1fr 1fr 1fr 1fr auto auto;
    gap:9px;
    align-items:center;
}

.billing-filters input,
.billing-filters select {
    width:100%;
    height:38px;
    padding:0 10px;
    border:1px solid var(--admin-border);
    border-radius:8px;
    background:var(--admin-surface-soft);
    color:var(--admin-text);
    font-family:inherit;
    font-size:9px;
    outline:none;
}

.billing-filter-button,
.billing-clear-button,
.billing-view-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    height:38px;
    padding:0 13px;
    border-radius:8px;
    font-size:9px;
    font-weight:700;
    text-decoration:none;
    white-space:nowrap;
}

.billing-filter-button {
    border:0;
    background:var(--admin-accent);
    color:#fff;
    cursor:pointer;
}

.billing-clear-button {
    border:1px solid var(--admin-border);
    background:var(--admin-surface-soft);
    color:var(--admin-heading);
}

.billing-table-card {
    overflow:hidden;
}

.billing-table-scroll {
    overflow-x:auto;
}

.billing-table {
    width:100%;
    min-width:1100px;
    border-collapse:collapse;
}

.billing-table th {
    padding:11px 13px;
    border-bottom:1px solid var(--admin-border);
    background:var(--admin-surface-soft);
    color:var(--admin-muted);
    font-size:8px;
    text-align:left;
}

.billing-table td {
    padding:12px 13px;
    border-bottom:1px solid var(--admin-border);
    color:var(--admin-text);
    font-size:9px;
    vertical-align:middle;
}

.billing-table td strong {
    display:block;
    color:var(--admin-heading);
}

.billing-table td small {
    display:block;
    margin-top:3px;
    color:var(--admin-muted);
    font-size:8px;
}

.billing-status {
    display:inline-flex;
    padding:5px 8px;
    border-radius:999px;
    font-size:8px;
    font-weight:700;
}

.billing-status.paid {
    background:rgba(18,183,106,.12);
    color:#12B76A;
}

.billing-status.unpaid {
    background:rgba(247,144,9,.12);
    color:#F79009;
}

.billing-view-button {
    height:30px;
    border:1px solid var(--admin-border);
    color:var(--admin-heading);
}

.billing-muted {
    color:var(--admin-muted);
}

.billing-empty {
    padding:50px !important;
    text-align:center;
    color:var(--admin-muted) !important;
}

.billing-pagination {
    padding:14px;
}

@media(max-width:1100px) {

    .billing-stats {
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .billing-filters {
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

}

@media(max-width:650px) {

    .billing-stats,
    .billing-filters {
        grid-template-columns:1fr;
    }

}

</style>

@endpush


@endsection