<?php

namespace App\Http\Controllers;

use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\User;
use App\Services\DisputeRoomCommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'roomCloser',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Admins use the full case page
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


        $transactionUrl =
            $this->participantTransactionUrl(
                $dispute,
                $role
            );


        /*
        |--------------------------------------------------------------------------
        | A live participant is removed when Support closes the room
        |--------------------------------------------------------------------------
        |
        | The record=1 query is used only by the transaction page's explicit
        | "View dispute record" link. It preserves the read-only audit record.
        |
        */

        if (
            $dispute->isRoomClosed()
            &&
            !$request->boolean('record')
        ) {

            return redirect(
                $transactionUrl
            )->with(
                'warning',
                $dispute->room_closed_message
            );
        }


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

                'transactionUrl' =>
                    $transactionUrl,

                'recordMode' =>
                    $request->boolean('record'),
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
                            $dispute,
                            $role
                        )
                );


        $freshDispute =
            $dispute->fresh();


        $roomClosed =
            $freshDispute->isRoomClosed();


        return response()->json([
            'messages' =>
                $messages,

            'resolved' =>
                $freshDispute->isResolved(),

            'room_closed' =>
                $roomClosed,

            'close_message' =>
                $roomClosed
                    ? $freshDispute->room_closed_message
                    : null,

            'redirect_url' =>
                $roomClosed
                &&
                $role !== TransactionDisputeMessage::ROLE_ADMIN

                    ? $this->participantTransactionUrl(
                        $freshDispute,
                        $role
                    )

                    : null,
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


        if ($dispute->isRoomClosed()) {

            return response()->json(
                [
                    'message' =>
                        $dispute->room_closed_message,

                    'room_closed' =>
                        true,

                    'redirect_url' =>
                        $role !== TransactionDisputeMessage::ROLE_ADMIN

                            ? $this->participantTransactionUrl(
                                $dispute,
                                $role
                            )

                            : null,
                ],
                409
            );
        }


        if (!$dispute->isRoomActivated()) {

            return response()->json(
                [
                    'message' =>
                        'Midpoint Support has not activated this dispute room yet.',
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
            TransactionDisputeMessage::participantVisibility(
                $role
            );


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            $visibility =
                $validated['visibility']
                ??
                TransactionDisputeMessage::VISIBILITY_BUYER;
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
            DB::transaction(
                function () use (
                    $dispute,
                    $request,
                    $role,
                    $visibility,
                    $messageText,
                    $attachments
                ) {

                    $lockedDispute =
                        TransactionDispute::query()
                            ->whereKey(
                                $dispute->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    if (!$lockedDispute->isRoomActive()) {
                        return null;
                    }


                    return TransactionDisputeMessage::create([

                        'transaction_dispute_id' =>
                            $lockedDispute->id,

                        'secure_transaction_id' =>
                            $lockedDispute->secure_transaction_id,

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
                }
            );


        if (!$roomMessage) {

            foreach ($attachments as $attachment) {

                $path =
                    (string)
                    (
                        $attachment['path']
                        ??
                        ''
                    );


                if ($path !== '') {
                    Storage::disk('local')->delete(
                        $path
                    );
                }
            }


            $freshDispute =
                $dispute->fresh();


            return response()->json(
                [
                    'message' =>
                        $freshDispute->room_closed_message,

                    'room_closed' =>
                        true,

                    'redirect_url' =>
                        $role !== TransactionDisputeMessage::ROLE_ADMIN

                            ? $this->participantTransactionUrl(
                                $freshDispute,
                                $role
                            )

                            : null,
                ],
                409
            );
        }


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
                    $dispute,
                    $role
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
                )
                ->visibleToRole(
                    $role
                );


        return $query;
    }


    protected function canSeeMessage(
        TransactionDisputeMessage $message,
        string $role
    ): bool {

        return $message->isVisibleToRole(
            $role
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON Serialization
    |--------------------------------------------------------------------------
    */

    protected function serializeMessage(
        TransactionDisputeMessage $message,
        TransactionDispute $dispute,
        string $viewerRole
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
                        $message->sender_role
                        ===
                        TransactionDisputeMessage::ROLE_ADMIN
                        &&
                        $viewerRole
                        !==
                        TransactionDisputeMessage::ROLE_ADMIN

                            ? 'Midpoint Support'

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
                            )
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


    protected function participantTransactionUrl(
        TransactionDispute $dispute,
        string $role
    ): string {

        $dispute->loadMissing(
            'transaction'
        );


        $transaction =
            $dispute->transaction;


        abort_unless(
            $transaction,
            404
        );


        if (
            $role
            ===
            TransactionDisputeMessage::ROLE_SELLER
        ) {

            return route(
                'seller.transactions.show',
                [
                    'secureTransaction' =>
                        $transaction->public_token,
                ]
            );
        }


        return route(
            'buyer.transactions.show',
            [
                'secureTransaction' =>
                    $transaction->public_token,
            ]
        );
    }
}
