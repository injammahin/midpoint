<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerProduct extends Model
{
    protected $fillable = [

        'user_id',

        'name',

        'slug',

        'price',

        'description',

        'image',

        'is_active',

    ];


    protected $casts = [

        'price' =>
            'decimal:2',

        'is_active' =>
            'boolean',

    ];


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}