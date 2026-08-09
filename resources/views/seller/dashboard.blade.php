@extends('account.layouts.app')


@section(
    'title',
    'Seller Dashboard'
)


@php

    $dashboardRole = 'seller';


    $firstName =
        explode(
            ' ',
            trim($user->name)
        )[0];


    $hour =
        now()->hour;


    $greeting =
        $hour < 12
            ? 'Good morning'
            : (
                $hour < 17
                    ? 'Good afternoon'
                    : 'Good evening'
            );

@endphp


@section('content')


{{-- =========================================================
    DASHBOARD HEADER
========================================================== --}}

<div class="dashboard-page-header">

    <div>

        <h1 class="dashboard-page-title">

            {{ $greeting }},
            {{ $firstName }}

            <span class="dashboard-wave">
                👋
            </span>

        </h1>


        <p class="dashboard-page-subtitle">

            {{ $seller['business_name'] }}

            <span>·</span>

            Seller account

            <span>·</span>

            {{ $seller['location'] }}

        </p>

    </div>


    <a
        href="{{ route('seller.transactions.create') }}"
        class="dashboard-primary-button"
    >

        <i class="fa-solid fa-plus"></i>

        Create transaction

    </a>

</div>



{{-- =========================================================
    PAYMENT RECEIVED
========================================================== --}}

<section class="seller-highlight-card">

    <div class="seller-highlight-content">


        <div class="seller-highlight-left">

            <span class="dashboard-badge green">

                <i class="fa-solid fa-sack-dollar"></i>

                Payment received · Ready to dispatch

            </span>


            <h2>

                {{ $featuredTransaction['product'] }}

                ·

                ₦{{
                    number_format(
                        $featuredTransaction['amount']
                    )
                }} held

            </h2>


            <p class="dashboard-muted">

                Buyer:
                {{ $featuredTransaction['buyer'] }}

                <span>·</span>

                {{ $featuredTransaction['reference'] }}

                <span>·</span>

                Paid {{ $featuredTransaction['paid_ago'] }}

            </p>


            <div class="seller-delivery-box">

                <strong>
                    Deliver to
                </strong>


                <span>

                    {{ $featuredTransaction['delivery_address'] }}

                </span>


                <span>

                    {{ $featuredTransaction['delivery_phone'] }}

                    ·

                    {{ $featuredTransaction['buyer'] }}

                </span>

            </div>


            <p class="seller-instruction">

                The money is secured — it's safe to send the item.
                Arrange your delivery, then mark the item as
                <strong>dispatched</strong>
                so the buyer knows it's on the way.

            </p>


            <div class="dashboard-action-row">

                <button
                    type="button"
                    class="dashboard-green-button"
                    data-dashboard-toast="The transaction module will handle dispatch confirmation when we build it."
                >

                    <i class="fa-solid fa-box"></i>

                    Item dispatched

                </button>


                <a
                    href="{{ route('seller.transactions') }}"
                    class="dashboard-text-button"
                >
                    View transaction
                </a>

            </div>

        </div>



        <div class="seller-highlight-payout">

            <span class="dashboard-muted">
                Your payout on completion
            </span>


            <div class="seller-payout-box">

                <strong>

                    ₦{{
                        number_format(
                            $featuredTransaction['payout']
                        )
                    }}

                </strong>


                <span>
                    after 5% fee + VAT
                </span>

            </div>


            <p>

                Released when the buyer accepts
                or the 8-hour inspection ends.

            </p>

        </div>

    </div>

</section>



{{-- =========================================================
    STATISTICS
========================================================== --}}

<div class="seller-stat-grid">

    @foreach ($statistics as $stat)

        <article class="dashboard-card dashboard-stat-card">

            <span class="dashboard-stat-label">

                {{ $stat['label'] }}

            </span>


            <strong class="dashboard-stat-value">

                {{ $stat['value'] }}

                @if(!empty($stat['suffix']))

                    <small>
                        {{ $stat['suffix'] }}
                    </small>

                @endif

            </strong>


            <span
                class="dashboard-stat-note
                       {{
                            $stat['class'] ?? ''
                       }}"
            >

                {{ $stat['note'] }}

            </span>

        </article>

    @endforeach

</div>



{{-- =========================================================
    LOWER DASHBOARD
========================================================== --}}

<div class="seller-dashboard-grid">


    {{-- Recent Transactions --}}
    <section class="dashboard-card dashboard-table-card">

        <div class="dashboard-card-header">

            <strong>
                Recent transactions
            </strong>


            <a
                href="{{ route('seller.transactions') }}"
            >
                View all
            </a>

        </div>


        <div class="dashboard-table-scroll">

            <table class="dashboard-table">

                <thead>

                    <tr>

                        <th>
                            Item
                        </th>

                        <th>
                            Buyer
                        </th>

                        <th>
                            Amount
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

                                <small>
                                    {{ $transaction['reference'] }}
                                </small>

                            </td>


                            <td>
                                {{ $transaction['buyer'] }}
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
    <div class="seller-dashboard-side">


        {{-- Revenue --}}
        <section class="dashboard-card dashboard-side-card">

            <strong class="dashboard-side-title">
                Revenue summary · July
            </strong>


            <div class="revenue-chart">

                <div
                    class="revenue-bar light"
                    style="height: 40%"
                ></div>

                <div
                    class="revenue-bar light"
                    style="height: 62%"
                ></div>

                <div
                    class="revenue-bar light"
                    style="height: 48%"
                ></div>

                <div
                    class="revenue-bar strong"
                    style="height: 88%"
                ></div>

            </div>


            <div class="revenue-labels">

                <span>Wk 1</span>

                <span>Wk 2</span>

                <span>Wk 3</span>

                <span>Wk 4</span>

            </div>


            <div class="revenue-total">

                <span>
                    Net payouts (after 5% fee)
                </span>

                <strong>
                    ₦1,218,400
                </strong>

            </div>

        </section>



        {{-- Notifications --}}
        <section class="dashboard-card dashboard-side-card">

            <div class="dashboard-side-heading">

                <strong>
                    Notifications
                </strong>


                <span class="dashboard-notification-count">

                    {{ count($notifications) }}

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
                href="{{ route('seller.notifications') }}"
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
                    href="{{ route('seller.transactions.create') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-plus purple-icon"></i>

                    New transaction

                </a>


                <button
                    type="button"
                    class="dashboard-outline-button"
                    data-dashboard-toast="Dispatch controls will connect to the transaction module."
                >

                    <i class="fa-solid fa-box"></i>

                    Mark item dispatched

                </button>


                <a
                    href="{{ route('seller.profile-settings') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-building-columns"></i>

                    Update payout bank

                </a>


                <a
                    href="{{ route('seller.business-profile') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-store"></i>

                    Edit business profile

                </a>


                <a
                    href="{{ route('verified-sellers') }}"
                    class="dashboard-outline-button"
                >

                    <i class="fa-solid fa-star"></i>

                    Manage verified package

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

    </div>

</div>

@endsection