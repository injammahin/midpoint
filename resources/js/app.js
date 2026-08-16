/*
|--------------------------------------------------------------------------
| Laravel Bootstrap
|--------------------------------------------------------------------------
*/

import './bootstrap';
import QRCode from 'qrcode';

window.MidpointQRCode =
    QRCode;

/*
|--------------------------------------------------------------------------
| Font Awesome
|--------------------------------------------------------------------------
*/

import '@fortawesome/fontawesome-free/css/all.min.css';
import './live-support-user';
import '../css/live-support.css';

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Public Mobile Navigation
    |--------------------------------------------------------------------------
    */

    const menuButton =
        document.getElementById('mobile-menu-button');

    const mobileMenu =
        document.getElementById('mobile-menu');

    const mobileMenuOverlay =
        document.getElementById('mobile-menu-overlay');

    const openIcon =
        document.getElementById('menu-open-icon');

    const closeIcon =
        document.getElementById('menu-close-icon');


    /*
    |--------------------------------------------------------------------------
    | Only initialize on pages containing the public header.
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Don't `return` from the entire DOMContentLoaded callback because later
    | this app.js file may contain other frontend modules.
    |
    */

    if (menuButton && mobileMenu) {

        let menuIsOpen = false;


        /*
        |--------------------------------------------------------------------------
        | Open Menu
        |--------------------------------------------------------------------------
        */

        const openMenu = () => {

            menuIsOpen = true;


            /*
            |--------------------------------------------------------------------------
            | Drawer
            |--------------------------------------------------------------------------
            */

            mobileMenu.classList.remove(
                'translate-x-full'
            );

            mobileMenu.classList.add(
                'translate-x-0'
            );


            /*
            |--------------------------------------------------------------------------
            | Overlay
            |--------------------------------------------------------------------------
            */

            if (mobileMenuOverlay) {

                mobileMenuOverlay.classList.remove(
                    'invisible',
                    'pointer-events-none',
                    'opacity-0'
                );

                mobileMenuOverlay.classList.add(
                    'visible',
                    'pointer-events-auto',
                    'opacity-100'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Icons
            |--------------------------------------------------------------------------
            */

            openIcon?.classList.add(
                'hidden'
            );

            closeIcon?.classList.remove(
                'hidden'
            );


            /*
            |--------------------------------------------------------------------------
            | Accessibility
            |--------------------------------------------------------------------------
            */

            menuButton.setAttribute(
                'aria-expanded',
                'true'
            );

            menuButton.setAttribute(
                'aria-label',
                'Close menu'
            );


            /*
            |--------------------------------------------------------------------------
            | Prevent Background Scrolling
            |--------------------------------------------------------------------------
            */

            document.body.classList.add(
                'overflow-hidden'
            );

        };


        /*
        |--------------------------------------------------------------------------
        | Close Menu
        |--------------------------------------------------------------------------
        */

        const closeMenu = () => {

            menuIsOpen = false;


            /*
            |--------------------------------------------------------------------------
            | Drawer
            |--------------------------------------------------------------------------
            */

            mobileMenu.classList.remove(
                'translate-x-0'
            );

            mobileMenu.classList.add(
                'translate-x-full'
            );


            /*
            |--------------------------------------------------------------------------
            | Overlay
            |--------------------------------------------------------------------------
            */

            if (mobileMenuOverlay) {

                mobileMenuOverlay.classList.remove(
                    'visible',
                    'pointer-events-auto',
                    'opacity-100'
                );

                mobileMenuOverlay.classList.add(
                    'invisible',
                    'pointer-events-none',
                    'opacity-0'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Icons
            |--------------------------------------------------------------------------
            */

            openIcon?.classList.remove(
                'hidden'
            );

            closeIcon?.classList.add(
                'hidden'
            );


            /*
            |--------------------------------------------------------------------------
            | Accessibility
            |--------------------------------------------------------------------------
            */

            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );

            menuButton.setAttribute(
                'aria-label',
                'Open menu'
            );


            /*
            |--------------------------------------------------------------------------
            | Restore Background Scrolling
            |--------------------------------------------------------------------------
            */

            document.body.classList.remove(
                'overflow-hidden'
            );

        };


        /*
        |--------------------------------------------------------------------------
        | Toggle Menu
        |--------------------------------------------------------------------------
        */

        menuButton.addEventListener(
            'click',
            (event) => {

                event.preventDefault();

                event.stopPropagation();


                if (menuIsOpen) {

                    closeMenu();

                } else {

                    openMenu();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Click Overlay
        |--------------------------------------------------------------------------
        */

        mobileMenuOverlay?.addEventListener(
            'click',
            () => {

                closeMenu();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Close Button Inside Drawer
        |--------------------------------------------------------------------------
        */

        const drawerCloseButton =
            document.getElementById(
                'mobile-menu-close-button'
            );


        drawerCloseButton?.addEventListener(
            'click',
            () => {

                closeMenu();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Close After Clicking Navigation Link
        |--------------------------------------------------------------------------
        */

        mobileMenu
            .querySelectorAll('a')
            .forEach((link) => {

                link.addEventListener(
                    'click',
                    () => {

                        closeMenu();

                    }
                );

            });


        /*
        |--------------------------------------------------------------------------
        | ESC
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            (event) => {

                if (
                    event.key === 'Escape'
                    &&
                    menuIsOpen
                ) {

                    closeMenu();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Return To Desktop
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'resize',
            () => {

                if (
                    window.innerWidth >= 1024
                    &&
                    menuIsOpen
                ) {

                    closeMenu();

                }

            }
        );

    }

});