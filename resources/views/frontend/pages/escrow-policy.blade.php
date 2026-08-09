@extends('frontend.layouts.app')

@section('title', 'Escrow Policy | MidPoint')

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
                Escrow Policy
            </h1>


            <p
                class="mb-2
                       text-[13px]
                       text-[#5A6660]"
            >
                Last updated: 1 August 2026 ·
                How MidPoint holds, releases and refunds funds
            </p>


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

                    Illustrative content for design purposes only.
                    Requires legal and regulatory review before launch.
                </div>

            </div>


            {{-- 1 --}}
            <h2 class="legal-title">
                1. How funds are held
            </h2>

            <p class="legal-text">
                Buyer payments are collected by our licensed payment partner
                and held in a segregated client account. Held funds are not
                MidPoint's property, are not used for our operating expenses,
                and are not lent or invested. Each amount is ring-fenced
                against a specific transaction reference.
            </p>


            {{-- 2 --}}
            <h2 class="legal-title">
                2. What is held
            </h2>

            <p class="legal-text">
                The product price plus any delivery amount entered by the
                buyer at checkout. Both are released together on the same
                triggers.
            </p>


            {{-- 3 --}}
            <h2 class="legal-title">
                3. Release triggers
            </h2>


            <div
                class="mp-card
                       mt-[10px]
                       overflow-hidden"
            >

                <div class="overflow-x-auto">

                    <table
                        class="min-w-[680px]
                               w-full
                               border-collapse
                               text-[14px]"
                    >

                        <thead>

                            <tr>

                                <th class="legal-th">
                                    Trigger
                                </th>

                                <th class="legal-th">
                                    Outcome
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td class="legal-td">
                                    Buyer confirms receipt and accepts the item
                                </td>

                                <td class="legal-td">
                                    Immediate release to seller,
                                    less the 5% fee and VAT
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    8-hour inspection window expires
                                    with no action
                                </td>

                                <td class="legal-td">
                                    Automatic release to seller
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    Buyer never confirms receipt,
                                    7 days after dispatch
                                </td>

                                <td class="legal-td">
                                    Discretionary release to seller on
                                    satisfactory proof of delivery, after
                                    at least two buyer notifications
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    Dispute opened before window expiry
                                </td>

                                <td class="legal-td">
                                    Auto-release suspended; funds locked
                                    pending resolution
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    Seller fails to dispatch within
                                    a reasonable period
                                </td>

                                <td class="legal-td">
                                    Transaction cancelled; refund to buyer
                                    less the non-refundable processing fee
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    Both parties mutually cancel after funding
                                </td>

                                <td class="legal-td">
                                    Refund to buyer less the non-refundable
                                    processing fee
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td">
                                    Cancelled before the buyer funds it
                                </td>

                                <td class="legal-td">
                                    No fee arises; nothing deducted
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- 4 --}}
            <h2 class="legal-title">
                4. Refunds
            </h2>

            <p class="legal-text">
                Approved refunds return the amount held — the product price
                plus any delivery amount — to the buyer's original payment
                method, typically within 5–10 business days depending on the
                bank,
                <strong>
                    less the non-refundable payment processing fee.
                </strong>
            </p>


            <div
                class="mp-card
                       mt-3
                       !border-[#F79009]
                       !bg-[#FEF4E6]
                       p-[18px]"
            >

                <strong
                    class="text-[13px]
                           text-[#B54708]"
                >
                    Non-refundable processing fee
                </strong>


                <p
                    class="mt-2
                           text-[13px]
                           leading-[1.65]
                           text-[#B54708]"
                >
                    The payment gateway charges a processing fee when the
                    buyer funds a transaction — currently
                    <strong>
                        1.5% of the amount paid, capped at ₦2,000,
                        plus 7.5% VAT
                    </strong>.
                    The gateway does not return this fee when a payment is
                    reversed, so MidPoint cannot refund it. It is deducted
                    from the buyer's refund total on every refund or
                    cancellation of a funded transaction, whatever the
                    reason and whoever is at fault. MidPoint charges no
                    service fee of its own on a refunded transaction.
                </p>


                <hr
                    class="my-4
                           border-0
                           border-t
                           border-[#F0D9B5]"
                >


                <strong
                    class="text-[13px]
                           text-[#B54708]"
                >
                    Worked example — ₦145,000 transaction
                </strong>


                <div
                    class="mt-2
                           flex flex-col gap-1
                           text-[13px]
                           text-[#B54708]"
                >

                    <div
                        class="flex justify-between gap-4"
                    >
                        <span>
                            Amount held
                        </span>

                        <strong>
                            ₦145,000.00
                        </strong>
                    </div>


                    <div
                        class="flex justify-between gap-4"
                    >
                        <span>
                            Processing fee (1.5%, capped)
                        </span>

                        <strong>
                            − ₦2,000.00
                        </strong>
                    </div>


                    <div
                        class="flex justify-between gap-4"
                    >
                        <span>
                            VAT on processing fee (7.5%)
                        </span>

                        <strong>
                            − ₦150.00
                        </strong>
                    </div>


                    <div
                        class="mt-1 flex
                               justify-between gap-4
                               border-t
                               border-[#F0D9B5]
                               pt-[5px]"
                    >

                        <strong>
                            Refunded to buyer
                        </strong>

                        <strong>
                            ₦142,850.00
                        </strong>

                    </div>

                </div>

            </div>


            <p class="legal-text mt-3">
                Where a full refund follows a dispute, the buyer bears the
                cost of returning the item and must upload proof of postage;
                the refund is released once the seller confirms receipt of
                the return or MidPoint is otherwise satisfied it was returned.
                Where a transaction is cancelled before the buyer has funded
                it, no fee arises and nothing is deducted.
            </p>


            {{-- 5 --}}
            <h2 class="legal-title">
                5. Deductions at release
            </h2>

            <p class="legal-text">
                On release to a seller we deduct the 5% MidPoint Service Fee
                and 7.5% VAT on that fee. No deduction is applied to the
                delivery amount, which passes through to the seller in full.
                Buyers are never charged a MidPoint service fee. On a refund,
                no MidPoint service fee is charged; only the gateway processing
                fee described in section 4 is deducted.
            </p>


            {{-- 6 --}}
            <h2 class="legal-title">
                6. Payout eligibility
            </h2>

            <p class="legal-text">
                Sellers must have completed identity verification, including
                a successful BVN-to-account-name match, before any payout is
                made. Where verification fails or is withdrawn, funds remain
                held and the transaction may be refunded to the buyer.
            </p>


            {{-- 7 --}}
            <h2 class="legal-title">
                7. Unclaimed funds
            </h2>

            <p class="legal-text">
                Where funds cannot be released or refunded because a party is
                unreachable or a payout account cannot be verified, we hold
                the funds and attempt contact for 180 days, after which they
                are handled in accordance with Nigerian unclaimed-property
                and financial regulations.
            </p>


            {{-- 8 --}}
            <h2 class="legal-title">
                8. Limits & suspension
            </h2>

            <p class="legal-text">
                We may impose transaction limits, delay a release, or freeze
                funds where we have reasonable grounds to suspect fraud,
                money laundering, prohibited goods, or a breach of these
                policies, and where required to comply with a lawful order.
            </p>


            <div
                class="mt-7 flex
                       flex-wrap gap-[10px]"
            >

                <a
                    href="{{ route('terms-and-conditions') }}"
                    class="mp-btn mp-btn-outline"
                >
                    Read the Terms & Conditions
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