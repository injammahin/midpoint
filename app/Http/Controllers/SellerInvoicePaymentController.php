<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerInvoicePayment;

use App\Services\PaystackService;
use App\Services\SellerInvoicePaymentService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use RuntimeException;
use Throwable;

class SellerInvoicePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Initialize Paystack Seller Invoice Payment
    |--------------------------------------------------------------------------
    */

    public function initialize(
        Request $request,
        SellerInvoice $invoice,
        PaystackService $paystack
    ) {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Invoice Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $invoice->user_id
            ===
            (int) $user->id,
            403,
            'This seller invoice does not belong to your account.'
        );


        $invoice->loadMissing([

            'application',

            'user',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Already Paid
        |--------------------------------------------------------------------------
        */

        if (
            $invoice->status
            ===
            'paid'
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'success',
                    'This seller invoice has already been paid and your seller package is active.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Application
        |--------------------------------------------------------------------------
        */

        $application =
            $invoice->application;


        abort_unless(
            $application
            &&
            (int) $application->user_id
            ===
            (int) $user->id,
            403,
            'This seller application does not belong to your account.'
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Waiting For Payment
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $application->status
            ===
            SellerApplication::STATUS_PAYMENT_PENDING,
            409,
            'This seller application is not waiting for payment.'
        );


        /*
        |--------------------------------------------------------------------------
        | Existing Checkout Attempt
        |--------------------------------------------------------------------------
        */

        $activeAttempt =
            SellerInvoicePayment::query()

                ->where(
                    'seller_invoice_id',
                    $invoice->id
                )

                ->where(
                    'user_id',
                    $user->id
                )

                ->whereIn(
                    'status',
                    [

                        SellerInvoicePayment::STATUS_INITIALIZED,

                        SellerInvoicePayment::STATUS_PENDING,

                    ]
                )

                ->whereNotNull(
                    'authorization_url'
                )

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Re-use Checkout
        |--------------------------------------------------------------------------
        */

        if (
            $activeAttempt
            &&
            $activeAttempt->authorization_url
        ) {

            return redirect()

                ->away(
                    $activeAttempt
                        ->authorization_url
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        |
        | DATABASE AMOUNT ONLY.
        |
        | Never take this from request input.
        |
        */

        $amount =
            round(
                (float) $invoice->amount,
                2
            );


        $amountSubunit =
            (int) round(
                $amount
                *
                100
            );


        if (
            $amountSubunit <= 0
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'The seller invoice amount is invalid.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        $currency =
            strtoupper(
                $invoice->currency
                ?:
                'NGN'
            );


        /*
        |--------------------------------------------------------------------------
        | Generate Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            $this->generatePaymentReference(
                $invoice
            );


        /*
        |--------------------------------------------------------------------------
        | Create Local Payment Attempt
        |--------------------------------------------------------------------------
        */

        $payment =
            SellerInvoicePayment::create([

                'seller_invoice_id' =>
                    $invoice->id,

                'seller_application_id' =>
                    $application->id,

                'user_id' =>
                    $user->id,

                'provider' =>
                    'paystack',

                'reference' =>
                    $reference,

                'amount' =>
                    $amount,

                'amount_subunit' =>
                    $amountSubunit,

                'currency' =>
                    $currency,

                'status' =>
                    SellerInvoicePayment::STATUS_CREATED,

            ]);


        /*
        |--------------------------------------------------------------------------
        | Initialize Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $paystackData =
                $paystack
                    ->initializeTransaction([

                        /*
                        |--------------------------------------------------------------------------
                        | Customer
                        |--------------------------------------------------------------------------
                        */

                        'email' =>
                            $user->email,


                        /*
                        |--------------------------------------------------------------------------
                        | Amount In Kobo
                        |--------------------------------------------------------------------------
                        */

                        'amount' =>
                            (string) $amountSubunit,


                        /*
                        |--------------------------------------------------------------------------
                        | Currency
                        |--------------------------------------------------------------------------
                        */

                        'currency' =>
                            $currency,


                        /*
                        |--------------------------------------------------------------------------
                        | Unique Reference
                        |--------------------------------------------------------------------------
                        */

                        'reference' =>
                            $reference,


                        /*
                        |--------------------------------------------------------------------------
                        | Callback
                        |--------------------------------------------------------------------------
                        */

                        'callback_url' =>
                            route(
                                'seller-invoices.paystack.callback'
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Metadata
                        |--------------------------------------------------------------------------
                        */

                        'metadata' =>
                            json_encode(
                                [

                                    'payment_type' =>
                                        'seller_package_invoice',

                                    'seller_invoice_id' =>
                                        $invoice->id,

                                    'seller_application_id' =>
                                        $application->id,

                                    'seller_user_id' =>
                                        $user->id,

                                    'invoice_number' =>
                                        $invoice
                                            ->invoice_number,

                                    'application_reference' =>
                                        $application
                                            ->reference,

                                    'business_name' =>
                                        $application
                                            ->business_name,

                                    'package_name' =>
                                        $application
                                            ->package_name,

                                ],
                                JSON_UNESCAPED_SLASHES
                            ),

                    ]);


            /*
            |--------------------------------------------------------------------------
            | Paystack Checkout Response
            |--------------------------------------------------------------------------
            */

            $authorizationUrl =
                $paystackData[
                    'authorization_url'
                ]
                ??
                null;


            $accessCode =
                $paystackData[
                    'access_code'
                ]
                ??
                null;


            /*
            |--------------------------------------------------------------------------
            | Validate Paystack Response
            |--------------------------------------------------------------------------
            */

            if (
                !$authorizationUrl
                ||
                !$accessCode
            ) {

                throw new RuntimeException(
                    'Paystack did not return a valid seller checkout URL.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Save Checkout
            |--------------------------------------------------------------------------
            */

            $payment->update([

                'access_code' =>
                    $accessCode,

                'authorization_url' =>
                    $authorizationUrl,

                'status' =>
                    SellerInvoicePayment::STATUS_INITIALIZED,

                'initialized_at' =>
                    now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirect Seller To Paystack
            |--------------------------------------------------------------------------
            */

            return redirect()

                ->away(
                    $authorizationUrl
                );


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Mark Attempt Failed
            |--------------------------------------------------------------------------
            */

            $payment->update([

                'status' =>
                    SellerInvoicePayment::STATUS_FAILED,

                'gateway_response' =>
                    Str::limit(
                        $exception
                            ->getMessage(),
                        255,
                        ''
                    ),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Paystack seller invoice initialization failed.',
                [

                    'seller_invoice_id' =>
                        $invoice->id,

                    'seller_application_id' =>
                        $application->id,

                    'user_id' =>
                        $user->id,

                    'reference' =>
                        $reference,

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Return
            |--------------------------------------------------------------------------
            */

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'We could not start Paystack checkout. Please try again.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Paystack Callback
    |--------------------------------------------------------------------------
    */

    public function callback(
        Request $request,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            trim(
                (string) (
                    $request->query(
                        'reference'
                    )
                    ?:
                    $request->query(
                        'trxref'
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Missing Reference
        |--------------------------------------------------------------------------
        */

        if (
            $reference === ''
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'Paystack did not return a seller payment reference.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Payment Attempt
        |--------------------------------------------------------------------------
        */

        $payment =
            SellerInvoicePayment::query()

                ->with([

                    'invoice.application',

                    'invoice.user',

                ])

                ->where(
                    'reference',
                    $reference
                )

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Reference
        |--------------------------------------------------------------------------
        */

        if (
            !$payment
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'The returned seller payment reference is not recognized by MidPoint.'
                );
        }


        $invoice =
            $payment->invoice;


        /*
        |--------------------------------------------------------------------------
        | Already Completed
        |--------------------------------------------------------------------------
        */

        if (
            $payment->status
            ===
            SellerInvoicePayment::STATUS_SUCCESS

            &&

            $invoice

            &&

            $invoice->status
            ===
            'paid'
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'success',
                    'Payment already confirmed. Your verified seller package is active.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY WITH PAYSTACK SERVER
        |--------------------------------------------------------------------------
        */

        try {

            $verifiedData =
                $paystack
                    ->verifyTransaction(
                        $reference
                    );


            /*
            |--------------------------------------------------------------------------
            | Process Verified Result
            |--------------------------------------------------------------------------
            */

            $result =
                $sellerPayments
                    ->processVerifiedPayment(
                        $payment,
                        $verifiedData
                    );


            /*
            |--------------------------------------------------------------------------
            | Successful + Email Sent
            |--------------------------------------------------------------------------
            */

            if (
                $result['successful']
            ) {

                if (
                    $result['newly_paid']
                    &&
                    $result['email_sent']
                    ===
                    true
                ) {

                    return redirect()

                        ->route(
                            'verified-sellers'
                        )

                        ->with(
                            'success',
                            'Payment successful. Your seller package is active. A confirmation email with your PDF invoice has been sent to your verified email address.'
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | Successful But Mail Failed
                |--------------------------------------------------------------------------
                */

                if (
                    $result['newly_paid']
                    &&
                    $result['email_sent']
                    ===
                    false
                ) {

                    return redirect()

                        ->route(
                            'verified-sellers'
                        )

                        ->with(
                            'success',
                            'Payment successful and your seller package is active. The confirmation email could not be delivered, so please contact support if you need the invoice resent.'
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Processed By Webhook
                |--------------------------------------------------------------------------
                */

                return redirect()

                    ->route(
                        'verified-sellers'
                    )

                    ->with(
                        'success',
                        'Payment already confirmed. Your verified seller package is active.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Not Successful Yet
            |--------------------------------------------------------------------------
            */

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'Paystack has not confirmed this seller invoice payment as successful yet. If money left your account, do not pay again yet.'
                );


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Verification Error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Paystack seller invoice callback verification failed.',
                [

                    'reference' =>
                        $reference,

                    'seller_invoice_payment_id' =>
                        $payment->id,

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            report(
                $exception
            );


            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'We could not verify this seller payment right now. If you were charged, do not pay again yet.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download Paid Invoice
    |--------------------------------------------------------------------------
    */

    public function download(
        Request $request,
        SellerInvoice $invoice
    ) {

        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $invoice->user_id
            ===
            (int) $request
                ->user()
                ->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Must Be Paid
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $invoice->status
            ===
            'paid',
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $invoice->load([
            'application.user',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView(
            'pdf.seller-package-invoice',
            [

                'user' =>
                    $invoice->user,

                'application' =>
                    $invoice
                        ->application,

                'invoice' =>
                    $invoice,

            ]
        )

            ->setPaper(
                'a4',
                'portrait'
            )

            ->download(
                $invoice
                    ->invoice_number
                .
                '.pdf'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Payment Reference
    |--------------------------------------------------------------------------
    */

    private function generatePaymentReference(
        SellerInvoice $invoice
    ): string {

        do {

            $reference =
                'MP-SINV-'
                .
                $invoice->id
                .
                '-'
                .
                now()->format(
                    'YmdHis'
                )
                .
                '-'
                .
                Str::upper(
                    Str::random(8)
                );


        } while (

            SellerInvoicePayment::query()

                ->where(
                    'reference',
                    $reference
                )

                ->exists()

        );


        return $reference;
    }
}