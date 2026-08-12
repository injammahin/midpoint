@extends('admin.layouts.app')


@section('title', 'Transaction Disputes')


@section('page-title', 'Transaction Disputes')



@section('content')

<div class="txn-admin-page">


    {{-- =====================================================
        HEADING
    ====================================================== --}}

    <div class="txn-admin-heading">

        <div>

            <h2>
                Buyer Dispute Requests
            </h2>


            <p>
                Review and manage dispute requests submitted
                against paid MidPoint secure transactions.
            </p>

        </div>


        <a
            href="{{ route('admin.transactions.index') }}"
            class="txn-action-btn"
        >

            <i class="fa-solid fa-money-bill-transfer"></i>

            Paid Transactions

        </a>

    </div>



    {{-- =====================================================
        STATS
    ====================================================== --}}

    <div class="dispute-stats-grid">


        {{-- TOTAL --}}
        <div class="admin-card txn-stat">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-scale-balanced"></i>

            </div>


            <span>
                Total Disputes
            </span>


            <strong>
                {{ number_format($stats['total'] ?? 0) }}
            </strong>

        </div>



        {{-- OPEN --}}
        <div class="admin-card txn-stat dispute-stat-open">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-circle-exclamation"></i>

            </div>


            <span>
                Open
            </span>


            <strong>
                {{ number_format($stats['open'] ?? 0) }}
            </strong>

        </div>



        {{-- UNDER REVIEW --}}
        <div class="admin-card txn-stat dispute-stat-review">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>


            <span>
                Under Review
            </span>


            <strong>
                {{ number_format($stats['under_review'] ?? 0) }}
            </strong>

        </div>



        {{-- AWAITING BUYER --}}
        <div class="admin-card txn-stat dispute-stat-buyer">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-user-clock"></i>

            </div>


            <span>
                Awaiting Buyer
            </span>


            <strong>
                {{ number_format($stats['awaiting_buyer'] ?? 0) }}
            </strong>

        </div>



        {{-- AWAITING SELLER --}}
        <div class="admin-card txn-stat dispute-stat-seller">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-store"></i>

            </div>


            <span>
                Awaiting Seller
            </span>


            <strong>
                {{ number_format($stats['awaiting_seller'] ?? 0) }}
            </strong>

        </div>



        {{-- RESOLVED --}}
        <div class="admin-card txn-stat dispute-stat-resolved">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>


            <span>
                Resolved
            </span>


            <strong>
                {{ number_format($stats['resolved'] ?? 0) }}
            </strong>

        </div>

    </div>



    {{-- =====================================================
        FILTER
    ====================================================== --}}

    <div class="admin-card txn-filter-card">

        <form
            method="GET"
            action="{{ route('admin.disputes.index') }}"
            class="dispute-filter-grid"
        >


            {{-- SEARCH --}}
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Transaction, buyer, seller..."
            >



            {{-- STATUS --}}
            <select name="status">

                <option value="">
                    All statuses
                </option>


                <option
                    value="open"
                    {{ request('status') === 'open' ? 'selected' : '' }}
                >
                    Open
                </option>


                <option
                    value="under_review"
                    {{ request('status') === 'under_review' ? 'selected' : '' }}
                >
                    Under Review
                </option>


                <option
                    value="awaiting_buyer"
                    {{ request('status') === 'awaiting_buyer' ? 'selected' : '' }}
                >
                    Awaiting Buyer
                </option>


                <option
                    value="awaiting_seller"
                    {{ request('status') === 'awaiting_seller' ? 'selected' : '' }}
                >
                    Awaiting Seller
                </option>


                <option
                    value="resolved"
                    {{ request('status') === 'resolved' ? 'selected' : '' }}
                >
                    Resolved
                </option>

            </select>



            {{-- REASON --}}
            <select name="reason">

                <option value="">
                    All reasons
                </option>


                <option
                    value="not_received"
                    {{ request('reason') === 'not_received' ? 'selected' : '' }}
                >
                    Not received
                </option>


                <option
                    value="not_as_described"
                    {{ request('reason') === 'not_as_described' ? 'selected' : '' }}
                >
                    Not as described
                </option>


                <option
                    value="damaged"
                    {{ request('reason') === 'damaged' ? 'selected' : '' }}
                >
                    Damaged
                </option>


                <option
                    value="wrong_item"
                    {{ request('reason') === 'wrong_item' ? 'selected' : '' }}
                >
                    Wrong item
                </option>


                <option
                    value="missing_parts"
                    {{ request('reason') === 'missing_parts' ? 'selected' : '' }}
                >
                    Missing parts
                </option>


                <option
                    value="other"
                    {{ request('reason') === 'other' ? 'selected' : '' }}
                >
                    Other
                </option>

            </select>



            {{-- DATE FROM --}}
            <input
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
            >



            {{-- DATE TO --}}
            <input
                type="date"
                name="date_to"
                value="{{ request('date_to') }}"
            >



            {{-- FILTER --}}
            <button
                type="submit"
                class="txn-filter-btn"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>



            {{-- CLEAR --}}
            <a
                href="{{ route('admin.disputes.index') }}"
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

        @if($disputes->count() > 0)

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
                                Reason
                            </th>

                            <th>
                                Requested Outcome
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Opened
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($disputes as $dispute)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | Status Color
                                |--------------------------------------------------------------------------
                                */

                                $statusClass =
                                    match($dispute->status) {

                                        'open' =>
                                            'red',

                                        'under_review' =>
                                            'blue',

                                        'awaiting_buyer' =>
                                            'yellow',

                                        'awaiting_seller' =>
                                            'purple',

                                        'resolved' =>
                                            'green',

                                        default =>
                                            'gray',
                                    };


                                /*
                                |--------------------------------------------------------------------------
                                | Reason Label
                                |--------------------------------------------------------------------------
                                */

                                $reasonLabel =
                                    match($dispute->reason) {

                                        'not_received' =>
                                            'Not Received',

                                        'not_as_described' =>
                                            'Not As Described',

                                        'damaged' =>
                                            'Damaged',

                                        'wrong_item' =>
                                            'Wrong Item',

                                        'missing_parts' =>
                                            'Missing Parts',

                                        default =>
                                            'Other',
                                    };


                                /*
                                |--------------------------------------------------------------------------
                                | Outcome Label
                                |--------------------------------------------------------------------------
                                */

                                $outcomeLabel =
                                    match($dispute->desired_outcome) {

                                        'full_refund' =>
                                            'Full Refund',

                                        'partial_refund' =>
                                            'Partial Refund',

                                        'replacement' =>
                                            'Replacement',

                                        default =>
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    (string) $dispute->desired_outcome
                                                )
                                            ),
                                    };

                            @endphp


                            <tr>


                                {{-- =================================================
                                    TRANSACTION
                                ================================================== --}}

                                <td>

                                    <strong>

                                        {{
                                            $dispute
                                                ->transaction
                                                ?->reference
                                            ??
                                            '-'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $dispute
                                                ->transaction
                                                ?->title
                                            ??
                                            'Secure transaction'
                                        }}

                                    </small>

                                </td>



                                {{-- =================================================
                                    BUYER
                                ================================================== --}}

                                <td>

                                    <strong>

                                        {{
                                            $dispute
                                                ->buyer
                                                ?->name
                                            ??
                                            'Buyer'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $dispute
                                                ->buyer
                                                ?->email
                                            ??
                                            $dispute
                                                ->transaction
                                                ?->buyer_email
                                            ??
                                            ''
                                        }}

                                    </small>

                                </td>



                                {{-- =================================================
                                    SELLER
                                ================================================== --}}

                                <td>

                                    <strong>

                                        {{
                                            $dispute
                                                ->seller
                                                ?->name
                                            ??
                                            'Seller'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $dispute
                                                ->seller
                                                ?->email
                                            ??
                                            ''
                                        }}

                                    </small>

                                </td>



                                {{-- =================================================
                                    REASON
                                ================================================== --}}

                                <td>

                                    <span class="txn-badge red">

                                        {{ $reasonLabel }}

                                    </span>

                                </td>



                                {{-- =================================================
                                    DESIRED OUTCOME
                                ================================================== --}}

                                <td>

                                    <strong>

                                        {{ $outcomeLabel }}

                                    </strong>

                                </td>



                                {{-- =================================================
                                    AMOUNT
                                ================================================== --}}

                                <td>

                                    <strong>

                                        ₦{{
                                            number_format(
                                                (float) (
                                                    $dispute
                                                        ->transaction
                                                        ?->total_amount
                                                    ??
                                                    0
                                                ),
                                                2
                                            )
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $dispute
                                                ->transaction
                                                ?->currency
                                            ??
                                            'NGN'
                                        }}

                                    </small>

                                </td>



                                {{-- =================================================
                                    STATUS
                                ================================================== --}}

                                <td>

                                    <span
                                        class="
                                            txn-badge
                                            {{ $statusClass }}
                                        "
                                    >

                                        @if($dispute->status === 'open')

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-circle-exclamation
                                                "
                                            ></i>

                                        @elseif($dispute->status === 'under_review')

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-magnifying-glass
                                                "
                                            ></i>

                                        @elseif($dispute->status === 'awaiting_buyer')

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-user-clock
                                                "
                                            ></i>

                                        @elseif($dispute->status === 'awaiting_seller')

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-store
                                                "
                                            ></i>

                                        @elseif($dispute->status === 'resolved')

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-circle-check
                                                "
                                            ></i>

                                        @endif


                                        {{
                                            $dispute->status_label
                                            ??
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $dispute->status
                                                )
                                            )
                                        }}

                                    </span>

                                </td>



                                {{-- =================================================
                                    OPENED
                                ================================================== --}}

                                <td>

                                    <strong>

                                        {{
                                            $dispute->opened_at
                                                ? $dispute
                                                    ->opened_at
                                                    ->format('d M Y')
                                                : '-'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            $dispute->opened_at
                                                ? $dispute
                                                    ->opened_at
                                                    ->format('h:i A')
                                                : ''
                                        }}

                                    </small>

                                </td>



                                {{-- =================================================
                                    ACTION
                                ================================================== --}}

                                <td>

                                    <a
                                        href="{{
                                            route(
                                                'admin.disputes.show',
                                                $dispute
                                            )
                                        }}"
                                        class="txn-action-btn"
                                    >

                                        <i class="fa-solid fa-eye"></i>


                                        @if($dispute->status === 'open')

                                            Review

                                        @elseif($dispute->status === 'resolved')

                                            View

                                        @else

                                            Manage

                                        @endif

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>



            {{-- =================================================
                PAGINATION
            ================================================== --}}

            <div
                style="
                    padding:
                        15px
                        18px;
                "
            >

                {{ $disputes->links() }}

            </div>


        @else

            {{-- =================================================
                EMPTY
            ================================================== --}}

            <div class="txn-empty">

                <i class="fa-regular fa-circle-check"></i>


                <strong>
                    No dispute requests found
                </strong>


                <span>

                    New buyer disputes will automatically
                    appear here after they are submitted.

                </span>

            </div>

        @endif

    </div>

</div>

@endsection



{{-- =========================================================
    STYLES
========================================================== --}}

@push('styles')

    @include(
        'admin.transactions.partials.styles'
    )


    <style>

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        .dispute-stats-grid {
            display:
                grid;

            grid-template-columns:
                repeat(
                    6,
                    minmax(
                        0,
                        1fr
                    )
                );

            gap:
                12px;
        }


        /*
        |--------------------------------------------------------------------------
        | Open
        |--------------------------------------------------------------------------
        */

        .dispute-stat-open
        .txn-stat-icon {
            background:
                #FFF1F2;

            color:
                #B42318;
        }


        /*
        |--------------------------------------------------------------------------
        | Review
        |--------------------------------------------------------------------------
        */

        .dispute-stat-review
        .txn-stat-icon {
            background:
                #EEF4FF;

            color:
                #3538CD;
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer
        |--------------------------------------------------------------------------
        */

        .dispute-stat-buyer
        .txn-stat-icon {
            background:
                #FFF7E8;

            color:
                #B54708;
        }


        /*
        |--------------------------------------------------------------------------
        | Seller
        |--------------------------------------------------------------------------
        */

        .dispute-stat-seller
        .txn-stat-icon {
            background:
                #F2F0FF;

            color:
                #6941C6;
        }


        /*
        |--------------------------------------------------------------------------
        | Resolved
        |--------------------------------------------------------------------------
        */

        .dispute-stat-resolved
        .txn-stat-icon {
            background:
                #ECFDF3;

            color:
                #067647;
        }


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        .dispute-filter-grid {
            display:
                grid;

            grid-template-columns:
                minmax(220px, 2fr)
                minmax(145px, 1fr)
                minmax(145px, 1fr)
                minmax(135px, 1fr)
                minmax(135px, 1fr)
                auto
                auto;

            gap:
                10px;

            align-items:
                center;
        }


        .dispute-filter-grid
        input,

        .dispute-filter-grid
        select {
            width:
                100%;

            min-height:
                40px;

            padding:
                0
                11px;

            border:
                1px
                solid
                var(--admin-border);

            border-radius:
                9px;

            outline:
                none;

            background:
                var(--admin-card);

            color:
                var(--admin-text);

            font:
                inherit;

            font-size:
                11px;
        }


        .dispute-filter-grid
        input:focus,

        .dispute-filter-grid
        select:focus {
            border-color:
                #0EA584;

            box-shadow:
                0
                0
                0
                3px
                rgba(
                    14,
                    165,
                    132,
                    .08
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 1350px) {

            .dispute-stats-grid {
                grid-template-columns:
                    repeat(
                        3,
                        minmax(
                            0,
                            1fr
                        )
                    );
            }

        }


        @media(max-width: 1150px) {

            .dispute-filter-grid {
                grid-template-columns:
                    repeat(
                        3,
                        minmax(
                            0,
                            1fr
                        )
                    );
            }

        }


        @media(max-width: 760px) {

            .dispute-stats-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(
                            0,
                            1fr
                        )
                    );
            }


            .dispute-filter-grid {
                grid-template-columns:
                    1fr;
            }

        }


        @media(max-width: 480px) {

            .dispute-stats-grid {
                grid-template-columns:
                    1fr;
            }

        }

    </style>

@endpush