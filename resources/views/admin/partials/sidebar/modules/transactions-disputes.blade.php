@php

    /*
    |--------------------------------------------------------------------------
    | Active Routes
    |--------------------------------------------------------------------------
    */

    $transactionsActive =
        request()->routeIs(
            'admin.transactions.*'
        );


    $disputesActive =
        request()->routeIs(
            'admin.disputes.*'
        );


    $moduleActive =
        $transactionsActive
        ||
        $disputesActive;


    /*
    |--------------------------------------------------------------------------
    | Open Disputes
    |--------------------------------------------------------------------------
    */

    $openDisputes =
        $adminOpenDisputeCount
        ??
        0;

@endphp



<div
    class="
        admin-menu-group
        {{ $moduleActive ? 'is-open' : '' }}
    "
>


    {{-- =====================================================
        MAIN BUTTON
    ====================================================== --}}

    <button
        type="button"
        class="
            admin-menu-link
            admin-menu-toggle
            {{ $moduleActive ? 'active-parent' : '' }}
        "
        data-sidebar-group
        data-tooltip="Transactions & Disputes"
        aria-expanded="{{ $moduleActive ? 'true' : 'false' }}"
    >

        <span class="admin-menu-icon">

            <i class="fa-solid fa-money-bill-transfer"></i>

        </span>


        <span class="admin-menu-label">

            Transactions

        </span>


        @if($openDisputes > 0)

            <span class="admin-menu-count">

                {{
                    $openDisputes > 99
                        ? '99+'
                        : $openDisputes
                }}

            </span>

        @endif


        <span class="admin-menu-chevron">

            <i class="fa-solid fa-chevron-down"></i>

        </span>

    </button>



    {{-- =====================================================
        SUBMENU
    ====================================================== --}}

    <div class="admin-submenu">


        {{-- Paid Transactions --}}
        <a
            href="{{ route('admin.transactions.index') }}"
            class="{{ $transactionsActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-shield-halved"></i>


            <span>
                Paid Transactions
            </span>

        </a>



        {{-- Disputes --}}
        <a
            href="{{ route('admin.disputes.index') }}"
            class="{{ $disputesActive ? 'active' : '' }}"
        >

            <i class="fa-solid fa-triangle-exclamation"></i>


            <span>
                Disputes
            </span>


            @if($openDisputes > 0)

                <span class="admin-submenu-count">

                    {{
                        $openDisputes > 99
                            ? '99+'
                            : $openDisputes
                    }}

                </span>

            @endif

        </a>

    </div>



    {{-- =====================================================
        COLLAPSED FLYOUT
    ====================================================== --}}

    <div class="admin-flyout">

        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i class="fa-solid fa-money-bill-transfer"></i>

            </span>


            <div>

                <strong>
                    Transactions
                </strong>

                <span>
                    Payment & dispute monitoring
                </span>

            </div>

        </div>


        <div class="admin-flyout-links">


            <a
                href="{{ route('admin.transactions.index') }}"
                class="{{ $transactionsActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Paid Transactions
                </span>

            </a>


            <a
                href="{{ route('admin.disputes.index') }}"
                class="{{ $disputesActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>
                    Disputes
                </span>

            </a>

        </div>

    </div>

</div>