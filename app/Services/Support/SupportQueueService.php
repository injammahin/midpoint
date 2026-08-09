<?php

namespace App\Services\Support;

use App\Events\SupportAgentInboxUpdated;
use App\Events\SupportMessageSent;
use App\Events\SupportSessionUpdated;
use App\Models\SupportAgentProfile;
use App\Models\SupportChatMessage;
use App\Models\SupportChatSession;
use App\Models\SupportChatSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportQueueService
{
    /*
    |--------------------------------------------------------------------------
    | Create / Retrieve Customer Session
    |--------------------------------------------------------------------------
    */

    public function createOrGet(
        User $user,
        ?string $topic = null
    ): SupportChatSession {

        $existing =
            SupportChatSession::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->whereIn(
                    'status',
                    [
                        'waiting',
                        'active',
                    ]
                )

                ->latest('id')

                ->first();


        if ($existing) {

            return $existing;

        }


        $session =
            DB::transaction(
                function () use (
                    $user,
                    $topic
                ) {

                    $position =
                        SupportChatSession::query()

                            ->where(
                                'status',
                                'waiting'
                            )

                            ->count()
                            + 1;


                    return SupportChatSession::create(
                        [
                            'uuid' =>
                                (string) Str::uuid(),

                            'user_id' =>
                                $user->id,

                            'status' =>
                                'waiting',

                            'topic' =>
                                $topic,

                            'queue_position' =>
                                $position,

                            'queue_entered_at' =>
                                now(),

                            'last_message_at' =>
                                now(),
                        ]
                    );

                }
            );


        $settings =
            SupportChatSetting::current();


        /*
        |--------------------------------------------------------------------------
        | Personalized Welcome
        |--------------------------------------------------------------------------
        */

        $welcome =
            'Hi '
            .
            $user->name
            .
            ' 👋 '
            .
            (
                $settings->welcome_message
                ?: 'Welcome to MidPoint Live Support. Please wait while we connect you with an agent.'
            );


        $this->systemMessage(
            $session,
            $welcome
        );


        /*
        |--------------------------------------------------------------------------
        | Alert Agent Console
        |--------------------------------------------------------------------------
        */

        broadcast(
            new SupportAgentInboxUpdated(
                'new_request',
                $session
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Auto Assign
        |--------------------------------------------------------------------------
        */

        $assigned =
            $this->tryAssign(
                $session
            );


        if (!$assigned) {

            $session->refresh();


            $this->systemMessage(
                $session,
                (
                    $settings->queue_message
                    ?: 'All of our agents are currently assisting other customers.'
                )
                .
                ' You are #'
                .
                $session->queue_position
                .
                ' in the queue.'
            );

        }


        return $session->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Automatically Pick Agent
    |--------------------------------------------------------------------------
    */

    public function tryAssign(
        SupportChatSession $session
    ): bool {

        if (
            $session->status
            !== 'waiting'
        ) {

            return false;

        }


        $onlineSince =
            now()->subSeconds(
                config(
                    'support.agent_online_seconds',
                    90
                )
            );


        $agents =
            SupportAgentProfile::query()

                ->where(
                    'is_enabled',
                    true
                )

                ->where(
                    'is_accepting_chats',
                    true
                )

                ->where(
                    'last_seen_at',
                    '>=',
                    $onlineSince
                )

                ->withCount(
                    [
                        'activeSessions',
                    ]
                )

                ->orderBy(
                    'active_sessions_count'
                )

                ->orderBy(
                    'last_seen_at',
                    'desc'
                )

                ->get();


        foreach (
            $agents as $agent
        ) {

            if (
                $agent
                    ->active_sessions_count
                >=
                $agent
                    ->max_active_chats
            ) {

                continue;

            }


            if (
                $this->assignSpecific(
                    $session,
                    $agent->user
                )
            ) {

                return true;

            }

        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Assign Specific Agent
    |--------------------------------------------------------------------------
    */

    public function assignSpecific(
        SupportChatSession $session,
        User $agent
    ): bool {

        $assigned =
            DB::transaction(
                function () use (
                    $session,
                    $agent
                ) {

                    $lockedSession =
                        SupportChatSession::query()

                            ->whereKey(
                                $session->id
                            )

                            ->lockForUpdate()

                            ->first();


                    if (
                        !$lockedSession
                        ||
                        $lockedSession->status
                        !== 'waiting'
                    ) {

                        return false;

                    }


                    $profile =
                        SupportAgentProfile::query()

                            ->where(
                                'user_id',
                                $agent->id
                            )

                            ->lockForUpdate()

                            ->first();


                    if (
                        !$profile
                        ||
                        !$profile->is_enabled
                        ||
                        !$profile->is_accepting_chats
                    ) {

                        return false;

                    }


                    $activeChats =
                        SupportChatSession::query()

                            ->where(
                                'agent_id',
                                $agent->id
                            )

                            ->where(
                                'status',
                                'active'
                            )

                            ->count();


                    if (
                        $activeChats
                        >=
                        $profile->max_active_chats
                    ) {

                        return false;

                    }


                    $lockedSession->update(
                        [
                            'agent_id' =>
                                $agent->id,

                            'status' =>
                                'active',

                            'assigned_at' =>
                                now(),

                            'queue_position' =>
                                null,
                        ]
                    );


                    return true;

                }
            );


        if (!$assigned) {

            return false;

        }


        $session->refresh();


        $this->systemMessage(
            $session,
            $agent->name
            .
            ' has joined the conversation. How can we help you today?'
        );


        broadcast(
            new SupportSessionUpdated(
                $session
            )
        );


        broadcast(
            new SupportAgentInboxUpdated(
                'assigned',
                $session
            )
        );


        $this->recalculateQueue();


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Assign Waiting Customers
    |--------------------------------------------------------------------------
    */

    public function assignWaitingSessions()
    {
        /*
         * Safety limit prevents accidental endless looping.
         */
        for (
            $i = 0;
            $i < 100;
            $i++
        ) {

            $session =
                SupportChatSession::query()

                    ->where(
                        'status',
                        'waiting'
                    )

                    ->orderBy(
                        'queue_entered_at'
                    )

                    ->orderBy(
                        'id'
                    )

                    ->first();


            if (!$session) {

                return;

            }


            if (
                !$this->tryAssign(
                    $session
                )
            ) {

                return;

            }

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Queue Position Updates
    |--------------------------------------------------------------------------
    */

    public function recalculateQueue()
    {
        $sessions =
            SupportChatSession::query()

                ->where(
                    'status',
                    'waiting'
                )

                ->orderBy(
                    'queue_entered_at'
                )

                ->orderBy(
                    'id'
                )

                ->get();


        foreach (
            $sessions as $index => $session
        ) {

            $newPosition =
                $index + 1;


            if (
                (int) $session->queue_position
                ===
                $newPosition
            ) {

                continue;

            }


            $session->update(
                [
                    'queue_position' =>
                        $newPosition,
                ]
            );


            $session->refresh();


            $this->systemMessage(
                $session,
                'Queue update: you are now #'
                .
                $newPosition
                .
                ' in the support queue.'
            );


            broadcast(
                new SupportSessionUpdated(
                    $session
                )
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | System Message
    |--------------------------------------------------------------------------
    */

    public function systemMessage(
        SupportChatSession $session,
        string $message
    ): SupportChatMessage {

        $chatMessage =
            SupportChatMessage::create(
                [
                    'support_chat_session_id' =>
                        $session->id,

                    'sender_id' =>
                        null,

                    'type' =>
                        'system',

                    'body' =>
                        $message,
                ]
            );


        $session->update(
            [
                'last_message_at' =>
                    now(),
            ]
        );


        broadcast(
            new SupportMessageSent(
                $chatMessage
            )
        );


        return $chatMessage;
    }
}