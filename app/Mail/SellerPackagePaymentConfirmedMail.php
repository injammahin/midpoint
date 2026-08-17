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


        $this
            ->application
            ->loadMissing(
                'user'
            );
    }


    public function build()
    {
        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'pdf.seller-package-invoice',
                [
                    'user' =>
                        $this
                            ->application
                            ->user,

                    'application' =>
                        $this
                            ->application,

                    'invoice' =>
                        $this
                            ->invoice,
                ]
            )
                ->setPaper(
                    'a4',
                    'portrait'
                );


        /*
        |--------------------------------------------------------------------------
        | Subject
        |--------------------------------------------------------------------------
        */

        $subject =
            match (
                $this
                    ->invoice
                    ->purchase_type
            ) {

                SellerInvoice::TYPE_RENEWAL =>
                    'Seller package renewed - ',

                SellerInvoice::TYPE_UPGRADE =>
                    'Seller package upgraded - ',

                SellerInvoice::TYPE_DOWNGRADE =>
                    'Seller package changed - ',

                default =>
                    'Payment confirmed - ',
            };


        return $this

            ->subject(
                $subject
                .
                $this
                    ->invoice
                    ->invoice_number
            )

            ->view(
                'emails.seller.payment-confirmed',
                [
                    'user' =>
                        $this
                            ->application
                            ->user,

                    'application' =>
                        $this
                            ->application,

                    'invoice' =>
                        $this
                            ->invoice,
                ]
            )

            ->attachData(
                $pdf->output(),

                $this
                    ->invoice
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