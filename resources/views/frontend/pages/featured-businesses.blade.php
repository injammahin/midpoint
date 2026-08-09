@extends('frontend.layouts.app')

@section('title', 'Featured Businesses | MidPoint')

@section('content')

@php

    $businesses = [

        [
            'initials' => 'TG',
            'name' => 'Temi Gadgets',
            'category' => 'Phones & Electronics',
            'description' => 'UK-used and new phones, laptops and accessories. Every device tested before dispatch.',
            'location' => 'Ikeja, Lagos',
            'trust' => '4.9',
            'gradient' => 'linear-gradient(135deg,#0B3D2E,#12B76A)',
        ],

        [
            'initials' => 'CH',
            'name' => 'Crowned Hair Empire',
            'category' => 'Beauty & Hair',
            'description' => 'Luxury human-hair wigs and bundles, custom-made and shipped nationwide.',
            'location' => 'Surulere, Lagos',
            'trust' => '4.8',
            'gradient' => 'linear-gradient(135deg,#7A5AF8,#B49CFF)',
        ],

        [
            'initials' => 'ZS',
            'name' => 'Zaria Stitches',
            'category' => 'Fashion & Tailoring',
            'description' => 'Ready-to-wear Ankara sets and bespoke tailoring with 5-day turnaround.',
            'location' => 'Wuse, Abuja',
            'trust' => '4.7',
            'gradient' => 'linear-gradient(135deg,#B54708,#F79009)',
        ],

        [
            'initials' => 'HP',
            'name' => 'HomePlus NG',
            'category' => 'Home & Kitchen',
            'description' => 'Air fryers, blenders, cookware and small appliances with warranty.',
            'location' => 'Lekki, Lagos',
            'trust' => '4.8',
            'gradient' => 'linear-gradient(135deg,#123B54,#3B82C4)',
        ],

        [
            'initials' => 'PH',
            'name' => 'PowerHub NG',
            'category' => 'Power & Tools',
            'description' => 'Generators, inverters and solar kits with installation guidance.',
            'location' => 'Aba, Abia',
            'trust' => '4.3',
            'gradient' => 'linear-gradient(135deg,#5A1846,#C13584)',
        ],

        [
            'initials' => 'BK',
            'name' => 'Bookish Naija',
            'category' => 'Books & Stationery',
            'description' => 'New and pre-loved books, exam packs and premium stationery.',
            'location' => 'Bodija, Ibadan',
            'trust' => '4.9',
            'gradient' => 'linear-gradient(135deg,#0E7A4C,#7EF0B6)',
        ],

    ];

@endphp


<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap">

            <div class="mp-section-head">

                <div class="mp-eyebrow">
                    Featured businesses
                </div>

                <h1>
                    Verified sellers, not a marketplace.
                </h1>

                <p>
                    MidPoint doesn't list products. These are trusted
                    businesses you can start a protected transaction with
                    directly.
                </p>

            </div>


            <div class="mp-grid-3">

                @foreach ($businesses as $business)

                    <article class="mp-card mp-business-card">

                        <div class="flex items-center gap-3">

                            <div
                                class="grid h-[50px] w-[50px]
                                       shrink-0 place-items-center
                                       rounded-[15px]
                                       text-[16px]
                                       font-bold text-white"
                                style="background: {{ $business['gradient'] }}"
                            >
                                {{ $business['initials'] }}
                            </div>


                            <div class="min-w-0">

                                <strong class="block truncate">
                                    {{ $business['name'] }}
                                </strong>

                                <div class="mp-small mp-muted">
                                    {{ $business['category'] }}
                                </div>

                            </div>


                            <span
                                class="ml-auto
                                       shrink-0
                                       text-[13px]
                                       font-bold
                                       text-[#0E7A4C]"
                            >
                                🛡 {{ $business['trust'] }}
                            </span>

                        </div>


                        <p class="mp-small mp-muted">
                            {{ $business['description'] }}
                        </p>


                        <div class="mp-small mp-muted">
                            📍 {{ $business['location'] }}
                        </div>


                        <div
                            class="mt-auto
                                   flex flex-col gap-2
                                   sm:flex-row"
                        >

                            <button
                                type="button"
                                class="mp-btn mp-btn-green
                                       mp-btn-sm flex-1"
                            >
                                Start secure transaction
                            </button>


                            <button
                                type="button"
                                class="mp-btn mp-btn-outline
                                       mp-btn-sm"
                            >
                                View business
                            </button>

                        </div>

                    </article>

                @endforeach

            </div>


            <div
                class="mt-[26px]
                       flex flex-col gap-[14px]
                       rounded-[18px]
                       bg-[linear-gradient(120deg,#0B3D2E,#7A5AF8)]
                       p-[26px]
                       text-white
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >

                <div>

                    <strong class="text-[17px]">
                        Own a business? Get verified and listed here.
                    </strong>

                    <div class="mp-small text-[#DCD4FB]">
                        List your products so buyers can start secure
                        transactions instantly. Plans from ₦5,000/month.
                    </div>

                </div>


                <a
                    href="{{ url('/verified-sellers') }}"
                    class="mp-btn shrink-0 bg-white text-[#0B3D2E]"
                >
                    Become a verified seller
                </a>

            </div>

        </div>

    </section>

</div>

@endsection