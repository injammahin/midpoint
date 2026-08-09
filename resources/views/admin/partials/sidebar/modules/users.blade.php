{{-- =========================================================
    USER MANAGEMENT
========================================================== --}}

<a
    href="{{ route('admin.users.index') }}"
    class="admin-menu-link
           {{
                request()->routeIs('admin.users.*')
                    ? 'active'
                    : ''
           }}"
>

    <span class="admin-menu-icon">

        <i class="fa-solid fa-users"></i>

    </span>


    <span class="admin-menu-text">
        User Management
    </span>

</a>