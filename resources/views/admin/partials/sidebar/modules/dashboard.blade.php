@php

    /*
    |--------------------------------------------------------------------------
    | Correct Dashboard By Account Type
    |--------------------------------------------------------------------------
    */

    $dashboardRoute =
        auth()
            ->user()
            ->isAdmin()

            ? route(
                'admin.dashboard'
            )

            : route(
                'admin.staff-dashboard'
            );


    $dashboardActive =

        auth()
            ->user()
            ->isAdmin()

            ? request()->routeIs(
                'admin.dashboard'
            )

            : request()->routeIs(
                'admin.staff-dashboard'
            );

@endphp


@if(
    auth()
        ->user()
        ->hasAdminPermission(
            'dashboard.view'
        )
)

    <a
        href="{{ $dashboardRoute }}"

        class="
            admin-menu-link

            {{
                $dashboardActive
                    ? 'active'
                    : ''
            }}
        "

        data-tooltip="Dashboard"
    >

        <span class="admin-menu-icon">

            <i class="fa-solid fa-gauge-high"></i>

        </span>


        <span class="admin-menu-label">

            Dashboard

        </span>

    </a>

@endif