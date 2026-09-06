<?php

namespace App\Http\Controllers;

use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\User;
use App\Services\DisputeRoomCommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DisputeRoomController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Participant Room
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        TransactionDispute $dispute
    ) {

        $role =
            $this->authorizeRoom(
                $request->user(),
                $dispute
            );


        $dispute->load([
            'transaction.buyer',
            'transaction.seller',
            'buyer',
            'seller',
            'roomActivator',
        ]);


        $messages =
            $this
                ->visibleMessagesQuery(
                    $dispute,
                    $role
                )
                ->with('sender')
                ->latest('id')
                ->limit(100)
                ->get()
                ->reverse()
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Admins Normally Use admin.disputes.show
        |--------------------------------------------------------------------------
        */

        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            return redirect()
                ->route(
                    'admin.disputes.show',
                    $dispute
                );
        }


        $layout =
            $role
            ===
            TransactionDisputeMessage::ROLE_SELLER

                ? 'seller.layouts.app'

                : 'buyer.layouts.app';


        return view(
            'shared.disputes.room',
            [
                'layout' =>
                    $layout,

                'role' =>
                    $role,

                'dispute' =>
                    $dispute,

                'transaction' =>
                    $dispute->transaction,

                'messages' =>
                    $messages,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Poll Messages
    |--------------------------------------------------------------------------
    */

    public function messages(
        Request $request,
        TransactionDispute $dispute
    ): JsonResponse {

        $role =
            $this->authorizeRoom(
                $request->user(),
                $dispute
            );


        $afterId =
            max(
                0,
                (int)
                $request->query(
                    'after_id',
                    0
                )
            );


        $messages =
            $this
                ->visibleMessagesQuery(
                    $dispute,
                    $role
                )
                ->where(
                    'id',
                    '>',
                    $afterId
                )
                ->with('sender')
                ->orderBy('id')
                ->limit(100)
                ->get()
                ->map(
                    fn (
                        TransactionDisputeMessage $message
                    ) =>
                        $this->serializeMessage(
                            $message,
                            $dispute
                        )
                );


        return response()->json([
            'messages' =>
                $messages,

            'resolved' =>
                $dispute->fresh()->isResolved(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Send Message / Proof
    |--------------------------------------------------------------------------
    */

    public function send(
        Request $request,
        TransactionDispute $dispute,
        DisputeRoomCommunicationService $communications
    ): JsonResponse {

        $role =
            $this->authorizeRoom(
                $request->user(),
                $dispute
            );


        if (
            !$dispute->isRoomActive()
        ) {

            return response()->json(
                [
                    'message' =>
                        'Midpoint Support has not activated this dispute room yet.',
                ],
                422
            );
        }


        if (
            $dispute->isResolved()
        ) {

            return response()->json(
                [
                    'message' =>
                        'This dispute has already been resolved. The room is now read-only.',
                ],
                422
            );
        }


        $visibilityRules = [

            TransactionDisputeMessage::VISIBILITY_ALL,

            TransactionDisputeMessage::VISIBILITY_BUYER,

            TransactionDisputeMessage::VISIBILITY_SELLER,

            TransactionDisputeMessage::VISIBILITY_INTERNAL,

        ];


        $validated =
            $request->validate([

                'message' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'visibility' => [
                    'nullable',
                    Rule::in(
                        $visibilityRules
                    ),
                ],

                'attachments' => [
                    'nullable',
                    'array',
                    'max:6',
                ],

                'attachments.*' => [
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,mp4,mov,webm,doc,docx,xls,xlsx,csv,txt,zip',
                    'max:20480',
                ],
            ]);


        $messageText =
            trim(
                (string)
                (
                    $validated['message']
                    ??
                    ''
                )
            );


        $incomingFiles =
            $request->file(
                'attachments',
                []
            );


        if (
            $messageText === ''
            &&
            count(
                $incomingFiles
            )
            ===
            0
        ) {

            return response()->json(
                [
                    'message' =>
                        'Write a message or attach at least one proof file.',
                ],
                422
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Only Admin Can Create Private / Internal Messages
        |--------------------------------------------------------------------------
        */

        $visibility =
            TransactionDisputeMessage::VISIBILITY_ALL;


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            $visibility =
                $validated['visibility']
                ??
                TransactionDisputeMessage::VISIBILITY_ALL;
        }


        /*
        |--------------------------------------------------------------------------
        | Store Attachments Privately
        |--------------------------------------------------------------------------
        */

        $attachments = [];


        foreach (
            $incomingFiles
            as
            $file
        ) {

            $extension =
                strtolower(
                    (string)
                    $file->getClientOriginalExtension()
                );


            $filename =
                Str::uuid()
                .
                (
                    $extension !== ''
                        ? '.' . $extension
                        : ''
                );


            $path =
                $file->storeAs(
                    'transaction-disputes/'
                    .
                    $dispute->id
                    .
                    '/room',
                    $filename,
                    'local'
                );


            $attachments[] = [

                'original_name' =>
                    mb_substr(
                        $file->getClientOriginalName(),
                        0,
                        255
                    ),

                'path' =>
                    $path,

                'mime' =>
                    (string)
                    $file->getMimeType(),

                'size' =>
                    (int)
                    $file->getSize(),

            ];
        }


        $roomMessage =
            TransactionDisputeMessage::create([

                'transaction_dispute_id' =>
                    $dispute->id,

                'secure_transaction_id' =>
                    $dispute->secure_transaction_id,

                'sender_id' =>
                    $request->user()->id,

                'sender_role' =>
                    $role,

                'visibility' =>
                    $visibility,

                'message' =>
                    $messageText !== ''
                        ? $messageText
                        : null,

                'attachments' =>
                    $attachments !== []
                        ? $attachments
                        : null,

                'is_system' =>
                    false,

            ]);


        $roomMessage->load(
            'sender'
        );


        /*
        |--------------------------------------------------------------------------
        | Notification / Email
        |--------------------------------------------------------------------------
        |
        | Financial/message persistence must not be rolled back just because
        | an email provider has a temporary problem.
        |
        */

        $communications->messageSent(
            $dispute->fresh([
                'transaction.buyer',
                'transaction.seller',
            ]),
            $roomMessage
        );


        return response()->json([
            'message' =>
                'Message sent.',

            'room_message' =>
                $this->serializeMessage(
                    $roomMessage,
                    $dispute
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Private Attachment Download
    |--------------------------------------------------------------------------
    */

    public function download(
        Request $request,
        TransactionDispute $dispute,
        TransactionDisputeMessage $message,
        int $index
    ) {

        $role =
            $this->authorizeRoom(
                $request->user(),
                $dispute
            );


        abort_unless(
            (int)
            $message->transaction_dispute_id
            ===
            (int)
            $dispute->id,
            404
        );


        abort_unless(
            $this->canSeeMessage(
                $message,
                $role
            ),
            403
        );


        $attachments =
            $message->attachments
            ??
            [];


        abort_unless(
            isset(
                $attachments[$index]
            ),
            404
        );


        $attachment =
            $attachments[$index];


        $path =
            (string)
            (
                $attachment['path']
                ??
                ''
            );


        abort_unless(
            $path !== ''
            &&
            Storage::disk('local')
                ->exists(
                    $path
                ),
            404
        );


        return Storage::disk('local')
            ->download(
                $path,
                (string)
                (
                    $attachment['original_name']
                    ??
                    basename(
                        $path
                    )
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    protected function authorizeRoom(
        User $user,
        TransactionDispute $dispute
    ): string {

        if (
            $user->canAccessAdminPanel()
        ) {

            return TransactionDisputeMessage::ROLE_ADMIN;
        }


        if (
            (int)
            $dispute->buyer_id
            ===
            (int)
            $user->id
        ) {

            return TransactionDisputeMessage::ROLE_BUYER;
        }


        if (
            (int)
            $dispute->seller_id
            ===
            (int)
            $user->id
        ) {

            return TransactionDisputeMessage::ROLE_SELLER;
        }


        abort(
            403,
            'You do not have access to this dispute room.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Visible Message Query
    |--------------------------------------------------------------------------
    */

    protected function visibleMessagesQuery(
        TransactionDispute $dispute,
        string $role
    ) {

        $query =
            TransactionDisputeMessage::query()

                ->where(
                    'transaction_dispute_id',
                    $dispute->id
                );


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            return $query;
        }


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_BUYER
        ) {

            return $query->whereIn(
                'visibility',
                [
                    TransactionDisputeMessage::VISIBILITY_ALL,
                    TransactionDisputeMessage::VISIBILITY_BUYER,
                ]
            );
        }


        return $query->whereIn(
            'visibility',
            [
                TransactionDisputeMessage::VISIBILITY_ALL,
                TransactionDisputeMessage::VISIBILITY_SELLER,
            ]
        );
    }


    protected function canSeeMessage(
        TransactionDisputeMessage $message,
        string $role
    ): bool {

        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            return true;
        }


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_BUYER
        ) {

            return in_array(
                $message->visibility,
                [
                    TransactionDisputeMessage::VISIBILITY_ALL,
                    TransactionDisputeMessage::VISIBILITY_BUYER,
                ],
                true
            );
        }


        return in_array(
            $message->visibility,
            [
                TransactionDisputeMessage::VISIBILITY_ALL,
                TransactionDisputeMessage::VISIBILITY_SELLER,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON Serialization
    |--------------------------------------------------------------------------
    */

    protected function serializeMessage(
        TransactionDisputeMessage $message,
        TransactionDispute $dispute
    ): array {

        $attachments =
            collect(
                $message->attachments
                ??
                []
            )
                ->values()
                ->map(
                    function (
                        array $attachment,
                        int $index
                    ) use (
                        $dispute,
                        $message
                    ) {

                        return [

                            'name' =>
                                $attachment['original_name']
                                ??
                                'Attachment',

                            'mime' =>
                                $attachment['mime']
                                ??
                                null,

                            'size' =>
                                $attachment['size']
                                ??
                                null,

                            'url' =>
                                route(
                                    'dispute-room.attachments.download',
                                    [
                                        'dispute' =>
                                            $dispute->id,

                                        'message' =>
                                            $message->id,

                                        'index' =>
                                            $index,
                                    ]
                                ),
                        ];
                    }
                )
                ->all();


        return [

            'id' =>
                $message->id,

            'sender_id' =>
                $message->sender_id,

            'sender_role' =>
                $message->sender_role,

            'sender_name' =>
                $message->is_system

                    ? 'Midpoint'

                    : (
                        $message->sender?->name
                        ?:
                        match (
                            $message->sender_role
                        ) {

                            TransactionDisputeMessage::ROLE_ADMIN =>
                                'Midpoint Support',

                            TransactionDisputeMessage::ROLE_BUYER =>
                                'Buyer',

                            TransactionDisputeMessage::ROLE_SELLER =>
                                'Seller',

                            default =>
                                'Midpoint',
                        }
                    ),

            'visibility' =>
                $message->visibility,

            'message' =>
                $message->message,

            'attachments' =>
                $attachments,

            'is_system' =>
                (bool)
                $message->is_system,

            'created_at' =>
                $message->created_at
                    ?->format(
                        'd M Y, h:i A'
                    ),
        ];
    }
}
