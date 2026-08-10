<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SellerReview extends Model
{
    protected $fillable = [

        'seller_id',

        'buyer_id',

        'seller_product_id',

        'rating',

        'review',

        'is_published',

    ];


    protected $casts = [

        'rating' =>
            'integer',

        'is_published' =>
            'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | Seller
    |--------------------------------------------------------------------------
    */

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer
    |--------------------------------------------------------------------------
    */

    public function buyer()
    {
        return $this->belongsTo(
            User::class,
            'buyer_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(
            SellerProduct::class,
            'seller_product_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Published Scope
    |--------------------------------------------------------------------------
    */

    public function scopePublished(
        Builder $query
    ): Builder {

        return $query->where(
            'is_published',
            true
        );
    }
}