<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerInvoicePayment extends Model
{
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


    protected $fillable = [

        'seller_invoice_id',

        'seller_application_id',

        'user_id',

        'provider',

        'reference',

        'access_code',

        'authorization_url',

        'amount',

        'amount_subunit',

        'currency',

        'status',

        'paystack_transaction_id',

        'channel',

        'gateway_response',

        'initialized_at',

        'verified_at',

        'paid_at',

    ];


    protected $casts = [

        'amount' =>
            'decimal:2',

        'amount_subunit' =>
            'integer',

        'paystack_transaction_id' =>
            'integer',

        'initialized_at' =>
            'datetime',

        'verified_at' =>
            'datetime',

        'paid_at' =>
            'datetime',

    ];


    public function invoice()
    {
        return $this->belongsTo(
            SellerInvoice::class,
            'seller_invoice_id'
        );
    }


    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function isSuccessful(): bool
    {
        return
            $this->status
            ===
            self::STATUS_SUCCESS;
    }
}