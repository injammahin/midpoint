<?php

namespace App\Services;

use App\Mail\TransactionStatusUpdateMail;
use App\Models\SecureTransaction;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\TransactionNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class DisputeRoomCommunicationService
{
    public function __construct(
        protected TransactionEmailDeliveryService $emailDelivery
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Room Activated
    |--------------------------------------------------------------------------
    */

    public function roomActivated(
        TransactionDispute $dispute
    ): void {

        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        $transaction =
            $dispute->transaction;


        if (!$transaction) {
            return;
        }


        $message =
            'Midpoint Support has opened a dispute resolution room for transaction '
            .
            $transaction->reference
            .
            '. You can now communicate privately with Midpoint Support and upload supporting evidence. The buyer and seller cannot see each other\'s messages. Seller payout remains locked while the dispute is active.';


        $this->notifyBuyer(
            $dispute,
            'dispute-room-activated',
            'Midpoint opened your dispute resolution room',
            $message,
            'Dispute room active'
        );


        $this->notifySeller(
            $dispute,
            'dispute-room-activated',
            'Midpoint opened a dispute resolution room',
            $message,
            'Dispute room active'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | New Room Message
    |--------------------------------------------------------------------------
    */

    public function messageSent(
        TransactionDispute $dispute,
        TransactionDisputeMessage $roomMessage
    ): void {

        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        if (
            !$dispute->transaction
            ||
            $roomMessage->visibility
            ===
            TransactionDisputeMessage::VISIBILITY_INTERNAL
        ) {

            return;
        }


        $senderLabel =
            match ($roomMessage->sender_role) {

                TransactionDisputeMessage::ROLE_ADMIN =>
                    'Midpoint Support',

                TransactionDisputeMessage::ROLE_BUYER =>
                    'Buyer',

                TransactionDisputeMessage::ROLE_SELLER =>
                    'Seller',

                default =>
                    'Midpoint',
            };


        $messageText =
            trim(
                (string)
                $roomMessage->message
            );


        if ($messageText === '') {

            $messageText =
                'A new attachment was added to the dispute resolution room.';
        }


        if (
            mb_strlen(
                $messageText
            )
            >
            500
        ) {

            $messageText =
                mb_substr(
                    $messageText,
                    0,
                    497
                )
                .
                '...';
        }


        $notificationMessage =
            $senderLabel
            .
            ': '
            .
            $messageText;


        $event =
            'dispute-message-'
            .
            $roomMessage->id;


        /*
        |--------------------------------------------------------------------------
        | Admin -> Buyer / Seller
        |--------------------------------------------------------------------------
        */

        if (
            $roomMessage->sender_role
            ===
            TransactionDisputeMessage::ROLE_ADMIN
        ) {

            if (
                in_array(
                    $roomMessage->visibility,
                    [
                        TransactionDisputeMessage::VISIBILITY_ALL,
                        TransactionDisputeMessage::VISIBILITY_BUYER,
                    ],
                    true
                )
            ) {

                $this->notifyBuyer(
                    $dispute,
                    $event,
                    'New message from Midpoint Support',
                    $notificationMessage,
                    'Action may be required'
                );
            }


            if (
                in_array(
                    $roomMessage->visibility,
                    [
                        TransactionDisputeMessage::VISIBILITY_ALL,
                        TransactionDisputeMessage::VISIBILITY_SELLER,
                    ],
                    true
                )
            ) {

                $this->notifySeller(
                    $dispute,
                    $event,
                    'New message from Midpoint Support',
                    $notificationMessage,
                    'Action may be required'
                );
            }


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer / Seller Messages Stay Private From Each Other
        |--------------------------------------------------------------------------
        |
        | Admins read participant replies from the admin dispute room. Never send
        | a buyer message to the seller, or a seller message to the buyer.
        |
        */

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Room Closed
    |--------------------------------------------------------------------------
    */

    public function roomClosed(
        TransactionDispute $dispute
    ): void {

        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        $transaction =
            $dispute->transaction;


        if (!$transaction) {
            return;
        }


        $message =
            $dispute->room_closed_message
            .
            ' You have been returned to transaction '
            .
            $transaction->reference
            .
            '. The read-only dispute record remains available from that transaction.';


        $this->notifyBuyer(
            $dispute,
            'dispute-room-closed-' . $dispute->id,
            'Midpoint closed the dispute room',
            $message,
            'Room closed',
            route(
                'buyer.transactions.show',
                [
                    'secureTransaction' =>
                        $transaction->public_token,
                ]
            ),
            'View transaction'
        );


        $this->notifySeller(
            $dispute,
            'dispute-room-closed-' . $dispute->id,
            'Midpoint closed the dispute room',
            $message,
            'Room closed',
            route(
                'seller.transactions.show',
                [
                    'secureTransaction' =>
                        $transaction->public_token,
                ]
            ),
            'View transaction'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolution Update
    |--------------------------------------------------------------------------
    */

    public function resolutionUpdate(
        TransactionDispute $dispute,
        string $event,
        string $title,
        string $message,
        ?string $badge = null
    ): void {

        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        $transaction =
            $dispute->transaction;


        if (!$transaction) {
            return;
        }

        $this->notifyBuyer(
            $dispute,
            $event,
            $title,
            $message,
            $badge,
            route(
                'buyer.transactions.show',
                [
                    'secureTransaction' =>
                        $transaction->public_token,
                ]
            ),
            'View transaction'
        );


        $this->notifySeller(
            $dispute,
            $event,
            $title,
            $message,
            $badge,
            route(
                'seller.transactions.show',
                [
                    'secureTransaction' =>
                        $transaction->public_token,
                ]
            ),
            'View transaction'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Notification + Email
    |--------------------------------------------------------------------------
    */

    protected function notifyBuyer(
        TransactionDispute $dispute,
        string $event,
        string $title,
        string $message,
        ?string $badge = null,
        ?string $destinationUrl = null,
        string $actionText = 'Open dispute room'
    ): void {

        $transaction =
            $dispute->transaction;


        $buyer =
            $transaction
                ?->buyer;


        if (
            !$transaction
            ||
            !$buyer
        ) {

            return;
        }


        $destinationUrl =
            $destinationUrl
            ?:
            route(
                'dispute-room.show',
                $dispute
            );


        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':buyer:'
            .
            $event;


        TransactionNotification::firstOrCreate(
            [
                'event_key' =>
                    $eventKey,
            ],
            [
                'user_id' =>
                    $buyer->id,

                'secure_transaction_id' =>
                    $transaction->id,

                'audience' =>
                    'buyer',

                'type' =>
                    'dispute',

                'title' =>
                    $title,

                'message' =>
                    $message,

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'dispute_id' =>
                        $dispute->id,

                    'url' =>
                        $destinationUrl,
                ],
            ]
        );


        if (
            empty(
                $buyer->email
            )
        ) {

            return;
        }


        try {

            $this->emailDelivery->send(
                $transaction,
                $eventKey,
                'buyer',
                $buyer->email,
                $title
                .
                ' - '
                .
                $transaction->reference,
                new TransactionStatusUpdateMail(
                    $transaction,
                    $title,
                    $message,
                    $actionText,
                    $destinationUrl,
                    $badge
                )
            );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Buyer dispute-room communication failed.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'user_id' =>
                        $buyer->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Notification + Email
    |--------------------------------------------------------------------------
    */

    protected function notifySeller(
        TransactionDispute $dispute,
        string $event,
        string $title,
        string $message,
        ?string $badge = null,
        ?string $destinationUrl = null,
        string $actionText = 'Open dispute room'
    ): void {

        $transaction =
            $dispute->transaction;


        $seller =
            $transaction
                ?->seller;


        if (
            !$transaction
            ||
            !$seller
        ) {

            return;
        }


        $destinationUrl =
            $destinationUrl
            ?:
            route(
                'dispute-room.show',
                $dispute
            );


        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':seller:'
            .
            $event;


        TransactionNotification::firstOrCreate(
            [
                'event_key' =>
                    $eventKey,
            ],
            [
                'user_id' =>
                    $seller->id,

                'secure_transaction_id' =>
                    $transaction->id,

                'audience' =>
                    'seller',

                'type' =>
                    'dispute',

                'title' =>
                    $title,

                'message' =>
                    $message,

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'dispute_id' =>
                        $dispute->id,

                    'url' =>
                        $destinationUrl,
                ],
            ]
        );


        if (
            empty(
                $seller->email
            )
        ) {

            return;
        }


        try {

            $this->emailDelivery->send(
                $transaction,
                $eventKey,
                'seller',
                $seller->email,
                $title
                .
                ' - '
                .
                $transaction->reference,
                new TransactionStatusUpdateMail(
                    $transaction,
                    $title,
                    $message,
                    $actionText,
                    $destinationUrl,
                    $badge
                )
            );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller dispute-room communication failed.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'user_id' =>
                        $seller->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );
        }
    }
}
