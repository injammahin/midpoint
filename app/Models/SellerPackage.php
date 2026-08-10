<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_period',
        'product_limit',
        'description',
        'features',
        'theme',
        'is_popular',
        'is_active',
        'sort_order',
    ];


    protected $casts = [

        'price' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'features' =>
            'array',

        'is_popular' =>
            'boolean',

        'is_active' =>
            'boolean',

        'sort_order' =>
            'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */

    public function subscriptions()
    {
        return $this->hasMany(
            SellerSubscription::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        $query
    ) {
        return $query->where(
            'is_active',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Ordered Scope
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered(
        $query
    ) {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}