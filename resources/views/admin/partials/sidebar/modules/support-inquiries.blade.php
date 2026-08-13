@php

    $canContacts =
        auth()
            ->user()
            ->hasAdminPermission(
                'support.contacts.manage'
            );


    $canMessages =
        auth()
            ->user()
            ->hasAdminPermission(
                'support.messages.view'
            );


    $canLive =
        auth()
            ->user()
            ->hasAdminPermission(
                'support.live.manage'
            );


    $canLiveSettings =
        auth()
            ->user()
            ->hasAdminPermission(
                'support.live_settings.manage'
            );


    $supportActive =
        request()->routeIs(
            'admin.support-inquiries.*'
        )
        ||
        request()->routeIs(
            'admin.live-support.*'
        );


    $contactUnread =
        $adminUnreadContactCount
        ??
        $contactUnreadCount
        ??
        0;


    $supportUnread =
        $supportUnreadCount
        ??
        0;


    $liveSupportWaiting =
        $liveSupportWaitingCount
        ??
        0;


    $totalSupportCount =

        (
            $canContacts
                ? $contactUnread
                : 0
        )

        +

        (
            $canMessages
                ? $supportUnread
                : 0
        )

        +

        (
            $canLive
                ? $liveSupportWaiting
                : 0
        );

@endphp



@if(
    $canContacts
    ||
    $canMessages
    ||
    $canLive
    ||
    $canLiveSettings
)

<div
    class="
        admin-menu-group
        {{ $supportActive ? 'is-open' : '' }}
    "
>


    <button
        type="button"

        class="
            admin-menu-link
            admin-menu-toggle
            {{ $supportActive ? 'active-parent' : '' }}
        "

        data-sidebar-group

        data-tooltip="Support & Inquiries"

        aria-expanded="{{ $supportActive ? 'true' : 'false' }}"
    >

        <span class="admin-menu-icon">

            <i class="fa-solid fa-headset"></i>

        </span>


        <span class="admin-menu-label">

            Support & Inquiries

        </span>


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


        <span class="admin-menu-chevron">

            <i class="fa-solid fa-chevron-down"></i>

        </span>

    </button>



    <div class="admin-submenu">


        @if($canContacts)

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

        @endif



        @if($canMessages)

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

            </a>

        @endif



        @if($canLive)

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
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-headset"></i>

                <span>
                    Live Support
                </span>

            </a>

        @endif



        @if($canLiveSettings)

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
                        ? 'active'
                        : ''
                }}"
            >

                <i class="fa-solid fa-screwdriver-wrench"></i>

                <span>
                    Live Support Settings
                </span>

            </a>

        @endif

    </div>



    <div class="admin-flyout">


        <div class="admin-flyout-head">

            <span class="admin-flyout-icon">

                <i class="fa-solid fa-headset"></i>

            </span>


            <div>

                <strong>
                    Support & Inquiries
                </strong>

                <span>
                    Customer support
                </span>

            </div>

        </div>



        <div class="admin-flyout-links">


            @if($canContacts)

                <a
                    href="{{
                        route(
                            'admin.support-inquiries.contacts'
                        )
                    }}"
                >

                    <i class="fa-solid fa-envelope"></i>

                    <span>
                        Contact Messages
                    </span>

                </a>

            @endif


            @if($canMessages)

                <a
                    href="{{
                        route(
                            'admin.support-inquiries.support-messages'
                        )
                    }}"
                >

                    <i class="fa-solid fa-comments"></i>

                    <span>
                        Support Messages
                    </span>

                </a>

            @endif


            @if($canLive)

                <a
                    href="{{
                        route(
                            'admin.live-support.index'
                        )
                    }}"
                >

                    <i class="fa-solid fa-headset"></i>

                    <span>
                        Live Support
                    </span>

                </a>

            @endif


            @if($canLiveSettings)

                <a
                    href="{{
                        route(
                            'admin.live-support.settings'
                        )
                    }}"
                >

                    <i class="fa-solid fa-screwdriver-wrench"></i>

                    <span>
                        Live Support Settings
                    </span>

                </a>

            @endif

        </div>

    </div>

</div>

@endif