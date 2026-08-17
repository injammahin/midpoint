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
    | Initialize Seller Package Payment
    |--------------------------------------------------------------------------
    */

    public function initialize(
        Request $request,
        SellerInvoice $invoice,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {
        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Invoice Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $invoice->user_id === (int) $user->id,
            403,
            'This seller invoice does not belong to your account.'
        );


        $invoice->loadMissing([
            'application',
            'user',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Invoice Already Paid
        |--------------------------------------------------------------------------
        */

        if ($invoice->status === 'paid') {

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'success',
                    'This seller invoice has already been paid and your seller package is active.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Application
        |--------------------------------------------------------------------------
        */

        $application = $invoice->application;


        abort_unless(
            $application
            &&
            (int) $application->user_id === (int) $user->id,
            403,
            'This seller application does not belong to your account.'
        );


        /*
        |--------------------------------------------------------------------------
        | Allowed Application Status
        |--------------------------------------------------------------------------
        |
        | active is allowed here because this method can also reconcile an
        | earlier payment where the application activation partially completed.
        |
        */

        if (
            $invoice->isInitialPurchase()
        ) {

            $allowedStatuses = [
                SellerApplication::STATUS_PAYMENT_PENDING,
                SellerApplication::STATUS_ACTIVE,
            ];

        } else {

            /*
            * Renewal does NOT require another admin approval.
            *
            * After expiry the original approved application normally has
            * status = expired.
            */
            $allowedStatuses = [
                SellerApplication::STATUS_EXPIRED,
                SellerApplication::STATUS_ACTIVE,
            ];
        }


        abort_unless(
            in_array(
                $application->status,
                $allowedStatuses,
                true
            ),
            409,
            'This seller invoice is not currently payable.'
        );


        /*
        |--------------------------------------------------------------------------
        | Find Existing Paystack Attempt
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Never simply redirect to an old authorization_url.
        |
        | First ask Paystack what happened to the reference.
        |
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
                        SellerInvoicePayment::STATUS_SUCCESS,
                    ]
                )

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Reconcile Existing Attempt
        |--------------------------------------------------------------------------
        */

        if ($activeAttempt) {

            $reconciled =
                $this->reconcileExistingAttempt(
                    $activeAttempt,
                    $paystack,
                    $sellerPayments
                );


            if ($reconciled !== null) {

                return $reconciled;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $amount =
            round(
                (float) $invoice->amount,
                2
            );


        $amountSubunit =
            (int) round(
                $amount * 100
            );


        if ($amountSubunit <= 0) {

            return redirect()
                ->route('verified-sellers')
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
                (string) (
                    $invoice->currency
                    ?: 'NGN'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Generate Unique Paystack Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            $this->generatePaymentReference(
                $invoice
            );


        /*
        |--------------------------------------------------------------------------
        | Create Local Payment Record BEFORE Calling Paystack
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
                $paystack->initializeTransaction([

                    'email' =>
                        $user->email,

                    /*
                    |--------------------------------------------------------------------------
                    | Paystack Amount In Kobo
                    |--------------------------------------------------------------------------
                    */

                    'amount' =>
                        (string) $amountSubunit,

                    'currency' =>
                        $currency,

                    'reference' =>
                        $reference,

                    /*
                    |--------------------------------------------------------------------------
                    | Seller Package Callback
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
                                    $invoice->invoice_number,

                                'application_reference' =>
                                    $application->reference,

                                'business_name' =>
                                    $application->business_name,

                                'package_name' =>
                                    $invoice->effective_package_name,

                                'purchase_type' =>
                                    $invoice->purchase_type,

                                'renewal_of_subscription_id' =>
                                    $invoice->renewal_of_subscription_id,

                            ],
                            JSON_UNESCAPED_SLASHES
                        ),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Checkout Information
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
            | Log
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Seller Paystack checkout initialized.',
                [

                    'seller_invoice_id' =>
                        $invoice->id,

                    'seller_invoice_payment_id' =>
                        $payment->id,

                    'reference' =>
                        $reference,

                    /*
                    |--------------------------------------------------------------------------
                    | Safe Key Identifier
                    |--------------------------------------------------------------------------
                    |
                    | Does NOT expose the Paystack key.
                    |
                    */

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                    'callback_url' =>
                        route(
                            'seller-invoices.paystack.callback'
                        ),

                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Send User To Paystack
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->away(
                    $authorizationUrl
                );


        } catch (Throwable $exception) {


            /*
            |--------------------------------------------------------------------------
            | Mark Initialization Failed
            |--------------------------------------------------------------------------
            */

            $payment->update([

                'status' =>
                    SellerInvoicePayment::STATUS_FAILED,

                'gateway_response' =>
                    Str::limit(
                        $exception->getMessage(),
                        255,
                        ''
                    ),

            ]);


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

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                    'error' =>
                        $exception->getMessage(),

                ]
            );


            report(
                $exception
            );


            return redirect()
                ->route('verified-sellers')
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
        | Reference Returned By Paystack
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


        if ($reference === '') {

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'Paystack did not return a seller payment reference.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Local Payment
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


        if (!$payment) {

            Log::error(
                'Seller Paystack callback reference not found locally.',
                [

                    'reference' =>
                        $reference,

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                ]
            );


            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'The returned seller payment reference is not recognized by Midpoint.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 1: Verify With Paystack
        |--------------------------------------------------------------------------
        |
        | Keep this separate from local package activation.
        |
        | This means we know whether:
        |
        | 1. Paystack verification failed
        |
        | OR
        |
        | 2. Paystack succeeded but our database activation failed
        |
        */

        try {

            $verifiedData =
                $paystack->verifyTransaction(
                    $reference
                );


            Log::info(
                'Seller Paystack callback verified with gateway.',
                [

                    'reference' =>
                        $reference,

                    'seller_invoice_payment_id' =>
                        $payment->id,

                    'status' =>
                        $verifiedData['status']
                        ??
                        null,

                    'paystack_transaction_id' =>
                        $verifiedData['id']
                        ??
                        null,

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                ]
            );


        } catch (Throwable $exception) {


            Log::error(
                'Paystack seller invoice callback gateway verification failed.',
                [

                    'reference' =>
                        $reference,

                    'seller_invoice_payment_id' =>
                        $payment->id,

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                    'error' =>
                        $exception->getMessage(),

                ]
            );


            report(
                $exception
            );


            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'We could not verify this seller payment with Paystack right now. If you were charged, do not pay again yet.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 2: Save Payment And Activate Package
        |--------------------------------------------------------------------------
        */

        try {

            $result =
                $sellerPayments
                    ->processVerifiedPayment(
                        $payment,
                        $verifiedData
                    );


        } catch (Throwable $exception) {


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | At this point Paystack may already have confirmed SUCCESS.
            |
            | Do NOT tell user to pay again.
            |
            */

            Log::critical(
                'Seller payment verified but package activation failed.',
                [

                    'reference' =>
                        $reference,

                    'seller_invoice_payment_id' =>
                        $payment->id,

                    'paystack_status' =>
                        $verifiedData['status']
                        ??
                        null,

                    'paystack_transaction_id' =>
                        $verifiedData['id']
                        ??
                        null,

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                    'error' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),

                ]
            );


            report(
                $exception
            );


            /*
            |--------------------------------------------------------------------------
            | Gateway Said Success
            |--------------------------------------------------------------------------
            */

            if (
                strtolower(
                    (string) (
                        $verifiedData['status']
                        ??
                        ''
                    )
                )
                ===
                'success'
            ) {

                return redirect()
                    ->route('verified-sellers')
                    ->with(
                        'error',
                        'Your payment was confirmed by Paystack, but package activation could not finish. Do not pay again. Midpoint can safely reconcile this payment.'
                    );
            }


            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'The seller payment could not be processed.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Successful
        |--------------------------------------------------------------------------
        */

        if (
            $result['successful']
        ) {


            /*
            |--------------------------------------------------------------------------
            | New Payment + Email Sent
            |--------------------------------------------------------------------------
            */

            if (
                $result['newly_paid']
                &&
                $result['email_sent']
                ===
                true
            ) {

                return redirect()
                    ->route('verified-sellers')
                    ->with(
                        'success',
                        'Payment successful. Your seller package is active. A confirmation email with your PDF invoice has been sent.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | New Payment + Email Failed
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
                    ->route('verified-sellers')
                    ->with(
                        'success',
                        'Payment successful and your seller package is active. The confirmation email could not be delivered.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Already Processed / Reconciled
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'success',
                    'Payment already confirmed. Your verified seller package is active.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Paystack Has Not Confirmed Success
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('verified-sellers')
            ->with(
                'error',
                'Paystack has not confirmed this seller invoice payment as successful yet. If money left your account, do not pay again yet.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reconcile Existing Payment Attempt
    |--------------------------------------------------------------------------
    |
    | Returns:
    |
    | RedirectResponse = stop initialize flow
    |
    | null = old attempt is terminal/stale, create new transaction
    |
    */

    private function reconcileExistingAttempt(
        SellerInvoicePayment $attempt,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {

        try {

            /*
            |--------------------------------------------------------------------------
            | Ask Paystack About Existing Reference
            |--------------------------------------------------------------------------
            */

            $verifiedData =
                $paystack
                    ->verifyTransaction(
                        $attempt->reference
                    );


            $status =
                strtolower(
                    trim(
                        (string) (
                            $verifiedData['status']
                            ??
                            ''
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Existing Payment Already Successful
            |--------------------------------------------------------------------------
            */

            if (
                $status === 'success'
            ) {

                $result =
                    $sellerPayments
                        ->processVerifiedPayment(
                            $attempt,
                            $verifiedData
                        );


                if (
                    $result['successful']
                ) {

                    return redirect()
                        ->route('verified-sellers')
                        ->with(
                            'success',
                            'Your previous Paystack payment was confirmed and your seller package is now active.'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Failed Paystack Attempt
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $status,
                    [
                        'failed',
                        'abandoned',
                        'reversed',
                    ],
                    true
                )
            ) {

                $attempt->update([

                    'status' =>
                        SellerInvoicePayment::STATUS_FAILED,

                    /*
                    |--------------------------------------------------------------------------
                    | Never Reuse This Checkout URL
                    |--------------------------------------------------------------------------
                    */

                    'authorization_url' =>
                        null,

                    'access_code' =>
                        null,

                    'verified_at' =>
                        now(),

                    'gateway_response' =>
                        $verifiedData[
                            'gateway_response'
                        ]
                        ??
                        $status,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Create New Paystack Transaction
                |--------------------------------------------------------------------------
                */

                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Existing Transaction Still Pending
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $status,
                    [
                        'ongoing',
                        'pending',
                        'processing',
                    ],
                    true
                )
                &&
                $attempt->authorization_url
            ) {

                return redirect()
                    ->away(
                        $attempt
                            ->authorization_url
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Unknown Non-Terminal Status
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'A previous Paystack payment attempt is still being processed. Please do not pay again yet.'
                );


        } catch (Throwable $exception) {


            /*
            |--------------------------------------------------------------------------
            | Detect Old Paystack Integration Reference
            |--------------------------------------------------------------------------
            */

            $message =
                strtolower(
                    $exception
                        ->getMessage()
                );


            $notFound =
                str_contains(
                    $message,
                    'not found'
                )
                ||
                str_contains(
                    $message,
                    'transaction reference'
                )
                ||
                str_contains(
                    $message,
                    'invalid transaction'
                );


            /*
            |--------------------------------------------------------------------------
            | TEST MODE ONLY
            |--------------------------------------------------------------------------
            |
            | If API keys were changed, an old test reference belongs to the old
            | integration. Mark it stale and create a new Paystack transaction.
            |
            */

            if (
                $notFound
                &&
                config(
                    'services.paystack.mode'
                )
                ===
                'test'
            ) {

                Log::warning(
                    'Stale seller Paystack test checkout invalidated.',
                    [

                        'seller_invoice_payment_id' =>
                            $attempt->id,

                        'reference' =>
                            $attempt->reference,

                        'key_fingerprint' =>
                            $paystack
                                ->secretKeyFingerprint(),

                        'error' =>
                            $exception
                                ->getMessage(),

                    ]
                );


                $attempt->update([

                    'status' =>
                        SellerInvoicePayment::STATUS_FAILED,

                    'authorization_url' =>
                        null,

                    'access_code' =>
                        null,

                    'verified_at' =>
                        now(),

                    'gateway_response' =>
                        'Stale Paystack test checkout after integration/key change.',

                ]);


                /*
                |--------------------------------------------------------------------------
                | Allow Fresh Initialization
                |--------------------------------------------------------------------------
                */

                return null;
            }


            /*
            |--------------------------------------------------------------------------
            | Unknown Problem
            |--------------------------------------------------------------------------
            |
            | Do NOT create another payment because the existing one might have
            | succeeded.
            |
            */

            Log::error(
                'Unable to reconcile existing seller Paystack checkout.',
                [

                    'seller_invoice_payment_id' =>
                        $attempt->id,

                    'reference' =>
                        $attempt->reference,

                    'key_fingerprint' =>
                        $paystack
                            ->secretKeyFingerprint(),

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'We could not confirm the previous payment attempt. Please do not pay again until it has been verified.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download Paid Seller Invoice
    |--------------------------------------------------------------------------
    */

    public function download(
        Request $request,
        SellerInvoice $invoice
    ) {

        abort_unless(
            (int) $invoice->user_id
            ===
            (int) $request->user()->id,
            403
        );


        abort_unless(
            $invoice->status
            ===
            'paid',
            404
        );


        $invoice->load([
            'application.user',
        ]);


        return Pdf::loadView(
            'pdf.seller-package-invoice',
            [

                'user' =>
                    $invoice->user,

                'application' =>
                    $invoice->application,

                'invoice' =>
                    $invoice,

            ]
        )

            ->setPaper(
                'a4',
                'portrait'
            )

            ->download(
                $invoice->invoice_number
                .
                '.pdf'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Seller Payment Reference
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