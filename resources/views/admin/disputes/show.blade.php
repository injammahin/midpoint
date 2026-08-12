@extends('admin.layouts.app')


@section('title', 'Dispute ' . $transaction->reference)


@section('page-title', 'Dispute Review')



@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Friendly Labels
    |--------------------------------------------------------------------------
    */

    $reason =
        match($dispute->reason) {

            'not_received' =>
                'Item Not Received',

            'not_as_described' =>
                'Item Not As Described',

            'damaged' =>
                'Item Arrived Damaged',

            'wrong_item' =>
                'Wrong Item Received',

            'missing_parts' =>
                'Missing Parts / Items',

            default =>
                'Other Issue',
        };


    $outcome =
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
                        $dispute->desired_outcome
                    )
                ),
        };

@endphp



<div class="txn-admin-page">


    {{-- =====================================================
        HEADING
    ====================================================== --}}

    <div class="txn-admin-heading">

        <div>

            <h2>
                Dispute: {{ $transaction->reference }}
            </h2>


            <p>

                Opened

                {{
                    $dispute->opened_at
                        ? $dispute->opened_at->format(
                            'd M Y, h:i A'
                        )
                        : ''
                }}

            </p>

        </div>


        <div style="display:flex; gap:8px; flex-wrap:wrap;">

            <a
                href="{{
                    route(
                        'admin.transactions.show',
                        $transaction
                    )
                }}"
                class="txn-action-btn"
            >

                <i class="fa-solid fa-money-bill-transfer"></i>

                Transaction

            </a>


            <a
                href="{{ route('admin.disputes.index') }}"
                class="txn-action-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                All Disputes

            </a>

        </div>

    </div>



    {{-- =====================================================
        IMPORTANT WARNING
    ====================================================== --}}

    <div class="txn-dispute-alert">

        <strong>

            <i class="fa-solid fa-lock"></i>

            Seller payout is paused

        </strong>


        <p>

            This transaction is currently disputed.

            MidPoint should review the buyer's evidence and transaction
            details before any manual refund, release, or settlement action.

        </p>

    </div>



    <div class="txn-detail-grid">


        {{-- =================================================
            LEFT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- Dispute Request --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Buyer Dispute Request
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Reason
                    </span>

                    <span class="txn-badge red">

                        {{ $reason }}

                    </span>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Desired outcome
                    </span>

                    <strong>
                        {{ $outcome }}
                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Status
                    </span>

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

                </div>



                <div
                    style="
                        margin-top:18px;
                        padding:16px;
                        border:1px solid var(--admin-border);
                        border-radius:12px;
                        background:var(--admin-subtle);
                    "
                >

                    <div
                        style="
                            margin-bottom:8px;
                            color:var(--admin-heading);
                            font-size:11px;
                            font-weight:700;
                        "
                    >

                        Buyer's explanation

                    </div>


                    <div
                        style="
                            color:var(--admin-text);
                            font-size:11px;
                            line-height:1.75;
                            white-space:pre-line;
                        "
                    >{{ $dispute->description }}</div>

                </div>

            </div>



            {{-- Evidence --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Buyer Evidence
                </h3>


                @if(
                    is_array($dispute->evidence)
                    &&
                    count($dispute->evidence) > 0
                )

                    <div class="dispute-evidence-grid">

                        @foreach($dispute->evidence as $evidence)

                            @php

                                $extension =
                                    strtolower(
                                        pathinfo(
                                            $evidence,
                                            PATHINFO_EXTENSION
                                        )
                                    );


                                $fileUrl =
                                    asset(
                                        'storage/'
                                        .
                                        ltrim(
                                            $evidence,
                                            '/'
                                        )
                                    );


                                $isImage =
                                    in_array(
                                        $extension,
                                        [
                                            'jpg',
                                            'jpeg',
                                            'png',
                                            'webp',
                                        ],
                                        true
                                    );


                                $isVideo =
                                    in_array(
                                        $extension,
                                        [
                                            'mp4',
                                            'mov',
                                        ],
                                        true
                                    );

                            @endphp


                            <div class="dispute-evidence-item">

                                @if($isImage)

                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                    >

                                        <img
                                            src="{{ $fileUrl }}"
                                            alt="Dispute evidence"
                                        >

                                    </a>


                                @elseif($isVideo)

                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                        class="dispute-evidence-file"
                                    >

                                        <i class="fa-solid fa-video"></i>

                                        Open Video Evidence

                                    </a>


                                @else

                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                        class="dispute-evidence-file"
                                    >

                                        <i class="fa-solid fa-file-pdf"></i>

                                        Open Evidence File

                                    </a>

                                @endif

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="txn-empty">

                        <span>
                            No evidence files available.
                        </span>

                    </div>

                @endif

            </div>



            {{-- Return --}}
            @if(
                $dispute->return_method
                ||
                $dispute->return_proof_path
            )

                <div class="admin-card txn-detail-card">

                    <h3>
                        Return Information
                    </h3>


                    @if($dispute->return_method)

                        <div class="txn-detail-row">

                            <span>
                                Return method
                            </span>

                            <strong>
                                {{ $dispute->return_method }}
                            </strong>

                        </div>

                    @endif


                    @if($dispute->return_proof_path)

                        <div class="txn-detail-row">

                            <span>
                                Return proof
                            </span>


                            <a
                                href="{{
                                    asset(
                                        'storage/'
                                        .
                                        ltrim(
                                            $dispute->return_proof_path,
                                            '/'
                                        )
                                    )
                                }}"
                                target="_blank"
                                class="txn-action-btn"
                            >

                                <i class="fa-solid fa-paperclip"></i>

                                Open Proof

                            </a>

                        </div>

                    @endif

                </div>

            @endif

        </div>



        {{-- =================================================
            RIGHT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- Transaction --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Transaction
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
                        Item
                    </span>

                    <strong>
                        {{ $transaction->title }}
                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Amount
                    </span>

                    <strong
                        style="
                            color:#087443;
                            font-size:15px;
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


                <div class="txn-detail-row">

                    <span>
                        Payment
                    </span>

                    <span class="txn-badge green">

                        PAID

                    </span>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Current status
                    </span>

                    <span class="txn-badge red">

                        {{ $transaction->status_label }}

                    </span>

                </div>

            </div>



            {{-- Buyer --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Buyer
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Name
                    </span>

                    <strong>

                        {{
                            $dispute->buyer?->name
                            ??
                            'Buyer'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Email
                    </span>

                    <strong>

                        {{
                            $dispute->buyer?->email
                            ??
                            $transaction->buyer_email
                        }}

                    </strong>

                </div>


                @if($transaction->buyer_phone)

                    <div class="txn-detail-row">

                        <span>
                            Phone
                        </span>

                        <strong>
                            {{ $transaction->buyer_phone }}
                        </strong>

                    </div>

                @endif

            </div>



            {{-- Seller --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Seller
                </h3>


                <div class="txn-detail-row">

                    <span>
                        Name
                    </span>

                    <strong>

                        {{
                            $dispute->seller?->name
                            ??
                            'Seller'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Email
                    </span>

                    <strong>

                        {{
                            $dispute->seller?->email
                            ??
                            ''
                        }}

                    </strong>

                </div>

            </div>



            {{-- Paystack --}}
            <div class="admin-card txn-detail-card">

                <h3>
                    Payment Security
                </h3>


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


                <div class="txn-detail-row">

                    <span>
                        Seller payout
                    </span>

                    <span class="txn-badge red">

                        PAUSED

                    </span>

                </div>

            </div>



            @if($dispute->admin_note)

                <div class="admin-card txn-detail-card">

                    <h3>
                        Admin Note
                    </h3>


                    <div
                        style="
                            color:var(--admin-text);
                            font-size:11px;
                            line-height:1.7;
                            white-space:pre-line;
                        "
                    >{{ $dispute->admin_note }}</div>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection



@push('styles')

    @include(
        'admin.transactions.partials.styles'
    )

@endpush