<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    protected $fillable = [
        'seller_id',
        'currency',
        'available_balance',
        'pending_withdrawal_balance',
        'total_credited',
        'total_withdrawn',
        'total_spent',
    ];


    protected $casts = [

        'available_balance' =>
            'decimal:2',

        'pending_withdrawal_balance' =>
            'decimal:2',

        'total_credited' =>
            'decimal:2',

        'total_withdrawn' =>
            'decimal:2',

        'total_spent' =>
            'decimal:2',
    ];


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
    | Wallet Ledger
    |--------------------------------------------------------------------------
    */

    public function transactions()
    {
        return $this->hasMany(
            SellerWalletTransaction::class,
            'seller_wallet_id'
        );
    }
}