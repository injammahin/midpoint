<footer
    class="bg-[#0D120F] pb-[30px] pt-[60px] text-[#B9C4BE]"
>

    <div
        class="mx-auto max-w-[1160px] px-[22px]"
    >

        {{-- ============================
            MAIN FOOTER GRID
        ============================= --}}
        <div
            class="grid grid-cols-1 gap-[30px]
                   border-b border-[#232A26]
                   pb-9
                   sm:grid-cols-2
                   lg:grid-cols-[1.5fr_1fr_1fr_1fr_1fr]"
        >

            {{-- ===================================
                BRAND COLUMN
            ==================================== --}}
            <div>

                <a
                    href="{{ url('/') }}"
                    class="mb-3 inline-flex items-center gap-[9px]
                           font-['Bricolage_Grotesque']
                           text-[20px] font-extrabold"
                >

                    <span
                        class="grid h-8 w-8 place-items-center
                               rounded-[10px]
                               bg-gradient-to-br
                               from-[#0B3D2E]
                               to-[#12B76A]
                               text-[15px]
                               font-extrabold
                               text-white"
                    >
                        M
                    </span>

                    <span>
                        <span class="text-white">Mid</span><span class="text-[#C4B5FD]">Point</span>
                    </span>

                </a>


                <p
                    class="max-w-[280px] text-[13px] leading-[1.6] text-[#8E9B94]"
                >
                    The trusted middle for online transactions in Nigeria.
                    Buy with confidence. Sell with confidence.
                </p>


                {{-- Store buttons --}}
                <div
                    class="mt-5 flex flex-wrap gap-[10px]"
                >

                    {{-- App Store --}}
                    <a
                        href="javascript:void(0)"
                        class="inline-flex items-center gap-[9px]
                               rounded-xl border border-[#2E3833]
                               bg-[#1A211D]
                               px-[14px] py-2
                               transition duration-150
                               hover:-translate-y-px
                               hover:border-[#7EF0B6]
                               hover:bg-[#202823]"
                    >

                        <span
                            class="text-[20px] leading-none text-[#7EF0B6]"
                        >
                            &#63743;
                        </span>

                        <span>
                            <small
                                class="block text-[9.5px] leading-[1.3]
                                       tracking-[0.04em]
                                       text-[#8E9B94]"
                            >
                                Download on the
                            </small>

                            <strong
                                class="block font-['Bricolage_Grotesque']
                                       text-[14px]
                                       leading-[1.2]
                                       text-white"
                            >
                                App Store
                            </strong>
                        </span>

                    </a>


                    {{-- Google Play --}}
                    <a
                        href="javascript:void(0)"
                        class="inline-flex items-center gap-[9px]
                               rounded-xl border border-[#2E3833]
                               bg-[#1A211D]
                               px-[14px] py-2
                               transition duration-150
                               hover:-translate-y-px
                               hover:border-[#7EF0B6]
                               hover:bg-[#202823]"
                    >

                        <span
                            class="text-[18px] leading-none text-[#7EF0B6]"
                        >
                            ▶
                        </span>

                        <span>
                            <small
                                class="block text-[9.5px]
                                       leading-[1.3]
                                       tracking-[0.04em]
                                       text-[#8E9B94]"
                            >
                                Get it on
                            </small>

                            <strong
                                class="block font-['Bricolage_Grotesque']
                                       text-[14px]
                                       leading-[1.2]
                                       text-white"
                            >
                                Google Play
                            </strong>
                        </span>

                    </a>

                </div>

            </div>


            {{-- ===================================
                PRODUCT
            ==================================== --}}
            <div>

                <h4
                    class="mb-[14px]
                           font-['Bricolage_Grotesque']
                           text-[14px]
                           font-bold
                           text-white"
                >
                    Product
                </h4>

                <div class="flex flex-col">

                    <a
                        href="{{ url('/how-it-works') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               transition hover:text-[#7EF0B6]"
                    >
                        How it works
                    </a>

                    <a
                        href="{{ url('/pricing') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               transition hover:text-[#7EF0B6]"
                    >
                        Pricing
                    </a>

                    <a
                        href="{{ url('/featured-businesses') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               transition hover:text-[#7EF0B6]"
                    >
                        Featured businesses
                    </a>

                    <a
                        href="{{ url('/verified-sellers') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               transition hover:text-[#7EF0B6]"
                    >
                        Become a verified seller
                    </a>

                    <a
                        href="{{ url('/faqs') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               transition hover:text-[#7EF0B6]"
                    >
                        FAQs
                    </a>

                </div>

            </div>


            {{-- ===================================
                COMPANY
            ==================================== --}}
            <div>

                <h4
                    class="mb-[14px]
                           font-['Bricolage_Grotesque']
                           text-[14px]
                           font-bold text-white"
                >
                    Company
                </h4>

                <div class="flex flex-col">

                    <a
                        href="{{ url('/about') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        About
                    </a>

                    <a
                        href="{{ url('/contact') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Contact
                    </a>

                    <a
                        href="{{ url('/support') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Support Centre
                    </a>

                </div>

            </div>


            {{-- ===================================
                LEGAL
            ==================================== --}}
            <div>

                <h4
                    class="mb-[14px]
                           font-['Bricolage_Grotesque']
                           text-[14px]
                           font-bold text-white"
                >
                    Legal
                </h4>

                <div class="flex flex-col">

                    <a
                        href="{{ url('/terms-and-conditions') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Terms & Conditions
                    </a>

                    <a
                        href="{{ url('/privacy-policy') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Privacy Policy
                    </a>

                    <a
                        href="{{ url('/escrow-policy') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Escrow Policy
                    </a>

                    <a
                        href="{{ url('/faqs') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        FAQs
                    </a>

                </div>

            </div>


            {{-- ===================================
                ACCOUNT
            ==================================== --}}
            <div>

                <h4
                    class="mb-[14px]
                           font-['Bricolage_Grotesque']
                           text-[14px]
                           font-bold text-white"
                >
                    Account
                </h4>

                <div class="flex flex-col">

                    <a
                        href="{{ url('/login') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Log in
                    </a>

                    <a
                        href="{{ url('/register') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Register
                    </a>

                    <a
                        href="{{ url('/seller/dashboard') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Seller dashboard
                    </a>

                    <a
                        href="{{ url('/user/dashboard') }}"
                        class="py-1 text-[13.5px] text-[#8E9B94]
                               hover:text-[#7EF0B6]"
                    >
                        Buyer dashboard
                    </a>

                </div>

            </div>

        </div>


        {{-- ============================
            FOOTER BOTTOM
        ============================= --}}
        <div
            class="flex flex-col gap-[10px]
                   pt-[22px]
                   text-[12.5px]
                   text-[#6E7A74]
                   md:flex-row
                   md:items-center
                   md:justify-between"
        >

            <span>
                © {{ date('Y') }} MidPoint Technologies Ltd. Lagos, Nigeria.
            </span>


            <div
                class="flex flex-wrap gap-[14px]"
            >

                <a
                    href="{{ url('/terms-and-conditions') }}"
                    class="transition hover:text-[#7EF0B6]"
                >
                    Terms & Conditions
                </a>

                <a
                    href="{{ url('/privacy-policy') }}"
                    class="transition hover:text-[#7EF0B6]"
                >
                    Privacy Policy
                </a>

                <a
                    href="{{ url('/escrow-policy') }}"
                    class="transition hover:text-[#7EF0B6]"
                >
                    Escrow Policy
                </a>

            </div>

        </div>

    </div>

</footer>