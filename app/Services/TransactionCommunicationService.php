<?php

namespace App\Services;

use App\Mail\TransactionStatusUpdateMail;

use App\Models\SecureTransaction;
use App\Models\TransactionNotification;
use App\Models\User;

use Illuminate\Support\Facades\Log;

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
        string $message
    ): void {
        $transaction->loadMissing([
            'buyer',
            'seller',
        ]);

        if (!$transaction->buyer) {
            return;
        }

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

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'status' =>
                        $transaction->status,
                ],
            ]
        );

        if (
            !$transaction->buyer->email
        ) {
            return;
        }

        $subject =
            $title
            .
            ' - '
            .
            $transaction->reference;

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
                )
            )
        );
    }

    public function seller(
        SecureTransaction $transaction,
        string $event,
        string $title,
        string $message
    ): void {
        $transaction->loadMissing([
            'seller',
            'buyer',
        ]);

        if (!$transaction->seller) {
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

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'status' =>
                        $transaction->status,
                ],
            ]
        );

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

        $subject =
            $title
            .
            ' - '
            .
            $transaction->reference;

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
                )
            )
        );
    }

    public function adminsForDispute(
        SecureTransaction $transaction
    ): void {
        $transaction->loadMissing([
            'buyer',
            'seller',
            'dispute',
        ]);

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

        foreach ($admins as $admin) {
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
                    ],
                ]
            );

            if (!$admin->email) {
                continue;
            }

            $this->emailDelivery->send(
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
                    'A buyer opened a dispute for this transaction. Automatic seller payout has been paused until MidPoint reviews the case.',
                    'Open admin dashboard',
                    route(
                        'admin.dashboard'
                    )
                )
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