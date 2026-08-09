<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportChatAttachment extends Model
{
    protected $fillable = [

        'support_chat_message_id',
        'kind',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',

    ];


    public function message()
    {
        return $this->belongsTo(
            SupportChatMessage::class,
            'support_chat_message_id'
        );
    }
}