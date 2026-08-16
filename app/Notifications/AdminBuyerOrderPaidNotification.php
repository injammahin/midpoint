<?php

namespace App\Notifications;

use App\Models\SecureTransaction;

use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Notification;


class AdminBuyerOrderPaidNotification extends Notification
{
    use Queueable;


    public function __construct(
        protected SecureTransaction $transaction
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

        return [

            'type' =>
                'buyer_order_paid',


            'title' =>
                'New paid marketplace order',


            'message' =>

                (
                    $this
                        ->transaction
                        ->buyer
                        ?->name

                    ?:

                    $this
                        ->transaction
                        ->buyer_email
                )

                .

                ' paid ₦'

                .

                number_format(
                    (float) (
                        $this
                            ->transaction
                            ->paid_amount

                        ?:

                        $this
                            ->transaction
                            ->total_amount
                    ),
                    2
                )

                .

                ' for '

                .

                $this
                    ->transaction
                    ->title

                .

                '.',


            'secure_transaction_id' =>
                $this
                    ->transaction
                    ->id,


            'reference' =>
                $this
                    ->transaction
                    ->reference,


            'public_token' =>
                $this
                    ->transaction
                    ->public_token,


            'icon' =>
                'fa-cart-shopping',


            'url' =>
                route(
                    'admin.transactions.show',
                    [

                        'secureTransaction' =>
                            $this
                                ->transaction
                                ->public_token,

                    ]
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