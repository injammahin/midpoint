<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionDisputeMessage extends Model
{
    public const ROLE_SYSTEM =
        'system';

    public const ROLE_ADMIN =
        'admin';

    public const ROLE_BUYER =
        'buyer';

    public const ROLE_SELLER =
        'seller';


    public const VISIBILITY_ALL =
        'all';

    public const VISIBILITY_BUYER =
        'buyer';

    public const VISIBILITY_SELLER =
        'seller';

    public const VISIBILITY_INTERNAL =
        'internal';


    protected $fillable = [
        'transaction_dispute_id',
        'secure_transaction_id',
        'sender_id',
        'sender_role',
        'visibility',
        'message',
        'attachments',
        'is_system',
    ];


    protected $casts = [
        'attachments' =>
            'array',

        'is_system' =>
            'boolean',
    ];


    public function dispute()
    {
        return $this->belongsTo(
            TransactionDispute::class,
            'transaction_dispute_id'
        );
    }


    public function transaction()
    {
        return $this->belongsTo(
            SecureTransaction::class,
            'secure_transaction_id'
        );
    }


    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Private participant conversations
    |--------------------------------------------------------------------------
    |
    | Buyer messages belong to the buyer/admin thread. Seller messages belong
    | to the seller/admin thread. This sender-role check also protects legacy
    | messages that were previously stored with visibility="all".
    |
    */

    public function scopeVisibleToRole(
        Builder $query,
        string $role
    ): Builder {

        if ($role === self::ROLE_ADMIN) {
            return $query;
        }


        if ($role === self::ROLE_BUYER) {

            return $query
                ->where(
                    'sender_role',
                    '!=',
                    self::ROLE_SELLER
                )
                ->whereIn(
                    'visibility',
                    [
                        self::VISIBILITY_ALL,
                        self::VISIBILITY_BUYER,
                    ]
                );
        }


        if ($role === self::ROLE_SELLER) {

            return $query
                ->where(
                    'sender_role',
                    '!=',
                    self::ROLE_BUYER
                )
                ->whereIn(
                    'visibility',
                    [
                        self::VISIBILITY_ALL,
                        self::VISIBILITY_SELLER,
                    ]
                );
        }


        return $query->whereRaw('1 = 0');
    }


    public function isVisibleToRole(
        string $role
    ): bool {

        if ($role === self::ROLE_ADMIN) {
            return true;
        }


        if (
            $role === self::ROLE_BUYER
            &&
            $this->sender_role !== self::ROLE_SELLER
        ) {

            return in_array(
                $this->visibility,
                [
                    self::VISIBILITY_ALL,
                    self::VISIBILITY_BUYER,
                ],
                true
            );
        }


        if (
            $role === self::ROLE_SELLER
            &&
            $this->sender_role !== self::ROLE_BUYER
        ) {

            return in_array(
                $this->visibility,
                [
                    self::VISIBILITY_ALL,
                    self::VISIBILITY_SELLER,
                ],
                true
            );
        }


        return false;
    }


    public static function participantVisibility(
        string $senderRole
    ): string {

        return match ($senderRole) {
            self::ROLE_BUYER =>
                self::VISIBILITY_BUYER,

            self::ROLE_SELLER =>
                self::VISIBILITY_SELLER,

            default =>
                self::VISIBILITY_INTERNAL,
        };
    }
}
