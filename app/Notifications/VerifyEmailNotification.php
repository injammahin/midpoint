<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;


    /**
     * Plain verification token.
     */
    protected string $token;


    /**
     * Constructor.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }


    /**
     * Notification channels.
     */
    public function via($notifiable): array
    {
        return [
            'mail',
        ];
    }


    /**
     * Build email.
     */
    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config(
            'verification.expire_minutes',
            5
        );


        /*
        |--------------------------------------------------------------------------
        | Verification URL
        |--------------------------------------------------------------------------
        */

        $verificationUrl = URL::temporarySignedRoute(

            'verification.verify',

            now()->addMinutes(
                $expireMinutes
            ),

            [
                'id' =>
                    $notifiable->getKey(),

                'hash' =>
                    sha1(
                        $notifiable->getEmailForVerification()
                    ),

                'token' =>
                    $this->token,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Custom Email View
        |--------------------------------------------------------------------------
        */

        return (new MailMessage)

            ->subject(
                'Verify your Midpoint email address'
            )

            ->view(
                'emails.verify-email',
                [
                    'user' =>
                        $notifiable,

                    'verificationUrl' =>
                        $verificationUrl,

                    'expireMinutes' =>
                        $expireMinutes,
                ]
            );
    }
}