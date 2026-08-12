<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDispute extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_OPEN =
        'open';

    public const STATUS_UNDER_REVIEW =
        'under_review';

    public const STATUS_AWAITING_BUYER =
        'awaiting_buyer';

    public const STATUS_AWAITING_SELLER =
        'awaiting_seller';

    public const STATUS_RESOLVED =
        'resolved';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'secure_transaction_id',

        'buyer_id',

        'seller_id',

        'reason',

        'description',

        'desired_outcome',

        'evidence',

        'return_method',

        'return_proof_path',

        'status',

        'admin_note',

        'opened_at',

        'resolved_at',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'evidence' =>
            'array',

        'opened_at' =>
            'datetime',

        'resolved_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function transaction()
    {
        return $this->belongsTo(
            SecureTransaction::class,
            'secure_transaction_id'
        );
    }


    public function buyer()
    {
        return $this->belongsTo(
            User::class,
            'buyer_id'
        );
    }


    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    public function statusHistories()
    {
        return $this->hasMany(
            TransactionDisputeStatusHistory::class,
            'transaction_dispute_id'
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

            self::STATUS_OPEN =>
                'Open',

            self::STATUS_UNDER_REVIEW =>
                'Under Review',

            self::STATUS_AWAITING_BUYER =>
                'Awaiting Buyer',

            self::STATUS_AWAITING_SELLER =>
                'Awaiting Seller',

            self::STATUS_RESOLVED =>
                'Resolved',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $this->status
                    )
                ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Resolved
    |--------------------------------------------------------------------------
    */

    public function isResolved(): bool
    {
        return
            $this->status
            ===
            self::STATUS_RESOLVED;
    }
}