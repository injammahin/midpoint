<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_ACTIVE =
        'active';

    public const STATUS_EXPIRED =
        'expired';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'seller_package_id',

        'seller_application_id',

        'package_name',

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        |
        | Your existing database currently contains both fields.
        |
        | package_price is required in your existing table.
        |
        */

        'package_price',

        'price',

        'billing_period',

        'product_limit',

        'status',

        'payment_reference',

        'started_at',

        'expires_at',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'package_price' =>
            'decimal:2',

        'price' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'started_at' =>
            'datetime',

        'expires_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Package
    |--------------------------------------------------------------------------
    */

    public function package()
    {
        return $this->belongsTo(
            SellerPackage::class,
            'seller_package_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Application
    |--------------------------------------------------------------------------
    */

    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {

        return $query

            ->where(
                'status',
                self::STATUS_ACTIVE
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
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Expired Scope
    |--------------------------------------------------------------------------
    */

    public function scopeExpired(
        Builder $query
    ): Builder {

        return $query->where(
            function ($query) {

                $query

                    ->where(
                        'status',
                        self::STATUS_EXPIRED
                    )

                    ->orWhere(
                        function ($query) {

                            $query

                                ->whereNotNull(
                                    'expires_at'
                                )

                                ->where(
                                    'expires_at',
                                    '<=',
                                    now()
                                );
                        }
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Current Status
    |--------------------------------------------------------------------------
    */

    public function isCurrentlyActive(): bool
    {
        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $this->status
            !==
            self::STATUS_ACTIVE
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | No Expiration
        |--------------------------------------------------------------------------
        */

        if (
            !$this->expires_at
        ) {

            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Time
        |--------------------------------------------------------------------------
        */

        return $this
            ->expires_at
            ->isFuture();
    }


    /*
    |--------------------------------------------------------------------------
    | Days Left
    |--------------------------------------------------------------------------
    */

    public function getDaysLeftAttribute(): int
    {
        if (
            !$this->expires_at
            ||
            !$this->isCurrentlyActive()
        ) {

            return 0;
        }


        $seconds =
            now()->diffInSeconds(
                $this->expires_at,
                false
            );


        if (
            $seconds <= 0
        ) {

            return 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Round Up
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | 4 hours left
        |
        | shows:
        |
        | 1 day left
        |
        */

        return (int) ceil(
            $seconds / 86400
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remaining Time Text
    |--------------------------------------------------------------------------
    */

    public function getRemainingTimeAttribute(): string
    {
        if (
            !$this->expires_at
            ||
            !$this->isCurrentlyActive()
        ) {

            return 'Expired';
        }


        return now()
            ->diffForHumans(
                $this->expires_at,
                true
            )
            .
            ' left';
    }
}