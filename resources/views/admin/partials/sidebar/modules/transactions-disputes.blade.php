@php

    $canTransactions =
        auth()
            ->user()
            ->hasAdminPermission(
                'transactions.view'
            );


    $canDisputes =
        auth()
            ->user()
            ->hasAdminPermission(
                'disputes.manage'
            );


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


    $openDisputes =
        $adminOpenDisputeCount
        ??
        0;

@endphp



@if(
    $canTransactions
    ||
    $canDisputes
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

        data-tooltip="Transactions"

        aria-expanded="{{ $moduleActive ? 'true' : 'false' }}"
    >

        <span class="admin-menu-icon">

            <i class="fa-solid fa-money-bill-transfer"></i>

        </span>


        <span class="admin-menu-label">

            Transactions

        </span>


        @if(
            $canDisputes
            &&
            $openDisputes > 0
        )

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



    <div class="admin-submenu">


        @if($canTransactions)

            <a
                href="{{ route('admin.transactions.index') }}"
                class="{{ $transactionsActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Paid Transactions
                </span>

            </a>

        @endif



        @if($canDisputes)

            <a
                href="{{ route('admin.disputes.index') }}"
                class="{{ $disputesActive ? 'active' : '' }}"
            >

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>
                    Disputes
                </span>


                @if(
                    $openDisputes > 0
                )

                    <span class="admin-submenu-count">

                        {{
                            $openDisputes > 99
                                ? '99+'
                                : $openDisputes
                        }}

                    </span>

                @endif

            </a>

        @endif

    </div>



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


            @if($canTransactions)

                <a href="{{ route('admin.transactions.index') }}">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Paid Transactions
                    </span>

                </a>

            @endif


            @if($canDisputes)

                <a href="{{ route('admin.disputes.index') }}">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                    <span>
                        Disputes
                    </span>

                </a>

            @endif

        </div>

    </div>

</div>

@endif