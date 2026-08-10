<aside
    id="buyerMainSidebar"
    class="buyer-main-sidebar"
>


    {{-- =========================================================
        BUYER MENU
    ========================================================== --}}

    <div class="buyer-sidebar-section-title">

        Buyer Menu

    </div>



    <nav class="buyer-sidebar-nav">


        {{-- =====================================================
            DASHBOARD
        ====================================================== --}}

        <a
            href="{{ route('buyer.dashboard') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'buyer.dashboard'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-house"></i>

            </span>


            <span class="buyer-sidebar-label">

                Dashboard

            </span>

        </a>



        {{-- =====================================================
            TRANSACTIONS
        ====================================================== --}}

        <a
            href="{{ route('buyer.transactions') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'buyer.transactions*'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-file-lines"></i>

            </span>


            <span class="buyer-sidebar-label">

                Transactions

            </span>

        </a>



        {{-- =====================================================
            NOTIFICATIONS
        ====================================================== --}}

        <a
            href="{{ route('buyer.notifications') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'buyer.notifications*'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-bell"></i>

            </span>


            <span class="buyer-sidebar-label">

                Notifications

            </span>

        </a>



        {{-- =====================================================
            FEATURED BUSINESSES
        ====================================================== --}}

        <a
            href="{{ route('featured-businesses') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'featured-businesses'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-store"></i>

            </span>


            <span class="buyer-sidebar-label">

                Featured businesses

            </span>

        </a>



        {{-- =====================================================
            PROFILE SETTINGS
        ====================================================== --}}

        <a
            href="{{ route('buyer.profile-settings') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'buyer.profile-settings*'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-gear"></i>

            </span>


            <span class="buyer-sidebar-label">

                Profile settings

            </span>

        </a>



        {{-- =====================================================
            SUPPORT
        ====================================================== --}}

        <a
            href="{{ route('support') }}"

            class="
                buyer-sidebar-link

                {{
                    request()->routeIs(
                        'support'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-regular fa-comments"></i>

            </span>


            <span class="buyer-sidebar-label">

                Support

            </span>

        </a>

    </nav>



    {{-- =========================================================
        SWITCH
    ========================================================== --}}

    <div
        class="
            buyer-sidebar-section-title
            buyer-sidebar-switch-title
        "
    >

        Switch

    </div>



    {{-- =========================================================
        SELLER VIEW
    ========================================================== --}}

    <form
        method="POST"
        action="{{ route('account.switch', 'seller') }}"
    >

        @csrf


        <button
            type="submit"
            class="buyer-sidebar-link"
        >

            <span class="buyer-sidebar-icon">

                <i
                    class="
                        fa-solid
                        fa-arrow-right-arrow-left
                    "
                ></i>

            </span>


            <span class="buyer-sidebar-label">

                Seller view

            </span>

        </button>

    </form>



    {{-- =========================================================
        LOGOUT
    ========================================================== --}}

    <form
        method="POST"
        action="{{ route('logout') }}"
    >

        @csrf


        <button
            type="submit"

            class="
                buyer-sidebar-link
                buyer-sidebar-logout
            "
        >

            <span class="buyer-sidebar-icon">

                <i
                    class="
                        fa-solid
                        fa-arrow-right-from-bracket
                    "
                ></i>

            </span>


            <span class="buyer-sidebar-label">

                Log out

            </span>

        </button>

    </form>

</aside>