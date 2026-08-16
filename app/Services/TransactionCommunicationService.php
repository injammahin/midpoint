<?php

namespace App\Services;

use App\Mail\TransactionStatusUpdateMail;

use App\Models\SecureTransaction;
use App\Models\TransactionNotification;
use App\Models\User;
use App\Notifications\AdminTransactionDisputeOpenedNotification;
use Illuminate\Support\Facades\Log;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeStatusHistory;
use Throwable;

class TransactionCommunicationService
{
    public function __construct(
        protected TransactionEmailDeliveryService $emailDelivery
    ) {
    }

    public function buyer(
        SecureTransaction $transaction,
        string $event,
        string $title,
        string $message,
        ?string $badgeText = null,
        array $extraData = []
    ): void {

        $transaction->loadMissing([
            'buyer',
            'seller',
        ]);


        if (
            !$transaction->buyer
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Unique Event
        |--------------------------------------------------------------------------
        */

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':buyer:'
            .
            $event;


        /*
        |--------------------------------------------------------------------------
        | In-App Notification
        |--------------------------------------------------------------------------
        */

        TransactionNotification::firstOrCreate(
            [
                'event_key' =>
                    $eventKey,
            ],
            [
                'user_id' =>
                    $transaction->buyer_id,

                'secure_transaction_id' =>
                    $transaction->id,

                'audience' =>
                    'buyer',

                'type' =>
                    $this->resolveType(
                        $event
                    ),

                'title' =>
                    $title,

                'message' =>
                    $message,

                'data' =>
                    array_merge(
                        [
                            'reference' =>
                                $transaction->reference,

                            'public_token' =>
                                $transaction->public_token,

                            'status' =>
                                $transaction->status,
                        ],
                        $extraData
                    ),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | No Email
        |--------------------------------------------------------------------------
        */

        if (
            !$transaction->buyer->email
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Subject
        |--------------------------------------------------------------------------
        */

        $subject =
            $title
            .
            ' - '
            .
            $transaction->reference;


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $this->emailDelivery->send(
            $transaction,
            $eventKey,
            'buyer',
            $transaction->buyer->email,
            $subject,
            new TransactionStatusUpdateMail(

                $transaction,

                $title,

                $message,

                'View transaction',

                route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $transaction->public_token,
                    ]
                ),

                $badgeText

            )
        );
    }

    public function seller(
        SecureTransaction $transaction,
        string $event,
        string $title,
        string $message,
        ?string $badgeText = null,
        array $extraData = []
    ): void {

        $transaction->loadMissing([
            'seller',
            'buyer',
        ]);


        if (
            !$transaction->seller
        ) {

            Log::warning(
                'Seller communication skipped because seller relation is missing.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'seller_id' =>
                        $transaction->seller_id,
                ]
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Event Key
        |--------------------------------------------------------------------------
        */

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':seller:'
            .
            $event;


        /*
        |--------------------------------------------------------------------------
        | In-App Notification
        |--------------------------------------------------------------------------
        */

        TransactionNotification::firstOrCreate(
            [
                'event_key' =>
                    $eventKey,
            ],
            [
                'user_id' =>
                    $transaction->seller_id,

                'secure_transaction_id' =>
                    $transaction->id,

                'audience' =>
                    'seller',

                'type' =>
                    $this->resolveType(
                        $event
                    ),

                'title' =>
                    $title,

                'message' =>
                    $message,

                'data' =>
                    array_merge(
                        [
                            'reference' =>
                                $transaction->reference,

                            'public_token' =>
                                $transaction->public_token,

                            'status' =>
                                $transaction->status,
                        ],
                        $extraData
                    ),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | No Email
        |--------------------------------------------------------------------------
        */

        if (
            !$transaction->seller->email
        ) {

            Log::warning(
                'Seller transaction email skipped because seller email is empty.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'seller_id' =>
                        $transaction->seller_id,
                ]
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Subject
        |--------------------------------------------------------------------------
        */

        $subject =
            $title
            .
            ' - '
            .
            $transaction->reference;


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $this->emailDelivery->send(
            $transaction,
            $eventKey,
            'seller',
            $transaction->seller->email,
            $subject,
            new TransactionStatusUpdateMail(

                $transaction,

                $title,

                $message,

                'View transaction',

                route(
                    'seller.transactions.show',
                    [
                        'secureTransaction' =>
                            $transaction->public_token,
                    ]
                ),

                $badgeText

            )
        );
    }

    public function adminsForDispute(
        SecureTransaction $transaction
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $transaction->loadMissing([

            'buyer',

            'seller',

            'dispute',

        ]);


        /*
        |--------------------------------------------------------------------------
        | No Dispute
        |--------------------------------------------------------------------------
        */

        if (
            !$transaction->dispute
        ) {

            Log::warning(
                'Admin dispute notification skipped because dispute record is missing.',
                [

                    'transaction_id' =>
                        $transaction->id,

                    'reference' =>
                        $transaction->reference,

                ]
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Active Admins
        |--------------------------------------------------------------------------
        */

        $admins =
            User::query()

                ->where(
                    'role',
                    'admin'
                )

                ->where(
                    'status',
                    true
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Notify Every Admin
        |--------------------------------------------------------------------------
        */

        foreach (
            $admins
            as
            $admin
        ) {

            /*
            |--------------------------------------------------------------------------
            | Unique Transaction Event
            |--------------------------------------------------------------------------
            */

            $eventKey =
                'transaction:'
                .
                $transaction->id
                .
                ':admin:'
                .
                $admin->id
                .
                ':dispute-opened';


            /*
            |--------------------------------------------------------------------------
            | Existing Midpoint Transaction Notification
            |--------------------------------------------------------------------------
            |
            | Keep this because buyer/seller transaction notifications already use
            | the transaction_notifications architecture.
            |
            */

            TransactionNotification::firstOrCreate(
                [

                    'event_key' =>
                        $eventKey,

                ],
                [

                    'user_id' =>
                        $admin->id,

                    'secure_transaction_id' =>
                        $transaction->id,

                    'audience' =>
                        'admin',

                    'type' =>
                        'dispute',

                    'title' =>
                        'New transaction dispute',

                    'message' =>
                        'A buyer opened a dispute for '
                        .
                        $transaction->reference
                        .
                        '.',

                    'data' => [

                        'reference' =>
                            $transaction->reference,

                        'public_token' =>
                            $transaction->public_token,

                        'dispute_id' =>
                            $transaction
                                ->dispute
                                ->id,

                        'url' =>
                            route(
                                'admin.disputes.show',
                                $transaction->dispute
                            ),

                    ],

                ]
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN HEADER BELL NOTIFICATION
            |--------------------------------------------------------------------------
            |
            | Your admin header uses Laravel's "notifications" table, therefore we
            | also create a database notification here.
            |
            */

            $alreadyNotified =
                $admin

                    ->notifications()

                    ->where(
                        'type',
                        AdminTransactionDisputeOpenedNotification::class
                    )

                    ->where(
                        'data->dispute_id',
                        $transaction
                            ->dispute
                            ->id
                    )

                    ->exists();


            if (
                !$alreadyNotified
            ) {

                $admin->notify(

                    new AdminTransactionDisputeOpenedNotification(
                        $transaction,
                        $transaction->dispute
                    )

                );
            }


            /*
            |--------------------------------------------------------------------------
            | Email
            |--------------------------------------------------------------------------
            */

            if (
                !$admin->email
            ) {

                continue;
            }


            $this
                ->emailDelivery
                ->send(

                    $transaction,

                    $eventKey,

                    'admin',

                    $admin->email,

                    'New transaction dispute - '
                    .
                    $transaction->reference,

                    new TransactionStatusUpdateMail(

                        $transaction,

                        'New transaction dispute',

                        'A buyer opened a dispute for this transaction. Automatic seller payout has been paused until Midpoint reviews the case.',

                        'Review dispute',

                        route(
                            'admin.disputes.show',
                            $transaction->dispute
                        )

                    )

                );
        }
    }

        /*
    |--------------------------------------------------------------------------
    | Dispute Status Changed
    |--------------------------------------------------------------------------
    */

    public function disputeStatusChanged(
        SecureTransaction $transaction,
        TransactionDispute $dispute,
        TransactionDisputeStatusHistory $history
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $transaction->loadMissing([
            'buyer',
            'seller',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $status =
            $history->to_status;


        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        |
        | History ID makes every status-change communication unique.
        |
        */

        $event =
            'dispute-status-'
            .
            $history->id
            .
            '-'
            .
            $status;


        /*
        |--------------------------------------------------------------------------
        | Extra Notification Data
        |--------------------------------------------------------------------------
        */

        $extraData = [

            'dispute_id' =>
                $dispute->id,

            'dispute_status' =>
                $status,

            'dispute_status_label' =>
                $dispute->status_label,

        ];


        /*
        |--------------------------------------------------------------------------
        | Admin Message
        |--------------------------------------------------------------------------
        */

        $note =
            trim(
                (string) $history->note
            );


        /*
        |--------------------------------------------------------------------------
        | UNDER REVIEW
        |--------------------------------------------------------------------------
        |
        | Buyer receives email.
        |
        */

        if (
            $status
            ===
            TransactionDispute::STATUS_UNDER_REVIEW
        ) {

            $message =
                'Midpoint has started reviewing your dispute for transaction '
                .
                $transaction->reference
                .
                '. Seller payout remains paused while the case is being reviewed.';


            if (
                $note !== ''
            ) {

                $message .=
                    ' Midpoint message: '
                    .
                    $note;
            }


            $this->buyer(
                $transaction,
                $event,
                'Your dispute is now under review',
                $message,
                'Under Review',
                $extraData
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | AWAITING BUYER
        |--------------------------------------------------------------------------
        |
        | Buyer receives email.
        |
        */

        if (
            $status
            ===
            TransactionDispute::STATUS_AWAITING_BUYER
        ) {

            $message =
                'Midpoint needs additional information or action from you before reviewing this dispute further.';


            if (
                $note !== ''
            ) {

                $message .=
                    ' Request from Midpoint: '
                    .
                    $note;
            }


            $this->buyer(
                $transaction,
                $event,
                'Action required for your dispute',
                $message,
                'Awaiting Buyer',
                $extraData
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | AWAITING SELLER
        |--------------------------------------------------------------------------
        |
        | Seller receives email.
        |
        */

        if (
            $status
            ===
            TransactionDispute::STATUS_AWAITING_SELLER
        ) {

            $message =
                'Midpoint needs additional information or action from you regarding the buyer dispute on transaction '
                .
                $transaction->reference
                .
                '. Seller payout remains paused.';


            if (
                $note !== ''
            ) {

                $message .=
                    ' Request from Midpoint: '
                    .
                    $note;
            }


            $this->seller(
                $transaction,
                $event,
                'Action required for a transaction dispute',
                $message,
                'Awaiting Seller',
                $extraData
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVED
        |--------------------------------------------------------------------------
        |
        | Both buyer and seller receive email.
        |
        */

        if (
            $status
            ===
            TransactionDispute::STATUS_RESOLVED
        ) {

            $message =
                'Midpoint has completed the review of the dispute for transaction '
                .
                $transaction->reference
                .
                '.';


            if (
                $note !== ''
            ) {

                $message .=
                    ' Resolution note: '
                    .
                    $note;
            }


            /*
            |--------------------------------------------------------------------------
            | Buyer
            |--------------------------------------------------------------------------
            */

            $this->buyer(
                $transaction,
                $event,
                'Your dispute review has been resolved',
                $message,
                'Resolved',
                $extraData
            );


            /*
            |--------------------------------------------------------------------------
            | Seller
            |--------------------------------------------------------------------------
            */

            $this->seller(
                $transaction,
                $event,
                'Transaction dispute review resolved',
                $message,
                'Resolved',
                $extraData
            );
        }
    }

    protected function resolveType(
        string $event
    ): string {
        if (
            str_contains(
                $event,
                'preparing'
            )
            ||
            str_contains(
                $event,
                'dispatch'
            )
            ||
            str_contains(
                $event,
                'transit'
            )
            ||
            str_contains(
                $event,
                'deliver'
            )
        ) {
            return 'dispatch';
        }

        if (
            str_contains(
                $event,
                'inspection'
            )
        ) {
            return 'inspection';
        }

        if (
            str_contains(
                $event,
                'dispute'
            )
        ) {
            return 'dispute';
        }

        return 'payment';
    }
}