@extends('admin.layouts.app')

@section('title', 'Seller KYC')

@push('styles')
<style>
    .kyc-page {
        padding: 4px 0;
    }

    .kyc-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .kyc-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .kyc-head p {
        margin: 0;
        color: #718078;
        font-size: 12px;
    }

    .kyc-card {
        background: var(--admin-surface, #fff);
        border: 1px solid var(--admin-border, #e3e8e5);
        border-radius: 16px;
        padding: 18px;
    }

    .kyc-filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .kyc-filter {
        padding: 8px 11px;
        border-radius: 9px;
        background: #f2f5f3;
        color: #526158;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
    }

    .kyc-filter.active {
        background: #0b3d2e;
        color: #fff;
    }

    .kyc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .kyc-table th,
    .kyc-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf1ef;
        text-align: left;
        font-size: 11px;
    }

    .kyc-table th {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #849089;
    }

    .kyc-badge {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
    }

    .kyc-badge.pending {
        background: #fff4dc;
        color: #9a5900;
    }

    .kyc-badge.approved {
        background: #e9f9f0;
        color: #087443;
    }

    .kyc-badge.rejected {
        background: #fff0f0;
        color: #bf3030;
    }

    .kyc-view {
        color: #0b6947;
        font-weight: 800;
        text-decoration: none;
    }

    .kyc-alert {
        padding: 11px 13px;
        border-radius: 10px;
        margin-bottom: 14px;
        font-size: 11px;
    }

    .kyc-alert.success {
        background: #e9f9f0;
        color: #087443;
    }

    .kyc-alert.error {
        background: #fff0f0;
        color: #b22f2f;
    }

    @media(max-width: 760px) {
        .kyc-scroll {
            overflow-x: auto;
        }

        .kyc-head {
            display: block;
        }
    }
</style>
@endpush


@section('content')

<div class="kyc-page">

    <div class="kyc-head">

        <div>

            <h1>
                Seller KYC
            </h1>

            <p>
                Review identity verification submitted for wallet withdrawals.
            </p>

        </div>

    </div>


    @if(session('success'))

        <div class="kyc-alert success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="kyc-alert error">
            {{ session('error') }}
        </div>

    @endif


    <div class="kyc-card">

        <div class="kyc-filters">

            <a
                href="{{ route('admin.seller-kyc.index') }}"
                class="kyc-filter {{ !$status ? 'active' : '' }}"
            >
                All
            </a>


            <a
                href="{{
                    route(
                        'admin.seller-kyc.index',
                        [
                            'status' => 'pending',
                        ]
                    )
                }}"
                class="kyc-filter {{
                    $status === 'pending'
                        ? 'active'
                        : ''
                }}"
            >
                Pending
            </a>


            <a
                href="{{
                    route(
                        'admin.seller-kyc.index',
                        [
                            'status' => 'approved',
                        ]
                    )
                }}"
                class="kyc-filter {{
                    $status === 'approved'
                        ? 'active'
                        : ''
                }}"
            >
                Approved
            </a>


            <a
                href="{{
                    route(
                        'admin.seller-kyc.index',
                        [
                            'status' => 'rejected',
                        ]
                    )
                }}"
                class="kyc-filter {{
                    $status === 'rejected'
                        ? 'active'
                        : ''
                }}"
            >
                Rejected
            </a>

        </div>


        @if($kycs->count())

            <div class="kyc-scroll">

                <table class="kyc-table">

                    <thead>

                        <tr>

                            <th>
                                Seller
                            </th>

                            <th>
                                ID
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Submitted
                            </th>

                            <th></th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $kycs
                            as
                            $kyc
                        )

                            <tr>

                                <td>

                                    <strong>
                                        {{ $kyc->seller?->name }}
                                    </strong>

                                    <br>

                                    <small>
                                        {{ $kyc->seller?->email }}
                                    </small>

                                </td>


                                <td>

                                    {{
                                        strtoupper(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $kyc->id_type
                                            )
                                        )
                                    }}

                                    ••••{{ $kyc->id_number_last4 }}

                                </td>


                                <td>

                                    <span
                                        class="kyc-badge {{
                                            $kyc->status
                                        }}"
                                    >

                                        {{ $kyc->status_label }}

                                    </span>

                                </td>


                                <td>

                                    {{
                                        optional(
                                            $kyc->submitted_at
                                        )->format(
                                            'd M Y, h:i A'
                                        )
                                    }}

                                </td>


                                <td>

                                    <a
                                        class="kyc-view"
                                        href="{{
                                            route(
                                                'admin.seller-kyc.show',
                                                $kyc
                                            )
                                        }}"
                                    >
                                        Review →
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div style="margin-top:14px;">

                {{ $kycs->links() }}

            </div>

        @else

            <div
                style="
                    padding:30px;
                    text-align:center;
                    color:#7b8881;
                    font-size:12px;
                "
            >
                No KYC submissions found.
            </div>

        @endif

    </div>

</div>

@endsection