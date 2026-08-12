@extends('admin.layouts.app')


@section('title', 'Paid Transactions')


@section('page-title', 'Paid Transactions')



@section('content')

<div class="txn-admin-page">


    {{-- =====================================================
        HEADING
    ====================================================== --}}

    <div class="txn-admin-heading">

        <div>

            <h2>
                Buyer-Paid Transactions
            </h2>


            <p>
                Monitor only secure transactions where the buyer
                has successfully completed payment.
                Unpaid generated links are not shown here.
            </p>

        </div>


        <a
            href="{{ route('admin.disputes.index') }}"
            class="txn-action-btn"
        >

            <i class="fa-solid fa-triangle-exclamation"></i>

            View Disputes

            @if(($adminOpenDisputeCount ?? 0) > 0)

                ({{ $adminOpenDisputeCount }})

            @endif

        </a>

    </div>



    {{-- =====================================================
        STATS
    ====================================================== --}}

    <div class="txn-admin-stats">


        <div class="admin-card txn-stat">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>


            <span>
                Paid transactions
            </span>


            <strong>
                {{ number_format($stats['total']) }}
            </strong>

        </div>



        <div class="admin-card txn-stat is-success">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-naira-sign"></i>

            </div>


            <span>
                Total secured
            </span>


            <strong>

                ₦{{
                    number_format(
                        $stats['secured_amount'],
                        0
                    )
                }}

            </strong>

        </div>



        <div class="admin-card txn-stat is-warning">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-triangle-exclamation"></i>

            </div>


            <span>
                Disputed
            </span>


            <strong>
                {{ number_format($stats['disputed']) }}
            </strong>

        </div>



        <div class="admin-card txn-stat is-purple">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>


            <span>
                Completed
            </span>


            <strong>
                {{ number_format($stats['completed']) }}
            </strong>

        </div>

    </div>



    {{-- =====================================================
        FILTERS
    ====================================================== --}}

    <div class="admin-card txn-filter-card">

        <form
            method="GET"
            action="{{ route('admin.transactions.index') }}"
            class="txn-filters"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Reference, buyer, seller, item..."
            >



            <select name="status">

                <option value="">
                    All statuses
                </option>

                @foreach([
                    'payment_secured' => 'Payment Secured',
                    'preparing_item' => 'Preparing Item',
                    'dispatched' => 'Dispatched',
                    'in_transit' => 'In Transit',
                    'delivered' => 'Delivered',
                    'inspection' => 'Inspection',
                    'disputed' => 'Disputed',
                    'release_approved' => 'Release Approved',
                    'payout_pending' => 'Payout Pending',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        {{ request('status') === $value ? 'selected' : '' }}
                    >

                        {{ $label }}

                    </option>

                @endforeach

            </select>



            <select name="payout_status">

                <option value="">
                    All payouts
                </option>

                @foreach([
                    'locked' => 'Locked',
                    'initializing' => 'Initializing',
                    'pending' => 'Pending',
                    'success' => 'Success',
                    'failed' => 'Failed',
                    'reversed' => 'Reversed',
                ] as $value => $label)

                    <option
                        value="{{ $value }}"
                        {{ request('payout_status') === $value ? 'selected' : '' }}
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>



            <select name="dispute">

                <option value="">
                    All dispute states
                </option>

                <option
                    value="yes"
                    {{ request('dispute') === 'yes' ? 'selected' : '' }}
                >
                    Has dispute
                </option>

                <option
                    value="no"
                    {{ request('dispute') === 'no' ? 'selected' : '' }}
                >
                    No dispute
                </option>

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
                class="txn-filter-btn"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            <a
                href="{{ route('admin.transactions.index') }}"
                class="txn-clear-btn"
            >

                Clear

            </a>

        </form>

    </div>



    {{-- =====================================================
        TABLE
    ====================================================== --}}

    <div class="admin-card txn-table-card">

        @if($transactions->count() > 0)

            <div class="txn-table-scroll">

                <table class="txn-table">

                    <thead>

                        <tr>

                            <th>
                                Transaction
                            </th>

                            <th>
                                Buyer
                            </th>

                            <th>
                                Seller
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Payment
                            </th>

                            <th>
                                Order Status
                            </th>

                            <th>
                                Dispute
                            </th>

                            <th>
                                Paid
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($transactions as $transaction)

                            @php

                                $statusColor =
                                    match($transaction->status) {

                                        'disputed' =>
                                            'red',

                                        'completed' =>
                                            'green',

                                        'release_approved',
                                        'payout_pending' =>
                                            'purple',

                                        'delivered',
                                        'inspection' =>
                                            'blue',

                                        default =>
                                            'yellow',
                                    };

                            @endphp


                            <tr>


                                {{-- Transaction --}}
                                <td>

                                    <strong>
                                        {{ $transaction->reference }}
                                    </strong>


                                    <small>
                                        {{ $transaction->title }}
                                    </small>

                                </td>



                                {{-- Buyer --}}
                                <td>

                                    <strong>

                                        {{
                                            $transaction->buyer?->name
                                            ??
                                            'Buyer'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $transaction->buyer?->email
                                            ??
                                            $transaction->buyer_email
                                        }}

                                    </small>

                                </td>



                                {{-- Seller --}}
                                <td>

                                    <strong>

                                        {{
                                            $transaction->seller?->name
                                            ??
                                            'Unknown seller'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $transaction->seller?->email
                                            ??
                                            ''
                                        }}

                                    </small>

                                </td>



                                {{-- Amount --}}
                                <td>

                                    <strong>

                                        ₦{{
                                            number_format(
                                                (float)
                                                (
                                                    $transaction->paid_amount
                                                    ?:
                                                    $transaction->total_amount
                                                ),
                                                2
                                            )
                                        }}

                                    </strong>


                                    <small>
                                        {{ $transaction->currency }}
                                    </small>

                                </td>



                                {{-- Payment --}}
                                <td>

                                    <span class="txn-badge green">

                                        <i class="fa-solid fa-lock"></i>

                                        PAID

                                    </span>


                                    @if($transaction->successfulPayment?->channel)

                                        <small>

                                            {{
                                                ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $transaction
                                                            ->successfulPayment
                                                            ->channel
                                                    )
                                                )
                                            }}

                                        </small>

                                    @endif

                                </td>



                                {{-- Status --}}
                                <td>

                                    <span
                                        class="txn-badge {{ $statusColor }}"
                                    >

                                        {{ $transaction->status_label }}

                                    </span>

                                </td>



                                {{-- Dispute --}}
                                <td>

                                    @if($transaction->dispute)

                                        <a
                                            href="{{
                                                route(
                                                    'admin.disputes.show',
                                                    $transaction->dispute
                                                )
                                            }}"
                                            class="txn-badge red"
                                            style="text-decoration:none;"
                                        >

                                            <i class="fa-solid fa-triangle-exclamation"></i>

                                            Disputed

                                        </a>

                                    @else

                                        <span class="txn-badge gray">

                                            None

                                        </span>

                                    @endif

                                </td>



                                {{-- Date --}}
                                <td>

                                    <strong>

                                        {{
                                            $transaction->paid_at
                                                ? $transaction->paid_at->format('d M Y')
                                                : '-'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $transaction->paid_at
                                                ? $transaction->paid_at->format('h:i A')
                                                : ''
                                        }}

                                    </small>

                                </td>



                                {{-- Action --}}
                                <td>

                                    <a
                                        href="{{
                                            route(
                                                'admin.transactions.show',
                                                $transaction
                                            )
                                        }}"
                                        class="txn-action-btn"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        Monitor

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div style="padding:15px 18px;">

                {{ $transactions->links() }}

            </div>

        @else

            <div class="txn-empty">

                <i class="fa-solid fa-shield-halved"></i>


                <strong>
                    No paid transactions found
                </strong>


                <span>
                    Seller-generated links will only appear here after
                    buyer payment has been successfully verified.
                </span>

            </div>

        @endif

    </div>

</div>

@endsection



@push('styles')

    @include(
        'admin.transactions.partials.styles'
    )

@endpush