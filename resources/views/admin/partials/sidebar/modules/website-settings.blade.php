@php

    /*
    |--------------------------------------------------------------------------
    | Website Settings Active
    |--------------------------------------------------------------------------
    |
    | Seller Applications are intentionally EXCLUDED.
    |
    */

    $websiteSettingsActive =
        request()->routeIs(
            'admin.website-settings.app-settings*'
        )
        ||
        request()->routeIs(
            'admin.website-settings.faqs*'
        )
        ||
        request()->routeIs(
            'admin.website-settings.pricing*'
        )
        ||
        request()->routeIs(
            'admin.website-settings.become-seller*'
        )
        ||
        request()->routeIs(
            'admin.website-settings.seller-packages.*'
        );

@endphp



<div
    class="
        admin-menu-group
        {{
            $websiteSettingsActive
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
                $websiteSettingsActive
                    ? 'active-parent'
                    : ''
            }}
        "

        data-sidebar-group

        data-tooltip="Website Settings"

        aria-expanded="{{
            $websiteSettingsActive
                ? 'true'
                : 'false'
        }}"
    >

        <span class="admin-menu-icon">

            <i
                class="
                    fa-solid
                    fa-sliders
                "
            ></i>

        </span>


        <span class="admin-menu-label">

            Website Settings

        </span>


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
        SUBMENU
    ====================================================== --}}

    <div class="admin-submenu">


        {{-- App Settings --}}
        <a
            href="{{
                route(
                    'admin.website-settings.app-settings'
                )
            }}"

            class="{{
                request()->routeIs(
                    'admin.website-settings.app-settings*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i
                class="
                    fa-solid
                    fa-gear
                "
            ></i>


            <span>
                App Settings
            </span>

        </a>



        {{-- FAQ --}}
        <a
            href="{{
                route(
                    'admin.website-settings.faqs'
                )
            }}"

            class="{{
                request()->routeIs(
                    'admin.website-settings.faqs*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i
                class="
                    fa-regular
                    fa-circle-question
                "
            ></i>


            <span>
                FAQ Page
            </span>

        </a>



        {{-- Pricing --}}
        <a
            href="{{
                route(
                    'admin.website-settings.pricing'
                )
            }}"

            class="{{
                request()->routeIs(
                    'admin.website-settings.pricing*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i
                class="
                    fa-solid
                    fa-tags
                "
            ></i>


            <span>
                Pricing Page
            </span>

        </a>



        {{-- Become Seller --}}
        <a
            href="{{
                route(
                    'admin.website-settings.become-seller'
                )
            }}"

            class="{{
                request()->routeIs(
                    'admin.website-settings.become-seller*'
                )
                ||
                request()->routeIs(
                    'admin.website-settings.seller-packages.*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i
                class="
                    fa-solid
                    fa-store
                "
            ></i>


            <span>
                Become Seller Page
            </span>

        </a>

    </div>



    {{-- =====================================================
        COLLAPSED FLYOUT
    ====================================================== --}}

    <div class="admin-flyout">


        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i
                    class="
                        fa-solid
                        fa-sliders
                    "
                ></i>

            </span>


            <div>

                <strong>
                    Website Settings
                </strong>


                <span>
                    4 submenus
                </span>

            </div>

        </div>



        <div class="admin-flyout-links">


            <a
                href="{{
                    route(
                        'admin.website-settings.app-settings'
                    )
                }}"
            >

                <i class="fa-solid fa-gear"></i>

                <span>
                    App Settings
                </span>

            </a>



            <a
                href="{{
                    route(
                        'admin.website-settings.faqs'
                    )
                }}"
            >

                <i
                    class="
                        fa-regular
                        fa-circle-question
                    "
                ></i>

                <span>
                    FAQ Page
                </span>

            </a>



            <a
                href="{{
                    route(
                        'admin.website-settings.pricing'
                    )
                }}"
            >

                <i class="fa-solid fa-tags"></i>

                <span>
                    Pricing Page
                </span>

            </a>



            <a
                href="{{
                    route(
                        'admin.website-settings.become-seller'
                    )
                }}"
            >

                <i class="fa-solid fa-store"></i>

                <span>
                    Become Seller Page
                </span>

            </a>

        </div>

    </div>

</div>