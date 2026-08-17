<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SellerWithdrawalAccount extends Model
{
    protected $fillable = [
        'seller_id',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'paystack_recipient_code',
        'is_verified',
        'is_active',
        'verified_at',
    ];


    protected $hidden = [
        'account_number_encrypted',
        'account_number_hash',
    ];


    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];


    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    public function withdrawals()
    {
        return $this->hasMany(
            SellerWithdrawal::class,
            'seller_withdrawal_account_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Encrypt Account Number
    |--------------------------------------------------------------------------
    */

    public function setAccountNumberAttribute(
        $value
    ): void {

        $number =
            preg_replace(
                '/\D+/',
                '',
                (string) $value
            );


        $this->attributes[
            'account_number_encrypted'
        ] =
            Crypt::encryptString(
                $number
            );


        $this->attributes[
            'account_number_hash'
        ] =
            hash(
                'sha256',
                $number
            );


        $this->attributes[
            'account_number_last4'
        ] =
            substr(
                $number,
                -4
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Decrypt Account Number
    |--------------------------------------------------------------------------
    */

    public function getAccountNumberAttribute(): ?string
    {
        if (
            empty(
                $this->attributes[
                    'account_number_encrypted'
                ]
            )
        ) {
            return null;
        }


        return Crypt::decryptString(
            $this->attributes[
                'account_number_encrypted'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Masked Number
    |--------------------------------------------------------------------------
    */

    public function getMaskedAccountNumberAttribute(): string
    {
        return
            '••••••'
            .
            $this->account_number_last4;
    }
}