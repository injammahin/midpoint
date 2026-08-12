<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDisputeStatusHistory extends Model
{
    protected $fillable = [

        'transaction_dispute_id',

        'secure_transaction_id',

        'admin_id',

        'from_status',

        'to_status',

        'note',

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


    public function admin()
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }
}