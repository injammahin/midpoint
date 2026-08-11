<?php

namespace App\Mail;

use App\Models\SecureTransaction;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerPaymentReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public SecureTransaction $transaction;

    public function __construct(
        SecureTransaction $transaction
    ) {
        $this->transaction =
            $transaction;

        $this->transaction
            ->loadMissing([
                'seller',
                'buyer',
            ]);
    }

    public function build()
    {
        return $this
            ->subject(
                'Buyer payment secured - '
                .
                $this->transaction
                    ->reference
            )
            ->view(
                'emails.transactions.seller-payment-received'
            );
    }
}