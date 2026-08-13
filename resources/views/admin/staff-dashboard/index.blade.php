@extends('admin.layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Formatting
    |--------------------------------------------------------------------------
    */

    $averageRating =
        (float) (
            $stats['average_rating']
            ?? 0
        );


    $ratingPercentage =
        $averageRating > 0
            ? round(
                ($averageRating / 5) * 100
            )
            : 0;


    $resolutionMinutes =
        (int) (
            $stats['average_resolution_minutes']
            ?? 0
        );


    if ($resolutionMinutes >= 60) {

        $resolutionTimeLabel =
            floor(
                $resolutionMinutes / 60
            )
            .
            'h '
            .
            (
                $resolutionMinutes % 60
            )
            .
            'm';

    } elseif ($resolutionMinutes > 0) {

        $resolutionTimeLabel =
            $resolutionMinutes
            .
            ' min';

    } else {

        $resolutionTimeLabel =
            '—';
    }


    $periodLabel =
        match($period) {

            1 =>
                'This month',

            6 =>
                'Last 6 months',

            12 =>
                'Last 12 months',

            default =>
                'This month',
        };


    $staffChartData = [

        'performance' =>
            $performanceChart,

        'ratings' =>
            $ratingDistribution,

    ];

@endphp


<div class="staff-dashboard">


    {{-- =========================================================
        HERO
    ========================================================== --}}

    <section class="admin-card staff-hero">

        <div>

            <div class="staff-kicker">

                <span class="staff-live-dot"></span>

                Staff Operations Center

            </div>


            <h2>

                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                {{ $user->name }}

            </h2>


            <p>

                Monitor your support queue, disputes, resolved cases,
                and customer feedback from one place.

            </p>


            <div class="staff-hero-meta">

                <span>

                    <i class="fa-regular fa-calendar"></i>

                    {{ now()->format('l, d F Y') }}

                </span>


                <span>

                    <i class="fa-regular fa-clock"></i>

                    {{ now()->format('h:i A') }}

                </span>


                <span>

                    <i class="fa-solid fa-headset"></i>

                    Support Agent

                </span>

            </div>

        </div>


        <div class="staff-hero-actions">


            {{-- Availability --}}

            <div
                class="
                    staff-agent-status

                    {{
                        $profile->is_enabled
                        &&
                        $profile->is_accepting_chats
                            ? 'online'
                            : 'offline'
                    }}
                "
            >

                <span></span>


                {{
                    $profile->is_enabled
                    &&
                    $profile->is_accepting_chats
                        ? 'Accepting chats'
                        : 'Not accepting chats'
                }}

            </div>


            <form
                method="GET"
                action="{{ route('admin.staff-dashboard') }}"
                class="staff-period-form"
            >

                <label for="staffPeriod">
                    Performance period
                </label>


                <select
                    id="staffPeriod"
                    name="period"
                    onchange="this.form.submit()"
                >

                    <option
                        value="1"
                        {{ $period === 1 ? 'selected' : '' }}
                    >
                        This month
                    </option>


                    <option
                        value="6"
                        {{ $period === 6 ? 'selected' : '' }}
                    >
                        Last 6 months
                    </option>


                    <option
                        value="12"
                        {{ $period === 12 ? 'selected' : '' }}
                    >
                        Last 12 months
                    </option>

                </select>

            </form>

        </div>

    </section>


    {{-- =========================================================
        PRIORITY / ALERT BAR
    ========================================================== --}}

    @if(
        ($stats['new_disputes'] ?? 0) > 0
        ||
        ($stats['waiting_chats'] ?? 0) > 0
        ||
        ($stats['new_contacts'] ?? 0) > 0
    )

        <section class="staff-priority-bar">

            <div class="staff-priority-icon">

                <i class="fa-solid fa-bell"></i>

            </div>


            <div class="staff-priority-copy">

                <strong>
                    New work needs attention
                </strong>

                <span>

                    {{ $stats['new_disputes'] }} new dispute{{ $stats['new_disputes'] === 1 ? '' : 's' }},

                    {{ $stats['waiting_chats'] }} customer{{ $stats['waiting_chats'] === 1 ? '' : 's' }} waiting for chat,

                    and

                    {{ $stats['new_contacts'] }} new support request{{ $stats['new_contacts'] === 1 ? '' : 's' }}.

                </span>

            </div>


            <div class="staff-priority-actions">

                @if(
                    auth()
                        ->user()
                        ->hasAdminPermission(
                            'support.live.manage'
                        )
                )

                    <a href="{{ route('admin.live-support.index') }}">

                        <i class="fa-solid fa-headset"></i>

                        Open Live Support

                    </a>

                @endif


                @if(
                    auth()
                        ->user()
                        ->hasAdminPermission(
                            'disputes.manage'
                        )
                )

                    <a href="{{ route('admin.disputes.index') }}">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        View Disputes

                    </a>

                @endif

            </div>

        </section>

    @endif


    {{-- =========================================================
        PRIMARY KPI CARDS
    ========================================================== --}}

    <section class="staff-kpi-grid">


        {{-- Waiting Chats --}}

        <article class="admin-card staff-kpi-card">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon green">

                    <i class="fa-solid fa-comments"></i>

                </span>


                <span class="staff-kpi-badge">
                    Live queue
                </span>

            </div>


            <div class="staff-kpi-label">
                Waiting chats
            </div>


            <strong class="staff-kpi-value">

                {{ number_format($stats['waiting_chats']) }}

            </strong>


            <p>

                Customers currently waiting for an agent.

            </p>

        </article>


        {{-- My Active Chats --}}

        <article class="admin-card staff-kpi-card">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon blue">

                    <i class="fa-solid fa-headset"></i>

                </span>


                <span class="staff-kpi-badge">
                    Mine
                </span>

            </div>


            <div class="staff-kpi-label">
                My active chats
            </div>


            <strong class="staff-kpi-value">

                {{ number_format($stats['my_active_chats']) }}

            </strong>


            <p>

                Conversations currently assigned to you.

            </p>

        </article>


        {{-- New Disputes --}}

        <article class="admin-card staff-kpi-card">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon amber">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </span>


                <span class="staff-kpi-badge attention">
                    Needs review
                </span>

            </div>


            <div class="staff-kpi-label">
                New disputes
            </div>


            <strong class="staff-kpi-value">

                {{ number_format($stats['new_disputes']) }}

            </strong>


            <p>

                Newly opened transaction disputes.

            </p>

        </article>


        {{-- Unresolved Queue --}}

        <article class="admin-card staff-kpi-card">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon red">

                    <i class="fa-solid fa-list-check"></i>

                </span>


                <span class="staff-kpi-badge">
                    Team queue
                </span>

            </div>


            <div class="staff-kpi-label">
                Unresolved work
            </div>


            <strong class="staff-kpi-value">

                {{ number_format($stats['unresolved_work_queue']) }}

            </strong>


            <p>

                Chats, disputes and requests still requiring attention.

            </p>

        </article>


        {{-- My Resolved Cases --}}

        <article class="admin-card staff-kpi-card highlight">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon purple">

                    <i class="fa-solid fa-circle-check"></i>

                </span>


                <span class="staff-kpi-badge success">
                    My performance
                </span>

            </div>


            <div class="staff-kpi-label">
                My resolved cases
            </div>


            <strong class="staff-kpi-value">

                {{ number_format($stats['my_resolved_cases']) }}

            </strong>


            <p>

                {{ number_format($stats['my_resolved_cases_period']) }}

                resolved during {{ strtolower($periodLabel) }}.

            </p>

        </article>


        {{-- Personal Rating --}}

        <article class="admin-card staff-kpi-card rating-card">

            <div class="staff-kpi-top">

                <span class="staff-kpi-icon yellow">

                    <i class="fa-solid fa-star"></i>

                </span>


                <span class="staff-kpi-badge success">
                    Only your ratings
                </span>

            </div>


            <div class="staff-kpi-label">
                My customer rating
            </div>


            <div class="staff-rating-value">

                <strong>

                    {{
                        $stats['rating_count'] > 0
                            ? number_format(
                                $averageRating,
                                2
                            )
                            : '—'
                    }}

                </strong>


                <span>
                    / 5
                </span>

            </div>


            <div class="staff-stars">

                @for($star = 1; $star <= 5; $star++)

                    <i
                        class="
                            fa-solid
                            fa-star

                            {{
                                $star <= round($averageRating)
                                    ? 'active'
                                    : ''
                            }}
                        "
                    ></i>

                @endfor

            </div>


            <p>

                Based on

                {{ number_format($stats['rating_count']) }}

                customer rating{{ $stats['rating_count'] === 1 ? '' : 's' }}.

            </p>

        </article>

    </section>


    {{-- =========================================================
        SECONDARY STATS
    ========================================================== --}}

    <section class="staff-mini-grid">


        <article class="admin-card staff-mini-card">

            <span class="staff-mini-icon">

                <i class="fa-solid fa-message"></i>

            </span>


            <div>

                <span>
                    My resolved chats
                </span>


                <strong>
                    {{ number_format($stats['my_resolved_chats']) }}
                </strong>

            </div>

        </article>


        <article class="admin-card staff-mini-card">

            <span class="staff-mini-icon">

                <i class="fa-solid fa-scale-balanced"></i>

            </span>


            <div>

                <span>
                    My resolved disputes
                </span>


                <strong>
                    {{ number_format($stats['my_resolved_disputes']) }}
                </strong>

            </div>

        </article>


        <article class="admin-card staff-mini-card">

            <span class="staff-mini-icon">

                <i class="fa-regular fa-envelope"></i>

            </span>


            <div>

                <span>
                    New support requests
                </span>


                <strong>
                    {{ number_format($stats['new_contacts']) }}
                </strong>

            </div>

        </article>


        <article class="admin-card staff-mini-card">

            <span class="staff-mini-icon">

                <i class="fa-solid fa-stopwatch"></i>

            </span>


            <div>

                <span>
                    Avg. chat resolution
                </span>


                <strong>
                    {{ $resolutionTimeLabel }}
                </strong>

            </div>

        </article>


        <article class="admin-card staff-mini-card">

            <span class="staff-mini-icon">

                <i class="fa-solid fa-star"></i>

            </span>


            <div>

                <span>
                    5-star ratings
                </span>


                <strong>
                    {{ number_format($stats['five_star_ratings']) }}
                </strong>

            </div>

        </article>

    </section>


    {{-- =========================================================
        PERFORMANCE CHARTS
    ========================================================== --}}

    <section class="staff-chart-grid">


        {{-- Resolution Performance --}}

        <article class="admin-card staff-chart-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        My performance
                    </span>


                    <h3>
                        Resolved workload
                    </h3>


                    <p>

                        Chats and disputes resolved personally by you.

                    </p>

                </div>


                <span class="staff-chart-period">

                    {{ $periodLabel }}

                </span>

            </div>


            <div
                id="staffResolvedChart"
                class="staff-chart"
            ></div>

        </article>


        {{-- Rating Trend --}}

        <article class="admin-card staff-chart-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Customer feedback
                    </span>


                    <h3>
                        My rating trend
                    </h3>


                    <p>

                        Average rating from customers you personally supported.

                    </p>

                </div>


                <div class="staff-rating-summary">

                    <strong>

                        {{
                            $stats['rating_count'] > 0
                                ? number_format(
                                    $averageRating,
                                    2
                                )
                                : '—'
                        }}

                    </strong>

                    <span>
                        / 5
                    </span>

                </div>

            </div>


            <div
                id="staffRatingTrendChart"
                class="staff-chart"
            ></div>

        </article>

    </section>


    {{-- =========================================================
        QUEUES
    ========================================================== --}}

    <section class="staff-work-grid">


        {{-- =====================================================
            DISPUTES
        ====================================================== --}}

        <article class="admin-card staff-list-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Dispute queue
                    </span>

                    <h3>
                        Disputes needing attention
                    </h3>

                </div>


                @if(
                    auth()
                        ->user()
                        ->hasAdminPermission(
                            'disputes.manage'
                        )
                )

                    <a
                        href="{{ route('admin.disputes.index') }}"
                        class="staff-view-all"
                    >
                        View all
                    </a>

                @endif

            </div>


            <div class="staff-list">


                @forelse($recentDisputes as $dispute)

                    <a
                        href="{{ route('admin.disputes.show', $dispute) }}"
                        class="staff-list-item"
                    >

                        <span
                            class="
                                staff-list-icon
                                dispute
                            "
                        >

                            <i class="fa-solid fa-scale-balanced"></i>

                        </span>


                        <div class="staff-list-copy">

                            <strong>

                                {{
                                    $dispute->transaction?->reference
                                    ??
                                    'Dispute #' . $dispute->id
                                }}

                            </strong>


                            <span>

                                {{
                                    $dispute->buyer?->name
                                    ??
                                    'Buyer'
                                }}

                                vs

                                {{
                                    $dispute->seller?->name
                                    ??
                                    'Seller'
                                }}

                            </span>


                            <small>

                                Opened

                                {{
                                    optional(
                                        $dispute->opened_at
                                        ??
                                        $dispute->created_at
                                    )->diffForHumans()
                                }}

                            </small>

                        </div>


                        <span
                            class="
                                staff-status-pill
                                status-{{
                                    str_replace(
                                        '_',
                                        '-',
                                        $dispute->status
                                    )
                                }}
                            "
                        >

                            {{ $dispute->status_label }}

                        </span>

                    </a>

                @empty

                    <div class="staff-empty">

                        <i class="fa-solid fa-circle-check"></i>

                        <strong>
                            No unresolved disputes
                        </strong>

                        <span>
                            The dispute queue is clear.
                        </span>

                    </div>

                @endforelse

            </div>

        </article>


        {{-- =====================================================
            WAITING LIVE SUPPORT
        ====================================================== --}}

        <article class="admin-card staff-list-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Live support
                    </span>

                    <h3>
                        Customers waiting
                    </h3>

                </div>


                @if(
                    auth()
                        ->user()
                        ->hasAdminPermission(
                            'support.live.manage'
                        )
                )

                    <a
                        href="{{ route('admin.live-support.index') }}"
                        class="staff-view-all"
                    >
                        Open support
                    </a>

                @endif

            </div>


            <div class="staff-list">


                @forelse($recentWaitingChats as $session)

                    <a
                        href="{{ route('admin.live-support.index') }}"
                        class="staff-list-item"
                    >

                        <span class="staff-list-icon chat">

                            <i class="fa-solid fa-comments"></i>

                        </span>


                        <div class="staff-list-copy">

                            <strong>

                                {{
                                    $session->user?->name
                                    ??
                                    'Customer'
                                }}

                            </strong>


                            <span>

                                {{
                                    $session->topic
                                    ?: 'General support'
                                }}

                            </span>


                            <small>

                                Waiting

                                {{
                                    $session->created_at
                                        ? $session->created_at->diffForHumans()
                                        : ''
                                }}

                            </small>

                        </div>


                        @if($session->queue_position)

                            <span class="staff-queue-number">

                                #{{ $session->queue_position }}

                            </span>

                        @endif

                    </a>

                @empty

                    <div class="staff-empty">

                        <i class="fa-solid fa-headset"></i>

                        <strong>
                            Nobody is waiting
                        </strong>

                        <span>
                            The live support queue is clear.
                        </span>

                    </div>

                @endforelse

            </div>

        </article>


        {{-- =====================================================
            CONTACT REQUESTS
        ====================================================== --}}

        <article class="admin-card staff-list-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Support inbox
                    </span>

                    <h3>
                        New support requests
                    </h3>

                </div>


                @if(
                    auth()
                        ->user()
                        ->hasAdminPermission(
                            'support.contacts.manage'
                        )
                )

                    <a
                        href="{{
                            route(
                                'admin.support-inquiries.contacts'
                            )
                        }}"
                        class="staff-view-all"
                    >
                        View inbox
                    </a>

                @endif

            </div>


            <div class="staff-list">


                @forelse($recentContacts as $contact)

                    <a
                        href="{{
                            route(
                                'admin.support-inquiries.contacts.show',
                                $contact
                            )
                        }}"
                        class="staff-list-item"
                    >

                        <span class="staff-list-icon contact">

                            <i class="fa-regular fa-envelope"></i>

                        </span>


                        <div class="staff-list-copy">

                            <strong>
                                {{ $contact->name }}
                            </strong>


                            <span>
                                {{ $contact->topic_label }}
                            </span>


                            <small>

                                {{ $contact->created_at->diffForHumans() }}

                            </small>

                        </div>


                        <span
                            class="
                                staff-status-pill
                                status-{{
                                    str_replace(
                                        '_',
                                        '-',
                                        $contact->status
                                    )
                                }}
                            "
                        >

                            {{
                                ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $contact->status
                                    )
                                )
                            }}

                        </span>

                    </a>

                @empty

                    <div class="staff-empty">

                        <i class="fa-regular fa-envelope-open"></i>

                        <strong>
                            Support inbox clear
                        </strong>

                        <span>
                            No open customer requests.
                        </span>

                    </div>

                @endforelse

            </div>

        </article>

    </section>


    {{-- =========================================================
        CUSTOMER FEEDBACK
    ========================================================== --}}

    <section class="staff-feedback-grid">


        {{-- Recent Reviews --}}

        <article class="admin-card staff-feedback-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Customer feedback
                    </span>


                    <h3>
                        My latest ratings
                    </h3>


                    <p>
                        Only feedback for conversations handled by you.
                    </p>

                </div>

            </div>


            <div class="staff-review-list">


                @forelse($recentRatings as $rating)

                    <div class="staff-review-item">

                        <div class="staff-review-top">


                            <div class="staff-review-user">

                                <span class="staff-review-avatar">

                                    {{
                                        strtoupper(
                                            mb_substr(
                                                $rating->user?->name
                                                ??
                                                'C',
                                                0,
                                                1
                                            )
                                        )
                                    }}

                                </span>


                                <div>

                                    <strong>

                                        {{
                                            $rating->user?->name
                                            ??
                                            'Customer'
                                        }}

                                    </strong>


                                    <small>

                                        {{
                                            optional(
                                                $rating->rated_at
                                            )->diffForHumans()
                                        }}

                                    </small>

                                </div>

                            </div>


                            <div class="staff-review-stars">

                                @for($star = 1; $star <= 5; $star++)

                                    <i
                                        class="
                                            fa-solid
                                            fa-star

                                            {{
                                                $star <= $rating->rating
                                                    ? 'active'
                                                    : ''
                                            }}
                                        "
                                    ></i>

                                @endfor

                            </div>

                        </div>


                        @if($rating->review)

                            <p>

                                “{{ $rating->review }}”

                            </p>

                        @else

                            <p class="muted">

                                Customer left a rating without a written review.

                            </p>

                        @endif

                    </div>

                @empty

                    <div class="staff-empty large">

                        <i class="fa-regular fa-star"></i>

                        <strong>
                            No customer ratings yet
                        </strong>

                        <span>

                            Ratings from chats you resolve will appear here.

                        </span>

                    </div>

                @endforelse

            </div>

        </article>


        {{-- Rating Distribution --}}

        <article class="admin-card staff-rating-chart-card">

            <div class="staff-section-head">

                <div>

                    <span class="staff-section-kicker">
                        Rating quality
                    </span>


                    <h3>
                        My rating distribution
                    </h3>

                </div>

            </div>


            <div
                id="staffRatingDistributionChart"
                class="staff-rating-donut"
            ></div>


            <div class="staff-rating-score">

                <strong>

                    {{
                        $stats['rating_count'] > 0
                            ? number_format(
                                $averageRating,
                                2
                            )
                            : '—'
                    }}

                </strong>

                <span>
                    average out of 5
                </span>

            </div>

        </article>

    </section>

</div>

@endsection


@push('styles')

<style>

.staff-dashboard {
    display: flex;
    flex-direction: column;
    gap: 20px;
}


/* =========================================================
   HERO
========================================================= */

.staff-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    padding: 24px;
}


.staff-kicker,
.staff-section-kicker {
    display: flex;
    align-items: center;
    gap: 7px;

    color: #0f9f85;

    font-size: 11px;
    font-weight: 800;

    text-transform: uppercase;
    letter-spacing: .08em;
}


.staff-live-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #10b981;

    box-shadow:
        0 0 0 5px rgba(16, 185, 129, .12);
}


.staff-hero h2 {
    margin: 9px 0 6px;

    color: #172033;

    font-size: 25px;
}


.staff-hero p {
    margin: 0;

    max-width: 650px;

    color: #64748b;

    font-size: 14px;

    line-height: 1.55;
}


.staff-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;

    margin-top: 15px;
}


.staff-hero-meta span {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    color: #64748b;

    font-size: 12px;
}


.staff-hero-actions {
    min-width: 220px;

    display: flex;
    flex-direction: column;
    align-items: flex-end;

    gap: 12px;
}


.staff-agent-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    border-radius: 999px;

    padding: 8px 11px;

    font-size: 11px;
    font-weight: 800;
}


.staff-agent-status > span {
    width: 8px;
    height: 8px;

    border-radius: 50%;
}


.staff-agent-status.online {
    background: #ecfdf5;
    color: #047857;
}


.staff-agent-status.online > span {
    background: #10b981;
}


.staff-agent-status.offline {
    background: #f8fafc;
    color: #64748b;
}


.staff-agent-status.offline > span {
    background: #94a3b8;
}


.staff-period-form {
    display: flex;
    flex-direction: column;
    gap: 5px;
}


.staff-period-form label {
    color: #94a3b8;

    font-size: 10px;
    font-weight: 800;

    text-transform: uppercase;
    letter-spacing: .05em;
}


.staff-period-form select {
    min-width: 170px;
    height: 40px;

    border: 1px solid #dbe3ec;
    border-radius: 10px;

    padding: 0 10px;

    background: #fff;

    color: #334155;

    font-weight: 700;

    outline: none;
}


/* =========================================================
   PRIORITY BAR
========================================================= */

.staff-priority-bar {
    display: flex;
    align-items: center;

    gap: 13px;

    border: 1px solid #fde68a;
    border-radius: 15px;

    padding: 14px 16px;

    background: #fffbeb;
}


.staff-priority-icon {
    width: 40px;
    height: 40px;

    flex: 0 0 40px;

    border-radius: 11px;

    display: grid;
    place-items: center;

    background: #fef3c7;
    color: #b45309;
}


.staff-priority-copy {
    flex: 1;

    min-width: 0;
}


.staff-priority-copy strong {
    display: block;

    color: #78350f;

    font-size: 13px;
}


.staff-priority-copy span {
    display: block;

    margin-top: 3px;

    color: #92400e;

    font-size: 12px;
}


.staff-priority-actions {
    display: flex;
    gap: 8px;

    flex-wrap: wrap;
}


.staff-priority-actions a {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    border: 1px solid #fcd34d;
    border-radius: 9px;

    padding: 8px 10px;

    background: #fff;

    color: #92400e;

    font-size: 11px;
    font-weight: 800;

    text-decoration: none;
}


/* =========================================================
   KPI
========================================================= */

.staff-kpi-grid {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(0, 1fr)
        );

    gap: 16px;
}


.staff-kpi-card {
    padding: 19px;
}


.staff-kpi-card.highlight,
.staff-kpi-card.rating-card {
    border-color: #c7d2fe;
}


.staff-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    margin-bottom: 16px;
}


.staff-kpi-icon {
    width: 38px;
    height: 38px;

    border-radius: 11px;

    display: grid;
    place-items: center;
}


.staff-kpi-icon.green {
    background: #ecfdf5;
    color: #059669;
}


.staff-kpi-icon.blue {
    background: #eff6ff;
    color: #2563eb;
}


.staff-kpi-icon.amber {
    background: #fffbeb;
    color: #d97706;
}


.staff-kpi-icon.red {
    background: #fff1f2;
    color: #e11d48;
}


.staff-kpi-icon.purple {
    background: #f5f3ff;
    color: #7c3aed;
}


.staff-kpi-icon.yellow {
    background: #fefce8;
    color: #ca8a04;
}


.staff-kpi-badge {
    border-radius: 999px;

    padding: 5px 8px;

    background: #f8fafc;

    color: #64748b;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
    letter-spacing: .04em;
}


.staff-kpi-badge.attention {
    background: #fff7ed;
    color: #c2410c;
}


.staff-kpi-badge.success {
    background: #ecfdf5;
    color: #047857;
}


.staff-kpi-label {
    color: #64748b;

    font-size: 12px;
    font-weight: 700;
}


.staff-kpi-value {
    display: block;

    margin-top: 5px;

    color: #172033;

    font-size: 29px;
    line-height: 1.1;
}


.staff-kpi-card p {
    margin: 8px 0 0;

    color: #94a3b8;

    font-size: 11px;

    line-height: 1.45;
}


.staff-rating-value {
    display: flex;
    align-items: baseline;

    gap: 4px;

    margin-top: 4px;
}


.staff-rating-value strong {
    color: #172033;

    font-size: 29px;
}


.staff-rating-value span {
    color: #94a3b8;

    font-size: 12px;
}


.staff-stars {
    display: flex;

    gap: 2px;

    margin-top: 6px;
}


.staff-stars i,
.staff-review-stars i {
    color: #e2e8f0;
}


.staff-stars i.active,
.staff-review-stars i.active {
    color: #f59e0b;
}


/* =========================================================
   MINI STATS
========================================================= */

.staff-mini-grid {
    display: grid;

    grid-template-columns:
        repeat(
            5,
            minmax(0, 1fr)
        );

    gap: 12px;
}


.staff-mini-card {
    display: flex;
    align-items: center;

    gap: 11px;

    padding: 14px;
}


.staff-mini-icon {
    width: 35px;
    height: 35px;

    flex: 0 0 35px;

    border-radius: 10px;

    display: grid;
    place-items: center;

    background: #f0fdfa;
    color: #0f766e;
}


.staff-mini-card span {
    display: block;

    color: #64748b;

    font-size: 10px;
}


.staff-mini-card strong {
    display: block;

    margin-top: 2px;

    color: #172033;

    font-size: 17px;
}


/* =========================================================
   CHARTS
========================================================= */

.staff-chart-grid {
    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 16px;
}


.staff-chart-card {
    padding: 20px;
}


.staff-section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 15px;
}


.staff-section-head h3 {
    margin: 5px 0 3px;

    color: #172033;

    font-size: 16px;
}


.staff-section-head p {
    margin: 0;

    color: #94a3b8;

    font-size: 11px;
}


.staff-chart-period {
    border-radius: 8px;

    padding: 6px 8px;

    background: #f8fafc;

    color: #64748b;

    font-size: 10px;
    font-weight: 800;
}


.staff-chart {
    min-height: 300px;
}


.staff-rating-summary strong {
    color: #172033;

    font-size: 21px;
}


.staff-rating-summary span {
    color: #94a3b8;

    font-size: 10px;
}


/* =========================================================
   WORK QUEUES
========================================================= */

.staff-work-grid {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(0, 1fr)
        );

    gap: 16px;
}


.staff-list-card {
    padding: 18px;
}


.staff-view-all {
    color: #0f9f85;

    font-size: 10px;
    font-weight: 800;

    text-decoration: none;
}


.staff-list {
    display: flex;
    flex-direction: column;
}


.staff-list-item {
    display: flex;
    align-items: center;

    gap: 10px;

    border-top: 1px solid #eef2f7;

    padding: 11px 0;

    color: inherit;

    text-decoration: none;

    transition: background .15s ease;
}


.staff-list-item:first-child {
    border-top: 0;
}


.staff-list-item:hover {
    background: #fafcfc;
}


.staff-list-icon {
    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    border-radius: 9px;

    display: grid;
    place-items: center;

    font-size: 11px;
}


.staff-list-icon.dispute {
    background: #fff7ed;
    color: #c2410c;
}


.staff-list-icon.chat {
    background: #ecfdf5;
    color: #047857;
}


.staff-list-icon.contact {
    background: #eff6ff;
    color: #2563eb;
}


.staff-list-copy {
    flex: 1;

    min-width: 0;
}


.staff-list-copy strong,
.staff-list-copy span,
.staff-list-copy small {
    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


.staff-list-copy strong {
    color: #334155;

    font-size: 11px;
}


.staff-list-copy span {
    margin-top: 2px;

    color: #64748b;

    font-size: 10px;
}


.staff-list-copy small {
    margin-top: 2px;

    color: #94a3b8;

    font-size: 9px;
}


.staff-status-pill,
.staff-queue-number {
    border-radius: 999px;

    padding: 5px 7px;

    background: #f8fafc;

    color: #64748b;

    font-size: 8px;
    font-weight: 800;

    white-space: nowrap;
}


.status-open,
.status-new {
    background: #fff1f2;
    color: #be123c;
}


.status-under-review,
.status-in-progress {
    background: #fff7ed;
    color: #c2410c;
}


.status-awaiting-buyer,
.status-awaiting-seller {
    background: #eff6ff;
    color: #1d4ed8;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.staff-empty {
    display: flex;
    flex-direction: column;
    align-items: center;

    padding: 28px 10px;

    text-align: center;
}


.staff-empty i {
    margin-bottom: 8px;

    color: #94a3b8;

    font-size: 22px;
}


.staff-empty strong {
    color: #475569;

    font-size: 11px;
}


.staff-empty span {
    margin-top: 3px;

    color: #94a3b8;

    font-size: 9px;
}


.staff-empty.large {
    min-height: 200px;

    justify-content: center;
}


/* =========================================================
   FEEDBACK
========================================================= */

.staff-feedback-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 2fr)
        minmax(280px, 1fr);

    gap: 16px;
}


.staff-feedback-card,
.staff-rating-chart-card {
    padding: 19px;
}


.staff-review-list {
    display: flex;
    flex-direction: column;
}


.staff-review-item {
    border-top: 1px solid #eef2f7;

    padding: 13px 0;
}


.staff-review-item:first-child {
    border-top: 0;
}


.staff-review-top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;
}


.staff-review-user {
    display: flex;
    align-items: center;

    gap: 9px;
}


.staff-review-avatar {
    width: 33px;
    height: 33px;

    border-radius: 9px;

    display: grid;
    place-items: center;

    background: #ecfdf5;
    color: #047857;

    font-size: 11px;
    font-weight: 800;
}


.staff-review-user strong {
    display: block;

    color: #334155;

    font-size: 11px;
}


.staff-review-user small {
    display: block;

    margin-top: 2px;

    color: #94a3b8;

    font-size: 9px;
}


.staff-review-stars {
    display: flex;

    gap: 2px;

    font-size: 10px;
}


.staff-review-item p {
    margin: 9px 0 0 42px;

    color: #475569;

    font-size: 11px;

    line-height: 1.5;
}


.staff-review-item p.muted {
    color: #94a3b8;
    font-style: italic;
}


.staff-rating-donut {
    min-height: 270px;
}


.staff-rating-score {
    border-top: 1px solid #eef2f7;

    padding-top: 13px;

    text-align: center;
}


.staff-rating-score strong {
    display: block;

    color: #172033;

    font-size: 24px;
}


.staff-rating-score span {
    color: #94a3b8;

    font-size: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1200px) {

    .staff-kpi-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }


    .staff-mini-grid {
        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );
    }


    .staff-work-grid {
        grid-template-columns: 1fr;
    }

}


@media(max-width: 900px) {

    .staff-hero {
        flex-direction: column;
    }


    .staff-hero-actions {
        width: 100%;
        align-items: flex-start;
    }


    .staff-chart-grid,
    .staff-feedback-grid {
        grid-template-columns: 1fr;
    }

}


@media(max-width: 650px) {

    .staff-kpi-grid,
    .staff-mini-grid {
        grid-template-columns: 1fr;
    }


    .staff-priority-bar {
        align-items: flex-start;

        flex-direction: column;
    }


    .staff-priority-actions {
        width: 100%;
    }

}

</style>

@endpush


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const data =
            @json($staffChartData);


        /*
        |--------------------------------------------------------------------------
        | Resolved Workload
        |--------------------------------------------------------------------------
        */

        const resolvedTarget =
            document.querySelector(
                '#staffResolvedChart'
            );


        if (
            resolvedTarget
            &&
            window.ApexCharts
        ) {

            const resolvedChart =
                new ApexCharts(
                    resolvedTarget,
                    {

                        chart: {
                            type: 'area',
                            height: 300,
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            }
                        },


                        series: [

                            {
                                name: 'Support chats',
                                data:
                                    data
                                        .performance
                                        .resolved_chats
                            },

                            {
                                name: 'Disputes',
                                data:
                                    data
                                        .performance
                                        .resolved_disputes
                            }

                        ],


                        xaxis: {

                            categories:
                                data
                                    .performance
                                    .labels,

                            labels: {
                                rotate: -45
                            }

                        },


                        yaxis: {

                            min: 0,

                            forceNiceScale: true,

                            labels: {

                                formatter:
                                    function (value) {

                                        return Math.round(
                                            value
                                        );

                                    }

                            }

                        },


                        stroke: {

                            curve: 'smooth',

                            width: 2

                        },


                        fill: {

                            type: 'gradient',

                            gradient: {

                                shadeIntensity: 1,

                                opacityFrom: .26,

                                opacityTo: .04,

                                stops: [
                                    0,
                                    90,
                                    100
                                ]

                            }

                        },


                        dataLabels: {
                            enabled: false
                        },


                        grid: {

                            borderColor:
                                '#EEF2F7'

                        },


                        legend: {

                            position: 'top',

                            horizontalAlign: 'left'

                        },


                        tooltip: {

                            shared: true,

                            intersect: false

                        }

                    }
                );


            resolvedChart.render();

        }


        /*
        |--------------------------------------------------------------------------
        | Personal Rating Trend
        |--------------------------------------------------------------------------
        */

        const ratingTarget =
            document.querySelector(
                '#staffRatingTrendChart'
            );


        if (
            ratingTarget
            &&
            window.ApexCharts
        ) {

            const ratingChart =
                new ApexCharts(
                    ratingTarget,
                    {

                        chart: {

                            type: 'line',

                            height: 300,

                            toolbar: {
                                show: false
                            }

                        },


                        series: [

                            {

                                name:
                                    'Average rating',

                                data:
                                    data
                                        .performance
                                        .average_rating

                            }

                        ],


                        xaxis: {

                            categories:
                                data
                                    .performance
                                    .labels,

                            labels: {
                                rotate: -45
                            }

                        },


                        yaxis: {

                            min: 0,

                            max: 5,

                            tickAmount: 5,

                            labels: {

                                formatter:
                                    function (value) {

                                        return Number(
                                            value
                                        ).toFixed(
                                            1
                                        );

                                    }

                            }

                        },


                        stroke: {

                            curve: 'smooth',

                            width: 3

                        },


                        markers: {

                            size: 4

                        },


                        dataLabels: {
                            enabled: false
                        },


                        grid: {

                            borderColor:
                                '#EEF2F7'

                        },


                        tooltip: {

                            y: {

                                formatter:
                                    function (value) {

                                        if (
                                            value === null
                                            ||
                                            typeof value
                                            ===
                                            'undefined'
                                        ) {

                                            return 'No rating';

                                        }


                                        return Number(
                                            value
                                        ).toFixed(
                                            2
                                        )
                                        +
                                        ' / 5';

                                    }

                            }

                        }

                    }
                );


            ratingChart.render();

        }


        /*
        |--------------------------------------------------------------------------
        | Rating Distribution
        |--------------------------------------------------------------------------
        */

        const distributionTarget =
            document.querySelector(
                '#staffRatingDistributionChart'
            );


        if (
            distributionTarget
            &&
            window.ApexCharts
        ) {

            const totalRatings =
                data
                    .ratings
                    .series
                    .reduce(
                        function (
                            total,
                            value
                        ) {

                            return total + value;

                        },
                        0
                    );


            if (
                totalRatings > 0
            ) {

                const distributionChart =
                    new ApexCharts(
                        distributionTarget,
                        {

                            chart: {

                                type:
                                    'donut',

                                height:
                                    270

                            },


                            labels:
                                data
                                    .ratings
                                    .labels,


                            series:
                                data
                                    .ratings
                                    .series,


                            legend: {

                                position:
                                    'bottom'

                            },


                            dataLabels: {

                                enabled:
                                    false

                            },


                            plotOptions: {

                                pie: {

                                    donut: {

                                        size:
                                            '68%',

                                        labels: {

                                            show:
                                                true,

                                            total: {

                                                show:
                                                    true,

                                                label:
                                                    'Ratings',

                                                formatter:
                                                    function () {

                                                        return totalRatings;

                                                    }

                                            }

                                        }

                                    }

                                }

                            }

                        }
                    );


                distributionChart.render();

            } else {

                distributionTarget.innerHTML =
                    `
                    <div
                        style="
                            height:270px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            flex-direction:column;
                            color:#94a3b8;
                            font-size:12px;
                            text-align:center;
                        "
                    >
                        <i
                            class="fa-regular fa-star"
                            style="
                                font-size:26px;
                                margin-bottom:8px;
                            "
                        ></i>

                        No customer ratings yet
                    </div>
                    `;

            }

        }

    }
);

</script>

@endpush