<aside
    id="adminSidebar"
    class="admin-sidebar"
>

    {{-- =========================================================
        BRAND
    ========================================================== --}}
    <div class="admin-sidebar-brand">

        <a
            href="{{ route('admin.dashboard') }}"
            class="admin-brand"
        >

            <span class="admin-brand-icon">
                M
            </span>


            <span class="admin-brand-text">

                <strong>
                    Mid<span>Point</span>
                </strong>

                <small>
                    Admin Panel
                </small>

            </span>

        </a>

    </div>


{{-- =========================================================
    NAVIGATION
========================================================== --}}
<div class="admin-sidebar-scroll">


    {{-- =====================================================
        OVERVIEW
    ====================================================== --}}
    <div class="admin-nav-section-title">
        Overview
    </div>


    <nav class="admin-navigation">

        {{-- Dashboard --}}
        @include(
            'admin.partials.sidebar.modules.dashboard'
        )

    </nav>



    {{-- =====================================================
        MANAGEMENT
    ====================================================== --}}
    <div class="admin-nav-section-title admin-nav-section-spacing">
        Management
    </div>


    <nav class="admin-navigation">

        {{-- Users --}}
        @include(
            'admin.partials.sidebar.modules.users-applications'
        )


        {{-- Website --}}
        @include(
            'admin.partials.sidebar.modules.website-settings'
        )


        {{-- Support --}}
        @include(
            'admin.partials.sidebar.modules.support-inquiries'
        )

    </nav>

</div>



    {{-- =========================================================
        USER
    ========================================================== --}}
    <div class="admin-sidebar-footer">

        <div class="admin-sidebar-user">

            <div class="admin-user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>


            <div class="admin-sidebar-user-info">

                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <span>
                    Administrator
                </span>

            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
                class="admin-sidebar-logout"
            >
                @csrf

                <button
                    type="submit"
                    title="Log out"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>

            </form>

        </div>

    </div>

</aside>