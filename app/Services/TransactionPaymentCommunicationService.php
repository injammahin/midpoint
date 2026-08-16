<?php

namespace App\Services;

use App\Mail\BuyerPaymentConfirmedMail;
use App\Mail\SellerPaymentReceivedMail;

use App\Models\SecureTransaction;
use App\Models\TransactionNotification;
use App\Models\User;
use App\Notifications\AdminBuyerOrderPaidNotification;
use Illuminate\Support\Facades\Log;

class TransactionPaymentCommunicationService
{
    public function __construct(
        protected TransactionEmailDeliveryService $emailDelivery
    ) {
    }

    public function handle(
        SecureTransaction $transaction
    ): void {
        $transaction->refresh();

        $transaction->loadMissing([
            'seller',
            'buyer',
        ]);

        if (
            $transaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {
            return;
        }

        $this->seller(
            $transaction
        );


        $this->buyer(
            $transaction
        );


        $this->admins(
            $transaction
        );
        
    }

    protected function seller(
        SecureTransaction $transaction
    ): void {
        if (!$transaction->seller) {
            Log::warning(
                'Payment seller notification skipped because seller is unavailable.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'seller_id' =>
                        $transaction->seller_id,
                ]
            );

            return;
        }

        $amount =
            (float) (
                $transaction->paid_amount
                ?:
                $transaction->total_amount
            );

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':seller:payment-received';

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
                    'payment',

                'title' =>
                    'Payment received — ₦'
                    .
                    number_format(
                        $amount,
                        2
                    )
                    .
                    ' secured',

                'message' =>
                    (
                        $transaction->buyer?->name
                        ?:
                        $transaction->buyer_email
                    )
                    .
                    ' completed payment for '
                    .
                    $transaction->title
                    .
                    '. You can now prepare the order.',

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'amount' =>
                        $amount,
                ],
            ]
        );

        if (
            !$transaction->seller->email
        ) {
            Log::warning(
                'Seller payment email skipped because seller email is empty.',
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
            'Buyer payment secured - '
            .
            $transaction->reference;

        $this->emailDelivery->send(
            $transaction,
            $eventKey,
            'seller',
            $transaction->seller->email,
            $subject,
            new SellerPaymentReceivedMail(
                $transaction
            )
        );
    }

    protected function buyer(
        SecureTransaction $transaction
    ): void {
        if (!$transaction->buyer) {
            return;
        }

        $amount =
            (float) (
                $transaction->paid_amount
                ?:
                $transaction->total_amount
            );

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':buyer:payment-confirmed';

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
                    'payment',

                'title' =>
                    'Payment complete — ₦'
                    .
                    number_format(
                        $amount,
                        2
                    )
                    .
                    ' secured',

                'message' =>
                    'Your payment for '
                    .
                    $transaction->title
                    .
                    ' has been verified successfully. Your invoice has been emailed to you.',

                'data' => [
                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'invoice_number' =>
                        $transaction->invoice_number,

                    'amount' =>
                        $amount,
                ],
            ]
        );

        if (
            !$transaction->buyer->email
        ) {
            return;
        }

        $subject =
            'Payment confirmed - '
            .
            $transaction->invoice_number;

        $this->emailDelivery->send(
            $transaction,
            $eventKey,
            'buyer',
            $transaction->buyer->email,
            $subject,
            new BuyerPaymentConfirmedMail(
                $transaction
            )
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Admin Payment Notification
    |--------------------------------------------------------------------------
    */

    protected function admins(
        SecureTransaction $transaction
    ): void {

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


        foreach (
            $admins
            as
            $admin
        ) {

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Notification
            |--------------------------------------------------------------------------
            */

            $alreadyNotified =
                $admin

                    ->notifications()

                    ->where(
                        'type',
                        AdminBuyerOrderPaidNotification::class
                    )

                    ->where(
                        'data->secure_transaction_id',
                        $transaction->id
                    )

                    ->exists();


            if (
                !$alreadyNotified
            ) {

                $admin->notify(

                    new AdminBuyerOrderPaidNotification(
                        $transaction
                    )

                );
            }
        }
    }
}