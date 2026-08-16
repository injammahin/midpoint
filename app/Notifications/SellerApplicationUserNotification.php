<?php

namespace App\Notifications;

use App\Models\SellerApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApplicationUserNotification extends Notification
{
    use Queueable;


    public SellerApplication $application;

    public string $type;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        SellerApplication $application,
        string $type
    ) {
        $this->application =
            $application;

        $this->type =
            $type;
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Channel
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
    | Email
    |--------------------------------------------------------------------------
    */

    public function toMail(
        $notifiable
    ): MailMessage {
        /*
        |--------------------------------------------------------------------------
        | Load Invoice
        |--------------------------------------------------------------------------
        */

        $this->application->loadMissing([
            'invoice',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Revision Required
        |--------------------------------------------------------------------------
        */

        if (
            $this->type
            ===
            'revision_required'
        ) {
            return (new MailMessage)

                ->subject(
                    'Action Required: Revise Your Midpoint Seller Application'
                )

                ->view(
                    'emails.seller.revision-required',
                    [
                        'user' =>
                            $notifiable,

                        'application' =>
                            $this->application,
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Approved + Invoice
        |--------------------------------------------------------------------------
        */

        if (
            $this->type
            ===
            'approved'
        ) {
            return (new MailMessage)

                ->subject(
                    'Approved! Your Midpoint Seller Invoice Is Ready'
                )

                ->view(
                    'emails.seller.approved-invoice',
                    [
                        'user' =>
                            $notifiable,

                        'application' =>
                            $this->application,

                        'invoice' =>
                            $this->application
                                ->invoice,
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Successful
        |--------------------------------------------------------------------------
        */

        if (
            $this->type
            ===
            'payment_successful'
        ) {
            return (new MailMessage)

                ->subject(
                    'Payment Confirmed - Your Midpoint Seller Account Is Active'
                )

                ->view(
                    'emails.seller.payment-confirmed',
                    [
                        'user' =>
                            $notifiable,

                        'application' =>
                            $this->application,

                        'invoice' =>
                            $this->application
                                ->invoice,
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return (new MailMessage)

            ->subject(
                'Midpoint Seller Application Update'
            )

            ->line(
                'There has been an update to your seller application.'
            )

            ->action(
                'View Application',
                route(
                    'verified-sellers'
                )
            );
    }
}