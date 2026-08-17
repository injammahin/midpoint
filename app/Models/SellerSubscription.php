<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    public const STATUS_PENDING =
        'pending';

    public const STATUS_ACTIVE =
        'active';

    public const STATUS_EXPIRED =
        'expired';

    public const STATUS_CANCELLED =
        'cancelled';


    protected $fillable = [

        'user_id',

        'seller_package_id',

        'seller_application_id',

        'seller_invoice_id',

        'purchase_type',

        'renewed_from_subscription_id',

        'renewal_sequence',

        'package_name',

        'package_price',

        'price',

        'billing_period',

        'product_limit',

        'status',

        'payment_reference',

        'starts_at',

        'started_at',

        'expires_at',

        'cancelled_at',
    ];


    protected $casts = [

        'package_price' =>
            'decimal:2',

        'price' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'renewal_sequence' =>
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
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function package()
    {
        return $this->belongsTo(
            SellerPackage::class,
            'seller_package_id'
        );
    }


    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }


    public function invoice()
    {
        return $this->belongsTo(
            SellerInvoice::class,
            'seller_invoice_id'
        );
    }


    public function renewedFrom()
    {
        return $this->belongsTo(
            self::class,
            'renewed_from_subscription_id'
        );
    }


    public function renewals()
    {
        return $this->hasMany(
            self::class,
            'renewed_from_subscription_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
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
    | Current Status
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


        return
            $this->expires_at
                ->isFuture();
    }


    /*
    |--------------------------------------------------------------------------
    | Remaining Days
    |--------------------------------------------------------------------------
    */

    public function getDaysLeftAttribute(): int
    {
        if (
            !$this->isCurrentlyActive()
            ||
            !$this->expires_at
        ) {

            return 0;
        }


        return max(
            0,
            now()
                ->startOfDay()
                ->diffInDays(
                    $this
                        ->expires_at
                        ->copy()
                        ->startOfDay(),
                    false
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remaining Time
    |--------------------------------------------------------------------------
    */

    public function getRemainingTimeAttribute(): string
    {
        if (
            !$this
                ->isCurrentlyActive()
        ) {

            return
                'Expired';
        }


        if (
            !$this->expires_at
        ) {

            return
                'No expiry';
        }


        return now()
            ->diffForHumans(
                $this->expires_at,
                [
                    'parts' => 2,
                    'short' => true,
                    'syntax' =>
                        \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                ]
            )
            .
            ' left';
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Type
    |--------------------------------------------------------------------------
    */

    public function getPurchaseTypeLabelAttribute(): string
    {
        return match (
            $this->purchase_type
            ?:
            SellerInvoice::TYPE_INITIAL
        ) {

            SellerInvoice::TYPE_RENEWAL =>
                'Renewal',

            SellerInvoice::TYPE_UPGRADE =>
                'Upgrade',

            SellerInvoice::TYPE_DOWNGRADE =>
                'Plan change',

            default =>
                'Initial',
        };
    }
}