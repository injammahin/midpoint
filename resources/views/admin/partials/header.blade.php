<header class="admin-header">

    <div class="admin-header-left">

        <button
            type="button"
            id="adminSidebarToggle"
            class="admin-icon-button
                   admin-sidebar-toggle"
            aria-label="Toggle sidebar"
        >

            <i class="fa-solid fa-bars"></i>

        </button>


        <div class="admin-page-heading">

            <span>
                MIDPOINT ADMINISTRATION
            </span>

            <h1>
                @yield('page-title', 'Dashboard')
            </h1>

        </div>

    </div>


    <div class="admin-header-actions">

        {{-- Website --}}
        <a
            href="{{ route('home') }}"
            target="_blank"
            class="admin-header-button
                   admin-view-website"
        >

            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            <span>
                View Website
            </span>

        </a>


        {{-- Theme --}}
        <button
            type="button"
            id="adminThemeToggle"
            class="admin-icon-button"
            title="Toggle theme"
        >

            <i
                id="adminThemeIcon"
                class="fa-solid fa-moon"
            ></i>

        </button>



    {{-- =========================================================
        ADMIN NOTIFICATIONS
    ========================================================== --}}

    <div
        class="admin-notification-dropdown"
        id="adminNotificationDropdown"
    >

    <button
        type="button"
        id="adminNotificationButton"
        class="admin-icon-button
               admin-notification-button"
        title="Notifications"
        aria-label="Notifications"
    >

        <i class="fa-solid fa-bell"></i>


        @if (
            ($adminUnreadNotificationCount ?? 0)
            > 0
        )

            <span
                class="admin-notification-count"
            >
                {{
                    $adminUnreadNotificationCount > 99
                        ? '99+'
                        : $adminUnreadNotificationCount
                }}
            </span>

        @endif

    </button>


    {{-- Dropdown --}}
    <div
        id="adminNotificationMenu"
        class="admin-notification-menu"
    >

        {{-- Header --}}
        <div class="admin-notification-menu-head">

            <div>

                <strong>
                    Notifications
                </strong>

                <span>
                    {{ $adminUnreadNotificationCount ?? 0 }}
                    unread
                </span>

            </div>


            @if (
                ($adminUnreadNotificationCount ?? 0)
                > 0
            )

                <form
                    method="POST"
                    action="{{ route('admin.notifications.read-all') }}"
                >
                    @csrf

                    <button type="submit">
                        Mark all read
                    </button>

                </form>

            @endif

        </div>


        {{-- Notifications --}}
        <div class="admin-notification-list">

            @forelse (
                $adminLatestNotifications ?? []
                as $notification
            )

                <a
                    href="{{ route(
                        'admin.notifications.open',
                        $notification->id
                    ) }}"
                    class="admin-notification-item
                           {{ is_null($notification->read_at) ? 'unread' : '' }}"
                >

                    <span
                        class="admin-notification-item-icon"
                    >

                        <i
                            class="fa-solid {{
                                data_get(
                                    $notification->data,
                                    'icon',
                                    'fa-bell'
                                )
                            }}"
                        ></i>

                    </span>


                    <span
                        class="admin-notification-item-content"
                    >

                        <strong>
                            {{
                                data_get(
                                    $notification->data,
                                    'title',
                                    'Notification'
                                )
                            }}
                        </strong>


                        <span>
                            {{
                                data_get(
                                    $notification->data,
                                    'message',
                                    ''
                                )
                            }}
                        </span>


                        <small>
                            {{
                                $notification
                                    ->created_at
                                    ->diffForHumans()
                            }}
                        </small>

                    </span>


                    @if (
                        is_null(
                            $notification->read_at
                        )
                    )

                        <span
                            class="admin-notification-unread-dot"
                        ></span>

                    @endif

                </a>

            @empty

                <div
                    class="admin-notification-empty"
                >

                    <i class="fa-regular fa-bell"></i>

                    <strong>
                        No notifications
                    </strong>

                    <span>
                        You're all caught up.
                    </span>

                </div>

            @endforelse

        </div>


        {{-- Footer --}}
        <a
            href="{{ route('admin.support-inquiries.contacts') }}"
            class="admin-notification-footer"
        >
            View contact messages

            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

</div>


        {{-- User dropdown --}}
        <div class="admin-user-dropdown">

            <button
                type="button"
                id="adminUserDropdownButton"
                class="admin-user-dropdown-button"
            >

                <div class="admin-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>


                <div class="admin-header-user-info">

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>


                <i class="fa-solid fa-chevron-down"></i>

            </button>


            <div
                id="adminUserDropdownMenu"
                class="admin-user-dropdown-menu"
            >

                <div class="admin-dropdown-profile">

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <span>
                        {{ auth()->user()->email }}
                    </span>

                </div>


                <a href="javascript:void(0)">
                    <i class="fa-regular fa-user"></i>
                    My Profile
                </a>


                <a
                    href="{{ route('admin.website-settings.app-settings') }}"
                >
                    <i class="fa-solid fa-gear"></i>
                    Settings
                </a>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button type="submit">

                        <i class="fa-solid fa-arrow-right-from-bracket"></i>

                        Log out

                    </button>

                </form>

            </div>

        </div>

    </div>

</header>