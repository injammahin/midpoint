<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    protected $fillable = [

        'user_id',

        'payment_alerts',

        'dispatch_updates',

        'inspection_reminders',

        'whatsapp_notifications',

        'marketing_emails',

    ];


    protected $casts = [

        'payment_alerts' =>
            'boolean',

        'dispatch_updates' =>
            'boolean',

        'inspection_reminders' =>
            'boolean',

        'whatsapp_notifications' =>
            'boolean',

        'marketing_emails' =>
            'boolean',

    ];


    protected $attributes = [

        'payment_alerts' =>
            true,

        'dispatch_updates' =>
            true,

        'inspection_reminders' =>
            true,

        'whatsapp_notifications' =>
            true,

        'marketing_emails' =>
            false,

    ];


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}