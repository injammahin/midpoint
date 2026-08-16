<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SecureTransaction extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_PAYMENT_SECURED = 'payment_secured';

    public const STATUS_PREPARING_ITEM = 'preparing_item';

    public const STATUS_DISPATCHED = 'dispatched';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_INSPECTION = 'inspection';

    public const STATUS_DISPUTED = 'disputed';

    public const STATUS_RELEASE_APPROVED = 'release_approved';

    public const STATUS_PAYOUT_PENDING = 'payout_pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';


    /*
    |--------------------------------------------------------------------------
    | Payment Statuses
    |--------------------------------------------------------------------------
    */

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';


    /*
    |--------------------------------------------------------------------------
    | Payout Statuses
    |--------------------------------------------------------------------------
    */

    public const PAYOUT_LOCKED = 'locked';

    public const PAYOUT_INITIALIZING = 'initializing';

    public const PAYOUT_PENDING = 'pending';

    public const PAYOUT_SUCCESS = 'success';

    public const PAYOUT_FAILED = 'failed';

    public const PAYOUT_REVERSED = 'reversed';


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

        'preparing_at',

        'dispatched_at',

        'in_transit_at',

        'delivered_at',

        'received_at',

        'inspection_started_at',

        'inspection_ends_at',

        'auto_complete_at',

        'release_approved_at',

        'funds_released_at',

        'completed_at',

        'service_fee_rate',

        'vat_rate',

        'service_fee_amount',

        'vat_amount',

        'seller_net_amount',

        'payout_status',

        'paystack_transfer_reference',

        'paystack_transfer_code',

        'payout_initiated_at',

        'payout_completed_at',

        'seller_payment_email_sent_at',

        'buyer_payment_email_sent_at',

        'stock_reserved_at',

        'stock_reserved_until',

        'stock_released_at',

        'stock_deducted_at',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'images' => 'array',

        'quantity' => 'integer',

        'inspection_hours' => 'integer',

        'unit_price' => 'decimal:2',

        'subtotal' => 'decimal:2',

        'delivery_fee' => 'decimal:2',

        'total_amount' => 'decimal:2',

        'paid_amount' => 'decimal:2',

        'service_fee_rate' => 'decimal:4',

        'vat_rate' => 'decimal:4',

        'service_fee_amount' => 'decimal:2',

        'vat_amount' => 'decimal:2',

        'seller_net_amount' => 'decimal:2',

        'link_expires_at' => 'datetime',

        'claimed_at' => 'datetime',

        'paid_at' => 'datetime',

        'preparing_at' => 'datetime',

        'dispatched_at' => 'datetime',

        'in_transit_at' => 'datetime',

        'delivered_at' => 'datetime',

        'received_at' => 'datetime',

        'inspection_started_at' => 'datetime',

        'inspection_ends_at' => 'datetime',

        'auto_complete_at' => 'datetime',

        'release_approved_at' => 'datetime',

        'funds_released_at' => 'datetime',

        'completed_at' => 'datetime',

        'payout_initiated_at' => 'datetime',

        'payout_completed_at' => 'datetime',

        'seller_payment_email_sent_at' => 'datetime',

        'buyer_payment_email_sent_at' => 'datetime',
        'stock_reserved_at' => 'datetime',

        'stock_reserved_until' => 'datetime',

        'stock_released_at' => 'datetime',

        'stock_deducted_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Route Binding
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Transaction Reference
    |--------------------------------------------------------------------------
    */

    public static function generateReference(): string
    {
        do {

            $reference =
                'MP-TXN-'
                .
                now()->format('Ymd')
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
    | Payment Attempts
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(
            SecureTransactionPayment::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Successful Payment
    |--------------------------------------------------------------------------
    */

    public function successfulPayment()
    {
        return $this
            ->hasOne(
                SecureTransactionPayment::class
            )
            ->where(
                'status',
                SecureTransactionPayment::STATUS_SUCCESS
            )
            ->latestOfMany();
    }


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function notifications()
    {
        return $this->hasMany(
            TransactionNotification::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Dispute
    |--------------------------------------------------------------------------
    */

    public function dispute()
    {
        return $this->hasOne(
            TransactionDispute::class
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
    | Invoice Number
    |--------------------------------------------------------------------------
    */

    public function getInvoiceNumberAttribute(): string
    {
        return
            'MP-INV-'
            .
            str_replace(
                'MP-TXN-',
                '',
                $this->reference
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {

            self::STATUS_AWAITING_PAYMENT =>
                'Awaiting payment',

            self::STATUS_PAYMENT_SECURED =>
                'Payment secured',

            self::STATUS_PREPARING_ITEM =>
                'Preparing item',

            self::STATUS_DISPATCHED =>
                'Dispatched',

            self::STATUS_IN_TRANSIT =>
                'In transit',

            self::STATUS_DELIVERED =>
                'Delivered',

            self::STATUS_INSPECTION =>
                'Inspection in progress',

            self::STATUS_DISPUTED =>
                'Disputed',

            self::STATUS_RELEASE_APPROVED =>
                'Funds release approved',

            self::STATUS_PAYOUT_PENDING =>
                'Payout processing',

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
                        (string) $this->status
                    )
                ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Link Expired
    |--------------------------------------------------------------------------
    */

    public function isLinkExpired(): bool
    {
        if (!$this->link_expires_at) {
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
    | Awaiting Payment
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
    | Payment Secured
    |--------------------------------------------------------------------------
    */

    public function isPaymentSecured(): bool
    {
        return
            $this->payment_status
            ===
            self::PAYMENT_PAID;
    }


    /*
    |--------------------------------------------------------------------------
    | Disputed
    |--------------------------------------------------------------------------
    */

    public function isDisputed(): bool
    {
        return
            $this->status
            ===
            self::STATUS_DISPUTED;
    }


    /*
    |--------------------------------------------------------------------------
    | Completed
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return
            $this->status
            ===
            self::STATUS_COMPLETED;
    }


    /*
    |--------------------------------------------------------------------------
    | Inspection Active
    |--------------------------------------------------------------------------
    */

    public function hasActiveInspection(): bool
    {
        return
            $this->status
            ===
            self::STATUS_INSPECTION

            &&

            $this->inspection_ends_at

            &&

            $this
                ->inspection_ends_at
                ->isFuture();
    }


    /*
    |--------------------------------------------------------------------------
    | Auto Complete Due
    |--------------------------------------------------------------------------
    */

    public function isAutoCompleteDue(): bool
    {
        if (!$this->auto_complete_at) {
            return false;
        }


        if (
            $this->status
            ===
            self::STATUS_DISPUTED
        ) {
            return false;
        }


        return $this
            ->auto_complete_at
            ->lte(
                now()
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Can Update Fulfilment
    |--------------------------------------------------------------------------
    */

    public function canSellerUpdateFulfilment(): bool
    {
        if (
            $this->payment_status
            !==
            self::PAYMENT_PAID
        ) {
            return false;
        }


        return in_array(
            $this->status,
            [
                self::STATUS_PAYMENT_SECURED,
                self::STATUS_PREPARING_ITEM,
                self::STATUS_DISPATCHED,
                self::STATUS_IN_TRANSIT,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Next Seller Status
    |--------------------------------------------------------------------------
    */

    public function nextSellerStatus(): ?string
    {
        return match ($this->status) {

            self::STATUS_PAYMENT_SECURED =>
                self::STATUS_PREPARING_ITEM,

            self::STATUS_PREPARING_ITEM =>
                self::STATUS_DISPATCHED,

            self::STATUS_DISPATCHED =>
                self::STATUS_IN_TRANSIT,

            self::STATUS_IN_TRANSIT =>
                self::STATUS_DELIVERED,

            default =>
                null,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Payout Ready
    |--------------------------------------------------------------------------
    */

    public function isReleaseApproved(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_RELEASE_APPROVED,
                self::STATUS_PAYOUT_PENDING,
                self::STATUS_COMPLETED,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Payout Completed
    |--------------------------------------------------------------------------
    */

    public function isPayoutCompleted(): bool
    {
        return
            $this->payout_status
            ===
            self::PAYOUT_SUCCESS

            &&

            !is_null(
                $this->payout_completed_at
            );
    }
}