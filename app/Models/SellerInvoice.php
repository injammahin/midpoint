<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerInvoice extends Model
{
    protected $fillable = [

        'invoice_number',

        'seller_application_id',

        'user_id',

        'amount',

        'currency',

        'status',

        'payment_method',

        'payment_reference',

        'issued_at',

        'due_at',

        'paid_at',

    ];


    protected $casts = [

        'amount' =>
            'decimal:2',

        'issued_at' =>
            'datetime',

        'due_at' =>
            'datetime',

        'paid_at' =>
            'datetime',

    ];


    public static function generateInvoiceNumber(): string
    {
        do {

            $number =
                'MP-INV-'
                .
                now()->format(
                    'Ym'
                )
                .
                '-'
                .
                Str::upper(
                    Str::random(7)
                );

        } while (
            static::where(
                'invoice_number',
                $number
            )->exists()
        );


        return $number;
    }


    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}