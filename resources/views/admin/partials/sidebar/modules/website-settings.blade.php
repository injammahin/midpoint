@php

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    $canHome =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.home_page.manage'
            );


    $canAbout =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.about_page.manage'
            );


    $canHow =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.how_it_works_page.manage'
            );


    $canApp =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.app_settings.manage'
            );


    $canFaq =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.faqs.manage'
            );


    $canPricing =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.pricing.manage'
            );


    $canPackages =
        auth()
            ->user()
            ->hasAdminPermission(
                'website.seller_packages.manage'
            );


    /*
    |--------------------------------------------------------------------------
    | Active
    |--------------------------------------------------------------------------
    */

    $websiteSettingsActive =

        request()->routeIs(
            'admin.website-settings.home-page*'
        )

        ||

        request()->routeIs(
            'admin.website-settings.about-page*'
        )

        ||

        request()->routeIs(
            'admin.website-settings.how-it-works-page*'
        )

        ||

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



@if(
    $canHome
    ||
    $canAbout
    ||
    $canHow
    ||
    $canApp
    ||
    $canFaq
    ||
    $canPricing
    ||
    $canPackages
)


<div
    class="
        admin-menu-group
        {{ $websiteSettingsActive ? 'is-open' : '' }}
    "
>


    <button
        type="button"

        class="
            admin-menu-link
            admin-menu-toggle
            {{ $websiteSettingsActive ? 'active-parent' : '' }}
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

            <i class="fa-solid fa-sliders"></i>

        </span>


        <span class="admin-menu-label">

            Website Settings

        </span>


        <span class="admin-menu-chevron">

            <i class="fa-solid fa-chevron-down"></i>

        </span>

    </button>



    <div class="admin-submenu">


        @if($canHome)

            <a
                href="{{
                    route(
                        'admin.website-settings.home-page'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.website-settings.home-page*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-house"></i>

                <span>
                    Home Page
                </span>

            </a>

        @endif



        @if($canAbout)

            <a
                href="{{
                    route(
                        'admin.website-settings.about-page'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.website-settings.about-page*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-regular fa-building"></i>

                <span>
                    About Page
                </span>

            </a>

        @endif



        @if($canHow)

            <a
                href="{{
                    route(
                        'admin.website-settings.how-it-works-page'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.website-settings.how-it-works-page*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-route"></i>

                <span>
                    How It Works Page
                </span>

            </a>

        @endif



        @if($canApp)

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

                <i class="fa-solid fa-gear"></i>

                <span>
                    App Settings
                </span>

            </a>

        @endif



        @if($canFaq)

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

                <i class="fa-regular fa-circle-question"></i>

                <span>
                    FAQ Page
                </span>

            </a>

        @endif



        @if($canPricing)

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

                <i class="fa-solid fa-tags"></i>

                <span>
                    Pricing Page
                </span>

            </a>

        @endif



        @if($canPackages)

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

                <i class="fa-solid fa-store"></i>

                <span>
                    Become Seller Page
                </span>

            </a>

        @endif


    </div>



    {{-- =========================================================
        COLLAPSED FLYOUT
    ========================================================== --}}

    <div class="admin-flyout">


        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i class="fa-solid fa-sliders"></i>

            </span>


            <div>

                <strong>
                    Website Settings
                </strong>

                <span>
                    Public website content
                </span>

            </div>

        </div>



        <div class="admin-flyout-links">


            @if($canHome)

                <a
                    href="{{
                        route(
                            'admin.website-settings.home-page'
                        )
                    }}"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Home Page</span>
                </a>

            @endif


            @if($canAbout)

                <a
                    href="{{
                        route(
                            'admin.website-settings.about-page'
                        )
                    }}"
                >
                    <i class="fa-regular fa-building"></i>
                    <span>About Page</span>
                </a>

            @endif


            @if($canHow)

                <a
                    href="{{
                        route(
                            'admin.website-settings.how-it-works-page'
                        )
                    }}"
                >
                    <i class="fa-solid fa-route"></i>
                    <span>How It Works Page</span>
                </a>

            @endif


            @if($canApp)

                <a
                    href="{{
                        route(
                            'admin.website-settings.app-settings'
                        )
                    }}"
                >
                    <i class="fa-solid fa-gear"></i>
                    <span>App Settings</span>
                </a>

            @endif


            @if($canFaq)

                <a
                    href="{{
                        route(
                            'admin.website-settings.faqs'
                        )
                    }}"
                >
                    <i class="fa-regular fa-circle-question"></i>
                    <span>FAQ Page</span>
                </a>

            @endif


            @if($canPricing)

                <a
                    href="{{
                        route(
                            'admin.website-settings.pricing'
                        )
                    }}"
                >
                    <i class="fa-solid fa-tags"></i>
                    <span>Pricing Page</span>
                </a>

            @endif


            @if($canPackages)

                <a
                    href="{{
                        route(
                            'admin.website-settings.become-seller'
                        )
                    }}"
                >
                    <i class="fa-solid fa-store"></i>
                    <span>Become Seller Page</span>
                </a>

            @endif


        </div>


    </div>


</div>


@endif