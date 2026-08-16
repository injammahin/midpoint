@extends('admin.layouts.app')


@section('title', 'About Page')


@section('page-title', 'About Page')


@push('styles')

<style>

    @include(
        'admin.website-settings.partials.content-editor-styles'
    )

</style>

@endpush



@section('content')


@php

    $activeTab =
        request(
            'tab',
            'hero'
        );


    $stats =
        old(
            'stats',
            $content['stats']
            ??
            []
        );


    $principles =
        old(
            'principles',
            $content['principles']
            ??
            []
        );

@endphp



<div class="cp-page">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="cp-header">


        <div>

            <h2>
                About Page
            </h2>


            <p>

                Manage every public About page section.
                Stat cards and principles can be added or removed
                without changing the Blade template.

            </p>

        </div>



        <a
            href="{{ route('about') }}"
            target="_blank"
            rel="noopener"
            class="cp-public-link"
        >

            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            View About Page

        </a>


    </div>



    @if(session('success'))

        <div class="cp-alert success">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif



    @if($errors->any())

        <div class="cp-alert error">


            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                <strong>
                    Please fix the following:
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



    <form
        method="POST"
        action="{{
            route(
                'admin.website-settings.about-page.update'
            )
        }}"
    >

        @csrf

        @method('PUT')



        {{-- =====================================================
            TABS
        ====================================================== --}}

        <div class="cp-tabs">


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="hero"
            >
                <i class="fa-solid fa-heading"></i>
                SEO & Hero
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="stats"
            >
                <i class="fa-solid fa-chart-simple"></i>
                Facts & Stats
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="principles"
            >
                <i class="fa-solid fa-compass"></i>
                Principles
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="cta"
            >
                <i class="fa-solid fa-bullhorn"></i>
                CTA
            </button>


        </div>



        {{-- =====================================================
            SEO / HERO
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="hero"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>


                    <div>

                        <h3>
                            Search Engine Content
                        </h3>

                        <p>
                            Browser title and search description.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div class="cp-grid two">


                        <div class="cp-field">

                            <label>
                                Meta title
                            </label>

                            <input
                                type="text"
                                name="meta_title"
                                value="{{
                                    old(
                                        'meta_title',
                                        $page->meta_title
                                    )
                                }}"
                                maxlength="255"
                                required
                            >

                        </div>



                        <div class="cp-field">

                            <label>
                                Meta description
                            </label>

                            <textarea
                                name="meta_description"
                            >{{ old('meta_description', $page->meta_description) }}</textarea>

                        </div>


                    </div>


                </div>


            </div>



            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-font"></i>
                    </span>


                    <div>

                        <h3>
                            About Introduction
                        </h3>

                        <p>
                            First content visitors see.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div class="cp-grid">


                        <div class="cp-field">

                            <label>
                                Eyebrow
                            </label>

                            <input
                                type="text"
                                name="hero_eyebrow"
                                value="{{
                                    old(
                                        'hero_eyebrow',
                                        data_get(
                                            $content,
                                            'hero.eyebrow'
                                        )
                                    )
                                }}"
                                required
                            >

                        </div>



                        <div class="cp-field">

                            <label>
                                Main title
                            </label>

                            <textarea
                                name="hero_title"
                                required
                            >{{ old('hero_title', data_get($content, 'hero.title')) }}</textarea>

                        </div>



                        <div class="cp-field">

                            <label>
                                Description
                            </label>

                            <textarea
                                name="hero_description"
                                style="min-height:150px;"
                                required
                            >{{ old('hero_description', data_get($content, 'hero.description')) }}</textarea>

                        </div>


                    </div>


                </div>


            </div>


        </section>



        {{-- =====================================================
            STATS
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="stats"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-chart-column"></i>
                    </span>


                    <div>

                        <h3>
                            About Facts & Stats
                        </h3>

                        <p>
                            Add as many as needed, up to 12.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div
                        class="cp-repeater"
                        id="about-stats-repeater"
                    >


                        @foreach ($stats as $index => $stat)


                            <div class="cp-repeat-item">


                                <div class="cp-repeat-top">

                                    <strong>
                                        Stat Card
                                    </strong>


                                    <button
                                        type="button"
                                        class="cp-remove"
                                        data-remove-repeat
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>



                                <div class="cp-grid two">


                                    <div class="cp-field">

                                        <label>
                                            Label
                                        </label>

                                        <input
                                            type="text"
                                            name="stats[{{ $index }}][label]"
                                            value="{{ $stat['label'] ?? '' }}"
                                            required
                                        >

                                    </div>



                                    <div class="cp-field">

                                        <label>
                                            Main value
                                        </label>

                                        <input
                                            type="text"
                                            name="stats[{{ $index }}][value]"
                                            value="{{ $stat['value'] ?? '' }}"
                                            required
                                        >

                                    </div>


                                </div>



                                <div class="cp-field">

                                    <label>
                                        Supporting text
                                    </label>

                                    <input
                                        type="text"
                                        name="stats[{{ $index }}][description]"
                                        value="{{ $stat['description'] ?? '' }}"
                                    >

                                </div>


                            </div>


                        @endforeach


                    </div>



                    <button
                        type="button"
                        class="cp-add"
                        data-add-repeat="about-stat"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add stat card

                    </button>


                </div>


            </div>


        </section>



        {{-- =====================================================
            PRINCIPLES
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="principles"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-compass"></i>
                    </span>


                    <div>

                        <h3>
                            Principles
                        </h3>

                        <p>
                            The values MidPoint communicates publicly.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div
                        class="cp-field"
                        style="margin-bottom:15px;"
                    >

                        <label>
                            Section heading
                        </label>

                        <input
                            type="text"
                            name="principles_heading"
                            value="{{
                                old(
                                    'principles_heading',
                                    $content['principles_heading']
                                    ??
                                    ''
                                )
                            }}"
                            required
                        >

                    </div>



                    <div
                        class="cp-repeater"
                        id="about-principles-repeater"
                    >


                        @foreach ($principles as $index => $principle)


                            <div class="cp-repeat-item">


                                <div class="cp-repeat-top">

                                    <strong>
                                        Principle
                                    </strong>


                                    <button
                                        type="button"
                                        class="cp-remove"
                                        data-remove-repeat
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>



                                <div class="cp-grid two">


                                    <div class="cp-field">

                                        <label>
                                            Icon / emoji
                                        </label>

                                        <input
                                            type="text"
                                            name="principles[{{ $index }}][icon]"
                                            value="{{ $principle['icon'] ?? '' }}"
                                        >

                                    </div>



                                    <div class="cp-field">

                                        <label>
                                            Title
                                        </label>

                                        <input
                                            type="text"
                                            name="principles[{{ $index }}][title]"
                                            value="{{ $principle['title'] ?? '' }}"
                                            required
                                        >

                                    </div>


                                </div>



                                <div class="cp-field">

                                    <label>
                                        Description
                                    </label>

                                    <textarea
                                        name="principles[{{ $index }}][description]"
                                        required
                                    >{{ $principle['description'] ?? '' }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>



                    <button
                        type="button"
                        class="cp-add"
                        data-add-repeat="about-principle"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add principle

                    </button>


                </div>


            </div>


        </section>



        {{-- =====================================================
            CTA
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="cta"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </span>


                    <div>

                        <h3>
                            Bottom Call To Action
                        </h3>

                        <p>
                            Encourage the visitor to continue.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div class="cp-grid two">


                        <div class="cp-field">

                            <label>
                                Title
                            </label>

                            <input
                                type="text"
                                name="cta_title"
                                value="{{
                                    old(
                                        'cta_title',
                                        data_get(
                                            $content,
                                            'cta.title'
                                        )
                                    )
                                }}"
                                required
                            >

                        </div>



                        <div class="cp-field">

                            <label>
                                Button text
                            </label>

                            <input
                                type="text"
                                name="cta_button_text"
                                value="{{
                                    old(
                                        'cta_button_text',
                                        data_get(
                                            $content,
                                            'cta.button_text'
                                        )
                                    )
                                }}"
                                required
                            >

                        </div>



                        <div
                            class="cp-field"
                            style="grid-column:1/-1;"
                        >

                            <label>
                                Description
                            </label>

                            <textarea
                                name="cta_description"
                                required
                            >{{ old('cta_description', data_get($content, 'cta.description')) }}</textarea>

                        </div>



                        <div
                            class="cp-field"
                            style="grid-column:1/-1;"
                        >

                            <label>
                                Button URL
                            </label>

                            <input
                                type="text"
                                name="cta_button_url"
                                value="{{
                                    old(
                                        'cta_button_url',
                                        data_get(
                                            $content,
                                            'cta.button_url'
                                        )
                                    )
                                }}"
                                required
                            >

                        </div>


                    </div>


                </div>


            </div>


        </section>



        <div class="cp-actions">


            <button
                type="submit"
                class="cp-save"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Save About Page

            </button>


        </div>


    </form>


</div>



<template id="about-stat-template">

    <div class="cp-repeat-item">

        <div class="cp-repeat-top">

            <strong>
                Stat Card
            </strong>

            <button
                type="button"
                class="cp-remove"
                data-remove-repeat
            >
                <i class="fa-solid fa-trash"></i>
            </button>

        </div>

        <div class="cp-grid two">

            <div class="cp-field">

                <label>
                    Label
                </label>

                <input
                    type="text"
                    name="stats[__INDEX__][label]"
                    required
                >

            </div>

            <div class="cp-field">

                <label>
                    Main value
                </label>

                <input
                    type="text"
                    name="stats[__INDEX__][value]"
                    required
                >

            </div>

        </div>

        <div class="cp-field">

            <label>
                Supporting text
            </label>

            <input
                type="text"
                name="stats[__INDEX__][description]"
            >

        </div>

    </div>

</template>



<template id="about-principle-template">

    <div class="cp-repeat-item">

        <div class="cp-repeat-top">

            <strong>
                Principle
            </strong>

            <button
                type="button"
                class="cp-remove"
                data-remove-repeat
            >
                <i class="fa-solid fa-trash"></i>
            </button>

        </div>

        <div class="cp-grid two">

            <div class="cp-field">

                <label>
                    Icon / emoji
                </label>

                <input
                    type="text"
                    name="principles[__INDEX__][icon]"
                    value="✨"
                >

            </div>

            <div class="cp-field">

                <label>
                    Title
                </label>

                <input
                    type="text"
                    name="principles[__INDEX__][title]"
                    required
                >

            </div>

        </div>

        <div class="cp-field">

            <label>
                Description
            </label>

            <textarea
                name="principles[__INDEX__][description]"
                required
            ></textarea>

        </div>

    </div>

</template>


@endsection



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Tabs
        |--------------------------------------------------------------------------
        */

        const tabs =
            document.querySelectorAll(
                '[data-cp-tab]'
            );


        const panels =
            document.querySelectorAll(
                '[data-cp-panel]'
            );


        let currentTab =
            @json($activeTab);


        const allowedTabs = [
            'hero',
            'stats',
            'principles',
            'cta',
        ];


        if (
            !allowedTabs.includes(
                currentTab
            )
        ) {
            currentTab =
                'hero';
        }


        function showTab(
            name
        ) {
            tabs.forEach(
                function (tab) {

                    tab.classList.toggle(
                        'active',
                        tab.dataset.cpTab
                        ===
                        name
                    );

                }
            );


            panels.forEach(
                function (panel) {

                    panel.classList.toggle(
                        'active',
                        panel.dataset.cpPanel
                        ===
                        name
                    );

                }
            );


            const url =
                new URL(
                    window.location.href
                );


            url.searchParams.set(
                'tab',
                name
            );


            history.replaceState(
                {},
                '',
                url
            );
        }


        tabs.forEach(
            function (tab) {

                tab.addEventListener(
                    'click',
                    function () {

                        showTab(
                            tab.dataset.cpTab
                        );

                    }
                );

            }
        );


        showTab(
            currentTab
        );


        /*
        |--------------------------------------------------------------------------
        | Repeaters
        |--------------------------------------------------------------------------
        */

        let statIndex =
            {{ count($stats) + 10 }};


        let principleIndex =
            {{ count($principles) + 10 }};


        function appendTemplate(
            templateId,
            targetId,
            index
        ) {
            const template =
                document.getElementById(
                    templateId
                );


            const target =
                document.getElementById(
                    targetId
                );


            let html =
                template.innerHTML;


            html =
                html.replaceAll(
                    '__INDEX__',
                    index
                );


            target.insertAdjacentHTML(
                'beforeend',
                html
            );
        }


        document
            .querySelector(
                '[data-add-repeat="about-stat"]'
            )
            .addEventListener(
                'click',
                function () {

                    appendTemplate(
                        'about-stat-template',
                        'about-stats-repeater',
                        statIndex++
                    );

                }
            );


        document
            .querySelector(
                '[data-add-repeat="about-principle"]'
            )
            .addEventListener(
                'click',
                function () {

                    appendTemplate(
                        'about-principle-template',
                        'about-principles-repeater',
                        principleIndex++
                    );

                }
            );


        document.addEventListener(
            'click',
            function (event) {

                const button =
                    event.target.closest(
                        '[data-remove-repeat]'
                    );


                if (
                    !button
                ) {
                    return;
                }


                const item =
                    button.closest(
                        '.cp-repeat-item'
                    );


                const repeater =
                    item.parentElement;


                if (
                    repeater
                        .querySelectorAll(
                            '.cp-repeat-item'
                        )
                        .length
                    <=
                    1
                ) {

                    alert(
                        'At least one item is required.'
                    );

                    return;
                }


                item.remove();

            }
        );

    }
);

</script>

@endpush