<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status
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

        'package_price',

        'price',

        'billing_period',

        'product_limit',

        'status',

        'payment_reference',

        /*
        |--------------------------------------------------------------------------
        | Both Names Supported
        |--------------------------------------------------------------------------
        |
        | Original DB:
        | starts_at
        |
        | Newer application:
        | started_at
        |
        */

        'starts_at',

        'started_at',

        'expires_at',

        'cancelled_at',

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

        'starts_at' =>
            'datetime',

        'started_at' =>
            'datetime',

        'expires_at' =>
            'datetime',

        'cancelled_at' =>
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
    | Package
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
    | Application
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
                function (
                    $query
                ) {

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

        return $query

            ->where(
                function (
                    $query
                ) {

                    $query

                        ->where(
                            'status',
                            self::STATUS_EXPIRED
                        )

                        ->orWhere(
                            function (
                                $query
                            ) {

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
    | Is Active?
    |--------------------------------------------------------------------------
    */

    public function isCurrentlyActive(): bool
    {
        if (
            $this->status
            !==
            self::STATUS_ACTIVE
        ) {

            return false;
        }


        if (
            !$this->expires_at
        ) {

            return true;
        }


        return $this
            ->expires_at
            ->isFuture();
    }


    /*
    |--------------------------------------------------------------------------
    | Days Remaining
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


        if ($seconds <= 0) {

            return 0;
        }


        return (int) ceil(
            $seconds
            /
            86400
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remaining Text
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