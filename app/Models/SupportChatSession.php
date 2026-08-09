<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChatSession extends Model
{
    protected $fillable = [

        'uuid',
        'user_id',
        'agent_id',
        'status',
        'topic',
        'queue_position',
        'queue_entered_at',
        'assigned_at',
        'resolved_at',
        'closed_at',
        'last_message_at',
        'resolution_code',
        'resolution_note',
        'rating',
        'review',
        'rated_at',

    ];


    protected $casts = [

        'queue_entered_at' =>
            'datetime',

        'assigned_at' =>
            'datetime',

        'resolved_at' =>
            'datetime',

        'closed_at' =>
            'datetime',

        'last_message_at' =>
            'datetime',

        'rated_at' =>
            'datetime',

    ];


    public function getRouteKeyName()
    {
        return 'uuid';
    }


    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    public function agent()
    {
        return $this->belongsTo(
            User::class,
            'agent_id'
        );
    }


    public function messages()
    {
        return $this->hasMany(
            SupportChatMessage::class
        );
    }


    public function payload(): array
    {
        $this->loadMissing([
            'user',
            'agent',
        ]);


        return [

            'uuid' =>
                $this->uuid,

            'status' =>
                $this->status,

            'topic' =>
                $this->topic,

            'queue_position' =>
                $this->queue_position,

            'created_at' =>
                optional(
                    $this->created_at
                )->toIso8601String(),

            'assigned_at' =>
                optional(
                    $this->assigned_at
                )->toIso8601String(),

            'user' => [

                'id' =>
                    $this->user?->id,

                'name' =>
                    $this->user?->name,

                'email' =>
                    $this->user?->email,

            ],

            'agent' =>
                $this->agent
                    ? [
                        'id' =>
                            $this->agent->id,

                        'name' =>
                            $this->agent->name,
                    ]
                    : null,

            'rating' =>
                $this->rating,

            'review' =>
                $this->review,

        ];
    }
}