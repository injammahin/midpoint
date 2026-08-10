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


    protected $fillable = [

        'name',

        'email',

        'phone',

        'password',

        'role',

        'preferred_role',

        'status',

        'email_verified_at',

        'email_verification_token',

        'verification_sent_at',

        'last_login_at',

        'last_login_ip',

    ];


    protected $hidden = [

        'password',

        'remember_token',

        'email_verification_token',

    ];


    protected $casts = [

        'email_verified_at' =>
            'datetime',

        'verification_sent_at' =>
            'datetime',

        'last_login_at' =>
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
        return (bool) $this->status;
    }


    public function isSellerMode(): bool
    {
        return $this->preferred_role === 'seller';
    }


    public function isBuyerMode(): bool
    {
        return $this->preferred_role === 'buyer';
    }


    /*
    |--------------------------------------------------------------------------
    | Send Verification Notification
    |--------------------------------------------------------------------------
    |
    | Every resend generates a NEW random token.
    |
    | Therefore:
    |
    | Link A sent
    | ↓
    | Link B requested
    | ↓
    | Token changes
    | ↓
    | Link A becomes invalid immediately.
    |
    */

    public function sendEmailVerificationNotification()
    {
        $plainToken =
            Str::random(64);


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
    public function supportAgentProfile()
    {
        return $this->hasOne(
            \App\Models\SupportAgentProfile::class
        );
    }


    public function supportChats()
    {
        return $this->hasMany(
            \App\Models\SupportChatSession::class
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
        \App\Models\SellerSubscription::class
    );
}


/*
|--------------------------------------------------------------------------
| Current Seller Subscription
|--------------------------------------------------------------------------
*/

public function activeSellerSubscription()
{
    return $this->hasOne(
        \App\Models\SellerSubscription::class
    )
        ->where(
            'status',
            'active'
        )
        ->where(
            function ($query) {

                $query
                    ->whereNull(
                        'expires_at'
                    )
                    ->orWhere(
                        'expires_at',
                        '>',
                        now()
                    );
            }
        )
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
        \App\Models\SellerProduct::class
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
        \App\Models\SellerApplication::class
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
        \App\Models\SellerInvoice::class
    );
}
public function routeNotificationForMail(
    $notification
): string {
    return $this->email;
}
}