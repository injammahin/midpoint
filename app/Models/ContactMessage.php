<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;


    protected $fillable = [

        'name',

        'email',

        'topic',

        'message',

        'status',

        'read_at',

        'read_by',

        'ip_address',

        'user_agent',

    ];


    protected $casts = [

        'read_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function reader()
    {
        return $this->belongsTo(
            User::class,
            'read_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getReferenceAttribute(): string
    {
        return 'MP-CT-' .
            str_pad(
                $this->id,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    public function getTopicLabelAttribute(): string
    {
        return match ($this->topic) {

            'transaction_help' =>
                'Transaction Help',

            'delivery_dispatch' =>
                'Delivery & Dispatch',

            'business_verification' =>
                'Business Verification',

            'partnership' =>
                'Partnership',

            'other' =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $this->topic
                    )
                ),
        };
    }


    public function isUnread(): bool
    {
        return is_null(
            $this->read_at
        );
    }
}