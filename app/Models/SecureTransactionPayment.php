<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SecureTransactionPayment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Statuses
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


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'secure_transaction_id',

        'buyer_id',

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


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'amount' =>
            'decimal:2',

        'amount_subunit' =>
            'integer',

        'paystack_transaction_id' =>
            'string',

        'initialized_at' =>
            'datetime',

        'verified_at' =>
            'datetime',

        'paid_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Secure Transaction
    |--------------------------------------------------------------------------
    */

    public function secureTransaction()
    {
        return $this->belongsTo(
            SecureTransaction::class
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
    | Successful?
    |--------------------------------------------------------------------------
    */

    public function isSuccessful(): bool
    {
        return
            $this->status
            ===
            self::STATUS_SUCCESS;
    }
}