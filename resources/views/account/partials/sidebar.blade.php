@php

    $isSeller =
        $dashboardRole === 'seller';


    /*
    |--------------------------------------------------------------------------
    | Seller Menu
    |--------------------------------------------------------------------------
    */

    $sellerMenu = [

        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => 'seller.dashboard',
            'active' => 'seller.dashboard',
        ],

        [
            'label' => 'Listed products',
            'icon' => 'fa-bag-shopping',
            'route' => 'seller.products',
            'active' => 'seller.products',
        ],

        [
            'label' => 'Create transaction',
            'icon' => 'fa-circle-plus',
            'route' => 'seller.transactions.create',
            'active' => 'seller.transactions.create',
        ],

        [
            'label' => 'Transactions',
            'icon' => 'fa-file-lines',
            'route' => 'seller.transactions',
            'active' => 'seller.transactions',
        ],

        [
            'label' => 'Notifications',
            'icon' => 'fa-bell',
            'route' => 'seller.notifications',
            'active' => 'seller.notifications',
        ],

        [
            'label' => 'Business profile',
            'icon' => 'fa-store',
            'route' => 'seller.business-profile',
            'active' => 'seller.business-profile',
        ],

        [
            'label' => 'Profile settings',
            'icon' => 'fa-gear',
            'route' => 'seller.profile-settings',
            'active' => 'seller.profile-settings',
        ],

        [
            'label' => 'Support',
            'icon' => 'fa-comments',
            'route' => 'support',
            'active' => 'support',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | Buyer Menu
    |--------------------------------------------------------------------------
    */

    $buyerMenu = [

        [
            'label' => 'Dashboard',
            'icon' => 'fa-house',
            'route' => 'buyer.dashboard',
            'active' => 'buyer.dashboard',
        ],

        [
            'label' => 'Transactions',
            'icon' => 'fa-file-lines',
            'route' => 'buyer.transactions',
            'active' => 'buyer.transactions',
        ],

        [
            'label' => 'Notifications',
            'icon' => 'fa-bell',
            'route' => 'buyer.notifications',
            'active' => 'buyer.notifications',
        ],

        [
            'label' => 'Featured businesses',
            'icon' => 'fa-store',
            'route' => 'featured-businesses',
            'active' => 'featured-businesses',
        ],

        [
            'label' => 'Profile settings',
            'icon' => 'fa-gear',
            'route' => 'buyer.profile-settings',
            'active' => 'buyer.profile-settings',
        ],

        [
            'label' => 'Support',
            'icon' => 'fa-comments',
            'route' => 'support',
            'active' => 'support',
        ],

    ];


    $menu =
        $isSeller
            ? $sellerMenu
            : $buyerMenu;

@endphp


<aside
    id="accountSidebar"
    class="account-sidebar"
>


    {{-- =========================================================
        MENU TITLE
    ========================================================== --}}

    <div class="account-sidebar-title">

        {{
            $isSeller
                ? 'Seller Menu'
                : 'Buyer Menu'
        }}

    </div>



    {{-- =========================================================
        NAVIGATION
    ========================================================== --}}

    <nav class="account-sidebar-nav">

        @foreach ($menu as $item)

            <a
                href="{{ route($item['route']) }}"
                class="account-sidebar-link
                       {{
                            request()->routeIs($item['active'])
                                ? 'active'
                                : ''
                       }}"
            >

                <span class="account-sidebar-icon">

                    <i class="fa-solid {{ $item['icon'] }}"></i>

                </span>


                <span>
                    {{ $item['label'] }}
                </span>

            </a>

        @endforeach

    </nav>



    {{-- =========================================================
        SWITCH
    ========================================================== --}}

    <div class="account-sidebar-title account-sidebar-switch-title">
        Switch
    </div>


    <form
        method="POST"
        action="{{
            route(
                'account.switch',
                $isSeller
                    ? 'buyer'
                    : 'seller'
            )
        }}"
    >

        @csrf


        <button
            type="submit"
            class="account-sidebar-link account-sidebar-button"
        >

            <span class="account-sidebar-icon switch">

                <i class="fa-solid fa-arrow-right-arrow-left"></i>

            </span>


            <span>

                {{
                    $isSeller
                        ? 'Buyer view'
                        : 'Seller view'
                }}

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
            class="account-sidebar-link
                   account-sidebar-button
                   account-sidebar-logout"
        >

            <span class="account-sidebar-icon logout">

                <i class="fa-solid fa-right-from-bracket"></i>

            </span>


            <span>
                Log out
            </span>

        </button>

    </form>

</aside>