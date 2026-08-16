<?php

namespace App\Services;

use App\Mail\BuyerPaymentConfirmedMail;
use App\Mail\SellerMarketplaceOrderReceivedMail;
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


    /*
    |--------------------------------------------------------------------------
    | Handle Successful Payment
    |--------------------------------------------------------------------------
    */

    public function handle(
        SecureTransaction $transaction
    ): void {

        $transaction->refresh();


        $transaction->loadMissing([
            'seller',
            'buyer',
            'product',
        ]);


        if (
            $transaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Communication
        |--------------------------------------------------------------------------
        */

        $this->seller(
            $transaction
        );


        /*
        |--------------------------------------------------------------------------
        | Buyer Communication
        |--------------------------------------------------------------------------
        */

        $this->buyer(
            $transaction
        );


        /*
        |--------------------------------------------------------------------------
        | Admin Communication
        |--------------------------------------------------------------------------
        */

        $this->admins(
            $transaction
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Communication Router
    |--------------------------------------------------------------------------
    |
    | Marketplace product checkout and seller-created transaction are
    | intentionally different seller communications.
    |
    */

    protected function seller(
        SecureTransaction $transaction
    ): void {

        if (
            $transaction->isMarketplaceCheckout()
        ) {

            $this->sellerMarketplaceOrder(
                $transaction
            );


            return;
        }


        $this->sellerSecureTransactionPayment(
            $transaction
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Marketplace Product Order
    |--------------------------------------------------------------------------
    */

    protected function sellerMarketplaceOrder(
        SecureTransaction $transaction
    ): void {

        if (
            !$transaction->seller
        ) {

            Log::warning(
                'Marketplace order seller notification skipped because seller is unavailable.',
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
        | Amount
        |--------------------------------------------------------------------------
        */

        $amount =
            (float) (
                $transaction->paid_amount
                ?:
                $transaction->total_amount
            );


        /*
        |--------------------------------------------------------------------------
        | Buyer
        |--------------------------------------------------------------------------
        */

        $buyerName =
            $transaction->buyer?->name
            ?:
            $transaction->buyer_email;


        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        $quantity =
            max(
                1,
                (int) $transaction->quantity
            );


        /*
        |--------------------------------------------------------------------------
        | Separate Event
        |--------------------------------------------------------------------------
        */

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':seller:marketplace-order-paid';


        /*
        |--------------------------------------------------------------------------
        | Seller In-App Notification
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


                /*
                |--------------------------------------------------------------------------
                | Keep payment type
                |--------------------------------------------------------------------------
                |
                | This means it still appears under the seller's Payments tab.
                |
                */

                'type' =>
                    'payment',


                'title' =>
                    'New order received — '
                    .
                    $quantity
                    .
                    ' × '
                    .
                    $transaction->title,


                'message' =>
                    $buyerName
                    .
                    ' ordered '
                    .
                    $quantity
                    .
                    ' × '
                    .
                    $transaction->title
                    .
                    '. ₦'
                    .
                    number_format(
                        $amount,
                        2
                    )
                    .
                    ' has been secured. Order '
                    .
                    $transaction->reference
                    .
                    '. Delivery: '
                    .
                    (
                        $transaction->delivery_note
                        ?:
                        'No delivery address provided.'
                    ),


                'data' => [

                    'reference' =>
                        $transaction->reference,


                    'public_token' =>
                        $transaction->public_token,


                    'transaction_source' =>
                        $transaction->transaction_source,


                    'seller_product_id' =>
                        $transaction->seller_product_id,


                    'item' =>
                        $transaction->title,


                    'quantity' =>
                        $quantity,


                    'unit_price' =>
                        (float) $transaction->unit_price,


                    'subtotal' =>
                        (float) $transaction->subtotal,


                    'delivery_fee' =>
                        (float) $transaction->delivery_fee,


                    'amount' =>
                        $amount,


                    'buyer_name' =>
                        $buyerName,


                    'buyer_email' =>
                        $transaction->buyer_email,


                    'buyer_phone' =>
                        $transaction->buyer_phone,


                    'delivery_address' =>
                        $transaction->delivery_note,

                ],

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Email Available?
        |--------------------------------------------------------------------------
        */

        if (
            !$transaction->seller->email
        ) {

            Log::warning(
                'Marketplace order email skipped because seller email is empty.',
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
        | Different Marketplace Subject
        |--------------------------------------------------------------------------
        */

        $subject =
            'New marketplace order - '
            .
            $transaction->reference;


        /*
        |--------------------------------------------------------------------------
        | Different Marketplace Email
        |--------------------------------------------------------------------------
        */

        $this->emailDelivery->send(
            $transaction,
            $eventKey,
            'seller',
            $transaction->seller->email,
            $subject,
            new SellerMarketplaceOrderReceivedMail(
                $transaction
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller-Created Secure Transaction
    |--------------------------------------------------------------------------
    |
    | This is your EXISTING behavior.
    |
    */

    protected function sellerSecureTransactionPayment(
        SecureTransaction $transaction
    ): void {

        if (
            !$transaction->seller
        ) {

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


        /*
        |--------------------------------------------------------------------------
        | Existing Seller Notification
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


                    'transaction_source' =>
                        $transaction->transaction_source,


                    'amount' =>
                        $amount,

                ],

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Email
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Existing Seller Payment Mail
        |--------------------------------------------------------------------------
        */

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


    /*
    |--------------------------------------------------------------------------
    | Buyer Payment Confirmation
    |--------------------------------------------------------------------------
    |
    | Buyer behavior remains unchanged.
    |
    */

    protected function buyer(
        SecureTransaction $transaction
    ): void {

        if (
            !$transaction->buyer
        ) {

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


                    'transaction_source' =>
                        $transaction->transaction_source,


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
    | Admin Notification
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