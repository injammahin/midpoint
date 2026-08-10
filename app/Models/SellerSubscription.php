<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    protected $fillable = [

        'user_id',

        'seller_package_id',

        'package_name',

        'package_price',

        'billing_period',

        'product_limit',

        'status',

        'payment_reference',

        'starts_at',

        'expires_at',

        'cancelled_at',

    ];


    protected $casts = [

        'package_price' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'starts_at' =>
            'datetime',

        'expires_at' =>
            'datetime',

        'cancelled_at' =>
            'datetime',

    ];


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function package()
    {
        return $this->belongsTo(
            SellerPackage::class,
            'seller_package_id'
        );
    }


    public function scopeActive(
        $query
    ) {
        return $query
            ->where(
                'status',
                'active'
            )
            ->where(
                function ($query) {

                    $query
                        ->whereNull(
                            'expires_at'
                        )
                        ->orWhere(
                            'expires_at',
                            '>',
                            now()
                        );
                }
            );
    }
}