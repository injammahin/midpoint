<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Admin Permissions
    |--------------------------------------------------------------------------
    */

    'permissions' => [

        /*
        |--------------------------------------------------------------------------
        | Overview
        |--------------------------------------------------------------------------
        */

        'dashboard.view' => [

            'label' =>
                'Dashboard',

            'group' =>
                'Overview',

        ],


        /*
        |--------------------------------------------------------------------------
        | Users & Applications
        |--------------------------------------------------------------------------
        */

        'users.manage' => [

            'label' =>
                'User Management',

            'group' =>
                'Users & Applications',

        ],


        'seller_applications.manage' => [

            'label' =>
                'Seller Applications',

            'group' =>
                'Users & Applications',

        ],


        'billing.invoices.view' => [

            'label' =>
                'Seller Invoices',

            'group' =>
                'Users & Applications',

        ],


        'billing.subscriptions.view' => [

            'label' =>
                'Purchased Packages',

            'group' =>
                'Users & Applications',

        ],


        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        'transactions.view' => [

            'label' =>
                'Paid Transactions',

            'group' =>
                'Transactions',

        ],


        'disputes.manage' => [

            'label' =>
                'Disputes',

            'group' =>
                'Transactions',

        ],


        /*
        |--------------------------------------------------------------------------
        | Website Settings
        |--------------------------------------------------------------------------
        */

        'website.app_settings.manage' => [

            'label' =>
                'App Settings',

            'group' =>
                'Website Settings',

        ],


        'website.faqs.manage' => [

            'label' =>
                'FAQ Page',

            'group' =>
                'Website Settings',

        ],


        'website.pricing.manage' => [

            'label' =>
                'Pricing Page',

            'group' =>
                'Website Settings',

        ],


        'website.seller_packages.manage' => [

            'label' =>
                'Become Seller / Packages',

            'group' =>
                'Website Settings',

        ],


        /*
        |--------------------------------------------------------------------------
        | Support
        |--------------------------------------------------------------------------
        */

        'support.contacts.manage' => [

            'label' =>
                'Contact Messages',

            'group' =>
                'Support & Inquiries',

        ],


        'support.messages.view' => [

            'label' =>
                'Support Messages',

            'group' =>
                'Support & Inquiries',

        ],


        'support.live.manage' => [

            'label' =>
                'Live Support',

            'group' =>
                'Support & Inquiries',

        ],


        'support.live_settings.manage' => [

            'label' =>
                'Live Support Settings',

            'group' =>
                'Support & Inquiries',

        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Route → Permission Mapping
    |--------------------------------------------------------------------------
    |
    | The sidebar hiding is only UI protection.
    |
    | THIS section is what actually protects the URL.
    |
    */

    'routes' => [

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        'admin.dashboard' =>
            'dashboard.view',


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        'admin.users.*' =>
            'users.manage',


        /*
        |--------------------------------------------------------------------------
        | Billing
        |--------------------------------------------------------------------------
        */

        'admin.billing.invoices.*' =>
            'billing.invoices.view',


        'admin.billing.subscriptions.*' =>
            'billing.subscriptions.view',


        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        'admin.transactions.*' =>
            'transactions.view',


        'admin.disputes.*' =>
            'disputes.manage',


        /*
        |--------------------------------------------------------------------------
        | Seller Applications
        |--------------------------------------------------------------------------
        */

        'admin.website-settings.seller-applications.*' =>
            'seller_applications.manage',


        /*
        |--------------------------------------------------------------------------
        | Website
        |--------------------------------------------------------------------------
        */

        'admin.website-settings.app-settings*' =>
            'website.app_settings.manage',


        'admin.website-settings.faqs*' =>
            'website.faqs.manage',


        'admin.website-settings.pricing*' =>
            'website.pricing.manage',


        'admin.website-settings.become-seller*' =>
            'website.seller_packages.manage',


        'admin.website-settings.seller-packages.*' =>
            'website.seller_packages.manage',


        /*
        |--------------------------------------------------------------------------
        | Support
        |--------------------------------------------------------------------------
        */

        'admin.support-inquiries.contacts*' =>
            'support.contacts.manage',


        'admin.support-inquiries.support-messages*' =>
            'support.messages.view',


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |
        | Settings must come BEFORE admin.live-support.*
        |--------------------------------------------------------------------------
        */

        'admin.live-support.settings*' =>
            'support.live_settings.manage',


        'admin.live-support.blackouts.*' =>
            'support.live_settings.manage',


        'admin.live-support.agents.*' =>
            'support.live_settings.manage',


        'admin.live-support.*' =>
            'support.live.manage',

    ],


    /*
    |--------------------------------------------------------------------------
    | Internal Admin Routes
    |--------------------------------------------------------------------------
    |
    | These aren't sidebar modules.
    |
    | Every valid admin/admin_staff user may access them.
    |
    */

    'always_allowed_routes' => [

        'admin.notifications.*',

    ],

];