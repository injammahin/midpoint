@extends('frontend.layouts.app')

@section('title', 'About MidPoint')

@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap !max-w-[820px]">

            <div class="mp-eyebrow">
                About MidPoint
            </div>

            <h1
                class="mb-4 font-['Bricolage_Grotesque']
                       text-[clamp(28px,3.6vw,42px)]
                       font-extrabold leading-[1.15]"
            >
                We exist because "what if they scam me?"
                kills good deals every day.
            </h1>

            <p class="mp-muted text-[16.5px]">
                Millions of Nigerians find buyers and sellers on WhatsApp,
                Instagram, Facebook Marketplace, Jiji and through referrals.
                The products are real. The people are mostly honest. But
                without trust, deals collapse — or worse, someone loses
                money. MidPoint is the neutral middle: we hold the buyer's
                payment safely and only release it to the seller when the
                buyer confirms the item, or the inspection period expires.
            </p>


            {{-- Stats --}}
            <div class="mp-grid-3 mt-9">

                <div class="mp-card mp-stat">

                    <div class="mp-stat-label">
                        Founded
                    </div>

                    <div class="mp-stat-value">
                        2026
                    </div>

                    <div class="mp-small mp-muted">
                        Lagos, Nigeria
                    </div>

                </div>


                <div class="mp-card mp-stat">

                    <div class="mp-stat-label">
                        Mission
                    </div>

                    <div class="mp-stat-value !text-[18px]">
                        Make trust free
                    </div>

                    <div class="mp-small mp-muted">
                        Between strangers who trade online
                    </div>

                </div>


                <div class="mp-card mp-stat">

                    <div class="mp-stat-label">
                        We are not
                    </div>

                    <div class="mp-stat-value !text-[18px]">
                        A marketplace
                    </div>

                    <div class="mp-small mp-muted">
                        Find your deal anywhere — protect it here
                    </div>

                </div>

            </div>


            <hr class="my-9 border-0 border-t border-[#E4EAE6]">


            <h2
                class="mb-3 font-['Bricolage_Grotesque']
                       text-[20px] font-bold"
            >
                Our principles
            </h2>


            <div class="mp-grid-2">

                <div class="mp-card p-5">

                    <strong>
                        Neutrality.
                    </strong>

                    <p class="mp-small mp-muted">
                        We don't take sides. Funds move based on rules both
                        parties agreed to upfront.
                    </p>

                </div>


                <div class="mp-card p-5">

                    <strong>
                        Transparency.
                    </strong>

                    <p class="mp-small mp-muted">
                        Sellers see exactly what they'll receive. Buyers see
                        exactly what they'll pay. No surprises.
                    </p>

                </div>


                <div class="mp-card p-5">

                    <strong>
                        Speed.
                    </strong>

                    <p class="mp-small mp-muted">
                        The moment a buyer accepts an item, the seller's
                        payout is on its way.
                    </p>

                </div>


                <div class="mp-card p-5">

                    <strong>
                        Local reality.
                    </strong>

                    <p class="mp-small mp-muted">
                        Built around how Nigerians actually trade —
                        POS-era pragmatism, WhatsApp-first deals, riders
                        and park logistics.
                    </p>

                </div>

            </div>

        </div>

    </section>

</div>

@endsection