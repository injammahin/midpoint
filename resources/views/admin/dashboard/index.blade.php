@extends('admin.layouts.app')


@section(
    'title',
    'Dashboard'
)


@section(
    'page-title',
    'Dashboard'
)


@section('content')

    {{-- =========================================================
        WELCOME
    ========================================================== --}}
    <section
        class="admin-card
               admin-dashboard-welcome"
    >

        <div>

            <div class="admin-dashboard-eyebrow">
                Administration overview
            </div>


            <h2>
                Welcome, {{ auth()->user()->name }}
            </h2>


            <p>
                Review live activity, secure transactions,
                seller growth and customer support activity.
            </p>


            <div class="admin-dashboard-meta">

                <span>

                    <i class="fa-regular fa-calendar"></i>

                    {{ now()->format('l, F j, Y') }}

                </span>


                <span>

                    <span class="admin-live-dot"></span>

                    Live database connected

                </span>

            </div>

        </div>


        <div class="admin-welcome-side">

            <div class="admin-welcome-side-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>


            <div class="admin-welcome-side-info">

                <span>
                    Platform status
                </span>

                <strong>
                    All systems operational
                </strong>

            </div>


            <div class="admin-welcome-days">

                <strong>
                    24
                </strong>

                <span>
                    inquiries
                </span>

            </div>

        </div>

    </section>



    {{-- =========================================================
        STATS
    ========================================================== --}}
    <section class="admin-stat-grid">

        {{-- Users --}}
        <article
            class="admin-card
                   admin-stat-card"
        >

            <div class="admin-stat-card-label">
                Total Users
            </div>


            <div class="admin-stat-card-value">
                {{ number_format($stats['users']) }}
            </div>


            <div class="admin-stat-card-info">

                <strong>
                    +12.4%
                </strong>

                from last month

            </div>


            <div class="admin-stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

        </article>


        {{-- Sellers --}}
        <article
            class="admin-card
                   admin-stat-card"
        >

            <div class="admin-stat-card-label">
                Verified Sellers
            </div>


            <div class="admin-stat-card-value">
                {{ number_format($stats['sellers']) }}
            </div>


            <div class="admin-stat-card-info">

                <strong>
                    18 pending
                </strong>

                verification requests

            </div>


            <div class="admin-stat-icon">

                <i class="fa-solid fa-store"></i>

            </div>

        </article>


        {{-- Transactions --}}
        <article
            class="admin-card
                   admin-stat-card"
        >

            <div class="admin-stat-card-label">
                Transactions
            </div>


            <div class="admin-stat-card-value">
                {{ number_format($stats['transactions']) }}
            </div>


            <div class="admin-stat-card-info">

                <strong>
                    +18.1%
                </strong>

                transaction growth

            </div>


            <div class="admin-stat-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>

        </article>


        {{-- Inquiry --}}
        <article
            class="admin-card
                   admin-stat-card"
        >

            <div class="admin-stat-card-label">
                Support & Inquiries
            </div>


            <div class="admin-stat-card-value">
                {{ number_format($stats['inquiries']) }}
            </div>


            <div class="admin-stat-card-info">

                <strong>
                    7 new
                </strong>

                waiting for response

            </div>


            <div class="admin-stat-icon">

                <i class="fa-solid fa-headset"></i>

            </div>

        </article>

    </section>



    {{-- =========================================================
        CHART + STATUS
    ========================================================== --}}
    <section class="admin-dashboard-grid">

        {{-- Chart --}}
        <article
            class="admin-card
                   admin-panel"
        >

            <div class="admin-panel-head">

                <div>

                    <h3>
                        Transaction Trend
                    </h3>

                    <p>
                        Monthly protected transactions during 2026.
                    </p>

                </div>


                <span class="admin-panel-badge">

                    <i class="fa-solid fa-circle"></i>

                    842 total

                </span>

            </div>


            <div class="admin-chart">

                <svg
                    viewBox="0 0 900 280"
                    preserveAspectRatio="none"
                    aria-label="Transaction trend chart"
                >

                    {{-- Grid --}}
                    <line
                        class="admin-chart-grid"
                        x1="40"
                        y1="35"
                        x2="880"
                        y2="35"
                    />

                    <line
                        class="admin-chart-grid"
                        x1="40"
                        y1="100"
                        x2="880"
                        y2="100"
                    />

                    <line
                        class="admin-chart-grid"
                        x1="40"
                        y1="165"
                        x2="880"
                        y2="165"
                    />

                    <line
                        class="admin-chart-grid"
                        x1="40"
                        y1="230"
                        x2="880"
                        y2="230"
                    />


                    {{-- Area --}}
                    <path
                        class="admin-chart-area"
                        d="
                            M40 215
                            L160 202
                            L280 190
                            L400 165
                            L520 147
                            L640 118
                            L760 86
                            L880 40
                            L880 230
                            L40 230
                            Z
                        "
                    />


                    {{-- Line --}}
                    <polyline
                        class="admin-chart-line"
                        points="
                            40,215
                            160,202
                            280,190
                            400,165
                            520,147
                            640,118
                            760,86
                            880,40
                        "
                    />


                    @foreach ([
                        [40,215],
                        [160,202],
                        [280,190],
                        [400,165],
                        [520,147],
                        [640,118],
                        [760,86],
                        [880,40],
                    ] as $point)

                        <circle
                            class="admin-chart-point"
                            cx="{{ $point[0] }}"
                            cy="{{ $point[1] }}"
                            r="5"
                        />

                    @endforeach

                </svg>


                <div class="admin-chart-labels">

                    <span>Jan</span>
                    <span>Feb</span>
                    <span>Mar</span>
                    <span>Apr</span>
                    <span>May</span>
                    <span>Jun</span>
                    <span>Jul</span>
                    <span>Aug</span>

                </div>

            </div>

        </article>



        {{-- Status --}}
        <article
            class="admin-card
                   admin-panel"
        >

            <div class="admin-panel-head">

                <div>

                    <h3>
                        Transaction Status
                    </h3>

                    <p>
                        Current transaction distribution.
                    </p>

                </div>

            </div>


            <div class="admin-donut-wrap">

                <div class="admin-donut">

                    <div class="admin-donut-center">

                        <strong>
                            100
                        </strong>

                        <span>
                            Total
                        </span>

                    </div>

                </div>

            </div>


            <div class="admin-status-list">

                <div class="admin-status-row">

                    <span
                        class="admin-status-dot"
                        style="background:#55dbc4"
                    ></span>

                    Active

                    <strong>
                        {{ $transactionStatuses['active'] }}
                    </strong>

                </div>


                <div class="admin-status-row">

                    <span
                        class="admin-status-dot"
                        style="background:#dbe5ef"
                    ></span>

                    Completed

                    <strong>
                        {{ $transactionStatuses['completed'] }}
                    </strong>

                </div>


                <div class="admin-status-row">

                    <span
                        class="admin-status-dot"
                        style="background:#50617e"
                    ></span>

                    Disputed

                    <strong>
                        {{ $transactionStatuses['disputed'] }}
                    </strong>

                </div>


                <div class="admin-status-row">

                    <span
                        class="admin-status-dot"
                        style="background:#ff9638"
                    ></span>

                    Cancelled

                    <strong>
                        {{ $transactionStatuses['cancelled'] }}
                    </strong>

                </div>

            </div>

        </article>

    </section>



    {{-- =========================================================
        QUICK ACTION + RECENT
    ========================================================== --}}
    <section class="admin-dashboard-grid">

        {{-- Quick Actions --}}
        <article
            class="admin-card
                   admin-panel"
        >

            <div class="admin-panel-head">

                <div>

                    <h3>
                        Quick Actions
                    </h3>

                    <p>
                        Frequently used administration modules.
                    </p>

                </div>

            </div>


            <div class="admin-quick-grid">

                <a
                    href="{{ route('admin.website-settings.app-settings') }}"
                    class="admin-quick-action"
                >

                    <span class="admin-quick-icon">

                        <i class="fa-solid fa-gear"></i>

                    </span>


                    <span class="admin-quick-action-content">

                        <strong>
                            App Settings
                        </strong>

                        <span>
                            Configure website settings
                        </span>

                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </a>


                <a
                    href="{{ route('admin.website-settings.faqs') }}"
                    class="admin-quick-action"
                >

                    <span class="admin-quick-icon">

                        <i class="fa-regular fa-circle-question"></i>

                    </span>


                    <span class="admin-quick-action-content">

                        <strong>
                            Manage FAQs
                        </strong>

                        <span>
                            Edit public questions
                        </span>

                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </a>


                <a
                    href="{{ route('admin.support-inquiries.contacts') }}"
                    class="admin-quick-action"
                >

                    <span class="admin-quick-icon">

                        <i class="fa-solid fa-envelope"></i>

                    </span>


                    <span class="admin-quick-action-content">

                        <strong>
                            Contact Messages
                        </strong>

                        <span>
                            Review contact inquiries
                        </span>

                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </a>


                <a
                    href="{{ route('admin.support-inquiries.support-messages') }}"
                    class="admin-quick-action"
                >

                    <span class="admin-quick-icon">

                        <i class="fa-solid fa-headset"></i>

                    </span>


                    <span class="admin-quick-action-content">

                        <strong>
                            Support Messages
                        </strong>

                        <span>
                            Customer support requests
                        </span>

                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </a>

            </div>

        </article>


        {{-- Setup --}}
        <article
            class="admin-card
                   admin-panel"
        >

            <div class="admin-panel-head">

                <div>

                    <h3>
                        Platform Setup
                    </h3>

                    <p>
                        Current administration modules.
                    </p>

                </div>


                <strong
                    style="
                        color:var(--admin-accent);
                        font-size:10px;
                    "
                >
                    100%
                </strong>

            </div>


            <div class="admin-status-list">

                @foreach ([
                    'Frontend website',
                    'Admin authentication',
                    'Admin dashboard',
                    'Website settings',
                    'Support module',
                    'Shared users table',
                ] as $item)

                    <div class="admin-status-row">

                        <span
                            class="admin-status-dot"
                            style="background:#55dbc4"
                        ></span>

                        {{ $item }}

                        <strong
                            style="color:var(--admin-accent)"
                        >
                            Active
                        </strong>

                    </div>

                @endforeach

            </div>

        </article>

    </section>

@endsection