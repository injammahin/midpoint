@extends('admin.layouts.app')


@section('title', 'Home Page Management')


@section('page-title', 'Home Page')


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Main Wrapper
    |--------------------------------------------------------------------------
    */

    .home-admin-wrap {
        display: grid;
        gap: 18px;
    }


    /*
    |--------------------------------------------------------------------------
    | Page Header
    |--------------------------------------------------------------------------
    */

    .home-admin-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }


    .home-admin-head h2 {
        margin: 0;

        color:
            var(--admin-heading);

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 24px;
    }


    .home-admin-head p {
        margin: 6px 0 0;

        color:
            var(--admin-muted);

        font-size: 13px;
        line-height: 1.6;
    }


    .home-admin-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 40px;

        padding:
            0 14px;

        border:
            1px solid
            var(--admin-border);

        border-radius: 10px;

        color:
            var(--admin-heading);

        background:
            var(--admin-surface);

        text-decoration: none;

        font-size: 12px;
        font-weight: 700;

        box-shadow:
            var(--admin-shadow);

        transition:
            .18s ease;
    }


    .home-admin-view:hover {
        border-color:
            var(--admin-accent);

        color:
            var(--admin-accent);
    }


    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .home-admin-alert {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        padding:
            13px 14px;

        border:
            1px solid
            color-mix(
                in srgb,
                var(--admin-accent) 35%,
                var(--admin-border)
            );

        border-radius: 11px;

        color:
            var(--admin-text);

        background:
            var(--admin-accent-soft);

        font-size: 12px;
    }


    .home-admin-alert.error {
        border-color:
            color-mix(
                in srgb,
                var(--admin-danger) 35%,
                var(--admin-border)
            );

        background:
            color-mix(
                in srgb,
                var(--admin-danger) 10%,
                var(--admin-surface)
            );
    }


    .home-admin-alert ul {
        margin:
            6px 0 0 17px;

        padding: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Tab Bar
    |--------------------------------------------------------------------------
    */

    .home-admin-tabs {
        display: flex;

        gap: 8px;

        overflow-x: auto;

        padding: 8px;

        border:
            1px solid
            var(--admin-border);

        border-radius: 13px;

        background:
            var(--admin-surface);

        box-shadow:
            var(--admin-shadow);

        scrollbar-width: thin;
    }


    .home-admin-tab {
        flex: 0 0 auto;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 42px;

        padding:
            0 14px;

        border:
            1px solid
            transparent;

        border-radius: 9px;

        color:
            var(--admin-muted);

        background:
            transparent;

        cursor: pointer;

        font: inherit;
        font-size: 12px;
        font-weight: 700;

        transition:
            .18s ease;
    }


    .home-admin-tab:hover {
        color:
            var(--admin-heading);

        background:
            var(--admin-surface-hover);
    }


    .home-admin-tab.active {
        border-color:
            color-mix(
                in srgb,
                var(--admin-accent) 30%,
                var(--admin-border)
            );

        color:
            var(--admin-accent);

        background:
            var(--admin-accent-soft);
    }


    /*
    |--------------------------------------------------------------------------
    | Tab Panels
    |--------------------------------------------------------------------------
    */

    .home-admin-panel {
        display: none;
    }


    .home-admin-panel.active {
        display: block;
    }


    /*
    |--------------------------------------------------------------------------
    | Form Grid
    |--------------------------------------------------------------------------
    */

    .home-admin-form-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 16px;
    }


    /*
    |--------------------------------------------------------------------------
    | Cards
    |--------------------------------------------------------------------------
    */

    .home-admin-card {
        border:
            1px solid
            var(--admin-border);

        border-radius: 14px;

        background:
            var(--admin-surface);

        box-shadow:
            var(--admin-shadow);

        overflow: hidden;
    }


    .home-admin-card.full {
        grid-column:
            1 / -1;
    }


    .home-admin-card-head {
        display: flex;
        align-items: center;

        gap: 11px;

        padding:
            15px 16px;

        border-bottom:
            1px solid
            var(--admin-border-soft);
    }


    .home-admin-card-head > span {
        display: grid;

        width: 36px;
        height: 36px;

        flex: 0 0 36px;

        place-items: center;

        border-radius: 10px;

        color:
            var(--admin-accent);

        background:
            var(--admin-accent-soft);
    }


    .home-admin-card-head h3 {
        margin: 0;

        color:
            var(--admin-heading);

        font-size: 14px;
    }


    .home-admin-card-head p {
        margin:
            3px 0 0;

        color:
            var(--admin-muted);

        font-size: 11px;
    }


    .home-admin-card-body {
        padding: 16px;
    }


    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .home-admin-fields {
        display: grid;
        gap: 14px;
    }


    .home-admin-fields.two {
        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
    }


    .home-admin-fields.three {
        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );
    }


    .home-admin-field {
        display: grid;

        gap: 7px;

        min-width: 0;
    }


    .home-admin-field label {
        color:
            var(--admin-heading);

        font-size: 11px;
        font-weight: 700;
    }


    .home-admin-field small {
        color:
            var(--admin-muted);

        font-size: 10px;
        line-height: 1.5;
    }


    .home-admin-field input,
    .home-admin-field textarea,
    .home-admin-field select {
        width: 100%;

        border:
            1px solid
            var(--admin-border);

        border-radius: 9px;

        outline: none;

        color:
            var(--admin-text);

        background:
            var(--admin-surface-soft);

        font: inherit;
        font-size: 12px;

        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }


    .home-admin-field input,
    .home-admin-field select {
        min-height: 41px;

        padding:
            0 11px;
    }


    .home-admin-field textarea {
        min-height: 96px;

        resize: vertical;

        padding:
            10px 11px;

        line-height: 1.55;
    }


    .home-admin-field input:focus,
    .home-admin-field textarea:focus,
    .home-admin-field select:focus {
        border-color:
            var(--admin-accent);

        box-shadow:
            0 0 0 3px
            color-mix(
                in srgb,
                var(--admin-accent) 14%,
                transparent
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Save Actions
    |--------------------------------------------------------------------------
    */

    .home-admin-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        flex-wrap: wrap;

        gap: 10px;

        margin-top: 16px;
    }


    .home-admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 40px;

        padding:
            0 14px;

        border:
            1px solid
            transparent;

        border-radius: 9px;

        cursor: pointer;

        font: inherit;

        font-size: 11px;
        font-weight: 800;

        text-decoration: none;

        transition:
            .18s ease;
    }


    .home-admin-btn.primary {
        color: #FFFFFF;

        background:
            var(--admin-accent-strong);
    }


    .home-admin-btn.primary:hover {
        filter:
            brightness(1.05);
    }


    .home-admin-btn.secondary {
        border-color:
            var(--admin-border);

        color:
            var(--admin-heading);

        background:
            var(--admin-surface);
    }


    .home-admin-btn.secondary:hover {
        border-color:
            var(--admin-accent);

        color:
            var(--admin-accent);
    }


    .home-admin-btn.danger {
        border-color:
            color-mix(
                in srgb,
                var(--admin-danger) 35%,
                var(--admin-border)
            );

        color:
            var(--admin-danger);

        background:
            transparent;
    }


    /*
    |--------------------------------------------------------------------------
    | Step Cards
    |--------------------------------------------------------------------------
    */

    .home-admin-step-card,
    .home-admin-why-card {
        display: grid;

        gap: 13px;

        padding: 14px;

        border:
            1px solid
            var(--admin-border-soft);

        border-radius: 11px;

        background:
            var(--admin-surface-soft);
    }


    .home-admin-step-number {
        display: grid;

        width: 31px;
        height: 31px;

        place-items: center;

        border-radius: 9px;

        color:
            var(--admin-accent);

        background:
            var(--admin-accent-soft);

        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Testimonials Layout
    |--------------------------------------------------------------------------
    */

    .home-testimonial-layout {
        display: grid;

        grid-template-columns:
            minmax(
                280px,
                .85fr
            )
            minmax(
                0,
                1.35fr
            );

        gap: 16px;
    }


    .home-testimonial-editor {
        position: sticky;

        top: 84px;

        align-self: start;
    }


    .home-testimonial-list {
        display: grid;
        gap: 10px;
    }


    .home-testimonial-item {
        display: grid;

        grid-template-columns:
            auto
            minmax(0, 1fr)
            auto;

        gap: 12px;

        align-items: start;

        padding: 14px;

        border:
            1px solid
            var(--admin-border);

        border-radius: 12px;

        background:
            var(--admin-surface-soft);
    }


    .home-testimonial-avatar {
        display: grid;

        width: 42px;
        height: 42px;

        place-items: center;

        border-radius: 12px;

        color: #FFFFFF;

        font-size: 11px;
        font-weight: 800;
    }


    .home-testimonial-item h4 {
        margin: 0;

        color:
            var(--admin-heading);

        font-size: 12px;
    }


    .home-testimonial-meta {
        margin-top: 2px;

        color:
            var(--admin-muted);

        font-size: 10px;
    }


    .home-testimonial-stars {
        margin-top: 7px;

        color: #F5B301;

        font-size: 11px;

        letter-spacing: 1px;
    }


    .home-testimonial-text {
        margin:
            7px 0 0;

        color:
            var(--admin-text);

        font-size: 11px;

        line-height: 1.55;
    }


    .home-testimonial-status {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        margin-top: 8px;

        color:
            var(--admin-muted);

        font-size: 9px;
        font-weight: 700;
    }


    .home-testimonial-status::before {
        content: '';

        width: 6px;
        height: 6px;

        border-radius: 50%;

        background:
            var(--admin-muted);
    }


    .home-testimonial-status.active {
        color:
            var(--admin-accent);
    }


    .home-testimonial-status.active::before {
        background:
            var(--admin-accent);
    }


    .home-testimonial-actions {
        display: flex;
        flex-direction: column;

        gap: 7px;
    }


    .home-testimonial-actions form {
        margin: 0;
    }


    .home-icon-btn {
        display: grid;

        width: 34px;
        height: 34px;

        place-items: center;

        border:
            1px solid
            var(--admin-border);

        border-radius: 8px;

        color:
            var(--admin-muted);

        background:
            var(--admin-surface);

        cursor: pointer;

        transition:
            .18s ease;
    }


    .home-icon-btn:hover {
        border-color:
            var(--admin-accent);

        color:
            var(--admin-accent);
    }


    .home-icon-btn.danger:hover {
        border-color:
            var(--admin-danger);

        color:
            var(--admin-danger);
    }


    /*
    |--------------------------------------------------------------------------
    | Checkbox
    |--------------------------------------------------------------------------
    */

    .home-admin-check {
        display: flex;
        align-items: center;

        gap: 9px;

        color:
            var(--admin-heading);

        font-size: 11px;
        font-weight: 700;

        cursor: pointer;
    }


    .home-admin-check input {
        width: 17px;
        height: 17px;

        accent-color:
            var(--admin-accent);
    }


    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    .home-admin-empty {
        padding:
            35px 16px;

        border:
            1px dashed
            var(--admin-border);

        border-radius: 12px;

        color:
            var(--admin-muted);

        text-align: center;

        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 980px) {

        .home-admin-form-grid,
        .home-testimonial-layout {
            grid-template-columns:
                1fr;
        }


        .home-testimonial-editor {
            position: static;
        }

    }


    @media(max-width: 720px) {

        .home-admin-head {
            flex-direction: column;
        }


        .home-admin-view {
            width: 100%;
        }


        .home-admin-fields.two,
        .home-admin-fields.three {
            grid-template-columns:
                1fr;
        }


        .home-testimonial-item {
            grid-template-columns:
                auto
                minmax(0, 1fr);
        }


        .home-testimonial-actions {
            grid-column:
                1 / -1;

            flex-direction: row;

            justify-content:
                flex-end;
        }


        .home-admin-actions {
            flex-direction: column;
        }


        .home-admin-btn {
            width: 100%;
        }

    }

</style>

@endpush



@section('content')


@php

    /*
    |--------------------------------------------------------------------------
    | Active Tab
    |--------------------------------------------------------------------------
    */

    $requestedTab =
        request(
            'tab',
            'hero'
        );


    $allowedTabs = [
        'hero',
        'steps',
        'why',
        'testimonials',
        'other',
    ];


    $activeTab =
        in_array(
            $requestedTab,
            $allowedTabs,
            true
        )
            ? $requestedTab
            : 'hero';

@endphp



<div class="home-admin-wrap">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="home-admin-head">


        <div>

            <h2>
                Home Page Management
            </h2>


            <p>
                Manage the public homepage section by section.
                Changes are reflected on the public website immediately.
            </p>

        </div>



        <a
            href="{{ route('home') }}"
            target="_blank"
            rel="noopener"
            class="home-admin-view"
        >

            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            View homepage

        </a>


    </div>



    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}

    @if(session('success'))

        <div class="home-admin-alert">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif



    {{-- =========================================================
        VALIDATION ERRORS
    ========================================================== --}}

    @if($errors->any())

        <div class="home-admin-alert error">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                <strong>
                    Please correct the following:
                </strong>


                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif



    {{-- =========================================================
        TABS
    ========================================================== --}}

    <div
        class="home-admin-tabs"
        role="tablist"
        aria-label="Homepage settings sections"
    >


        <button
            type="button"
            class="home-admin-tab"
            data-home-tab="hero"
        >

            <i class="fa-solid fa-house"></i>

            Hero & Stats

        </button>



        <button
            type="button"
            class="home-admin-tab"
            data-home-tab="steps"
        >

            <i class="fa-solid fa-list-ol"></i>

            Three Steps

        </button>



        <button
            type="button"
            class="home-admin-tab"
            data-home-tab="why"
        >

            <i class="fa-solid fa-shield-halved"></i>

            Why MidPoint

        </button>



        <button
            type="button"
            class="home-admin-tab"
            data-home-tab="testimonials"
        >

            <i class="fa-solid fa-star"></i>

            Testimonials

        </button>



        <button
            type="button"
            class="home-admin-tab"
            data-home-tab="other"
        >

            <i class="fa-solid fa-layer-group"></i>

            Other Content

        </button>


    </div>



    {{-- =========================================================
        HERO & STATS
    ========================================================== --}}

    <section
        class="home-admin-panel"
        data-home-panel="hero"
    >


        <form
            method="POST"
            action="{{ route('admin.website-settings.home-page.hero.update') }}"
        >

            @csrf

            @method('PUT')


            <div class="home-admin-form-grid">


                {{-- Hero Text --}}

                <div class="home-admin-card full">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-solid fa-heading"></i>
                        </span>


                        <div>

                            <h3>
                                Hero Content
                            </h3>

                            <p>
                                Main headline and description shown at the top.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields two">


                            <div
                                class="home-admin-field"
                                style="grid-column:1/-1;"
                            >

                                <label for="hero_badge">
                                    Badge text
                                </label>

                                <input
                                    id="hero_badge"
                                    type="text"
                                    name="hero_badge"
                                    value="{{ old('hero_badge', $settings->hero_badge) }}"
                                    maxlength="180"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_title_before">
                                    Title before highlighted text
                                </label>

                                <input
                                    id="hero_title_before"
                                    type="text"
                                    name="hero_title_before"
                                    value="{{ old('hero_title_before', $settings->hero_title_before) }}"
                                    maxlength="180"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_title_highlight">
                                    Highlighted title text
                                </label>

                                <input
                                    id="hero_title_highlight"
                                    type="text"
                                    name="hero_title_highlight"
                                    value="{{ old('hero_title_highlight', $settings->hero_title_highlight) }}"
                                    maxlength="120"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_title_after">
                                    Title ending
                                </label>

                                <input
                                    id="hero_title_after"
                                    type="text"
                                    name="hero_title_after"
                                    value="{{ old('hero_title_after', $settings->hero_title_after) }}"
                                    maxlength="30"
                                >

                                <small>
                                    Example: a full stop ".".
                                </small>

                            </div>



                            <div
                                class="home-admin-field"
                                style="grid-column:1/-1;"
                            >

                                <label for="hero_description">
                                    Description
                                </label>

                                <textarea
                                    id="hero_description"
                                    name="hero_description"
                                    maxlength="1200"
                                    required
                                >{{ old('hero_description', $settings->hero_description) }}</textarea>

                            </div>


                        </div>


                    </div>


                </div>



                {{-- Buttons --}}

                <div class="home-admin-card">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-solid fa-arrow-pointer"></i>
                        </span>


                        <div>

                            <h3>
                                Hero Buttons
                            </h3>

                            <p>
                                Configure the two hero call-to-action buttons.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields">


                            <div class="home-admin-field">

                                <label for="hero_primary_button_text">
                                    Primary button text
                                </label>

                                <input
                                    id="hero_primary_button_text"
                                    type="text"
                                    name="hero_primary_button_text"
                                    value="{{ old('hero_primary_button_text', $settings->hero_primary_button_text) }}"
                                    maxlength="80"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_primary_button_url">
                                    Primary button URL
                                </label>

                                <input
                                    id="hero_primary_button_url"
                                    type="text"
                                    name="hero_primary_button_url"
                                    value="{{ old('hero_primary_button_url', $settings->hero_primary_button_url) }}"
                                    maxlength="500"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_secondary_button_text">
                                    Secondary button text
                                </label>

                                <input
                                    id="hero_secondary_button_text"
                                    type="text"
                                    name="hero_secondary_button_text"
                                    value="{{ old('hero_secondary_button_text', $settings->hero_secondary_button_text) }}"
                                    maxlength="80"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="hero_secondary_button_url">
                                    Secondary button URL
                                </label>

                                <input
                                    id="hero_secondary_button_url"
                                    type="text"
                                    name="hero_secondary_button_url"
                                    value="{{ old('hero_secondary_button_url', $settings->hero_secondary_button_url) }}"
                                    maxlength="500"
                                    required
                                >

                            </div>


                        </div>


                    </div>


                </div>



                {{-- Hero Stats --}}

                <div class="home-admin-card">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-solid fa-chart-simple"></i>
                        </span>


                        <div>

                            <h3>
                                Hero Statistics
                            </h3>

                            <p>
                                Three statistics displayed below the hero actions.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields">


                            @for($i = 1; $i <= 3; $i++)

                                @php

                                    $word =
                                        [
                                            'one',
                                            'two',
                                            'three',
                                        ][$i - 1];

                                @endphp


                                <div class="home-admin-fields two">


                                    <div class="home-admin-field">

                                        <label>
                                            Stat {{ $i }} value
                                        </label>

                                        <input
                                            type="text"
                                            name="stat_{{ $word }}_value"
                                            value="{{ old('stat_'.$word.'_value', $settings->{'stat_'.$word.'_value'}) }}"
                                            maxlength="50"
                                            required
                                        >

                                    </div>



                                    <div class="home-admin-field">

                                        <label>
                                            Stat {{ $i }} label
                                        </label>

                                        <input
                                            type="text"
                                            name="stat_{{ $word }}_label"
                                            value="{{ old('stat_'.$word.'_label', $settings->{'stat_'.$word.'_label'}) }}"
                                            maxlength="100"
                                            required
                                        >

                                    </div>


                                </div>


                            @endfor


                        </div>


                    </div>


                </div>


            </div>



            <div class="home-admin-actions">

                <button
                    type="submit"
                    class="home-admin-btn primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Hero & Stats

                </button>

            </div>


        </form>


    </section>



    {{-- =========================================================
        THREE STEPS
    ========================================================== --}}

    <section
        class="home-admin-panel"
        data-home-panel="steps"
    >


        <form
            method="POST"
            action="{{ route('admin.website-settings.home-page.steps.update') }}"
        >

            @csrf

            @method('PUT')


            <div class="home-admin-card">


                <div class="home-admin-card-head">

                    <span>
                        <i class="fa-solid fa-list-ol"></i>
                    </span>


                    <div>

                        <h3>
                            Three Simple Steps
                        </h3>

                        <p>
                            Manage the heading and each transaction step.
                        </p>

                    </div>

                </div>



                <div class="home-admin-card-body">


                    <div class="home-admin-fields two">


                        <div class="home-admin-field">

                            <label for="steps_eyebrow">
                                Eyebrow
                            </label>

                            <input
                                id="steps_eyebrow"
                                type="text"
                                name="steps_eyebrow"
                                value="{{ old('steps_eyebrow', $settings->steps_eyebrow) }}"
                                maxlength="100"
                                required
                            >

                        </div>



                        <div class="home-admin-field">

                            <label for="steps_title">
                                Section title
                            </label>

                            <input
                                id="steps_title"
                                type="text"
                                name="steps_title"
                                value="{{ old('steps_title', $settings->steps_title) }}"
                                maxlength="255"
                                required
                            >

                        </div>



                        <div
                            class="home-admin-field"
                            style="grid-column:1/-1;"
                        >

                            <label for="steps_description">
                                Section description
                            </label>

                            <textarea
                                id="steps_description"
                                name="steps_description"
                                maxlength="1000"
                                required
                            >{{ old('steps_description', $settings->steps_description) }}</textarea>

                        </div>


                    </div>



                    <div
                        class="home-admin-fields three"
                        style="margin-top:16px;"
                    >


                        @foreach ([
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ] as $number => $word)


                            <div class="home-admin-step-card">


                                <span class="home-admin-step-number">
                                    {{ $number }}
                                </span>



                                <div class="home-admin-field">

                                    <label>
                                        Step {{ $number }} title
                                    </label>

                                    <input
                                        type="text"
                                        name="step_{{ $word }}_title"
                                        value="{{ old('step_'.$word.'_title', $settings->{'step_'.$word.'_title'}) }}"
                                        maxlength="150"
                                        required
                                    >

                                </div>



                                <div class="home-admin-field">

                                    <label>
                                        Step {{ $number }} description
                                    </label>

                                    <textarea
                                        name="step_{{ $word }}_description"
                                        maxlength="1000"
                                        required
                                    >{{ old('step_'.$word.'_description', $settings->{'step_'.$word.'_description'}) }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>


                </div>


            </div>



            <div class="home-admin-actions">

                <button
                    type="submit"
                    class="home-admin-btn primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Three Steps

                </button>

            </div>


        </form>


    </section>



    {{-- =========================================================
        WHY MIDPOINT
    ========================================================== --}}

    <section
        class="home-admin-panel"
        data-home-panel="why"
    >


        <form
            method="POST"
            action="{{ route('admin.website-settings.home-page.why.update') }}"
        >

            @csrf

            @method('PUT')


            <div class="home-admin-card">


                <div class="home-admin-card-head">

                    <span>
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>


                    <div>

                        <h3>
                            Why MidPoint
                        </h3>

                        <p>
                            Manage the four platform benefits shown to visitors.
                        </p>

                    </div>

                </div>



                <div class="home-admin-card-body">


                    <div class="home-admin-fields two">


                        <div class="home-admin-field">

                            <label for="why_eyebrow">
                                Eyebrow
                            </label>

                            <input
                                id="why_eyebrow"
                                type="text"
                                name="why_eyebrow"
                                value="{{ old('why_eyebrow', $settings->why_eyebrow) }}"
                                maxlength="100"
                                required
                            >

                        </div>



                        <div class="home-admin-field">

                            <label for="why_title">
                                Section title
                            </label>

                            <input
                                id="why_title"
                                type="text"
                                name="why_title"
                                value="{{ old('why_title', $settings->why_title) }}"
                                maxlength="255"
                                required
                            >

                        </div>


                    </div>



                    <div
                        class="home-admin-fields two"
                        style="margin-top:16px;"
                    >


                        @foreach ([
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                            4 => 'four',
                        ] as $number => $word)


                            <div class="home-admin-why-card">


                                <div class="home-admin-fields two">


                                    <div class="home-admin-field">

                                        <label>
                                            Icon / emoji
                                        </label>

                                        <input
                                            type="text"
                                            name="why_{{ $word }}_icon"
                                            value="{{ old('why_'.$word.'_icon', $settings->{'why_'.$word.'_icon'}) }}"
                                            maxlength="30"
                                            required
                                        >

                                    </div>



                                    <div class="home-admin-field">

                                        <label>
                                            Benefit {{ $number }} title
                                        </label>

                                        <input
                                            type="text"
                                            name="why_{{ $word }}_title"
                                            value="{{ old('why_'.$word.'_title', $settings->{'why_'.$word.'_title'}) }}"
                                            maxlength="150"
                                            required
                                        >

                                    </div>


                                </div>



                                <div class="home-admin-field">

                                    <label>
                                        Description
                                    </label>

                                    <textarea
                                        name="why_{{ $word }}_description"
                                        maxlength="1000"
                                        required
                                    >{{ old('why_'.$word.'_description', $settings->{'why_'.$word.'_description'}) }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>


                </div>


            </div>



            <div class="home-admin-actions">

                <button
                    type="submit"
                    class="home-admin-btn primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Why MidPoint

                </button>

            </div>


        </form>


    </section>



    {{-- =========================================================
        TESTIMONIALS
    ========================================================== --}}

    <section
        class="home-admin-panel"
        data-home-panel="testimonials"
    >


        {{-- Testimonials Section Heading --}}

        <div
            class="home-admin-card"
            style="margin-bottom:16px;"
        >


            <div class="home-admin-card-head">

                <span>
                    <i class="fa-solid fa-heading"></i>
                </span>


                <div>

                    <h3>
                        Testimonials Section Heading
                    </h3>

                    <p>
                        Manage the heading shown above the testimonial slider.
                    </p>

                </div>

            </div>



            <div class="home-admin-card-body">


                <form
                    method="POST"
                    action="{{ route('admin.website-settings.home-page.testimonials.section.update') }}"
                >

                    @csrf

                    @method('PUT')


                    <div class="home-admin-fields two">


                        <div class="home-admin-field">

                            <label for="testimonials_eyebrow">
                                Eyebrow
                            </label>

                            <input
                                id="testimonials_eyebrow"
                                type="text"
                                name="testimonials_eyebrow"
                                value="{{ old('testimonials_eyebrow', $settings->testimonials_eyebrow) }}"
                                maxlength="100"
                                required
                            >

                        </div>



                        <div class="home-admin-field">

                            <label for="testimonials_title">
                                Section title
                            </label>

                            <input
                                id="testimonials_title"
                                type="text"
                                name="testimonials_title"
                                value="{{ old('testimonials_title', $settings->testimonials_title) }}"
                                maxlength="255"
                                required
                            >

                        </div>


                    </div>



                    <div class="home-admin-actions">

                        <button
                            type="submit"
                            class="home-admin-btn primary"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Heading

                        </button>

                    </div>


                </form>


            </div>


        </div>



        <div class="home-testimonial-layout">


            {{-- =====================================================
                ADD / EDIT TESTIMONIAL
            ====================================================== --}}

            <div
                class="
                    home-admin-card
                    home-testimonial-editor
                "
            >


                <div class="home-admin-card-head">

                    <span>
                        <i class="fa-solid fa-quote-left"></i>
                    </span>


                    <div>

                        <h3 id="testimonial-form-title">
                            Add Testimonial
                        </h3>

                        <p>
                            Create a review or edit an existing testimonial.
                        </p>

                    </div>

                </div>



                <div class="home-admin-card-body">


                    <form
                        method="POST"

                        action="{{ route('admin.website-settings.home-page.testimonials.store') }}"

                        id="home-testimonial-form"

                        data-store-action="{{ route('admin.website-settings.home-page.testimonials.store') }}"

                        data-update-base="{{ url('/admin/website-settings/home-page/testimonials') }}"
                    >

                        @csrf


                        <input
                            type="hidden"
                            name="_method"
                            value="POST"
                            id="testimonial-method"
                        >



                        <div class="home-admin-fields">


                            <div class="home-admin-field">

                                <label for="testimonial-reviewer-name">
                                    Reviewer name
                                </label>

                                <input
                                    id="testimonial-reviewer-name"
                                    type="text"
                                    name="reviewer_name"
                                    maxlength="120"
                                    placeholder="e.g. Adaeze Bello"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="testimonial-reviewer-meta">
                                    Reviewer details
                                </label>

                                <input
                                    id="testimonial-reviewer-meta"
                                    type="text"
                                    name="reviewer_meta"
                                    maxlength="180"
                                    placeholder="e.g. Buyer, Lagos"
                                    required
                                >

                                <small>
                                    Business, role or location shown beneath the name.
                                </small>

                            </div>



                            <div class="home-admin-field">

                                <label for="testimonial-review-text">
                                    Review
                                </label>

                                <textarea
                                    id="testimonial-review-text"
                                    name="review_text"
                                    maxlength="1600"
                                    placeholder="Write the testimonial..."
                                    required
                                ></textarea>

                            </div>



                            <div class="home-admin-fields two">


                                <div class="home-admin-field">

                                    <label for="testimonial-rating">
                                        Rating
                                    </label>

                                    <select
                                        id="testimonial-rating"
                                        name="rating"
                                        required
                                    >

                                        @for($rating = 5; $rating >= 1; $rating--)

                                            <option value="{{ $rating }}">

                                                {{ $rating }}

                                                star{{ $rating > 1 ? 's' : '' }}

                                            </option>

                                        @endfor

                                    </select>

                                </div>



                                <div class="home-admin-field">

                                    <label for="testimonial-sort-order">
                                        Sort order
                                    </label>

                                    <input
                                        id="testimonial-sort-order"
                                        type="number"
                                        name="sort_order"
                                        value="0"
                                        min="0"
                                        max="999999"
                                        required
                                    >

                                    <small>
                                        Smaller numbers appear first.
                                    </small>

                                </div>


                            </div>



                            <div class="home-admin-fields two">


                                <div class="home-admin-field">

                                    <label for="testimonial-avatar-initials">
                                        Avatar initials
                                    </label>

                                    <input
                                        id="testimonial-avatar-initials"
                                        type="text"
                                        name="avatar_initials"
                                        maxlength="4"
                                        placeholder="AB"
                                    >

                                </div>



                                <div class="home-admin-field">

                                    <label for="testimonial-avatar-color">
                                        Avatar color
                                    </label>

                                    <input
                                        id="testimonial-avatar-color"
                                        type="color"
                                        name="avatar_color"
                                        value="#7A5AF8"
                                        required
                                    >

                                </div>


                            </div>



                            <label class="home-admin-check">

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    id="testimonial-is-active"
                                    value="1"
                                    checked
                                >

                                Show this testimonial on the homepage

                            </label>


                        </div>



                        <div class="home-admin-actions">


                            <button
                                type="button"
                                class="home-admin-btn secondary"
                                id="testimonial-cancel-edit"
                                hidden
                            >

                                <i class="fa-solid fa-xmark"></i>

                                Cancel Edit

                            </button>



                            <button
                                type="submit"
                                class="home-admin-btn primary"
                                id="testimonial-submit-text"
                            >

                                <i class="fa-solid fa-plus"></i>

                                Add Testimonial

                            </button>


                        </div>


                    </form>


                </div>


            </div>



            {{-- =====================================================
                TESTIMONIAL LIST
            ====================================================== --}}

            <div class="home-admin-card">


                <div class="home-admin-card-head">

                    <span>
                        <i class="fa-solid fa-comments"></i>
                    </span>


                    <div>

                        <h3>
                            All Testimonials
                        </h3>


                        <p>

                            {{ $testimonials->count() }}

                            testimonial{{ $testimonials->count() === 1 ? '' : 's' }}

                            saved. Only active testimonials appear publicly.

                        </p>

                    </div>

                </div>



                <div class="home-admin-card-body">


                    @if($testimonials->count())


                        <div class="home-testimonial-list">


                            @foreach ($testimonials as $testimonial)


                                @php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Testimonial Avatar
                                    |--------------------------------------------------------------------------
                                    */

                                    $testimonialInitials =
                                        $testimonial->avatar_initials;


                                    if (
                                        blank(
                                            $testimonialInitials
                                        )
                                    ) {

                                        $testimonialInitials =
                                            collect(
                                                preg_split(
                                                    '/\s+/',
                                                    trim(
                                                        $testimonial->reviewer_name
                                                    )
                                                )
                                            )
                                            ->filter()
                                            ->take(2)
                                            ->map(
                                                fn ($word) =>
                                                    strtoupper(
                                                        substr(
                                                            $word,
                                                            0,
                                                            1
                                                        )
                                                    )
                                            )
                                            ->implode('');

                                    }

                                @endphp



                                <article class="home-testimonial-item">


                                    <div
                                        class="home-testimonial-avatar"
                                        style="background: {{ $testimonial->avatar_color }};"
                                    >

                                        {{ $testimonialInitials ?: 'MP' }}

                                    </div>



                                    <div>


                                        <h4>
                                            {{ $testimonial->reviewer_name }}
                                        </h4>


                                        <div class="home-testimonial-meta">

                                            {{ $testimonial->reviewer_meta }}

                                        </div>



                                        <div class="home-testimonial-stars">

                                            {{ str_repeat('★', $testimonial->rating) }}

                                            {{ str_repeat('☆', 5 - $testimonial->rating) }}

                                        </div>



                                        <p class="home-testimonial-text">

                                            {{ $testimonial->review_text }}

                                        </p>



                                        <div
                                            class="
                                                home-testimonial-status
                                                {{ $testimonial->is_active ? 'active' : '' }}
                                            "
                                        >

                                            {{ $testimonial->is_active ? 'Visible' : 'Hidden' }}

                                            ·

                                            Sort order:

                                            {{ $testimonial->sort_order }}

                                        </div>


                                    </div>



                                    <div class="home-testimonial-actions">


                                        {{-- Edit --}}

                                        <button
                                            type="button"

                                            class="
                                                home-icon-btn
                                                testimonial-edit-btn
                                            "

                                            title="Edit testimonial"

                                            data-testimonial="{{ json_encode([
                                                'id' => $testimonial->id,
                                                'reviewer_name' => $testimonial->reviewer_name,
                                                'reviewer_meta' => $testimonial->reviewer_meta,
                                                'review_text' => $testimonial->review_text,
                                                'rating' => $testimonial->rating,
                                                'avatar_initials' => $testimonial->avatar_initials,
                                                'avatar_color' => $testimonial->avatar_color,
                                                'sort_order' => $testimonial->sort_order,
                                                'is_active' => (bool) $testimonial->is_active,
                                            ]) }}"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                        </button>



                                        {{-- Toggle Visibility --}}

                                        <form
                                            method="POST"
                                            action="{{ route('admin.website-settings.home-page.testimonials.toggle', $testimonial) }}"
                                        >

                                            @csrf

                                            @method('PATCH')


                                            <button
                                                type="submit"
                                                class="home-icon-btn"
                                                title="{{ $testimonial->is_active ? 'Hide testimonial' : 'Show testimonial' }}"
                                            >

                                                <i
                                                    class="
                                                        fa-solid
                                                        {{ $testimonial->is_active ? 'fa-eye-slash' : 'fa-eye' }}
                                                    "
                                                ></i>

                                            </button>


                                        </form>



                                        {{-- Delete --}}

                                        <form
                                            method="POST"
                                            action="{{ route('admin.website-settings.home-page.testimonials.destroy', $testimonial) }}"
                                            onsubmit="return confirm('Delete this testimonial permanently?');"
                                        >

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="home-icon-btn danger"
                                                title="Delete testimonial"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                            </button>


                                        </form>


                                    </div>


                                </article>


                            @endforeach


                        </div>


                    @else


                        <div class="home-admin-empty">

                            <i
                                class="fa-regular fa-comments"
                                style="
                                    display:block;
                                    margin-bottom:10px;
                                    font-size:24px;
                                "
                            ></i>

                            No testimonials have been added yet.

                        </div>


                    @endif


                </div>


            </div>


        </div>


    </section>



    {{-- =========================================================
        OTHER CONTENT
    ========================================================== --}}

    <section
        class="home-admin-panel"
        data-home-panel="other"
    >


        <form
            method="POST"
            action="{{ route('admin.website-settings.home-page.other.update') }}"
        >

            @csrf

            @method('PUT')


            <div class="home-admin-form-grid">


                {{-- Featured Businesses --}}

                <div class="home-admin-card">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-solid fa-store"></i>
                        </span>


                        <div>

                            <h3>
                                Featured Businesses
                            </h3>

                            <p>
                                Business cards remain dynamic and random.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields">


                            <div class="home-admin-field">

                                <label for="featured_eyebrow">
                                    Eyebrow
                                </label>

                                <input
                                    id="featured_eyebrow"
                                    type="text"
                                    name="featured_eyebrow"
                                    value="{{ old('featured_eyebrow', $settings->featured_eyebrow) }}"
                                    maxlength="100"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="featured_title">
                                    Section title
                                </label>

                                <input
                                    id="featured_title"
                                    type="text"
                                    name="featured_title"
                                    value="{{ old('featured_title', $settings->featured_title) }}"
                                    maxlength="255"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="featured_view_all_text">
                                    View all button text
                                </label>

                                <input
                                    id="featured_view_all_text"
                                    type="text"
                                    name="featured_view_all_text"
                                    value="{{ old('featured_view_all_text', $settings->featured_view_all_text) }}"
                                    maxlength="80"
                                    required
                                >

                            </div>


                        </div>


                    </div>


                </div>



                {{-- FAQ --}}

                <div class="home-admin-card">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-regular fa-circle-question"></i>
                        </span>


                        <div>

                            <h3>
                                FAQ Section
                            </h3>

                            <p>
                                FAQ questions remain managed from the FAQ Page menu.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields">


                            <div class="home-admin-field">

                                <label for="faq_eyebrow">
                                    Eyebrow
                                </label>

                                <input
                                    id="faq_eyebrow"
                                    type="text"
                                    name="faq_eyebrow"
                                    value="{{ old('faq_eyebrow', $settings->faq_eyebrow) }}"
                                    maxlength="100"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="faq_title">
                                    Section title
                                </label>

                                <input
                                    id="faq_title"
                                    type="text"
                                    name="faq_title"
                                    value="{{ old('faq_title', $settings->faq_title) }}"
                                    maxlength="255"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="faq_view_all_text">
                                    View all button text
                                </label>

                                <input
                                    id="faq_view_all_text"
                                    type="text"
                                    name="faq_view_all_text"
                                    value="{{ old('faq_view_all_text', $settings->faq_view_all_text) }}"
                                    maxlength="80"
                                    required
                                >

                            </div>


                        </div>


                    </div>


                </div>



                {{-- Final CTA --}}

                <div class="home-admin-card full">


                    <div class="home-admin-card-head">

                        <span>
                            <i class="fa-solid fa-bullhorn"></i>
                        </span>


                        <div>

                            <h3>
                                Final Call To Action
                            </h3>

                            <p>
                                Manage the promotional section at the bottom of the homepage.
                            </p>

                        </div>

                    </div>



                    <div class="home-admin-card-body">


                        <div class="home-admin-fields two">


                            <div class="home-admin-field">

                                <label for="final_cta_title">
                                    Title
                                </label>

                                <input
                                    id="final_cta_title"
                                    type="text"
                                    name="final_cta_title"
                                    value="{{ old('final_cta_title', $settings->final_cta_title) }}"
                                    maxlength="255"
                                    required
                                >

                            </div>



                            <div class="home-admin-field">

                                <label for="final_cta_button_text">
                                    Button text
                                </label>

                                <input
                                    id="final_cta_button_text"
                                    type="text"
                                    name="final_cta_button_text"
                                    value="{{ old('final_cta_button_text', $settings->final_cta_button_text) }}"
                                    maxlength="80"
                                    required
                                >

                            </div>



                            <div
                                class="home-admin-field"
                                style="grid-column:1/-1;"
                            >

                                <label for="final_cta_description">
                                    Description
                                </label>

                                <textarea
                                    id="final_cta_description"
                                    name="final_cta_description"
                                    maxlength="1200"
                                    required
                                >{{ old('final_cta_description', $settings->final_cta_description) }}</textarea>

                            </div>



                            <div
                                class="home-admin-field"
                                style="grid-column:1/-1;"
                            >

                                <label for="final_cta_button_url">
                                    Button URL
                                </label>

                                <input
                                    id="final_cta_button_url"
                                    type="text"
                                    name="final_cta_button_url"
                                    value="{{ old('final_cta_button_url', $settings->final_cta_button_url) }}"
                                    maxlength="500"
                                    required
                                >

                            </div>


                        </div>


                    </div>


                </div>


            </div>



            <div class="home-admin-actions">

                <button
                    type="submit"
                    class="home-admin-btn primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Other Content

                </button>

            </div>


        </form>


    </section>


</div>


@endsection



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Tab Elements
        |--------------------------------------------------------------------------
        */

        const tabs =
            Array.from(
                document.querySelectorAll(
                    '[data-home-tab]'
                )
            );


        const panels =
            Array.from(
                document.querySelectorAll(
                    '[data-home-panel]'
                )
            );


        const initialTab =
            @json($activeTab);


        /*
        |--------------------------------------------------------------------------
        | Activate Tab
        |--------------------------------------------------------------------------
        */

        function activateTab(
            name,
            updateUrl = true
        ) {

            tabs.forEach(
                function (tab) {

                    const active =
                        tab.dataset.homeTab
                        ===
                        name;


                    tab.classList.toggle(
                        'active',
                        active
                    );


                    tab.setAttribute(
                        'aria-selected',
                        active
                            ? 'true'
                            : 'false'
                    );

                }
            );


            panels.forEach(
                function (panel) {

                    panel.classList.toggle(
                        'active',
                        panel.dataset.homePanel
                        ===
                        name
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Update URL Without Reloading
            |--------------------------------------------------------------------------
            */

            if (
                updateUrl
            ) {

                const url =
                    new URL(
                        window.location.href
                    );


                url.searchParams.set(
                    'tab',
                    name
                );


                window.history.replaceState(
                    {},
                    '',
                    url.toString()
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Register Tabs
        |--------------------------------------------------------------------------
        */

        tabs.forEach(
            function (tab) {

                tab.addEventListener(
                    'click',
                    function () {

                        activateTab(
                            tab.dataset.homeTab
                        );

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Initial Tab
        |--------------------------------------------------------------------------
        */

        activateTab(
            initialTab,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | Testimonial Form
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'home-testimonial-form'
            );


        if (
            !form
        ) {
            return;
        }


        const formTitle =
            document.getElementById(
                'testimonial-form-title'
            );


        const method =
            document.getElementById(
                'testimonial-method'
            );


        const submitButton =
            document.getElementById(
                'testimonial-submit-text'
            );


        const cancelEdit =
            document.getElementById(
                'testimonial-cancel-edit'
            );


        const fields = {

            reviewer_name:
                document.getElementById(
                    'testimonial-reviewer-name'
                ),

            reviewer_meta:
                document.getElementById(
                    'testimonial-reviewer-meta'
                ),

            review_text:
                document.getElementById(
                    'testimonial-review-text'
                ),

            rating:
                document.getElementById(
                    'testimonial-rating'
                ),

            avatar_initials:
                document.getElementById(
                    'testimonial-avatar-initials'
                ),

            avatar_color:
                document.getElementById(
                    'testimonial-avatar-color'
                ),

            sort_order:
                document.getElementById(
                    'testimonial-sort-order'
                ),

            is_active:
                document.getElementById(
                    'testimonial-is-active'
                ),

        };


        /*
        |--------------------------------------------------------------------------
        | Reset Testimonial Editor
        |--------------------------------------------------------------------------
        */

        function resetEditor()
        {

            form.action =
                form.dataset.storeAction;


            method.value =
                'POST';


            form.reset();


            fields.rating.value =
                '5';


            fields.avatar_color.value =
                '#7A5AF8';


            fields.sort_order.value =
                '0';


            fields.is_active.checked =
                true;


            formTitle.textContent =
                'Add Testimonial';


            submitButton.innerHTML =
                '<i class="fa-solid fa-plus"></i> Add Testimonial';


            cancelEdit.hidden =
                true;

        }


        /*
        |--------------------------------------------------------------------------
        | Edit Testimonial Buttons
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.testimonial-edit-btn'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            let data;


                            try {

                                data =
                                    JSON.parse(
                                        button.dataset.testimonial
                                    );

                            }
                            catch (
                                error
                            ) {

                                console.error(
                                    'Unable to parse testimonial data.',
                                    error
                                );

                                return;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Change To Update URL
                            |--------------------------------------------------------------------------
                            */

                            form.action =
                                form.dataset.updateBase
                                +
                                '/'
                                +
                                data.id;


                            method.value =
                                'PUT';


                            /*
                            |--------------------------------------------------------------------------
                            | Fill Fields
                            |--------------------------------------------------------------------------
                            */

                            fields.reviewer_name.value =
                                data.reviewer_name
                                ||
                                '';


                            fields.reviewer_meta.value =
                                data.reviewer_meta
                                ||
                                '';


                            fields.review_text.value =
                                data.review_text
                                ||
                                '';


                            fields.rating.value =
                                String(
                                    data.rating
                                    ||
                                    5
                                );


                            fields.avatar_initials.value =
                                data.avatar_initials
                                ||
                                '';


                            fields.avatar_color.value =
                                data.avatar_color
                                ||
                                '#7A5AF8';


                            fields.sort_order.value =
                                String(
                                    data.sort_order
                                    ??
                                    0
                                );


                            fields.is_active.checked =
                                Boolean(
                                    data.is_active
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Editor State
                            |--------------------------------------------------------------------------
                            */

                            formTitle.textContent =
                                'Edit Testimonial';


                            submitButton.innerHTML =
                                '<i class="fa-solid fa-floppy-disk"></i> Save Testimonial';


                            cancelEdit.hidden =
                                false;


                            /*
                            |--------------------------------------------------------------------------
                            | Make Sure Testimonials Tab Is Active
                            |--------------------------------------------------------------------------
                            */

                            activateTab(
                                'testimonials'
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Scroll To Editor
                            |--------------------------------------------------------------------------
                            */

                            form.scrollIntoView({

                                behavior:
                                    'smooth',

                                block:
                                    'start',

                            });

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Cancel Editing
        |--------------------------------------------------------------------------
        */

        cancelEdit.addEventListener(
            'click',
            function () {

                resetEditor();

            }
        );

    }
);

</script>

@endpush