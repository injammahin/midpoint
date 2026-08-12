@extends('admin.layouts.app')

@section('title', 'Seller Application')

@section('page-title', 'Seller Application')


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Application Details
    |--------------------------------------------------------------------------
    |
    | Define the array separately so Blade does not need to parse a large
    | multiline array directly inside @foreach.
    |
    */

    $applicationDetails = [

        'Applicant' =>
            $application->user
                ? $application->user->name
                : 'N/A',

        'Email' =>
            $application->user
                ? $application->user->email
                : 'N/A',

        'Phone / WhatsApp' =>
            $application->phone
            ?: 'N/A',

        'Category' =>
            $application->category
            ?: 'N/A',

        'Location' =>
            $application->location
            ?: 'N/A',

        'CAC / BVN' =>
            $application->cac_or_bvn
            ?: 'N/A',

        'Existing Store' =>
            $application->store_link
            ?: 'Not provided',

        'Package' =>
            $application->package_name
            ?: 'N/A',

        'Package Price' =>
            '₦'
            .
            number_format(
                (float) $application->package_price,
                0
            )
            .
            '/'
            .
            $application->billing_period,

        'Product Limit' =>
            number_format(
                (int) $application->product_limit
            )
            .
            ' products',

    ];


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $statusLabel =
        match($application->status) {

            'submitted' =>
                'Under Review',

            'revision_required' =>
                'Revision Required',

            'payment_pending' =>
                'Payment Required',

            'active' =>
                'Active Seller',

            'superseded' =>
                'Superseded',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $application->status
                    )
                ),
        };


    $statusClass =
        match($application->status) {

            'submitted' =>
                'seller-status-review',

            'revision_required' =>
                'seller-status-revision',

            'payment_pending' =>
                'seller-status-payment',

            'active' =>
                'seller-status-active',

            default =>
                'seller-status-muted',
        };

@endphp



{{-- =========================================================
    FLASH MESSAGES
========================================================== --}}

@if(session('success'))

    <div class="seller-alert seller-alert-success">

        <i class="fa-solid fa-circle-check"></i>

        <span>
            {{ session('success') }}
        </span>

    </div>

@endif


@if(session('error'))

    <div class="seller-alert seller-alert-error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <span>
            {{ session('error') }}
        </span>

    </div>

@endif


@if($errors->any())

    <div class="seller-alert seller-alert-error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <div>

            @foreach($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    </div>

@endif



{{-- =========================================================
    PAGE HEADER
========================================================== --}}

<div class="seller-review-header">

    <div>

        <a
            href="{{ route('admin.website-settings.seller-applications.index') }}"
            class="seller-back-link"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to applications

        </a>


        <h2>
            {{ $application->business_name }}
        </h2>


        <p>
            Seller application
            <strong>{{ $application->reference }}</strong>
        </p>

    </div>


    <span class="seller-status {{ $statusClass }}">

        <span class="seller-status-dot"></span>

        {{ $statusLabel }}

    </span>

</div>



{{-- =========================================================
    MAIN GRID
========================================================== --}}

<div class="seller-review-grid">


    {{-- =====================================================
        LEFT SIDE
    ====================================================== --}}

    <div>


        {{-- =================================================
            APPLICATION INFORMATION
        ================================================== --}}

        <div class="admin-card seller-review-card">

            <div class="seller-card-heading">

                <div class="seller-card-heading-icon">

                    <i class="fa-solid fa-store"></i>

                </div>


                <div>

                    <h3>
                        Application Information
                    </h3>

                    <p>
                        Business and package details submitted by the applicant.
                    </p>

                </div>

            </div>



            <div class="seller-details-list">

                @foreach($applicationDetails as $label => $value)

                    <div class="seller-detail-row">

                        <div class="seller-detail-label">

                            {{ $label }}

                        </div>


                        <div class="seller-detail-value">

                            @if($label === 'Existing Store' && $application->store_link)

                                <a
                                    href="{{ $application->store_link }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    {{ $application->store_link }}

                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                                </a>

                            @else

                                {{ $value }}

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        </div>



        {{-- =================================================
            BUSINESS DESCRIPTION
        ================================================== --}}

        <div class="admin-card seller-review-card seller-review-spacing">

            <div class="seller-card-heading">

                <div class="seller-card-heading-icon">

                    <i class="fa-solid fa-align-left"></i>

                </div>


                <div>

                    <h3>
                        Business Description
                    </h3>

                    <p>
                        Information provided by the applicant about their business.
                    </p>

                </div>

            </div>


            <div class="seller-description">

                {{ $application->description }}

            </div>

        </div>



        {{-- =================================================
            DOCUMENTS
        ================================================== --}}

        <div class="admin-card seller-review-card seller-review-spacing">

            <div class="seller-card-heading">

                <div class="seller-card-heading-icon">

                    <i class="fa-solid fa-file-shield"></i>

                </div>


                <div>

                    <h3>
                        Verification Documents
                    </h3>

                    <p>
                        Review the documents uploaded with this application.
                    </p>

                </div>

            </div>



            @if($application->documents->count() > 0)

                <div class="seller-documents">

                    @foreach($application->documents as $document)

                        <a
                            href="{{ route('admin.website-settings.seller-applications.documents', $document) }}"
                            class="seller-document"
                        >

                            <div class="seller-document-icon">

                                @if($document->mime_type === 'application/pdf')

                                    <i class="fa-solid fa-file-pdf"></i>

                                @else

                                    <i class="fa-solid fa-file-image"></i>

                                @endif

                            </div>


                            <div class="seller-document-info">

                                <strong>
                                    {{ $document->original_name }}
                                </strong>


                                <span>

                                    @if($document->size)

                                        {{
                                            number_format(
                                                $document->size / 1024,
                                                1
                                            )
                                        }}
                                        KB

                                    @else

                                        Uploaded document

                                    @endif

                                </span>

                            </div>


                            <div class="seller-document-action">

                                <i class="fa-solid fa-download"></i>

                            </div>

                        </a>

                    @endforeach

                </div>

            @else

                <div class="seller-empty-documents">

                    <i class="fa-regular fa-folder-open"></i>

                    <strong>
                        No documents uploaded
                    </strong>

                </div>

            @endif

        </div>

    </div>



    {{-- =====================================================
        RIGHT SIDE
    ====================================================== --}}

    <div>


        {{-- =================================================
            APPLICANT
        ================================================== --}}

        <div class="admin-card seller-review-card">

            <div class="seller-applicant">

                <div class="seller-applicant-avatar">

                    {{
                        strtoupper(
                            substr(
                                $application->user
                                    ? $application->user->name
                                    : 'U',
                                0,
                                1
                            )
                        )
                    }}

                </div>


                <div>

                    <strong>

                        {{
                            $application->user
                                ? $application->user->name
                                : 'Unknown User'
                        }}

                    </strong>


                    <span>

                        {{
                            $application->user
                                ? $application->user->email
                                : 'No email'
                        }}

                    </span>

                </div>

            </div>



            <div class="seller-meta-list">

                <div>

                    <span>
                        Submitted
                    </span>


                    <strong>

                        @if($application->submitted_at)

                            {{
                                $application
                                    ->submitted_at
                                    ->format('d M Y, h:i A')
                            }}

                        @else

                            N/A

                        @endif

                    </strong>

                </div>


                <div>

                    <span>
                        Application ID
                    </span>


                    <strong>
                        #{{ $application->id }}
                    </strong>

                </div>


                <div>

                    <span>
                        Reference
                    </span>


                    <strong>
                        {{ $application->reference }}
                    </strong>

                </div>

            </div>

        </div>



        {{-- =================================================
            REVIEW ACTIONS
        ================================================== --}}

        <div class="admin-card seller-review-card seller-review-spacing">

            <div class="seller-card-heading">

                <div class="seller-card-heading-icon">

                    <i class="fa-solid fa-user-check"></i>

                </div>


                <div>

                    <h3>
                        Review Decision
                    </h3>

                    <p>
                        Approve the application or request corrections.
                    </p>

                </div>

            </div>



            @if($application->status === 'submitted')


                {{-- =========================================
                    APPROVE
                ========================================== --}}

                <form
                    method="POST"
                    action="{{ route('admin.website-settings.seller-applications.approve', $application) }}"
                    onsubmit="return confirm('Approve this seller application and generate the payment invoice?');"
                >

                    @csrf


                    <button
                        type="submit"
                        class="seller-approve-button"
                    >

                        <i class="fa-solid fa-circle-check"></i>

                        Approve & Generate Invoice

                    </button>

                </form>



                <div class="seller-review-divider">

                    <span>
                        OR
                    </span>

                </div>



                {{-- =========================================
                    REVISION
                ========================================== --}}

                <form
                    method="POST"
                    action="{{ route('admin.website-settings.seller-applications.revision', $application) }}"
                >

                    @csrf


                    <div class="seller-review-field">

                        <label for="revision_note">

                            Revision Note

                            <span>*</span>

                        </label>


                        <textarea
                            id="revision_note"
                            name="revision_note"
                            rows="6"
                            required
                            placeholder="Clearly explain what information or documents the applicant needs to correct..."
                        >{{ old('revision_note') }}</textarea>


                        <small>

                            This message will be emailed to the applicant.

                        </small>

                    </div>


                    <button
                        type="submit"
                        class="seller-revision-button"
                    >

                        <i class="fa-solid fa-rotate-left"></i>

                        Request Revision

                    </button>

                </form>



            @else

                <div class="seller-current-status">

                    <span>
                        Current status
                    </span>


                    <strong>
                        {{ $statusLabel }}
                    </strong>


                    @if($application->reviewed_at)

                        <small>

                            Reviewed
                            {{
                                $application
                                    ->reviewed_at
                                    ->format('d M Y, h:i A')
                            }}

                        </small>

                    @endif

                </div>

            @endif

        </div>



        {{-- =================================================
            REVISION NOTE
        ================================================== --}}

        @if($application->revision_note)

            <div class="admin-card seller-review-card seller-review-spacing">

                <div class="seller-card-heading">

                    <div class="seller-card-heading-icon seller-warning-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>


                    <div>

                        <h3>
                            Revision Note
                        </h3>

                        <p>
                            Correction request sent to the applicant.
                        </p>

                    </div>

                </div>


                <div class="seller-revision-note">

                    {{ $application->revision_note }}

                </div>

            </div>

        @endif



        {{-- =================================================
            INVOICE
        ================================================== --}}

        @if($application->invoice)

            <div class="admin-card seller-review-card seller-review-spacing">

                <div class="seller-card-heading">

                    <div class="seller-card-heading-icon">

                        <i class="fa-solid fa-file-invoice-dollar"></i>

                    </div>


                    <div>

                        <h3>
                            Seller Invoice
                        </h3>

                        <p>
                            Invoice generated after application approval.
                        </p>

                    </div>

                </div>



                <div class="seller-invoice-box">

                    <div class="seller-invoice-reference">

                        <span>
                            Invoice
                        </span>


                        <strong>
                            {{ $application->invoice->invoice_number }}
                        </strong>

                    </div>


                    <div class="seller-invoice-amount">

                        ₦{{
                            number_format(
                                (float) $application->invoice->amount,
                                0
                            )
                        }}

                    </div>


                    <div class="seller-invoice-detail">

                        <span>
                            Status
                        </span>


                        <strong
                            class="{{
                                $application->invoice->status === 'paid'
                                    ? 'seller-invoice-paid'
                                    : 'seller-invoice-unpaid'
                            }}"
                        >

                            {{
                                strtoupper(
                                    $application->invoice->status
                                )
                            }}

                        </strong>

                    </div>


                    <div class="seller-invoice-detail">

                        <span>
                            Issued
                        </span>


                        <strong>

                            @if($application->invoice->issued_at)

                                {{
                                    $application
                                        ->invoice
                                        ->issued_at
                                        ->format('d M Y')
                                }}

                            @else

                                N/A

                            @endif

                        </strong>

                    </div>


                    @if($application->invoice->paid_at)

                        <div class="seller-invoice-detail">

                            <span>
                                Paid
                            </span>


                            <strong>

                                {{
                                    $application
                                        ->invoice
                                        ->paid_at
                                        ->format('d M Y, h:i A')
                                }}

                            </strong>

                        </div>

                    @endif


                    @if($application->invoice->payment_reference)

                        <div class="seller-invoice-detail">

                            <span>
                                Payment Ref.
                            </span>


                            <strong>
                                {{ $application->invoice->payment_reference }}
                            </strong>

                        </div>

                    @endif

                </div>

            </div>

        @endif

    </div>

</div>



{{-- =========================================================
    CSS
========================================================== --}}

@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .seller-alert {
        display:flex;
        align-items:flex-start;
        gap:9px;

        margin-bottom:16px;
        padding:12px 14px;

        border-radius:10px;

        font-size:11px;
        line-height:1.5;
    }


    .seller-alert-success {
        border:1px solid #ABEFC6;
        background:#ECFDF3;
        color:#067647;
    }


    .seller-alert-error {
        border:1px solid #FECDD3;
        background:#FFF1F2;
        color:#B42318;
    }



    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .seller-review-header {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:20px;

        margin-bottom:18px;
    }


    .seller-review-header h2 {
        margin:9px 0 3px;

        color:var(--admin-heading);

        font-size:20px;
        line-height:1.25;
    }


    .seller-review-header p {
        margin:0;

        color:var(--admin-muted);

        font-size:12px;
    }


    .seller-back-link {
        display:inline-flex;
        align-items:center;
        gap:6px;

        color:var(--admin-muted);

        font-size:11px;
        font-weight:600;

        text-decoration:none;
    }


    .seller-back-link:hover {
        color:var(--admin-accent);
    }



    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .seller-status {
        display:inline-flex;
        align-items:center;
        gap:6px;

        padding:7px 10px;

        border-radius:999px;

        font-size:11px;
        font-weight:700;

        white-space:nowrap;
    }


    .seller-status-dot {
        width:6px;
        height:6px;

        border-radius:50%;

        background:currentColor;
    }


    .seller-status-review {
        background:rgba(47,128,237,.11);
        color:#2F80ED;
    }


    .seller-status-revision {
        background:rgba(247,144,9,.12);
        color:#F79009;
    }


    .seller-status-payment {
        background:rgba(123,97,255,.10);
        color:#7B61FF;
    }


    .seller-status-active {
        background:rgba(18,183,106,.11);
        color:#12B76A;
    }


    .seller-status-muted {
        background:var(--admin-surface-soft);
        color:var(--admin-muted);
    }



    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    .seller-review-grid {
        display:grid;

        grid-template-columns:
            minmax(0,1fr)
            350px;

        gap:18px;
    }


    .seller-review-card {
        padding:20px;
    }


    .seller-review-spacing {
        margin-top:18px;
    }



    /*
    |--------------------------------------------------------------------------
    | Card Heading
    |--------------------------------------------------------------------------
    */

    .seller-card-heading {
        display:flex;
        align-items:flex-start;
        gap:11px;

        margin-bottom:18px;
    }


    .seller-card-heading-icon {
        width:36px;
        height:36px;

        flex:0 0 36px;

        display:grid;
        place-items:center;

        border-radius:10px;

        background:var(--admin-accent-soft);
        color:var(--admin-accent);

        font-size:12px;
    }


    .seller-warning-icon {
        background:#FFF7E8;
        color:#F79009;
    }


    .seller-card-heading h3 {
        margin:1px 0 3px;

        color:var(--admin-heading);

        font-size:13px;
    }


    .seller-card-heading p {
        margin:0;

        color:var(--admin-muted);

        font-size:11px;
        line-height:1.5;
    }



    /*
    |--------------------------------------------------------------------------
    | Detail Rows
    |--------------------------------------------------------------------------
    */

    .seller-details-list {
        border-top:1px solid var(--admin-border);
    }


    .seller-detail-row {
        display:grid;

        grid-template-columns:
            165px
            minmax(0,1fr);

        gap:14px;

        padding:11px 0;

        border-bottom:1px solid var(--admin-border);
    }


    .seller-detail-label {
        color:var(--admin-muted);

        font-size:11px;
        font-weight:600;
    }


    .seller-detail-value {
        min-width:0;

        color:var(--admin-heading);

        font-size:12px;
        font-weight:600;

        word-break:break-word;
    }


    .seller-detail-value a {
        color:var(--admin-accent);

        text-decoration:none;
    }



    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    .seller-description {
        padding:15px;

        border:1px solid var(--admin-border);
        border-radius:10px;

        background:var(--admin-surface-soft);

        color:var(--admin-text);

        font-size:12px;
        line-height:1.7;

        white-space:pre-wrap;
    }



    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    .seller-documents {
        display:flex;
        flex-direction:column;
        gap:8px;
    }


    .seller-document {
        display:flex;
        align-items:center;
        gap:10px;

        padding:10px;

        border:1px solid var(--admin-border);
        border-radius:9px;

        background:var(--admin-surface-soft);

        text-decoration:none;

        transition:
            border-color .15s ease,
            background .15s ease;
    }


    .seller-document:hover {
        border-color:var(--admin-accent);
        background:var(--admin-accent-soft);
    }


    .seller-document-icon {
        width:34px;
        height:34px;

        flex:0 0 34px;

        display:grid;
        place-items:center;

        border-radius:8px;

        background:var(--admin-surface);

        color:var(--admin-accent);

        font-size:12px;
    }


    .seller-document-info {
        min-width:0;
        flex:1;
    }


    .seller-document-info strong {
        display:block;

        overflow:hidden;

        color:var(--admin-heading);

        font-size:11px;

        white-space:nowrap;
        text-overflow:ellipsis;
    }


    .seller-document-info span {
        display:block;

        margin-top:2px;

        color:var(--admin-muted);

        font-size:10px;
    }


    .seller-document-action {
        color:var(--admin-muted);

        font-size:12px;
    }


    .seller-empty-documents {
        display:flex;
        flex-direction:column;
        align-items:center;

        gap:8px;

        padding:30px;

        color:var(--admin-muted);

        text-align:center;
    }



    /*
    |--------------------------------------------------------------------------
    | Applicant
    |--------------------------------------------------------------------------
    */

    .seller-applicant {
        display:flex;
        align-items:center;
        gap:10px;

        padding-bottom:16px;

        border-bottom:1px solid var(--admin-border);
    }


    .seller-applicant-avatar {
        width:42px;
        height:42px;

        flex:0 0 42px;

        display:grid;
        place-items:center;

        border-radius:12px;

        background:var(--admin-accent-soft);
        color:var(--admin-accent);

        font-size:13px;
        font-weight:700;
    }


    .seller-applicant strong {
        display:block;

        color:var(--admin-heading);

        font-size:11px;
    }


    .seller-applicant span {
        display:block;

        margin-top:2px;

        color:var(--admin-muted);

        font-size:11px;
    }


    .seller-meta-list {
        display:flex;
        flex-direction:column;

        margin-top:14px;
    }


    .seller-meta-list > div {
        display:flex;
        justify-content:space-between;
        gap:12px;

        padding:8px 0;
    }


    .seller-meta-list span {
        color:var(--admin-muted);

        font-size:11px;
    }


    .seller-meta-list strong {
        color:var(--admin-heading);

        font-size:11px;

        text-align:right;
    }



    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    .seller-approve-button {
        width:100%;

        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;

        padding:11px 14px;

        border:0;
        border-radius:9px;

        background:#12B76A;
        color:#FFFFFF;

        font-family:inherit;
        font-size:12px;
        font-weight:700;

        cursor:pointer;
    }


    .seller-approve-button:hover {
        background:#0FA35F;
    }


    .seller-review-divider {
        display:flex;
        align-items:center;

        margin:17px 0;

        color:var(--admin-muted);

        font-size:10px;
    }


    .seller-review-divider::before,
    .seller-review-divider::after {
        content:"";

        height:1px;
        flex:1;

        background:var(--admin-border);
    }


    .seller-review-divider span {
        padding:0 9px;
    }


    .seller-review-field {
        display:flex;
        flex-direction:column;
        gap:6px;
    }


    .seller-review-field label {
        color:var(--admin-heading);

        font-size:12px;
        font-weight:600;
    }


    .seller-review-field label span {
        color:#D92D20;
    }


    .seller-review-field textarea {
        width:100%;
        min-height:110px;

        padding:10px;

        border:1px solid var(--admin-border);
        border-radius:9px;

        background:var(--admin-surface-soft);
        color:var(--admin-text);

        font-family:inherit;
        font-size:12px;

        resize:vertical;
        outline:none;
    }


    .seller-review-field textarea:focus {
        border-color:var(--admin-accent);
    }


    .seller-review-field small {
        color:var(--admin-muted);

        font-size:10px;
        line-height:1.5;
    }


    .seller-revision-button {
        width:100%;

        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;

        margin-top:11px;
        padding:10px 14px;

        border:1px solid #FEDF89;
        border-radius:9px;

        background:#FFF7E8;
        color:#B54708;

        font-family:inherit;
        font-size:12px;
        font-weight:700;

        cursor:pointer;
    }



    /*
    |--------------------------------------------------------------------------
    | Current Status
    |--------------------------------------------------------------------------
    */

    .seller-current-status {
        padding:15px;

        border:1px solid var(--admin-border);
        border-radius:10px;

        background:var(--admin-surface-soft);
    }


    .seller-current-status span {
        display:block;

        color:var(--admin-muted);

        font-size:11px;
    }


    .seller-current-status strong {
        display:block;

        margin-top:5px;

        color:var(--admin-heading);

        font-size:13px;
    }


    .seller-current-status small {
        display:block;

        margin-top:5px;

        color:var(--admin-muted);

        font-size:10px;
    }



    /*
    |--------------------------------------------------------------------------
    | Revision
    |--------------------------------------------------------------------------
    */

    .seller-revision-note {
        padding:13px;

        border:1px solid #FEDF89;
        border-radius:9px;

        background:#FFF7E8;
        color:#7A5B28;

        font-size:12px;
        line-height:1.6;

        white-space:pre-wrap;
    }



    /*
    |--------------------------------------------------------------------------
    | Invoice
    |--------------------------------------------------------------------------
    */

    .seller-invoice-box {
        padding:14px;

        border:1px solid var(--admin-border);
        border-radius:10px;

        background:var(--admin-surface-soft);
    }


    .seller-invoice-reference {
        display:flex;
        justify-content:space-between;
        gap:10px;

        padding-bottom:10px;

        border-bottom:1px solid var(--admin-border);
    }


    .seller-invoice-reference span,
    .seller-invoice-detail span {
        color:var(--admin-muted);

        font-size:10px;
    }


    .seller-invoice-reference strong,
    .seller-invoice-detail strong {
        color:var(--admin-heading);

        font-size:11px;

        text-align:right;
    }


    .seller-invoice-amount {
        padding:15px 0;

        color:var(--admin-heading);

        font-size:24px;
        font-weight:800;
    }


    .seller-invoice-detail {
        display:flex;
        justify-content:space-between;
        gap:10px;

        padding:6px 0;
    }


    .seller-invoice-paid {
        color:#12B76A !important;
    }


    .seller-invoice-unpaid {
        color:#F79009 !important;
    }



    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width:1050px) {

        .seller-review-grid {
            grid-template-columns:1fr;
        }

    }


    @media(max-width:650px) {

        .seller-review-header {
            flex-direction:column;
        }


        .seller-detail-row {
            grid-template-columns:1fr;
            gap:5px;
        }


        .seller-review-card {
            padding:15px;
        }

    }

</style>

@endpush


@endsection