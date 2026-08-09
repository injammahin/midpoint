<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAgentProfile extends Model
{
    protected $fillable = [

        'user_id',
        'is_enabled',
        'is_accepting_chats',
        'max_active_chats',
        'last_seen_at',

    ];


    protected $casts = [

        'is_enabled' =>
            'boolean',

        'is_accepting_chats' =>
            'boolean',

        'last_seen_at' =>
            'datetime',

    ];


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function activeSessions()
    {
        return $this
            ->hasMany(
                SupportChatSession::class,
                'agent_id',
                'user_id'
            )
            ->where(
                'status',
                'active'
            );
    }
}