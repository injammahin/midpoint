<header
    id="public-header"
    class="sticky top-0 z-[80]
           border-b border-[#E4EAE6]
           bg-[#F6F9F7]/95
           backdrop-blur-xl"
>

    {{-- =========================================================
        MAIN HEADER
    ========================================================== --}}
    <div
        class="mx-auto flex
               h-[66px]
               max-w-[1160px]
               items-center
               gap-[26px]
               px-[16px]
               sm:px-[22px]"
    >

        {{-- =====================================================
            LOGO
        ====================================================== --}}
        <a
            href="{{ route('home') }}"
            class="flex shrink-0
                   items-center gap-[9px]
                   font-['Bricolage_Grotesque']
                   text-[20px]
                   font-extrabold
                   leading-none"
        >

            <span
                class="grid h-8 w-8
                       place-items-center
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
                <span class="text-[#0B3D2E]">
                    Mid
                </span><span class="text-[#7A5AF8]">Point</span>
            </span>

        </a>


        {{-- =====================================================
            DESKTOP NAVIGATION
        ====================================================== --}}
        <nav
            class="ml-2 hidden
                   items-center
                   gap-1
                   lg:flex"
            aria-label="Main navigation"
        >

            {{-- About --}}
            <a
                href="{{ route('about') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('about')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                About
            </a>


            {{-- How it works --}}
            <a
                href="{{ route('how-it-works') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('how-it-works')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                How it works
            </a>


            {{-- Pricing --}}
            <a
                href="{{ route('pricing') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('pricing')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                Pricing
            </a>


            {{-- Featured Businesses --}}
            <a
                href="{{ route('featured-businesses') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('featured-businesses')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                Featured businesses
            </a>


            {{-- For Sellers --}}
            <a
                href="{{ route('verified-sellers') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('verified-sellers')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                For sellers
            </a>


            {{-- FAQs --}}
            <a
                href="{{ route('faqs') }}"
                class="rounded-[10px]
                       px-[13px] py-2
                       text-[14px]
                       font-medium
                       transition
                       duration-150
                       {{
                            request()->routeIs('faqs')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#5A6660] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >
                FAQs
            </a>

        </nav>


        {{-- =====================================================
            DESKTOP ACCOUNT ACTIONS
        ====================================================== --}}
        <div
            class="ml-auto hidden
                   items-center
                   gap-[10px]
                   lg:flex"
        >

            @auth

                {{-- =============================================
                    AUTHENTICATED USER
                ============================================== --}}

                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex
                           items-center
                           justify-center
                           gap-[7px]
                           rounded-xl
                           px-5 py-[11px]
                           text-[14px]
                           font-semibold
                           text-[#5A6660]
                           transition
                           duration-150
                           hover:bg-[#E8F7EF]
                           hover:text-[#0B3D2E]"
                >

                    <i class="fa-solid fa-gauge-high text-[12px]"></i>

                    Dashboard

                </a>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="m-0"
                >
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex
                               items-center
                               justify-center
                               gap-[7px]
                               rounded-xl
                               border-0
                               bg-[#0B3D2E]
                               px-5 py-[11px]
                               text-[14px]
                               font-semibold
                               text-white
                               transition
                               duration-150
                               hover:-translate-y-px
                               hover:bg-[#0E4A38]
                               hover:shadow-[0_6px_24px_-8px_rgba(11,61,46,.3)]"
                    >

                        <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>

                        Log out

                    </button>

                </form>

            @else

                {{-- =============================================
                    GUEST
                ============================================== --}}

                <a
                    href="{{ route('login') }}"
                    class="inline-flex
                           items-center
                           justify-center
                           rounded-xl
                           px-5 py-[11px]
                           text-[14.5px]
                           font-semibold
                           text-[#5A6660]
                           transition
                           duration-150
                           hover:text-[#0B3D2E]"
                >
                    Log in
                </a>


                <a
                    href="{{ route('register') }}"
                    class="inline-flex
                           items-center
                           justify-center
                           rounded-xl
                           bg-[#0B3D2E]
                           px-5 py-[11px]
                           text-[14.5px]
                           font-semibold
                           text-white
                           transition
                           duration-150
                           hover:-translate-y-px
                           hover:bg-[#0E4A38]
                           hover:shadow-[0_6px_24px_-8px_rgba(11,61,46,.3)]"
                >
                    Create free account
                </a>

            @endauth

        </div>


        {{-- =====================================================
            MOBILE HAMBURGER
        ====================================================== --}}
        <button
            type="button"
            id="mobile-menu-button"
            class="ml-auto grid
                   h-10 w-10
                   place-items-center
                   rounded-[10px]
                   border border-[#DDE6E1]
                   bg-[#E8F7EF]
                   text-[#0B3D2E]
                   transition
                   hover:bg-[#D8F1E3]
                   focus:outline-none
                   focus:ring-2
                   focus:ring-[#12B76A]/30
                   lg:hidden"
            aria-label="Open navigation menu"
            aria-controls="mobile-menu"
            aria-expanded="false"
        >

            {{-- Hamburger --}}
            <svg
                id="menu-open-icon"
                xmlns="http://www.w3.org/2000/svg"
                class="h-[19px] w-[19px]"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >

                <line
                    x1="4"
                    y1="6"
                    x2="20"
                    y2="6"
                />

                <line
                    x1="4"
                    y1="12"
                    x2="20"
                    y2="12"
                />

                <line
                    x1="4"
                    y1="18"
                    x2="20"
                    y2="18"
                />

            </svg>


            {{-- Close --}}
            <svg
                id="menu-close-icon"
                xmlns="http://www.w3.org/2000/svg"
                class="hidden
                       h-[19px]
                       w-[19px]"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >

                <line
                    x1="18"
                    y1="6"
                    x2="6"
                    y2="18"
                />

                <line
                    x1="6"
                    y1="6"
                    x2="18"
                    y2="18"
                />

            </svg>

        </button>

    </div>

</header>


{{-- =========================================================
    MOBILE MENU OVERLAY
========================================================== --}}
<div
    id="mobile-menu-overlay"
    class="pointer-events-none
           invisible
           fixed inset-x-0
           bottom-0 top-[66px]
           z-[60]
           bg-[#061A14]/45
           opacity-0
           backdrop-blur-[2px]
           transition-all
           duration-300
           lg:hidden"
></div>


{{-- =========================================================
    MOBILE SLIDE DRAWER
========================================================== --}}
<aside
    id="mobile-menu"
    class="fixed
           bottom-0
           right-0
           top-[66px]
           z-[70]
           flex
           w-[min(88vw,360px)]
           translate-x-full
           flex-col
           border-l border-[#E4EAE6]
           bg-[#F9FBFA]
           shadow-[-12px_0_40px_rgba(11,61,46,.12)]
           transition-transform
           duration-300
           ease-out
           lg:hidden"
    aria-label="Mobile navigation"
>

    {{-- =====================================================
        DRAWER HEADER
    ====================================================== --}}
    <div
        class="flex
               items-center
               justify-between
               border-b border-[#E4EAE6]
               px-5 py-4"
    >

        <div>

            <div
                class="font-['Bricolage_Grotesque']
                       text-[15px]
                       font-bold
                       text-[#0B3D2E]"
            >
                Menu
            </div>

            <div
                class="mt-[2px]
                       text-[11px]
                       text-[#83908A]"
            >

                @auth

                    Hi, {{ auth()->user()->name }}

                @else

                    Navigate MidPoint

                @endauth

            </div>

        </div>


        <button
            type="button"
            id="mobile-menu-close-button"
            class="grid
                   h-9 w-9
                   place-items-center
                   rounded-[10px]
                   border border-[#E4EAE6]
                   bg-white
                   text-[#5A6660]
                   transition
                   hover:bg-[#E8F7EF]
                   hover:text-[#0B3D2E]"
            aria-label="Close menu"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>


    {{-- =====================================================
        MOBILE MENU LINKS
    ====================================================== --}}
    <nav
        class="flex-1
               overflow-y-auto
               px-4 py-4"
    >

        <div class="flex flex-col gap-[4px]">

            {{-- =================================================
                About
            ================================================== --}}
            <a
                href="{{ route('about') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('about')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#E8F7EF]
                           text-[12px]
                           text-[#0E7A4C]"
                >
                    <i class="fa-regular fa-building"></i>
                </span>

                About

            </a>


            {{-- =================================================
                How It Works
            ================================================== --}}
            <a
                href="{{ route('how-it-works') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('how-it-works')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#F1EDFE]
                           text-[12px]
                           text-[#7A5AF8]"
                >
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </span>

                How it works

            </a>


            {{-- =================================================
                Pricing
            ================================================== --}}
            <a
                href="{{ route('pricing') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('pricing')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#E8F7EF]
                           text-[12px]
                           text-[#0E7A4C]"
                >
                    <i class="fa-solid fa-tags"></i>
                </span>

                Pricing

            </a>


            {{-- =================================================
                Featured Businesses
            ================================================== --}}
            <a
                href="{{ route('featured-businesses') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('featured-businesses')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#F1EDFE]
                           text-[12px]
                           text-[#7A5AF8]"
                >
                    <i class="fa-solid fa-store"></i>
                </span>

                Featured businesses

            </a>


            {{-- =================================================
                For Sellers
            ================================================== --}}
            <a
                href="{{ route('verified-sellers') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('verified-sellers')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#E8F7EF]
                           text-[12px]
                           text-[#0E7A4C]"
                >
                    <i class="fa-solid fa-certificate"></i>
                </span>

                For sellers

            </a>


            {{-- =================================================
                FAQ
            ================================================== --}}
            <a
                href="{{ route('faqs') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('faqs')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#F1EDFE]
                           text-[12px]
                           text-[#7A5AF8]"
                >
                    <i class="fa-regular fa-circle-question"></i>
                </span>

                FAQs

            </a>


            {{-- =================================================
                SEPARATOR
            ================================================== --}}
            <div
                class="my-2
                       border-t
                       border-[#E4EAE6]"
            ></div>


            {{-- =================================================
                Contact
            ================================================== --}}
            <a
                href="{{ route('contact') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('contact*')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#E8F7EF]
                           text-[12px]
                           text-[#0E7A4C]"
                >
                    <i class="fa-regular fa-envelope"></i>
                </span>

                Contact

            </a>


            {{-- =================================================
                Support
            ================================================== --}}
            <a
                href="{{ route('support') }}"
                class="mobile-public-nav-link
                       flex items-center
                       gap-3
                       rounded-[11px]
                       px-3 py-[11px]
                       text-[14px]
                       font-semibold
                       transition
                       {{
                            request()->routeIs('support')
                                ? 'bg-[#E8F7EF] text-[#0B3D2E]'
                                : 'text-[#4F5E57] hover:bg-[#E8F7EF] hover:text-[#0B3D2E]'
                       }}"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[9px]
                           bg-[#F1EDFE]
                           text-[12px]
                           text-[#7A5AF8]"
                >
                    <i class="fa-solid fa-headset"></i>
                </span>

                Support Centre

            </a>

        </div>

    </nav>


    {{-- =====================================================
        MOBILE ACCOUNT ACTIONS
    ====================================================== --}}
    <div
        class="border-t
               border-[#E4EAE6]
               bg-white
               p-4"
    >

        @auth

            {{-- =============================================
                AUTHENTICATED USER
            ============================================== --}}

            <div
                class="mb-3
                       flex items-center
                       gap-3
                       rounded-xl
                       bg-[#F6F9F7]
                       p-3"
            >

                {{-- User Avatar --}}
                <div
                    class="grid
                           h-9 w-9
                           shrink-0
                           place-items-center
                           rounded-[10px]
                           bg-[#E8F7EF]
                           font-bold
                           text-[#0E7A4C]"
                >
                    {{
                        strtoupper(
                            mb_substr(
                                auth()->user()->name ?? 'U',
                                0,
                                1
                            )
                        )
                    }}
                </div>


                {{-- User Information --}}
                <div class="min-w-0">

                    <strong
                        class="block
                               truncate
                               text-[12px]
                               text-[#0B3D2E]"
                    >
                        {{ auth()->user()->name }}
                    </strong>


                    <span
                        class="block
                               truncate
                               text-[10px]
                               text-[#83908A]"
                    >

                        {{-- =====================================
                            ADMIN / STAFF / NORMAL USER LABEL
                        ====================================== --}}

                        @if(auth()->user()->canAccessAdminPanel())

                            {{
                                auth()->user()->isAdmin()
                                    ? 'Administrator'
                                    : 'Admin User'
                            }}

                        @elseif(!auth()->user()->hasVerifiedEmail())

                            Email verification pending

                        @else

                            {{
                                ucfirst(
                                    auth()->user()->preferred_role
                                    ?? 'user'
                                )
                            }} account

                        @endif

                    </span>

                </div>

            </div>


            {{-- =============================================
                DASHBOARD
            ============================================== --}}
            <a
                href="{{ route('dashboard') }}"
                class="mb-2
                       flex w-full
                       items-center
                       justify-center
                       gap-2
                       rounded-xl
                       border
                       border-[#DDE6E1]
                       bg-white
                       px-5 py-[11px]
                       text-[14px]
                       font-semibold
                       text-[#0B3D2E]
                       transition
                       hover:bg-[#E8F7EF]"
            >

                <i class="fa-solid fa-gauge-high text-[12px]"></i>

                Dashboard

            </a>


            {{-- =============================================
                LOGOUT
            ============================================== --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf


                <button
                    type="submit"
                    class="flex w-full
                           items-center
                           justify-center
                           gap-2
                           rounded-xl
                           border-0
                           bg-[#0B3D2E]
                           px-5 py-[11px]
                           text-[14px]
                           font-semibold
                           text-white
                           transition
                           hover:bg-[#0E4A38]"
                >

                    <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i>

                    Log out

                </button>

            </form>


        @else

            {{-- =============================================
                GUEST
            ============================================== --}}

            <a
                href="{{ route('login') }}"
                class="mb-2
                       flex w-full
                       items-center
                       justify-center
                       rounded-xl
                       border
                       border-[#DDE6E1]
                       bg-white
                       px-5 py-[11px]
                       text-[14px]
                       font-semibold
                       text-[#0B3D2E]
                       transition
                       hover:bg-[#E8F7EF]"
            >
                Log in
            </a>


            <a
                href="{{ route('register') }}"
                class="flex w-full
                       items-center
                       justify-center
                       rounded-xl
                       bg-[#0B3D2E]
                       px-5 py-[11px]
                       text-[14px]
                       font-semibold
                       text-white
                       transition
                       hover:bg-[#0E4A38]"
            >
                Create free account
            </a>

        @endauth

    </div>

</aside>