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
                Review dispute requests submitted against paid
                MidPoint transactions.
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

    <div class="txn-admin-stats">


        <div class="admin-card txn-stat">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-scale-balanced"></i>

            </div>

            <span>
                Total disputes
            </span>

            <strong>
                {{ number_format($stats['total']) }}
            </strong>

        </div>



        <div class="admin-card txn-stat is-warning">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-circle-exclamation"></i>

            </div>

            <span>
                Open
            </span>

            <strong>
                {{ number_format($stats['open']) }}
            </strong>

        </div>



        <div class="admin-card txn-stat is-purple">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <span>
                Under review
            </span>

            <strong>
                {{ number_format($stats['under_review']) }}
            </strong>

        </div>



        <div class="admin-card txn-stat is-success">

            <div class="txn-stat-icon">

                <i class="fa-solid fa-check"></i>

            </div>

            <span>
                Resolved
            </span>

            <strong>
                {{ number_format($stats['resolved']) }}
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
            class="txn-filters"
            style="
                grid-template-columns:
                    minmax(220px,2fr)
                    repeat(4,minmax(135px,1fr))
                    auto
                    auto;
            "
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Transaction, buyer, seller..."
            >



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
                    value="resolved"
                    {{ request('status') === 'resolved' ? 'selected' : '' }}
                >
                    Resolved
                </option>

            </select>



            <select name="reason">

                <option value="">
                    All reasons
                </option>

                <option value="not_received">
                    Not received
                </option>

                <option value="not_as_described">
                    Not as described
                </option>

                <option value="damaged">
                    Damaged
                </option>

                <option value="wrong_item">
                    Wrong item
                </option>

                <option value="missing_parts">
                    Missing parts
                </option>

                <option value="other">
                    Other
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

                            <tr>


                                <td>

                                    <strong>
                                        {{ $dispute->transaction?->reference }}
                                    </strong>

                                    <small>
                                        {{ $dispute->transaction?->title }}
                                    </small>

                                </td>



                                <td>

                                    <strong>
                                        {{ $dispute->buyer?->name ?? 'Buyer' }}
                                    </strong>

                                    <small>
                                        {{ $dispute->buyer?->email }}
                                    </small>

                                </td>



                                <td>

                                    <strong>
                                        {{ $dispute->seller?->name ?? 'Seller' }}
                                    </strong>

                                    <small>
                                        {{ $dispute->seller?->email }}
                                    </small>

                                </td>



                                <td>

                                    <span class="txn-badge red">

                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $dispute->reason
                                                )
                                            )
                                        }}

                                    </span>

                                </td>



                                <td>

                                    <strong>

                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $dispute->desired_outcome
                                                )
                                            )
                                        }}

                                    </strong>

                                </td>



                                <td>

                                    <strong>

                                        ₦{{
                                            number_format(
                                                (float)
                                                $dispute
                                                    ->transaction
                                                    ?->total_amount,
                                                2
                                            )
                                        }}

                                    </strong>

                                </td>



                                <td>

                                    <span
                                        class="
                                            txn-badge

                                            {{
                                                $dispute->status === 'resolved'
                                                    ? 'green'
                                                    : (
                                                        $dispute->status === 'under_review'
                                                            ? 'blue'
                                                            : 'red'
                                                    )
                                            }}
                                        "
                                    >

                                        {{
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



                                <td>

                                    <strong>

                                        {{
                                            $dispute->opened_at
                                                ? $dispute->opened_at->format('d M Y')
                                                : ''
                                        }}

                                    </strong>

                                    <small>

                                        {{
                                            $dispute->opened_at
                                                ? $dispute->opened_at->format('h:i A')
                                                : ''
                                        }}

                                    </small>

                                </td>



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

                                        Review

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div style="padding:15px 18px;">

                {{ $disputes->links() }}

            </div>

        @else

            <div class="txn-empty">

                <i class="fa-regular fa-circle-check"></i>


                <strong>
                    No dispute requests found
                </strong>


                <span>
                    New buyer disputes will automatically appear here.
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