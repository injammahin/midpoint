<?php

namespace App\Events;

use App\Models\SupportChatSession;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportSessionUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    public SupportChatSession $session;


    public function __construct(
        SupportChatSession $session
    ) {
        $this->session =
            $session;
    }


    public function broadcastOn()
    {
        return new PrivateChannel(
            'support.session.'
            .
            $this->session->uuid
        );
    }


    public function broadcastAs()
    {
        return 'support.session.updated';
    }


    public function broadcastWith()
    {
        return [

            'session' =>
                $this
                    ->session
                    ->payload(),

        ];
    }
}