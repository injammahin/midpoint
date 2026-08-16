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
use Throwable;

class SupportQueueService
{
    /*
    |--------------------------------------------------------------------------
    | Create / Get Customer Chat
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

            return $existing->fresh([
                'user',
                'agent',
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Create Waiting Session
        |--------------------------------------------------------------------------
        */

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


                    return SupportChatSession::create([
                        'uuid' =>
                            (string) Str::uuid(),

                        'user_id' =>
                            $user->id,

                        'status' =>
                            'waiting',

                        'topic' =>
                            $topic ?: 'Live Support',

                        'queue_position' =>
                            $position,

                        'queue_entered_at' =>
                            now(),

                        'last_message_at' =>
                            now(),
                    ]);

                }
            );


        $settings =
            SupportChatSetting::current();


        /*
        |--------------------------------------------------------------------------
        | Welcome Message
        |--------------------------------------------------------------------------
        */

        $welcomeMessage =
            'Hi '
            .
            $user->name
            .
            ' 👋 '
            .
            (
                $settings->welcome_message
                ?:
                'Welcome to Midpoint Live Support. Please wait while we connect you with a support specialist.'
            );


        $this->systemMessage(
            $session,
            $welcomeMessage
        );


        /*
        |--------------------------------------------------------------------------
        | Notify Admins
        |--------------------------------------------------------------------------
        */

        $this->safeBroadcast(
            new SupportAgentInboxUpdated(
                'new_request',
                $session
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Try Automatic Assignment
        |--------------------------------------------------------------------------
        */

        $assigned =
            $this->tryAssign(
                $session
            );


        /*
        |--------------------------------------------------------------------------
        | No Agent Available
        |--------------------------------------------------------------------------
        */

        if (!$assigned) {

            $session->refresh();


            $queueMessage =
                (
                    $settings->queue_message
                    ?:
                    'All of our support specialists are currently assisting other customers.'
                )
                .
                ' You have been added to the queue. You are #'
                .
                (
                    $session->queue_position
                    ?: 1
                )
                .
                ' in the queue.';


            $this->systemMessage(
                $session,
                $queueMessage
            );

        }


        return $session->fresh([
            'user',
            'agent',
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Try Automatic Assignment
    |--------------------------------------------------------------------------
    */

    public function tryAssign(
        SupportChatSession $session
    ): bool {

        $session->refresh();


        if (
            $session->status
            !==
            'waiting'
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

                ->with([
                    'user',
                ])

                ->withCount([
                    'activeSessions',
                ])

                ->orderBy(
                    'active_sessions_count'
                )

                ->orderByDesc(
                    'last_seen_at'
                )

                ->get();


        foreach (
            $agents as $profile
        ) {

            if (
                $profile->active_sessions_count
                >=
                $profile->max_active_chats
            ) {

                continue;

            }


            if (!$profile->user) {

                continue;

            }


            if (
                $this->assignSpecific(
                    $session,
                    $profile->user
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

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Session
                    |--------------------------------------------------------------------------
                    */

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
                        !==
                        'waiting'
                    ) {

                        return false;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Agent
                    |--------------------------------------------------------------------------
                    */

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


                    /*
                    |--------------------------------------------------------------------------
                    | Capacity
                    |--------------------------------------------------------------------------
                    */

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


                    /*
                    |--------------------------------------------------------------------------
                    | Assign
                    |--------------------------------------------------------------------------
                    */

                    $lockedSession->update([
                        'agent_id' =>
                            $agent->id,

                        'status' =>
                            'active',

                        'assigned_at' =>
                            now(),

                        'queue_position' =>
                            null,

                        'last_message_at' =>
                            now(),
                    ]);


                    return true;

                }
            );


        if (!$assigned) {

            return false;

        }


        $session->refresh();


        /*
        |--------------------------------------------------------------------------
        | Agent Joined Message
        |--------------------------------------------------------------------------
        */

        $this->systemMessage(
            $session,
            $agent->name
            .
            ' joined the conversation.'
        );


        /*
        |--------------------------------------------------------------------------
        | Customer Session Update
        |--------------------------------------------------------------------------
        */

        $this->safeBroadcast(
            new SupportSessionUpdated(
                $session
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Admin Inbox Update
        |--------------------------------------------------------------------------
        */

        $this->safeBroadcast(
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
    | Assign Waiting Sessions
    |--------------------------------------------------------------------------
    */

    public function assignWaitingSessions()
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Limit
        |--------------------------------------------------------------------------
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
    | Recalculate Queue
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


            $session->update([
                'queue_position' =>
                    $newPosition,
            ]);


            $session->refresh();


            /*
            |--------------------------------------------------------------------------
            | Customer Queue Message
            |--------------------------------------------------------------------------
            */

            $this->systemMessage(
                $session,
                'Queue update: you are now #'
                .
                $newPosition
                .
                ' in the support queue.'
            );


            /*
            |--------------------------------------------------------------------------
            | Customer State Update
            |--------------------------------------------------------------------------
            */

            $this->safeBroadcast(
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
    |
    | THIS WAS THE MAIN ERROR.
    |
    */

    public function systemMessage(
        SupportChatSession $session,
        string $message
    ): SupportChatMessage {

        $chatMessage =
            DB::transaction(
                function () use (
                    $session,
                    $message
                ) {

                    $created =
                        SupportChatMessage::create([
                            'support_chat_session_id' =>
                                $session->id,

                            'sender_id' =>
                                null,

                            'type' =>
                                'system',

                            'body' =>
                                $message,
                        ]);


                    $session->update([
                        'last_message_at' =>
                            now(),
                    ]);


                    return $created;

                }
            );


        $session->refresh();


        $chatMessage->load([
            'sender',
            'attachments',
            'session',
        ]);


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | SupportMessageSent expects:
        |
        |   SupportChatSession $session
        |   SupportChatMessage $message
        |
        */

        $this->safeBroadcast(
            new SupportMessageSent(
                $session,
                $chatMessage
            )
        );


        return $chatMessage;
    }



    /*
    |--------------------------------------------------------------------------
    | Safe Realtime Broadcast
    |--------------------------------------------------------------------------
    |
    | A temporary Pusher problem should NEVER cause:
    |
    | - Chat creation to fail
    | - Resolve to fail
    | - Database state to become inconsistent
    |
    */

    private function safeBroadcast(
        object $event
    ): void {

        try {

            broadcast(
                $event
            );

        } catch (Throwable $exception) {

            report(
                $exception
            );

        }
    }
}