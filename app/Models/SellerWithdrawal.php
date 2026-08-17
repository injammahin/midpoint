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

        'amount' =>
            'decimal:2',

        'meta' =>
            'array',

        'requested_at' =>
            'datetime',

        'initiated_at' =>
            'datetime',

        'completed_at' =>
            'datetime',

        'failed_at' =>
            'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

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


    public function isProcessing(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING,

                self::STATUS_PROCESSING,

                self::STATUS_OTP,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Display
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->status
        ) {

            self::STATUS_PENDING =>
                'Pending',

            self::STATUS_PROCESSING =>
                'Processing',

            self::STATUS_OTP =>
                'OTP Required',

            self::STATUS_SUCCESSFUL =>
                'Successful',

            self::STATUS_FAILED =>
                'Failed',

            self::STATUS_REVERSED =>
                'Reversed',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        (string)
                        $this->status
                    )
                ),
        };
    }


    public function getStatusToneAttribute(): string
    {
        return match (
            $this->status
        ) {

            self::STATUS_SUCCESSFUL =>
                'success',

            self::STATUS_FAILED,
            self::STATUS_REVERSED =>
                'danger',

            self::STATUS_OTP =>
                'warning',

            self::STATUS_PENDING,
            self::STATUS_PROCESSING =>
                'processing',

            default =>
                'neutral',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Withdrawal Reference
    |--------------------------------------------------------------------------
    */

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

                    now()
                        ->format(
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