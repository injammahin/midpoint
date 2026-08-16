@extends('admin.layouts.app')


@section('title', 'Dispute ' . $transaction->reference)


@section('page-title', 'Dispute Review')



@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Reason
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


    /*
    |--------------------------------------------------------------------------
    | Desired Outcome
    |--------------------------------------------------------------------------
    */

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
                        (string) $dispute->desired_outcome
                    )
                ),
        };


    /*
    |--------------------------------------------------------------------------
    | Status Class
    |--------------------------------------------------------------------------
    */

    $workflowStatusClass =
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
    | Status Icon
    |--------------------------------------------------------------------------
    */

    $workflowStatusIcon =
        match($dispute->status) {

            'open' =>
                'fa-circle-exclamation',

            'under_review' =>
                'fa-magnifying-glass',

            'awaiting_buyer' =>
                'fa-user-clock',

            'awaiting_seller' =>
                'fa-store',

            'resolved' =>
                'fa-circle-check',

            default =>
                'fa-circle',
        };

@endphp



<div class="txn-admin-page">


    {{-- =====================================================
        SUCCESS
    ====================================================== --}}

    @if(session('success'))

        <div class="dispute-success-alert">

            <i class="fa-solid fa-circle-check"></i>


            <div>

                <strong>
                    Success
                </strong>


                <span>
                    {{ session('success') }}
                </span>

            </div>

        </div>

    @endif



    {{-- =====================================================
        ERROR
    ====================================================== --}}

    @if(session('error'))

        <div class="dispute-error-alert">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                <strong>
                    Unable to update dispute
                </strong>


                <span>
                    {{ session('error') }}
                </span>

            </div>

        </div>

    @endif



    {{-- =====================================================
        VALIDATION
    ====================================================== --}}

    @if($errors->any())

        <div class="dispute-error-alert">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                <strong>
                    Please check the form
                </strong>


                @foreach($errors->all() as $error)

                    <span>
                        • {{ $error }}
                    </span>

                @endforeach

            </div>

        </div>

    @endif



    {{-- =====================================================
        HEADING
    ====================================================== --}}

    <div class="txn-admin-heading">

        <div>

            <div
                class="
                    dispute-heading-status
                    txn-badge
                    {{ $workflowStatusClass }}
                "
            >

                <i
                    class="
                        fa-solid
                        {{ $workflowStatusIcon }}
                    "
                ></i>


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

            </div>


            <h2>
                Dispute: {{ $transaction->reference }}
            </h2>


            <p>

                Opened

                {{
                    $dispute->opened_at
                        ? $dispute
                            ->opened_at
                            ->format(
                                'd M Y, h:i A'
                            )
                        : '-'
                }}

                @if($dispute->resolved_at)

                    · Resolved

                    {{
                        $dispute
                            ->resolved_at
                            ->format(
                                'd M Y, h:i A'
                            )
                    }}

                @endif

            </p>

        </div>


        <div
            style="
                display:
                    flex;

                gap:
                    8px;

                flex-wrap:
                    wrap;
            "
        >

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
        PAYOUT WARNING
    ====================================================== --}}

    @if(
        $dispute->status
        !==
        \App\Models\TransactionDispute::STATUS_RESOLVED
    )

        <div class="txn-dispute-alert">

            <strong>

                <i class="fa-solid fa-lock"></i>

                Seller payout is paused

            </strong>


            <p>

                This transaction is currently disputed.

                Midpoint should review the buyer's claim,
                transaction details and evidence before
                any refund, payout or settlement action.

            </p>

        </div>


    @else

        <div class="dispute-resolved-alert">

            <i class="fa-solid fa-circle-check"></i>


            <div>

                <strong>
                    Dispute review completed
                </strong>


                <span>

                    This dispute has been marked as resolved.

                    Financial settlement should still follow
                    the approved refund or seller-payout workflow.

                </span>

            </div>

        </div>

    @endif



    {{-- =====================================================
        WORKFLOW
    ====================================================== --}}

    <div class="admin-card txn-detail-card dispute-workflow-card">

        <div class="dispute-workflow-header">

            <div>

                <h3>
                    Dispute Workflow
                </h3>


                <p>

                    Update the review status.

                    Midpoint will automatically email the
                    appropriate buyer or seller for each stage.

                </p>

            </div>


            <span
                class="
                    txn-badge
                    {{ $workflowStatusClass }}
                "
            >

                <i
                    class="
                        fa-solid
                        {{ $workflowStatusIcon }}
                    "
                ></i>


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

        </div>



        {{-- =================================================
            OPEN
        ================================================== --}}

        @if(
            $dispute->status
            ===
            \App\Models\TransactionDispute::STATUS_OPEN
        )

            <div class="dispute-workflow-info warning">

                <i class="fa-solid fa-circle-exclamation"></i>


                <div>

                    <strong>
                        New dispute requires initial review
                    </strong>


                    <span>

                        This dispute is currently counted as a
                        new dispute in the admin sidebar.

                        Mark it Under Review to acknowledge the
                        case and remove the red new-dispute badge.

                        The buyer will receive an email.

                    </span>

                </div>

            </div>



            <form
                method="POST"
                action="{{
                    route(
                        'admin.disputes.status.update',
                        $dispute
                    )
                }}"
            >

                @csrf

                @method('PATCH')


                <input
                    type="hidden"
                    name="status"
                    value="under_review"
                >


                <div class="dispute-form-field">

                    <label for="review-note">

                        Initial message

                        <small>
                            Optional
                        </small>

                    </label>


                    <textarea
                        id="review-note"
                        name="note"
                        rows="4"
                        placeholder="Optional message to the buyer about the review..."
                    >{{ old('note') }}</textarea>


                    <span class="dispute-field-help">

                        The buyer will receive an
                        "Under Review" email after this action.

                    </span>

                </div>


                <button
                    type="submit"
                    class="dispute-primary-button"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Mark Under Review

                </button>

            </form>



        {{-- =================================================
            RESOLVED
        ================================================== --}}

        @elseif(
            $dispute->status
            ===
            \App\Models\TransactionDispute::STATUS_RESOLVED
        )

            <div class="dispute-workflow-info success">

                <i class="fa-solid fa-circle-check"></i>


                <div>

                    <strong>
                        Dispute review resolved
                    </strong>


                    <span>

                        Both buyer and seller have been
                        notified that Midpoint completed
                        its dispute review.

                    </span>

                </div>

            </div>


            @if($dispute->admin_note)

                <div class="dispute-resolution-note">

                    <span>
                        Resolution note
                    </span>


                    <strong>
                        {{ $dispute->admin_note }}
                    </strong>

                </div>

            @endif



        {{-- =================================================
            ACTIVE WORKFLOW
        ================================================== --}}

        @else

            <form
                method="POST"
                action="{{
                    route(
                        'admin.disputes.status.update',
                        $dispute
                    )
                }}"
            >

                @csrf

                @method('PATCH')


                <div class="dispute-workflow-form">


                    {{-- =========================================
                        STATUS
                    ========================================== --}}

                    <div class="dispute-form-field">

                        <label for="dispute-status">
                            Next Status
                        </label>


                        <select
                            id="dispute-status"
                            name="status"
                            required
                        >

                            <option value="">
                                Select next action
                            </option>


                            {{-- BACK TO REVIEW --}}
                            @if(
                                in_array(
                                    $dispute->status,
                                    [
                                        \App\Models\TransactionDispute::STATUS_AWAITING_BUYER,
                                        \App\Models\TransactionDispute::STATUS_AWAITING_SELLER,
                                    ],
                                    true
                                )
                            )

                                <option
                                    value="under_review"
                                    {{
                                        old('status')
                                        ===
                                        'under_review'
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    Back to Under Review

                                </option>

                            @endif



                            {{-- AWAITING BUYER --}}
                            @if(
                                $dispute->status
                                !==
                                \App\Models\TransactionDispute::STATUS_AWAITING_BUYER
                            )

                                <option
                                    value="awaiting_buyer"
                                    {{
                                        old('status')
                                        ===
                                        'awaiting_buyer'
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    Awaiting Buyer

                                </option>

                            @endif



                            {{-- AWAITING SELLER --}}
                            @if(
                                $dispute->status
                                !==
                                \App\Models\TransactionDispute::STATUS_AWAITING_SELLER
                            )

                                <option
                                    value="awaiting_seller"
                                    {{
                                        old('status')
                                        ===
                                        'awaiting_seller'
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    Awaiting Seller

                                </option>

                            @endif



                            {{-- RESOLVED --}}
                            <option
                                value="resolved"
                                {{
                                    old('status')
                                    ===
                                    'resolved'
                                        ? 'selected'
                                        : ''
                                }}
                            >

                                Resolve Dispute Review

                            </option>

                        </select>


                        <span class="dispute-field-help">

                            Awaiting Buyer emails the buyer.

                            Awaiting Seller emails the seller.

                            Resolved emails both parties.

                        </span>

                    </div>



                    {{-- =========================================
                        NOTE
                    ========================================== --}}

                    <div class="dispute-form-field">

                        <label for="dispute-note">

                            Message / Admin Note

                        </label>


                        <textarea
                            id="dispute-note"
                            name="note"
                            rows="5"
                            placeholder="Explain what information is required or how the dispute was resolved..."
                        >{{ old('note') }}</textarea>


                        <span class="dispute-field-help">

                            Required when waiting for the buyer,
                            waiting for the seller, or resolving the case.

                            This message can be included in the email.

                        </span>

                    </div>

                </div>



                {{-- =============================================
                    EMAIL PREVIEW INFO
                ============================================== --}}

                <div
                    id="dispute-email-preview"
                    class="dispute-email-preview"
                >

                    <i class="fa-solid fa-envelope"></i>


                    <div>

                        <strong id="dispute-email-preview-title">

                            Select a status

                        </strong>


                        <span id="dispute-email-preview-text">

                            The notification recipient will
                            appear here.

                        </span>

                    </div>

                </div>



                <button
                    type="submit"
                    class="dispute-primary-button"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Update Dispute Status

                </button>

            </form>

        @endif

    </div>



    {{-- =====================================================
        MAIN DETAILS
    ====================================================== --}}

    <div class="txn-detail-grid">


        {{-- =================================================
            LEFT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- =============================================
                DISPUTE REQUEST
            ============================================== --}}

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
                        Current workflow
                    </span>


                    <span
                        class="
                            txn-badge
                            {{ $workflowStatusClass }}
                        "
                    >

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

                </div>



                {{-- EXPLANATION --}}
                <div class="dispute-explanation-box">

                    <span>
                        Buyer's explanation
                    </span>


                    <div>
                        {{ $dispute->description }}
                    </div>

                </div>

            </div>



            {{-- =============================================
                EVIDENCE
            ============================================== --}}

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
                                        rel="noopener"
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
                                        rel="noopener"
                                        class="dispute-evidence-file"
                                    >

                                        <i class="fa-solid fa-video"></i>

                                        Open Video Evidence

                                    </a>


                                @else

                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="dispute-evidence-file"
                                    >

                                        <i class="fa-solid fa-file"></i>

                                        Open Evidence File

                                    </a>

                                @endif

                            </div>

                        @endforeach

                    </div>


                @else

                    <div class="txn-empty">

                        <i class="fa-regular fa-file"></i>


                        <strong>
                            No evidence uploaded
                        </strong>


                        <span>

                            The buyer did not attach
                            evidence to this dispute.

                        </span>

                    </div>

                @endif

            </div>



            {{-- =============================================
                RETURN INFORMATION
            ============================================== --}}

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
                                rel="noopener"
                                class="txn-action-btn"
                            >

                                <i class="fa-solid fa-paperclip"></i>

                                Open Proof

                            </a>

                        </div>

                    @endif

                </div>

            @endif



            {{-- =============================================
                STATUS HISTORY
            ============================================== --}}

            <div class="admin-card txn-detail-card">

                <h3>
                    Dispute Status History
                </h3>


                @if(
                    isset($dispute->statusHistories)
                    &&
                    $dispute
                        ->statusHistories
                        ->isNotEmpty()
                )

                    <div class="dispute-history-list">

                        @foreach($dispute->statusHistories as $history)

                            @php

                                $historyClass =
                                    match($history->to_status) {

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

                            @endphp


                            <div class="dispute-history-item">

                                <div
                                    class="
                                        dispute-history-icon
                                        {{ $historyClass }}
                                    "
                                >

                                    <i class="fa-solid fa-clock-rotate-left"></i>

                                </div>


                                <div class="dispute-history-content">

                                    <div class="dispute-history-title">

                                        @if($history->from_status)

                                            {{
                                                ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $history->from_status
                                                    )
                                                )
                                            }}

                                        @else

                                            Created

                                        @endif


                                        <i class="fa-solid fa-arrow-right"></i>


                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $history->to_status
                                                )
                                            )
                                        }}

                                    </div>


                                    <div class="dispute-history-meta">

                                        By

                                        <strong>

                                            {{
                                                $history
                                                    ->admin
                                                    ?->name
                                                ??
                                                'Administrator'
                                            }}

                                        </strong>


                                        <span>·</span>


                                        {{
                                            $history
                                                ->created_at
                                                ->format(
                                                    'd M Y, h:i A'
                                                )
                                        }}

                                    </div>


                                    @if($history->note)

                                        <div class="dispute-history-note">

                                            {{ $history->note }}

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>


                @else

                    <div class="dispute-history-empty">

                        <i class="fa-solid fa-clock-rotate-left"></i>


                        <span>

                            No administrator status changes
                            have been recorded yet.

                        </span>

                    </div>

                @endif

            </div>

        </div>



        {{-- =================================================
            RIGHT
        ================================================== --}}

        <div class="txn-detail-stack">


            {{-- =============================================
                TRANSACTION
            ============================================== --}}

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
                            color:
                                #087443;

                            font-size:
                                15px;
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

                        <i class="fa-solid fa-lock"></i>

                        PAID

                    </span>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Transaction status
                    </span>


                    <span class="txn-badge red">

                        {{ $transaction->status_label }}

                    </span>

                </div>

            </div>



            {{-- =============================================
                BUYER
            ============================================== --}}

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
                            $dispute
                                ->buyer
                                ?->name
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
                            $dispute
                                ->buyer
                                ?->email
                            ??
                            $transaction->buyer_email
                            ??
                            '-'
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



            {{-- =============================================
                SELLER
            ============================================== --}}

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
                            $dispute
                                ->seller
                                ?->name
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
                            $dispute
                                ->seller
                                ?->email
                            ??
                            '-'
                        }}

                    </strong>

                </div>

            </div>



            {{-- =============================================
                PAYMENT SECURITY
            ============================================== --}}

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
                            $transaction
                                ->successfulPayment
                                ?->reference
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
                                ? $transaction
                                    ->paid_at
                                    ->format(
                                        'd M Y, h:i A'
                                    )
                                : '-'
                        }}

                    </strong>

                </div>


                <div class="txn-detail-row">

                    <span>
                        Payment channel
                    </span>


                    <strong>

                        {{
                            $transaction
                                ->successfulPayment
                                ?->channel

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
                        Seller payout
                    </span>


                    @if(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_RESOLVED
                    )

                        <span class="txn-badge yellow">

                            REVIEW COMPLETED

                        </span>


                    @else

                        <span class="txn-badge red">

                            <i class="fa-solid fa-lock"></i>

                            PAUSED

                        </span>

                    @endif

                </div>

            </div>



            {{-- =============================================
                LATEST ADMIN NOTE
            ============================================== --}}

            @if($dispute->admin_note)

                <div class="admin-card txn-detail-card">

                    <h3>
                        Latest Admin Note
                    </h3>


                    <div class="dispute-admin-note">

                        {{ $dispute->admin_note }}

                    </div>

                </div>

            @endif

        </div>

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
        | Heading
        |--------------------------------------------------------------------------
        */

        .dispute-heading-status {
            margin-bottom:
                9px;
        }


        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        .dispute-success-alert,

        .dispute-error-alert,

        .dispute-resolved-alert {
            display:
                flex;

            align-items:
                flex-start;

            gap:
                12px;

            padding:
                15px
                17px;

            border-radius:
                12px;

            font-size:
                11px;
        }


        .dispute-success-alert {
            border:
                1px
                solid
                #ABEFC6;

            background:
                #ECFDF3;

            color:
                #067647;
        }


        .dispute-error-alert {
            border:
                1px
                solid
                #FECDD3;

            background:
                #FFF1F2;

            color:
                #B42318;
        }


        .dispute-resolved-alert {
            border:
                1px
                solid
                #ABEFC6;

            background:
                #F3FFF8;

            color:
                #067647;
        }


        .dispute-success-alert > i,

        .dispute-error-alert > i,

        .dispute-resolved-alert > i {
            margin-top:
                2px;

            font-size:
                17px;
        }


        .dispute-success-alert strong,

        .dispute-error-alert strong,

        .dispute-resolved-alert strong {
            display:
                block;

            margin-bottom:
                3px;

            font-size:
                11px;
        }


        .dispute-success-alert span,

        .dispute-error-alert span,

        .dispute-resolved-alert span {
            display:
                block;

            line-height:
                1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | Workflow Card
        |--------------------------------------------------------------------------
        */

        .dispute-workflow-card {
            border-color:
                #CFE7DD;
        }


        .dispute-workflow-header {
            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                20px;

            margin-bottom:
                18px;
        }


        .dispute-workflow-header
        h3 {
            margin:
                0
                0
                5px;
        }


        .dispute-workflow-header
        p {
            max-width:
                620px;

            margin:
                0;

            color:
                var(--admin-muted);

            font-size:
                10px;

            line-height:
                1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | Workflow Info
        |--------------------------------------------------------------------------
        */

        .dispute-workflow-info {
            display:
                flex;

            align-items:
                flex-start;

            gap:
                11px;

            margin-bottom:
                18px;

            padding:
                14px;

            border-radius:
                11px;

            font-size:
                10px;

            line-height:
                1.6;
        }


        .dispute-workflow-info.warning {
            border:
                1px
                solid
                #FEDF89;

            background:
                #FFFDF5;

            color:
                #8A5A00;
        }


        .dispute-workflow-info.success {
            border:
                1px
                solid
                #ABEFC6;

            background:
                #ECFDF3;

            color:
                #067647;
        }


        .dispute-workflow-info
        > i {
            margin-top:
                2px;

            font-size:
                15px;
        }


        .dispute-workflow-info
        strong {
            display:
                block;

            margin-bottom:
                3px;

            font-size:
                11px;
        }


        .dispute-workflow-info
        span {
            display:
                block;
        }


        /*
        |--------------------------------------------------------------------------
        | Workflow Form
        |--------------------------------------------------------------------------
        */

        .dispute-workflow-form {
            display:
                grid;

            grid-template-columns:
                minmax(
                    220px,
                    .7fr
                )
                minmax(
                    0,
                    1.5fr
                );

            gap:
                15px;
        }


        .dispute-form-field {
            display:
                flex;

            flex-direction:
                column;

            gap:
                7px;

            margin-bottom:
                15px;
        }


        .dispute-form-field
        label {
            color:
                var(--admin-heading);

            font-size:
                10px;

            font-weight:
                700;
        }


        .dispute-form-field
        label
        small {
            margin-left:
                4px;

            color:
                var(--admin-muted);

            font-size:
                9px;

            font-weight:
                400;
        }


        .dispute-form-field
        select,

        .dispute-form-field
        textarea {
            width:
                100%;

            border:
                1px
                solid
                var(--admin-border);

            border-radius:
                10px;

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


        .dispute-form-field
        select {
            min-height:
                42px;

            padding:
                0
                12px;
        }


        .dispute-form-field
        textarea {
            min-height:
                100px;

            padding:
                11px
                12px;

            resize:
                vertical;

            line-height:
                1.6;
        }


        .dispute-form-field
        select:focus,

        .dispute-form-field
        textarea:focus {
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


        .dispute-field-help {
            color:
                var(--admin-muted);

            font-size:
                9px;

            line-height:
                1.55;
        }


        /*
        |--------------------------------------------------------------------------
        | Main Button
        |--------------------------------------------------------------------------
        */

        .dispute-primary-button {
            display:
                inline-flex;

            min-height:
                42px;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            padding:
                0
                17px;

            border:
                0;

            border-radius:
                10px;

            background:
                #0B8065;

            color:
                #FFFFFF;

            cursor:
                pointer;

            font:
                inherit;

            font-size:
                11px;

            font-weight:
                700;
        }


        .dispute-primary-button:hover {
            background:
                #096D57;
        }


        /*
        |--------------------------------------------------------------------------
        | Email Preview
        |--------------------------------------------------------------------------
        */

        .dispute-email-preview {
            display:
                flex;

            align-items:
                flex-start;

            gap:
                10px;

            margin-bottom:
                16px;

            padding:
                12px
                14px;

            border:
                1px
                solid
                var(--admin-border);

            border-radius:
                10px;

            background:
                var(--admin-subtle);

            color:
                var(--admin-muted);

            font-size:
                9px;

            line-height:
                1.55;
        }


        .dispute-email-preview
        > i {
            margin-top:
                2px;

            color:
                #0EA584;

            font-size:
                14px;
        }


        .dispute-email-preview
        strong {
            display:
                block;

            margin-bottom:
                2px;

            color:
                var(--admin-heading);

            font-size:
                10px;
        }


        /*
        |--------------------------------------------------------------------------
        | Explanation
        |--------------------------------------------------------------------------
        */

        .dispute-explanation-box {
            margin-top:
                18px;

            padding:
                16px;

            border:
                1px
                solid
                var(--admin-border);

            border-radius:
                12px;

            background:
                var(--admin-subtle);
        }


        .dispute-explanation-box
        > span {
            display:
                block;

            margin-bottom:
                8px;

            color:
                var(--admin-heading);

            font-size:
                10px;

            font-weight:
                700;
        }


        .dispute-explanation-box
        > div {
            color:
                var(--admin-text);

            font-size:
                11px;

            line-height:
                1.75;

            white-space:
                pre-line;
        }


        /*
        |--------------------------------------------------------------------------
        | Resolution Note
        |--------------------------------------------------------------------------
        */

        .dispute-resolution-note {
            padding:
                14px;

            border:
                1px
                solid
                #CDEDD9;

            border-radius:
                11px;

            background:
                #F7FFFA;
        }


        .dispute-resolution-note
        span {
            display:
                block;

            margin-bottom:
                6px;

            color:
                #60756B;

            font-size:
                9px;

            font-weight:
                700;

            text-transform:
                uppercase;

            letter-spacing:
                .05em;
        }


        .dispute-resolution-note
        strong {
            display:
                block;

            color:
                #315D47;

            font-size:
                10px;

            font-weight:
                500;

            line-height:
                1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | History
        |--------------------------------------------------------------------------
        */

        .dispute-history-list {
            display:
                flex;

            flex-direction:
                column;
        }


        .dispute-history-item {
            display:
                grid;

            grid-template-columns:
                40px
                minmax(
                    0,
                    1fr
                );

            gap:
                12px;

            padding:
                14px
                0;

            border-bottom:
                1px
                solid
                var(--admin-border);
        }


        .dispute-history-item:last-child {
            border-bottom:
                0;
        }


        .dispute-history-icon {
            display:
                grid;

            width:
                36px;

            height:
                36px;

            place-items:
                center;

            border-radius:
                50%;

            background:
                var(--admin-subtle);

            color:
                var(--admin-muted);
        }


        .dispute-history-icon.blue {
            background:
                #EEF4FF;

            color:
                #3538CD;
        }


        .dispute-history-icon.yellow {
            background:
                #FFF7E8;

            color:
                #B54708;
        }


        .dispute-history-icon.purple {
            background:
                #F2F0FF;

            color:
                #6941C6;
        }


        .dispute-history-icon.green {
            background:
                #ECFDF3;

            color:
                #067647;
        }


        .dispute-history-title {
            display:
                flex;

            align-items:
                center;

            flex-wrap:
                wrap;

            gap:
                6px;

            color:
                var(--admin-heading);

            font-size:
                10px;

            font-weight:
                700;
        }


        .dispute-history-title
        i {
            color:
                var(--admin-muted);

            font-size:
                8px;
        }


        .dispute-history-meta {
            display:
                flex;

            align-items:
                center;

            flex-wrap:
                wrap;

            gap:
                4px;

            margin-top:
                5px;

            color:
                var(--admin-muted);

            font-size:
                9px;
        }


        .dispute-history-note {
            margin-top:
                9px;

            padding:
                9px
                11px;

            border-radius:
                9px;

            background:
                var(--admin-subtle);

            color:
                var(--admin-text);

            font-size:
                9px;

            line-height:
                1.6;
        }


        .dispute-history-empty {
            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            padding:
                20px;

            border-radius:
                10px;

            background:
                var(--admin-subtle);

            color:
                var(--admin-muted);

            font-size:
                10px;

            text-align:
                center;
        }


        /*
        |--------------------------------------------------------------------------
        | Latest Admin Note
        |--------------------------------------------------------------------------
        */

        .dispute-admin-note {
            color:
                var(--admin-text);

            font-size:
                11px;

            line-height:
                1.7;

            white-space:
                pre-line;
        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media(max-width: 850px) {

            .dispute-workflow-form {
                grid-template-columns:
                    1fr;
            }


            .dispute-workflow-header {
                flex-direction:
                    column;
            }

        }

    </style>

@endpush



{{-- =========================================================
    SCRIPT
========================================================== --}}

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const statusSelect =
            document.getElementById(
                'dispute-status'
            );


        const preview =
            document.getElementById(
                'dispute-email-preview'
            );


        const previewTitle =
            document.getElementById(
                'dispute-email-preview-title'
            );


        const previewText =
            document.getElementById(
                'dispute-email-preview-text'
            );


        /*
        |--------------------------------------------------------------------------
        | No Active Status Form
        |--------------------------------------------------------------------------
        */

        if (
            !statusSelect
            ||
            !preview
            ||
            !previewTitle
            ||
            !previewText
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Update Preview
        |--------------------------------------------------------------------------
        */

        function updatePreview() {

            const status =
                statusSelect.value;


            /*
            |--------------------------------------------------------------------------
            | Under Review
            |--------------------------------------------------------------------------
            */

            if (
                status ===
                'under_review'
            ) {

                previewTitle.textContent =
                    'Buyer will be notified';


                previewText.textContent =
                    'The buyer will receive an email that Midpoint has resumed reviewing the dispute.';


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Awaiting Buyer
            |--------------------------------------------------------------------------
            */

            if (
                status ===
                'awaiting_buyer'
            ) {

                previewTitle.textContent =
                    'Buyer will receive an action-required email';


                previewText.textContent =
                    'Your admin message will be sent to the buyer explaining what additional information or action is required.';


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Awaiting Seller
            |--------------------------------------------------------------------------
            */

            if (
                status ===
                'awaiting_seller'
            ) {

                previewTitle.textContent =
                    'Seller will receive an action-required email';


                previewText.textContent =
                    'Your admin message will be sent to the seller explaining what additional information or action is required.';


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Resolved
            |--------------------------------------------------------------------------
            */

            if (
                status ===
                'resolved'
            ) {

                previewTitle.textContent =
                    'Buyer and seller will both be notified';


                previewText.textContent =
                    'Both parties will receive the dispute resolution email including the admin resolution note.';


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Empty
            |--------------------------------------------------------------------------
            */

            previewTitle.textContent =
                'Select a status';


            previewText.textContent =
                'The notification recipient will appear here.';

        }


        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        statusSelect.addEventListener(
            'change',
            updatePreview
        );


        /*
        |--------------------------------------------------------------------------
        | Initial
        |--------------------------------------------------------------------------
        */

        updatePreview();

    }
);

</script>

@endpush