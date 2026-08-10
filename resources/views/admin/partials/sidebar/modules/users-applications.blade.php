@php

    $usersActive =
        request()->routeIs(
            'admin.users.*'
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


    $usersApplicationsActive =
        $usersActive
        ||
        $sellerApplicationsActive
        ||
        $invoicesActive
        ||
        $subscriptionsActive;

@endphp


<div
    class="
        admin-menu-group
        {{ $usersApplicationsActive ? 'is-open' : '' }}
    "
>

    <button
        type="button"
        class="
            admin-menu-link
            admin-menu-toggle
            {{ $usersApplicationsActive ? 'active-parent' : '' }}
        "
        data-sidebar-group
        data-tooltip="Users & Applications"
        aria-expanded="{{ $usersApplicationsActive ? 'true' : 'false' }}"
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

        {{-- Users --}}
        <a
            href="{{ route('admin.users.index') }}"
            class="{{ $usersActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-users"></i>

            <span>
                User Management
            </span>

        </a>


        {{-- Applications --}}
        <a
            href="{{ route('admin.website-settings.seller-applications.index') }}"
            class="{{ $sellerApplicationsActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-file-signature"></i>

            <span>
                Seller Applications
            </span>

        </a>


        {{-- Invoices --}}
        <a
            href="{{ route('admin.billing.invoices.index') }}"
            class="{{ $invoicesActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-file-invoice-dollar"></i>

            <span>
                Seller Invoices
            </span>

        </a>


        {{-- Purchased Plans --}}
        <a
            href="{{ route('admin.billing.subscriptions.index') }}"
            class="{{ $subscriptionsActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-box-open"></i>

            <span>
                Purchased Packages
            </span>

        </a>

    </div>



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
                    4 submenus
                </span>

            </div>

        </div>


        <div class="admin-flyout-links">

            <a
                href="{{ route('admin.users.index') }}"
                class="{{ $usersActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-users"></i>

                <span>
                    User Management
                </span>

            </a>


            <a
                href="{{ route('admin.website-settings.seller-applications.index') }}"
                class="{{ $sellerApplicationsActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-file-signature"></i>

                <span>
                    Seller Applications
                </span>

            </a>


            <a
                href="{{ route('admin.billing.invoices.index') }}"
                class="{{ $invoicesActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-file-invoice-dollar"></i>

                <span>
                    Seller Invoices
                </span>

            </a>


            <a
                href="{{ route('admin.billing.subscriptions.index') }}"
                class="{{ $subscriptionsActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-box-open"></i>

                <span>
                    Purchased Packages
                </span>

            </a>

        </div>

    </div>

</div>