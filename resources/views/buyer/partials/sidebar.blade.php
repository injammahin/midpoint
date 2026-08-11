@php

    $sidebarUser =
        auth()->user();


    $buyerUnreadNotificationCount =
        0;


    if ($sidebarUser) {

        $buyerUnreadNotificationCount =
            \App\Models\TransactionNotification::query()

                ->where(
                    'user_id',
                    $sidebarUser->id
                )

                ->where(
                    'audience',
                    'buyer'
                )

                ->whereNull(
                    'read_at'
                )

                ->count();
    }


    $buyerDashboardActive =
        request()->routeIs(
            'buyer.dashboard'
        );


    $buyerTransactionsActive =
        request()->routeIs(
            'buyer.transactions'
        )
        ||
        request()->routeIs(
            'buyer.transactions.show'
        )
        ||
        request()->routeIs(
            'buyer.transactions.invoice'
        );


    $buyerNotificationsActive =
        request()->routeIs(
            'buyer.notifications*'
        );


    $buyerFeaturedBusinessesActive =
        request()->routeIs(
            'featured-businesses'
        )
        ||
        request()->routeIs(
            'featured-businesses.show'
        );


    $buyerProfileSettingsActive =
        request()->routeIs(
            'buyer.profile-settings*'
        );


    $buyerSupportActive =
        request()->routeIs(
            'support'
        )
        ||
        request()->routeIs(
            'support.*'
        );

@endphp



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

                {{ $buyerDashboardActive ? 'active' : '' }}
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

                {{ $buyerTransactionsActive ? 'active' : '' }}
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

                {{ $buyerNotificationsActive ? 'active' : '' }}
            "
        >

            <span class="buyer-sidebar-icon">

                <i class="fa-solid fa-bell"></i>

            </span>


            <span class="buyer-sidebar-label">

                Notifications

            </span>


            @if($buyerUnreadNotificationCount > 0)

                <span
                    class="buyer-sidebar-notification-badge"

                    title="{{
                        $buyerUnreadNotificationCount
                    }} unread notification(s)"
                >

                    {{
                        $buyerUnreadNotificationCount > 99
                            ? '99+'
                            : $buyerUnreadNotificationCount
                    }}

                </span>

            @endif

        </a>



        {{-- =====================================================
            FEATURED BUSINESSES
        ====================================================== --}}

        <a
            href="{{ route('featured-businesses') }}"

            class="
                buyer-sidebar-link

                {{ $buyerFeaturedBusinessesActive ? 'active' : '' }}
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

                {{ $buyerProfileSettingsActive ? 'active' : '' }}
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

                {{ $buyerSupportActive ? 'active' : '' }}
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

                <i class="fa-solid fa-arrow-right-arrow-left"></i>

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

                <i class="fa-solid fa-arrow-right-from-bracket"></i>

            </span>


            <span class="buyer-sidebar-label">

                Log out

            </span>

        </button>

    </form>

</aside>



<style>

/*
|--------------------------------------------------------------------------
| Buyer Notification Badge
|--------------------------------------------------------------------------
*/

.buyer-sidebar-notification-badge {
    min-width: 20px;
    height: 20px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-left: auto;

    padding: 0 6px;

    border: 2px solid #FFFFFF;
    border-radius: 999px;

    background: #F04438;

    color: #FFFFFF;

    font-size: 8px;
    font-weight: 800;

    line-height: 1;
}


/*
|--------------------------------------------------------------------------
| Notification Badge Inside Active Menu
|--------------------------------------------------------------------------
*/

.buyer-sidebar-link.active
.buyer-sidebar-notification-badge {
    border-color: #0B4B3C;

    background: #FFFFFF;

    color: #0B4B3C;
}


/*
|--------------------------------------------------------------------------
| Link Position
|--------------------------------------------------------------------------
*/

.buyer-sidebar-link {
    position: relative;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width: 1024px) {

    .buyer-sidebar-notification-badge {
        min-width: 18px;
        height: 18px;

        padding: 0 5px;

        font-size: 7px;
    }

}

</style>