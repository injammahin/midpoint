<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class SecureTransaction extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_AWAITING_PAYMENT =
        'awaiting_payment';


    public const STATUS_PAYMENT_SECURED =
        'payment_secured';


    public const STATUS_DISPATCHED =
        'dispatched';


    public const STATUS_INSPECTION =
        'inspection';


    public const STATUS_DISPUTED =
        'disputed';


    public const STATUS_RELEASE_APPROVED =
        'release_approved';


    public const STATUS_COMPLETED =
        'completed';


    public const STATUS_CANCELLED =
        'cancelled';


    public const STATUS_EXPIRED =
        'expired';


    /*
    |--------------------------------------------------------------------------
    | Payment Statuses
    |--------------------------------------------------------------------------
    */

    public const PAYMENT_UNPAID =
        'unpaid';


    public const PAYMENT_PENDING =
        'pending';


    public const PAYMENT_PAID =
        'paid';


    public const PAYMENT_FAILED =
        'failed';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'reference',

        'public_token',

        'seller_id',

        'buyer_id',

        'seller_product_id',

        'transaction_type',

        'title',

        'description',

        'images',

        'quantity',

        'unit_price',

        'subtotal',

        'delivery_fee',

        'total_amount',

        'currency',

        'buyer_email',

        'buyer_phone',

        'delivery_note',

        'inspection_hours',

        'status',

        'payment_status',

        'paystack_reference',

        'paystack_transaction_id',

        'paid_amount',

        'link_expires_at',

        'claimed_at',

        'paid_at',

        'dispatched_at',

        'received_at',

        'inspection_ends_at',

        'completed_at',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'images' =>
            'array',

        'quantity' =>
            'integer',

        'unit_price' =>
            'decimal:2',

        'subtotal' =>
            'decimal:2',

        'delivery_fee' =>
            'decimal:2',

        'total_amount' =>
            'decimal:2',

        'paid_amount' =>
            'decimal:2',

        'inspection_hours' =>
            'integer',

        'link_expires_at' =>
            'datetime',

        'claimed_at' =>
            'datetime',

        'paid_at' =>
            'datetime',

        'dispatched_at' =>
            'datetime',

        'received_at' =>
            'datetime',

        'inspection_ends_at' =>
            'datetime',

        'completed_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Route Binding
    |--------------------------------------------------------------------------
    |
    | Never expose database ID in the buyer payment URL.
    |
    */

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Reference
    |--------------------------------------------------------------------------
    */

    public static function generateReference(): string
    {
        do {

            $reference =
                'MP-TXN-'
                .
                now()->format(
                    'Ymd'
                )
                .
                '-'
                .
                Str::upper(
                    Str::random(7)
                );

        } while (
            static::query()
                ->where(
                    'reference',
                    $reference
                )
                ->exists()
        );


        return $reference;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Public Token
    |--------------------------------------------------------------------------
    */

    public static function generatePublicToken(): string
    {
        do {

            $token =
                Str::random(64);

        } while (
            static::query()
                ->where(
                    'public_token',
                    $token
                )
                ->exists()
        );


        return $token;
    }


    /*
    |--------------------------------------------------------------------------
    | Seller
    |--------------------------------------------------------------------------
    */

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer
    |--------------------------------------------------------------------------
    */

    public function buyer()
    {
        return $this->belongsTo(
            User::class,
            'buyer_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Listed Product
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(
            SellerProduct::class,
            'seller_product_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Share URL
    |--------------------------------------------------------------------------
    */

    public function getShareUrlAttribute(): string
    {
        return route(
            'secure-transactions.show',
            $this
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Link Expired?
    |--------------------------------------------------------------------------
    */

    public function isLinkExpired(): bool
    {
        if (
            !$this->link_expires_at
        ) {
            return false;
        }


        return $this
            ->link_expires_at
            ->lte(
                now()
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Awaiting Payment?
    |--------------------------------------------------------------------------
    */

    public function isAwaitingPayment(): bool
    {
        return
            $this->status
            ===
            self::STATUS_AWAITING_PAYMENT

            &&

            $this->payment_status
            ===
            self::PAYMENT_UNPAID;
    }


    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match(
            $this->status
        ) {

            self::STATUS_AWAITING_PAYMENT =>
                'Awaiting buyer payment',

            self::STATUS_PAYMENT_SECURED =>
                'Payment secured',

            self::STATUS_DISPATCHED =>
                'Dispatched',

            self::STATUS_INSPECTION =>
                'Under inspection',

            self::STATUS_DISPUTED =>
                'Disputed',

            self::STATUS_RELEASE_APPROVED =>
                'Payment release approved',

            self::STATUS_COMPLETED =>
                'Completed',

            self::STATUS_CANCELLED =>
                'Cancelled',

            self::STATUS_EXPIRED =>
                'Expired',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $this->status
                    )
                ),
        };
    }
}