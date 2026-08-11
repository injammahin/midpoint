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
use App\Http\Controllers\SecureTransactionController;
use App\Http\Controllers\PaystackPaymentController;

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
| Seller Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\SellerProfileSettingsController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerBusinessProfileController;
use App\Http\Controllers\Seller\SellerSecureTransactionController;
use App\Http\Controllers\Seller\SellerTransactionController;
use App\Http\Controllers\Seller\SellerNotificationController;
use App\Http\Controllers\Seller\SellerTransactionStatusController;

/*
|--------------------------------------------------------------------------
| Buyer Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\BuyerTransactionController;
use App\Http\Controllers\Buyer\BuyerNotificationController;
use App\Http\Controllers\Buyer\BuyerTransactionActionController;
use App\Http\Controllers\Buyer\BuyerTransactionDisputeController;

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
use App\Http\Controllers\Admin\SellerApplicationController as AdminSellerApplicationController;


/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [
        HomeController::class,
        'index',
    ]
)->name('home');


Route::view(
    '/about',
    'frontend.pages.about'
)->name('about');


Route::view(
    '/how-it-works',
    'frontend.pages.how-it-works'
)->name('how-it-works');


Route::get(
    '/pricing',
    [
        PricingController::class,
        'index',
    ]
)->name('pricing');


Route::get(
    '/featured-businesses',
    [
        FeaturedBusinessController::class,
        'index',
    ]
)->name('featured-businesses');


Route::get(
    '/featured-businesses/{seller}',
    [
        FeaturedBusinessController::class,
        'show',
    ]
)->name('featured-businesses.show');


Route::get(
    '/verified-sellers',
    [
        VerifiedSellerController::class,
        'index',
    ]
)->name('verified-sellers');


Route::get(
    '/faqs',
    [
        FaqController::class,
        'index',
    ]
)->name('faqs');


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
    ->middleware('throttle:5,1')
    ->name('contact.store');


Route::get(
    '/contact/thank-you',
    [
        ContactController::class,
        'thankYou',
    ]
)->name('contact.thank-you');


Route::get(
    '/support',
    [
        SupportController::class,
        'index',
    ]
)->name('support');


Route::get(
    '/support/chat/status',
    [
        SupportChatController::class,
        'status',
    ]
)->name('support.chat.status');


Route::view(
    '/terms-and-conditions',
    'frontend.pages.terms-and-conditions'
)->name('terms-and-conditions');


Route::view(
    '/privacy-policy',
    'frontend.pages.privacy-policy'
)->name('privacy-policy');


Route::view(
    '/escrow-policy',
    'frontend.pages.escrow-policy'
)->name('escrow-policy');


/*
|--------------------------------------------------------------------------
| Public Secure Transaction Link
|--------------------------------------------------------------------------
*/

Route::get(
    '/transaction/{secureTransaction}',
    [
        SecureTransactionController::class,
        'show',
    ]
)->name('secure-transactions.show');


/*
|--------------------------------------------------------------------------
| Paystack Public Endpoints
|--------------------------------------------------------------------------
*/

Route::get(
    '/payments/paystack/callback',
    [
        PaystackPaymentController::class,
        'callback',
    ]
)->name('payments.paystack.callback');


Route::post(
    '/webhooks/paystack',
    [
        PaystackPaymentController::class,
        'webhook',
    ]
)->name('webhooks.paystack');


/*
|--------------------------------------------------------------------------
| Guest Authentication
|--------------------------------------------------------------------------
*/

Route::middleware([
    'guest',
])->group(function () {

    Route::get(
        '/two-factor-challenge',
        [
            TwoFactorChallengeController::class,
            'show',
        ]
    )->name('two-factor.challenge');


    Route::post(
        '/two-factor-challenge',
        [
            TwoFactorChallengeController::class,
            'store',
        ]
    )
        ->middleware('throttle:10,1')
        ->name('two-factor.challenge.store');


    Route::get(
        '/login',
        [
            AuthController::class,
            'showLogin',
        ]
    )->name('login');


    Route::post(
        '/login',
        [
            AuthController::class,
            'login',
        ]
    )
        ->middleware('throttle:10,1')
        ->name('login.attempt');


    Route::get(
        '/register',
        [
            RegistrationController::class,
            'create',
        ]
    )->name('register');


    Route::post(
        '/register',
        [
            RegistrationController::class,
            'store',
        ]
    )
        ->middleware('throttle:5,1')
        ->name('register.store');
});


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [
        AuthController::class,
        'logout',
    ]
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Email Verification
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
    ->name('verification.notice');


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
    ->name('verification.send');


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
    ->name('verification.verify');


/*
|--------------------------------------------------------------------------
| Dashboard Redirect
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
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Stop Admin Impersonation
|--------------------------------------------------------------------------
*/

Route::post(
    '/impersonation/stop',
    [
        UserImpersonationController::class,
        'stop',
    ]
)
    ->middleware('auth')
    ->name('impersonation.stop');


/*
|--------------------------------------------------------------------------
| Authenticated Live Support
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
])->group(function () {

    Route::get(
        '/support/chat/sessions/{session}',
        [
            SupportChatController::class,
            'show',
        ]
    )->name('support.chat.sessions.show');


    Route::post(
        '/support/chat/sessions/{session}/messages',
        [
            SupportChatController::class,
            'storeMessage',
        ]
    )->name('support.chat.messages.store');


    Route::get(
        '/support/chat/attachments/{attachment}',
        [
            SupportChatController::class,
            'attachment',
        ]
    )->name('support.chat.attachments.show');
});


/*
|--------------------------------------------------------------------------
| Authenticated + Active + Verified Users
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Paystack Payment Initialization
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/transaction/{secureTransaction}/paystack',
        [
            PaystackPaymentController::class,
            'initialize',
        ]
    )->name('secure-transactions.paystack.initialize');


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
    )->name('seller-applications.store');


    Route::post(
        '/verified-sellers/invoices/{invoice}/pay',
        [
            SellerInvoicePaymentController::class,
            'pay',
        ]
    )->name('seller-invoices.pay');


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
        ->name('account.switch');


    /*
    |--------------------------------------------------------------------------
    | Live Support
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/support/chat/start',
        [
            SupportChatController::class,
            'start',
        ]
    )->name('support.chat.start');


    Route::post(
        '/support/chat/sessions/{session}/rating',
        [
            SupportChatController::class,
            'rate',
        ]
    )->name('support.chat.rate');


    Route::post(
        '/support/chat/sessions/{session}/skip-rating',
        [
            SupportChatController::class,
            'skipRating',
        ]
    )->name('support.chat.skip-rating');


    /*
    |--------------------------------------------------------------------------
    | Seller Area
    |--------------------------------------------------------------------------
    */

    Route::prefix('seller')
        ->name('seller.')
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
            )->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | Seller Profile Settings
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/profile-settings',
                [
                    SellerProfileSettingsController::class,
                    'index',
                ]
            )->name('profile-settings');


            Route::put(
                '/profile-settings/personal',
                [
                    SellerProfileSettingsController::class,
                    'updateProfile',
                ]
            )->name('profile-settings.personal');


            Route::put(
                '/profile-settings/bank',
                [
                    SellerProfileSettingsController::class,
                    'updateBank',
                ]
            )->name('profile-settings.bank');


            Route::put(
                '/profile-settings/notifications',
                [
                    SellerProfileSettingsController::class,
                    'updateNotifications',
                ]
            )->name('profile-settings.notifications');


            Route::put(
                '/profile-settings/password',
                [
                    SellerProfileSettingsController::class,
                    'changePassword',
                ]
            )->name('profile-settings.password');


            Route::post(
                '/profile-settings/two-factor/setup',
                [
                    SellerProfileSettingsController::class,
                    'setupTwoFactor',
                ]
            )->name('profile-settings.two-factor.setup');


            Route::post(
                '/profile-settings/two-factor/confirm',
                [
                    SellerProfileSettingsController::class,
                    'confirmTwoFactor',
                ]
            )->name('profile-settings.two-factor.confirm');


            Route::delete(
                '/profile-settings/two-factor',
                [
                    SellerProfileSettingsController::class,
                    'disableTwoFactor',
                ]
            )->name('profile-settings.two-factor.disable');


            /*
            |--------------------------------------------------------------------------
            | Seller Products
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/products',
                [
                    SellerProductController::class,
                    'index',
                ]
            )->name('products');


            Route::post(
                '/products',
                [
                    SellerProductController::class,
                    'store',
                ]
            )->name('products.store');


            Route::put(
                '/products/{sellerProduct}',
                [
                    SellerProductController::class,
                    'update',
                ]
            )->name('products.update');


            Route::delete(
                '/products/{sellerProduct}',
                [
                    SellerProductController::class,
                    'destroy',
                ]
            )->name('products.destroy');


            /*
            |--------------------------------------------------------------------------
            | Seller Secure Transaction Creation
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/transactions/create',
                [
                    SellerSecureTransactionController::class,
                    'create',
                ]
            )->name('transactions.create');


            Route::post(
                '/transactions',
                [
                    SellerSecureTransactionController::class,
                    'store',
                ]
            )->name('transactions.store');


            Route::get(
                '/transactions/{secureTransaction}/generated',
                [
                    SellerSecureTransactionController::class,
                    'generated',
                ]
            )->name('transactions.generated');


            /*
            |--------------------------------------------------------------------------
            | Seller Transaction Status Update
            |--------------------------------------------------------------------------
            */

            Route::patch(
                '/transactions/{secureTransaction}/status',
                [
                    SellerTransactionStatusController::class,
                    'update',
                ]
            )->name('transactions.status.update');


            /*
            |--------------------------------------------------------------------------
            | Seller Transactions
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/transactions',
                [
                    SellerTransactionController::class,
                    'index',
                ]
            )->name('transactions');


            Route::get(
                '/transactions/{secureTransaction}',
                [
                    SellerTransactionController::class,
                    'show',
                ]
            )->name('transactions.show');


            /*
            |--------------------------------------------------------------------------
            | Seller Notifications
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/notifications',
                [
                    SellerNotificationController::class,
                    'index',
                ]
            )->name('notifications');


            Route::post(
                '/notifications/read-all',
                [
                    SellerNotificationController::class,
                    'markAllRead',
                ]
            )->name('notifications.read-all');


            Route::get(
                '/notifications/{notification}/open',
                [
                    SellerNotificationController::class,
                    'open',
                ]
            )->name('notifications.open');


            /*
            |--------------------------------------------------------------------------
            | Seller Business Profile
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/business-profile',
                [
                    SellerBusinessProfileController::class,
                    'index',
                ]
            )->name('business-profile');


            Route::put(
                '/business-profile',
                [
                    SellerBusinessProfileController::class,
                    'update',
                ]
            )->name('business-profile.update');


            Route::delete(
                '/business-profile/profile-image',
                [
                    SellerBusinessProfileController::class,
                    'destroyProfileImage',
                ]
            )->name('business-profile.profile-image.destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | Buyer Area
    |--------------------------------------------------------------------------
    */

    Route::prefix('buyer')
        ->name('buyer.')
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
            )->name('dashboard');


            /*
            |--------------------------------------------------------------------------
            | Buyer Transaction Actions
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/transactions/{secureTransaction}/inspection',
                [
                    BuyerTransactionActionController::class,
                    'inspection',
                ]
            )->name('transactions.inspection');


            Route::post(
                '/transactions/{secureTransaction}/accept',
                [
                    BuyerTransactionActionController::class,
                    'accept',
                ]
            )->name('transactions.accept');


            /*
            |--------------------------------------------------------------------------
            | Buyer Transaction Dispute
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/transactions/{secureTransaction}/dispute',
                [
                    BuyerTransactionDisputeController::class,
                    'create',
                ]
            )->name('transactions.dispute.create');


            Route::post(
                '/transactions/{secureTransaction}/dispute',
                [
                    BuyerTransactionDisputeController::class,
                    'store',
                ]
            )->name('transactions.dispute.store');


            /*
            |--------------------------------------------------------------------------
            | Buyer Transaction Invoice
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/transactions/{secureTransaction}/invoice',
                [
                    BuyerTransactionController::class,
                    'invoice',
                ]
            )->name('transactions.invoice');


            /*
            |--------------------------------------------------------------------------
            | Buyer Transactions
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/transactions',
                [
                    BuyerTransactionController::class,
                    'index',
                ]
            )->name('transactions');


            Route::get(
                '/transactions/{secureTransaction}',
                [
                    BuyerTransactionController::class,
                    'show',
                ]
            )->name('transactions.show');


            /*
            |--------------------------------------------------------------------------
            | Buyer Notifications
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/notifications',
                [
                    BuyerNotificationController::class,
                    'index',
                ]
            )->name('notifications');


            Route::post(
                '/notifications/read-all',
                [
                    BuyerNotificationController::class,
                    'markAllRead',
                ]
            )->name('notifications.read-all');


            Route::get(
                '/notifications/{notification}/open',
                [
                    BuyerNotificationController::class,
                    'open',
                ]
            )->name('notifications.open');


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
            )->name('seller-invite');


            /*
            |--------------------------------------------------------------------------
            | Buyer Profile Settings
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
            )->name('profile-settings');
        });
});


/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'active',
        'admin',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Admin Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                DashboardController::class,
                'index',
            ]
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Billing
        |--------------------------------------------------------------------------
        */

        Route::prefix('billing')
            ->name('billing.')
            ->group(function () {

                Route::get(
                    '/invoices',
                    [
                        SellerInvoiceController::class,
                        'index',
                    ]
                )->name('invoices.index');


                Route::get(
                    '/subscriptions',
                    [
                        SellerSubscriptionController::class,
                        'index',
                    ]
                )->name('subscriptions.index');
            });


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')
            ->name('users.')
            ->group(function () {

                Route::get(
                    '/',
                    [
                        UserManagementController::class,
                        'index',
                    ]
                )->name('index');


                Route::patch(
                    '/{user}/status',
                    [
                        UserManagementController::class,
                        'toggleStatus',
                    ]
                )->name('status');


                Route::post(
                    '/{user}/login',
                    [
                        UserImpersonationController::class,
                        'start',
                    ]
                )->name('impersonate');
            });


        /*
        |--------------------------------------------------------------------------
        | Live Support
        |--------------------------------------------------------------------------
        */

        Route::prefix('support-inquiries/live-support')
            ->name('live-support.')
            ->group(function () {

                Route::get(
                    '/',
                    [
                        LiveSupportController::class,
                        'index',
                    ]
                )->name('index');


                Route::get(
                    '/feed',
                    [
                        LiveSupportController::class,
                        'feed',
                    ]
                )->name('feed');


                Route::post(
                    '/heartbeat',
                    [
                        LiveSupportController::class,
                        'heartbeat',
                    ]
                )->name('heartbeat');


                Route::post(
                    '/availability',
                    [
                        LiveSupportController::class,
                        'availability',
                    ]
                )->name('availability');


                Route::post(
                    '/sessions/{session}/claim',
                    [
                        LiveSupportController::class,
                        'claim',
                    ]
                )->name('claim');


                Route::post(
                    '/sessions/{session}/resolve',
                    [
                        LiveSupportController::class,
                        'resolve',
                    ]
                )->name('resolve');


                Route::get(
                    '/settings',
                    [
                        LiveSupportSettingsController::class,
                        'index',
                    ]
                )->name('settings');


                Route::put(
                    '/settings',
                    [
                        LiveSupportSettingsController::class,
                        'update',
                    ]
                )->name('settings.update');


                Route::post(
                    '/settings/blackouts',
                    [
                        LiveSupportSettingsController::class,
                        'blackoutStore',
                    ]
                )->name('blackouts.store');


                Route::delete(
                    '/settings/blackouts/{blackout}',
                    [
                        LiveSupportSettingsController::class,
                        'blackoutDestroy',
                    ]
                )->name('blackouts.destroy');


                Route::put(
                    '/settings/agents/{user}',
                    [
                        LiveSupportSettingsController::class,
                        'agentUpdate',
                    ]
                )->name('agents.update');
            });


        /*
        |--------------------------------------------------------------------------
        | Website Settings
        |--------------------------------------------------------------------------
        */

        Route::prefix('website-settings')
            ->name('website-settings.')
            ->group(function () {

                Route::get(
                    '/app-settings',
                    [
                        WebsiteSettingsController::class,
                        'appSettings',
                    ]
                )->name('app-settings');


                /*
                |--------------------------------------------------------------------------
                | Seller Applications
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/seller-applications',
                    [
                        AdminSellerApplicationController::class,
                        'index',
                    ]
                )->name('seller-applications.index');


                Route::get(
                    '/seller-applications/{sellerApplication}',
                    [
                        AdminSellerApplicationController::class,
                        'show',
                    ]
                )->name('seller-applications.show');


                Route::post(
                    '/seller-applications/{sellerApplication}/approve',
                    [
                        AdminSellerApplicationController::class,
                        'approve',
                    ]
                )->name('seller-applications.approve');


                Route::post(
                    '/seller-applications/{sellerApplication}/revision',
                    [
                        AdminSellerApplicationController::class,
                        'requestRevision',
                    ]
                )->name('seller-applications.revision');


                Route::get(
                    '/seller-application-documents/{document}',
                    [
                        AdminSellerApplicationController::class,
                        'document',
                    ]
                )->name('seller-applications.documents');


                /*
                |--------------------------------------------------------------------------
                | FAQs
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/faqs',
                    [
                        AdminFaqController::class,
                        'index',
                    ]
                )->name('faqs');


                Route::post(
                    '/faqs',
                    [
                        AdminFaqController::class,
                        'store',
                    ]
                )->name('faqs.store');


                Route::put(
                    '/faqs/{faq}',
                    [
                        AdminFaqController::class,
                        'update',
                    ]
                )->name('faqs.update');


                Route::patch(
                    '/faqs/{faq}/toggle-status',
                    [
                        AdminFaqController::class,
                        'toggleStatus',
                    ]
                )->name('faqs.toggle-status');


                Route::delete(
                    '/faqs/{faq}',
                    [
                        AdminFaqController::class,
                        'destroy',
                    ]
                )->name('faqs.destroy');


                /*
                |--------------------------------------------------------------------------
                | Pricing
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/pricing',
                    [
                        AdminPricingController::class,
                        'index',
                    ]
                )->name('pricing');


                Route::put(
                    '/pricing',
                    [
                        AdminPricingController::class,
                        'update',
                    ]
                )->name('pricing.update');


                /*
                |--------------------------------------------------------------------------
                | Seller Packages
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/become-seller',
                    [
                        SellerPackageController::class,
                        'index',
                    ]
                )->name('become-seller');


                Route::post(
                    '/become-seller/packages',
                    [
                        SellerPackageController::class,
                        'store',
                    ]
                )->name('seller-packages.store');


                Route::put(
                    '/become-seller/packages/{sellerPackage}',
                    [
                        SellerPackageController::class,
                        'update',
                    ]
                )->name('seller-packages.update');


                Route::patch(
                    '/become-seller/packages/{sellerPackage}/toggle',
                    [
                        SellerPackageController::class,
                        'toggle',
                    ]
                )->name('seller-packages.toggle');


                Route::delete(
                    '/become-seller/packages/{sellerPackage}',
                    [
                        SellerPackageController::class,
                        'destroy',
                    ]
                )->name('seller-packages.destroy');
            });


        /*
        |--------------------------------------------------------------------------
        | Support & Inquiries
        |--------------------------------------------------------------------------
        */

        Route::prefix('support-inquiries')
            ->name('support-inquiries.')
            ->group(function () {

                Route::get(
                    '/contacts',
                    [
                        ContactMessageController::class,
                        'index',
                    ]
                )->name('contacts');


                Route::get(
                    '/contacts/{contactMessage}',
                    [
                        ContactMessageController::class,
                        'show',
                    ]
                )->name('contacts.show');


                Route::patch(
                    '/contacts/{contactMessage}/status',
                    [
                        ContactMessageController::class,
                        'updateStatus',
                    ]
                )->name('contacts.status');


                Route::get(
                    '/support-messages',
                    [
                        SupportInquiryController::class,
                        'supportMessages',
                    ]
                )->name('support-messages');
            });


        /*
        |--------------------------------------------------------------------------
        | Admin Notifications
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/notifications/feed',
            [
                AdminNotificationController::class,
                'feed',
            ]
        )->name('notifications.feed');


        Route::post(
            '/notifications/read-all',
            [
                AdminNotificationController::class,
                'markAllRead',
            ]
        )->name('notifications.read-all');


        Route::get(
            '/notifications/{notification}/open',
            [
                AdminNotificationController::class,
                'open',
            ]
        )->name('notifications.open');
    });