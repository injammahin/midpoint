<?php

namespace App\Notifications;

use App\Models\SellerApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApplicationSubmittedNotification extends Notification
{
    use Queueable;


    public SellerApplication $application;


    public function __construct(
        SellerApplication $application
    ) {
        $this->application =
            $application;
    }


    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    */

    public function via(
        $notifiable
    ): array {

        return [
            'mail',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Mail
    |--------------------------------------------------------------------------
    */

    public function toMail(
        $notifiable
    ): MailMessage {

        return (new MailMessage)

            ->subject(
                'Seller Application Received - MidPoint'
            )

            ->view(
                'emails.seller-application-submitted',
                [
                    'user' =>
                        $notifiable,

                    'application' =>
                        $this->application,
                ]
            );
    }
}