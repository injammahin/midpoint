<?php

namespace App\Events;

use App\Models\SupportChatSession;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportAgentInboxUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;


    public string $action;

    public SupportChatSession $session;


    public function __construct(
        string $action,
        SupportChatSession $session
    ) {
        $this->action =
            $action;

        $this->session =
            $session;
    }


    public function broadcastOn()
    {
        return new PrivateChannel(
            'support.agents'
        );
    }


    public function broadcastAs()
    {
        return 'support.inbox.updated';
    }


    public function broadcastWith()
    {
        return [

            'action' =>
                $this->action,

            'session' =>
                $this
                    ->session
                    ->payload(),

        ];
    }
}