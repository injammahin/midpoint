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


    /*
    |--------------------------------------------------------------------------
    | Seller KYC Permission
    |--------------------------------------------------------------------------
    |
    | For now Seller KYC uses the same permission as seller applications.
    |
    */




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
    | Active Routes
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


    /*
    |--------------------------------------------------------------------------
    | Parent Module Active
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Visible Menu Count
    |--------------------------------------------------------------------------
    */

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


        {{-- =====================================================
            MAIN MENU BUTTON
        ====================================================== --}}

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



        {{-- =====================================================
            NORMAL EXPANDED SIDEBAR SUBMENU
        ====================================================== --}}

        <div class="admin-submenu">


            {{-- User Management --}}

            @if($canUsers)

                <a
                    href="{{ route('admin.users.index') }}"

                    class="{{
                        $usersActive
                            ? 'active'
                            : ''
                    }}"
                >

                    <i class="fa-solid fa-users"></i>


                    <span>
                        User Management
                    </span>

                </a>

            @endif



            {{-- Role Management --}}

            @if($canStaff)

                <a
                    href="{{ route('admin.staff.index') }}"

                    class="{{
                        $staffActive
                            ? 'active'
                            : ''
                    }}"
                >

                    <i class="fa-solid fa-user-shield"></i>


                    <span>
                        Role Management
                    </span>

                </a>

            @endif



            {{-- Seller Applications --}}

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







            {{-- Seller Invoices --}}

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



            {{-- Purchased Packages --}}

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


            {{-- Flyout Header --}}

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



            {{-- Flyout Links --}}

            <div class="admin-flyout-links">


                {{-- User Management --}}

                @if($canUsers)

                    <a
                        href="{{ route('admin.users.index') }}"

                        class="{{
                            $usersActive
                                ? 'active'
                                : ''
                        }}"
                    >

                        <i class="fa-solid fa-users"></i>


                        <span>
                            User Management
                        </span>

                    </a>

                @endif



                {{-- Role Management --}}

                @if($canStaff)

                    <a
                        href="{{ route('admin.staff.index') }}"

                        class="{{
                            $staffActive
                                ? 'active'
                                : ''
                        }}"
                    >

                        <i class="fa-solid fa-user-shield"></i>


                        <span>
                            Role Management
                        </span>

                    </a>

                @endif



                {{-- Seller Applications --}}

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







                {{-- Seller Invoices --}}

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



                {{-- Purchased Packages --}}

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

        </div>

    </div>

@endif