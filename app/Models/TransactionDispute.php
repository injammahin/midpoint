<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDispute extends Model
{
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

    protected $casts = [
        'evidence' => 'array',
        'opened_at' => 'datetime',
        'resolved_at' => 'datetime',
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
}