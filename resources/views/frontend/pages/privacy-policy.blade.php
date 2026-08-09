@extends('frontend.layouts.app')

@section('title', 'Privacy Policy | MidPoint')

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
                Privacy Policy
            </h1>


            <p
                class="mb-2
                       text-[13px]
                       text-[#5A6660]"
            >
                Last updated: 1 August 2026 ·
                Compliant with the Nigeria Data Protection Act (NDPA) 2023
                and NDPR
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
                    Must be reviewed by a qualified data-protection lawyer
                    before launch.
                </div>

            </div>


            {{-- 1 --}}
            <h2 class="legal-title">
                1. Who we are
            </h2>

            <p class="legal-text">
                MidPoint Technologies Ltd is the data controller for personal
                data processed through midpoint.ng and the MidPoint apps.
                Contact our Data Protection Officer at privacy@midpoint.ng.
            </p>


            {{-- 2 --}}
            <h2 class="legal-title">
                2. What we collect
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
                                    Category
                                </th>

                                <th class="legal-th">
                                    Examples
                                </th>

                                <th class="legal-th">
                                    Why
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td class="legal-td font-bold">
                                    Account
                                </td>

                                <td class="legal-td">
                                    Name, phone, email, city, password hash
                                </td>

                                <td class="legal-td">
                                    To create and secure your account
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td font-bold">
                                    Transaction
                                </td>

                                <td class="legal-td">
                                    Item details, prices, delivery address,
                                    dispatch and dispute records
                                </td>

                                <td class="legal-td">
                                    To operate escrow and resolve disputes
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td font-bold">
                                    Verification (KYC)
                                </td>

                                <td class="legal-td">
                                    BVN, date of birth, bank account number
                                    and resolved account name, CAC number
                                    for businesses
                                </td>

                                <td class="legal-td">
                                    Legal obligation to verify identity
                                    before payout
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td font-bold">
                                    Evidence
                                </td>

                                <td class="legal-td">
                                    Photos, videos, chat screenshots you
                                    upload to a dispute
                                </td>

                                <td class="legal-td">
                                    To adjudicate disputes fairly
                                </td>

                            </tr>


                            <tr>

                                <td class="legal-td font-bold">
                                    Technical
                                </td>

                                <td class="legal-td">
                                    Device, IP address, log data
                                </td>

                                <td class="legal-td">
                                    Security, fraud prevention,
                                    service reliability
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- 3 --}}
            <h2 class="legal-title">
                3. How we handle your BVN
            </h2>


            <div
                class="mp-card
                       !border-[#7A5AF8]
                       !bg-[#F1EDFE]
                       p-[18px]"
            >

                <div
                    class="flex flex-col
                           gap-2
                           text-[13px]
                           leading-[1.65]
                           text-[#4B2ED6]"
                >

                    <p>
                        We collect your BVN
                        <strong>
                            only with your express, separately-recorded consent
                        </strong>,
                        and only to confirm that your payout account name
                        matches your registered identity.
                    </p>


                    <p>
                        Verification is performed by our licensed payment
                        partner, <strong>Paystack</strong>. Your BVN is
                        transmitted securely to Paystack and is
                        <strong>not stored in full</strong> on MidPoint systems
                        — we retain only a verification reference, the match
                        result, and the last four digits.
                    </p>


                    <p>
                        A BVN does not grant access to your account balance
                        or transaction history, and cannot be used to debit
                        your account.
                    </p>


                    <p>
                        We never share your BVN with buyers, sellers, or any
                        third party other than our payment partner for this
                        verification, or a regulator or court where legally
                        compelled.
                    </p>


                    <p>
                        You may withdraw consent at any time by emailing
                        privacy@midpoint.ng. Payouts will be suspended until
                        you re-verify.
                    </p>

                </div>

            </div>


            {{-- 4 --}}
            <h2 class="legal-title">
                4. Legal bases
            </h2>

            <p class="legal-text">
                We process data to perform our contract with you
                (operating escrow), to comply with legal obligations
                (KYC, AML, tax), on the basis of your consent
                (BVN capture, marketing emails), and for our legitimate
                interests (fraud prevention, service improvement).
            </p>


            {{-- 5 --}}
            <h2 class="legal-title">
                5. Who we share with
            </h2>

            <p class="legal-text">
                Payment and verification partners (Paystack), cloud hosting
                and communications providers, professional advisers, and
                regulators or law-enforcement where legally required.
                In a dispute, the evidence you submit is shared with the
                other party to the transaction so they can respond.
                We do not sell personal data.
            </p>


            {{-- 6 --}}
            <h2 class="legal-title">
                6. Retention
            </h2>

            <p class="legal-text">
                Transaction and verification records are retained for a
                minimum of seven years to meet Nigerian financial
                record-keeping and tax obligations. Dispute evidence is
                retained for the life of the case plus seven years.
                Marketing preferences are retained until you opt out.
            </p>


            {{-- 7 --}}
            <h2 class="legal-title">
                7. Your rights
            </h2>

            <p class="legal-text">
                Under the NDPA you may request access to your data,
                correction of inaccurate data, deletion (where we are not
                legally required to retain it), restriction of processing,
                portability, and withdrawal of consent. Email
                privacy@midpoint.ng; we respond within 30 days.
                You may also complain to the Nigeria Data Protection Commission.
            </p>


            {{-- 8 --}}
            <h2 class="legal-title">
                8. Security
            </h2>

            <p class="legal-text">
                Data is encrypted in transit and at rest, access is
                restricted on a need-to-know basis, and we log
                administrative access. No system is perfectly secure;
                please use a strong, unique password and enable
                two-factor authentication.
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
                    href="{{ route('escrow-policy') }}"
                    class="mp-btn mp-btn-outline"
                >
                    Read the Escrow Policy
                </a>

            </div>

        </div>

    </section>

</div>

@endsection