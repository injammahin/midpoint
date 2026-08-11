@php

    $sidebarUser = auth()->user();

    $sidebarNow = now();

    $sidebarLatestPlan = null;

    $sidebarActivePlan = null;

    $sellerUnreadNotificationCount = 0;


    if ($sidebarUser) {

        $sidebarLatestPlan =
            \App\Models\SellerSubscription::query()
                ->where(
                    'user_id',
                    $sidebarUser->id
                )
                ->latest('id')
                ->first();


        $sidebarActivePlan =
            \App\Models\SellerSubscription::query()
                ->where(
                    'user_id',
                    $sidebarUser->id
                )
                ->where(
                    'status',
                    \App\Models\SellerSubscription::STATUS_ACTIVE
                )
                ->where(function ($query) use ($sidebarNow) {

                    $query
                        ->whereNull('expires_at')
                        ->orWhere(
                            'expires_at',
                            '>',
                            $sidebarNow
                        );

                })
                ->latest('id')
                ->first();


        $sellerUnreadNotificationCount =
            \App\Models\TransactionNotification::query()
                ->where(
                    'user_id',
                    $sidebarUser->id
                )
                ->where(
                    'audience',
                    'seller'
                )
                ->whereNull(
                    'read_at'
                )
                ->count();
    }


    $sellerProductsUnlocked =
        !is_null(
            $sidebarActivePlan
        );


    $sellerPlanExpired =
        false;


    if (!$sellerProductsUnlocked && $sidebarLatestPlan) {

        $sellerPlanExpired =
            $sidebarLatestPlan->status
            ===
            \App\Models\SellerSubscription::STATUS_EXPIRED

            ||

            (
                $sidebarLatestPlan->expires_at
                &&
                $sidebarLatestPlan
                    ->expires_at
                    ->lte(
                        $sidebarNow
                    )
            );
    }


    $sellerProductUrl =
        $sellerProductsUnlocked

            ? route('seller.products')

            : route('verified-sellers');


    if ($sellerPlanExpired) {

        $sellerProductTooltipTitle =
            'Your plan has expired';

        $sellerProductTooltip =
            'Renew or purchase another seller package to unlock Listed Products.';

    } else {

        $sellerProductTooltipTitle =
            'Seller package required';

        $sellerProductTooltip =
            'Purchase a seller package to unlock Listed Products.';
    }


    $sellerDashboardActive =
        request()->routeIs(
            'seller.dashboard'
        );


    $sellerProductsActive =
        request()->routeIs(
            'seller.products*'
        );


    $sellerCreateTransactionActive =
        request()->routeIs(
            'seller.transactions.create'
        )
        ||
        request()->routeIs(
            'seller.transactions.generated'
        );


    $sellerTransactionsActive =
        request()->routeIs(
            'seller.transactions'
        )
        ||
        request()->routeIs(
            'seller.transactions.show'
        );


    $sellerNotificationsActive =
        request()->routeIs(
            'seller.notifications*'
        );


    $sellerBusinessProfileActive =
        request()->routeIs(
            'seller.business-profile*'
        );


    $sellerProfileSettingsActive =
        request()->routeIs(
            'seller.profile-settings*'
        );


    $sellerSupportActive =
        request()->routeIs(
            'support'
        )
        ||
        request()->routeIs(
            'support.*'
        );

@endphp


<aside
    id="sellerMainSidebar"
    class="seller-main-sidebar"
>

    <div class="seller-sidebar-section-title">
        Seller Menu
    </div>


    <nav class="seller-sidebar-nav">


        {{-- Dashboard --}}

        <a
            href="{{ route('seller.dashboard') }}"
            class="seller-sidebar-link {{ $sellerDashboardActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-solid fa-house"></i>
            </span>

            <span class="seller-sidebar-label">
                Dashboard
            </span>

        </a>



        {{-- Listed Products --}}

        <div class="seller-locked-menu-wrapper">

            <a
                href="{{ $sellerProductUrl }}"

                @if(!$sellerProductsUnlocked)
                    aria-label="Listed Products locked. {{ $sellerProductTooltip }}"
                @endif

                class="
                    seller-sidebar-link

                    @if($sellerProductsUnlocked && $sellerProductsActive)
                        active
                    @elseif(!$sellerProductsUnlocked)
                        is-locked
                    @endif
                "
            >

                <span class="seller-sidebar-icon">

                    @if($sellerProductsUnlocked)

                        <i class="fa-solid fa-box-open"></i>

                    @elseif($sellerPlanExpired)

                        <i class="fa-solid fa-clock-rotate-left"></i>

                    @else

                        <i class="fa-solid fa-lock"></i>

                    @endif

                </span>


                <span class="seller-sidebar-label">
                    Listed products
                </span>


                @if(!$sellerProductsUnlocked)

                    <span class="seller-sidebar-lock">
                        <i class="fa-solid fa-lock"></i>
                    </span>

                @endif

            </a>


            @if(!$sellerProductsUnlocked)

                <div
                    class="seller-plan-tooltip"
                    role="tooltip"
                >

                    <span class="seller-plan-tooltip-icon">

                        @if($sellerPlanExpired)

                            <i class="fa-solid fa-clock-rotate-left"></i>

                        @else

                            <i class="fa-solid fa-lock"></i>

                        @endif

                    </span>


                    <div class="seller-plan-tooltip-content">

                        <strong>
                            {{ $sellerProductTooltipTitle }}
                        </strong>

                        <span>
                            {{ $sellerProductTooltip }}
                        </span>

                        <small>
                            Click to view seller packages →
                        </small>

                    </div>

                </div>

            @endif

        </div>



        {{-- Create Transaction --}}

        <a
            href="{{ route('seller.transactions.create') }}"
            class="seller-sidebar-link {{ $sellerCreateTransactionActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-solid fa-circle-plus"></i>
            </span>

            <span class="seller-sidebar-label">
                Create transaction
            </span>

        </a>



        {{-- Transactions --}}

        <a
            href="{{ route('seller.transactions') }}"
            class="seller-sidebar-link {{ $sellerTransactionsActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-solid fa-file-lines"></i>
            </span>


            <span class="seller-sidebar-label">
                Transactions
            </span>

        </a>



        {{-- Notifications --}}

        <a
            href="{{ route('seller.notifications') }}"
            class="seller-sidebar-link {{ $sellerNotificationsActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-bell"></i>

            </span>


            <span class="seller-sidebar-label">
                Notifications
            </span>


            @if($sellerUnreadNotificationCount > 0)

                <span
                    class="seller-sidebar-notification-badge"
                    title="{{ $sellerUnreadNotificationCount }} unread notification(s)"
                >

                    {{
                        $sellerUnreadNotificationCount > 99
                            ? '99+'
                            : $sellerUnreadNotificationCount
                    }}

                </span>

            @endif

        </a>



        {{-- Business Profile --}}

        <a
            href="{{ route('seller.business-profile') }}"
            class="seller-sidebar-link {{ $sellerBusinessProfileActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-solid fa-store"></i>
            </span>


            <span class="seller-sidebar-label">
                Business profile
            </span>

        </a>



        {{-- Profile Settings --}}

        <a
            href="{{ route('seller.profile-settings') }}"
            class="seller-sidebar-link {{ $sellerProfileSettingsActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-solid fa-gear"></i>
            </span>


            <span class="seller-sidebar-label">
                Profile settings
            </span>

        </a>



        {{-- Support --}}

        <a
            href="{{ route('support') }}"
            class="seller-sidebar-link {{ $sellerSupportActive ? 'active' : '' }}"
        >

            <span class="seller-sidebar-icon">
                <i class="fa-regular fa-comments"></i>
            </span>


            <span class="seller-sidebar-label">
                Support
            </span>

        </a>

    </nav>



    <div
        class="
            seller-sidebar-section-title
            seller-sidebar-switch-title
        "
    >
        Switch
    </div>



    {{-- Buyer View --}}

    <form
        method="POST"
        action="{{ route('account.switch', 'buyer') }}"
    >

        @csrf


        <button
            type="submit"
            class="seller-sidebar-link"
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-arrow-right-arrow-left"></i>

            </span>


            <span class="seller-sidebar-label">
                Buyer view
            </span>

        </button>

    </form>



    {{-- Logout --}}

    <form
        method="POST"
        action="{{ route('logout') }}"
    >

        @csrf


        <button
            type="submit"
            class="
                seller-sidebar-link
                seller-sidebar-logout
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-arrow-right-from-bracket"></i>

            </span>


            <span class="seller-sidebar-label">
                Log out
            </span>

        </button>

    </form>

</aside>



<style>

.seller-sidebar-notification-badge {
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


.seller-sidebar-link.active
.seller-sidebar-notification-badge {
    border-color: #0B4B3C;

    background: #FFFFFF;

    color: #0B4B3C;
}


.seller-sidebar-link {
    position: relative;
}


@media(max-width: 1024px) {

    .seller-sidebar-notification-badge {
        min-width: 18px;
        height: 18px;

        padding: 0 5px;

        font-size: 7px;
    }

}

</style>