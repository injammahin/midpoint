@extends('frontend.layouts.app')


@section(
    'title',
    'MidPoint — Buy with confidence. Sell with confidence.'
)


@section(
    'meta_description',
    'The safe middle for online transactions in Nigeria. MidPoint holds buyer payments securely until the buyer confirms and accepts the item.'
)


@push('styles')
    <style>
        /*
        |--------------------------------------------------------------------------
        | Home page
        |--------------------------------------------------------------------------
        */

        .midpoint-home {
            --forest: #0B3D2E;
            --forest-2: #0E4A38;
            --emerald: #12B76A;
            --mint: #E8F7EF;
            --mint-2: #D2F0E0;
            --purple: #7A5AF8;
            --lav: #F1EDFE;
            --ink: #0D120F;
            --slate: #5A6660;
            --line: #E4EAE6;
            --paper: #F6F9F7;
            --amber: #F79009;
            --shadow: 0 6px 24px -8px rgba(11, 61, 46, .12);
            --shadow-lg: 0 18px 50px -16px rgba(11, 61, 46, .22);
        }

        .midpoint-home .display-font {
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        /*
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        */

        .midpoint-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            background:
                linear-gradient(
                    160deg,
                    #0B3D2E 0%,
                    #0E4A38 55%,
                    #123B54 130%
                );
        }

        .midpoint-hero::after {
            content: "";
            position: absolute;
            top: -140px;
            right: -140px;
            width: 480px;
            height: 480px;
            pointer-events: none;
            border-radius: 9999px;
            background:
                radial-gradient(
                    circle,
                    rgba(122, 90, 248, .35),
                    transparent 65%
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Common eyebrow
        |--------------------------------------------------------------------------
        */

        .home-eyebrow::before {
            content: "";
            width: 22px;
            height: 2px;
            flex: none;
            border-radius: 9999px;
            background: #12B76A;
        }

        /*
        |--------------------------------------------------------------------------
        | Escrow timeline
        |--------------------------------------------------------------------------
        */

        .escrow-timeline {
            position: relative;
            padding-left: 30px;
        }

        .escrow-timeline::before {
            content: "";
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 10px;
            width: 2px;
            border-radius: 99px;
            background: #E4EAE6;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 22px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            top: 2px;
            left: -30px;
            z-index: 2;
            display: grid;
            width: 22px;
            height: 22px;
            place-items: center;
            border: 2px solid #E4EAE6;
            border-radius: 50%;
            background: #fff;
            font-size: 11px;
        }

        .timeline-item.done .timeline-dot {
            border-color: #12B76A;
            background: #12B76A;
            color: #fff;
        }

        .timeline-item.current .timeline-dot {
            border-color: #7A5AF8;
            background: #fff;
            box-shadow: 0 0 0 5px #F1EDFE;
        }

        /*
        |--------------------------------------------------------------------------
        | FAQ
        |--------------------------------------------------------------------------
        */

        .home-faq summary {
            list-style: none;
        }

        .home-faq summary::-webkit-details-marker {
            display: none;
        }

        .home-faq summary::after {
            content: "+";
            margin-left: 20px;
            flex: none;
            color: #12B76A;
            font-size: 20px;
            font-weight: 400;
            line-height: 1;
        }

        .home-faq details[open] summary::after {
            content: "–";
        }

        /*
        |--------------------------------------------------------------------------
        | Business cards
        |--------------------------------------------------------------------------
        */

        .home-business-card {
            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;
        }

        .home-business-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 50px -16px rgba(11, 61, 46, .22);
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 960px) {
            .home-demo-card {
                max-width: 460px;
                transform: none !important;
            }
        }

        @media (max-width: 640px) {
            .midpoint-hero::after {
                top: -190px;
                right: -220px;
                width: 420px;
                height: 420px;
            }
        }
    </style>
@endpush


@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Featured Businesses
        |--------------------------------------------------------------------------
        |
        | Static for now.
        | Later this will come from the database.
        |
        */

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
        ];


    @endphp


    <div class="midpoint-home">

        {{-- =========================================================
            HERO
        ========================================================== --}}
        <section class="midpoint-hero">

            <div
                class="relative z-10 mx-auto grid max-w-[1160px]
                       grid-cols-1 items-center gap-[50px]
                       px-[22px] py-[56px]
                       min-[961px]:grid-cols-[1.05fr_.95fr]
                       min-[961px]:py-[86px]
                       min-[961px]:pb-[96px]"
            >

                {{-- =======================
                    Hero text
                ======================== --}}
                <div>

                    <div
                        class="inline-flex items-center gap-2
                               rounded-full
                               border border-white/[.18]
                               bg-white/10
                               px-[15px] py-[7px]
                               text-[12px] font-semibold
                               sm:text-[13px]"
                    >
                        <span>🛡️</span>

                        <span>
                            Escrow-style protection for everyday Nigerian trade
                        </span>
                    </div>


                    <h1
                        class="display-font
                               my-[16px]
                               max-w-[580px]
                               text-[34px]
                               font-extrabold
                               leading-[1.06]
                               tracking-[-0.02em]
                               sm:text-[42px]
                               lg:text-[50px]
                               xl:text-[56px]"
                    >
                        The safe middle for every

                        <span class="text-[#7EF0B6]">
                            online deal
                        </span>.
                    </h1>


                    <p
                        class="max-w-[480px]
                               text-[15px] leading-[1.65]
                               text-[#C8DAD2]
                               sm:text-[17.5px]"
                    >
                        Found a seller on WhatsApp, Instagram or Jiji?
                        Don't pay directly. MidPoint holds the money until
                        you confirm your item — so nobody gets burned.
                    </p>


                    {{-- Hero buttons --}}
                    <div
                        class="mt-[26px] flex flex-wrap gap-3"
                    >

                        <a
                            href="{{ url('/register') }}"
                            class="inline-flex min-h-[52px]
                                   items-center justify-center
                                   rounded-[14px]
                                   bg-[#12B76A]
                                   px-7 py-[15px]
                                   text-[15px] font-semibold text-white
                                   transition
                                   hover:-translate-y-px
                                   hover:brightness-105
                                   sm:text-[16px]"
                        >
                            Start a secure transaction
                        </a>


                        <a
                            href="{{ url('/how-it-works') }}"
                            class="inline-flex min-h-[52px]
                                   items-center justify-center
                                   rounded-[14px]
                                   border border-white/20
                                   bg-white/10
                                   px-7 py-[15px]
                                   text-[15px] font-semibold text-white
                                   transition
                                   hover:bg-white/[.14]
                                   sm:text-[16px]"
                        >
                            See how it works
                        </a>

                    </div>


                    {{-- Hero statistics --}}
                    <div
                        class="mt-[38px]
                               flex flex-wrap
                               gap-x-[34px]
                               gap-y-5"
                    >

                        <div>
                            <strong
                                class="display-font block
                                       text-[22px]
                                       font-extrabold
                                       sm:text-[24px]"
                            >
                                ₦184M+
                            </strong>

                            <span
                                class="text-[12.5px] text-[#9DBBAF]"
                            >
                                Safely held & released
                            </span>
                        </div>


                        <div>
                            <strong
                                class="display-font block
                                       text-[22px]
                                       font-extrabold
                                       sm:text-[24px]"
                            >
                                12,400+
                            </strong>

                            <span
                                class="text-[12.5px] text-[#9DBBAF]"
                            >
                                Completed transactions
                            </span>
                        </div>


                        <div>
                            <strong
                                class="display-font block
                                       text-[22px]
                                       font-extrabold
                                       sm:text-[24px]"
                            >
                                8 hrs
                            </strong>

                            <span
                                class="text-[12.5px] text-[#9DBBAF]"
                            >
                                Buyer inspection window
                            </span>
                        </div>

                    </div>

                </div>


                {{-- =======================
                    Escrow demo card
                ======================== --}}
                <div
                    class="home-demo-card
                           w-full
                           rounded-[26px]
                           bg-white
                           p-[20px]
                           text-[#0D120F]
                           shadow-[0_18px_50px_-16px_rgba(11,61,46,.22)]
                           min-[961px]:rotate-[1.2deg]
                           sm:p-[24px]"
                >

                    {{-- Seller header --}}
                    <div
                        class="flex items-center
                               justify-between
                               gap-4"
                    >

                        <div
                            class="flex min-w-0
                                   items-center gap-[10px]"
                        >

                            <div
                                class="grid h-[38px] w-[38px]
                                       shrink-0 place-items-center
                                       rounded-xl
                                       bg-[#7A5AF8]
                                       text-[13px] font-bold
                                       text-white"
                            >
                                TG
                            </div>


                            <div class="min-w-0">

                                <div
                                    class="truncate
                                           text-[14px]
                                           font-bold"
                                >
                                    Temi Gadgets
                                </div>

                                <div
                                    class="text-[12px]
                                           text-[#5A6660]
                                           sm:text-[13px]"
                                >
                                    Seller · Ikeja, Lagos
                                </div>

                            </div>

                        </div>


                        <span
                            class="inline-flex shrink-0
                                   rounded-full
                                   bg-[#F1EDFE]
                                   px-[11px] py-1
                                   text-[11px]
                                   font-semibold
                                   text-[#7A5AF8]
                                   sm:text-[12px]"
                        >
                            In escrow
                        </span>

                    </div>


                    {{-- Held money --}}
                    <div
                        class="my-4 rounded-[14px]
                               bg-gradient-to-r
                               from-[#F1EDFE]
                               to-[#E8F7EF]
                               px-4 py-4
                               text-center"
                    >

                        <span
                            class="block text-[12px]
                                   font-semibold
                                   text-[#5636D9]
                                   sm:text-[13px]"
                        >
                            Held safely by MidPoint
                        </span>

                        <strong
                            class="display-font mt-1
                                   block text-[24px]
                                   font-extrabold
                                   sm:text-[26px]"
                        >
                            ₦145,000
                        </strong>

                        <span
                            class="block text-[12px]
                                   text-[#5A6660]
                                   sm:text-[13px]"
                        >
                            iPhone 12, 128GB · Blue
                        </span>

                    </div>


                    {{-- Timeline --}}
                    <div class="escrow-timeline">

                        <div class="timeline-item done">

                            <div class="timeline-dot">
                                ✓
                            </div>

                            <div
                                class="text-[14px]
                                       font-bold"
                            >
                                Payment received
                            </div>

                            <div
                                class="text-[12.5px]
                                       text-[#5A6660]"
                            >
                                Chiamaka paid securely · 10:42 AM
                            </div>

                        </div>


                        <div class="timeline-item done">

                            <div class="timeline-dot">
                                ✓
                            </div>

                            <div
                                class="text-[14px]
                                       font-bold"
                            >
                                Dispatched by seller
                            </div>

                            <div
                                class="text-[12.5px]
                                       text-[#5A6660]"
                            >
                                Sent to Ibadan · seller-arranged delivery
                            </div>

                        </div>


                        <div class="timeline-item current">

                            <div class="timeline-dot"></div>

                            <div
                                class="text-[14px]
                                       font-bold
                                       text-[#7A5AF8]"
                            >
                                Inspection in progress
                            </div>

                            <div
                                class="text-[12.5px]
                                       text-[#5A6660]"
                            >
                                6h 42m remaining
                            </div>

                        </div>


                        <div class="timeline-item">

                            <div class="timeline-dot"></div>

                            <div
                                class="text-[14px]
                                       font-bold"
                            >
                                Funds released to seller
                            </div>

                            <div
                                class="text-[12.5px]
                                       text-[#5A6660]"
                            >
                                After buyer confirms
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- =========================================================
            THREE SIMPLE STEPS
        ========================================================== --}}
        <section
            class="bg-[#F6F9F7]
                   py-[52px]
                   sm:py-[74px]"
        >

            <div
                class="mx-auto max-w-[1160px]
                       px-[22px]"
            >

                <div
                    class="mx-auto max-w-[620px]
                           text-center"
                >

                    <div
                        class="home-eyebrow
                               mb-[14px]
                               inline-flex
                               items-center
                               justify-center gap-2
                               text-[12.5px]
                               font-bold uppercase
                               tracking-[.12em]
                               text-[#12B76A]"
                    >
                        Three simple steps
                    </div>


                    <h2
                        class="display-font
                               text-[27px]
                               font-extrabold
                               leading-[1.15]
                               sm:text-[34px]
                               lg:text-[38px]"
                    >
                        From "is this seller legit?" to done deal.
                    </h2>


                    <p
                        class="mt-[10px]
                               text-[15px]
                               leading-[1.6]
                               text-[#5A6660]
                               sm:text-[16px]"
                    >
                        MidPoint isn't a marketplace.
                        Find your buyer or seller anywhere —
                        then bring the payment here.
                    </p>

                </div>


                <div
                    class="mt-10 grid
                           grid-cols-1
                           gap-5
                           md:grid-cols-2
                           min-[961px]:grid-cols-3"
                >

                    {{-- Step 1 --}}
                    <article
                        class="rounded-[18px]
                               border border-[#E4EAE6]
                               bg-white
                               p-[26px]
                               shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]"
                    >

                        <div
                            class="display-font
                                   mb-4
                                   grid h-[42px] w-[42px]
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#0B3D2E]
                                   text-[18px]
                                   font-extrabold
                                   text-[#7EF0B6]"
                        >
                            1
                        </div>


                        <h3
                            class="display-font
                                   mb-2
                                   text-[18px]
                                   font-bold"
                        >
                            Create the transaction
                        </h3>


                        <p
                            class="text-[14px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            The seller lists the item, price and delivery
                            option, then shares a secure invite link with
                            the buyer.
                        </p>

                    </article>


                    {{-- Step 2 --}}
                    <article
                        class="rounded-[18px]
                               border border-[#E4EAE6]
                               bg-white
                               p-[26px]
                               shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]"
                    >

                        <div
                            class="display-font
                                   mb-4
                                   grid h-[42px] w-[42px]
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#0B3D2E]
                                   text-[18px]
                                   font-extrabold
                                   text-[#7EF0B6]"
                        >
                            2
                        </div>


                        <h3
                            class="display-font
                                   mb-2
                                   text-[18px]
                                   font-bold"
                        >
                            Buyer pays MidPoint
                        </h3>


                        <p
                            class="text-[14px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            The buyer pays into MidPoint's secure hold —
                            not the seller's account. The seller ships
                            knowing the money is real.
                        </p>

                    </article>


                    {{-- Step 3 --}}
                    <article
                        class="rounded-[18px]
                               border border-[#E4EAE6]
                               bg-white
                               p-[26px]
                               shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]
                               md:col-span-2
                               min-[961px]:col-span-1"
                    >

                        <div
                            class="display-font
                                   mb-4
                                   grid h-[42px] w-[42px]
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#0B3D2E]
                                   text-[18px]
                                   font-extrabold
                                   text-[#7EF0B6]"
                        >
                            3
                        </div>


                        <h3
                            class="display-font
                                   mb-2
                                   text-[18px]
                                   font-bold"
                        >
                            Inspect, then release
                        </h3>


                        <p
                            class="text-[14px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            The buyer gets 8 hours to inspect.
                            Accept the item and funds go to the seller
                            instantly — or open a dispute.
                        </p>

                    </article>

                </div>

            </div>

        </section>



        {{-- =========================================================
            WHY MIDPOINT
        ========================================================== --}}
        <section
            class="border-y border-[#E4EAE6]
                   bg-white
                   py-[52px]
                   sm:py-[74px]"
        >

            <div
                class="mx-auto max-w-[1160px]
                       px-[22px]"
            >

                <div class="max-w-[620px]">

                    <div
                        class="home-eyebrow
                               mb-[14px]
                               inline-flex items-center
                               gap-2
                               text-[12.5px]
                               font-bold uppercase
                               tracking-[.12em]
                               text-[#12B76A]"
                    >
                        Why MidPoint
                    </div>


                    <h2
                        class="display-font
                               max-w-[560px]
                               text-[27px]
                               font-extrabold
                               sm:text-[34px]
                               lg:text-[38px]"
                    >
                        Built for how Nigerians actually buy and sell.
                    </h2>

                </div>


                <div
                    class="mt-[42px]
                           grid grid-cols-1
                           gap-x-5 gap-y-8
                           sm:grid-cols-2
                           min-[961px]:grid-cols-4"
                >

                    {{-- Feature 1 --}}
                    <div>

                        <div
                            class="grid h-11 w-11
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#E8F7EF]
                                   text-[20px]"
                        >
                            🛡️
                        </div>


                        <h3
                            class="display-font
                                   mb-[6px] mt-3
                                   text-[16px]
                                   font-bold"
                        >
                            No "pay before delivery" fear
                        </h3>


                        <p
                            class="text-[13px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            Money only moves when both sides are protected.
                        </p>

                    </div>


                    {{-- Feature 2 --}}
                    <div>

                        <div
                            class="grid h-11 w-11
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#F1EDFE]
                                   text-[20px]"
                        >
                            📦
                        </div>


                        <h3
                            class="display-font
                                   mb-[6px] mt-3
                                   text-[16px]
                                   font-bold"
                        >
                            Dispatch confirmation
                        </h3>


                        <p
                            class="text-[13px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            Sellers arrange their own delivery and mark the
                            item as dispatched — so you always know when
                            it's on the way.
                        </p>

                    </div>


                    {{-- Feature 3 --}}
                    <div>

                        <div
                            class="grid h-11 w-11
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#E8F7EF]
                                   text-[20px]"
                        >
                            ⏱️
                        </div>


                        <h3
                            class="display-font
                                   mb-[6px] mt-3
                                   text-[16px]
                                   font-bold"
                        >
                            8-hour inspection
                        </h3>


                        <p
                            class="text-[13px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            Open the box, test it, be sure — before a
                            single naira is released.
                        </p>

                    </div>


                    {{-- Feature 4 --}}
                    <div>

                        <div
                            class="grid h-11 w-11
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#F1EDFE]
                                   text-[20px]"
                        >
                            ⚖️
                        </div>


                        <h3
                            class="display-font
                                   mb-[6px] mt-3
                                   text-[16px]
                                   font-bold"
                        >
                            Fair dispute resolution
                        </h3>


                        <p
                            class="text-[13px]
                                   leading-[1.6]
                                   text-[#5A6660]"
                        >
                            If something's wrong, our resolution team
                            steps in with evidence from both sides.
                        </p>

                    </div>

                </div>

            </div>

        </section>



        {{-- =========================================================
            FEATURED BUSINESSES
        ========================================================== --}}
        <section
            class="bg-[#F6F9F7]
                   py-[52px]
                   sm:py-[74px]"
        >

            <div
                class="mx-auto max-w-[1160px]
                       px-[22px]"
            >

                {{-- Section heading --}}
                <div
                    class="mb-[42px]
                           flex flex-col gap-5
                           sm:flex-row
                           sm:items-end
                           sm:justify-between"
                >

                    <div>

                        <div
                            class="home-eyebrow
                                   mb-[14px]
                                   inline-flex items-center
                                   gap-2
                                   text-[12.5px]
                                   font-bold uppercase
                                   tracking-[.12em]
                                   text-[#12B76A]"
                        >
                            Featured businesses
                        </div>


                        <h2
                            class="display-font
                                   text-[27px]
                                   font-extrabold
                                   sm:text-[34px]
                                   lg:text-[38px]"
                        >
                            Verified sellers who trade the safe way.
                        </h2>

                    </div>


                    <a
                        href="{{ url('/featured-businesses') }}"
                        class="inline-flex min-h-[43px]
                               w-fit items-center
                               justify-center
                               rounded-xl
                               border-[1.5px]
                               border-[#E4EAE6]
                               bg-white
                               px-5 py-[11px]
                               text-[14px]
                               font-semibold
                               text-[#0B3D2E]
                               transition
                               hover:border-[#12B76A]
                               hover:text-[#12B76A]"
                    >
                        View all
                    </a>

                </div>


                {{-- Business cards --}}
                <div
                    class="grid grid-cols-1
                           gap-5
                           md:grid-cols-2
                           min-[961px]:grid-cols-3"
                >

                    @foreach ($businesses as $business)

                        <article
                            class="home-business-card
                                   flex h-full flex-col
                                   gap-3
                                   rounded-[18px]
                                   border border-[#E4EAE6]
                                   bg-white
                                   p-[22px]
                                   shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]"
                        >

                            {{-- Seller --}}
                            <div
                                class="flex items-center
                                       gap-3"
                            >

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

                                    <h3
                                        class="truncate
                                               text-[15px]
                                               font-bold"
                                    >
                                        {{ $business['name'] }}
                                    </h3>

                                    <div
                                        class="text-[13px]
                                               text-[#5A6660]"
                                    >
                                        {{ $business['category'] }}
                                    </div>

                                </div>


                                <div
                                    class="ml-auto
                                           inline-flex shrink-0
                                           items-center gap-[5px]
                                           text-[13px]
                                           font-bold
                                           text-[#0E7A4C]"
                                >
                                    <span>🛡</span>

                                    <span>
                                        {{ $business['trust'] }}
                                    </span>
                                </div>

                            </div>


                            <p
                                class="text-[13px]
                                       leading-[1.6]
                                       text-[#5A6660]"
                            >
                                {{ $business['description'] }}
                            </p>


                            <div
                                class="text-[13px]
                                       text-[#5A6660]"
                            >
                                📍 {{ $business['location'] }}
                            </div>


                            <div
                                class="mt-auto
                                       flex flex-col gap-2
                                       sm:flex-row"
                            >

                                <a
                                    href="{{ url('/business/temi-gadgets') }}"
                                    class="inline-flex
                                           min-h-[36px]
                                           flex-1 items-center
                                           justify-center
                                           rounded-[10px]
                                           bg-[#12B76A]
                                           px-[13px] py-[7px]
                                           text-center
                                           text-[13px]
                                           font-semibold
                                           text-white
                                           transition
                                           hover:brightness-105"
                                >
                                    Start secure transaction
                                </a>


                                <a
                                    href="{{ url('/business/temi-gadgets') }}"
                                    class="inline-flex
                                           min-h-[36px]
                                           items-center
                                           justify-center
                                           rounded-[10px]
                                           border-[1.5px]
                                           border-[#E4EAE6]
                                           bg-white
                                           px-[13px] py-[7px]
                                           text-[13px]
                                           font-semibold
                                           text-[#0B3D2E]
                                           transition
                                           hover:border-[#12B76A]
                                           hover:text-[#12B76A]"
                                >
                                    View business
                                </a>

                            </div>

                        </article>

                    @endforeach

                </div>

            </div>

        </section>



        {{-- =========================================================
            TESTIMONIALS
        ========================================================== --}}
        <section
            class="bg-[linear-gradient(160deg,#0B3D2E,#123B54)]
                   py-[52px]
                   text-white
                   sm:py-[74px]"
        >

            <div
                class="mx-auto max-w-[1160px]
                       px-[22px]"
            >

                <div class="mb-[42px]">

                    <div
                        class="home-eyebrow
                               mb-[14px]
                               inline-flex items-center
                               gap-2
                               text-[12.5px]
                               font-bold uppercase
                               tracking-[.12em]
                               text-[#7EF0B6]"
                    >
                        Testimonials
                    </div>


                    <h2
                        class="display-font
                               text-[27px]
                               font-extrabold
                               text-white
                               sm:text-[34px]
                               lg:text-[38px]"
                    >
                        People sleep better with MidPoint.
                    </h2>

                </div>


                <div
                    class="grid grid-cols-1
                           gap-5
                           md:grid-cols-2
                           min-[961px]:grid-cols-3"
                >

                    {{-- Testimonial 1 --}}
                    <article
                        class="rounded-[18px]
                               border border-white/[.12]
                               bg-white/[.06]
                               p-6"
                    >

                        <div
                            class="text-[13px]
                                   tracking-[2px]
                                   text-[#F5B301]"
                        >
                            ★★★★★
                        </div>


                        <p
                            class="my-3
                                   text-[14.5px]
                                   leading-[1.6]
                                   text-[#D7E5DE]"
                        >
                            "I sell wigs on Instagram. Buyers used to vanish
                            after 'I'll transfer now now'. With MidPoint I
                            ship only when the money is already held.
                            Game changer."
                        </p>


                        <div
                            class="flex items-center
                                   gap-[10px]"
                        >

                            <div
                                class="grid h-[38px] w-[38px]
                                       place-items-center
                                       rounded-xl
                                       bg-[#7A5AF8]
                                       text-[13px]
                                       font-bold"
                            >
                                AB
                            </div>


                            <div>

                                <div
                                    class="text-[13.5px]
                                           font-bold"
                                >
                                    Adaeze Bello
                                </div>

                                <div
                                    class="text-[13px]
                                           text-[#9DBBAF]"
                                >
                                    Crowned Hair Empire, Lagos
                                </div>

                            </div>

                        </div>

                    </article>


                    {{-- Testimonial 2 --}}
                    <article
                        class="rounded-[18px]
                               border border-white/[.12]
                               bg-white/[.06]
                               p-6"
                    >

                        <div
                            class="text-[13px]
                                   tracking-[2px]
                                   text-[#F5B301]"
                        >
                            ★★★★★
                        </div>


                        <p
                            class="my-3
                                   text-[14.5px]
                                   leading-[1.6]
                                   text-[#D7E5DE]"
                        >
                            "Bought a UK-used laptop from a Jiji seller in
                            Ibadan. The 8-hour inspection let me test
                            everything before his money was released.
                            Both of us were calm."
                        </p>


                        <div
                            class="flex items-center
                                   gap-[10px]"
                        >

                            <div
                                class="grid h-[38px] w-[38px]
                                       place-items-center
                                       rounded-xl
                                       bg-[#12B76A]
                                       text-[13px]
                                       font-bold"
                            >
                                KO
                            </div>


                            <div>

                                <div
                                    class="text-[13.5px]
                                           font-bold"
                                >
                                    Kunle Ogunleye
                                </div>

                                <div
                                    class="text-[13px]
                                           text-[#9DBBAF]"
                                >
                                    Buyer, Abeokuta
                                </div>

                            </div>

                        </div>

                    </article>


                    {{-- Testimonial 3 --}}
                    <article
                        class="rounded-[18px]
                               border border-white/[.12]
                               bg-white/[.06]
                               p-6
                               md:col-span-2
                               min-[961px]:col-span-1"
                    >

                        <div
                            class="text-[13px]
                                   tracking-[2px]
                                   text-[#F5B301]"
                        >
                            ★★★★★
                        </div>


                        <p
                            class="my-3
                                   text-[14.5px]
                                   leading-[1.6]
                                   text-[#D7E5DE]"
                        >
                            "I dispatch with my own rider and just tap
                            'Item dispatched' — the buyer gets notified
                            instantly. I received ₦19,000 on ₦20,000 clean.
                            Fair."
                        </p>


                        <div
                            class="flex items-center
                                   gap-[10px]"
                        >

                            <div
                                class="grid h-[38px] w-[38px]
                                       place-items-center
                                       rounded-xl
                                       bg-[#F79009]
                                       text-[13px]
                                       font-bold"
                            >
                                TU
                            </div>


                            <div>

                                <div
                                    class="text-[13.5px]
                                           font-bold"
                                >
                                    Tunde Usman
                                </div>

                                <div
                                    class="text-[13px]
                                           text-[#9DBBAF]"
                                >
                                    Temi Gadgets, Ikeja
                                </div>

                            </div>

                        </div>

                    </article>

                </div>

            </div>

        </section>



        {{-- =========================================================
            FAQ
        ========================================================== --}}
        <section
            class="bg-[#F6F9F7]
                   py-[52px]
                   sm:py-[74px]"
        >

            <div
                class="mx-auto max-w-[760px]
                       px-[22px]"
            >

                <div
                    class="mx-auto mb-[42px]
                           max-w-[620px]
                           text-center"
                >

                    <div
                        class="home-eyebrow
                               mb-[14px]
                               inline-flex items-center
                               justify-center gap-2
                               text-[12.5px]
                               font-bold uppercase
                               tracking-[.12em]
                               text-[#12B76A]"
                    >
                        FAQs
                    </div>


                    <h2
                        class="display-font
                               text-[27px]
                               font-extrabold
                               sm:text-[34px]
                               lg:text-[38px]"
                    >
                        Questions people ask before their first deal.
                    </h2>

                </div>


                <div class="home-faq">

@forelse ($homeFaqs as $faq)

    <details
        class="mb-[10px]
               overflow-hidden
               rounded-[14px]
               border border-[#E4EAE6]
               bg-white"
    >

        <summary
            class="flex cursor-pointer
                   items-center
                   justify-between
                   px-5 py-[17px]
                   text-[14px]
                   font-semibold
                   sm:text-[15px]"
        >
            {{ $faq->question }}
        </summary>


        <div
            class="px-5 pb-[17px]
                   text-[14px]
                   leading-[1.65]
                   text-[#5A6660]"
        >

            {!! nl2br(
                e(
                    $faq->answer
                )
            ) !!}

        </div>

    </details>


@empty

    <div
        class="rounded-[14px]
               border border-[#E4EAE6]
               bg-white
               p-6
               text-center
               text-[13px]
               text-[#5A6660]"
    >
        FAQs will be available soon.
    </div>

@endforelse

                </div>


                <div class="mt-[22px] text-center">

                    <a
                        href="{{ url('/faqs') }}"
                        class="inline-flex
                               min-h-[43px]
                               items-center justify-center
                               rounded-xl
                               border-[1.5px]
                               border-[#E4EAE6]
                               bg-white
                               px-5 py-[11px]
                               text-[14px]
                               font-semibold
                               text-[#0B3D2E]
                               transition
                               hover:border-[#12B76A]
                               hover:text-[#12B76A]"
                    >
                        See all FAQs
                    </a>

                </div>

            </div>

        </section>



        {{-- =========================================================
            FINAL CTA
        ========================================================== --}}
        <section
            class="bg-[#F6F9F7]
                   pb-[52px]
                   sm:pb-[74px]"
        >

            <div
                class="mx-auto max-w-[1160px]
                       px-[22px]"
            >

                <div
                    class="rounded-[18px]
                           bg-[linear-gradient(120deg,#0B3D2E,#7A5AF8)]
                           px-6 py-10
                           text-center
                           text-white
                           shadow-[0_6px_24px_-8px_rgba(11,61,46,.12)]
                           sm:px-12
                           sm:py-12"
                >

                    <h2
                        class="display-font
                               text-[25px]
                               font-extrabold
                               sm:text-[30px]
                               lg:text-[34px]"
                    >
                        Buy with confidence. Sell with confidence.
                    </h2>


                    <p
                        class="mx-auto mb-6 mt-[10px]
                               max-w-[460px]
                               text-[14px]
                               leading-[1.6]
                               text-[#E4DEFB]"
                    >
                        Your next online deal doesn't have to be a gamble.
                        It takes 2 minutes to create your first secure
                        transaction.
                    </p>


                    <a
                        href="{{ url('/register') }}"
                        class="inline-flex
                               min-h-[52px]
                               items-center justify-center
                               rounded-[14px]
                               bg-white
                               px-7 py-[15px]
                               text-[15px]
                               font-semibold
                               text-[#0B3D2E]
                               transition
                               hover:-translate-y-px
                               hover:shadow-lg
                               sm:text-[16px]"
                    >
                        Create free account
                    </a>

                </div>

            </div>

        </section>

    </div>

@endsection