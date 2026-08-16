<?php

namespace App\Mail;

use App\Models\SecureTransaction;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerMarketplaceOrderReceivedMail extends Mailable
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
                'product',
            ]);
    }


    public function build()
    {
        return $this
            ->subject(
                'New marketplace order - '
                .
                $this->transaction
                    ->reference
            )
            ->view(
                'emails.transactions.seller-marketplace-order-received'
            );
    }
}