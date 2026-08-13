<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;


    /*
    |--------------------------------------------------------------------------
    | Password Reset Token
    |--------------------------------------------------------------------------
    */

    protected string $token;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        string $token
    ) {
        $this->token =
            $token;
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
    | Build Email
    |--------------------------------------------------------------------------
    */

    public function toMail(
        $notifiable
    ): MailMessage {

        /*
        |--------------------------------------------------------------------------
        | Password Broker
        |--------------------------------------------------------------------------
        */

        $broker =
            config(
                'auth.defaults.passwords',
                'users'
            );


        /*
        |--------------------------------------------------------------------------
        | Token Expiration
        |--------------------------------------------------------------------------
        |
        | Your current config/auth.php uses 60 minutes.
        |
        */

        $expireMinutes =
            (int)
            config(
                'auth.passwords.'
                .
                $broker
                .
                '.expire',
                60
            );


        /*
        |--------------------------------------------------------------------------
        | Password Reset URL
        |--------------------------------------------------------------------------
        */

        $resetUrl =
            route(
                'password.reset',
                [
                    'token' =>
                        $this->token,

                    'email' =>
                        $notifiable
                            ->getEmailForPasswordReset(),
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Custom MidPoint Template
        |--------------------------------------------------------------------------
        */

        return (new MailMessage)

            ->subject(
                'Reset your MidPoint password'
            )

            ->view(
                'emails.password-reset',
                [
                    'user' =>
                        $notifiable,

                    'resetUrl' =>
                        $resetUrl,

                    'expireMinutes' =>
                        $expireMinutes,
                ]
            );
    }
}