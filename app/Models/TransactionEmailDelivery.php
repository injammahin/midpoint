<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionEmailDelivery extends Model
{
    protected $fillable = [
        'secure_transaction_id',
        'event_key',
        'audience',
        'email',
        'subject',
        'attempts',
        'last_attempt_at',
        'sent_at',
        'failed_at',
        'last_error',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(
            SecureTransaction::class,
            'secure_transaction_id'
        );
    }

    public function isSent(): bool
    {
        return !is_null(
            $this->sent_at
        );
    }
}