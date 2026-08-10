/*
|--------------------------------------------------------------------------
| Font Awesome
|--------------------------------------------------------------------------
*/

import '@fortawesome/fontawesome-free/css/all.min.css';
import './live-support-admin';
import './admin-notifications';
import '../css/live-support.css';

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Main Elements
    |--------------------------------------------------------------------------
    */

    const shell =
        document.getElementById('adminShell');

    const sidebarToggle =
        document.getElementById('adminSidebarToggle');

    const overlay =
        document.getElementById('adminSidebarOverlay');


    /*
    |--------------------------------------------------------------------------
    | User Dropdown Elements
    |--------------------------------------------------------------------------
    */

    const userDropdown =
        document.querySelector(
            '.admin-user-dropdown'
        );

    const userDropdownButton =
        document.getElementById(
            'adminUserDropdownButton'
        );


    /*
    |--------------------------------------------------------------------------
    | Notification Dropdown Elements
    |--------------------------------------------------------------------------
    */

    const notificationDropdown =
        document.getElementById(
            'adminNotificationDropdown'
        );

    const notificationButton =
        document.getElementById(
            'adminNotificationButton'
        );


    /*
    |--------------------------------------------------------------------------
    | Theme Elements
    |--------------------------------------------------------------------------
    */

    const themeToggle =
        document.getElementById(
            'adminThemeToggle'
        );

    const themeIcon =
        document.getElementById(
            'adminThemeIcon'
        );

    const root =
        document.documentElement;


    /*
    |--------------------------------------------------------------------------
    | Responsive Breakpoint
    |--------------------------------------------------------------------------
    */

    const desktopBreakpoint = 900;

    const isDesktop = () => {
        return window.innerWidth > desktopBreakpoint;
    };


    /*
    |--------------------------------------------------------------------------
    | Helper: Close All Flyouts
    |--------------------------------------------------------------------------
    */

    function closeAllFlyouts() {

        document
            .querySelectorAll(
                '.admin-flyout.show'
            )
            .forEach((flyout) => {

                flyout.classList.remove(
                    'show'
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Close User Dropdown
    |--------------------------------------------------------------------------
    */

    function closeUserDropdown() {

        userDropdown
            ?.classList
            .remove('open');

    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Close Notification Dropdown
    |--------------------------------------------------------------------------
    */

    function closeNotificationDropdown() {

        notificationDropdown
            ?.classList
            .remove('open');

    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Close Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    function closeMobileSidebar() {

        shell
            ?.classList
            .remove(
                'mobile-sidebar-open'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Close Everything
    |--------------------------------------------------------------------------
    */

    function closeAdminPopups() {

        closeAllFlyouts();

        closeUserDropdown();

        closeNotificationDropdown();

    }


    /*
    |--------------------------------------------------------------------------
    | Restore Sidebar State
    |--------------------------------------------------------------------------
    */

    function restoreDesktopSidebarState() {

        if (!shell || !isDesktop()) {
            return;
        }


        const savedState =
            localStorage.getItem(
                'midpoint_admin_sidebar'
            );


        if (savedState === 'collapsed') {

            shell.classList.add(
                'sidebar-collapsed'
            );

        } else {

            shell.classList.remove(
                'sidebar-collapsed'
            );

        }

    }


    restoreDesktopSidebarState();


    /*
    |--------------------------------------------------------------------------
    | Sidebar Toggle
    |--------------------------------------------------------------------------
    */

    sidebarToggle?.addEventListener(
        'click',
        (event) => {

            event.stopPropagation();


            if (!shell) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Desktop Sidebar
            |--------------------------------------------------------------------------
            */

            if (isDesktop()) {

                shell.classList.toggle(
                    'sidebar-collapsed'
                );


                const isCollapsed =
                    shell.classList.contains(
                        'sidebar-collapsed'
                    );


                localStorage.setItem(
                    'midpoint_admin_sidebar',
                    isCollapsed
                        ? 'collapsed'
                        : 'expanded'
                );


                closeAdminPopups();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Mobile Sidebar
            |--------------------------------------------------------------------------
            */

            shell.classList.toggle(
                'mobile-sidebar-open'
            );


            closeUserDropdown();

            closeNotificationDropdown();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Mobile Overlay
    |--------------------------------------------------------------------------
    */

    overlay?.addEventListener(
        'click',
        () => {

            closeMobileSidebar();

            closeAdminPopups();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Sidebar Accordion Menus
    |--------------------------------------------------------------------------
    */

    const sidebarGroupButtons =
        document.querySelectorAll(
            '[data-sidebar-group]'
        );


    sidebarGroupButtons.forEach(
        (button) => {

            button.addEventListener(
                'click',
                (event) => {

                    event.preventDefault();

                    event.stopPropagation();


                    /*
                    |--------------------------------------------------------------------------
                    | On desktop collapsed mode, submenu opens through hover.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isDesktop()
                        &&
                        shell?.classList.contains(
                            'sidebar-collapsed'
                        )
                    ) {

                        return;

                    }


                    const currentGroup =
                        button.closest(
                            '.admin-menu-group'
                        );


                    if (!currentGroup) {
                        return;
                    }


                    const willOpen =
                        !currentGroup.classList.contains(
                            'is-open'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Close Other Sidebar Groups
                    |--------------------------------------------------------------------------
                    |
                    | This keeps the sidebar clean even when it eventually has
                    | many modules.
                    |
                    */

                    document
                        .querySelectorAll(
                            '.admin-menu-group.is-open'
                        )
                        .forEach((group) => {

                            if (
                                group !==
                                currentGroup
                            ) {

                                group.classList.remove(
                                    'is-open'
                                );

                            }

                        });


                    currentGroup
                        .classList
                        .toggle(
                            'is-open',
                            willOpen
                        );

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Collapsed Sidebar Flyout Menus
    |--------------------------------------------------------------------------
    */

    const sidebarGroups =
        document.querySelectorAll(
            '.admin-menu-group'
        );


    sidebarGroups.forEach(
        (group) => {

            const flyout =
                group.querySelector(
                    '.admin-flyout'
                );


            if (!flyout) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Mouse Enter
            |--------------------------------------------------------------------------
            */

            group.addEventListener(
                'mouseenter',
                () => {

                    if (
                        !isDesktop()
                        ||
                        !shell?.classList.contains(
                            'sidebar-collapsed'
                        )
                    ) {

                        return;

                    }


                    closeAllFlyouts();


                    /*
                    |--------------------------------------------------------------------------
                    | Show First So We Can Read Its Height
                    |--------------------------------------------------------------------------
                    */

                    flyout.classList.add(
                        'show'
                    );


                    const groupRect =
                        group.getBoundingClientRect();


                    const flyoutHeight =
                        flyout.offsetHeight;


                    const viewportHeight =
                        window.innerHeight;


                    let top =
                        groupRect.top;


                    /*
                    |--------------------------------------------------------------------------
                    | Keep Flyout Inside Bottom of Screen
                    |--------------------------------------------------------------------------
                    */

                    if (
                        top +
                        flyoutHeight >
                        viewportHeight - 15
                    ) {

                        top =
                            viewportHeight
                            - flyoutHeight
                            - 15;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Keep Flyout Away From Top
                    |--------------------------------------------------------------------------
                    */

                    top =
                        Math.max(
                            10,
                            top
                        );


                    flyout.style.top =
                        `${top}px`;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Mouse Leave
            |--------------------------------------------------------------------------
            */

            group.addEventListener(
                'mouseleave',
                () => {

                    flyout.classList.remove(
                        'show'
                    );

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Close Mobile Sidebar After Clicking Sidebar Link
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.admin-sidebar a'
        )
        .forEach((link) => {

            link.addEventListener(
                'click',
                () => {

                    if (!isDesktop()) {

                        closeMobileSidebar();

                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Notification Dropdown
    |--------------------------------------------------------------------------
    */

    notificationButton?.addEventListener(
        'click',
        (event) => {

            event.preventDefault();

            event.stopPropagation();


            /*
            |--------------------------------------------------------------------------
            | Close Account Dropdown
            |--------------------------------------------------------------------------
            */

            closeUserDropdown();

            closeAllFlyouts();


            /*
            |--------------------------------------------------------------------------
            | Toggle Notifications
            |--------------------------------------------------------------------------
            */

            notificationDropdown
                ?.classList
                .toggle('open');

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Prevent Notification Menu Click From Closing Itself
    |--------------------------------------------------------------------------
    */

    notificationDropdown?.addEventListener(
        'click',
        (event) => {

            event.stopPropagation();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | User Dropdown
    |--------------------------------------------------------------------------
    */

    userDropdownButton?.addEventListener(
        'click',
        (event) => {

            event.preventDefault();

            event.stopPropagation();


            /*
            |--------------------------------------------------------------------------
            | Close Notification Dropdown
            |--------------------------------------------------------------------------
            */

            closeNotificationDropdown();

            closeAllFlyouts();


            /*
            |--------------------------------------------------------------------------
            | Toggle User Dropdown
            |--------------------------------------------------------------------------
            */

            userDropdown
                ?.classList
                .toggle('open');

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Prevent User Dropdown Click From Closing Itself
    |--------------------------------------------------------------------------
    */

    userDropdown?.addEventListener(
        'click',
        (event) => {

            event.stopPropagation();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Click Anywhere Outside Dropdowns
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        (event) => {

            /*
            |--------------------------------------------------------------------------
            | User Dropdown
            |--------------------------------------------------------------------------
            */

            if (
                userDropdown
                &&
                !userDropdown.contains(
                    event.target
                )
            ) {

                closeUserDropdown();

            }


            /*
            |--------------------------------------------------------------------------
            | Notification Dropdown
            |--------------------------------------------------------------------------
            */

            if (
                notificationDropdown
                &&
                !notificationDropdown.contains(
                    event.target
                )
            ) {

                closeNotificationDropdown();

            }


            /*
            |--------------------------------------------------------------------------
            | Flyouts
            |--------------------------------------------------------------------------
            */

            if (
                !event.target.closest(
                    '.admin-menu-group'
                )
            ) {

                closeAllFlyouts();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Admin Theme
    |--------------------------------------------------------------------------
    */

    const savedTheme =
        localStorage.getItem(
            'midpoint_admin_theme'
        );


    /*
    |--------------------------------------------------------------------------
    | Restore Saved Theme
    |--------------------------------------------------------------------------
    */

    if (
        savedTheme === 'light'
        ||
        savedTheme === 'dark'
    ) {

        root.setAttribute(
            'data-admin-theme',
            savedTheme
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Theme Icon
    |--------------------------------------------------------------------------
    */

    function syncThemeIcon() {

        if (!themeIcon) {
            return;
        }


        const currentTheme =
            root.getAttribute(
                'data-admin-theme'
            );


        /*
        |--------------------------------------------------------------------------
        | Show What Clicking Will Change To
        |--------------------------------------------------------------------------
        */

        if (currentTheme === 'dark') {

            themeIcon.className =
                'fa-solid fa-sun';

        } else {

            themeIcon.className =
                'fa-solid fa-moon';

        }

    }


    syncThemeIcon();


    /*
    |--------------------------------------------------------------------------
    | Theme Toggle
    |--------------------------------------------------------------------------
    */

    themeToggle?.addEventListener(
        'click',
        (event) => {

            event.preventDefault();

            event.stopPropagation();


            const currentTheme =
                root.getAttribute(
                    'data-admin-theme'
                );


            const newTheme =
                currentTheme === 'dark'
                    ? 'light'
                    : 'dark';


            root.setAttribute(
                'data-admin-theme',
                newTheme
            );


            localStorage.setItem(
                'midpoint_admin_theme',
                newTheme
            );


            syncThemeIcon();


            closeUserDropdown();

            closeNotificationDropdown();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Window Resize
    |--------------------------------------------------------------------------
    */

    let resizeTimer = null;


    window.addEventListener(
        'resize',
        () => {

            clearTimeout(
                resizeTimer
            );


            resizeTimer =
                setTimeout(
                    () => {

                        closeAllFlyouts();

                        closeUserDropdown();

                        closeNotificationDropdown();


                        /*
                        |--------------------------------------------------------------------------
                        | Desktop
                        |--------------------------------------------------------------------------
                        */

                        if (isDesktop()) {

                            closeMobileSidebar();


                            /*
                            |--------------------------------------------------------------------------
                            | Restore user's collapsed preference.
                            |--------------------------------------------------------------------------
                            */

                            restoreDesktopSidebarState();

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Mobile
                            |--------------------------------------------------------------------------
                            |
                            | Desktop collapsed class should never affect the
                            | mobile sidebar.
                            |
                            */

                            shell
                                ?.classList
                                .remove(
                                    'sidebar-collapsed'
                                );

                        }

                    },
                    100
                );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        (event) => {

            if (
                event.key !== 'Escape'
            ) {

                return;

            }


            closeAdminPopups();

            closeMobileSidebar();

        }
    );

});