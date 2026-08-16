<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeTestimonial extends Model
{
    use HasFactory;


    protected $fillable = [

        'reviewer_name',

        'reviewer_meta',

        'review_text',

        'rating',

        'avatar_initials',

        'avatar_color',

        'is_active',

        'sort_order',

        'updated_by',

    ];


    protected $casts = [

        'rating' =>
            'integer',

        'is_active' =>
            'boolean',

        'sort_order' =>
            'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Updating Admin
    |--------------------------------------------------------------------------
    */

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Only Active Testimonials
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
    | Ordered Testimonials
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered(
        $query
    ) {
        return $query
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'id'
            );
    }
}