<?php

namespace App\Mail;

use App\Models\SecureTransaction;

use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;


class SecureTransactionInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    public SecureTransaction $transaction;


    /*
    |--------------------------------------------------------------------------
    | Seller Name
    |--------------------------------------------------------------------------
    */

    public string $sellerName;


    /*
    |--------------------------------------------------------------------------
    | Secure Link
    |--------------------------------------------------------------------------
    */

    public string $secureUrl;


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        SecureTransaction $transaction
    ) {
        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        $this->transaction =
            $transaction;


        /*
        |--------------------------------------------------------------------------
        | Seller
        |--------------------------------------------------------------------------
        */

        $transaction->loadMissing([
            'seller',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Seller Display Name
        |--------------------------------------------------------------------------
        */

        $this->sellerName =
            $transaction->seller
                ?->name
            ?:
            'Midpoint Seller';


        /*
        |--------------------------------------------------------------------------
        | Secure Transaction URL
        |--------------------------------------------------------------------------
        */

        $this->secureUrl =
            route(
                'secure-transactions.show',
                $transaction
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Email
    |--------------------------------------------------------------------------
    */

    public function build()
    {
        return $this
            ->subject(
                'You have a secure Midpoint transaction - '
                .
                $this->transaction->reference
            )

            ->view(
                'emails.secure-transaction-invitation'
            );
    }
}