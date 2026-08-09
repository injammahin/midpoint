document.addEventListener('DOMContentLoaded', () => {

    const sidebar =
        document.getElementById(
            'accountSidebar'
        );


    const sidebarToggle =
        document.getElementById(
            'accountSidebarToggle'
        );


    const backdrop =
        document.getElementById(
            'accountSidebarBackdrop'
        );


    const toast =
        document.getElementById(
            'accountToast'
        );


    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    const openSidebar = () => {

        if (!sidebar) {
            return;
        }


        sidebar.classList.add(
            'open'
        );


        backdrop?.classList.add(
            'show'
        );


        document.body.classList.add(
            'account-menu-open'
        );


        sidebarToggle?.setAttribute(
            'aria-expanded',
            'true'
        );

    };


    const closeSidebar = () => {

        if (!sidebar) {
            return;
        }


        sidebar.classList.remove(
            'open'
        );


        backdrop?.classList.remove(
            'show'
        );


        document.body.classList.remove(
            'account-menu-open'
        );


        sidebarToggle?.setAttribute(
            'aria-expanded',
            'false'
        );

    };


    sidebarToggle?.addEventListener(
        'click',
        () => {

            if (
                sidebar?.classList.contains(
                    'open'
                )
            ) {

                closeSidebar();

                return;
            }


            openSidebar();

        }
    );


    backdrop?.addEventListener(
        'click',
        closeSidebar
    );


    sidebar
        ?.querySelectorAll(
            'a'
        )
        .forEach(
            (link) => {

                link.addEventListener(
                    'click',
                    closeSidebar
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Escape closes sidebar
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        (event) => {

            if (
                event.key === 'Escape'
            ) {

                closeSidebar();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Reset when returning to desktop
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'resize',
        () => {

            if (
                window.innerWidth > 960
            ) {

                closeSidebar();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Dashboard Toast
    |--------------------------------------------------------------------------
    */

    let toastTimer;


    const showToast = (
        message
    ) => {

        if (
            !toast
            ||
            !message
        ) {

            return;
        }


        toast.textContent =
            message;


        toast.classList.add(
            'show'
        );


        clearTimeout(
            toastTimer
        );


        toastTimer =
            setTimeout(
                () => {

                    toast.classList.remove(
                        'show'
                    );

                },
                3200
            );

    };


    document
        .querySelectorAll(
            '[data-dashboard-toast]'
        )
        .forEach(
            (button) => {

                button.addEventListener(
                    'click',
                    () => {

                        showToast(
                            button.dataset
                                .dashboardToast
                        );

                    }
                );

            }
        );

});