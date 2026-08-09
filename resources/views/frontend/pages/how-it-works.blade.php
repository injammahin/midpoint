@extends('frontend.layouts.app')

@section('title', 'How MidPoint Works')

@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap">

            <div class="mp-section-head">

                <div class="mp-eyebrow">
                    How MidPoint works
                </div>

                <h1>
                    One flow. Both sides protected.
                </h1>

                <p>
                    Whether you're the buyer or the seller, here's exactly
                    what happens from start to finish.
                </p>

            </div>


            <div class="mp-grid-2 items-start">

                {{-- SELLER --}}
                <div class="mp-card p-7">

                    <span class="mp-badge mp-badge-green mb-4">
                        For sellers
                    </span>


                    <div class="mp-timeline">

                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                1
                            </div>

                            <div class="mp-timeline-title">
                                Create a transaction
                            </div>

                            <div class="mp-timeline-text">
                                Add the product, photos, price and quantity.
                                Choose your delivery option.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                2
                            </div>

                            <div class="mp-timeline-title">
                                Share the invite link
                            </div>

                            <div class="mp-timeline-text">
                                Send it to your buyer on WhatsApp,
                                Instagram DM — anywhere.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                3
                            </div>

                            <div class="mp-timeline-title">
                                Ship when payment is held
                            </div>

                            <div class="mp-timeline-text">
                                We notify you the moment the buyer's money
                                is secured. Arrange your delivery, then tap
                                <strong>Item dispatched</strong> so the
                                buyer knows it's on the way.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                4
                            </div>

                            <div class="mp-timeline-title">
                                Get paid
                            </div>

                            <div class="mp-timeline-text">
                                Once the buyer accepts (or 8 hours pass),
                                your payout lands. You receive the price
                                minus the 5% service fee.
                            </div>

                        </div>

                    </div>

                </div>


                {{-- BUYER --}}
                <div class="mp-card p-7">

                    <span class="mp-badge mp-badge-purple mb-4">
                        For buyers
                    </span>


                    <div class="mp-timeline">

                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                1
                            </div>

                            <div class="mp-timeline-title">
                                Open the seller's invite
                            </div>

                            <div class="mp-timeline-text">
                                Review the product, price and delivery
                                details before committing.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                2
                            </div>

                            <div class="mp-timeline-title">
                                Pay MidPoint, not the seller
                            </div>

                            <div class="mp-timeline-text">
                                You pay the product price. Your money is
                                held safely — the seller never touches it
                                until you're satisfied.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                3
                            </div>

                            <div class="mp-timeline-title">
                                Receive and inspect
                            </div>

                            <div class="mp-timeline-text">
                                You have 8 hours to check the item properly.
                            </div>

                        </div>


                        <div class="mp-timeline-item done">

                            <div class="mp-timeline-dot">
                                4
                            </div>

                            <div class="mp-timeline-title">
                                Accept or dispute
                            </div>

                            <div class="mp-timeline-text">
                                Happy? Release the funds. Something wrong?
                                Open a dispute and our team steps in.
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Delivery --}}
            <div class="mp-card mt-6 p-[26px]">

                <h2
                    class="mb-[14px] font-['Bricolage_Grotesque']
                           text-[20px] font-bold"
                >
                    Delivery
                </h2>


                <div class="mp-grid-2">

                    <div
                        class="rounded-[14px]
                               border-[1.5px] border-[#12B76A]
                               bg-[#E8F7EF] p-[18px]"
                    >

                        <strong>
                            Sellers arrange their own delivery
                        </strong>

                        <p class="mp-small mp-muted mt-[6px]">
                            Use your own rider, park logistics, a courier
                            company or hand delivery — whatever works for
                            your route. When the item leaves your hands,
                            tap <strong>Item dispatched</strong> in your
                            dashboard and the buyer is notified immediately.
                            Any delivery cost is agreed directly between
                            you and your buyer.
                        </p>

                    </div>


                    <div
                        class="rounded-[14px]
                               border-[1.5px] border-dashed
                               border-[#E4EAE6] p-[18px]"
                    >

                        <div class="mb-[6px] flex flex-wrap items-center gap-2">

                            <strong>
                                MidPoint Courier
                            </strong>

                            <span class="mp-badge mp-badge-purple">
                                Coming soon
                            </span>

                        </div>

                        <p class="mp-small mp-muted">
                            We're building an integrated courier service so
                            sellers can hand off delivery and buyers get live
                            tracking inside MidPoint. Until then, sellers
                            handle delivery themselves.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

@endsection