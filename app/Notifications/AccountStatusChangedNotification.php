<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountStatusChangedNotification extends Notification
{
    use Queueable;


    protected bool $active;


    public function __construct(
        bool $active
    ) {
        $this->active = $active;
    }


    public function via($notifiable)
    {
        return [
            'mail',
        ];
    }


    public function toMail($notifiable)
    {
        if (!$this->active) {

            return (new MailMessage)

                ->subject(
                    'Your MidPoint account has been deactivated'
                )

                ->greeting(
                    'Hello ' . $notifiable->name . ','
                )

                ->line(
                    'Your MidPoint account has been deactivated by an administrator.'
                )

                ->line(
                    'You will not be able to access protected MidPoint account features while your account is inactive.'
                )

                ->line(
                    'If you believe this was a mistake, please contact MidPoint support.'
                )

                ->action(
                    'Contact Support',
                    route(
                        'support'
                    )
                );

        }


        return (new MailMessage)

            ->subject(
                'Your MidPoint account is active again'
            )

            ->greeting(
                'Hello ' . $notifiable->name . ','
            )

            ->line(
                'Your MidPoint account has been reactivated.'
            )

            ->action(
                'Log in to MidPoint',
                route(
                    'login'
                )
            );
    }
}