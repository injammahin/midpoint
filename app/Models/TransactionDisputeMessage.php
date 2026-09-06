<?php

namespace App\Models;

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
}
