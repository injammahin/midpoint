@extends('admin.layouts.app')

@section('title', 'Live Support Settings')
@section('page-title', 'Live Support Settings')


@section('content')

<div class="alss-page">

    {{-- =====================================================
        PAGE HEADER
    ====================================================== --}}
    <div class="alss-page-head">

        <div>

            <div class="alss-eyebrow">

                <i class="fa-solid fa-headset"></i>

                LIVE SUPPORT

            </div>

            <h2>
                Support Settings
            </h2>

            <p>
                Configure availability, operating hours,
                temporary closures and support agents.
            </p>

        </div>


        <a
            href="{{ route('admin.live-support.index') }}"
            class="alss-back"
        >
            <i class="fa-solid fa-arrow-left"></i>

            Live Support
        </a>

    </div>



    {{-- =====================================================
        SUCCESS
    ====================================================== --}}
    @if(session('success'))

        <div class="alss-alert-success">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif



    {{-- =====================================================
        VALIDATION
    ====================================================== --}}
    @if($errors->any())

        <div class="alss-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <strong>
                    Please correct the following:
                </strong>

                <ul>

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif



    <div class="alss-layout">


        {{-- =================================================
            LEFT COLUMN
        ================================================== --}}
        <div class="alss-main">


            {{-- =============================================
                AVAILABILITY
            ============================================== --}}
            <form
                method="POST"
                action="{{ route('admin.live-support.settings.update') }}"
                class="admin-card alss-card"
            >

                @csrf
                @method('PUT')


                <div class="alss-card-head">

                    <div class="alss-card-icon">

                        <i class="fa-regular fa-clock"></i>

                    </div>


                    <div>

                        <h3>
                            Availability Schedule
                        </h3>

                        <p>
                            Control when customers can start
                            new live support conversations.
                        </p>

                    </div>

                </div>



                {{-- Enabled --}}
                <label class="alss-toggle-row">

                    <div>

                        <strong>
                            Live Support
                        </strong>

                        <span>
                            Allow customers to create support requests.
                        </span>

                    </div>


                    <span class="alss-switch">

                        <input
                            type="checkbox"
                            name="enabled"
                            value="1"
                            {{ old('enabled', $settings->enabled) ? 'checked' : '' }}
                        >

                        <span></span>

                    </span>

                </label>



                {{-- Times --}}
                <div class="alss-grid-2">

                    <div class="alss-field">

                        <label for="opens_at">
                            Opening time
                        </label>

                        <div class="alss-input-wrap">

                            <i class="fa-regular fa-clock"></i>

                            <input
                                id="opens_at"
                                type="time"
                                name="opens_at"
                                value="{{ old(
                                    'opens_at',
                                    substr($settings->opens_at, 0, 5)
                                ) }}"
                                required
                            >

                        </div>

                    </div>


                    <div class="alss-field">

                        <label for="closes_at">
                            Closing time
                        </label>

                        <div class="alss-input-wrap">

                            <i class="fa-regular fa-clock"></i>

                            <input
                                id="closes_at"
                                type="time"
                                name="closes_at"
                                value="{{ old(
                                    'closes_at',
                                    substr($settings->closes_at, 0, 5)
                                ) }}"
                                required
                            >

                        </div>

                    </div>

                </div>



                {{-- Days --}}
                @php

                    $days = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        7 => 'Sunday',
                    ];

                    $activeDays =
                        old(
                            'active_days',
                            $settings->active_days ?? []
                        );

                @endphp


                <div class="alss-field alss-field-space">

                    <label>
                        Available days
                    </label>


                    <div class="alss-days">

                        @foreach($days as $number => $day)

                            <label class="alss-day">

                                <input
                                    type="checkbox"
                                    name="active_days[]"
                                    value="{{ $number }}"
                                    {{
                                        in_array(
                                            $number,
                                            $activeDays
                                        )
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span class="alss-day-check">

                                    <i class="fa-solid fa-check"></i>

                                </span>

                                <strong>
                                    {{ $day }}
                                </strong>

                            </label>

                        @endforeach

                    </div>

                </div>



                {{-- Timezone --}}
                <div class="alss-field alss-field-space">

                    <label for="timezone">
                        Timezone
                    </label>

                    <div class="alss-input-wrap">

                        <i class="fa-solid fa-earth-africa"></i>

                        <input
                            id="timezone"
                            type="text"
                            name="timezone"
                            value="{{ old(
                                'timezone',
                                $settings->timezone
                            ) }}"
                            required
                        >

                    </div>

                    <small>
                        Support schedules are evaluated using this timezone.
                    </small>

                </div>



                {{-- Messages --}}
                <div class="alss-field alss-field-space">

                    <label for="welcome_message">
                        Welcome message
                    </label>

                    <textarea
                        id="welcome_message"
                        name="welcome_message"
                        rows="4"
                        placeholder="Message shown when a customer starts a chat..."
                    >{{ old(
                        'welcome_message',
                        $settings->welcome_message
                    ) }}</textarea>

                </div>


                <div class="alss-field alss-field-space">

                    <label for="offline_message">
                        Offline message
                    </label>

                    <textarea
                        id="offline_message"
                        name="offline_message"
                        rows="4"
                        placeholder="Message shown outside support hours..."
                    >{{ old(
                        'offline_message',
                        $settings->offline_message
                    ) }}</textarea>

                </div>


                <div class="alss-field alss-field-space">

                    <label for="queue_message">
                        Queue message
                    </label>

                    <textarea
                        id="queue_message"
                        name="queue_message"
                        rows="4"
                        placeholder="Message shown while agents are busy..."
                    >{{ old(
                        'queue_message',
                        $settings->queue_message
                    ) }}</textarea>

                </div>



                <div class="alss-actions">

                    <button
                        type="submit"
                        class="alss-primary-button"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save settings

                    </button>

                </div>

            </form>



            {{-- =============================================
                TEMPORARY CLOSURES
            ============================================== --}}
            <div class="admin-card alss-card">

                <div class="alss-card-head">

                    <div class="alss-card-icon warning">

                        <i class="fa-regular fa-calendar-xmark"></i>

                    </div>


                    <div>

                        <h3>
                            Temporary Closures
                        </h3>

                        <p>
                            Close support temporarily for holidays,
                            maintenance or exceptional circumstances.
                        </p>

                    </div>

                </div>



                <form
                    method="POST"
                    action="{{ route(
                        'admin.live-support.blackouts.store'
                    ) }}"
                >

                    @csrf


                    <div class="alss-grid-2">

                        <div class="alss-field">

                            <label for="starts_at">
                                From
                            </label>

                            <input
                                id="starts_at"
                                type="datetime-local"
                                name="starts_at"
                                required
                            >

                        </div>


                        <div class="alss-field">

                            <label for="ends_at">
                                Until
                            </label>

                            <input
                                id="ends_at"
                                type="datetime-local"
                                name="ends_at"
                                required
                            >

                        </div>

                    </div>


                    <div class="alss-field alss-field-space">

                        <label for="reason">
                            Reason
                        </label>

                        <input
                            id="reason"
                            type="text"
                            name="reason"
                            placeholder="e.g. Public holiday"
                        >

                    </div>


                    <button
                        type="submit"
                        class="alss-secondary-button"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add offline period

                    </button>

                </form>



                @if($blackouts->count())

                    <div class="alss-blackout-list">

                        @foreach($blackouts as $blackout)

                            <div class="alss-blackout">

                                <div class="alss-blackout-icon">

                                    <i class="fa-regular fa-calendar-xmark"></i>

                                </div>


                                <div class="alss-blackout-info">

                                    <strong>

                                        {{
                                            $blackout
                                                ->starts_at
                                                ->timezone(
                                                    $settings->timezone
                                                )
                                                ->format(
                                                    'M j, Y g:i A'
                                                )
                                        }}

                                        <span>
                                            →
                                        </span>

                                        {{
                                            $blackout
                                                ->ends_at
                                                ->timezone(
                                                    $settings->timezone
                                                )
                                                ->format(
                                                    'M j, Y g:i A'
                                                )
                                        }}

                                    </strong>

                                    <small>

                                        {{
                                            $blackout->reason
                                            ?: 'Temporary closure'
                                        }}

                                    </small>

                                </div>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.live-support.blackouts.destroy',
                                        $blackout
                                    ) }}"
                                >

                                    @csrf
                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="alss-delete"
                                        title="Delete"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                    </button>

                                </form>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>



        {{-- =================================================
            SUPPORT AGENTS
        ================================================== --}}
        <aside class="admin-card alss-card alss-agents">

            <div class="alss-card-head">

                <div class="alss-card-icon">

                    <i class="fa-solid fa-user-headset"></i>

                </div>


                <div>

                    <h3>
                        Support Agents
                    </h3>

                    <p>
                        Administrators permitted to receive
                        live support conversations.
                    </p>

                </div>

            </div>



            <div class="alss-agent-list">

                @forelse($admins as $admin)

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.live-support.agents.update',
                            $admin
                        ) }}"
                        class="alss-agent"
                    >

                        @csrf
                        @method('PUT')


                        <div class="alss-agent-head">

                            <div class="alss-agent-avatar">

                                {{
                                    strtoupper(
                                        substr(
                                            $admin->name,
                                            0,
                                            1
                                        )
                                    )
                                }}

                            </div>


                            <div>

                                <strong>
                                    {{ $admin->name }}
                                </strong>

                                <span>
                                    {{ $admin->email }}
                                </span>

                            </div>

                        </div>



                        <label class="alss-agent-enabled">

                            <input
                                type="checkbox"
                                name="is_enabled"
                                value="1"
                                {{
                                    $admin
                                        ->supportAgentProfile
                                        ?->is_enabled
                                        ? 'checked'
                                        : ''
                                }}
                            >

                            <span>
                                Support agent
                            </span>

                        </label>



                        <div class="alss-field">

                            <label>
                                Maximum concurrent chats
                            </label>

                            <input
                                type="number"
                                name="max_active_chats"
                                min="1"
                                max="20"
                                value="{{
                                    $admin
                                        ->supportAgentProfile
                                        ?->max_active_chats
                                    ?? 3
                                }}"
                            >

                        </div>


                        <button
                            type="submit"
                            class="alss-agent-update"
                        >

                            <i class="fa-solid fa-check"></i>

                            Update agent

                        </button>

                    </form>

                @empty

                    <div class="alss-empty">
                        No administrator accounts available.
                    </div>

                @endforelse

            </div>

        </aside>

    </div>

</div>

@endsection