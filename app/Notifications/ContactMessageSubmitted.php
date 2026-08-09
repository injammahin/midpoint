<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContactMessageSubmitted extends Notification
{
    use Queueable;


    protected ContactMessage $contactMessage;


    public function __construct(
        ContactMessage $contactMessage
    ) {
        $this->contactMessage =
            $contactMessage;
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */

    public function via($notifiable)
    {
        return [
            'database',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Database Notification
    |--------------------------------------------------------------------------
    */

    public function toDatabase($notifiable)
    {
        return [

            'type' =>
                'contact_message',

            'title' =>
                'New contact message',

            'message' =>
                $this->contactMessage->name .
                ' sent a ' .
                strtolower(
                    $this->contactMessage->topic_label
                ) .
                ' inquiry.',

            'contact_message_id' =>
                $this->contactMessage->id,

            'reference' =>
                $this->contactMessage->reference,

            'name' =>
                $this->contactMessage->name,

            'email' =>
                $this->contactMessage->email,

            'icon' =>
                'fa-envelope',

            'url' =>
                route(
                    'admin.support-inquiries.contacts.show',
                    $this->contactMessage
                ),

        ];
    }


    public function toArray($notifiable)
    {
        return $this->toDatabase(
            $notifiable
        );
    }
}