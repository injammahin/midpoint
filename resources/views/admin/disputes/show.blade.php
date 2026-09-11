@extends('admin.layouts.app')


@section(
    'title',
    'Dispute #'
    .
    $dispute->id
    .
    ' | Midpoint Admin'
)


@section('content')

    @php

        $paidAmount =
            round(
                (float) 
                (
                    $transaction->paid_amount
                    ?:
                    $transaction->total_amount
                ),
                2
            );


        $serviceFeeRate =
            (float) 
            config(
                'secure_transactions.service_fee_percent',
                5
            );


        $vatRate =
            (float) 
            config(
                'secure_transactions.fee_vat_percent',
                7.5
            );


        $successfulPayment =
            $transaction->successfulPayment;


        $refundStatus =
            $dispute->paystack_refund_status
            ?:
            null;

    @endphp


    <div class="adp-page">

        {{-- =========================================================
        HEADER
        ========================================================== --}}

        <div class="adp-header">

            <div>

                <div class="adp-eyebrow">
                    Transaction dispute
                </div>


                <h1>

                    Dispute #{{ $dispute->id }}

                </h1>


                <p>

                    {{ $transaction->reference }}

                    ·

                    {{
        $transaction->title
        ?:
        'Secure transaction'
                        }}

                </p>

            </div>


            <div class="adp-header-actions">

                <a href="{{ route('admin.disputes.index') }}" class="adp-button secondary">

                    <i class="fa-solid fa-arrow-left"></i>

                    All disputes

                </a>


                @if (!$dispute->isRoomActivated() && !$dispute->isResolved())

                        <form method="POST" action="{{
                    route(
                        'admin.disputes.room.activate',
                        $dispute
                    )
                                            }}">

                            @csrf


                            <button type="submit" class="adp-button primary">

                                <i class="fa-solid fa-comments"></i>

                                Activate resolution room

                            </button>

                        </form>

                @endif


                @if ($dispute->isRoomActive())

                        <form method="POST" action="{{
                    route(
                        'admin.disputes.room.close',
                        $dispute
                    )
                                            }}"
                            onsubmit="return confirm('Close this room now? Buyer and seller will be redirected to their transaction and the room will become read-only.');">

                            @csrf


                            <input type="hidden" name="close_reason"
                                value="Midpoint Support closed this room. Any final decision or further status update will appear on the transaction page.">


                            <button type="submit" class="adp-button danger">

                                <i class="fa-solid fa-door-closed"></i>

                                Close room

                            </button>

                        </form>

                @endif

            </div>

        </div>



        {{-- =========================================================
        FLASH
        ========================================================== --}}

        @if (session('success'))

            <div class="adp-alert success">

                <i class="fa-solid fa-circle-check"></i>

                {{ session('success') }}

            </div>

        @endif


        @if (session('error'))

            <div class="adp-alert error">

                <i class="fa-solid fa-circle-exclamation"></i>

                {{ session('error') }}

            </div>

        @endif


        @if ($errors->any())

            <div class="adp-alert error">

                <i class="fa-solid fa-circle-exclamation"></i>

                {{ $errors->first() }}

            </div>

        @endif



        {{-- =========================================================
        TOP METRICS
        ========================================================== --}}

        <div class="adp-metrics">

            <div>

                <span>
                    Buyer paid
                </span>

                <strong>
                    ₦{{ number_format($paidAmount, 2) }}
                </strong>

            </div>


            <div>

                <span>
                    Seller net before dispute
                </span>

                <strong>
                    ₦{{
        number_format(
            (float) 
            $transaction->seller_net_amount,
            2
        )
                        }}
                </strong>

            </div>


            <div>

                <span>
                    Dispute
                </span>

                <strong>
                    {{ $dispute->status_label }}
                </strong>

            </div>


            <div>

                <span>
                    Seller payout
                </span>

                <strong class="locked">
                    Locked
                </strong>

            </div>

        </div>



        <div class="adp-layout">

            {{-- =====================================================
            MAIN
            ====================================================== --}}

            <main class="adp-main">

                {{-- =================================================
                DISPUTE INFORMATION
                ================================================== --}}

                <section class="adp-card">

                    <div class="adp-card-head">

                        <div>

                            <h2>
                                Dispute information
                            </h2>

                            <p>
                                Original buyer complaint and submitted evidence.
                            </p>

                        </div>


                        <span class="adp-status-badge">

                            {{ $dispute->status_label }}

                        </span>

                    </div>


                    <div class="adp-parties">

                        <div>

                            <span>
                                Buyer
                            </span>

                            <strong>
                                {{ $dispute->buyer?->name ?: 'Buyer' }}
                            </strong>

                            <small>
                                {{ $dispute->buyer?->email }}
                            </small>

                        </div>


                        <div>

                            <span>
                                Seller
                            </span>

                            <strong>
                                {{ $dispute->seller?->name ?: 'Seller' }}
                            </strong>

                            <small>
                                {{ $dispute->seller?->email }}
                            </small>

                        </div>

                    </div>


                    <div class="adp-detail-grid">

                        <div>

                            <span>
                                Reason
                            </span>

                            <strong>

                                {{
        ucwords(
            str_replace(
                '_',
                ' ',
                $dispute->reason
            )
        )
                                    }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                Desired outcome
                            </span>

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

                        </div>


                        <div>

                            <span>
                                Opened
                            </span>

                            <strong>

                                {{
        optional(
            $dispute->opened_at
        )->format(
                'd M Y, h:i A'
            )
                                    }}

                            </strong>

                        </div>


                        <div>

                            <span>
                                Paystack reference
                            </span>

                            <strong>

                                {{
        $successfulPayment?->reference
        ?:
        $transaction->paystack_reference
        ?:
        '—'
                                    }}

                            </strong>

                        </div>

                    </div>


                    <div class="adp-description">

                        {!!
        nl2br(
            e(
                $dispute->description
            )
        )
                            !!}

                    </div>


                    @if (
                            is_array(
                                $dispute->evidence
                            )
                            &&
                            count(
                                $dispute->evidence
                            )
                        )

                        <div class="adp-evidence">

                            <h3>
                                Initial evidence
                            </h3>


                            <div>

                                @foreach ($dispute->evidence as $evidence)

                                                <a href="{{
                                    asset(
                                        'storage/'
                                        .
                                        ltrim(
                                            $evidence,
                                            '/'
                                        )
                                    )
                                                                                    }}" target="_blank" rel="noopener">

                                                    <i class="fa-solid fa-paperclip"></i>

                                                    {{
                                    basename(
                                        $evidence
                                    )
                                                                                    }}

                                                </a>

                                @endforeach

                            </div>

                        </div>

                    @endif

                </section>



                {{-- =================================================
                RESOLUTION ROOM
                ================================================== --}}

                @include(
                    'shared.disputes.room-panel',
                    [
                        'dispute' =>
                            $dispute,

                        'messages' =>
                            $messages,

                        'roomRole' =>
                            'admin',

                        'adminMode' =>
                            true,
                    ]
                )

            </main>



            {{-- =====================================================
            SIDEBAR
            ====================================================== --}}

            <aside class="adp-sidebar">

                {{-- =================================================
                WORKFLOW
                ================================================== --}}

                @if (!$dispute->isResolved())

                        <section class="adp-card">

                            <div class="adp-card-head compact">

                                <div>

                                    <h2>
                                        Case workflow
                                    </h2>

                                    <p>
                                        Request action without making a financial decision.
                                    </p>

                                </div>

                            </div>


                            <form method="POST" action="{{
                    route(
                        'admin.disputes.status.update',
                        $dispute
                    )
                                                }}" class="adp-form">

                                @csrf
                                @method('PATCH')


                                <label for="status">
                                    Status
                                </label>


                                <select id="status" name="status" required>

                                    <option value="under_review">
                                        Under review
                                    </option>

                                    <option value="awaiting_buyer">
                                        Awaiting buyer
                                    </option>

                                    <option value="awaiting_seller">
                                        Awaiting seller
                                    </option>

                                </select>


                                <label for="note">
                                    Workflow note
                                </label>


                                <textarea id="note" name="note" rows="4" maxlength="5000"
                                    placeholder="Explain what information or action is required..."></textarea>


                                <button type="submit" class="adp-button dark full">

                                    Update workflow

                                </button>

                            </form>

                        </section>

                @endif



                {{-- =================================================
                PAYSTACK REFUND STATE
                ================================================== --}}

                @if ($dispute->hasRefundResolution())

                        <section class="adp-card">

                            <div class="adp-card-head compact">

                                <div>

                                    <h2>
                                        Paystack refund
                                    </h2>

                                    <p>
                                        Gateway refund state for this dispute decision.
                                    </p>

                                </div>

                            </div>


                            <div class="adp-refund-summary">

                                <div>

                                    <span>
                                        Refund amount
                                    </span>

                                    <strong>
                                        ₦{{
                    number_format(
                        (float) 
                        $dispute->refund_amount,
                        2
                    )
                                                        }}
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Paystack status
                                    </span>

                                    <strong>

                                        {{
                    $refundStatus
                    ? ucwords(
                        str_replace(
                            [
                                '_',
                                '-',
                            ],
                            ' ',
                            $refundStatus
                        )
                    )
                    : 'Waiting'
                                                        }}

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Refund ID
                                    </span>

                                    <strong>
                                        {{ $dispute->paystack_refund_id ?: '—' }}
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Expected
                                    </span>

                                    <strong>

                                        {{
                    optional(
                        $dispute->refund_expected_at
                    )->format(
                            'd M Y'
                        )
                    ?:
                    '—'
                                                        }}

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Approved amount (kobo)
                                    </span>

                                    <strong>
                                        {{
                    $dispute->refund_amount_subunit
                    ??
                    '—'
                                                        }}
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Paystack amount (kobo)
                                    </span>

                                    <strong>
                                        {{
                    $dispute->paystack_refund_amount_subunit
                    ??
                    '—'
                                                        }}
                                    </strong>

                                </div>

                            </div>


                            @if ($dispute->refund_error)

                                <div class="adp-refund-error">

                                    {{ $dispute->refund_error }}

                                </div>

                            @endif


                            @if (!$dispute->refund_processed_at)

                                    <form method="POST" action="{{
                                route(
                                    'admin.disputes.refund.sync',
                                    $dispute
                                )
                                                                    }}">

                                        @csrf


                                        <button type="submit" class="adp-button secondary full">

                                            <i class="fa-solid fa-rotate"></i>

                                            Sync Paystack refund

                                        </button>

                                    </form>

                            @endif

                        </section>

                @endif



                {{-- =================================================
                FINAL DECISION
                ================================================== --}}

                @if (
                        !$dispute->isResolved()
                        &&
                        !$dispute->resolution_type
                        &&
                        !in_array(
                            $dispute->resolution_status,
                            [
                                \App\Models\TransactionDispute::RESOLUTION_STATUS_INITIATING,
                                \App\Models\TransactionDispute::RESOLUTION_STATUS_REFUND_PENDING,
                                \App\Models\TransactionDispute::RESOLUTION_STATUS_REFUND_PROCESSING,
                                \App\Models\TransactionDispute::RESOLUTION_STATUS_REFUND_NEEDS_ATTENTION,
                                \App\Models\TransactionDispute::RESOLUTION_STATUS_REFUND_SYNC_REQUIRED,
                            ],
                            true
                        )
                    )

                    <section class="adp-card decision">

                        <div class="adp-card-head compact">

                            <div>

                                <h2>
                                    Final decision
                                </h2>

                                <p>
                                    Financial actions are final. Review the evidence first.
                                </p>

                            </div>

                        </div>


                        @if (!$dispute->isRoomActivated())

                            <div class="adp-decision-lock">

                                <i class="fa-solid fa-lock"></i>

                                Activate the resolution room before making a final decision.

                            </div>

                        @else

                                <form method="POST" action="{{
                            route(
                                'admin.disputes.resolve',
                                $dispute
                            )
                                                            }}" class="adp-form" id="resolutionForm">

                                    @csrf


                                    <label for="resolutionType">
                                        Resolution
                                    </label>


                                    <select id="resolutionType" name="resolution_type" required>

                                        <option value="">
                                            Select decision
                                        </option>

                                        <option value="full_refund">
                                            Full refund to buyer
                                        </option>

                                        <option value="partial_refund">
                                            Partial refund + seller settlement
                                        </option>

                                        <option value="release_to_seller">
                                            Release seller entitlement
                                        </option>

                                        <option value="resume_transaction">
                                            Resume normal transaction
                                        </option>

                                    </select>


                                    <div id="partialRefundField" hidden>

                                        <label for="refundAmount">
                                            Buyer refund amount (₦)
                                        </label>


                                        <input id="refundAmount" type="number" name="refund_amount" min="0.01"
                                            max="{{ max(0, $paidAmount - 0.01) }}" step="0.01" placeholder="50000">

                                    </div>


                                    <div id="resolutionPreview" class="adp-resolution-preview" hidden data-paid="{{ $paidAmount }}"
                                        data-service-fee-rate="{{ $serviceFeeRate }}" data-vat-rate="{{ $vatRate }}"
                                        data-existing-seller-net="{{
                            (float) 
                            $transaction->seller_net_amount
                                                                }}"></div>


                                    <label for="resolutionNote">
                                        Decision reason
                                    </label>


                                    <textarea id="resolutionNote" name="resolution_note" rows="5" minlength="20" maxlength="5000"
                                        required
                                        placeholder="Explain the evidence reviewed and why Midpoint made this decision..."></textarea>


                                    <div class="adp-paystack-note">

                                        <i class="fa-solid fa-circle-info"></i>

                                        Full/partial refunds are sent through Paystack using
                                        the original successful payment transaction. Midpoint
                                        does not deduct its service fee from the refunded
                                        portion. Paystack's original transaction processing
                                        charge is handled by Paystack/merchant settlement and
                                        is not manually subtracted from the buyer refund here.

                                    </div>


                                    <button type="submit" class="adp-button danger full" onclick="
                                                    return confirm(
                                                        'Confirm this final dispute decision? Financial actions cannot be casually reversed.'
                                                    );
                                                ">

                                        <i class="fa-solid fa-gavel"></i>

                                        Confirm final decision

                                    </button>

                                </form>

                        @endif

                    </section>

                @endif



                {{-- =================================================
                RESOLUTION SUMMARY
                ================================================== --}}

                @if ($dispute->resolution_type)

                        <section class="adp-card">

                            <div class="adp-card-head compact">

                                <div>

                                    <h2>
                                        Resolution record
                                    </h2>

                                </div>

                            </div>


                            <div class="adp-resolution-record">

                                <div>

                                    <span>
                                        Decision
                                    </span>

                                    <strong>
                                        {{ $dispute->resolution_type_label }}
                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Refund
                                    </span>

                                    <strong>

                                        ₦{{
                    number_format(
                        (float) 
                        $dispute->refund_amount,
                        2
                    )
                                                        }}

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Seller settlement
                                    </span>

                                    <strong>

                                        ₦{{
                    number_format(
                        (float) 
                        $dispute->seller_settlement_amount,
                        2
                    )
                                                        }}

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Resolution status
                                    </span>

                                    <strong>

                                        {{
                    ucwords(
                        str_replace(
                            '_',
                            ' ',
                            (string) 
                            $dispute->resolution_status
                        )
                    )
                                                        }}

                                    </strong>

                                </div>

                            </div>


                            @if ($dispute->resolution_note)

                                    <div class="adp-resolution-note">

                                        {!!
                                nl2br(
                                    e(
                                        $dispute->resolution_note
                                    )
                                )
                                                                    !!}

                                    </div>

                            @endif

                        </section>

                @endif

            </aside>

        </div>

    </div>


    @push('styles')

        <style>
            .adp-page {
                width: 100%;
            }

            .adp-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 18px;
            }

            .adp-eyebrow {
                margin-bottom: 4px;
                color: #12B76A;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .1em;
                text-transform: uppercase;
            }

            .adp-header h1 {
                margin: 0;
                color: #13231B;
                font-family: 'Bricolage Grotesque', sans-serif;
                font-size: 25px;
                font-weight: 800;
            }

            .adp-header p {
                margin: 4px 0 0;
                color: #718078;
                font-size: 12px;
            }

            .adp-header-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .adp-button {
                min-height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 0 12px;
                border: 0;
                border-radius: 9px;
                font-size: 11px;
                font-weight: 800;
                text-decoration: none;
                cursor: pointer;
            }

            .adp-button.full {
                width: 100%;
            }

            .adp-button.primary {
                background: #12B76A;
                color: #FFFFFF;
            }

            .adp-button.dark {
                background: #0B3D2E;
                color: #FFFFFF;
            }

            .adp-button.secondary {
                border: 1px solid #DCE5E0;
                background: #FFFFFF;
                color: #0B3D2E;
            }

            .adp-button.danger {
                background: #B42318;
                color: #FFFFFF;
            }

            .adp-alert {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                margin-bottom: 14px;
                padding: 11px 13px;
                border-radius: 10px;
                font-size: 11px;
            }

            .adp-alert.success {
                border: 1px solid #BCE7CF;
                background: #ECFDF3;
                color: #067647;
            }

            .adp-alert.error {
                border: 1px solid #F5C5C0;
                background: #FFF1F0;
                color: #B42318;
            }

            .adp-metrics {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 15px;
            }

            .adp-metrics>div {
                padding: 13px;
                border: 1px solid #E0E7E3;
                border-radius: 11px;
                background: #FFFFFF;
            }

            .adp-metrics span,
            .adp-metrics strong {
                display: block;
            }

            .adp-metrics span {
                color: #7C8882;
                font-size: 10px;
            }

            .adp-metrics strong {
                margin-top: 3px;
                color: #223129;
                font-size: 12px;
            }

            .adp-metrics strong.locked {
                color: #B54708;
            }

            .adp-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 330px;
                align-items: start;
                gap: 15px;
            }

            .adp-main,
            .adp-sidebar {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .adp-sidebar {
                position: sticky;
                top: 88px;
            }

            .adp-card {
                padding: 17px;
                border: 1px solid #DCE5E0;
                border-radius: 15px;
                background: #FFFFFF;
                box-shadow: 0 12px 35px -32px rgba(11, 61, 46, .30);
            }

            .adp-card.decision {
                border-color: #F2CBC7;
            }

            .adp-card-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 15px;
            }

            .adp-card-head.compact {
                margin-bottom: 12px;
            }

            .adp-card-head h2 {
                margin: 0;
                color: #17251F;
                font-size: 12px;
                font-weight: 800;
            }

            .adp-card-head p {
                margin: 3px 0 0;
                color: #7A8780;
                font-size: 10px;
                line-height: 1.5;
            }

            .adp-status-badge {
                padding: 6px 9px;
                border-radius: 999px;
                background: #FFF4E5;
                color: #B54708;
                font-size: 10px;
                font-weight: 800;
            }

            .adp-parties {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 9px;
                margin-bottom: 10px;
            }

            .adp-parties>div {
                padding: 11px;
                border-radius: 10px;
                background: #F7F9F8;
            }

            .adp-parties span,
            .adp-parties strong,
            .adp-parties small {
                display: block;
            }

            .adp-parties span {
                color: #7B8781;
                font-size: 9px;
                text-transform: uppercase;
            }

            .adp-parties strong {
                margin-top: 3px;
                color: #24332B;
                font-size: 12px;
            }

            .adp-parties small {
                margin-top: 2px;
                color: #7A8780;
                font-size: 10px;
            }

            .adp-detail-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .adp-detail-grid>div {
                padding: 10px;
                border: 1px solid #E7ECE9;
                border-radius: 9px;
            }

            .adp-detail-grid span,
            .adp-detail-grid strong {
                display: block;
            }

            .adp-detail-grid span {
                color: #7D8983;
                font-size: 9px;
            }

            .adp-detail-grid strong {
                margin-top: 2px;
                color: #314038;
                font-size: 11px;
                overflow-wrap: anywhere;
            }

            .adp-description {
                margin-top: 12px;
                padding: 12px;
                border-left: 3px solid #12B76A;
                border-radius: 0 9px 9px 0;
                background: #F7FAF8;
                color: #4E5C54;
                font-size: 12px;
                line-height: 1.65;
            }

            .adp-evidence {
                margin-top: 13px;
            }

            .adp-evidence h3 {
                margin: 0 0 7px;
                color: #26342D;
                font-size: 11px;
            }

            .adp-evidence>div {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .adp-evidence a {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                max-width: 100%;
                padding: 6px 8px;
                border: 1px solid #DCE5E0;
                border-radius: 8px;
                color: #0B3D2E;
                font-size: 10px;
                font-weight: 700;
                text-decoration: none;
            }

            .adp-form label {
                display: block;
                margin-bottom: 5px;
                color: #415047;
                font-size: 10px;
                font-weight: 800;
            }

            .adp-form select,
            .adp-form input,
            .adp-form textarea {
                width: 100%;
                margin-bottom: 11px;
                border: 1px solid #DCE5E0;
                border-radius: 9px;
                background: #FBFCFB;
                color: #28372F;
                font-family: inherit;
                font-size: 11px;
                outline: none;
            }

            .adp-form select,
            .adp-form input {
                height: 38px;
                padding: 0 10px;
            }

            .adp-form textarea {
                padding: 9px 10px;
                line-height: 1.55;
                resize: vertical;
            }

            .adp-form select:focus,
            .adp-form input:focus,
            .adp-form textarea:focus {
                border-color: #12B76A;
                box-shadow: 0 0 0 3px rgba(18, 183, 106, .08);
            }

            .adp-refund-summary,
            .adp-resolution-record {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 7px;
                margin-bottom: 11px;
            }

            .adp-refund-summary>div,
            .adp-resolution-record>div {
                padding: 8px;
                border-radius: 8px;
                background: #F7F9F8;
            }

            .adp-refund-summary span,
            .adp-refund-summary strong,
            .adp-resolution-record span,
            .adp-resolution-record strong {
                display: block;
            }

            .adp-refund-summary span,
            .adp-resolution-record span {
                color: #7B8781;
                font-size: 9px;
            }

            .adp-refund-summary strong,
            .adp-resolution-record strong {
                margin-top: 2px;
                color: #314038;
                font-size: 10px;
                overflow-wrap: anywhere;
            }

            .adp-refund-error {
                margin-bottom: 10px;
                padding: 8px;
                border-radius: 8px;
                background: #FFF1F0;
                color: #B42318;
                font-size: 10px;
                line-height: 1.5;
            }

            .adp-decision-lock,
            .adp-paystack-note {
                display: flex;
                align-items: flex-start;
                gap: 7px;
                margin-bottom: 10px;
                padding: 9px;
                border-radius: 9px;
                font-size: 10px;
                line-height: 1.5;
            }

            .adp-decision-lock {
                background: #FFF6E8;
                color: #925B0A;
            }

            .adp-paystack-note {
                background: #F1F7F4;
                color: #587068;
            }

            .adp-resolution-preview {
                margin-bottom: 11px;
                padding: 10px;
                border-radius: 9px;
                background: #F5F8F6;
                color: #48574F;
                font-size: 10px;
                line-height: 1.8;
            }

            .adp-resolution-preview strong {
                color: #0B3D2E;
            }

            .adp-resolution-note {
                padding: 9px;
                border-radius: 8px;
                background: #F7F9F8;
                color: #56645C;
                font-size: 10px;
                line-height: 1.6;
            }

            @media(max-width: 1050px) {
                .adp-layout {
                    grid-template-columns: 1fr;
                }

                .adp-sidebar {
                    position: static;
                }
            }

            @media(max-width: 720px) {
                .adp-header {
                    flex-direction: column;
                }

                .adp-metrics {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media(max-width: 520px) {

                .adp-metrics,
                .adp-parties,
                .adp-detail-grid,
                .adp-refund-summary,
                .adp-resolution-record {
                    grid-template-columns: 1fr;
                }
            }
        </style>

    @endpush


    @push('scripts')

        <script>

            document.addEventListener(
                'DOMContentLoaded',
                function () {

                    const type =
                        document.getElementById(
                            'resolutionType'
                        );


                    const partialField =
                        document.getElementById(
                            'partialRefundField'
                        );


                    const amount =
                        document.getElementById(
                            'refundAmount'
                        );


                    const preview =
                        document.getElementById(
                            'resolutionPreview'
                        );


                    if (
                        !type
                        ||
                        !preview
                    ) {

                        return;
                    }


                    const paid =
                        Number(
                            preview.dataset.paid
                            ||
                            0
                        );


                    const feeRate =
                        Number(
                            preview.dataset.serviceFeeRate
                            ||
                            0
                        );


                    const vatRate =
                        Number(
                            preview.dataset.vatRate
                            ||
                            0
                        );


                    const existingSellerNet =
                        Number(
                            preview.dataset.existingSellerNet
                            ||
                            0
                        );


                    function money(
                        value
                    ) {

                        return new Intl
                            .NumberFormat(
                                'en-NG',
                                {
                                    style:
                                        'currency',

                                    currency:
                                        'NGN',

                                    maximumFractionDigits:
                                        2,
                                }
                            )
                            .format(
                                Number(
                                    value
                                    ||
                                    0
                                )
                            );
                    }


                    function roundMoney(
                        value
                    ) {

                        return Math.round(
                            (
                                Number(
                                    value
                                    ||
                                    0
                                )
                                +
                                Number.EPSILON
                            )
                            *
                            100
                        )
                            /
                            100;
                    }


                    function render() {
                        const selected =
                            type.value;


                        partialField.hidden =
                            selected
                            !==
                            'partial_refund';


                        amount.required =
                            selected
                            ===
                            'partial_refund';


                        if (
                            !selected
                        ) {

                            preview.hidden =
                                true;

                            return;
                        }


                        let html =
                            '';


                        if (
                            selected
                            ===
                            'full_refund'
                        ) {

                            html =
                                '<strong>Buyer refund:</strong> '
                                +
                                money(
                                    paid
                                )
                                +
                                '<br>'
                                +
                                '<strong>Midpoint fee on refunded amount:</strong> '
                                +
                                money(
                                    0
                                )
                                +
                                '<br>'
                                +
                                '<strong>Seller settlement:</strong> '
                                +
                                money(
                                    0
                                );

                        } else if (
                            selected
                            ===
                            'partial_refund'
                        ) {

                            const refund =
                                Math.max(
                                    0,
                                    Number(
                                        amount.value
                                        ||
                                        0
                                    )
                                );


                            const retained =
                                roundMoney(
                                    Math.max(
                                        0,
                                        paid
                                        -
                                        refund
                                    )
                                );


                            const fee =
                                roundMoney(
                                    retained
                                    *
                                    feeRate
                                    /
                                    100
                                );


                            const vat =
                                roundMoney(
                                    fee
                                    *
                                    vatRate
                                    /
                                    100
                                );


                            const seller =
                                roundMoney(
                                    Math.max(
                                        0,
                                        retained
                                        -
                                        fee
                                        -
                                        vat
                                    )
                                );


                            html =
                                '<strong>Buyer refund:</strong> '
                                +
                                money(
                                    refund
                                )
                                +
                                '<br>'
                                +
                                '<strong>Remaining transaction amount:</strong> '
                                +
                                money(
                                    retained
                                )
                                +
                                '<br>'
                                +
                                '<strong>Midpoint service fee on remaining amount:</strong> '
                                +
                                money(
                                    fee
        )
                                        +
                                        '<br>'
                                        +
                                        '<strong>VAT on service fee:</strong> '
                                        +
                                        money(
                                            vat
                                        )
                                        +
                                        '<br>'
                                        +
                                        '<strong>Seller settlement:</strong> '
                                        +
                                        money(
                                            seller
                                        );

                                } else if (
                                    selected
                                    ===
                                    'release_to_seller'
                                ) {

                                    html =
                                        '<strong>Buyer refund:</strong> '
                                        +
                                        money(
                                            0
                                        )
                                        +
                                        '<br>'
                                        +
                                        '<strong>Seller settlement:</strong> '
                                        +
                                        money(
                                            existingSellerNet
                                        );

                                } else {

                                    html =
                                        '<strong>No immediate refund or wallet credit.</strong>'
                                        +
                                        '<br>'
                                        +
                                        'The transaction returns to its protected delivery/inspection flow.';
                                }


                                preview.innerHTML =
                                    html;


                                preview.hidden =
                                    false;
                            }


                            type.addEventListener(
                                'change',
                                render
                            );


                            amount?.addEventListener(
                                'input',
                                render
                            );


                            render();

                        }
                    );

                </script>

    @endpush


@endsection