@php

    /*
    |--------------------------------------------------------------------------
    | Logged-in Seller
    |--------------------------------------------------------------------------
    */

    $sidebarUser =
        auth()->user();


    /*
    |--------------------------------------------------------------------------
    | Current Time
    |--------------------------------------------------------------------------
    */

    $sidebarNow =
        now();


    /*
    |--------------------------------------------------------------------------
    | Latest Subscription
    |--------------------------------------------------------------------------
    |
    | This can be:
    |
    | active
    | expired
    | or another historic subscription
    |
    */

    $sidebarLatestPlan =
        null;


    /*
    |--------------------------------------------------------------------------
    | Valid Active Subscription
    |--------------------------------------------------------------------------
    */

    $sidebarActivePlan =
        null;


    if ($sidebarUser) {


        /*
        |--------------------------------------------------------------------------
        | Latest Subscription
        |--------------------------------------------------------------------------
        */

        $sidebarLatestPlan =
            \App\Models\SellerSubscription::query()

                ->where(
                    'user_id',
                    $sidebarUser->id
                )

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | ACTIVE PLAN
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We check all requirements explicitly.
        |
        | It MUST:
        |
        | 1. belong to this exact user
        | 2. status = active
        | 3. either have no expiry or expiry > NOW
        |
        | Therefore a brand-new user with no subscription can NEVER
        | accidentally unlock Listed Products.
        |
        */

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

                ->where(
                    function ($query) use ($sidebarNow) {

                        $query

                            ->whereNull(
                                'expires_at'
                            )

                            ->orWhere(
                                'expires_at',
                                '>',
                                $sidebarNow
                            );
                    }
                )

                ->latest('id')

                ->first();

    }



    /*
    |--------------------------------------------------------------------------
    | Listed Products Unlocked?
    |--------------------------------------------------------------------------
    */

    $sellerProductsUnlocked =
        !is_null(
            $sidebarActivePlan
        );



    /*
    |--------------------------------------------------------------------------
    | Plan Expired?
    |--------------------------------------------------------------------------
    */

    $sellerPlanExpired =
        false;


    if (
        !$sellerProductsUnlocked
        &&
        $sidebarLatestPlan
    ) {

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



    /*
    |--------------------------------------------------------------------------
    | Product Destination
    |--------------------------------------------------------------------------
    |
    | Active plan:
    |
    | /seller/products
    |
    | Locked:
    |
    | /verified-sellers
    |
    */

    $sellerProductUrl =
        $sellerProductsUnlocked

            ? route(
                'seller.products'
            )

            : route(
                'verified-sellers'
            );



    /*
    |--------------------------------------------------------------------------
    | Tooltip
    |--------------------------------------------------------------------------
    */

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

@endphp



<aside
    id="sellerMainSidebar"
    class="seller-main-sidebar"
>


    {{-- =========================================================
        SELLER MENU
    ========================================================== --}}

    <div class="seller-sidebar-section-title">

        Seller Menu

    </div>



    <nav class="seller-sidebar-nav">


        {{-- =====================================================
            DASHBOARD
        ====================================================== --}}

        <a
            href="{{ route('seller.dashboard') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.dashboard'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-house"></i>

            </span>


            <span class="seller-sidebar-label">

                Dashboard

            </span>

        </a>



        {{-- =====================================================
            LISTED PRODUCTS
        ====================================================== --}}

        <div class="seller-locked-menu-wrapper">


            <a
                href="{{ $sellerProductUrl }}"

                @if(!$sellerProductsUnlocked)

                    aria-label="
                        Listed Products locked.
                        {{ $sellerProductTooltip }}
                    "

                @endif

                class="
                    seller-sidebar-link

                    @if(
                        $sellerProductsUnlocked
                        &&
                        request()->routeIs(
                            'seller.products*'
                        )
                    )

                        active

                    @elseif(!$sellerProductsUnlocked)

                        is-locked

                    @endif
                "
            >


                {{-- Icon --}}
                <span class="seller-sidebar-icon">

                    @if($sellerProductsUnlocked)

                        <i class="fa-solid fa-box-open"></i>

                    @elseif($sellerPlanExpired)

                        <i class="fa-solid fa-clock-rotate-left"></i>

                    @else

                        <i class="fa-solid fa-lock"></i>

                    @endif

                </span>



                {{-- Label --}}
                <span class="seller-sidebar-label">

                    Listed products

                </span>



                {{-- Lock --}}
                @if(!$sellerProductsUnlocked)

                    <span class="seller-sidebar-lock">

                        <i class="fa-solid fa-lock"></i>

                    </span>

                @endif

            </a>



            {{-- =================================================
                LOCK TOOLTIP
            ================================================== --}}

            @if(!$sellerProductsUnlocked)

                <div
                    class="seller-plan-tooltip"
                    role="tooltip"
                >

                    <span class="seller-plan-tooltip-icon">

                        @if($sellerPlanExpired)

                            <i
                                class="
                                    fa-solid
                                    fa-clock-rotate-left
                                "
                            ></i>

                        @else

                            <i
                                class="
                                    fa-solid
                                    fa-lock
                                "
                            ></i>

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



        {{-- =====================================================
            CREATE TRANSACTION
        ====================================================== --}}

        <a
            href="{{ route('seller.transactions.create') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.transactions.create'
                    )
                    ||
                    request()->routeIs(
                        'seller.transactions.generated'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-circle-plus"></i>

            </span>


            <span class="seller-sidebar-label">

                Create transaction

            </span>

        </a>


        {{-- =====================================================
            TRANSACTIONS
        ====================================================== --}}

        <a
            href="{{ route('seller.transactions') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.transactions'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-file-lines"></i>

            </span>


            <span class="seller-sidebar-label">

                Transactions

            </span>

        </a>



        {{-- =====================================================
            NOTIFICATIONS
        ====================================================== --}}

        <a
            href="{{ route('seller.notifications') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.notifications'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-bell"></i>

            </span>


            <span class="seller-sidebar-label">

                Notifications

            </span>

        </a>



        {{-- =====================================================
            BUSINESS PROFILE
        ====================================================== --}}

        <a
            href="{{ route('seller.business-profile') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.business-profile'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-store"></i>

            </span>


            <span class="seller-sidebar-label">

                Business profile

            </span>

        </a>



        {{-- =====================================================
            PROFILE SETTINGS
        ====================================================== --}}

        <a
            href="{{ route('seller.profile-settings') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'seller.profile-settings'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-solid fa-gear"></i>

            </span>


            <span class="seller-sidebar-label">

                Profile settings

            </span>

        </a>



        {{-- =====================================================
            SUPPORT
        ====================================================== --}}

        <a
            href="{{ route('support') }}"

            class="
                seller-sidebar-link

                {{
                    request()->routeIs(
                        'support'
                    )
                        ? 'active'
                        : ''
                }}
            "
        >

            <span class="seller-sidebar-icon">

                <i class="fa-regular fa-comments"></i>

            </span>


            <span class="seller-sidebar-label">

                Support

            </span>

        </a>

    </nav>



    {{-- =========================================================
        SWITCH
    ========================================================== --}}

    <div
        class="
            seller-sidebar-section-title
            seller-sidebar-switch-title
        "
    >

        Switch

    </div>



    {{-- =========================================================
        BUYER VIEW
    ========================================================== --}}

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

                <i
                    class="
                        fa-solid
                        fa-arrow-right-arrow-left
                    "
                ></i>

            </span>


            <span class="seller-sidebar-label">

                Buyer view

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
                seller-sidebar-link
                seller-sidebar-logout
            "
        >

            <span class="seller-sidebar-icon">

                <i
                    class="
                        fa-solid
                        fa-arrow-right-from-bracket
                    "
                ></i>

            </span>


            <span class="seller-sidebar-label">

                Log out

            </span>

        </button>

    </form>

</aside>