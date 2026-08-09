<a
    href="{{ route('admin.dashboard') }}"
    class="admin-menu-link
           {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
    data-tooltip="Dashboard"
>

    <span class="admin-menu-icon">
        <i class="fa-solid fa-gauge-high"></i>
    </span>


    <span class="admin-menu-label">
        Dashboard
    </span>

</a>