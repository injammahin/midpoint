@extends('admin.layouts.app')


@section('title', 'Seller Applications')


@section('page-title', 'Seller Applications')


@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}

    <div class="admin-module-header">

        <div>

            <h2>
                Seller Applications
            </h2>

            <p>
                Review seller verification requests,
                package selections and payment status.
            </p>

        </div>

    </div>



    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}

    @if(session('success'))

        <div
            style="
                margin-bottom: 16px;
                padding: 13px 15px;
                border: 1px solid #ABEFC6;
                border-radius: 10px;
                background: #ECFDF3;
                color: #067647;
                font-size: 12px;
                font-weight: 600;
            "
        >

            {{ session('success') }}

        </div>

    @endif



    {{-- =========================================================
        ERROR MESSAGE
    ========================================================== --}}

    @if(session('error'))

        <div
            style="
                margin-bottom: 16px;
                padding: 13px 15px;
                border: 1px solid #FECDD3;
                border-radius: 10px;
                background: #FFF1F2;
                color: #B42318;
                font-size: 12px;
                font-weight: 600;
            "
        >

            {{ session('error') }}

        </div>

    @endif



    {{-- =========================================================
        FILTER
    ========================================================== --}}

    <div
        class="admin-card"
        style="
            margin-bottom: 16px;
            padding: 15px;
        "
    >

        <form
            method="GET"
            action="{{ route('admin.website-settings.seller-applications.index') }}"
            style="
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            "
        >

            <div
                style="
                    display: flex;
                    flex-direction: column;
                    gap: 5px;
                "
            >

                <label
                    for="sellerApplicationStatus"
                    style="
                        color: var(--admin-muted);
                        font-size:12px;
                        font-weight: 600;
                    "
                >
                    Status
                </label>


                <select
                    id="sellerApplicationStatus"
                    name="status"
                    style="
                        min-width: 210px;
                        height: 39px;
                        padding: 0 11px;
                        border: 1px solid var(--admin-border);
                        border-radius: 9px;
                        background: var(--admin-surface-soft);
                        color: var(--admin-text);
                        font-family: inherit;
                        font-size: 11px;
                        outline: none;
                    "
                >

                    <option value="">
                        All applications
                    </option>


                    <option
                        value="submitted"
                        {{ request('status') === 'submitted' ? 'selected' : '' }}
                    >
                        Under Review
                    </option>


                    <option
                        value="revision_required"
                        {{ request('status') === 'revision_required' ? 'selected' : '' }}
                    >
                        Revision Required
                    </option>


                    <option
                        value="payment_pending"
                        {{ request('status') === 'payment_pending' ? 'selected' : '' }}
                    >
                        Payment Required
                    </option>


                    <option
                        value="active"
                        {{ request('status') === 'active' ? 'selected' : '' }}
                    >
                        Active Seller
                    </option>


                    <option
                        value="superseded"
                        {{ request('status') === 'superseded' ? 'selected' : '' }}
                    >
                        Superseded
                    </option>

                </select>

            </div>



            <button
                type="submit"
                style="
                    align-self: flex-end;
                    height: 39px;
                    padding: 0 15px;
                    border: 0;
                    border-radius: 9px;
                    background: var(--admin-accent-strong);
                    color: #052e2b;
                    font-family: inherit;
                    font-size:12px;
                    font-weight: 700;
                    cursor: pointer;
                "
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>



            @if(request()->filled('status'))

                <a
                    href="{{ route('admin.website-settings.seller-applications.index') }}"
                    style="
                        align-self: flex-end;
                        height: 39px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 6px;
                        padding: 0 14px;
                        border: 1px solid var(--admin-border);
                        border-radius: 9px;
                        background: var(--admin-surface-soft);
                        color: var(--admin-heading);
                        font-size:12px;
                        font-weight: 600;
                        text-decoration: none;
                    "
                >

                    <i class="fa-solid fa-xmark"></i>

                    Clear

                </a>

            @endif

        </form>

    </div>



    {{-- =========================================================
        APPLICATION LIST
    ========================================================== --}}

    <div class="admin-card">

        <div
            style="
                overflow-x: auto;
            "
        >

            <table
                style="
                    width: 100%;
                    min-width: 900px;
                    border-collapse: collapse;
                "
            >

                <thead>

                    <tr>

                        <th class="seller-app-th">
                            Reference
                        </th>

                        <th class="seller-app-th">
                            Applicant
                        </th>

                        <th class="seller-app-th">
                            Business
                        </th>

                        <th class="seller-app-th">
                            Package
                        </th>

                        <th class="seller-app-th">
                            Status
                        </th>

                        <th class="seller-app-th">
                            Submitted
                        </th>

                        <th
                            class="seller-app-th"
                            style="text-align: right;"
                        >
                            Action
                        </th>

                    </tr>

                </thead>



                <tbody>

                    {{-- =====================================================
                        APPLICATIONS EXIST
                    ====================================================== --}}

                    @if($applications->count() > 0)

                        @foreach($applications as $application)

                            @php

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
                                            'is-review',

                                        'revision_required' =>
                                            'is-revision',

                                        'payment_pending' =>
                                            'is-payment',

                                        'active' =>
                                            'is-active',

                                        'superseded' =>
                                            'is-muted',

                                        default =>
                                            'is-muted',

                                    };

                            @endphp


                            <tr class="seller-app-row">

                                {{-- Reference --}}
                                <td class="seller-app-td">

                                    <strong
                                        style="
                                            color: var(--admin-heading);
                                            font-size: 11px;
                                        "
                                    >

                                        {{ $application->reference }}

                                    </strong>

                                </td>



                                {{-- Applicant --}}
                                <td class="seller-app-td">

                                    <div class="seller-app-user">

                                        <div class="seller-app-avatar">

                                            {{
                                                strtoupper(
                                                    substr(
                                                        $application->user?->name ?? 'U',
                                                        0,
                                                        1
                                                    )
                                                )
                                            }}

                                        </div>


                                        <div>

                                            <strong>

                                                {{
                                                    $application->user?->name
                                                    ?? 'Unknown User'
                                                }}

                                            </strong>


                                            <span>

                                                {{
                                                    $application->user?->email
                                                    ?? 'No email'
                                                }}

                                            </span>

                                        </div>

                                    </div>

                                </td>



                                {{-- Business --}}
                                <td class="seller-app-td">

                                    <strong
                                        style="
                                            display: block;
                                            color: var(--admin-heading);
                                            font-size: 11px;
                                        "
                                    >

                                        {{ $application->business_name }}

                                    </strong>


                                    <span
                                        style="
                                            display: block;
                                            margin-top: 3px;
                                            color: var(--admin-muted);
                                            font-size:13px;
                                        "
                                    >

                                        {{ $application->category }}

                                    </span>

                                </td>



                                {{-- Package --}}
                                <td class="seller-app-td">

                                    <strong
                                        style="
                                            display: block;
                                            color: var(--admin-heading);
                                            font-size: 11px;
                                        "
                                    >

                                        {{ $application->package_name }}

                                    </strong>


                                    <span
                                        style="
                                            display: block;
                                            margin-top: 3px;
                                            color: var(--admin-muted);
                                            font-size:13px;
                                        "
                                    >

                                        ₦{{
                                            number_format(
                                                (float) $application->package_price,
                                                0
                                            )
                                        }}

                                        /

                                        {{ $application->billing_period }}

                                    </span>


                                    <span
                                        style="
                                            display: block;
                                            margin-top: 3px;
                                            color: var(--admin-muted);
                                            font-size:13px;
                                        "
                                    >

                                        {{
                                            number_format(
                                                $application->product_limit
                                            )
                                        }}
                                        products

                                    </span>

                                </td>



                                {{-- Status --}}
                                <td class="seller-app-td">

                                    <span
                                        class="
                                            seller-application-status
                                            {{ $statusClass }}
                                        "
                                    >

                                        <span></span>

                                        {{ $statusLabel }}

                                    </span>

                                </td>



                                {{-- Submitted --}}
                                <td class="seller-app-td">

                                    @if($application->submitted_at)

                                        <strong
                                            style="
                                                display: block;
                                                color: var(--admin-heading);
                                                font-size:12px;
                                                font-weight: 600;
                                            "
                                        >

                                            {{
                                                $application
                                                    ->submitted_at
                                                    ->format('d M Y')
                                            }}

                                        </strong>


                                        <span
                                            style="
                                                display: block;
                                                margin-top: 3px;
                                                color: var(--admin-muted);
                                                font-size:13px;
                                            "
                                        >

                                            {{
                                                $application
                                                    ->submitted_at
                                                    ->format('h:i A')
                                            }}

                                        </span>

                                    @else

                                        <span
                                            style="
                                                color: var(--admin-muted);
                                                font-size:12px;
                                            "
                                        >
                                            N/A
                                        </span>

                                    @endif

                                </td>



                                {{-- Action --}}
                                <td
                                    class="seller-app-td"
                                    style="text-align: right;"
                                >

                                    <a
                                        href="{{
                                            route(
                                                'admin.website-settings.seller-applications.show',
                                                $application
                                            )
                                        }}"
                                        class="seller-app-review-button"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        Review

                                    </a>

                                </td>

                            </tr>

                        @endforeach



                    {{-- =====================================================
                        EMPTY STATE
                    ====================================================== --}}

                    @else

                        <tr>

                            <td
                                colspan="7"
                                style="
                                    padding: 65px 20px;
                                "
                            >

                                <div class="seller-application-empty">

                                    <div>

                                        <i class="fa-solid fa-file-signature"></i>

                                    </div>


                                    <strong>
                                        No seller applications
                                    </strong>


                                    <p>

                                        Seller applications submitted by
                                        users will appear here for review.

                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>



        {{-- =====================================================
            PAGINATION
        ====================================================== --}}

        @if($applications->hasPages())

            <div
                style="
                    padding: 15px;
                    border-top: 1px solid var(--admin-border);
                "
            >

                {{ $applications->links() }}

            </div>

        @endif

    </div>



    {{-- =========================================================
        PAGE CSS
    ========================================================== --}}

    @push('styles')

        <style>

            .seller-app-th {
                padding: 12px 14px;
                border-bottom: 1px solid var(--admin-border);
                background: var(--admin-surface-soft);
                color: var(--admin-muted);
                font-size:13px;
                font-weight: 700;
                letter-spacing: .025em;
                text-align: left;
                white-space: nowrap;
            }


            .seller-app-td {
                padding: 13px 14px;
                border-bottom: 1px solid var(--admin-border);
                color: var(--admin-text);
                font-size:12px;
                vertical-align: middle;
            }


            .seller-app-row:last-child .seller-app-td {
                border-bottom: 0;
            }


            .seller-app-row {
                transition: background .15s ease;
            }


            .seller-app-row:hover {
                background: var(--admin-surface-soft);
            }



            /* =====================================================
               USER
            ====================================================== */

            .seller-app-user {
                display: flex;
                align-items: center;
                gap: 9px;
                min-width: 180px;
            }


            .seller-app-avatar {
                width: 32px;
                height: 32px;
                flex: 0 0 32px;

                display: grid;
                place-items: center;

                border-radius: 9px;

                background: var(--admin-accent-soft);
                color: var(--admin-accent);

                font-size:12px;
                font-weight: 700;
            }


            .seller-app-user > div:last-child {
                min-width: 0;
            }


            .seller-app-user strong {
                display: block;

                overflow: hidden;

                color: var(--admin-heading);

                font-size:12px;
                font-weight: 600;

                white-space: nowrap;
                text-overflow: ellipsis;
            }


            .seller-app-user span {
                display: block;

                max-width: 190px;

                margin-top: 2px;

                overflow: hidden;

                color: var(--admin-muted);

                font-size:12px;

                white-space: nowrap;
                text-overflow: ellipsis;
            }



            /* =====================================================
               STATUS
            ====================================================== */

            .seller-application-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;

                padding: 5px 8px;

                border-radius: 999px;

                font-size:12px;
                font-weight: 700;

                white-space: nowrap;
            }


            .seller-application-status > span {
                width: 6px;
                height: 6px;

                flex: 0 0 6px;

                border-radius: 50%;
            }



            .seller-application-status.is-review {
                background: rgba(47, 128, 237, .10);
                color: #2F80ED;
            }

            .seller-application-status.is-review > span {
                background: #2F80ED;
            }



            .seller-application-status.is-revision {
                background: rgba(247, 144, 9, .12);
                color: #F79009;
            }

            .seller-application-status.is-revision > span {
                background: #F79009;
            }



            .seller-application-status.is-payment {
                background: rgba(123, 97, 255, .10);
                color: #7B61FF;
            }

            .seller-application-status.is-payment > span {
                background: #7B61FF;
            }



            .seller-application-status.is-active {
                background: rgba(18, 183, 106, .11);
                color: #12B76A;
            }

            .seller-application-status.is-active > span {
                background: #12B76A;
            }



            .seller-application-status.is-muted {
                background: var(--admin-surface-hover);
                color: var(--admin-muted);
            }

            .seller-application-status.is-muted > span {
                background: var(--admin-muted);
            }



            /* =====================================================
               REVIEW BUTTON
            ====================================================== */

            .seller-app-review-button {
                height: 31px;

                display: inline-flex;
                align-items: center;
                justify-content: center;

                gap: 6px;

                padding: 0 10px;

                border: 1px solid var(--admin-border);
                border-radius: 8px;

                background: var(--admin-surface-soft);
                color: var(--admin-heading);

                font-size:13px;
                font-weight: 700;

                text-decoration: none;

                transition:
                    border-color .15s ease,
                    background .15s ease,
                    color .15s ease;
            }


            .seller-app-review-button:hover {
                border-color: var(--admin-accent);
                background: var(--admin-accent-soft);
                color: var(--admin-accent);
            }



            /* =====================================================
               EMPTY STATE
            ====================================================== */

            .seller-application-empty {
                display: flex;
                flex-direction: column;
                align-items: center;

                text-align: center;
            }


            .seller-application-empty > div {
                width: 48px;
                height: 48px;

                display: grid;
                place-items: center;

                margin-bottom: 11px;

                border-radius: 13px;

                background: var(--admin-accent-soft);
                color: var(--admin-accent);

                font-size: 15px;
            }


            .seller-application-empty strong {
                color: var(--admin-heading);
                font-size: 12px;
            }


            .seller-application-empty p {
                max-width: 350px;

                margin: 5px 0 0;

                color: var(--admin-muted);

                font-size:13px;
                line-height: 1.6;
            }



            /* =====================================================
               RESPONSIVE
            ====================================================== */

            @media (max-width: 700px) {

                .admin-module-header {
                    margin-bottom: 14px;
                }

            }

        </style>

    @endpush


@endsection