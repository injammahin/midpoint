import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({

    plugins: [

        laravel({

            input: [

                /*
                |--------------------------------------------------------------------------
                | Frontend
                |--------------------------------------------------------------------------
                */

                'resources/css/app.css',
                'resources/js/app.js',


                /*
                |--------------------------------------------------------------------------
                | Account / Seller Dashboard
                |--------------------------------------------------------------------------
                */

                'resources/css/account-dashboard.css',
                'resources/js/account-dashboard.js',


                /*
                |--------------------------------------------------------------------------
                | Admin
                |--------------------------------------------------------------------------
                */

                'resources/css/admin.css',
                'resources/js/admin.js',

            ],

            refresh: true,

        }),

    ],

});