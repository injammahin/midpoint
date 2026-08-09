<?php

namespace App\Http\Controllers\Admin;

use App\Events\SupportAgentInboxUpdated;
use App\Events\SupportMessageSent;
use App\Events\SupportSessionUpdated;
use App\Http\Controllers\Controller;
use App\Models\SupportAgentProfile;
use App\Models\SupportChatSession;
use App\Services\Support\SupportQueueService;
use Illuminate\Http\Request;

class LiveSupportController extends Controller
{
    public function index(
        Request $request
    ) {

        $profile =
            SupportAgentProfile::firstOrCreate(
                [
                    'user_id' =>
                        $request->user()->id,
                ],
                [
                    'is_enabled' =>
                        true,

                    'is_accepting_chats' =>
                        false,

                    'max_active_chats' =>
                        3,
                ]
            );


        if (
            $profile->is_enabled
        ) {

            $profile->update(
                [
                    'last_seen_at' =>
                        now(),
                ]
            );

        }


        return view(
            'admin.live-support.index',
            [
                'profile' =>
                    $profile,

                'waitingCount' =>
                    SupportChatSession::where(
                        'status',
                        'waiting'
                    )->count(),

                'activeCount' =>
                    SupportChatSession::where(
                        'agent_id',
                        $request->user()->id
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->count(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Agent Feed
    |--------------------------------------------------------------------------
    */

    public function feed(
        Request $request
    ) {

        $waiting =
            SupportChatSession::query()

                ->with('user')

                ->where(
                    'status',
                    'waiting'
                )

                ->orderBy(
                    'queue_position'
                )

                ->get()

                ->map(
                    fn ($session) =>
                        $session->payload()
                );


        $active =
            SupportChatSession::query()

                ->with([
                    'user',
                    'agent',
                ])

                ->where(
                    'status',
                    'active'
                )

                ->where(
                    'agent_id',
                    $request->user()->id
                )

                ->orderByDesc(
                    'last_message_at'
                )

                ->get()

                ->map(
                    fn ($session) =>
                        $session->payload()
                );


        return response()->json(
            [
                'waiting' =>
                    $waiting,

                'active' =>
                    $active,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Heartbeat
    |--------------------------------------------------------------------------
    */

    public function heartbeat(
        Request $request,
        SupportQueueService $queue
    ) {

        $profile =
            $request
                ->user()
                ->supportAgentProfile;


        abort_unless(
            $profile
            &&
            $profile->is_enabled,
            403
        );


        $profile->update(
            [
                'last_seen_at' =>
                    now(),
            ]
        );


        if (
            $profile->is_accepting_chats
        ) {

            $queue
                ->assignWaitingSessions();

        }


        return response()->json(
            [
                'ok' =>
                    true,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Available / Unavailable
    |--------------------------------------------------------------------------
    */

    public function availability(
        Request $request,
        SupportQueueService $queue
    ) {

        $validated =
            $request->validate(
                [
                    'available' =>
                        [
                            'required',
                            'boolean',
                        ],
                ]
            );


        $profile =
            $request
                ->user()
                ->supportAgentProfile;


        abort_unless(
            $profile
            &&
            $profile->is_enabled,
            403
        );


        $profile->update(
            [
                'is_accepting_chats' =>
                    $validated['available'],

                'last_seen_at' =>
                    now(),
            ]
        );


        if (
            $profile->is_accepting_chats
        ) {

            $queue
                ->assignWaitingSessions();

        }


        return response()->json(
            [
                'available' =>
                    $profile
                        ->is_accepting_chats,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Manually Claim Waiting Customer
    |--------------------------------------------------------------------------
    */

public function claim(
    \App\Models\SupportChatSession $session
)
{
    $user =
        auth()->user();


    /*
    |--------------------------------------------------------------------------
    | Support Agent Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        $user
            ->supportAgentProfile;


    if (
        !$profile
        ||
        !$profile->is_enabled
    ) {

        return response()->json(
            [
                'message' =>
                    'You are not enabled as a support agent.',
            ],
            403
        );

    }


    try {

        $claimedSession =
            \Illuminate\Support\Facades\DB::transaction(
                function () use (
                    $session,
                    $user,
                    $profile
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Conversation
                    |--------------------------------------------------------------------------
                    */

                    $lockedSession =
                        \App\Models\SupportChatSession::query()

                            ->where(
                                'id',
                                $session->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();



                    /*
                    |--------------------------------------------------------------------------
                    | Already Assigned To Current Agent
                    |--------------------------------------------------------------------------
                    |
                    | Make claim idempotent.
                    |
                    | If the browser accidentally submits twice, don't throw 409.
                    |
                    */

                    if (
                        $lockedSession->status
                        ===
                        'active'
                        &&
                        (int) $lockedSession->agent_id
                        ===
                        (int) $user->id
                    ) {

                        return $lockedSession;

                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Assigned To Another Agent
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedSession->status
                        ===
                        'active'
                        &&
                        $lockedSession->agent_id
                    ) {

                        abort(
                            409,
                            'This conversation has already been accepted by another support agent.'
                        );

                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Must Still Be Waiting
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedSession->status
                        !==
                        'waiting'
                    ) {

                        abort(
                            409,
                            'This conversation is no longer waiting for an agent.'
                        );

                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Capacity Check
                    |--------------------------------------------------------------------------
                    */

                    $activeCount =
                        \App\Models\SupportChatSession::query()

                            ->where(
                                'agent_id',
                                $user->id
                            )

                            ->where(
                                'status',
                                'active'
                            )

                            ->count();


                    if (
                        $activeCount
                        >=
                        (int) $profile->max_active_chats
                    ) {

                        abort(
                            409,
                            'You have reached your maximum concurrent chat limit.'
                        );

                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Manual Claim
                    |--------------------------------------------------------------------------
                    |
                    | Important:
                    |
                    | We DO NOT reject the manual "Accept chat" action just because
                    | is_accepting_chats is false.
                    |
                    | is_accepting_chats controls automatic queue assignment.
                    |
                    | An enabled agent may still manually accept a conversation.
                    |
                    */

                    $lockedSession->forceFill(
                        [
                            'agent_id' =>
                                $user->id,

                            'status' =>
                                'active',

                            'queue_position' =>
                                null,

                            'assigned_at' =>
                                now(),

                            'last_message_at' =>
                                now(),
                        ]
                    )->save();


                    return $lockedSession;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $claimedSession->load([
            'user',
            'agent',
        ]);



        /*
        |--------------------------------------------------------------------------
        | System Message
        |--------------------------------------------------------------------------
        */

        $systemMessage =
            \App\Models\SupportChatMessage::create(
                [
                    'support_chat_session_id' =>
                        $claimedSession->id,

                    'sender_id' =>
                        null,

                    'type' =>
                        'system',

                    'body' =>
                        $user->name
                        .
                        ' joined the conversation.',
                ]
            );


        $systemMessage->load([
            'sender',
            'attachments',
        ]);



        /*
        |--------------------------------------------------------------------------
        | Broadcast System Message
        |--------------------------------------------------------------------------
        */

        broadcast(
            new \App\Events\SupportMessageSent(
                $claimedSession,
                $systemMessage
            )
        );



        /*
        |--------------------------------------------------------------------------
        | Broadcast Session State
        |--------------------------------------------------------------------------
        */

        broadcast(
            new \App\Events\SupportSessionUpdated(
                $claimedSession
            )
        );



        /*
        |--------------------------------------------------------------------------
        | Refresh Agent Inbox
        |--------------------------------------------------------------------------
        */

        broadcast(
            new \App\Events\SupportAgentInboxUpdated(
                'claimed',
                $claimedSession
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Recalculate Remaining Queue
        |--------------------------------------------------------------------------
        */

        app(
            \App\Services\Support\SupportQueueService::class
        )->recalculateQueue();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Conversation accepted successfully.',

            'session' =>
                $claimedSession->payload(),
        ]);

    } catch (
        \Symfony\Component\HttpKernel\Exception\HttpException $exception
    ) {

        return response()->json(
            [
                'message' =>
                    $exception->getMessage(),
            ],
            $exception->getStatusCode()
        );

    } catch (
        \Throwable $exception
    ) {

        report(
            $exception
        );


        return response()->json(
            [
                'message' =>
                    'Unable to accept this conversation.',
            ],
            500
        );

    }
}

    /*
    |--------------------------------------------------------------------------
    | Resolve Conversation
    |--------------------------------------------------------------------------
    */

public function resolve(
    Request $request,
    SupportChatSession $session,
    SupportQueueService $queue
) {

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    $validated =
        $request->validate([
            'resolution_code' => [
                'required',
                'string',
                'max:100',
            ],

            'resolution_note' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);


    $user =
        $request->user();


    $newlyResolved =
        false;


    $systemMessage =
        null;


    try {

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        $resolvedSession =
            \Illuminate\Support\Facades\DB::transaction(
                function () use (
                    $session,
                    $user,
                    $validated,
                    &$newlyResolved,
                    &$systemMessage
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

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Already Resolved
                    |--------------------------------------------------------------------------
                    |
                    | Important:
                    |
                    | If the browser sends Resolve twice, or the first response
                    | was lost, return success rather than 422.
                    |
                    */

                    if (
                        in_array(
                            $lockedSession->status,
                            [
                                'resolved',
                                'closed',
                            ],
                            true
                        )
                        &&
                        (int) $lockedSession->agent_id
                        ===
                        (int) $user->id
                    ) {

                        return $lockedSession;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Wrong Agent
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $lockedSession->agent_id
                        !==
                        (int) $user->id
                    ) {

                        abort(
                            403,
                            'This conversation is not assigned to you.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Must Be Active
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedSession->status
                        !==
                        'active'
                    ) {

                        abort(
                            409,
                            'Only an active conversation can be resolved.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Resolve
                    |--------------------------------------------------------------------------
                    */

                    $lockedSession->update([
                        'status' =>
                            'resolved',

                        'resolution_code' =>
                            $validated[
                                'resolution_code'
                            ],

                        'resolution_note' =>
                            $validated[
                                'resolution_note'
                            ]
                            ?? null,

                        'resolved_at' =>
                            now(),

                        'last_message_at' =>
                            now(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Create Resolution System Message
                    |--------------------------------------------------------------------------
                    |
                    | Store it in the SAME transaction.
                    |
                    */

                    $systemMessage =
                        \App\Models\SupportChatMessage::create([
                            'support_chat_session_id' =>
                                $lockedSession->id,

                            'sender_id' =>
                                null,

                            'type' =>
                                'system',

                            'body' =>
                                'This support conversation has been marked as resolved by '
                                .
                                $user->name
                                .
                                '. Please rate your support experience.',
                        ]);


                    $newlyResolved =
                        true;


                    return $lockedSession;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Refresh Session
        |--------------------------------------------------------------------------
        */

        $resolvedSession->refresh();


        $resolvedSession->load([
            'user',
            'agent',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Broadcast Resolution Message
        |--------------------------------------------------------------------------
        */

        if (
            $newlyResolved
            &&
            $systemMessage
        ) {

            $systemMessage->load([
                'sender',
                'attachments',
                'session',
            ]);


            try {

                broadcast(
                    new \App\Events\SupportMessageSent(
                        $resolvedSession,
                        $systemMessage
                    )
                );

            } catch (\Throwable $exception) {

                report(
                    $exception
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Broadcast Session State
        |--------------------------------------------------------------------------
        */

        try {

            broadcast(
                new \App\Events\SupportSessionUpdated(
                    $resolvedSession
                )
            );


            broadcast(
                new \App\Events\SupportAgentInboxUpdated(
                    'resolved',
                    $resolvedSession
                )
            );

        } catch (\Throwable $exception) {

            report(
                $exception
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Free Agent Capacity
        |--------------------------------------------------------------------------
        */

        $queue
            ->assignWaitingSessions();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'already_resolved' =>
                !$newlyResolved,

            'message' =>
                $newlyResolved
                    ? 'Conversation resolved.'
                    : 'Conversation was already resolved.',

            'session' =>
                $resolvedSession->payload(),
        ]);


    } catch (
        \Symfony\Component\HttpKernel\Exception\HttpException $exception
    ) {

        return response()->json(
            [
                'message' =>
                    $exception->getMessage()
                    ?:
                    'Unable to resolve this conversation.',
            ],
            $exception->getStatusCode()
        );


    } catch (\Throwable $exception) {

        report(
            $exception
        );


        return response()->json(
            [
                'message' =>
                    'Unable to resolve this conversation.',
            ],
            500
        );

    }
}
}