<?php

namespace App\Notifications;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SellerPaymentReceivedAdminNotification extends Notification
{
    use Queueable;


    protected SellerApplication $application;

    protected SellerInvoice $invoice;


    public function __construct(
        SellerApplication $application,
        SellerInvoice $invoice
    ) {
        $this->application =
            $application;

        $this->invoice =
            $invoice;
    }


    /*
    |--------------------------------------------------------------------------
    | Channel
    |--------------------------------------------------------------------------
    */

    public function via(
        $notifiable
    ): array {

        return [
            'database',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        $notifiable
    ): array {

        return [

            'type' =>
                'seller_payment',

            'title' =>
                'Seller payment received',

            'message' =>
                $this->application
                    ->business_name
                .
                ' paid ₦'
                .
                number_format(
                    (float)
                    $this->invoice
                        ->amount,
                    0
                )
                .
                ' for the '
                .
                $this->application
                    ->package_name
                .
                ' package.',

            'application_id' =>
                $this->application->id,

            'invoice_id' =>
                $this->invoice->id,

            'invoice_number' =>
                $this->invoice
                    ->invoice_number,

            'payment_reference' =>
                $this->invoice
                    ->payment_reference,

            'icon' =>
                'fa-credit-card',

            'url' =>
                route(
                    'admin.website-settings.seller-applications.show',
                    $this->application
                ),

        ];
    }


    public function toArray(
        $notifiable
    ): array {

        return $this->toDatabase(
            $notifiable
        );
    }
}