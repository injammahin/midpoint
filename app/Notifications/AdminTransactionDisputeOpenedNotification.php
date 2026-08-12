<?php

namespace App\Notifications;

use App\Models\SecureTransaction;
use App\Models\TransactionDispute;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminTransactionDisputeOpenedNotification extends Notification
{
    use Queueable;


    protected SecureTransaction $transaction;

    protected TransactionDispute $dispute;


    public function __construct(
        SecureTransaction $transaction,
        TransactionDispute $dispute
    ) {
        $this->transaction =
            $transaction;

        $this->dispute =
            $dispute;
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
    | Database Notification
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        $notifiable
    ): array {

        $buyerName =
            $this->transaction
                ->buyer
                ?->name
            ?:
            $this->transaction
                ->buyer_email
            ?:
            'A buyer';


        return [

            'type' =>
                'transaction_dispute',

            'title' =>
                'New transaction dispute',

            'message' =>
                $buyerName
                .
                ' opened a dispute for '
                .
                $this->transaction
                    ->reference
                .
                '. Seller payout has been paused.',

            'transaction_id' =>
                $this->transaction
                    ->id,

            'transaction_reference' =>
                $this->transaction
                    ->reference,

            'public_token' =>
                $this->transaction
                    ->public_token,

            'dispute_id' =>
                $this->dispute
                    ->id,

            'reason' =>
                $this->dispute
                    ->reason,

            'desired_outcome' =>
                $this->dispute
                    ->desired_outcome,

            'icon' =>
                'fa-triangle-exclamation',

            'url' =>
                route(
                    'admin.disputes.show',
                    $this->dispute
                ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Array
    |--------------------------------------------------------------------------
    */

    public function toArray(
        $notifiable
    ): array {

        return $this->toDatabase(
            $notifiable
        );
    }
}