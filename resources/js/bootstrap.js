import axios from 'axios';

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';


window.axios = axios;


window.axios.defaults.headers.common[
    'X-Requested-With'
] = 'XMLHttpRequest';


const csrfToken =
    document
        .querySelector(
            'meta[name="csrf-token"]'
        )
        ?.getAttribute(
            'content'
        );


if (csrfToken) {

    window.axios.defaults.headers.common[
        'X-CSRF-TOKEN'
    ] = csrfToken;

}


window.Pusher =
    Pusher;


if (
    import.meta.env
        .VITE_PUSHER_APP_KEY
) {

    window.Echo =
        new Echo({

            broadcaster:
                'pusher',

            key:
                import.meta.env
                    .VITE_PUSHER_APP_KEY,

            cluster:
                import.meta.env
                    .VITE_PUSHER_APP_CLUSTER,

            forceTLS:
                true,

            authEndpoint:
                '/broadcasting/auth',

            auth: {

                headers: {

                    'X-CSRF-TOKEN':
                        csrfToken || '',

                },

            },

        });

}