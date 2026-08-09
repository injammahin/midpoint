<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\SupportChatSession;


/*
|--------------------------------------------------------------------------
| Live Support Conversation
|--------------------------------------------------------------------------
|
| Customer:
|     Can access their own conversation.
|
| Support Agent:
|     Any ENABLED admin support agent may subscribe.
|
| This is important because an agent needs to receive realtime updates
| even while the conversation is still waiting in the queue.
|
*/

Broadcast::channel(
    'support.session.{uuid}',
    function ($user, $uuid) {

        $session =
            SupportChatSession::query()

                ->where(
                    'uuid',
                    $uuid
                )

                ->first();


        if (!$session) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        if (
            (int) $session->user_id
            ===
            (int) $user->id
        ) {

            return true;

        }


        /*
        |--------------------------------------------------------------------------
        | Administrator / Support Agent
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === 'admin'
        ) {

            $profile =
                $user
                    ->supportAgentProfile;


            return
                $profile
                &&
                (bool) $profile->is_enabled;

        }


        return false;
    }
);


/*
|--------------------------------------------------------------------------
| Support Agent Global Inbox
|--------------------------------------------------------------------------
*/

Broadcast::channel(
    'support.agents',
    function ($user) {

        if (
            $user->role !== 'admin'
        ) {

            return false;

        }


        $profile =
            $user
                ->supportAgentProfile;


        return
            $profile
            &&
            (bool) $profile->is_enabled;
    }
);