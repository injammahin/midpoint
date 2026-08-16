@extends('frontend.layouts.app')

@section('title', 'Terms & Conditions | Midpoint')

@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap !max-w-[820px]">

            <div class="mp-eyebrow">
                Legal
            </div>


            <h1
                class="mb-2
                       font-['Bricolage_Grotesque']
                       text-[clamp(26px,3.4vw,38px)]
                       font-extrabold"
            >
                Terms & Conditions
            </h1>


            <p
                class="mb-2
                       text-[13px]
                       text-[#5A6660]"
            >
                Last updated: 1 August 2026 ·
                Midpoint Technologies Ltd (RC 1839204), Lagos, Nigeria
            </p>


            {{-- Prototype notice --}}
            <div
                class="mp-card
                       mb-[26px]
                       !border-[#F79009]
                       !bg-[#FEF4E6]
                       p-4"
            >

                <div
                    class="text-[13px]
                           leading-[1.6]
                           text-[#B54708]"
                >
                    <strong>
                        Prototype notice.
                    </strong>

                    This document is illustrative content for design purposes
                    and has not been drafted or reviewed by a qualified lawyer.
                    It must be replaced with legally reviewed terms before launch.
                </div>

            </div>


            {{-- 1 --}}
            <h2 class="legal-title">
                1. What Midpoint is
            </h2>

            <p class="legal-text">
                Midpoint provides an escrow-style transaction service.
                We are not a marketplace, we do not list, sell, own,
                inspect or warrant any item or service traded through
                the platform, and we are not a party to the underlying
                contract between buyer and seller. Buyers and sellers
                find each other independently and use Midpoint only to
                hold and release payment.
            </p>


            {{-- 2 --}}
            <h2 class="legal-title">
                2. Eligibility & accounts
            </h2>

            <p class="legal-text">
                You must be at least 18 years old and legally able to
                enter contracts. You must provide accurate information
                and keep your login credentials secure. Sellers must
                complete identity verification (KYC) before receiving
                payouts. We may suspend or close accounts used for fraud,
                prohibited goods, or repeated dispute abuse.
            </p>


            {{-- 3 --}}
            <h2 class="legal-title">
                3. Fees, VAT & delivery costs
            </h2>

            <p class="legal-text">
                Sellers pay a Midpoint Service Fee of 5% of the product
                price, deducted from the payout when funds are released.
                Nigerian Value Added Tax of 7.5% is applied to the service
                fee (not to the product price) and is remitted to the
                Federal Inland Revenue Service. Buyers pay no Midpoint
                service fee. Delivery is arranged directly between buyer
                and seller; any delivery amount entered at checkout is held
                in escrow alongside the product price and released on the
                same terms.
            </p>


            <p class="legal-text mt-[10px]">
                Separately from the Midpoint Service Fee, a
                <strong>payment processing fee</strong> is charged by our
                payment gateway when a buyer funds a transaction. This fee
                is currently
                <strong>
                    1.5% of the amount paid, capped at ₦2,000,
                    plus 7.5% VAT on that fee
                </strong>.
                This charge is levied by the gateway, not by Midpoint,
                and is subject to change by the gateway. Please refer to
                clause 6A for how this fee is treated on refunds and
                cancellations.
            </p>


            {{-- 4 --}}
            <h2 class="legal-title">
                4. Delivery & dispatch
            </h2>

            <p class="legal-text">
                Sellers are solely responsible for arranging delivery and
                must mark the item as dispatched when it is sent. Sellers
                must dispatch within a reasonable period after payment is
                held. Where a seller fails to dispatch, Midpoint may, after
                contacting both parties, cancel the transaction and refund
                the buyer in full.
            </p>


            {{-- 5 --}}
            <h2 class="legal-title">
                5. Confirmation of receipt & inspection window
            </h2>

            <p class="legal-text">
                The buyer confirms receipt in the app once the item arrives.
                On confirming receipt, the buyer either (a) accepts the item,
                releasing funds immediately, or (b) starts an inspection
                window of <strong>8 hours</strong>. During the inspection
                window the buyer may accept the item or open a dispute.
            </p>


            {{-- 6 --}}
            <h2 class="legal-title">
                6. Auto-release rules
            </h2>

            <p class="legal-text">
                The following automatic release rules apply and are
                material terms of using Midpoint:
            </p>


            <div
                class="mp-card mt-[10px]
                       p-[18px]"
            >

                <div
                    class="flex flex-col
                           gap-[10px]
                           text-[13px]
                           leading-[1.65]"
                >

                    <div>
                        <strong>6.1</strong>
                        If the buyer starts the inspection window and takes
                        no action, funds are released automatically to the
                        seller when the 8-hour window expires.
                    </div>

                    <div>
                        <strong>6.2</strong>
                        Opening a dispute before the window expires suspends
                        auto-release. Funds remain locked until the dispute
                        is resolved.
                    </div>

                    <div>
                        <strong>6.3</strong>
                        If the buyer never confirms receipt, Midpoint may
                        release funds to the seller
                        <strong>7 days</strong> after the seller marked the
                        item dispatched, provided the seller supplies
                        satisfactory proof of dispatch and delivery and the
                        buyer has been notified at least twice and has not
                        responded.
                    </div>

                    <div>
                        <strong>6.4</strong>
                        Auto-release, once executed, is final. A buyer who
                        did not act within the window may still raise a
                        complaint, but Midpoint cannot recover funds already
                        paid out and any remedy lies against the seller directly.
                    </div>

                    <div>
                        <strong>6.5</strong>
                        Midpoint may extend or pause any window at its
                        discretion where there is credible evidence of
                        delay, fraud, or a genuine request for more
                        inspection time.
                    </div>

                </div>

            </div>


            {{-- 6A --}}
            <h2 class="legal-title">
                6A. Refunds & cancellations — non-refundable processing fee
            </h2>


            <div
                class="mp-card mt-[10px]
                       !border-[#F79009]
                       !bg-[#FEF4E6]
                       p-[18px]"
            >

                <div
                    class="flex flex-col
                           gap-[10px]
                           text-[13px]
                           leading-[1.65]
                           text-[#B54708]"
                >

                    <div>
                        <strong>6A.1</strong>
                        In the event of a buyer refund or a cancellation of
                        a funded transaction, the
                        <strong>payment processing gateway fee</strong>
                        (currently 1.5% of the amount paid, capped at
                        ₦2,000, plus 7.5% VAT on that fee) is
                        <strong>non-refundable</strong> and will be deducted
                        from the buyer's refund total.
                    </div>

                    <div>
                        <strong>6A.2</strong>
                        This is because the gateway charges that fee at the
                        point the buyer's payment is collected and does not
                        return it to Midpoint when a transaction is reversed.
                        Midpoint cannot recover the cost and does not retain
                        it. Midpoint charges no service fee of its own on a
                        refunded transaction.
                    </div>

                    <div>
                        <strong>6A.3</strong>
                        The deduction applies to every refund or cancellation
                        of a funded transaction, regardless of the reason for
                        the refund and regardless of which party is found to
                        be at fault. Where a seller is found at fault in a
                        dispute, the buyer may pursue recovery of this amount
                        from the seller directly; Midpoint does not act as
                        collector for it.
                    </div>

                    <div>
                        <strong>6A.4</strong>
                        The refundable amount is therefore: the product price,
                        plus any delivery amount held, minus the non-refundable
                        gateway fee and its VAT. Any cost of returning the item
                        to the seller is borne separately by the buyer.
                    </div>

                    <div>
                        <strong>6A.5</strong>
                        The applicable fee and the exact refundable amount are
                        shown to the buyer before a refund request is submitted.
                        Gateway pricing is set by the gateway and may change;
                        the rate in force at the time the payment was collected
                        applies.
                    </div>

                    <div>
                        <strong>6A.6</strong>
                        Where a transaction is cancelled before the buyer has
                        funded it, no fee arises and nothing is deducted.
                    </div>

                </div>

            </div>


            {{-- 7 --}}
            <h2 class="legal-title">
                7. Disputes
            </h2>

            <p class="legal-text">
                Disputes must be opened before the inspection window expires
                and must include supporting evidence. The seller has 48 hours
                to respond with their own evidence. Midpoint reviews both
                submissions and issues a decision, typically within 24–72
                hours. Possible outcomes are full refund, partial refund,
                replacement, or release of funds to the seller. Where a full
                refund is granted, the buyer must return the item at their own
                cost and upload proof of postage; the refund is released once
                the seller confirms the return has arrived, or once Midpoint
                is otherwise satisfied it has been returned. All refunds are
                subject to the non-refundable gateway fee deduction set out
                in clause 6A. Repeated bad-faith disputes may result in account
                suspension. Midpoint's decision is final within the platform;
                nothing in this clause removes your right to pursue legal remedies.
            </p>


            {{-- 8 --}}
            <h2 class="legal-title">
                8. Prohibited items & conduct
            </h2>

            <p class="legal-text">
                You may not use Midpoint for firearms, illegal drugs,
                counterfeit goods, stolen property, human or wildlife
                trafficking, unlicensed financial or securities products,
                or anything unlawful under Nigerian law. You may not use
                Midpoint to launder funds or to move money without a genuine
                underlying trade.
            </p>


            {{-- 9 --}}
            <h2 class="legal-title">
                9. Liability
            </h2>

            <p class="legal-text">
                Midpoint's role is limited to holding and releasing funds
                according to these terms. We are not liable for the quality,
                legality, safety or fitness of any item or service, for
                delivery failures, or for losses arising from a party's own
                breach. To the maximum extent permitted by law, our total
                liability for any transaction is limited to the amount held
                in escrow for that transaction.
            </p>


            {{-- 10 --}}
            <h2 class="legal-title">
                10. Changes & governing law
            </h2>

            <p class="legal-text">
                We may update these terms and will notify users of material
                changes. These terms are governed by the laws of the Federal
                Republic of Nigeria, and disputes are subject to the
                jurisdiction of the courts of Lagos State.
            </p>


            {{-- Links --}}
            <div
                class="mt-7 flex
                       flex-wrap gap-[10px]"
            >

                <a
                    href="{{ route('escrow-policy') }}"
                    class="mp-btn mp-btn-outline"
                >
                    Read the Escrow Policy
                </a>


                <a
                    href="{{ route('privacy-policy') }}"
                    class="mp-btn mp-btn-outline"
                >
                    Read the Privacy Policy
                </a>

            </div>

        </div>

    </section>

</div>

@endsection