<?php

namespace App\Events;

use App\Models\SupportChatMessage;
use App\Models\SupportChatSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;


class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    public array $message;


    protected string $sessionUuid;



    public function __construct(
        SupportChatSession $session,
        SupportChatMessage $message
    ) {

        $this->sessionUuid =
            $session->uuid;


        $this->message =
            $message->payload();

    }



    /*
    |--------------------------------------------------------------------------
    | Private Conversation Channel
    |--------------------------------------------------------------------------
    */

    public function broadcastOn()
    {
        return new PrivateChannel(
            'support.session.'
            .
            $this->sessionUuid
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Event Name
    |--------------------------------------------------------------------------
    */

    public function broadcastAs()
    {
        return 'support.message';
    }



    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    */

    public function broadcastWith()
    {
        return [
            'message' =>
                $this->message,
        ];
    }
}