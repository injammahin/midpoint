<?php

namespace App\Mail;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerPackagePaymentConfirmedMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    public SellerApplication $application;

    public SellerInvoice $invoice;


    public function __construct(
        SellerApplication $application,
        SellerInvoice $invoice
    ) {

        $this->application =
            $application;


        $this->invoice =
            $invoice;


        $this->application
            ->loadMissing([
                'user',
            ]);
    }


    public function build()
    {
        /*
        |--------------------------------------------------------------------------
        | Build PDF Invoice
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'pdf.seller-package-invoice',
                [

                    'user' =>
                        $this->application
                            ->user,

                    'application' =>
                        $this->application,

                    'invoice' =>
                        $this->invoice,

                ]
            )

            ->setPaper(
                'a4',
                'portrait'
            );


        /*
        |--------------------------------------------------------------------------
        | Email + PDF Attachment
        |--------------------------------------------------------------------------
        */

        return $this

            ->subject(
                'Payment confirmed - '
                .
                $this->invoice
                    ->invoice_number
            )

            ->view(
                'emails.seller.payment-confirmed',
                [

                    'user' =>
                        $this->application
                            ->user,

                    'application' =>
                        $this->application,

                    'invoice' =>
                        $this->invoice,

                ]
            )

            ->attachData(

                $pdf->output(),

                $this->invoice
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