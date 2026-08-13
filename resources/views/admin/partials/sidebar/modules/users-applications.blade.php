@php

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    $canUsers =
        auth()
            ->user()
            ->hasAdminPermission(
                'users.manage'
            );


    $canStaff =
        auth()
            ->user()
            ->isAdmin();


    $canApplications =
        auth()
            ->user()
            ->hasAdminPermission(
                'seller_applications.manage'
            );


    $canInvoices =
        auth()
            ->user()
            ->hasAdminPermission(
                'billing.invoices.view'
            );


    $canSubscriptions =
        auth()
            ->user()
            ->hasAdminPermission(
                'billing.subscriptions.view'
            );


    /*
    |--------------------------------------------------------------------------
    | Active
    |--------------------------------------------------------------------------
    */

    $usersActive =
        request()->routeIs(
            'admin.users.*'
        );


    $staffActive =
        request()->routeIs(
            'admin.staff.*'
        );


    $sellerApplicationsActive =
        request()->routeIs(
            'admin.website-settings.seller-applications.*'
        );


    $invoicesActive =
        request()->routeIs(
            'admin.billing.invoices.*'
        );


    $subscriptionsActive =
        request()->routeIs(
            'admin.billing.subscriptions.*'
        );


    $moduleActive =
        $usersActive
        ||
        $staffActive
        ||
        $sellerApplicationsActive
        ||
        $invoicesActive
        ||
        $subscriptionsActive;


    $visibleCount =
        collect([
            $canUsers,
            $canStaff,
            $canApplications,
            $canInvoices,
            $canSubscriptions,
        ])
            ->filter()
            ->count();

@endphp



@if(
    $visibleCount > 0
)

<div
    class="
        admin-menu-group
        {{ $moduleActive ? 'is-open' : '' }}
    "
>


    <button
        type="button"

        class="
            admin-menu-link
            admin-menu-toggle
            {{ $moduleActive ? 'active-parent' : '' }}
        "

        data-sidebar-group

        data-tooltip="Users & Applications"

        aria-expanded="{{ $moduleActive ? 'true' : 'false' }}"
    >

        <span class="admin-menu-icon">

            <i class="fa-solid fa-user-group"></i>

        </span>


        <span class="admin-menu-label">

            Users & Applications

        </span>


        <span class="admin-menu-chevron">

            <i class="fa-solid fa-chevron-down"></i>

        </span>

    </button>



    <div class="admin-submenu">


        @if($canUsers)

            <a
                href="{{ route('admin.users.index') }}"
                class="{{ $usersActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-users"></i>

                <span>
                    User Management
                </span>

            </a>

        @endif



        @if($canStaff)

            <a
                href="{{ route('admin.staff.index') }}"
                class="{{ $staffActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-user-shield"></i>

                <span>
                    Role Management
                </span>

            </a>

        @endif



        @if($canApplications)

            <a
                href="{{
                    route(
                        'admin.website-settings.seller-applications.index'
                    )
                }}"

                class="{{
                    $sellerApplicationsActive
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-file-signature"></i>

                <span>
                    Seller Applications
                </span>

            </a>

        @endif



        @if($canInvoices)

            <a
                href="{{
                    route(
                        'admin.billing.invoices.index'
                    )
                }}"

                class="{{
                    $invoicesActive
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-file-invoice-dollar"></i>

                <span>
                    Seller Invoices
                </span>

            </a>

        @endif



        @if($canSubscriptions)

            <a
                href="{{
                    route(
                        'admin.billing.subscriptions.index'
                    )
                }}"

                class="{{
                    $subscriptionsActive
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-box-open"></i>

                <span>
                    Purchased Packages
                </span>

            </a>

        @endif

    </div>



    {{-- =====================================================
        COLLAPSED SIDEBAR FLYOUT
    ====================================================== --}}

    <div class="admin-flyout">


        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i class="fa-solid fa-user-group"></i>

            </span>


            <div>

                <strong>
                    Users & Applications
                </strong>

                <span>

                    {{ $visibleCount }}

                    submenu{{ $visibleCount === 1 ? '' : 's' }}

                </span>

            </div>

        </div>



        <div class="admin-flyout-links">


            @if($canUsers)

                <a href="{{ route('admin.users.index') }}">

                    <i class="fa-solid fa-users"></i>

                    <span>
                        User Management
                    </span>

                </a>

            @endif


            @if($canStaff)

                <a href="{{ route('admin.staff.index') }}">

                    <i class="fa-solid fa-user-shield"></i>

                    <span>
                        Role Management
                    </span>

                </a>

            @endif


            @if($canApplications)

                <a
                    href="{{
                        route(
                            'admin.website-settings.seller-applications.index'
                        )
                    }}"
                >

                    <i class="fa-solid fa-file-signature"></i>

                    <span>
                        Seller Applications
                    </span>

                </a>

            @endif


            @if($canInvoices)

                <a
                    href="{{
                        route(
                            'admin.billing.invoices.index'
                        )
                    }}"
                >

                    <i class="fa-solid fa-file-invoice-dollar"></i>

                    <span>
                        Seller Invoices
                    </span>

                </a>

            @endif


            @if($canSubscriptions)

                <a
                    href="{{
                        route(
                            'admin.billing.subscriptions.index'
                        )
                    }}"
                >

                    <i class="fa-solid fa-box-open"></i>

                    <span>
                        Purchased Packages
                    </span>

                </a>

            @endif

        </div>

    </div>

</div>

@endif