<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDispute extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Workflow Statuses
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
    | Resolution Types
    |--------------------------------------------------------------------------
    */

    public const RESOLUTION_FULL_REFUND =
        'full_refund';

    public const RESOLUTION_PARTIAL_REFUND =
        'partial_refund';

    public const RESOLUTION_RELEASE_TO_SELLER =
        'release_to_seller';

    public const RESOLUTION_RESUME_TRANSACTION =
        'resume_transaction';


    /*
    |--------------------------------------------------------------------------
    | Resolution Statuses
    |--------------------------------------------------------------------------
    */

    public const RESOLUTION_STATUS_INITIATING =
        'initiating';

    public const RESOLUTION_STATUS_REFUND_PENDING =
        'refund_pending';

    public const RESOLUTION_STATUS_REFUND_PROCESSING =
        'refund_processing';

    public const RESOLUTION_STATUS_REFUND_NEEDS_ATTENTION =
        'refund_needs_attention';

    public const RESOLUTION_STATUS_REFUND_FAILED =
        'refund_failed';

    public const RESOLUTION_STATUS_REFUND_SYNC_REQUIRED =
        'refund_sync_required';

    public const RESOLUTION_STATUS_REFUND_PROCESSED =
        'refund_processed';

    public const RESOLUTION_STATUS_COMPLETED =
        'completed';


    public const ROOM_CLOSE_MANUAL =
        'manual';

    public const ROOM_CLOSE_FINAL_DECISION =
        'final_decision';


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

        'room_activated_at',

        'room_activated_by',

        'room_closed_at',

        'room_closed_by',

        'room_close_type',

        'room_close_reason',

        'resolution_type',

        'resolution_status',

        'refund_amount',

        'approved_refund_amount',

        'refund_gateway_fee_amount',

        'refund_amount_subunit',

        'approved_refund_amount_subunit',

        'refund_gateway_fee_subunit',

'seller_settlement_amount',

        'resolution_service_fee_amount',

        'resolution_vat_amount',

        'resolution_note',

        'resolved_by',

        'resolution_initiated_at',

        'paystack_refund_id',

        'paystack_refund_reference',

        'paystack_refund_status',

        'paystack_refund_amount_subunit',

        'paystack_refund_requested_at',

        'refund_expected_at',

        'refund_processed_at',

        'refund_error',

        'opened_at',

        'resolved_at',

    ];


    protected $casts = [

        'evidence' =>
            'array',

        'room_activated_at' =>
            'datetime',

        'room_closed_at' =>
            'datetime',

        'resolution_initiated_at' =>
            'datetime',

        'refund_expected_at' =>
            'datetime',

        'refund_processed_at' =>
            'datetime',

        'paystack_refund_requested_at' =>
            'datetime',

        'refund_amount_subunit' =>
            'integer',

        'approved_refund_amount_subunit' =>
            'integer',

        'refund_gateway_fee_subunit' =>
            'integer',

        'paystack_refund_amount_subunit' =>
            'integer',

        'opened_at' =>
            'datetime',

        'resolved_at' =>
            'datetime',

        'refund_amount' =>
            'decimal:2',

        'approved_refund_amount' =>
            'decimal:2',

        'refund_gateway_fee_amount' =>
            'decimal:2',

        'seller_settlement_amount' =>
            'decimal:2',

        'resolution_service_fee_amount' =>
            'decimal:2',

        'resolution_vat_amount' =>
            'decimal:2',

    ];


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


    public function roomActivator()
    {
        return $this->belongsTo(
            User::class,
            'room_activated_by'
        );
    }


    public function roomCloser()
    {
        return $this->belongsTo(
            User::class,
            'room_closed_by'
        );
    }


    public function resolver()
    {
        return $this->belongsTo(
            User::class,
            'resolved_by'
        );
    }


    public function statusHistories()
    {
        return $this->hasMany(
            TransactionDisputeStatusHistory::class,
            'transaction_dispute_id'
        );
    }


    public function messages()
    {
        return $this->hasMany(
            TransactionDisputeMessage::class,
            'transaction_dispute_id'
        );
    }


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
                        (string) $this->status
                    )
                ),
        };
    }


    public function getResolutionTypeLabelAttribute(): ?string
    {
        if (!$this->resolution_type) {
            return null;
        }

        return match ($this->resolution_type) {

            self::RESOLUTION_FULL_REFUND =>
                'Full refund',

            self::RESOLUTION_PARTIAL_REFUND =>
                'Partial refund',

            self::RESOLUTION_RELEASE_TO_SELLER =>
                'Release to seller',

            self::RESOLUTION_RESUME_TRANSACTION =>
                'Resume transaction',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $this->resolution_type
                    )
                ),
        };
    }


    public function getResolutionStatusLabelAttribute(): ?string
    {
        if (!$this->resolution_status) {
            return null;
        }


        return match ($this->resolution_status) {

            self::RESOLUTION_STATUS_INITIATING =>
                'Decision recorded',

            self::RESOLUTION_STATUS_REFUND_PENDING =>
                'Refund pending',

            self::RESOLUTION_STATUS_REFUND_PROCESSING =>
                'Refund processing',

            self::RESOLUTION_STATUS_REFUND_NEEDS_ATTENTION =>
                'Refund needs attention',

            self::RESOLUTION_STATUS_REFUND_FAILED =>
                'Refund failed',

            self::RESOLUTION_STATUS_REFUND_SYNC_REQUIRED =>
                'Refund reconciliation required',

            self::RESOLUTION_STATUS_REFUND_PROCESSED =>
                'Refund processed',

            self::RESOLUTION_STATUS_COMPLETED =>
                'Completed',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $this->resolution_status
                    )
                ),
        };
    }


    public function isResolved(): bool
    {
        return
            $this->status
            ===
            self::STATUS_RESOLVED;
    }


    public function isRoomActive(): bool
    {
        return
            $this->isRoomActivated()
            &&
            !$this->isRoomClosed();
    }


    public function isRoomActivated(): bool
    {
        return !is_null(
            $this->room_activated_at
        );
    }


    public function isRoomClosed(): bool
    {
        return
            !is_null(
                $this->room_closed_at
            )
            ||
            $this->isResolved();
    }


    public function getRoomClosedMessageAttribute(): string
    {
        return
            trim(
                (string) $this->room_close_reason
            )
            ?:
            (
                $this->resolution_type
                    ? 'Midpoint made the final dispute decision. This room is closed; review the decision on the transaction page.'
                    : 'Midpoint Support closed this dispute room. Review the latest dispute status on the transaction page.'
            );
    }


    public function hasRefundResolution(): bool
    {
        return in_array(
            $this->resolution_type,
            [
                self::RESOLUTION_FULL_REFUND,
                self::RESOLUTION_PARTIAL_REFUND,
            ],
            true
        );
    }
}
