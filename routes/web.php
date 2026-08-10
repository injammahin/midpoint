<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Frontend Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\AccountViewController;
use App\Http\Controllers\VerifiedSellerController;
use App\Http\Controllers\SellerApplicationController;
use App\Http\Controllers\SellerInvoicePaymentController;
use App\Http\Controllers\FeaturedBusinessController;


/*
|--------------------------------------------------------------------------
| Authentication Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;


/*
|--------------------------------------------------------------------------
| Seller / Buyer Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Seller\SellerProfileSettingsController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Seller\SellerBusinessProfileController;

/*
|--------------------------------------------------------------------------
| Admin Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WebsiteSettingsController;
use App\Http\Controllers\Admin\SupportInquiryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\AdminNotificationController;

use App\Http\Controllers\Admin\FaqController as AdminFaqController;

use App\Http\Controllers\Admin\PricingController as AdminPricingController;

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserImpersonationController;

use App\Http\Controllers\Admin\LiveSupportController;
use App\Http\Controllers\Admin\LiveSupportSettingsController;

use App\Http\Controllers\Admin\SellerPackageController;
use App\Http\Controllers\Admin\SellerInvoiceController;
use App\Http\Controllers\Admin\SellerSubscriptionController;

use App\Http\Controllers\Admin\SellerApplicationController
    as AdminSellerApplicationController;


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| PUBLIC WEBSITE
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [
        HomeController::class,
        'index',
    ]
)->name(
    'home'
);


/*
|--------------------------------------------------------------------------
| About
|--------------------------------------------------------------------------
*/

Route::view(
    '/about',
    'frontend.pages.about'
)->name(
    'about'
);


/*
|--------------------------------------------------------------------------
| How It Works
|--------------------------------------------------------------------------
*/

Route::view(
    '/how-it-works',
    'frontend.pages.how-it-works'
)->name(
    'how-it-works'
);


/*
|--------------------------------------------------------------------------
| Pricing
|--------------------------------------------------------------------------
*/

Route::get(
    '/pricing',
    [
        PricingController::class,
        'index',
    ]
)->name(
    'pricing'
);


/*
|--------------------------------------------------------------------------
| Featured Businesses
|--------------------------------------------------------------------------
*/

Route::get(
    '/featured-businesses',
    [
        FeaturedBusinessController::class,
        'index',
    ]
)->name(
    'featured-businesses'
);


/*
|--------------------------------------------------------------------------
| Seller Business Profile
|--------------------------------------------------------------------------
*/

Route::get(
    '/featured-businesses/{seller}',
    [
        FeaturedBusinessController::class,
        'show',
    ]
)->name(
    'featured-businesses.show'
);


/*
|--------------------------------------------------------------------------
| Verified Sellers
|--------------------------------------------------------------------------
*/

Route::get(
    '/verified-sellers',
    [
        VerifiedSellerController::class,
        'index',
    ]
)->name(
    'verified-sellers'
);


/*
|--------------------------------------------------------------------------
| FAQs
|--------------------------------------------------------------------------
*/

Route::get(
    '/faqs',
    [
        FaqController::class,
        'index',
    ]
)->name(
    'faqs'
);


/*
|--------------------------------------------------------------------------
| Contact
|--------------------------------------------------------------------------
*/

Route::get(
    '/contact',
    [
        ContactController::class,
        'create',
    ]
)->name(
    'contact'
);


Route::post(
    '/contact',
    [
        ContactController::class,
        'store',
    ]
)
    ->middleware(
        'throttle:5,1'
    )
    ->name(
        'contact.store'
    );


Route::get(
    '/contact/thank-you',
    [
        ContactController::class,
        'thankYou',
    ]
)->name(
    'contact.thank-you'
);


/*
|--------------------------------------------------------------------------
| Support Centre
|--------------------------------------------------------------------------
*/

Route::get(
    '/support',
    [
        SupportController::class,
        'index',
    ]
)->name(
    'support'
);


/*
|--------------------------------------------------------------------------
| Live Support Availability
|--------------------------------------------------------------------------
*/

Route::get(
    '/support/chat/status',
    [
        SupportChatController::class,
        'status',
    ]
)->name(
    'support.chat.status'
);


/*
|--------------------------------------------------------------------------
| Legal Pages
|--------------------------------------------------------------------------
*/

Route::view(
    '/terms-and-conditions',
    'frontend.pages.terms-and-conditions'
)->name(
    'terms-and-conditions'
);


Route::view(
    '/privacy-policy',
    'frontend.pages.privacy-policy'
)->name(
    'privacy-policy'
);


Route::view(
    '/escrow-policy',
    'frontend.pages.escrow-policy'
)->name(
    'escrow-policy'
);


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| GUEST AUTHENTICATION
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::middleware([
    'guest',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Two-Factor Challenge
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/two-factor-challenge',
        [
            TwoFactorChallengeController::class,
            'show',
        ]
    )->name(
        'two-factor.challenge'
    );


    Route::post(
        '/two-factor-challenge',
        [
            TwoFactorChallengeController::class,
            'store',
        ]
    )
        ->middleware(
            'throttle:10,1'
        )
        ->name(
            'two-factor.challenge.store'
        );


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/login',
        [
            AuthController::class,
            'showLogin',
        ]
    )->name(
        'login'
    );


    Route::post(
        '/login',
        [
            AuthController::class,
            'login',
        ]
    )
        ->middleware(
            'throttle:10,1'
        )
        ->name(
            'login.attempt'
        );


    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/register',
        [
            RegistrationController::class,
            'create',
        ]
    )->name(
        'register'
    );


    Route::post(
        '/register',
        [
            RegistrationController::class,
            'store',
        ]
    )
        ->middleware(
            'throttle:5,1'
        )
        ->name(
            'register.store'
        );

});


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [
        AuthController::class,
        'logout',
    ]
)
    ->middleware([
        'auth',
    ])
    ->name(
        'logout'
    );


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| EMAIL VERIFICATION
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Verification Notice
|--------------------------------------------------------------------------
*/

Route::get(
    '/email/verify',
    [
        EmailVerificationController::class,
        'notice',
    ]
)
    ->middleware([
        'auth',
        'active',
    ])
    ->name(
        'verification.notice'
    );


/*
|--------------------------------------------------------------------------
| Resend Verification Email
|--------------------------------------------------------------------------
*/

Route::post(
    '/email/verification-notification',
    [
        EmailVerificationController::class,
        'resend',
    ]
)
    ->middleware([
        'auth',
        'active',
        'throttle:6,1',
    ])
    ->name(
        'verification.send'
    );


/*
|--------------------------------------------------------------------------
| Verify Email
|--------------------------------------------------------------------------
*/

Route::get(
    '/email/verify/{id}/{hash}/{token}',
    [
        EmailVerificationController::class,
        'verify',
    ]
)
    ->middleware([
        'signed',
        'throttle:6,1',
    ])
    ->name(
        'verification.verify'
    );


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| MAIN DASHBOARD ROUTER
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    DashboardRedirectController::class
)
    ->middleware([
        'auth',
        'active',
    ])
    ->name(
        'dashboard'
    );


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| STOP ADMIN IMPERSONATION
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::post(
    '/impersonation/stop',
    [
        UserImpersonationController::class,
        'stop',
    ]
)
    ->middleware([
        'auth',
    ])
    ->name(
        'impersonation.stop'
    );


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| SHARED AUTHENTICATED LIVE SUPPORT ROUTES
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Get Chat Session
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/support/chat/sessions/{session}',
        [
            SupportChatController::class,
            'show',
        ]
    )->name(
        'support.chat.sessions.show'
    );


    /*
    |--------------------------------------------------------------------------
    | Send Chat Message
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/support/chat/sessions/{session}/messages',
        [
            SupportChatController::class,
            'storeMessage',
        ]
    )->name(
        'support.chat.messages.store'
    );


    /*
    |--------------------------------------------------------------------------
    | Secure Attachment
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/support/chat/attachments/{attachment}',
        [
            SupportChatController::class,
            'attachment',
        ]
    )->name(
        'support.chat.attachments.show'
    );

});


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| VERIFIED CUSTOMER ACTIONS
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
    'verified',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Seller Application
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/verified-sellers/apply',
        [
            SellerApplicationController::class,
            'store',
        ]
    )->name(
        'seller-applications.store'
    );


    /*
    |--------------------------------------------------------------------------
    | Seller Invoice Payment
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/verified-sellers/invoices/{invoice}/pay',
        [
            SellerInvoicePaymentController::class,
            'pay',
        ]
    )->name(
        'seller-invoices.pay'
    );


    /*
    |--------------------------------------------------------------------------
    | Switch Buyer / Seller View
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/account/switch/{view}',
        [
            AccountViewController::class,
            'switch',
        ]
    )
        ->whereIn(
            'view',
            [
                'seller',
                'buyer',
            ]
        )
        ->name(
            'account.switch'
        );


    /*
    |--------------------------------------------------------------------------
    | Start Live Support Chat
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/support/chat/start',
        [
            SupportChatController::class,
            'start',
        ]
    )->name(
        'support.chat.start'
    );


    /*
    |--------------------------------------------------------------------------
    | Rate Live Support
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/support/chat/sessions/{session}/rating',
        [
            SupportChatController::class,
            'rate',
        ]
    )->name(
        'support.chat.rate'
    );


    /*
    |--------------------------------------------------------------------------
    | Skip Live Support Rating
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/support/chat/sessions/{session}/skip-rating',
        [
            SupportChatController::class,
            'skipRating',
        ]
    )->name(
        'support.chat.skip-rating'
    );


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | SELLER AREA
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'seller'
    )
        ->name(
            'seller.'
        )
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Seller Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                [
                    SellerDashboardController::class,
                    'index',
                ]
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            |--------------------------------------------------------------------------
            | SELLER PROFILE SETTINGS
            |--------------------------------------------------------------------------
            |--------------------------------------------------------------------------
            */


            /*
            |--------------------------------------------------------------------------
            | Profile Settings Page
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/profile-settings',
                [
                    SellerProfileSettingsController::class,
                    'index',
                ]
            )->name(
                'profile-settings'
            );


            /*
            |--------------------------------------------------------------------------
            | Update Personal Details
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/profile-settings/personal',
                [
                    SellerProfileSettingsController::class,
                    'updateProfile',
                ]
            )->name(
                'profile-settings.personal'
            );


            /*
            |--------------------------------------------------------------------------
            | Update Bank Details
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/profile-settings/bank',
                [
                    SellerProfileSettingsController::class,
                    'updateBank',
                ]
            )->name(
                'profile-settings.bank'
            );


            /*
            |--------------------------------------------------------------------------
            | Notification Preferences
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/profile-settings/notifications',
                [
                    SellerProfileSettingsController::class,
                    'updateNotifications',
                ]
            )->name(
                'profile-settings.notifications'
            );


            /*
            |--------------------------------------------------------------------------
            | Change Password
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/profile-settings/password',
                [
                    SellerProfileSettingsController::class,
                    'changePassword',
                ]
            )->name(
                'profile-settings.password'
            );


            /*
            |--------------------------------------------------------------------------
            | Begin Two-Factor Setup
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/profile-settings/two-factor/setup',
                [
                    SellerProfileSettingsController::class,
                    'setupTwoFactor',
                ]
            )->name(
                'profile-settings.two-factor.setup'
            );


            /*
            |--------------------------------------------------------------------------
            | Confirm Two-Factor Setup
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/profile-settings/two-factor/confirm',
                [
                    SellerProfileSettingsController::class,
                    'confirmTwoFactor',
                ]
            )->name(
                'profile-settings.two-factor.confirm'
            );


            /*
            |--------------------------------------------------------------------------
            | Disable Two-Factor Authentication
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/profile-settings/two-factor',
                [
                    SellerProfileSettingsController::class,
                    'disableTwoFactor',
                ]
            )->name(
                'profile-settings.two-factor.disable'
            );


            /*
            |--------------------------------------------------------------------------
            |--------------------------------------------------------------------------
            | SELLER PRODUCTS
            |--------------------------------------------------------------------------
            |--------------------------------------------------------------------------
            */


            /*
            |--------------------------------------------------------------------------
            | Listed Products
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/products',
                [
                    SellerProductController::class,
                    'index',
                ]
            )->name(
                'products'
            );


            /*
            |--------------------------------------------------------------------------
            | Store Product
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/products',
                [
                    SellerProductController::class,
                    'store',
                ]
            )->name(
                'products.store'
            );


            /*
            |--------------------------------------------------------------------------
            | Update Product
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/products/{sellerProduct}',
                [
                    SellerProductController::class,
                    'update',
                ]
            )->name(
                'products.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Delete Product
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/products/{sellerProduct}',
                [
                    SellerProductController::class,
                    'destroy',
                ]
            )->name(
                'products.destroy'
            );


            /*
            |--------------------------------------------------------------------------
            | Create Transaction
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/transactions/create',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'seller',

                    'pageTitle' =>
                        'Create transaction',

                    'pageIcon' =>
                        'fa-circle-plus',
                ]
            )->name(
                'transactions.create'
            );


            /*
            |--------------------------------------------------------------------------
            | Transactions
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/transactions',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'seller',

                    'pageTitle' =>
                        'Transactions',

                    'pageIcon' =>
                        'fa-file-lines',
                ]
            )->name(
                'transactions'
            );


            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/notifications',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'seller',

                    'pageTitle' =>
                        'Notifications',

                    'pageIcon' =>
                        'fa-bell',
                ]
            )->name(
                'notifications'
            );



            /*
            |--------------------------------------------------------------------------
            | Business Profile
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/business-profile',
                [
                    SellerBusinessProfileController::class,
                    'index',
                ]
            )->name(
                'business-profile'
            );


            /*
            |--------------------------------------------------------------------------
            | Update Business Profile
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/business-profile',
                [
                    SellerBusinessProfileController::class,
                    'update',
                ]
            )->name(
                'business-profile.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Business Profile Picture
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/business-profile/profile-image',
                [
                    SellerBusinessProfileController::class,
                    'destroyProfileImage',
                ]
            )->name(
                'business-profile.profile-image.destroy'
            );
            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | DO NOT add another /profile-settings Route::view() here.
            |
            | The real profile settings route is already defined above
            | using SellerProfileSettingsController@index.
            |
            */

        });


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | BUYER AREA
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'buyer'
    )
        ->name(
            'buyer.'
        )
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Buyer Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                [
                    BuyerDashboardController::class,
                    'index',
                ]
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            | Transactions
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/transactions',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'buyer',

                    'pageTitle' =>
                        'Transactions',

                    'pageIcon' =>
                        'fa-file-lines',
                ]
            )->name(
                'transactions'
            );


            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/notifications',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'buyer',

                    'pageTitle' =>
                        'Notifications',

                    'pageIcon' =>
                        'fa-bell',
                ]
            )->name(
                'notifications'
            );


            /*
            |--------------------------------------------------------------------------
            | Seller Invite
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/seller-invite',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'buyer',

                    'pageTitle' =>
                        'Open seller invite',

                    'pageIcon' =>
                        'fa-link',
                ]
            )->name(
                'seller-invite'
            );


            /*
            |--------------------------------------------------------------------------
            | Buyer Profile Settings
            |--------------------------------------------------------------------------
            |
            | Still coming soon for now.
            |
            */

            Route::view(
                '/profile-settings',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'buyer',

                    'pageTitle' =>
                        'Profile settings',

                    'pageIcon' =>
                        'fa-gear',
                ]
            )->name(
                'profile-settings'
            );

        });

});


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

Route::prefix(
    'admin'
)
    ->name(
        'admin.'
    )
    ->middleware([
        'auth',
        'active',
        'admin',
    ])
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | BILLING
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'billing'
        )
            ->name(
                'billing.'
            )
            ->group(function () {


                /*
                |--------------------------------------------------------------------------
                | Invoices
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/invoices',
                    [
                        SellerInvoiceController::class,
                        'index',
                    ]
                )->name(
                    'invoices.index'
                );


                /*
                |--------------------------------------------------------------------------
                | Purchased Packages
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/subscriptions',
                    [
                        SellerSubscriptionController::class,
                        'index',
                    ]
                )->name(
                    'subscriptions.index'
                );

            });


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                DashboardController::class,
                'index',
            ]
        )->name(
            'dashboard'
        );


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | USER MANAGEMENT
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'users'
        )
            ->name(
                'users.'
            )
            ->group(function () {


                /*
                |--------------------------------------------------------------------------
                | User List
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    [
                        UserManagementController::class,
                        'index',
                    ]
                )->name(
                    'index'
                );


                /*
                |--------------------------------------------------------------------------
                | Activate / Deactivate
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/{user}/status',
                    [
                        UserManagementController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'status'
                );


                /*
                |--------------------------------------------------------------------------
                | Login As User
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/{user}/login',
                    [
                        UserImpersonationController::class,
                        'start',
                    ]
                )->name(
                    'impersonate'
                );

            });


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | LIVE SUPPORT
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'support-inquiries/live-support'
        )
            ->name(
                'live-support.'
            )
            ->group(function () {


                /*
                |--------------------------------------------------------------------------
                | Agent Workspace
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    [
                        LiveSupportController::class,
                        'index',
                    ]
                )->name(
                    'index'
                );


                /*
                |--------------------------------------------------------------------------
                | Inbox Feed
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/feed',
                    [
                        LiveSupportController::class,
                        'feed',
                    ]
                )->name(
                    'feed'
                );


                /*
                |--------------------------------------------------------------------------
                | Heartbeat
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/heartbeat',
                    [
                        LiveSupportController::class,
                        'heartbeat',
                    ]
                )->name(
                    'heartbeat'
                );


                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/availability',
                    [
                        LiveSupportController::class,
                        'availability',
                    ]
                )->name(
                    'availability'
                );


                /*
                |--------------------------------------------------------------------------
                | Claim Conversation
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/sessions/{session}/claim',
                    [
                        LiveSupportController::class,
                        'claim',
                    ]
                )->name(
                    'claim'
                );


                /*
                |--------------------------------------------------------------------------
                | Resolve Conversation
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/sessions/{session}/resolve',
                    [
                        LiveSupportController::class,
                        'resolve',
                    ]
                )->name(
                    'resolve'
                );


                /*
                |--------------------------------------------------------------------------
                | Live Support Settings
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/settings',
                    [
                        LiveSupportSettingsController::class,
                        'index',
                    ]
                )->name(
                    'settings'
                );


                /*
                |--------------------------------------------------------------------------
                | Update Settings
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/settings',
                    [
                        LiveSupportSettingsController::class,
                        'update',
                    ]
                )->name(
                    'settings.update'
                );


                /*
                |--------------------------------------------------------------------------
                | Store Blackout
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/settings/blackouts',
                    [
                        LiveSupportSettingsController::class,
                        'blackoutStore',
                    ]
                )->name(
                    'blackouts.store'
                );


                /*
                |--------------------------------------------------------------------------
                | Delete Blackout
                |--------------------------------------------------------------------------
                */

                Route::delete(
                    '/settings/blackouts/{blackout}',
                    [
                        LiveSupportSettingsController::class,
                        'blackoutDestroy',
                    ]
                )->name(
                    'blackouts.destroy'
                );


                /*
                |--------------------------------------------------------------------------
                | Update Support Agent
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/settings/agents/{user}',
                    [
                        LiveSupportSettingsController::class,
                        'agentUpdate',
                    ]
                )->name(
                    'agents.update'
                );

            });


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | WEBSITE SETTINGS
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'website-settings'
        )
            ->name(
                'website-settings.'
            )
            ->group(function () {


                /*
                |--------------------------------------------------------------------------
                | App Settings
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/app-settings',
                    [
                        WebsiteSettingsController::class,
                        'appSettings',
                    ]
                )->name(
                    'app-settings'
                );


                /*
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                | SELLER APPLICATIONS
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */


                /*
                |--------------------------------------------------------------------------
                | Seller Application List
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/seller-applications',
                    [
                        AdminSellerApplicationController::class,
                        'index',
                    ]
                )->name(
                    'seller-applications.index'
                );


                /*
                |--------------------------------------------------------------------------
                | Seller Application Details
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/seller-applications/{sellerApplication}',
                    [
                        AdminSellerApplicationController::class,
                        'show',
                    ]
                )->name(
                    'seller-applications.show'
                );


                /*
                |--------------------------------------------------------------------------
                | Approve Application
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/seller-applications/{sellerApplication}/approve',
                    [
                        AdminSellerApplicationController::class,
                        'approve',
                    ]
                )->name(
                    'seller-applications.approve'
                );


                /*
                |--------------------------------------------------------------------------
                | Request Revision
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/seller-applications/{sellerApplication}/revision',
                    [
                        AdminSellerApplicationController::class,
                        'requestRevision',
                    ]
                )->name(
                    'seller-applications.revision'
                );


                /*
                |--------------------------------------------------------------------------
                | Seller Application Document
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/seller-application-documents/{document}',
                    [
                        AdminSellerApplicationController::class,
                        'document',
                    ]
                )->name(
                    'seller-applications.documents'
                );


                /*
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                | FAQ MANAGEMENT
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/faqs',
                    [
                        AdminFaqController::class,
                        'index',
                    ]
                )->name(
                    'faqs'
                );


                Route::post(
                    '/faqs',
                    [
                        AdminFaqController::class,
                        'store',
                    ]
                )->name(
                    'faqs.store'
                );


                Route::put(
                    '/faqs/{faq}',
                    [
                        AdminFaqController::class,
                        'update',
                    ]
                )->name(
                    'faqs.update'
                );


                Route::patch(
                    '/faqs/{faq}/toggle-status',
                    [
                        AdminFaqController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'faqs.toggle-status'
                );


                Route::delete(
                    '/faqs/{faq}',
                    [
                        AdminFaqController::class,
                        'destroy',
                    ]
                )->name(
                    'faqs.destroy'
                );


                /*
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                | PRICING
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/pricing',
                    [
                        AdminPricingController::class,
                        'index',
                    ]
                )->name(
                    'pricing'
                );


                Route::put(
                    '/pricing',
                    [
                        AdminPricingController::class,
                        'update',
                    ]
                )->name(
                    'pricing.update'
                );


                /*
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                | SELLER PACKAGES
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/become-seller',
                    [
                        SellerPackageController::class,
                        'index',
                    ]
                )->name(
                    'become-seller'
                );


                /*
                |--------------------------------------------------------------------------
                | Create Seller Package
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/become-seller/packages',
                    [
                        SellerPackageController::class,
                        'store',
                    ]
                )->name(
                    'seller-packages.store'
                );


                /*
                |--------------------------------------------------------------------------
                | Update Seller Package
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/become-seller/packages/{sellerPackage}',
                    [
                        SellerPackageController::class,
                        'update',
                    ]
                )->name(
                    'seller-packages.update'
                );


                /*
                |--------------------------------------------------------------------------
                | Activate / Hide Seller Package
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/become-seller/packages/{sellerPackage}/toggle',
                    [
                        SellerPackageController::class,
                        'toggle',
                    ]
                )->name(
                    'seller-packages.toggle'
                );


                /*
                |--------------------------------------------------------------------------
                | Delete Seller Package
                |--------------------------------------------------------------------------
                */

                Route::delete(
                    '/become-seller/packages/{sellerPackage}',
                    [
                        SellerPackageController::class,
                        'destroy',
                    ]
                )->name(
                    'seller-packages.destroy'
                );

            });


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | SUPPORT & INQUIRIES
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'support-inquiries'
        )
            ->name(
                'support-inquiries.'
            )
            ->group(function () {


                /*
                |--------------------------------------------------------------------------
                | Contact Messages
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/contacts',
                    [
                        ContactMessageController::class,
                        'index',
                    ]
                )->name(
                    'contacts'
                );


                /*
                |--------------------------------------------------------------------------
                | Contact Details
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/contacts/{contactMessage}',
                    [
                        ContactMessageController::class,
                        'show',
                    ]
                )->name(
                    'contacts.show'
                );


                /*
                |--------------------------------------------------------------------------
                | Contact Status
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/contacts/{contactMessage}/status',
                    [
                        ContactMessageController::class,
                        'updateStatus',
                    ]
                )->name(
                    'contacts.status'
                );


                /*
                |--------------------------------------------------------------------------
                | Support Messages
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/support-messages',
                    [
                        SupportInquiryController::class,
                        'supportMessages',
                    ]
                )->name(
                    'support-messages'
                );

            });


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | ADMIN NOTIFICATIONS
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | Notification Feed
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/notifications/feed',
            [
                AdminNotificationController::class,
                'feed',
            ]
        )->name(
            'notifications.feed'
        );


        /*
        |--------------------------------------------------------------------------
        | Open Notification
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/notifications/{notification}/open',
            [
                AdminNotificationController::class,
                'open',
            ]
        )->name(
            'notifications.open'
        );


        /*
        |--------------------------------------------------------------------------
        | Mark All Read
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/notifications/read-all',
            [
                AdminNotificationController::class,
                'markAllRead',
            ]
        )->name(
            'notifications.read-all'
        );

    });