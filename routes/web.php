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


/*
|--------------------------------------------------------------------------
| Authentication Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\EmailVerificationController;


/*
|--------------------------------------------------------------------------
| Seller / Buyer Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;


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
)->name('home');


/*
|--------------------------------------------------------------------------
| About
|--------------------------------------------------------------------------
*/

Route::view(
    '/about',
    'frontend.pages.about'
)->name('about');


/*
|--------------------------------------------------------------------------
| How It Works
|--------------------------------------------------------------------------
*/

Route::view(
    '/how-it-works',
    'frontend.pages.how-it-works'
)->name('how-it-works');


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
)->name('pricing');


/*
|--------------------------------------------------------------------------
| Featured Businesses
|--------------------------------------------------------------------------
*/

Route::view(
    '/featured-businesses',
    'frontend.pages.featured-businesses'
)->name('featured-businesses');


/*
|--------------------------------------------------------------------------
| Verified Sellers
|--------------------------------------------------------------------------
*/

Route::view(
    '/verified-sellers',
    'frontend.pages.verified-sellers'
)->name('verified-sellers');


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
)->name('faqs');


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
)->name('contact');


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
|
| This route is public because even logged-out visitors may need to know
| whether support is currently available.
|
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

Route::middleware(
    'guest'
)->group(function () {


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
    ->middleware(
        'auth'
    )
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
|
| We intentionally do not require auth here because the verification email
| may be opened from another browser/device.
|
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
|
| Admin  -> Admin Dashboard
| Seller -> Seller Dashboard
| Buyer  -> Buyer Dashboard
|
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
|
| IMPORTANT:
| This route must remain outside the admin middleware.
|
| While impersonating, Auth::user() is the customer account.
|
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
| VERIFIED USER AREA
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
            | Dashboard
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
            | Listed Products
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/products',
                'account.coming-soon',
                [
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Listed products',
                    'pageIcon' => 'fa-bag-shopping',
                ]
            )->name(
                'products'
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
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Create transaction',
                    'pageIcon' => 'fa-circle-plus',
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
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Transactions',
                    'pageIcon' => 'fa-file-lines',
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
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Notifications',
                    'pageIcon' => 'fa-bell',
                ]
            )->name(
                'notifications'
            );


            /*
            |--------------------------------------------------------------------------
            | Business Profile
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/business-profile',
                'account.coming-soon',
                [
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Business profile',
                    'pageIcon' => 'fa-store',
                ]
            )->name(
                'business-profile'
            );


            /*
            |--------------------------------------------------------------------------
            | Profile Settings
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/profile-settings',
                'account.coming-soon',
                [
                    'dashboardRole' => 'seller',
                    'pageTitle' => 'Profile settings',
                    'pageIcon' => 'fa-gear',
                ]
            )->name(
                'profile-settings'
            );

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
            | Dashboard
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
                    'dashboardRole' => 'buyer',
                    'pageTitle' => 'Transactions',
                    'pageIcon' => 'fa-file-lines',
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
                    'dashboardRole' => 'buyer',
                    'pageTitle' => 'Notifications',
                    'pageIcon' => 'fa-bell',
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
                    'dashboardRole' => 'buyer',
                    'pageTitle' => 'Open seller invite',
                    'pageIcon' => 'fa-link',
                ]
            )->name(
                'seller-invite'
            );


            /*
            |--------------------------------------------------------------------------
            | Profile Settings
            |--------------------------------------------------------------------------
            */

            Route::view(
                '/profile-settings',
                'account.coming-soon',
                [
                    'dashboardRole' => 'buyer',
                    'pageTitle' => 'Profile settings',
                    'pageIcon' => 'fa-gear',
                ]
            )->name(
                'profile-settings'
            );

        });


    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | AUTHENTICATED LIVE SUPPORT
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Start Chat
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
    | Send Message
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
    | View / Download Attachment
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


    /*
    |--------------------------------------------------------------------------
    | Rate Chat
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
    | Skip Rating
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
                | Activate / Deactivate User
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
        |
        | IMPORTANT:
        |
        | This is intentionally OUTSIDE the users group.
        |
        | Route names therefore become:
        |
        | admin.live-support.index
        | admin.live-support.feed
        | admin.live-support.settings
        | etc.
        |
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
                | Realtime Feed
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
                | Agent Heartbeat
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
                | Agent Availability
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
                | Claim Waiting Chat
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
                | Resolve Chat
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
                |--------------------------------------------------------------------------
                | LIVE SUPPORT SETTINGS
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */


                /*
                |--------------------------------------------------------------------------
                | Settings Page
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
                | Add Support Blackout
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
                | Delete Support Blackout
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
                | FAQ MANAGEMENT
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */


                /*
                |--------------------------------------------------------------------------
                | FAQ List
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


                /*
                |--------------------------------------------------------------------------
                | Store FAQ
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/faqs',
                    [
                        AdminFaqController::class,
                        'store',
                    ]
                )->name(
                    'faqs.store'
                );


                /*
                |--------------------------------------------------------------------------
                | Update FAQ
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/faqs/{faq}',
                    [
                        AdminFaqController::class,
                        'update',
                    ]
                )->name(
                    'faqs.update'
                );


                /*
                |--------------------------------------------------------------------------
                | Toggle FAQ Status
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/faqs/{faq}/toggle-status',
                    [
                        AdminFaqController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'faqs.toggle-status'
                );


                /*
                |--------------------------------------------------------------------------
                | Delete FAQ
                |--------------------------------------------------------------------------
                */

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
                | Become Seller
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/become-seller',
                    [
                        WebsiteSettingsController::class,
                        'becomeSeller',
                    ]
                )->name(
                    'become-seller'
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
                | Contact Message Details
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
                | Contact Message Status
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
        | Mark All Notifications Read
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