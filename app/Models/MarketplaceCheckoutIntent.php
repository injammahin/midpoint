<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MarketplaceCheckoutIntent extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_CREATED =
        'created';


    public const STATUS_INITIALIZED =
        'initialized';


    public const STATUS_PENDING =
        'pending';


    public const STATUS_SUCCESS =
        'success';


    public const STATUS_FAILED =
        'failed';


    public const STATUS_ABANDONED =
        'abandoned';


    public const STATUS_EXPIRED =
        'expired';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'token',

        'seller_product_id',

        'seller_id',

        'buyer_id',

        'buyer_email',

        'buyer_phone',

        'delivery_address',

        'product_name',

        'product_description',

        'product_images',

        'quantity',

        'unit_price',

        'subtotal',

        'delivery_fee',

        'total_amount',

        'currency',

        'paystack_reference',

        'access_code',

        'authorization_url',

        'payment_status',

        'paystack_transaction_id',

        'channel',

        'gateway_response',

        'reserved_until',

        'initialized_at',

        'verified_at',

        'paid_at',

        'finalized_at',

        'secure_transaction_id',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'product_images' =>
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


        'reserved_until' =>
            'datetime',


        'initialized_at' =>
            'datetime',


        'verified_at' =>
            'datetime',


        'paid_at' =>
            'datetime',


        'finalized_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Generate Temporary Token
    |--------------------------------------------------------------------------
    */

    public static function generateToken(): string
    {
        do {

            $token =
                Str::random(
                    64
                );

        } while (

            static::query()

                ->where(
                    'token',
                    $token
                )

                ->exists()

        );


        return $token;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Marketplace Paystack Reference
    |--------------------------------------------------------------------------
    */

    public static function generatePaystackReference(
        int $productId
    ): string {

        do {

            $reference =

                'MP-MKT-'

                .

                $productId

                .

                '-'

                .

                now()->format(
                    'YmdHis'
                )

                .

                '-'

                .

                Str::upper(
                    Str::random(
                        8
                    )
                );

        } while (

            static::query()

                ->where(
                    'paystack_reference',
                    $reference
                )

                ->exists()

        );


        return $reference;
    }


    /*
    |--------------------------------------------------------------------------
    | Product
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
    | Final Secure Transaction
    |--------------------------------------------------------------------------
    */

    public function secureTransaction()
    {
        return $this->belongsTo(
            SecureTransaction::class,
            'secure_transaction_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Reservation
    |--------------------------------------------------------------------------
    */

    public function hasActiveReservation(): bool
    {
        return

            $this->secure_transaction_id
            ===
            null

            &&

            in_array(
                $this->payment_status,
                [

                    self::STATUS_CREATED,

                    self::STATUS_INITIALIZED,

                    self::STATUS_PENDING,

                ],
                true
            )

            &&

            $this->reserved_until

            &&

            $this
                ->reserved_until
                ->isFuture();
    }
}