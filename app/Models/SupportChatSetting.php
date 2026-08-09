<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChatSetting extends Model
{
    protected $fillable = [

        'enabled',
        'timezone',
        'active_days',
        'opens_at',
        'closes_at',
        'welcome_message',
        'offline_message',
        'queue_message',
        'updated_by',

    ];


    protected $casts = [

        'enabled' =>
            'boolean',

        'active_days' =>
            'array',

    ];


    public static function current(): self
    {
        return static::firstOrCreate(
            [
                'id' => 1,
            ],
            [
                'enabled' =>
                    true,

                'timezone' =>
                    config(
                        'support.timezone',
                        'Africa/Lagos'
                    ),

                /*
                 * Monday - Saturday
                 */
                'active_days' =>
                    [
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                    ],

                'opens_at' =>
                    '08:00:00',

                'closes_at' =>
                    '20:00:00',

                'welcome_message' =>
                    'Welcome to MidPoint Live Support. Please wait while we connect you with a support specialist.',

                'offline_message' =>
                    'Live Support is currently unavailable. Please send us an email or return during our support hours.',

                'queue_message' =>
                    'All of our support specialists are currently assisting other customers. You have been added to the queue.',

            ]
        );
    }
}