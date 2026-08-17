@extends('admin.layouts.app')

@section('title', 'Seller Withdrawals')


@push('styles')

<style>

    .wd-page {
        padding: 4px 0;
    }


    .wd-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 18px;

        margin-bottom: 18px;
    }


    .wd-head h1 {
        margin: 0 0 6px;

        color: #17251f;

        font-size: 26px;
    }


    .wd-head p {
        margin: 0;

        color: #718078;

        font-size: 12px;
        line-height: 1.6;
    }


    .wd-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 12px;

        margin-bottom: 16px;
    }


    .wd-stat {
        padding: 16px;

        border: 1px solid #e3e8e5;
        border-radius: 15px;

        background: #ffffff;
    }


    .wd-stat span {
        display: block;

        margin-bottom: 7px;

        color: #7e8a84;

        font-size: 9px;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: .07em;
    }


    .wd-stat strong {
        display: block;

        color: #16372a;

        font-size: 21px;
    }


    .wd-stat small {
        display: block;

        margin-top: 5px;

        color: #859089;

        font-size: 9px;
    }


    .wd-card {
        padding: 17px;

        border: 1px solid #e3e8e5;
        border-radius: 16px;

        background: #ffffff;
    }


    .wd-filters {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;

        gap: 10px;

        margin-bottom: 14px;
    }


    .wd-input,
    .wd-select {
        width: 100%;

        padding: 10px 11px;

        border: 1px solid #dce5e0;
        border-radius: 10px;

        outline: none;

        background: #ffffff;

        color: #283a31;

        font-size: 11px;
    }


    .wd-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 7px;

        padding: 10px 13px;

        border: 0;
        border-radius: 10px;

        background: #0b3d2e;

        color: #ffffff;

        cursor: pointer;

        font-size: 11px;
        font-weight: 800;

        text-decoration: none;
    }


    .wd-btn.secondary {
        background: #edf4f0;

        color: #1d543d;
    }


    .wd-scroll {
        overflow-x: auto;
    }


    .wd-table {
        width: 100%;
        min-width: 920px;

        border-collapse: collapse;
    }


    .wd-table th,
    .wd-table td {
        padding: 12px 9px;

        border-bottom: 1px solid #edf1ef;

        text-align: left;
        vertical-align: middle;
    }


    .wd-table th {
        color: #849089;

        font-size: 8px;

        text-transform: uppercase;
        letter-spacing: .08em;
    }


    .wd-table td {
        color: #536158;

        font-size: 10px;
    }


    .wd-table strong {
        color: #1b2e25;
    }


    .wd-badge {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        padding: 5px 8px;

        border-radius: 999px;

        font-size: 8px;
        font-weight: 800;
    }


    .wd-badge.success {
        background: #e8f8ef;
        color: #087443;
    }


    .wd-badge.danger {
        background: #fff0f0;
        color: #bf3030;
    }


    .wd-badge.warning {
        background: #fff4dc;
        color: #9a5900;
    }


    .wd-badge.processing {
        background: #edf5ff;
        color: #2660a8;
    }


    .wd-badge.neutral {
        background: #f1f4f2;
        color: #69766f;
    }


    .wd-view {
        color: #0b6947;

        font-weight: 800;

        text-decoration: none;
    }


    .wd-empty {
        padding: 34px;

        color: #7d8983;

        font-size: 11px;

        text-align: center;
    }


    @media(max-width: 980px) {

        .wd-stats {
            grid-template-columns: 1fr 1fr;
        }


        .wd-filters {
            grid-template-columns: 1fr 1fr;
        }


        .wd-filters .wide {
            grid-column: 1 / -1;
        }

    }


    @media(max-width: 620px) {

        .wd-stats,
        .wd-filters {
            grid-template-columns: 1fr;
        }


        .wd-head {
            display: block;
        }

    }

</style>

@endpush



@section('content')

<div class="wd-page">

    <div class="wd-head">

        <div>

            <h1>
                Seller Withdrawals
            </h1>


            <p>
                Monitor all automatic seller payouts sent from the
                Midpoint Paystack balance. Admin does not approve
                these withdrawals.
            </p>

        </div>

    </div>



    @if(session('success'))

        <div
            style="
                margin-bottom:14px;
                padding:11px 13px;
                border-radius:10px;
                background:#e9f9f0;
                color:#087443;
                font-size:11px;
            "
        >

            {{ session('success') }}

        </div>

    @endif



    {{-- =========================================================
        STATISTICS
    ========================================================== --}}

    <div class="wd-stats">

        <div class="wd-stat">

            <span>
                All requests
            </span>

            <strong>
                {{ number_format($stats['total_count']) }}
            </strong>

            <small>
                ₦{{ number_format($stats['total_requested'], 2) }}
                requested
            </small>

        </div>


        <div class="wd-stat">

            <span>
                Successful
            </span>

            <strong>
                {{ number_format($stats['successful_count']) }}
            </strong>

            <small>
                ₦{{ number_format($stats['successful_amount'], 2) }}
                paid
            </small>

        </div>


        <div class="wd-stat">

            <span>
                Processing
            </span>

            <strong>
                {{ number_format($stats['processing_count']) }}
            </strong>

            <small>
                Pending with Paystack
            </small>

        </div>


        <div class="wd-stat">

            <span>
                Failed / reversed
            </span>

            <strong>
                {{ number_format($stats['failed_count']) }}
            </strong>

            <small>
                Funds returned to seller wallet
            </small>

        </div>

    </div>



    <div class="wd-card">


        {{-- =====================================================
            FILTERS
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('admin.withdrawals.index') }}"
            class="wd-filters"
        >

            <input
                class="wd-input wide"
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Seller, email, reference, Paystack transfer code, bank..."
            >


            <select
                class="wd-select"
                name="status"
            >

                <option value="">
                    All statuses
                </option>


                <option
                    value="pending"
                    {{ request('status') === 'pending' ? 'selected' : '' }}
                >
                    Pending
                </option>


                <option
                    value="processing"
                    {{ request('status') === 'processing' ? 'selected' : '' }}
                >
                    Processing
                </option>


                <option
                    value="otp"
                    {{ request('status') === 'otp' ? 'selected' : '' }}
                >
                    OTP Required
                </option>


                <option
                    value="successful"
                    {{ request('status') === 'successful' ? 'selected' : '' }}
                >
                    Successful
                </option>


                <option
                    value="failed"
                    {{ request('status') === 'failed' ? 'selected' : '' }}
                >
                    Failed
                </option>


                <option
                    value="reversed"
                    {{ request('status') === 'reversed' ? 'selected' : '' }}
                >
                    Reversed
                </option>

            </select>


            <input
                class="wd-input"
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
            >


            <input
                class="wd-input"
                type="date"
                name="date_to"
                value="{{ request('date_to') }}"
            >


            <div
                style="
                    display:flex;
                    gap:7px;
                "
            >

                <button
                    class="wd-btn"
                    type="submit"
                >

                    <i class="fa-solid fa-filter"></i>

                    Filter

                </button>


                <a
                    class="wd-btn secondary"
                    href="{{ route('admin.withdrawals.index') }}"
                >

                    <i class="fa-solid fa-rotate-left"></i>

                </a>

            </div>

        </form>



        {{-- =====================================================
            TABLE
        ====================================================== --}}

        @if($withdrawals->count())

            <div class="wd-scroll">

                <table class="wd-table">

                    <thead>

                        <tr>

                            <th>Seller</th>

                            <th>Reference</th>

                            <th>Destination</th>

                            <th>Amount</th>

                            <th>Status</th>

                            <th>Requested</th>

                            <th></th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($withdrawals as $withdrawal)

                            <tr>

                                <td>

                                    <strong>
                                        {{
                                            $withdrawal->seller?->name
                                            ??
                                            'Unknown seller'
                                        }}
                                    </strong>

                                    <br>

                                    <small>
                                        {{ $withdrawal->seller?->email }}
                                    </small>

                                </td>


                                <td>

                                    <strong>
                                        {{ $withdrawal->reference }}
                                    </strong>

                                    <br>


                                    @if($withdrawal->paystack_transfer_code)

                                        <small>
                                            {{ $withdrawal->paystack_transfer_code }}
                                        </small>

                                    @else

                                        <small>
                                            Transfer code pending
                                        </small>

                                    @endif

                                </td>


                                <td>

                                    <strong>
                                        {{ $withdrawal->bank_name }}
                                    </strong>

                                    <br>

                                    <small>

                                        {{ $withdrawal->account_name }}

                                        ·

                                        ••••{{ $withdrawal->account_number_last4 }}

                                    </small>

                                </td>


                                <td>

                                    <strong>

                                        ₦{{ number_format(
                                            (float)
                                            $withdrawal->amount,
                                            2
                                        ) }}

                                    </strong>

                                </td>


                                <td>

                                    <span
                                        class="
                                            wd-badge
                                            {{ $withdrawal->status_tone }}
                                        "
                                    >

                                        {{ $withdrawal->status_label }}

                                    </span>

                                </td>


                                <td>

                                    {{
                                        optional(
                                            $withdrawal->requested_at
                                        )
                                        ->format(
                                            'd M Y'
                                        )
                                    }}

                                    <br>

                                    <small>

                                        {{
                                            optional(
                                                $withdrawal->requested_at
                                            )
                                            ->format(
                                                'h:i A'
                                            )
                                        }}

                                    </small>

                                </td>


                                <td>

                                    <a
                                        class="wd-view"
                                        href="{{
                                            route(
                                                'admin.withdrawals.show',
                                                $withdrawal
                                            )
                                        }}"
                                    >

                                        View →

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div style="margin-top:14px;">

                {{ $withdrawals->links() }}

            </div>

        @else

            <div class="wd-empty">

                <i
                    class="fa-solid fa-money-bill-transfer"
                    style="
                        display:block;
                        font-size:23px;
                        margin-bottom:8px;
                    "
                ></i>

                No seller withdrawals found.

            </div>

        @endif

    </div>

</div>

@endsection