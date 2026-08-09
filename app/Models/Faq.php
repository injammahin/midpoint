<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [

        'question',

        'answer',

        'sort_order',

        'is_active',

        'show_on_home',

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'is_active' => 'boolean',

        'show_on_home' => 'boolean',

        'sort_order' => 'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }


    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }


    public function scopeHomepage($query)
    {
        return $query
            ->where(
                'show_on_home',
                true
            );
    }
}