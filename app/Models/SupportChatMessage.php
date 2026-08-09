<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChatMessage extends Model
{
    protected $fillable = [

        'support_chat_session_id',
        'sender_id',
        'type',
        'body',
        'read_at',

    ];


    protected $casts = [

        'read_at' =>
            'datetime',

    ];


    public function session()
    {
        return $this->belongsTo(
            SupportChatSession::class,
            'support_chat_session_id'
        );
    }


    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }


    public function attachments()
    {
        return $this->hasMany(
            SupportChatAttachment::class
        );
    }


    public function payload(): array
    {
        $this->loadMissing([
            'sender',
            'attachments',
            'session',
        ]);


        if (!$this->sender_id) {

            $senderType =
                'system';

        } elseif (
            $this->sender_id
            ===
            $this->session->user_id
        ) {

            $senderType =
                'user';

        } else {

            $senderType =
                'agent';

        }


        return [

            'id' =>
                $this->id,

            'type' =>
                $this->type,

            'body' =>
                $this->body,

            'sender_type' =>
                $senderType,

            'sender' =>
                $this->sender
                    ? [
                        'id' =>
                            $this->sender->id,

                        'name' =>
                            $this->sender->name,
                    ]
                    : null,

            'created_at' =>
                $this
                    ->created_at
                    ->toIso8601String(),

            'attachments' =>
                $this
                    ->attachments
                    ->map(
                        function (
                            $attachment
                        ) {

                            return [

                                'id' =>
                                    $attachment->id,

                                'kind' =>
                                    $attachment->kind,

                                'name' =>
                                    $attachment->original_name,

                                'mime' =>
                                    $attachment->mime_type,

                                'size' =>
                                    $attachment->size,

                                'url' =>
                                    route(
                                        'support.chat.attachments.show',
                                        $attachment
                                    ),

                            ];

                        }
                    )
                    ->values()
                    ->all(),

        ];
    }
}