<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChatBlackout extends Model
{
    protected $fillable = [

        'starts_at',
        'ends_at',
        'reason',
        'is_active',
        'created_by',

    ];


    protected $casts = [

        'starts_at' =>
            'datetime',

        'ends_at' =>
            'datetime',

        'is_active' =>
            'boolean',

    ];
}