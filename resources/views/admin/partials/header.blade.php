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

    data-notification-feed="{{
        route(
            'admin.notifications.feed'
        )
    }}"
>

    {{-- =====================================================
        BELL BUTTON
    ====================================================== --}}

    <button
        type="button"
        id="adminNotificationButton"
        class="
            admin-icon-button
            admin-notification-button
        "
        title="Notifications"
        aria-label="Notifications"
    >

        <i class="fa-solid fa-bell"></i>


        <span
            id="adminNotificationCount"
            class="admin-notification-count"
            style="{{
                ($adminUnreadNotificationCount ?? 0) > 0
                    ? ''
                    : 'display:none;'
            }}"
        >

            {{
                ($adminUnreadNotificationCount ?? 0) > 99
                    ? '99+'
                    : ($adminUnreadNotificationCount ?? 0)
            }}

        </span>

    </button>



    {{-- =====================================================
        DROPDOWN
    ====================================================== --}}

    <div
        id="adminNotificationMenu"
        class="admin-notification-menu"
    >


        {{-- =================================================
            HEADER
        ================================================== --}}

        <div class="admin-notification-menu-head">

            <div>

                <strong>
                    Notifications
                </strong>


                <span id="adminNotificationUnreadText">

                    {{
                        $adminUnreadNotificationCount
                        ??
                        0
                    }}

                    unread

                </span>

            </div>



            <form
                id="adminNotificationReadAllForm"
                method="POST"
                action="{{
                    route(
                        'admin.notifications.read-all'
                    )
                }}"
                style="{{
                    ($adminUnreadNotificationCount ?? 0) > 0
                        ? ''
                        : 'display:none;'
                }}"
            >

                @csrf


                <button type="submit">

                    Mark all read

                </button>

            </form>

        </div>



        {{-- =================================================
            LIST
        ================================================== --}}

        <div
            class="admin-notification-list"
            id="adminNotificationList"
        >

            @if(
                ($adminLatestNotifications ?? collect())
                    ->count()
                >
                0
            )

                @foreach($adminLatestNotifications as $notification)

                    <a
                        href="{{
                            route(
                                'admin.notifications.open',
                                $notification->id
                            )
                        }}"

                        class="
                            admin-notification-item

                            {{
                                is_null(
                                    $notification->read_at
                                )
                                    ? 'unread'
                                    : ''
                            }}
                        "
                    >

                        <span
                            class="admin-notification-item-icon"
                        >

                            <i
                                class="
                                    fa-solid

                                    {{
                                        data_get(
                                            $notification->data,
                                            'icon',
                                            'fa-bell'
                                        )
                                    }}
                                "
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



                        @if(
                            is_null(
                                $notification->read_at
                            )
                        )

                            <span
                                class="
                                    admin-notification-unread-dot
                                "
                            ></span>

                        @endif

                    </a>

                @endforeach


            @else

                <div
                    class="admin-notification-empty"
                >

                    <i
                        class="fa-regular fa-bell"
                    ></i>


                    <strong>
                        No notifications
                    </strong>


                    <span>
                        You're all caught up.
                    </span>

                </div>

            @endif

        </div>

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