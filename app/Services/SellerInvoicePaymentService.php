<?php

namespace App\Services;

use App\Mail\SellerPackagePaymentConfirmedMail;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerInvoicePayment;
use App\Models\User;

use App\Notifications\SellerPaymentReceivedAdminNotification;

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

use RuntimeException;
use Throwable;

class SellerInvoicePaymentService
{
    protected SellerSubscriptionService $subscriptions;


    public function __construct(
        SellerSubscriptionService $subscriptions
    ) {
        $this->subscriptions =
            $subscriptions;
    }


    /*
    |--------------------------------------------------------------------------
    | Process Verified Paystack Payment
    |--------------------------------------------------------------------------
    */

    public function processVerifiedPayment(
        SellerInvoicePayment $payment,
        array $data
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Paystack Data
        |--------------------------------------------------------------------------
        */

        $status =
            strtolower(
                trim(
                    (string) (
                        $data['status']
                        ??
                        ''
                    )
                )
            );


        $reference =
            trim(
                (string) (
                    $data['reference']
                    ??
                    ''
                )
            );


        $amountSubunit =
            (int) (
                $data['amount']
                ??
                0
            );


        $currency =
            strtoupper(
                trim(
                    (string) (
                        $data['currency']
                        ??
                        ''
                    )
                )
            );


        $paystackEmail =
            strtolower(
                trim(
                    (string) (
                        $data['customer']['email']
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Load Records
        |--------------------------------------------------------------------------
        */

        $payment->loadMissing([

            'invoice.application',

            'invoice.user',

        ]);


        $invoice =
            $payment->invoice;


        $application =
            $invoice
                ? $invoice->application
                : null;


        $user =
            $invoice
                ? $invoice->user
                : null;


        /*
        |--------------------------------------------------------------------------
        | Required Records
        |--------------------------------------------------------------------------
        */

        if (!$invoice) {

            throw new RuntimeException(
                'Seller invoice was not found for this payment.'
            );
        }


        if (!$application) {

            throw new RuntimeException(
                'Seller application was not found for this invoice.'
            );
        }


        if (!$user) {

            throw new RuntimeException(
                'Seller account was not found for this invoice.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Relationships
        |--------------------------------------------------------------------------
        */

        $this->validateRelationships(
            $payment,
            $invoice,
            $application
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Paystack Payment
        |--------------------------------------------------------------------------
        */

        $this->validateGatewayIdentity(

            $payment,

            $invoice,

            $user,

            $reference,

            $amountSubunit,

            $currency,

            $paystackEmail

        );


        /*
        |--------------------------------------------------------------------------
        | Not Successful
        |--------------------------------------------------------------------------
        */

        if (
            $status !== 'success'
        ) {

            $this->processFailedOrPendingPayment(
                $payment,
                $data,
                $status
            );


            return [

                'successful' =>
                    false,

                'newly_paid' =>
                    false,

                'email_sent' =>
                    false,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Paystack Paid Time
        |--------------------------------------------------------------------------
        */

        $paidAt =
            $this->resolvePaidAt(
                $data
            );


        /*
        |--------------------------------------------------------------------------
        | STEP 1
        |
        | PERMANENTLY SAVE PAYSTACK SUCCESS
        |--------------------------------------------------------------------------
        |
        | This intentionally happens OUTSIDE the package activation transaction.
        |
        | If subscription creation fails afterward, Midpoint still remembers
        | that Paystack received the payment.
        |
        */

        $this->persistGatewaySuccess(
            $payment->id,
            $data,
            $paidAt
        );


        /*
        |--------------------------------------------------------------------------
        | STEP 2
        |
        | Activate Invoice + Seller Package
        |--------------------------------------------------------------------------
        */

        $newlyPaid =
            $this->fulfillSuccessfulPayment(

                $payment->id,

                $invoice->id,

                $application->id,

                $reference,

                $amountSubunit,

                $paidAt,

                $data

            );


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $emailSent =
            null;


        if (
            $newlyPaid
        ) {

            /*
            |--------------------------------------------------------------------------
            | Customer Confirmation
            |--------------------------------------------------------------------------
            */

            $emailSent =
                $this->sendCustomerConfirmation(
                    $invoice->id
                );


            /*
            |--------------------------------------------------------------------------
            | Admin Notification
            |--------------------------------------------------------------------------
            */

            $this->notifyAdmins(
                $invoice->id
            );
        }


        return [

            'successful' =>
                true,

            'newly_paid' =>
                $newlyPaid,

            'email_sent' =>
                $emailSent,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Local Relationships
    |--------------------------------------------------------------------------
    */

    private function validateRelationships(
        SellerInvoicePayment $payment,
        SellerInvoice $invoice,
        SellerApplication $application
    ): void {

        if (
            (int) $payment->seller_invoice_id
            !==
            (int) $invoice->id
        ) {

            throw new RuntimeException(
                'Seller invoice payment relationship is invalid.'
            );
        }


        if (
            (int) $payment->seller_application_id
            !==
            (int) $application->id
        ) {

            throw new RuntimeException(
                'Seller application payment relationship is invalid.'
            );
        }


        if (
            (int) $payment->user_id
            !==
            (int) $invoice->user_id
        ) {

            throw new RuntimeException(
                'Seller invoice payment user does not match the invoice owner.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Paystack Identity
    |--------------------------------------------------------------------------
    */

    private function validateGatewayIdentity(
        SellerInvoicePayment $payment,
        SellerInvoice $invoice,
        User $user,
        string $reference,
        int $amountSubunit,
        string $currency,
        string $paystackEmail
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        if (
            $reference === ''
            ||
            !hash_equals(
                (string) $payment->reference,
                $reference
            )
        ) {

            throw new RuntimeException(
                'Paystack reference does not match the Midpoint seller payment reference.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Amount Against Payment Attempt
        |--------------------------------------------------------------------------
        */

        if (
            $amountSubunit
            !==
            (int) $payment->amount_subunit
        ) {

            throw new RuntimeException(
                'Paystack amount does not match the seller payment amount.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Amount Against Invoice
        |--------------------------------------------------------------------------
        */

        $invoiceAmountSubunit =
            (int) round(
                ((float) $invoice->amount)
                *
                100
            );


        if (
            $amountSubunit
            !==
            $invoiceAmountSubunit
        ) {

            throw new RuntimeException(
                'Paystack amount does not match the seller invoice amount.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        $paymentCurrency =
            strtoupper(
                trim(
                    (string) $payment->currency
                )
            );


        $invoiceCurrency =
            strtoupper(
                trim(
                    (string) (
                        $invoice->currency
                        ?: 'NGN'
                    )
                )
            );


        if (
            $currency === ''
            ||
            $currency !== $paymentCurrency
            ||
            $currency !== $invoiceCurrency
        ) {

            throw new RuntimeException(
                'Paystack currency does not match the seller invoice currency.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $localEmail =
            strtolower(
                trim(
                    (string) $user->email
                )
            );


        if (
            $paystackEmail !== ''
            &&
            $paystackEmail !== $localEmail
        ) {

            throw new RuntimeException(
                'Paystack customer email does not match the seller account email.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Paid Time
    |--------------------------------------------------------------------------
    */

    private function resolvePaidAt(
        array $data
    ): Carbon {

        $paidAtValue =
            $data['paid_at']
            ??
            $data['paidAt']
            ??
            null;


        return $paidAtValue

            ? Carbon::parse(
                $paidAtValue
            )

            : now();
    }


    /*
    |--------------------------------------------------------------------------
    | PERMANENTLY SAVE PAYSTACK SUCCESS
    |--------------------------------------------------------------------------
    |
    | This update is intentionally committed BEFORE package activation.
    |
    */

    private function persistGatewaySuccess(
        int $paymentId,
        array $data,
        Carbon $paidAt
    ): void {

        SellerInvoicePayment::query()

            ->whereKey(
                $paymentId
            )

            ->update([

                'status' =>
                    SellerInvoicePayment::STATUS_SUCCESS,

                'paystack_transaction_id' =>
                    isset($data['id'])
                        ? (string) $data['id']
                        : null,

                'channel' =>
                    $data['channel']
                    ??
                    null,

                'gateway_response' =>
                    $data['gateway_response']
                    ??
                    'Successful',

                'verified_at' =>
                    now(),

                'paid_at' =>
                    $paidAt,

            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Fulfill Successful Seller Package Payment
    |--------------------------------------------------------------------------
    */

    private function fulfillSuccessfulPayment(
        int $paymentId,
        int $invoiceId,
        int $applicationId,
        string $reference,
        int $amountSubunit,
        Carbon $paidAt,
        array $data
    ): bool {

        return DB::transaction(
            function () use (
                $paymentId,
                $invoiceId,
                $applicationId,
                $reference,
                $amountSubunit,
                $paidAt,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Payment
                |--------------------------------------------------------------------------
                */

                $lockedPayment =
                    SellerInvoicePayment::query()

                        ->whereKey(
                            $paymentId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Lock Invoice
                |--------------------------------------------------------------------------
                */

                $lockedInvoice =
                    SellerInvoice::query()

                        ->whereKey(
                            $invoiceId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Lock Seller Application
                |--------------------------------------------------------------------------
                */

                $lockedApplication =
                    SellerApplication::query()

                        ->whereKey(
                            $applicationId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Payment Must Already Be Saved As Success
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedPayment->status
                    !==
                    SellerInvoicePayment::STATUS_SUCCESS
                ) {

                    throw new RuntimeException(
                        'Seller payment is not marked successful locally.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Re-check Invoice Amount
                |--------------------------------------------------------------------------
                */

                $lockedInvoiceAmountSubunit =
                    (int) round(
                        ((float) $lockedInvoice->amount)
                        *
                        100
                    );


                if (
                    $lockedInvoiceAmountSubunit
                    !==
                    $amountSubunit
                ) {

                    throw new RuntimeException(
                        'Seller invoice amount changed while payment was being fulfilled.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Already Paid
                |--------------------------------------------------------------------------
                |
                | Callback and webhook can both run.
                |
                | This also repairs a situation where invoice was paid but
                | subscription activation was not completed.
                |
                */

                if (
                    $lockedInvoice->status
                    ===
                    'paid'
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Different Payment Reference
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedInvoice->payment_reference
                        &&
                        $lockedInvoice->payment_reference
                        !==
                        $reference
                    ) {

                        Log::critical(
                            'Possible duplicate seller package payment detected.',
                            [

                                'seller_invoice_id' =>
                                    $lockedInvoice->id,

                                'existing_reference' =>
                                    $lockedInvoice
                                        ->payment_reference,

                                'new_reference' =>
                                    $reference,

                            ]
                        );


                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Ensure Subscription Exists
                    |--------------------------------------------------------------------------
                    */

                    $this->subscriptions
                        ->activateFromApplication(
                            $lockedApplication,
                            $reference
                        );


                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Seller Application Status
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $lockedApplication->status,
                        [
                            SellerApplication::STATUS_PAYMENT_PENDING,
                            SellerApplication::STATUS_ACTIVE,
                        ],
                        true
                    )
                ) {

                    throw new RuntimeException(
                        'This seller application cannot be activated from the current status: '
                        .
                        $lockedApplication->status
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Payment Method
                |--------------------------------------------------------------------------
                */

                $channel =
                    trim(
                        (string) (
                            $data['channel']
                            ??
                            ''
                        )
                    );


                $paymentMethod =
                    $channel !== ''

                        ? 'paystack_'
                            .
                            $channel

                        : 'paystack';


                /*
                |--------------------------------------------------------------------------
                | Mark Invoice Paid
                |--------------------------------------------------------------------------
                */

                $lockedInvoice->update([

                    'status' =>
                        'paid',

                    'payment_method' =>
                        $paymentMethod,

                    'payment_reference' =>
                        $reference,

                    'paid_at' =>
                        $paidAt,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Create / Activate Subscription
                |--------------------------------------------------------------------------
                */

                $this->subscriptions
                    ->activateFromApplication(
                        $lockedApplication,
                        $reference
                    );


                return true;

            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Failed / Pending Paystack Result
    |--------------------------------------------------------------------------
    */

    protected function processFailedOrPendingPayment(
        SellerInvoicePayment $payment,
        array $data,
        string $status
    ): void {

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

            $payment->update([

                'status' =>
                    SellerInvoicePayment::STATUS_FAILED,

                'verified_at' =>
                    now(),

                'gateway_response' =>
                    $data['gateway_response']
                    ??
                    $status,

            ]);


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Pending
        |--------------------------------------------------------------------------
        */

        $payment->update([

            'status' =>
                SellerInvoicePayment::STATUS_PENDING,

            'verified_at' =>
                now(),

            'gateway_response' =>
                $data['gateway_response']
                ??
                $status,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Confirmation Email
    |--------------------------------------------------------------------------
    */

    protected function sendCustomerConfirmation(
        int $invoiceId
    ): bool {

        try {

            $invoice =
                SellerInvoice::query()

                    ->with([
                        'application.user',
                    ])

                    ->findOrFail(
                        $invoiceId
                    );


            $application =
                $invoice->application;


            $user =
                $application
                    ? $application->user
                    : null;


            if (
                !$application
                ||
                !$user
                ||
                empty(
                    $user->email
                )
            ) {

                throw new RuntimeException(
                    'Seller payment confirmation email recipient is missing.'
                );
            }


            Mail::to(
                $user->email
            )->send(

                new SellerPackagePaymentConfirmedMail(
                    $application,
                    $invoice
                )

            );


            Log::info(
                'Seller package payment confirmation email sent with PDF invoice.',
                [

                    'seller_application_id' =>
                        $application->id,

                    'seller_invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $user->id,

                    'email' =>
                        $user->email,

                    'payment_reference' =>
                        $invoice
                            ->payment_reference,

                ]
            );


            return true;


        } catch (Throwable $exception) {


            /*
            |--------------------------------------------------------------------------
            | NEVER Roll Back Successful Payment Because Email Failed
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Seller package payment confirmation email failed.',
                [

                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            report(
                $exception
            );


            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Notify Admins
    |--------------------------------------------------------------------------
    */

    protected function notifyAdmins(
        int $invoiceId
    ): void {

        try {

            $invoice =
                SellerInvoice::query()

                    ->with([
                        'application',
                    ])

                    ->findOrFail(
                        $invoiceId
                    );


            $application =
                $invoice
                    ->application;


            if (!$application) {

                return;
            }


            $admins =
                User::query()

                    ->where(
                        'role',
                        'admin'
                    )

                    ->where(
                        'status',
                        true
                    )

                    ->get();


            if (
                $admins->isNotEmpty()
            ) {

                Notification::send(

                    $admins,

                    new SellerPaymentReceivedAdminNotification(
                        $application,
                        $invoice
                    )

                );
            }


        } catch (Throwable $exception) {


            Log::error(
                'Admin seller package payment notification failed.',
                [

                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            report(
                $exception
            );
        }
    }
}