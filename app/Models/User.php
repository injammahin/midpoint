<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Str;


class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory;
    use Notifiable;
    use MustVerifyEmailTrait;


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Basic Information
        |--------------------------------------------------------------------------
        */

        'name',

        'email',

        'phone',

        'city',

        'password',


        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

        'role',

        'preferred_role',

        'status',


        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */

        'email_verified_at',

        'email_verification_token',

        'verification_sent_at',


        /*
        |--------------------------------------------------------------------------
        | Login Tracking
        |--------------------------------------------------------------------------
        */

        'last_login_at',

        'last_login_ip',


        /*
        |--------------------------------------------------------------------------
        | Bank / Payout Information
        |--------------------------------------------------------------------------
        */

        'bank_name',

        'bank_account_name',

        'bank_account_number',


        /*
        |--------------------------------------------------------------------------
        | Two-Factor Authentication
        |--------------------------------------------------------------------------
        */

        'two_factor_secret',

        'two_factor_recovery_codes',

        'two_factor_confirmed_at',

    ];


    /*
    |--------------------------------------------------------------------------
    | Hidden
    |--------------------------------------------------------------------------
    */

    protected $hidden = [

        'password',

        'remember_token',

        'email_verification_token',

        /*
        |--------------------------------------------------------------------------
        | Never Expose 2FA Credentials
        |--------------------------------------------------------------------------
        */

        'two_factor_secret',

        'two_factor_recovery_codes',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'email_verified_at' =>
            'datetime',

        'verification_sent_at' =>
            'datetime',

        'last_login_at' =>
            'datetime',

        'two_factor_confirmed_at' =>
            'datetime',

        'status' =>
            'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }


    public function isActive(): bool
    {
        return (bool)
            $this->status;
    }


    public function isSellerMode(): bool
    {
        return $this->preferred_role
            ===
            'seller';
    }


    public function isBuyerMode(): bool
    {
        return $this->preferred_role
            ===
            'buyer';
    }


    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Enabled?
    |--------------------------------------------------------------------------
    */

    public function hasTwoFactorEnabled(): bool
    {
        return
            !empty(
                $this->two_factor_secret
            )

            &&

            !is_null(
                $this->two_factor_confirmed_at
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Email Verification Notification
    |--------------------------------------------------------------------------
    |
    | Every resend generates a completely new token.
    |
    */

    public function sendEmailVerificationNotification()
    {
        $plainToken =
            Str::random(
                64
            );


        $this->forceFill([

            'email_verification_token' =>
                hash(
                    'sha256',
                    $plainToken
                ),

            'verification_sent_at' =>
                now(),

        ])->saveQuietly();


        $this->notify(
            new VerifyEmailNotification(
                $plainToken
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Mail Address
    |--------------------------------------------------------------------------
    */

    public function routeNotificationForMail(
        $notification
    ): string {

        return $this->email;
    }


    /*
    |--------------------------------------------------------------------------
    | Support Agent Profile
    |--------------------------------------------------------------------------
    */

    public function supportAgentProfile()
    {
        return $this->hasOne(
            SupportAgentProfile::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Support Chats
    |--------------------------------------------------------------------------
    */

    public function supportChats()
    {
        return $this->hasMany(
            SupportChatSession::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Package Subscriptions
    |--------------------------------------------------------------------------
    */

    public function sellerSubscriptions()
    {
        return $this->hasMany(
            SellerSubscription::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Seller Subscription
    |--------------------------------------------------------------------------
    |
    | SellerSubscription::active() already checks:
    |
    | status = active
    |
    | AND
    |
    | expires_at IS NULL
    | OR
    | expires_at > now()
    |
    */

    public function activeSellerSubscription()
    {
        return $this->hasOne(
            SellerSubscription::class
        )
            ->active()
            ->latestOfMany();
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Products
    |--------------------------------------------------------------------------
    */

    public function sellerProducts()
    {
        return $this->hasMany(
            SellerProduct::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Applications
    |--------------------------------------------------------------------------
    */

    public function sellerApplications()
    {
        return $this->hasMany(
            SellerApplication::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Invoices
    |--------------------------------------------------------------------------
    */

    public function sellerInvoices()
    {
        return $this->hasMany(
            SellerInvoice::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Preferences
    |--------------------------------------------------------------------------
    */

    public function notificationPreference()
    {
        return $this->hasOne(
            UserNotificationPreference::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Reviews Received
    |--------------------------------------------------------------------------
    |
    | All reviews received by this seller.
    |
    */

    public function sellerReviewsReceived()
    {
        return $this->hasMany(
            SellerReview::class,
            'seller_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Published Seller Reviews
    |--------------------------------------------------------------------------
    |
    | THIS IS THE RELATIONSHIP YOUR ERROR IS COMPLAINING ABOUT.
    |
    | FeaturedBusinessController uses:
    |
    | ->withAvg(
    |     'publishedSellerReviews as seller_rating',
    |     'rating'
    | )
    |
    | and:
    |
    | ->withCount(
    |     'publishedSellerReviews as seller_review_count'
    | )
    |
    */

    public function publishedSellerReviews()
    {
        return $this->hasMany(
            SellerReview::class,
            'seller_id'
        )
            ->where(
                'is_published',
                true
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Reviews Written
    |--------------------------------------------------------------------------
    |
    | Reviews this user has written as a buyer.
    |
    */

    public function sellerReviewsWritten()
    {
        return $this->hasMany(
            SellerReview::class,
            'buyer_id'
        );
    }
    public function sellerBusinessProfile()
    {
        return $this->hasOne(
            \App\Models\SellerBusinessProfile::class
        );
    }
    /*
|--------------------------------------------------------------------------
| Secure Transactions As Seller
|--------------------------------------------------------------------------
*/

public function secureTransactionsAsSeller()
{
    return $this->hasMany(
        \App\Models\SecureTransaction::class,
        'seller_id'
    );
}


/*
|--------------------------------------------------------------------------
| Secure Transactions As Buyer
|--------------------------------------------------------------------------
*/

public function secureTransactionsAsBuyer()
{
    return $this->hasMany(
        \App\Models\SecureTransaction::class,
        'buyer_id'
    );
}
}