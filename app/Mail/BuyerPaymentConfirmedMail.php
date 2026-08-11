<?php

namespace App\Mail;

use App\Models\SecureTransaction;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuyerPaymentConfirmedMail extends Mailable
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
        $pdf =
            Pdf::loadView(
                'pdf.transaction-payment-invoice',
                [
                    'transaction' =>
                        $this->transaction,
                ]
            )
                ->setPaper(
                    'a4',
                    'portrait'
                );

        return $this
            ->subject(
                'Payment confirmed - '
                .
                $this->transaction
                    ->invoice_number
            )
            ->view(
                'emails.transactions.buyer-payment-confirmed'
            )
            ->attachData(
                $pdf->output(),
                $this->transaction
                    ->invoice_number
                .
                '.pdf',
                [
                    'mime' =>
                        'application/pdf',
                ]
            );
    }
}