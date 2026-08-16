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

        'stock',

        'description',

        'image',

        'images',

        'is_active',
        
        'out_of_stock_notified_at',

    ];


    protected $casts = [

        'price' =>
            'decimal:2',

        'stock' =>
            'integer',

        'images' =>
            'array',

        'is_active' =>
            'boolean',
            
        'out_of_stock_notified_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Main Product Image
    |--------------------------------------------------------------------------
    */

    public function getMainImageAttribute(): ?string
    {
        $images =
            is_array($this->images)
                ? $this->images
                : [];


        if (
            count($images) > 0
        ) {

            return $images[0];
        }


        return $this->image;
    }


    /*
    |--------------------------------------------------------------------------
    | All Product Images
    |--------------------------------------------------------------------------
    |
    | Also supports products created before the new multiple-image system.
    |
    */

    public function getAllImagesAttribute(): array
    {
        $images =
            is_array($this->images)
                ? $this->images
                : [];


        if (
            count($images) === 0
            &&
            $this->image
        ) {

            $images[] =
                $this->image;
        }


        return array_values(
            array_unique(
                array_filter(
                    $images
                )
            )
        );
    }
}