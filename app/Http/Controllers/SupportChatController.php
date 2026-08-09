<?php

namespace App\Http\Controllers;

use App\Events\SupportAgentInboxUpdated;
use App\Events\SupportMessageSent;
use App\Models\SupportChatAttachment;
use App\Models\SupportChatMessage;
use App\Models\SupportChatSession;
use App\Services\Support\SupportAvailabilityService;
use App\Services\Support\SupportQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    public function status(
        Request $request,
        SupportAvailabilityService $availability
    ) {

        return response()->json(
            [
                'authenticated' =>
                    auth()->check(),

                'availability' =>
                    $availability->status(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Start
    |--------------------------------------------------------------------------
    */

    public function start(
        Request $request,
        SupportAvailabilityService $availability,
        SupportQueueService $queue
    ) {

        $supportStatus =
            $availability->status();


        if (
            !$supportStatus['available']
        ) {

            return response()->json(
                [
                    'message' =>
                        $supportStatus['message'],

                    'availability' =>
                        $supportStatus,
                ],
                422
            );

        }


        $request->validate(
            [
                'topic' =>
                    [
                        'nullable',
                        'string',
                        'max:255',
                    ],
            ]
        );


        $session =
            $queue->createOrGet(
                $request->user(),
                $request->input(
                    'topic'
                )
            );


        return $this->sessionResponse(
            $session,
            $request->user()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Load Conversation
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        SupportChatSession $session
    ) {

        $this->authorizeAccess(
            $request,
            $session
        );


        return $this->sessionResponse(
            $session,
            $request->user()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Message / Attachments
    |--------------------------------------------------------------------------
    */

public function storeMessage(
    \Illuminate\Http\Request $request,
    \App\Models\SupportChatSession $session
)
{
    $user =
        $request->user();


    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    $isCustomer =
        (int) $session->user_id
        ===
        (int) $user->id;


    $isAgent =
        $user->role === 'admin'
        &&
        (
            (int) $session->agent_id
            ===
            (int) $user->id
        );


    if (
        !$isCustomer
        &&
        !$isAgent
    ) {

        return response()->json(
            [
                'message' =>
                    'You are not authorized to send messages in this conversation.',
            ],
            403
        );

    }



    /*
    |--------------------------------------------------------------------------
    | Conversation State
    |--------------------------------------------------------------------------
    |
    | Customer may send while waiting.
    | Both may send while active.
    |
    */

    if (
        !in_array(
            $session->status,
            [
                'waiting',
                'active',
            ],
            true
        )
    ) {

        return response()->json(
            [
                'message' =>
                    'This conversation is already closed.',
            ],
            409
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $validated =
        $request->validate(
            [
                'body' => [
                    'nullable',
                    'string',
                    'max:10000',
                    'required_without:attachments',
                ],

                'attachments' => [
                    'nullable',
                    'array',
                    'max:5',
                ],

                'attachments.*' => [
                    'file',
                    'max:51200',

                    'mimes:jpg,jpeg,png,webp,pdf,txt,doc,docx,xls,xlsx,csv,zip,mp4,webm,mov',
                ],
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Create Message
    |--------------------------------------------------------------------------
    */

    $message =
        \Illuminate\Support\Facades\DB::transaction(
            function () use (
                $request,
                $session,
                $user,
                $validated
            ) {


                $message =
                    \App\Models\SupportChatMessage::create(
                        [
                            'support_chat_session_id' =>
                                $session->id,

                            'sender_id' =>
                                $user->id,

                            'type' =>
                                $request->hasFile(
                                    'attachments'
                                )
                                    ? 'attachment'
                                    : 'text',

                            'body' =>
                                $validated['body']
                                ?? null,
                        ]
                    );



                /*
                |--------------------------------------------------------------------------
                | Attachments
                |--------------------------------------------------------------------------
                */

                foreach (
                    $request->file(
                        'attachments',
                        []
                    )
                    as $file
                ) {

                    $mime =
                        $file->getMimeType();


                    $kind =
                        str_starts_with(
                            $mime,
                            'image/'
                        )
                            ? 'image'
                            :
                            (
                                str_starts_with(
                                    $mime,
                                    'video/'
                                )
                                    ? 'video'
                                    : 'file'
                            );


                    $extension =
                        $file
                            ->getClientOriginalExtension();


                    $filename =
                        (string)
                        \Illuminate\Support\Str::uuid();


                    if ($extension) {

                        $filename .=
                            '.'
                            .
                            strtolower(
                                $extension
                            );

                    }


                    $path =
                        $file->storeAs(
                            'support-chat/'
                            .
                            $session->uuid,

                            $filename,

                            'local'
                        );


                    \App\Models\SupportChatAttachment::create(
                        [
                            'support_chat_message_id' =>
                                $message->id,

                            'kind' =>
                                $kind,

                            'disk' =>
                                'local',

                            'path' =>
                                $path,

                            'original_name' =>
                                $file
                                    ->getClientOriginalName(),

                            'mime_type' =>
                                $mime,

                            'size' =>
                                $file->getSize(),
                        ]
                    );

                }



                /*
                |--------------------------------------------------------------------------
                | Update Session
                |--------------------------------------------------------------------------
                */

                $session->forceFill(
                    [
                        'last_message_at' =>
                            now(),
                    ]
                )->save();


                return $message;
            }
        );


    /*
    |--------------------------------------------------------------------------
    | Load Message
    |--------------------------------------------------------------------------
    */

    $message->load([
        'sender',
        'attachments',
    ]);


    /*
    |--------------------------------------------------------------------------
    | CRITICAL: Broadcast EVERY sender
    |--------------------------------------------------------------------------
    |
    | Do not wrap this only inside:
    |
    |     if ($isAgent)
    |
    | Customer messages must be broadcast too.
    |
    */

    broadcast(
        new \App\Events\SupportMessageSent(
            $session,
            $message
        )
    )->toOthers();


    /*
    |--------------------------------------------------------------------------
    | Agent Inbox Update
    |--------------------------------------------------------------------------
    |
    | Useful when a customer sends a message.
    |
    */

    if ($isCustomer) {

        broadcast(
            new \App\Events\SupportAgentInboxUpdated(
                'message',
                $session
            )
        );

    }


    return response()->json([
        'success' =>
            true,

        'message' =>
            $message->payload(),
    ]);
}


    /*
    |--------------------------------------------------------------------------
    | Secure Attachment Viewer
    |--------------------------------------------------------------------------
    */

    public function attachment(
        Request $request,
        SupportChatAttachment $attachment
    ) {

        $attachment->load(
            'message.session'
        );


        $session =
            $attachment
                ->message
                ->session;


        $this->authorizeAccess(
            $request,
            $session
        );


        abort_unless(
            Storage::disk(
                $attachment->disk
            )->exists(
                $attachment->path
            ),
            404
        );


        return Storage::disk(
            $attachment->disk
        )->response(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' =>
                    $attachment->mime_type
                    ?: 'application/octet-stream',

                'Content-Disposition' =>
                    'inline; filename="'
                    .
                    addslashes(
                        $attachment->original_name
                    )
                    .
                    '"',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Rating
    |--------------------------------------------------------------------------
    */

    public function rate(
        Request $request,
        SupportChatSession $session
    ) {

        abort_unless(
            $session->user_id
            ===
            $request->user()->id,
            403
        );


        abort_unless(
            in_array(
                $session->status,
                [
                    'resolved',
                    'closed',
                ]
            ),
            422
        );


        $validated =
            $request->validate(
                [
                    'rating' =>
                        [
                            'required',
                            'integer',
                            'between:1,5',
                        ],

                    'review' =>
                        [
                            'nullable',
                            'string',
                            'max:2000',
                        ],
                ]
            );


        $session->update(
            [
                'rating' =>
                    $validated['rating'],

                'review' =>
                    $validated['review']
                    ?? null,

                'rated_at' =>
                    now(),

                'status' =>
                    'closed',

                'closed_at' =>
                    now(),
            ]
        );


        broadcast(
            new SupportAgentInboxUpdated(
                'rated',
                $session
            )
        );


        return response()->json(
            [
                'message' =>
                    'Thank you for your feedback.',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Skip Rating
    |--------------------------------------------------------------------------
    */

    public function skipRating(
        Request $request,
        SupportChatSession $session
    ) {

        abort_unless(
            $session->user_id
            ===
            $request->user()->id,
            403
        );


        $session->update(
            [
                'status' =>
                    'closed',

                'closed_at' =>
                    now(),
            ]
        );


        return response()->json(
            [
                'message' =>
                    'Conversation closed.',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON Response
    |--------------------------------------------------------------------------
    */

    private function sessionResponse(
        SupportChatSession $session,
        $currentUser
    ) {

        $session->load([
            'user',
            'agent',
        ]);


        /*
         * Latest 100 messages.
         */
        $messages =
            $session
                ->messages()

                ->with([
                    'sender',
                    'attachments',
                    'session',
                ])

                ->latest('id')

                ->take(100)

                ->get()

                ->sortBy('id')

                ->values()

                ->map(
                    fn ($message) =>
                        $message->payload()
                );


        /*
         * Mark opposite party messages as read.
         */
        $session
            ->messages()

            ->whereNotNull(
                'sender_id'
            )

            ->where(
                'sender_id',
                '!=',
                $currentUser->id
            )

            ->whereNull(
                'read_at'
            )

            ->update(
                [
                    'read_at' =>
                        now(),
                ]
            );


        return response()->json(
            [
                'session' =>
                    $session->payload(),

                'messages' =>
                    $messages,
            ]
        );
    }


    private function authorizeAccess(
        Request $request,
        SupportChatSession $session
    ) {

        $user =
            $request->user();


        if (
            $session->user_id
            ===
            $user->id
        ) {

            return;

        }


        if (
            $user->role === 'admin'
            &&
            $user
                ->supportAgentProfile
                ?->is_enabled
        ) {

            return;

        }


        abort(403);
    }


    private function authorizeSend(
        Request $request,
        SupportChatSession $session
    ) {

        $user =
            $request->user();


        /*
         * Customer
         */
        if (
            $session->user_id
            ===
            $user->id
        ) {

            abort_unless(
                in_array(
                    $session->status,
                    [
                        'waiting',
                        'active',
                    ]
                ),
                422
            );


            return;

        }


        /*
         * Agent must actually own the active conversation.
         */
        abort_unless(
            $session->status === 'active'
            &&
            $session->agent_id === $user->id
            &&
            $user
                ->supportAgentProfile
                ?->is_enabled,
            403
        );
    }
}