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
use App\Http\Controllers\Seller\SellerProductController;
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

Route::view(
    '/featured-businesses',
    'frontend.pages.featured-businesses'
)->name(
    'featured-businesses'
);


/*
|--------------------------------------------------------------------------
| Verified Sellers
|--------------------------------------------------------------------------
|
| Public page.
|
| Guests can:
| - See packages
| - Select packages
| - Be redirected to login before applying
|
| Logged-in users can additionally see their application/payment state.
|
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
|
| Public because a guest needs to know whether live support is available.
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

Route::middleware([
    'guest',
])->group(function () {


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
|
| Auth is intentionally not required because users may open the
| verification email in another browser/device.
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
| Must remain outside the admin middleware.
|
| While impersonating:
|
| Auth::user()
|
| is the customer, not the administrator.
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
| SHARED AUTHENTICATED LIVE SUPPORT ROUTES
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| These routes are NOT behind "verified".
|
| Reason:
|
| Both customer and admin use the same conversation endpoints.
| Admin accounts should not be blocked simply because their email_verified_at
| field is null.
|
| Authorization is handled inside SupportChatController.
|
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
    |
    | Customer or assigned support agent.
    |
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
|
| Actions that specifically require a verified user account.
|
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
    |
    | User must:
    |
    | - Be logged in
    | - Be active
    | - Have a verified email
    |
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
    | Seller Invoice Demo Payment
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
    |
    | Starting a brand-new conversation requires a verified user.
    |
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


            Route::post(
                '/products',
                [
                    SellerProductController::class,
                    'store',
                ]
            )->name(
                'products.store'
            );


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

            Route::view(
                '/business-profile',
                'account.coming-soon',
                [
                    'dashboardRole' =>
                        'seller',

                    'pageTitle' =>
                        'Business profile',

                    'pageIcon' =>
                        'fa-store',
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
                    'dashboardRole' =>
                        'seller',

                    'pageTitle' =>
                        'Profile settings',

                    'pageIcon' =>
                        'fa-gear',
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
            | Profile Settings
            |--------------------------------------------------------------------------
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
                |--------------------------------------------------------------------------
                | LIVE SUPPORT SETTINGS
                |--------------------------------------------------------------------------
                |--------------------------------------------------------------------------
                */


                /*
                |--------------------------------------------------------------------------
                | Settings
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
                |
                | Sidebar may display these under "Users & Applications",
                | but we keep the current route names for compatibility:
                |
                | admin.website-settings.seller-applications.*
                |
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
                |
                | Approval generates the seller package invoice.
                |
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
                | Secure Seller Application Document
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
                | Toggle FAQ
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
                | PRICING PAGE
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

                /*
                |--------------------------------------------------------------------------
                | Seller Package Page
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
        | ADMIN NOTIFICATIONS
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