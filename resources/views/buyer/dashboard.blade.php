@extends('account.layouts.app')


@section(
    'title',
    'Buyer Dashboard'
)


@php

    $dashboardRole = 'buyer';


    $firstName =
        explode(
            ' ',
            trim($user->name)
        )[0];

@endphp


@section('content')


{{-- =========================================================
    PAGE HEADER
========================================================== --}}

<div class="dashboard-page-header buyer-page-header">

    <div>

        <h1 class="dashboard-page-title">

            Hi {{ $firstName }}

            <span class="dashboard-wave">
                👋
            </span>

        </h1>


        <p class="dashboard-page-subtitle">

            Buyer account

            <span>·</span>

            {{ $buyer['location'] }}

        </p>

    </div>

</div>



{{-- =========================================================
    BUYER SUMMARY
========================================================== --}}

<div class="buyer-summary-grid">


    {{-- Escrow --}}
    <section class="buyer-protection-card">

        <span class="buyer-protection-label">
            Your money protected in escrow
        </span>


        <strong class="buyer-protection-amount">

            {{ $statistics['escrow'] }}

        </strong>


        <p>

            {{
                $statistics[
                    'purchases_in_progress'
                ]
            }}
            purchases in progress · Not one naira
            reaches a seller until you accept.

        </p>


        <div class="buyer-protection-actions">

            <a
                href="{{ route('featured-businesses') }}"
                class="buyer-white-button"
            >

                <i class="fa-solid fa-shield-halved"></i>

                Buy securely

            </a>


            <a
                href="{{ route('buyer.seller-invite') }}"
                class="buyer-purple-button"
            >

                <i class="fa-solid fa-link"></i>

                Open a seller invite

            </a>

        </div>

    </section>



    {{-- Right Statistics --}}
    <div class="buyer-summary-stats">

        <div class="buyer-small-stats">

            <article class="dashboard-card buyer-stat-card">

                <span>
                    Trust score
                </span>


                <strong>

                    <i class="fa-regular fa-shield"></i>

                    {{
                        $statistics[
                            'trust_score'
                        ]
                    }}

                </strong>

            </article>


            <article class="dashboard-card buyer-stat-card">

                <span>
                    Purchases
                </span>


                <strong>

                    {{
                        $statistics[
                            'purchases'
                        ]
                    }}

                </strong>

            </article>

        </div>


        <article class="dashboard-card buyer-lifetime-card">

            <span>
                Protected from risk (lifetime)
            </span>


            <strong>

                {{
                    $statistics[
                        'protected_lifetime'
                    ]
                }}

            </strong>


            <p>
                Total payments held safely before release
            </p>

        </article>

    </div>

</div>



{{-- =========================================================
    ACTION NEEDED
========================================================== --}}

<section class="buyer-action-card">

    <div class="buyer-action-content">


        <div>

            <span class="dashboard-badge purple">

                Action needed · Confirm receipt

            </span>


            <h2>

                {{ $featuredTransaction['product'] }}

                ·

                {{ $featuredTransaction['amount'] }}

            </h2>


            <p class="dashboard-muted">

                Seller:
                {{ $featuredTransaction['seller'] }}

                <span>·</span>

                {{ $featuredTransaction['delivery_type'] }}

                <span>·</span>

                {{ $featuredTransaction['reference'] }}

            </p>


            <p class="buyer-action-description">

                Has your item arrived? Confirm receipt,
                then choose whether to release the funds
                right away or take the 8-hour inspection
                window first.

            </p>


            <div class="dashboard-action-row">

                <button
                    type="button"
                    class="dashboard-green-button"
                    data-dashboard-toast="Receipt confirmation will be connected when we build the transaction workflow."
                >

                    <i class="fa-solid fa-box"></i>

                    Order received

                </button>


                <button
                    type="button"
                    class="dashboard-danger-button"
                    data-dashboard-toast="The dispute workflow will be built with the transaction module."
                >

                    <i class="fa-solid fa-scale-balanced"></i>

                    Dispute

                </button>


                <a
                    href="{{ route('buyer.transactions') }}"
                    class="dashboard-text-button"
                >
                    View details
                </a>

            </div>

        </div>



        <div class="buyer-escrow-side">

            <span class="dashboard-muted">
                Held safely in escrow
            </span>


            <div class="buyer-escrow-box">

                <strong>

                    {{
                        $featuredTransaction[
                            'escrow_amount'
                        ]
                    }}

                </strong>


                <span>
                    Nothing released yet
                </span>

            </div>


            <p>

                No countdown is running until
                you confirm receipt.

            </p>

        </div>

    </div>

</section>



{{-- =========================================================
    LOWER AREA
========================================================== --}}

<div class="buyer-dashboard-grid">


    {{-- Transactions --}}
    <section class="dashboard-card dashboard-table-card">

        <div class="dashboard-card-header">

            <strong>
                Your transactions
            </strong>

        </div>


        <div class="dashboard-table-scroll">

            <table class="dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Item
                        </th>

                        <th>
                            Seller
                        </th>

                        <th>
                            You paid
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($transactions as $transaction)

                        <tr>

                            <td>

                                <strong>
                                    {{ $transaction['product'] }}
                                </strong>

                            </td>


                            <td>

                                {{ $transaction['seller'] }}

                            </td>


                            <td>

                                <strong>

                                    {{ $transaction['amount'] }}

                                </strong>

                            </td>


                            <td>

                                <span
                                    class="dashboard-badge {{
                                        $transaction['status_class']
                                    }}"
                                >

                                    {{ $transaction['status'] }}

                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </section>



    {{-- Right Column --}}
    <div class="buyer-dashboard-side">


        {{-- Notifications --}}
        <section class="dashboard-card dashboard-side-card">

            <div class="dashboard-side-heading">

                <strong>
                    Notifications
                </strong>


                <span class="dashboard-notification-count">

                    2

                </span>

            </div>


            <div class="dashboard-notification-list">

                @foreach ($notifications as $notification)

                    <div class="dashboard-notification-item">

                        <i
                            class="fa-solid {{
                                $notification['icon']
                            }}"
                        ></i>


                        <p>

                            <strong>
                                {{ $notification['title'] }}
                            </strong>

                            —

                            {{ $notification['message'] }}

                        </p>

                    </div>

                @endforeach

            </div>


            <a
                href="{{ route('buyer.notifications') }}"
                class="dashboard-outline-button full"
            >
                Open notification centre
            </a>

        </section>



        {{-- Quick Actions --}}
        <section class="dashboard-card dashboard-side-card">

            <strong class="dashboard-side-title">
                Quick actions
            </strong>


            <div class="dashboard-quick-actions">

                <a
                    href="{{ route('featured-businesses') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-store"></i>

                    Buy from a verified business

                </a>


                <a
                    href="{{ route('buyer.seller-invite') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-link"></i>

                    Open a seller invite

                </a>


                <a
                    href="{{ route('buyer.transactions') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-file-lines"></i>

                    Purchase history

                </a>


                <a
                    href="{{ route('support') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-regular fa-comments"></i>

                    Contact support

                </a>

            </div>

        </section>



        {{-- Businesses --}}
        <section class="dashboard-card dashboard-side-card">

            <strong class="dashboard-side-title">
                Featured businesses
            </strong>


            <p class="dashboard-side-description">

                Verified sellers you can trade with safely.

            </p>


            <div class="buyer-business-list">

                @foreach ($businesses as $business)

                    <a
                        href="{{ route('featured-businesses') }}"
                        class="buyer-business"
                    >

                        <span
                            class="buyer-business-avatar {{
                                $business['style']
                            }}"
                        >

                            {{ $business['initials'] }}

                        </span>


                        <span class="buyer-business-info">

                            <strong>
                                {{ $business['name'] }}
                            </strong>

                            <small>
                                {{ $business['category'] }}
                            </small>

                        </span>


                        <span class="buyer-business-score">

                            <i class="fa-regular fa-shield"></i>

                            {{ $business['trust'] }}

                        </span>

                    </a>

                @endforeach

            </div>


            <a
                href="{{ route('featured-businesses') }}"
                class="dashboard-outline-button full"
            >
                See all
            </a>

        </section>

    </div>

</div>

@endsection