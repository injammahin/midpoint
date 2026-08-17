<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerWithdrawal extends Model
{
    public const STATUS_PENDING =
        'pending';

    public const STATUS_PROCESSING =
        'processing';

    public const STATUS_OTP =
        'otp';

    public const STATUS_SUCCESSFUL =
        'successful';

    public const STATUS_FAILED =
        'failed';

    public const STATUS_REVERSED =
        'reversed';


    protected $fillable = [
        'seller_wallet_id',
        'seller_id',
        'seller_withdrawal_account_id',
        'reference',
        'paystack_transfer_reference',
        'paystack_transfer_code',
        'paystack_recipient_code',
        'bank_name',
        'account_name',
        'account_number_last4',
        'currency',
        'amount',
        'status',
        'failure_reason',
        'meta',
        'requested_at',
        'initiated_at',
        'completed_at',
        'failed_at',
    ];


    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'requested_at' => 'datetime',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];


    public function wallet()
    {
        return $this->belongsTo(
            SellerWallet::class,
            'seller_wallet_id'
        );
    }


    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    public function withdrawalAccount()
    {
        return $this->belongsTo(
            SellerWithdrawalAccount::class,
            'seller_withdrawal_account_id'
        );
    }


    public function walletTransactions()
    {
        return $this->hasMany(
            SellerWalletTransaction::class,
            'seller_withdrawal_id'
        );
    }


    public function isFinal(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_SUCCESSFUL,
                self::STATUS_FAILED,
                self::STATUS_REVERSED,
            ],
            true
        );
    }


    public static function generateReference(
        int $sellerId
    ): string {

        do {

            $reference =
                strtolower(
                    'mp-withdraw-'
                    .
                    $sellerId
                    .
                    '-'
                    .
                    now()->format(
                        'ymdHis'
                    )
                    .
                    '-'
                    .
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