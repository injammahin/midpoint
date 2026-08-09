@php

    $websiteSettingsActive =
        request()->routeIs(
            'admin.website-settings.*'
        );

@endphp


<div
    class="admin-menu-group
           {{ $websiteSettingsActive ? 'is-open' : '' }}"
>

    {{-- Main menu --}}
    <button
        type="button"
        class="admin-menu-link
               admin-menu-toggle
               {{ $websiteSettingsActive ? 'active-parent' : '' }}"
        data-sidebar-group
        data-tooltip="Website Settings"
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


    {{-- Expanded submenu --}}
    <div class="admin-submenu">

        <a
            href="{{ route('admin.website-settings.app-settings') }}"
            class="{{ request()->routeIs('admin.website-settings.app-settings') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-gear"></i>

            <span>
                App Settings
            </span>
        </a>


        <a
            href="{{ route('admin.website-settings.faqs') }}"
            class="{{ request()->routeIs('admin.website-settings.faqs') ? 'active' : '' }}"
        >
            <i class="fa-regular fa-circle-question"></i>

            <span>
                FAQ Page
            </span>
        </a>


        <a
            href="{{ route('admin.website-settings.pricing') }}"
            class="{{ request()->routeIs('admin.website-settings.pricing') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-tags"></i>

            <span>
                Pricing Page
            </span>
        </a>


        <a
            href="{{ route('admin.website-settings.become-seller') }}"
            class="{{ request()->routeIs('admin.website-settings.become-seller') ? 'active' : '' }}"
        >
            <i class="fa-solid fa-store"></i>

            <span>
                Become Seller Page
            </span>
        </a>

    </div>


    {{-- Collapsed flyout --}}
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
                    4 submenus
                </span>

            </div>

        </div>


        <div class="admin-flyout-links">

            <a
                href="{{ route('admin.website-settings.app-settings') }}"
            >
                <i class="fa-solid fa-gear"></i>
                App Settings
            </a>


            <a
                href="{{ route('admin.website-settings.faqs') }}"
            >
                <i class="fa-regular fa-circle-question"></i>
                FAQ Page
            </a>


            <a
                href="{{ route('admin.website-settings.pricing') }}"
            >
                <i class="fa-solid fa-tags"></i>
                Pricing Page
            </a>


            <a
                href="{{ route('admin.website-settings.become-seller') }}"
            >
                <i class="fa-solid fa-store"></i>
                Become Seller Page
            </a>

        </div>

    </div>

</div>