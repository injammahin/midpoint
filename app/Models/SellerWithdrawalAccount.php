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


    protected static function booted(): void
    {
        static::deleting(
            function (SellerWithdrawalAccount $account) {

                $kyc =
                    SellerKycVerification::query()
                        ->where(
                            'seller_id',
                            $account->seller_id
                        )
                        ->where(
                            'seller_withdrawal_account_id',
                            $account->id
                        )
                        ->first();


                if (!$kyc) {
                    return;
                }


                $kyc
                    ->forceFill([

                        'status' =>
                            SellerKycVerification::STATUS_PENDING,

                        'provider_status' =>
                            'invalidated_bank_deleted',

                        'paystack_identification_status' =>
                            'invalidated_bank_deleted',

                        'seller_withdrawal_account_id' =>
                            null,

                        'name_match' =>
                            null,

                        'bank_name_match' =>
                            null,

                        'approved_at' =>
                            null,

                        'auto_verified_at' =>
                            null,

                        'paystack_identification_completed_at' =>
                            null,

                        'failure_code' =>
                            'verified_bank_deleted',

                        'failure_message' =>
                            'The bank account linked to this KYC verification was deleted. Add an active verified bank and verify the exact BVN again.',

                        'provider_response' =>
                            array_merge(
                                $kyc->provider_response ?? [],
                                [
                                    'exact_bvn_confirmed' =>
                                        false,

                                    'local_invalidation' =>
                                        'verified_bank_deleted',

                                    'invalidated_at' =>
                                        now()->toIso8601String(),
                                ]
                            ),

                    ])
                    ->save();
            }
        );
    }


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
    | Exclusive Seller Ownership
    |--------------------------------------------------------------------------
    |
    | A withdrawal bank account may belong to only one Midpoint seller. If an
    | old data set contains the same bank/account hash for multiple sellers,
    | fail closed until the duplicate account is removed by support.
    |
    */

    public function isUniquelyOwnedBySeller(): bool
    {
        $accountHash = trim(
            (string) $this->account_number_hash
        );


        if (
            $accountHash === ''
            || trim((string) $this->bank_code) === ''
            || !$this->seller_id
        ) {
            return false;
        }


        return !static::query()
            ->where(
                'seller_id',
                '!=',
                $this->seller_id
            )
            ->where(
                'bank_code',
                $this->bank_code
            )
            ->where(
                'account_number_hash',
                $accountHash
            )
            ->exists();
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
