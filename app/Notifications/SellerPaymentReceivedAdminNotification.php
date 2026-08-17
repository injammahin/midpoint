<?php

namespace App\Notifications;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SellerPaymentReceivedAdminNotification extends Notification
{
    use Queueable;


    public function __construct(
        protected SellerApplication $application,
        protected SellerInvoice $invoice
    ) {
    }


    public function via(
        $notifiable
    ): array {

        return [
            'database',
        ];
    }


    public function toDatabase(
        $notifiable
    ): array {

        $title =
            match (
                $this
                    ->invoice
                    ->purchase_type
            ) {

                SellerInvoice::TYPE_RENEWAL =>
                    'Seller package renewed',

                SellerInvoice::TYPE_UPGRADE =>
                    'Seller package upgraded',

                SellerInvoice::TYPE_DOWNGRADE =>
                    'Seller changed package',

                default =>
                    'Seller payment received',
            };


        $action =
            match (
                $this
                    ->invoice
                    ->purchase_type
            ) {

                SellerInvoice::TYPE_RENEWAL =>
                    'renewed',

                SellerInvoice::TYPE_UPGRADE =>
                    'upgraded to',

                SellerInvoice::TYPE_DOWNGRADE =>
                    'changed to',

                default =>
                    'purchased',
            };


        return [
            'type' =>
                'seller_payment',

            'title' =>
                $title,

            'message' =>
                $this
                    ->application
                    ->business_name
                .
                ' '
                .
                $action
                .
                ' the '
                .
                $this
                    ->invoice
                    ->effective_package_name
                .
                ' package for ₦'
                .
                number_format(
                    (float)
                    $this
                        ->invoice
                        ->amount,
                    0
                )
                .
                '.',

            'purchase_type' =>
                $this
                    ->invoice
                    ->purchase_type,

            'application_id' =>
                $this
                    ->application
                    ->id,

            'invoice_id' =>
                $this
                    ->invoice
                    ->id,

            'invoice_number' =>
                $this
                    ->invoice
                    ->invoice_number,

            'payment_reference' =>
                $this
                    ->invoice
                    ->payment_reference,

            'icon' =>
                'fa-crown',

            /*
             * Admin lands on purchase history where the renewal
             * is clearly visible.
             */
            'url' =>
                route(
                    'admin.billing.subscriptions.index'
                ),
        ];
    }


    public function toArray(
        $notifiable
    ): array {

        return $this
            ->toDatabase(
                $notifiable
            );
    }
}