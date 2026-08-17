<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerWalletTransaction extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_TRANSACTION_RELEASE =
        'transaction_release';


    /*
    |--------------------------------------------------------------------------
    | Direction
    |--------------------------------------------------------------------------
    */

    public const DIRECTION_CREDIT =
        'credit';

    public const DIRECTION_DEBIT =
        'debit';


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_POSTED =
        'posted';


    protected $fillable = [
        'seller_wallet_id',
        'seller_id',
        'secure_transaction_id',
        'reference',
        'type',
        'direction',
        'status',
        'currency',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'meta',
        'processed_at',
    ];


    protected $casts = [
        'amount' =>
            'decimal:2',

        'balance_before' =>
            'decimal:2',

        'balance_after' =>
            'decimal:2',

        'meta' =>
            'array',

        'processed_at' =>
            'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    public function wallet()
    {
        return $this->belongsTo(
            SellerWallet::class,
            'seller_wallet_id'
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
    | Secure Transaction
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
    | Generate Wallet Reference
    |--------------------------------------------------------------------------
    */

    public static function generateReference(): string
    {
        do {

            $reference =
                'MP-WALLET-'
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
                    'reference',
                    $reference
                )
                ->exists()
        );

        return $reference;
    }
}