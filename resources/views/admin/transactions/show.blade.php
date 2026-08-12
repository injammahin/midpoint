@extends('admin.layouts.app')


@section('title', 'Transaction ' . $transaction->reference)


@section('page-title', 'Transaction Monitor')



@section('content')

<div class="txn-admin-page">


    {{-- =====================================================
        HEADING
    ====================================================== --}}

    <div class="txn-admin-heading">

        <div>

            <h2>
                {{ $transaction->reference }}
            </h2>


            <p>

                Buyer-paid secure transaction monitoring.

                Payment was successfully secured on

                <strong>

                    {{
                        $transaction->paid_at
                            ? $transaction->paid_at->format(
                                'd M Y, h:i A'
                            )
                            : 'N/A'
                    }}

                </strong>

            </p>

        </div>


        <a
            href="{{ route('admin.transactions.index') }}"
            class="txn-action-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Transactions

        </a>

    </div>



    {{-- =====================================================
        DISPUTE WARNING
    ====================================================== --}}

    @if($transaction->dispute)

        <div class="txn-dispute-alert">

            <strong>

                <i class="fa-solid fa-triangle-exclamation"></i>

                This transaction is disputed

            </strong>


            <p>

                Buyer opened a dispute.

                Automatic seller payout has been paused while
                MidPoint reviews the dispute request.

            </p>


            <a
                href="{{
                    route(
                        'admin.disputes.show',
                        $transaction->dispute
                    )
                }}"
                class="txn-action-btn"
                style="margin-top:12px;"
            >

                Review Dispute

            </a>

        </div>

    @endif



    <div class="txn-detail-grid">


        {{-- =================================================
            LEFT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- Item --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Transaction Information
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Reference
                    </span>

                    <strong>
                        {{ $transaction->reference }}
                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Type
                    </span>

                    <strong>

                        {{
                            ucfirst(
                                $transaction->transaction_type
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Item / Service
                    </span>

                    <strong>
                        {{ $transaction->title }}
                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Description
                    </span>

                    <div style="max-width:500px;">

                        {{
                            $transaction->description
                            ??
                            'No description'
                        }}

                    </div>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Quantity
                    </span>

                    <strong>
                        {{ number_format($transaction->quantity) }}
                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Unit price
                    </span>

                    <strong>

                        ₦{{
                            number_format(
                                (float) $transaction->unit_price,
                                2
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Delivery fee
                    </span>

                    <strong>

                        ₦{{
                            number_format(
                                (float) $transaction->delivery_fee,
                                2
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Total
                    </span>

                    <strong
                        style="
                            color:#087443;
                            font-size:16px;
                        "
                    >

                        ₦{{
                            number_format(
                                (float) $transaction->total_amount,
                                2
                            )
                        }}

                    </strong>

                </div>

            </div>



            {{-- Buyer / Seller --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Participants
                </h3>


                <div class="txn-party-grid">

                    <div class="txn-party">

                        <div class="txn-party-label">
                            Buyer
                        </div>


                        <strong>

                            {{
                                $transaction->buyer?->name
                                ??
                                'Buyer'
                            }}

                        </strong>


                        <span>

                            {{
                                $transaction->buyer?->email
                                ??
                                $transaction->buyer_email
                            }}

                        </span>


                        @if($transaction->buyer_phone)

                            <span>
                                {{ $transaction->buyer_phone }}
                            </span>

                        @endif

                    </div>



                    <div class="txn-party">

                        <div class="txn-party-label">
                            Seller
                        </div>


                        <strong>

                            {{
                                $transaction->seller?->name
                                ??
                                'Seller'
                            }}

                        </strong>


                        <span>

                            {{
                                $transaction->seller?->email
                                ??
                                ''
                            }}

                        </span>


                        @if($transaction->seller?->phone)

                            <span>
                                {{ $transaction->seller->phone }}
                            </span>

                        @endif

                    </div>

                </div>

            </div>



            {{-- Payment --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Buyer Payment
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Payment status
                    </span>

                    <span class="txn-badge green">

                        PAID & SECURED

                    </span>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Amount paid
                    </span>

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

                </div>


                <div class="txn-detail-row">

                    <span>
                        Paystack reference
                    </span>

                    <strong>

                        {{
                            $transaction->paystack_reference
                            ??
                            $transaction->successfulPayment?->reference
                            ??
                            '-'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Paystack transaction ID
                    </span>

                    <strong>

                        {{
                            $transaction->paystack_transaction_id
                            ??
                            $transaction->successfulPayment?->paystack_transaction_id
                            ??
                            '-'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Payment channel
                    </span>

                    <strong>

                        {{
                            $transaction->successfulPayment?->channel

                                ? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $transaction
                                            ->successfulPayment
                                            ->channel
                                    )
                                )

                                : 'Paystack'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Paid at
                    </span>

                    <strong>

                        {{
                            $transaction->paid_at
                                ? $transaction->paid_at->format(
                                    'd M Y, h:i A'
                                )
                                : '-'
                        }}

                    </strong>

                </div>

            </div>

        </div>



        {{-- =================================================
            RIGHT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- Current Status --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Current Status
                </h3>


                <div style="margin-bottom:17px;">

                    <span
                        class="
                            txn-badge

                            {{
                                $transaction->status === 'disputed'
                                    ? 'red'
                                    : (
                                        $transaction->status === 'completed'
                                            ? 'green'
                                            : 'blue'
                                    )
                            }}
                        "
                    >

                        {{ $transaction->status_label }}

                    </span>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Payout
                    </span>

                    <strong>

                        {{
                            $transaction->payout_status
                                ? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $transaction->payout_status
                                    )
                                )
                                : 'Locked'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Inspection
                    </span>

                    <strong>

                        {{ $transaction->inspection_hours }}

                        hours

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Disputed
                    </span>

                    <strong>

                        {{
                            $transaction->dispute
                                ? 'Yes'
                                : 'No'
                        }}

                    </strong>

                </div>

            </div>



            {{-- Money Breakdown --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Settlement Breakdown
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Gross amount
                    </span>

                    <strong>

                        ₦{{
                            number_format(
                                (float) $transaction->total_amount,
                                2
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Service fee
                    </span>

                    <strong>

                        ₦{{
                            number_format(
                                (float) (
                                    $transaction->service_fee_amount
                                    ?? 0
                                ),
                                2
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        VAT
                    </span>

                    <strong>

                        ₦{{
                            number_format(
                                (float) (
                                    $transaction->vat_amount
                                    ?? 0
                                ),
                                2
                            )
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Seller net
                    </span>

                    <strong
                        style="color:#087443;"
                    >

                        ₦{{
                            number_format(
                                (float) (
                                    $transaction->seller_net_amount
                                    ?? 0
                                ),
                                2
                            )
                        }}

                    </strong>

                </div>

            </div>



            {{-- Timeline --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Transaction Timeline
                </h3>


                @foreach([
                    'Payment secured' => $transaction->paid_at,
                    'Preparing item' => $transaction->preparing_at,
                    'Dispatched' => $transaction->dispatched_at,
                    'In transit' => $transaction->in_transit_at,
                    'Delivered' => $transaction->delivered_at,
                    'Inspection started' => $transaction->inspection_started_at,
                    'Release approved' => $transaction->release_approved_at,
                    'Funds released' => $transaction->funds_released_at,
                    'Completed' => $transaction->completed_at,
                ] as $label => $date)

                    @if($date)

                        <div class="txn-detail-row">

                            <span>
                                {{ $label }}
                            </span>


                            <strong>

                                {{
                                    $date->format(
                                        'd M Y, h:i A'
                                    )
                                }}

                            </strong>

                        </div>

                    @endif

                @endforeach

            </div>

        </div>

    </div>

</div>

@endsection



@push('styles')

    @include(
        'admin.transactions.partials.styles'
    )

@endpush