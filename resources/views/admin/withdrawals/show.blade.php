@extends('admin.layouts.app')

@section('title', 'Withdrawal Details')


@push('styles')

<style>

    .wds-page {
        max-width: 980px;
    }


    .wds-back {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        margin-bottom: 14px;

        color: #0b6947;

        font-size: 11px;
        font-weight: 800;

        text-decoration: none;
    }


    .wds-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        gap: 16px;

        margin-bottom: 15px;
    }


    .wds-head h1 {
        margin: 0 0 6px;

        color: #17251f;

        font-size: 25px;
    }


    .wds-head p {
        margin: 0;

        color: #718078;

        font-size: 11px;
    }


    .wds-card {
        margin-bottom: 15px;

        padding: 19px;

        border: 1px solid #e2e8e5;
        border-radius: 16px;

        background: #ffffff;
    }


    .wds-card h2 {
        margin: 0 0 14px;

        color: #1a3026;

        font-size: 15px;
    }


    .wds-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;

        gap: 11px;
    }


    .wds-item {
        min-width: 0;

        padding: 12px;

        border-radius: 11px;

        background: #f7f9f8;
    }


    .wds-item span {
        display: block;

        margin-bottom: 5px;

        color: #7f8b85;

        font-size: 8px;

        text-transform: uppercase;
        letter-spacing: .07em;
    }


    .wds-item strong,
    .wds-item code {
        color: #263a30;

        font-size: 11px;

        word-break: break-word;
    }


    .wds-badge {
        display: inline-flex;
        align-items: center;

        padding: 6px 9px;

        border-radius: 999px;

        font-size: 9px;
        font-weight: 800;
    }


    .wds-badge.success {
        background: #e8f8ef;
        color: #087443;
    }


    .wds-badge.danger {
        background: #fff0f0;
        color: #bf3030;
    }


    .wds-badge.warning {
        background: #fff4dc;
        color: #9a5900;
    }


    .wds-badge.processing {
        background: #edf5ff;
        color: #2660a8;
    }


    .wds-badge.neutral {
        background: #f1f4f2;
        color: #69766f;
    }


    .wds-btn {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        padding: 10px 13px;

        border: 0;
        border-radius: 10px;

        background: #0b3d2e;

        color: #ffffff;

        cursor: pointer;

        font-size: 10px;
        font-weight: 800;

        text-decoration: none;
    }


    .wds-alert {
        margin-bottom: 14px;

        padding: 12px 13px;

        border-radius: 11px;

        font-size: 10px;
        line-height: 1.6;
    }


    .wds-alert.success {
        background: #e9f9f0;
        color: #087443;
    }


    .wds-alert.warning {
        background: #fff5df;
        color: #915700;
    }


    .wds-alert.danger {
        background: #fff0f0;
        color: #b53030;
    }


    .wds-table {
        width: 100%;

        border-collapse: collapse;
    }


    .wds-table th,
    .wds-table td {
        padding: 10px 8px;

        border-bottom: 1px solid #edf1ef;

        text-align: left;

        font-size: 10px;
    }


    .wds-table th {
        color: #85918b;

        font-size: 8px;

        text-transform: uppercase;
        letter-spacing: .08em;
    }


    @media(max-width: 700px) {

        .wds-grid {
            grid-template-columns: 1fr;
        }


        .wds-head {
            display: block;
        }


        .wds-head > div:last-child {
            margin-top: 10px;
        }

    }

</style>

@endpush



@section('content')

<div class="wds-page">

    <a
        href="{{ route('admin.withdrawals.index') }}"
        class="wds-back"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to withdrawals

    </a>



    @if(session('success'))

        <div class="wds-alert success">

            {{ session('success') }}

        </div>

    @endif



    <div class="wds-head">

        <div>

            <h1>
                Withdrawal Details
            </h1>

            <p>
                {{ $withdrawal->reference }}
            </p>

        </div>


        <div
            style="
                display:flex;
                align-items:center;
                gap:8px;
                flex-wrap:wrap;
            "
        >

            <span
                class="
                    wds-badge
                    {{ $withdrawal->status_tone }}
                "
            >

                {{ $withdrawal->status_label }}

            </span>


            @if(!$withdrawal->isFinal())

                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.withdrawals.sync',
                            $withdrawal
                        )
                    }}"
                >

                    @csrf


                    <button
                        type="submit"
                        class="wds-btn"
                    >

                        <i class="fa-solid fa-arrows-rotate"></i>

                        Sync Paystack status

                    </button>

                </form>

            @endif

        </div>

    </div>



    @if(
        $withdrawal->status
        ===
        \App\Models\SellerWithdrawal::STATUS_OTP
    )

        <div class="wds-alert warning">

            Paystack is requiring OTP/manual transfer confirmation.

            For fully automatic payouts, disable Transfer Confirmation OTP
            in the Paystack dashboard and use the Server Approval URL.

        </div>

    @endif



    @if(
        in_array(
            $withdrawal->status,
            [
                \App\Models\SellerWithdrawal::STATUS_FAILED,
                \App\Models\SellerWithdrawal::STATUS_REVERSED,
            ],
            true
        )
    )

        <div class="wds-alert danger">

            <strong>
                Payout did not complete.
            </strong>

            <br>

            {{
                $withdrawal->failure_reason
                ?:
                'No failure reason was returned.'
            }}

        </div>

    @endif



    {{-- =========================================================
        WITHDRAWAL
    ========================================================== --}}

    <section class="wds-card">

        <h2>
            Withdrawal
        </h2>


        <div class="wds-grid">

            <div class="wds-item">

                <span>Amount</span>

                <strong>
                    ₦{{ number_format((float) $withdrawal->amount, 2) }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Currency</span>

                <strong>
                    {{ $withdrawal->currency }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Midpoint reference</span>

                <code>
                    {{ $withdrawal->reference }}
                </code>

            </div>


            <div class="wds-item">

                <span>Paystack reference</span>

                <code>
                    {{
                        $withdrawal->paystack_transfer_reference
                        ?:
                        'Not assigned'
                    }}
                </code>

            </div>


            <div class="wds-item">

                <span>Paystack transfer code</span>

                <code>
                    {{
                        $withdrawal->paystack_transfer_code
                        ?:
                        'Pending'
                    }}
                </code>

            </div>


            <div class="wds-item">

                <span>Paystack recipient</span>

                <code>
                    {{ $withdrawal->paystack_recipient_code }}
                </code>

            </div>


            <div class="wds-item">

                <span>Requested</span>

                <strong>
                    {{
                        optional(
                            $withdrawal->requested_at
                        )
                        ->format(
                            'd M Y, h:i A'
                        )
                        ?:
                        '—'
                    }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Initiated</span>

                <strong>
                    {{
                        optional(
                            $withdrawal->initiated_at
                        )
                        ->format(
                            'd M Y, h:i A'
                        )
                        ?:
                        '—'
                    }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Completed</span>

                <strong>
                    {{
                        optional(
                            $withdrawal->completed_at
                        )
                        ->format(
                            'd M Y, h:i A'
                        )
                        ?:
                        '—'
                    }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Failed / reversed</span>

                <strong>
                    {{
                        optional(
                            $withdrawal->failed_at
                        )
                        ->format(
                            'd M Y, h:i A'
                        )
                        ?:
                        '—'
                    }}
                </strong>

            </div>

        </div>

    </section>



    {{-- =========================================================
        SELLER
    ========================================================== --}}

    <section class="wds-card">

        <h2>
            Seller
        </h2>


        <div class="wds-grid">

            <div class="wds-item">

                <span>Name</span>

                <strong>
                    {{ $withdrawal->seller?->name }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Email</span>

                <strong>
                    {{ $withdrawal->seller?->email }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Phone</span>

                <strong>
                    {{
                        $withdrawal->seller?->phone
                        ?:
                        '—'
                    }}
                </strong>

            </div>


            <div class="wds-item">

                <span>KYC</span>

                <strong>
                    {{
                        $kyc
                            ? $kyc->status_label
                            : 'Not found'
                    }}
                </strong>

            </div>

        </div>

    </section>



    {{-- =========================================================
        BANK
    ========================================================== --}}

    <section class="wds-card">

        <h2>
            Bank Destination
        </h2>


        <div class="wds-grid">

            <div class="wds-item">

                <span>Bank</span>

                <strong>
                    {{ $withdrawal->bank_name }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Account name</span>

                <strong>
                    {{ $withdrawal->account_name }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Account</span>

                <strong>
                    ••••{{ $withdrawal->account_number_last4 }}
                </strong>

            </div>


            <div class="wds-item">

                <span>Saved bank still exists</span>

                <strong>

                    {{
                        $withdrawal->withdrawalAccount
                            ? 'Yes'
                            : 'No / deleted'
                    }}

                </strong>

            </div>

        </div>

    </section>



    {{-- =========================================================
        WALLET LEDGER
    ========================================================== --}}

    <section class="wds-card">

        <h2>
            Wallet Accounting
        </h2>


        @if($withdrawal->walletTransactions->count())

            <div style="overflow-x:auto;">

                <table class="wds-table">

                    <thead>

                        <tr>

                            <th>Type</th>

                            <th>Direction</th>

                            <th>Status</th>

                            <th>Amount</th>

                            <th>Created</th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $withdrawal->walletTransactions as $entry
                        )

                            <tr>

                                <td>
                                    {{
                                        ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $entry->type
                                            )
                                        )
                                    }}
                                </td>


                                <td>
                                    {{ ucfirst($entry->direction) }}
                                </td>


                                <td>
                                    {{ ucfirst($entry->status) }}
                                </td>


                                <td>

                                    ₦{{ number_format(
                                        (float)
                                        $entry->amount,
                                        2
                                    ) }}

                                </td>


                                <td>

                                    {{
                                        $entry
                                            ->created_at
                                            ?->format(
                                                'd M Y, h:i A'
                                            )
                                    }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div
                style="
                    color:#7c8882;
                    font-size:11px;
                "
            >

                No wallet ledger entries found for this withdrawal.

            </div>

        @endif

    </section>



    {{-- =========================================================
        METADATA
    ========================================================== --}}

    @if($withdrawal->meta)

        <section class="wds-card">

            <h2>
                Paystack / System Metadata
            </h2>


            <pre
                style="
                    margin:0;
                    padding:13px;
                    border-radius:11px;
                    background:#f6f8f7;
                    color:#34483d;
                    font-size:10px;
                    line-height:1.6;
                    overflow:auto;
                "
            >{{ json_encode(
                $withdrawal->meta,
                JSON_PRETTY_PRINT
                |
                JSON_UNESCAPED_SLASHES
            ) }}</pre>

        </section>

    @endif

</div>

@endsection