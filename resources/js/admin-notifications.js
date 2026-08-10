document.addEventListener(
    'DOMContentLoaded',
    () => {

        /*
        |--------------------------------------------------------------------------
        | Root
        |--------------------------------------------------------------------------
        */

        const dropdown =
            document.getElementById(
                'adminNotificationDropdown'
            );


        if (!dropdown) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const feedUrl =
            dropdown.dataset
                .notificationFeed;


        const countBadge =
            document.getElementById(
                'adminNotificationCount'
            );


        const unreadText =
            document.getElementById(
                'adminNotificationUnreadText'
            );


        const notificationList =
            document.getElementById(
                'adminNotificationList'
            );


        const readAllForm =
            document.getElementById(
                'adminNotificationReadAllForm'
            );


        const bellButton =
            document.getElementById(
                'adminNotificationButton'
            );


        if (
            !feedUrl
            ||
            !notificationList
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Overlapping Requests
        |--------------------------------------------------------------------------
        */

        let loading =
            false;


        /*
        |--------------------------------------------------------------------------
        | Escape HTML Is Not Needed
        |--------------------------------------------------------------------------
        |
        | We use textContent rather than innerHTML for user-controlled text.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Create Empty State
        |--------------------------------------------------------------------------
        */

        function createEmptyState()
        {
            const container =
                document.createElement(
                    'div'
                );


            container.className =
                'admin-notification-empty';


            const icon =
                document.createElement(
                    'i'
                );


            icon.className =
                'fa-regular fa-bell';


            const title =
                document.createElement(
                    'strong'
                );


            title.textContent =
                'No notifications';


            const message =
                document.createElement(
                    'span'
                );


            message.textContent =
                "You're all caught up.";


            container.append(
                icon,
                title,
                message
            );


            return container;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Notification Item
        |--------------------------------------------------------------------------
        */

        function createNotificationItem(
            notification
        ) {
            const link =
                document.createElement(
                    'a'
                );


            link.href =
                notification.open_url;


            link.className =
                'admin-notification-item';


            if (
                notification.unread
            ) {

                link.classList.add(
                    'unread'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Icon
            |--------------------------------------------------------------------------
            */

            const iconWrap =
                document.createElement(
                    'span'
                );


            iconWrap.className =
                'admin-notification-item-icon';


            const icon =
                document.createElement(
                    'i'
                );


            icon.className =
                'fa-solid '
                +
                (
                    notification.icon
                    ||
                    'fa-bell'
                );


            iconWrap.appendChild(
                icon
            );


            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            const content =
                document.createElement(
                    'span'
                );


            content.className =
                'admin-notification-item-content';


            const title =
                document.createElement(
                    'strong'
                );


            title.textContent =
                notification.title
                ||
                'Notification';


            const message =
                document.createElement(
                    'span'
                );


            message.textContent =
                notification.message
                ||
                '';


            const time =
                document.createElement(
                    'small'
                );


            time.textContent =
                notification.created_at
                ||
                '';


            content.append(
                title,
                message,
                time
            );


            /*
            |--------------------------------------------------------------------------
            | Unread Dot
            |--------------------------------------------------------------------------
            */

            link.append(
                iconWrap,
                content
            );


            if (
                notification.unread
            ) {

                const dot =
                    document.createElement(
                        'span'
                    );


                dot.className =
                    'admin-notification-unread-dot';


                link.appendChild(
                    dot
                );
            }


            return link;
        }


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        function renderNotifications(
            data
        ) {
            const count =
                Number(
                    data.unread_count
                    ||
                    0
                );


            /*
            |--------------------------------------------------------------------------
            | Badge
            |--------------------------------------------------------------------------
            */

            if (countBadge) {

                if (count > 0) {

                    countBadge.textContent =
                        count > 99
                            ? '99+'
                            : String(
                                count
                            );


                    countBadge.style.display =
                        '';

                } else {

                    countBadge.style.display =
                        'none';
                }

            }


            /*
            |--------------------------------------------------------------------------
            | Header Text
            |--------------------------------------------------------------------------
            */

            if (unreadText) {

                unreadText.textContent =
                    `${count} unread`;
            }


            /*
            |--------------------------------------------------------------------------
            | Mark All
            |--------------------------------------------------------------------------
            */

            if (readAllForm) {

                readAllForm.style.display =
                    count > 0
                        ? ''
                        : 'none';
            }


            /*
            |--------------------------------------------------------------------------
            | Notification List
            |--------------------------------------------------------------------------
            */

            notificationList
                .replaceChildren();


            const notifications =
                Array.isArray(
                    data.notifications
                )
                    ? data.notifications
                    : [];


            if (
                notifications.length
                ===
                0
            ) {

                notificationList.appendChild(
                    createEmptyState()
                );


                return;
            }


            notifications.forEach(
                (notification) => {

                    notificationList.appendChild(
                        createNotificationItem(
                            notification
                        )
                    );

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fetch
        |--------------------------------------------------------------------------
        */

        async function refreshNotifications()
        {
            if (loading) {
                return;
            }


            loading =
                true;


            try {

                const response =
                    await fetch(
                        feedUrl,
                        {
                            method:
                                'GET',

                            credentials:
                                'same-origin',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );


                if (!response.ok) {

                    return;
                }


                const data =
                    await response.json();


                if (
                    data.success
                    !==
                    true
                ) {

                    return;
                }


                renderNotifications(
                    data
                );

            } catch (error) {

                console.error(
                    'Unable to refresh admin notifications.',
                    error
                );

            } finally {

                loading =
                    false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Initial Refresh
        |--------------------------------------------------------------------------
        */

        refreshNotifications();


        /*
        |--------------------------------------------------------------------------
        | Refresh Every 5 Seconds
        |--------------------------------------------------------------------------
        |
        | This makes:
        |
        | contact message
        | seller application
        | payment
        |
        | appear while the admin remains on the page.
        |
        */

        const timer =
            setInterval(
                refreshNotifications,
                5000
            );


        /*
        |--------------------------------------------------------------------------
        | Bell Click
        |--------------------------------------------------------------------------
        */

        bellButton
            ?.addEventListener(
                'click',
                () => {

                    refreshNotifications();

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Browser Tab Becomes Visible
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'visibilitychange',
            () => {

                if (
                    document.visibilityState
                    ===
                    'visible'
                ) {

                    refreshNotifications();
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Clean Up
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'beforeunload',
            () => {

                clearInterval(
                    timer
                );

            }
        );

    }
);