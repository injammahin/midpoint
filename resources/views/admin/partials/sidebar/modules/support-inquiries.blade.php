@php

    /*
    |--------------------------------------------------------------------------
    | Support Module Active
    |--------------------------------------------------------------------------
    */

    $supportActive =
        request()->routeIs(
            'admin.support-inquiries.*'
        )
        ||
        request()->routeIs(
            'admin.live-support.*'
        );


    /*
    |--------------------------------------------------------------------------
    | Contact Messages
    |--------------------------------------------------------------------------
    */

    $contactUnread =
        $adminUnreadContactCount
        ??
        $contactUnreadCount
        ??
        0;


    /*
    |--------------------------------------------------------------------------
    | Support Messages
    |--------------------------------------------------------------------------
    */

    $supportUnread =
        $supportUnreadCount
        ?? 0;


    /*
    |--------------------------------------------------------------------------
    | Live Support Waiting Queue
    |--------------------------------------------------------------------------
    */

    $liveSupportWaiting =
        $liveSupportWaitingCount
        ?? 0;


    /*
    |--------------------------------------------------------------------------
    | Total Support Count
    |--------------------------------------------------------------------------
    */

    $totalSupportCount =
        $contactUnread
        +
        $supportUnread
        +
        $liveSupportWaiting;

@endphp



{{-- =========================================================
    SUPPORT & INQUIRIES GROUP
========================================================== --}}

<div
    class="
        admin-menu-group
        {{
            $supportActive
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
                $supportActive
                    ? 'active-parent'
                    : ''
            }}
        "
        data-sidebar-group
        data-tooltip="Support & Inquiries"
        aria-expanded="{{
            $supportActive
                ? 'true'
                : 'false'
        }}"
    >

        {{-- Icon --}}
        <span class="admin-menu-icon">

            <i class="fa-solid fa-headset"></i>

        </span>


        {{-- Label --}}
        <span class="admin-menu-label">

            Support & Inquiries

        </span>


        {{-- Total Count --}}
        @if(
            $totalSupportCount > 0
        )

            <span class="admin-menu-count">

                {{
                    $totalSupportCount > 99
                        ? '99+'
                        : $totalSupportCount
                }}

            </span>

        @endif


        {{-- Chevron --}}
        <span class="admin-menu-chevron">

            <i class="fa-solid fa-chevron-down"></i>

        </span>

    </button>



    {{-- =====================================================
        EXPANDED SUBMENU
    ====================================================== --}}

    <div class="admin-submenu">


        {{-- =================================================
            CONTACT MESSAGES
        ================================================== --}}

        <a
            href="{{
                route(
                    'admin.support-inquiries.contacts'
                )
            }}"
            class="{{
                request()->routeIs(
                    'admin.support-inquiries.contacts*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i class="fa-solid fa-envelope"></i>


            <span>
                Contact Messages
            </span>


            @if(
                $contactUnread > 0
            )

                <span class="admin-submenu-count">

                    {{
                        $contactUnread > 99
                            ? '99+'
                            : $contactUnread
                    }}

                </span>

            @endif

        </a>



        {{-- =================================================
            SUPPORT MESSAGES
        ================================================== --}}

        <a
            href="{{
                route(
                    'admin.support-inquiries.support-messages'
                )
            }}"
            class="{{
                request()->routeIs(
                    'admin.support-inquiries.support-messages*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i class="fa-solid fa-comments"></i>


            <span>
                Support Messages
            </span>


            @if(
                $supportUnread > 0
            )

                <span class="admin-submenu-count">

                    {{
                        $supportUnread > 99
                            ? '99+'
                            : $supportUnread
                    }}

                </span>

            @endif

        </a>



        {{-- =================================================
            LIVE SUPPORT
        ================================================== --}}

        <a
            href="{{
                route(
                    'admin.live-support.index'
                )
            }}"
            class="{{
                request()->routeIs(
                    'admin.live-support.index'
                )
                ||
                request()->routeIs(
                    'admin.live-support.feed'
                )
                ||
                request()->routeIs(
                    'admin.live-support.heartbeat'
                )
                ||
                request()->routeIs(
                    'admin.live-support.availability'
                )
                ||
                request()->routeIs(
                    'admin.live-support.claim'
                )
                ||
                request()->routeIs(
                    'admin.live-support.resolve'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i class="fa-solid fa-headset"></i>


            <span>
                Live Support
            </span>


            @if(
                $liveSupportWaiting > 0
            )

                <span
                    class="
                        admin-submenu-count
                        admin-submenu-count-live
                    "
                    title="{{
                        $liveSupportWaiting
                    }} customer(s) waiting"
                >

                    {{
                        $liveSupportWaiting > 99
                            ? '99+'
                            : $liveSupportWaiting
                    }}

                </span>

            @else

                <span
                    class="admin-live-status-dot"
                    title="No customers waiting"
                ></span>

            @endif

        </a>



        {{-- =================================================
            LIVE SUPPORT SETTINGS
        ================================================== --}}

        <a
            href="{{
                route(
                    'admin.live-support.settings'
                )
            }}"
            class="{{
                request()->routeIs(
                    'admin.live-support.settings*'
                )
                ||
                request()->routeIs(
                    'admin.live-support.blackouts.*'
                )
                ||
                request()->routeIs(
                    'admin.live-support.agents.*'
                )
                    ? 'active'
                    : ''
            }}"
        >

            <i class="fa-solid fa-sliders"></i>


            <span>
                Live Support Settings
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

                <i class="fa-solid fa-headset"></i>

            </span>


            <div>

                <strong>
                    Support & Inquiries
                </strong>

                <span>
                    4 submenus
                </span>

            </div>


            @if(
                $totalSupportCount > 0
            )

                <span class="admin-flyout-count">

                    {{
                        $totalSupportCount > 99
                            ? '99+'
                            : $totalSupportCount
                    }}

                </span>

            @endif

        </div>



        {{-- =================================================
            FLYOUT LINKS
        ================================================== --}}

        <div class="admin-flyout-links">


            {{-- Contact Messages --}}
            <a
                href="{{
                    route(
                        'admin.support-inquiries.contacts'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.support-inquiries.contacts*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-envelope"></i>


                <span>
                    Contact Messages
                </span>


                @if(
                    $contactUnread > 0
                )

                    <span class="admin-submenu-count">

                        {{
                            $contactUnread > 99
                                ? '99+'
                                : $contactUnread
                        }}

                    </span>

                @endif

            </a>



            {{-- Support Messages --}}
            <a
                href="{{
                    route(
                        'admin.support-inquiries.support-messages'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.support-inquiries.support-messages*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-comments"></i>


                <span>
                    Support Messages
                </span>


                @if(
                    $supportUnread > 0
                )

                    <span class="admin-submenu-count">

                        {{
                            $supportUnread > 99
                                ? '99+'
                                : $supportUnread
                        }}

                    </span>

                @endif

            </a>



            {{-- Live Support --}}
            <a
                href="{{
                    route(
                        'admin.live-support.index'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.live-support.index'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.feed'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.heartbeat'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.availability'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.claim'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.resolve'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-headset"></i>


                <span>
                    Live Support
                </span>


                @if(
                    $liveSupportWaiting > 0
                )

                    <span
                        class="
                            admin-submenu-count
                            admin-submenu-count-live
                        "
                    >

                        {{
                            $liveSupportWaiting > 99
                                ? '99+'
                                : $liveSupportWaiting
                        }}

                    </span>

                @else

                    <span
                        class="admin-live-status-dot"
                        title="No customers waiting"
                    ></span>

                @endif

            </a>



            {{-- Live Support Settings --}}
            <a
                href="{{
                    route(
                        'admin.live-support.settings'
                    )
                }}"
                class="{{
                    request()->routeIs(
                        'admin.live-support.settings*'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.blackouts.*'
                    )
                    ||
                    request()->routeIs(
                        'admin.live-support.agents.*'
                    )
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-sliders"></i>


                <span>
                    Live Support Settings
                </span>

            </a>

        </div>

    </div>

</div>