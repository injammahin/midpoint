<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerApplicationDocument extends Model
{
    protected $fillable = [

        'seller_application_id',

        'disk',

        'path',

        'original_name',

        'mime_type',

        'size',

    ];


    public function application()
    {
        return $this->belongsTo(
            SellerApplication::class,
            'seller_application_id'
        );
    }
}