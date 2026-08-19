<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerInvoice extends Model
{
    public const TYPE_INITIAL =
        'initial';

    public const TYPE_RENEWAL =
        'renewal';

    public const TYPE_UPGRADE =
        'upgrade';

    /*
     * Historical only.
     * New downgrades are not allowed.
     */
    public const TYPE_DOWNGRADE =
        'downgrade';


    protected $fillable = [

        'invoice_number',

        'seller_application_id',

        'seller_package_id',

        'purchase_type',

        'renewal_of_subscription_id',

        'package_name',

        'billing_period',

        'product_limit',

        /*
         * Full package price.
         *
         * For upgrade this may be different from amount.
         */
        'package_price',

        /*
         * Unused value from previous/current package.
         */
        'proration_credit',

        /*
         * Value already consumed from previous/current package.
         */
        'proration_used_amount',

        'proration_calculated_at',

        'user_id',

        /*
         * Actual amount seller needs to pay.
         */
        'amount',

        'currency',

        'status',

        'payment_method',

        'payment_reference',

        'issued_at',

        'due_at',

        'paid_at',
    ];


    protected $casts = [

        'amount' =>
            'decimal:2',

        'package_price' =>
            'decimal:2',

        'proration_credit' =>
            'decimal:2',

        'proration_used_amount' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'proration_calculated_at' =>
            'datetime',

        'issued_at' =>
            'datetime',

        'due_at' =>
            'datetime',

        'paid_at' =>
            'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Invoice Number
    |--------------------------------------------------------------------------
    */

    public static function generateInvoiceNumber(): string
    {
        do {

            $number =
                'MP-INV-'
                .
                now()->format(
                    'Ym'
                )
                .
                '-'
                .
                Str::upper(
                    Str::random(
                        7
                    )
                );

        } while (
            static::query()

                ->where(
                    'invoice_number',
                    $number
                )

                ->exists()
        );


        return $number;
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }


    public function package()
    {
        return $this->belongsTo(
            SellerPackage::class,
            'seller_package_id'
        );
    }


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function payments()
    {
        return $this->hasMany(
            SellerInvoicePayment::class,
            'seller_invoice_id'
        );
    }


    public function renewalOfSubscription()
    {
        return $this->belongsTo(
            SellerSubscription::class,
            'renewal_of_subscription_id'
        );
    }


    public function subscription()
    {
        return $this->hasOne(
            SellerSubscription::class,
            'seller_invoice_id'
        );
    }


    public function walletTransactions()
    {
        return $this->hasMany(
            SellerWalletTransaction::class,
            'seller_invoice_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return
            $this->status
            ===
            'paid';
    }


    public function isInitialPurchase(): bool
    {
        return
            (
                $this->purchase_type
                ?:
                self::TYPE_INITIAL
            )
            ===
            self::TYPE_INITIAL;
    }


    public function isRecurringPurchase(): bool
    {
        return
            !$this
                ->isInitialPurchase();
    }


    public function isUpgrade(): bool
    {
        return
            $this->purchase_type
            ===
            self::TYPE_UPGRADE;
    }


    public function hasProrationCredit(): bool
    {
        return
            (float) $this->proration_credit
            >
            0;
    }


    public function getPurchaseTypeLabelAttribute(): string
    {
        return match (
            $this->purchase_type
            ?:
            self::TYPE_INITIAL
        ) {

            self::TYPE_RENEWAL =>
                'Renewal',

            self::TYPE_UPGRADE =>
                'Upgrade',

            self::TYPE_DOWNGRADE =>
                'Plan change',

            default =>
                'Initial purchase',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Effective Package Snapshot
    |--------------------------------------------------------------------------
    |
    | Old invoices continue working through fallbacks.
    |
    */

    public function getEffectivePackageNameAttribute(): string
    {
        return
            $this->package_name

            ?:

            $this->application
                ?->package_name

            ?:

            'Seller Package';
    }


    public function getEffectiveBillingPeriodAttribute(): string
    {
        return
            $this->billing_period

            ?:

            $this->application
                ?->billing_period

            ?:

            'month';
    }


    public function getEffectiveProductLimitAttribute(): int
    {
        return
            (int) (
                $this->product_limit

                ?:

                $this->application
                    ?->product_limit

                ?:

                0
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Full Target Package Price
    |--------------------------------------------------------------------------
    |
    | Important for prorated upgrades.
    |
    | Example:
    |
    | Premium price = 25,000
    | amount actually paid = 15,666
    |
    | effective_package_price = 25,000
    |
    */

    public function getEffectivePackagePriceAttribute(): float
    {
        return round(
            (float) (
                $this->package_price
                ??
                $this->amount
                ??
                0
            ),
            2
        );
    }
}