@extends('admin.layouts.app')


@section('title', 'How It Works Page')


@section('page-title', 'How It Works Page')


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


    $sellerSteps =
        old(
            'seller_steps',
            $content['seller_steps']
            ??
            []
        );


    $buyerSteps =
        old(
            'buyer_steps',
            $content['buyer_steps']
            ??
            []
        );


    $deliveryCards =
        old(
            'delivery_cards',
            $content['delivery_cards']
            ??
            []
        );

@endphp



<div class="cp-page">


    <div class="cp-header">


        <div>

            <h2>
                How It Works Page
            </h2>


            <p>

                Manage the complete buyer and seller journey,
                delivery information and final call to action.

            </p>

        </div>



        <a
            href="{{ route('how-it-works') }}"
            target="_blank"
            rel="noopener"
            class="cp-public-link"
        >

            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            View How It Works

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
                'admin.website-settings.how-it-works-page.update'
            )
        }}"
    >

        @csrf

        @method('PUT')



        <div class="cp-tabs">


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="hero"
            >
                <i class="fa-solid fa-heading"></i>
                SEO & Header
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="seller"
            >
                <i class="fa-solid fa-store"></i>
                Seller Journey
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="buyer"
            >
                <i class="fa-solid fa-user"></i>
                Buyer Journey
            </button>


            <button
                type="button"
                class="cp-tab"
                data-cp-tab="delivery"
            >
                <i class="fa-solid fa-truck"></i>
                Delivery
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
            HEADER
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
                            SEO
                        </h3>

                        <p>
                            Search and browser information.
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
                        <i class="fa-solid fa-route"></i>
                    </span>


                    <div>

                        <h3>
                            Page Introduction
                        </h3>

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
                                Title
                            </label>

                            <input
                                type="text"
                                name="hero_title"
                                value="{{
                                    old(
                                        'hero_title',
                                        data_get(
                                            $content,
                                            'hero.title'
                                        )
                                    )
                                }}"
                                required
                            >

                        </div>



                        <div class="cp-field">

                            <label>
                                Description
                            </label>

                            <textarea
                                name="hero_description"
                                required
                            >{{ old('hero_description', data_get($content, 'hero.description')) }}</textarea>

                        </div>


                    </div>


                </div>


            </div>


        </section>



        {{-- =====================================================
            SELLERS
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="seller"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-store"></i>
                    </span>


                    <div>

                        <h3>
                            Seller Journey
                        </h3>

                        <p>
                            Add, remove or edit seller steps.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div
                        class="cp-field"
                        style="margin-bottom:15px;"
                    >

                        <label>
                            Seller badge
                        </label>

                        <input
                            type="text"
                            name="seller_badge"
                            value="{{
                                old(
                                    'seller_badge',
                                    $content['seller_badge']
                                    ??
                                    ''
                                )
                            }}"
                            required
                        >

                    </div>



                    <div
                        class="cp-repeater"
                        id="seller-step-repeater"
                    >


                        @foreach ($sellerSteps as $index => $step)


                            <div class="cp-repeat-item">


                                <div class="cp-repeat-top">

                                    <strong>
                                        Seller Step {{ $loop->iteration }}
                                    </strong>


                                    <button
                                        type="button"
                                        class="cp-remove"
                                        data-remove-repeat
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>



                                <div class="cp-field">

                                    <label>
                                        Title
                                    </label>

                                    <input
                                        type="text"
                                        name="seller_steps[{{ $index }}][title]"
                                        value="{{ $step['title'] ?? '' }}"
                                        required
                                    >

                                </div>



                                <div class="cp-field">

                                    <label>
                                        Description
                                    </label>

                                    <textarea
                                        name="seller_steps[{{ $index }}][description]"
                                        required
                                    >{{ $step['description'] ?? '' }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>



                    <button
                        type="button"
                        class="cp-add"
                        data-add-repeat="seller-step"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add seller step
                    </button>


                </div>


            </div>


        </section>



        {{-- =====================================================
            BUYERS
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="buyer"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>


                    <div>

                        <h3>
                            Buyer Journey
                        </h3>

                        <p>
                            Add, remove or edit buyer steps.
                        </p>

                    </div>

                </div>



                <div class="cp-card-body">


                    <div
                        class="cp-field"
                        style="margin-bottom:15px;"
                    >

                        <label>
                            Buyer badge
                        </label>

                        <input
                            type="text"
                            name="buyer_badge"
                            value="{{
                                old(
                                    'buyer_badge',
                                    $content['buyer_badge']
                                    ??
                                    ''
                                )
                            }}"
                            required
                        >

                    </div>



                    <div
                        class="cp-repeater"
                        id="buyer-step-repeater"
                    >


                        @foreach ($buyerSteps as $index => $step)


                            <div class="cp-repeat-item">


                                <div class="cp-repeat-top">

                                    <strong>
                                        Buyer Step {{ $loop->iteration }}
                                    </strong>


                                    <button
                                        type="button"
                                        class="cp-remove"
                                        data-remove-repeat
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>



                                <div class="cp-field">

                                    <label>
                                        Title
                                    </label>

                                    <input
                                        type="text"
                                        name="buyer_steps[{{ $index }}][title]"
                                        value="{{ $step['title'] ?? '' }}"
                                        required
                                    >

                                </div>



                                <div class="cp-field">

                                    <label>
                                        Description
                                    </label>

                                    <textarea
                                        name="buyer_steps[{{ $index }}][description]"
                                        required
                                    >{{ $step['description'] ?? '' }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>



                    <button
                        type="button"
                        class="cp-add"
                        data-add-repeat="buyer-step"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add buyer step
                    </button>


                </div>


            </div>


        </section>



        {{-- =====================================================
            DELIVERY
        ====================================================== --}}

        <section
            class="cp-panel"
            data-cp-panel="delivery"
        >


            <div class="cp-card">


                <div class="cp-card-head">

                    <span class="cp-card-icon">
                        <i class="fa-solid fa-truck"></i>
                    </span>


                    <div>

                        <h3>
                            Delivery Information
                        </h3>

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
                            name="delivery_heading"
                            value="{{
                                old(
                                    'delivery_heading',
                                    $content['delivery_heading']
                                    ??
                                    ''
                                )
                            }}"
                            required
                        >

                    </div>



                    <div
                        class="cp-repeater"
                        id="delivery-card-repeater"
                    >


                        @foreach ($deliveryCards as $index => $card)


                            <div class="cp-repeat-item">


                                <div class="cp-repeat-top">

                                    <strong>
                                        Delivery Card
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
                                            Icon
                                        </label>

                                        <input
                                            type="text"
                                            name="delivery_cards[{{ $index }}][icon]"
                                            value="{{ $card['icon'] ?? '' }}"
                                        >

                                    </div>



                                    <div class="cp-field">

                                        <label>
                                            Optional badge
                                        </label>

                                        <input
                                            type="text"
                                            name="delivery_cards[{{ $index }}][badge]"
                                            value="{{ $card['badge'] ?? '' }}"
                                            placeholder="Coming soon"
                                        >

                                    </div>


                                </div>



                                <div class="cp-field">

                                    <label>
                                        Title
                                    </label>

                                    <input
                                        type="text"
                                        name="delivery_cards[{{ $index }}][title]"
                                        value="{{ $card['title'] ?? '' }}"
                                        required
                                    >

                                </div>



                                <div class="cp-field">

                                    <label>
                                        Description
                                    </label>

                                    <textarea
                                        name="delivery_cards[{{ $index }}][description]"
                                        required
                                    >{{ $card['description'] ?? '' }}</textarea>

                                </div>


                            </div>


                        @endforeach


                    </div>



                    <button
                        type="button"
                        class="cp-add"
                        data-add-repeat="delivery-card"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add delivery card
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
                            Final Call To Action
                        </h3>

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

                Save How It Works Page

            </button>


        </div>


    </form>


</div>



{{-- =========================================================
    REPEATER TEMPLATES
========================================================== --}}

<template id="journey-step-template">

    <div class="cp-repeat-item">

        <div class="cp-repeat-top">

            <strong>
                Journey Step
            </strong>

            <button
                type="button"
                class="cp-remove"
                data-remove-repeat
            >
                <i class="fa-solid fa-trash"></i>
            </button>

        </div>

        <div class="cp-field">

            <label>
                Title
            </label>

            <input
                type="text"
                name="__TYPE__[__INDEX__][title]"
                required
            >

        </div>

        <div class="cp-field">

            <label>
                Description
            </label>

            <textarea
                name="__TYPE__[__INDEX__][description]"
                required
            ></textarea>

        </div>

    </div>

</template>



<template id="delivery-card-template">

    <div class="cp-repeat-item">

        <div class="cp-repeat-top">

            <strong>
                Delivery Card
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
                    Icon
                </label>

                <input
                    type="text"
                    name="delivery_cards[__INDEX__][icon]"
                    value="📦"
                >

            </div>

            <div class="cp-field">

                <label>
                    Badge
                </label>

                <input
                    type="text"
                    name="delivery_cards[__INDEX__][badge]"
                >

            </div>

        </div>

        <div class="cp-field">

            <label>
                Title
            </label>

            <input
                type="text"
                name="delivery_cards[__INDEX__][title]"
                required
            >

        </div>

        <div class="cp-field">

            <label>
                Description
            </label>

            <textarea
                name="delivery_cards[__INDEX__][description]"
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


        const allowedTabs = [
            'hero',
            'seller',
            'buyer',
            'delivery',
            'cta',
        ];


        let currentTab =
            @json($activeTab);


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

        let sellerIndex =
            {{ count($sellerSteps) + 20 }};


        let buyerIndex =
            {{ count($buyerSteps) + 20 }};


        let deliveryIndex =
            {{ count($deliveryCards) + 20 }};


        function addJourneyStep(
            type,
            targetId,
            index
        ) {
            const template =
                document.getElementById(
                    'journey-step-template'
                );


            let html =
                template.innerHTML;


            html =
                html.replaceAll(
                    '__TYPE__',
                    type
                );


            html =
                html.replaceAll(
                    '__INDEX__',
                    index
                );


            document
                .getElementById(
                    targetId
                )
                .insertAdjacentHTML(
                    'beforeend',
                    html
                );
        }


        document
            .querySelector(
                '[data-add-repeat="seller-step"]'
            )
            .addEventListener(
                'click',
                function () {

                    addJourneyStep(
                        'seller_steps',
                        'seller-step-repeater',
                        sellerIndex++
                    );

                }
            );


        document
            .querySelector(
                '[data-add-repeat="buyer-step"]'
            )
            .addEventListener(
                'click',
                function () {

                    addJourneyStep(
                        'buyer_steps',
                        'buyer-step-repeater',
                        buyerIndex++
                    );

                }
            );


        document
            .querySelector(
                '[data-add-repeat="delivery-card"]'
            )
            .addEventListener(
                'click',
                function () {

                    const template =
                        document.getElementById(
                            'delivery-card-template'
                        );


                    let html =
                        template.innerHTML;


                    html =
                        html.replaceAll(
                            '__INDEX__',
                            deliveryIndex++
                        );


                    document
                        .getElementById(
                            'delivery-card-repeater'
                        )
                        .insertAdjacentHTML(
                            'beforeend',
                            html
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


                const parent =
                    item.parentElement;


                if (
                    parent
                        .querySelectorAll(
                            '.cp-repeat-item'
                        )
                        .length
                    <=
                    1
                ) {

                    alert(
                        'At least one item must remain.'
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