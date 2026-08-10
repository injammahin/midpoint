@php

    /*
    |--------------------------------------------------------------------------
    | Users & Applications Active State
    |--------------------------------------------------------------------------
    |
    | Keep the parent dropdown open whenever the admin is inside:
    |
    | - User Management
    | - Seller Applications
    |
    */

    $usersApplicationsActive =
        request()->routeIs(
            'admin.users.*'
        )
        ||
        request()->routeIs(
            'admin.website-settings.seller-applications.*'
        );


    /*
    |--------------------------------------------------------------------------
    | Individual Active States
    |--------------------------------------------------------------------------
    */

    $usersActive =
        request()->routeIs(
            'admin.users.*'
        );


    $sellerApplicationsActive =
        request()->routeIs(
            'admin.website-settings.seller-applications.*'
        );

@endphp



<div
    class="
        admin-menu-group
        {{
            $usersApplicationsActive
                ? 'is-open'
                : ''
        }}
    "
>


    {{-- =====================================================
        MAIN MENU
    ====================================================== --}}

    <button
        type="button"

        class="
            admin-menu-link
            admin-menu-toggle

            {{
                $usersApplicationsActive
                    ? 'active-parent'
                    : ''
            }}
        "

        data-sidebar-group

        data-tooltip="Users & Applications"

        aria-expanded="{{
            $usersApplicationsActive
                ? 'true'
                : 'false'
        }}"
    >


        {{-- Icon --}}
        <span class="admin-menu-icon">

            <i
                class="
                    fa-solid
                    fa-user-group
                "
            ></i>

        </span>



        {{-- Label --}}
        <span class="admin-menu-label">

            Users & Applications

        </span>



        {{-- Chevron --}}
        <span class="admin-menu-chevron">

            <i
                class="
                    fa-solid
                    fa-chevron-down
                "
            ></i>

        </span>

    </button>



    {{-- =====================================================
        EXPANDED SUBMENU
    ====================================================== --}}

    <div class="admin-submenu">


        {{-- =================================================
            USER MANAGEMENT
        ================================================== --}}

        <a
            href="{{
                route(
                    'admin.users.index'
                )
            }}"

            class="{{
                $usersActive
                    ? 'active'
                    : ''
            }}"
        >

            <i
                class="
                    fa-solid
                    fa-users
                "
            ></i>


            <span>
                User Management
            </span>

        </a>



        {{-- =================================================
            SELLER APPLICATIONS
        ================================================== --}}

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

            <i
                class="
                    fa-solid
                    fa-file-signature
                "
            ></i>


            <span>
                Seller Applications
            </span>

        </a>

    </div>



    {{-- =====================================================
        COLLAPSED SIDEBAR FLYOUT
    ====================================================== --}}

    <div class="admin-flyout">


        {{-- =================================================
            FLYOUT HEADER
        ================================================== --}}

        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i
                    class="
                        fa-solid
                        fa-user-group
                    "
                ></i>

            </span>


            <div>

                <strong>
                    Users & Applications
                </strong>


                <span>
                    2 submenus
                </span>

            </div>

        </div>



        {{-- =================================================
            FLYOUT LINKS
        ================================================== --}}

        <div class="admin-flyout-links">


            {{-- User Management --}}
            <a
                href="{{
                    route(
                        'admin.users.index'
                    )
                }}"

                class="{{
                    $usersActive
                        ? 'active'
                        : ''
                }}"
            >

                <i
                    class="
                        fa-solid
                        fa-users
                    "
                ></i>


                <span>
                    User Management
                </span>

            </a>



            {{-- Seller Applications --}}
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

                <i
                    class="
                        fa-solid
                        fa-file-signature
                    "
                ></i>


                <span>
                    Seller Applications
                </span>

            </a>

        </div>

    </div>

</div>