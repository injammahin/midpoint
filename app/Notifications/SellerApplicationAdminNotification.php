<?php

namespace App\Notifications;

use App\Models\SellerApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SellerApplicationAdminNotification extends Notification
{
    use Queueable;


    protected SellerApplication $application;


    public function __construct(
        SellerApplication $application
    ) {
        $this->application =
            $application;
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
                'seller_application',

            'title' =>
                'New seller application',

            'message' =>
                $this->application
                    ->business_name
                .
                ' applied for the '
                .
                $this->application
                    ->package_name
                .
                ' seller package.',

            'seller_application_id' =>
                $this->application->id,

            'reference' =>
                $this->application
                    ->reference,

            'user_id' =>
                $this->application
                    ->user_id,

            'icon' =>
                'fa-file-signature',

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