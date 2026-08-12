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
        | Paystack Result
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
        | Load Local Records
        |--------------------------------------------------------------------------
        */

        $payment->loadMissing([

            'invoice.application',

            'invoice.user',

        ]);


        $invoice =
            $payment->invoice;


        if (!$invoice) {

            throw new RuntimeException(
                'Seller invoice was not found for this payment.'
            );
        }


        $application =
            $invoice->application;


        if (!$application) {

            throw new RuntimeException(
                'Seller application was not found for this invoice.'
            );
        }


        $user =
            $invoice->user;


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


        /*
        |--------------------------------------------------------------------------
        | Verify Reference
        |--------------------------------------------------------------------------
        */

        if (
            $reference === ''
            ||
            $reference !== $payment->reference
        ) {

            throw new RuntimeException(
                'Paystack reference does not match the MidPoint seller payment reference.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Amount Against Payment Attempt
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
        | Verify Amount Against Actual Invoice
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
        | Verify Currency
        |--------------------------------------------------------------------------
        */

        if (
            $currency === ''
            ||
            $currency
            !==
            strtoupper(
                (string) $payment->currency
            )
            ||
            $currency
            !==
            strtoupper(
                (string) $invoice->currency
            )
        ) {

            throw new RuntimeException(
                'Paystack currency does not match the seller invoice currency.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Email
        |--------------------------------------------------------------------------
        */

        if (
            $paystackEmail !== ''
            &&
            $paystackEmail
            !==
            strtolower(
                trim(
                    (string) $user->email
                )
            )
        ) {

            throw new RuntimeException(
                'Paystack customer email does not match the seller account email.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pending / Failed
        |--------------------------------------------------------------------------
        */

        if (
            $status
            !==
            'success'
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
        | Finalize Successful Payment
        |--------------------------------------------------------------------------
        */

        $newlyPaid =
            DB::transaction(
                function () use (
                    $payment,
                    $invoice,
                    $application,
                    $data,
                    $reference,
                    $amountSubunit
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Payment
                    |--------------------------------------------------------------------------
                    */

                    $lockedPayment =
                        SellerInvoicePayment::query()

                            ->whereKey(
                                $payment->id
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
                                $invoice->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Application
                    |--------------------------------------------------------------------------
                    */

                    $lockedApplication =
                        SellerApplication::query()

                            ->whereKey(
                                $application->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


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
                            'Seller invoice amount changed while payment was being verified.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Paid Time
                    |--------------------------------------------------------------------------
                    */

                    $paidAtValue =
                        $data['paid_at']
                        ??
                        $data['paidAt']
                        ??
                        null;


                    $paidAt =
                        $paidAtValue

                            ? Carbon::parse(
                                $paidAtValue
                            )

                            : now();


                    /*
                    |--------------------------------------------------------------------------
                    | Save Successful Paystack Payment
                    |--------------------------------------------------------------------------
                    */

                    $lockedPayment->update([

                        'status' =>
                            SellerInvoicePayment::STATUS_SUCCESS,

                        'paystack_transaction_id' =>
                            isset(
                                $data['id']
                            )
                                ? (int) $data['id']
                                : null,

                        'channel' =>
                            $data['channel']
                            ??
                            null,

                        'gateway_response' =>
                            $data['gateway_response']
                            ??
                            null,

                        'verified_at' =>
                            now(),

                        'paid_at' =>
                            $paidAt,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Already Paid
                    |--------------------------------------------------------------------------
                    |
                    | Callback and webhook may arrive very close together.
                    |
                    | Only the first request can activate the account.
                    |
                    */

                    if (
                        $lockedInvoice->status
                        ===
                        'paid'
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Different Reference = Possible Duplicate Payment
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $lockedInvoice
                                ->payment_reference
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
                        }


                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Application Must Be Payment Pending
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedApplication->status
                        !==
                        SellerApplication::STATUS_PAYMENT_PENDING
                    ) {

                        throw new RuntimeException(
                            'This seller application is no longer waiting for payment.'
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
                    | Activate Seller Subscription
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | This uses your project's EXISTING subscription algorithm.
                    |
                    */

                    $this
                        ->subscriptions
                        ->activateFromApplication(
                            $lockedApplication,
                            $reference
                        );


                    return true;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Send Confirmation Only Once
        |--------------------------------------------------------------------------
        */

        $emailSent =
            null;


        if (
            $newlyPaid
        ) {

            /*
            |--------------------------------------------------------------------------
            | Customer Email + PDF Invoice
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
    | Failed / Pending Payment
    |--------------------------------------------------------------------------
    */

    protected function processFailedOrPendingPayment(
        SellerInvoicePayment $payment,
        array $data,
        string $status
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Failed
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

            $payment->update([

                'status' =>
                    SellerInvoicePayment::STATUS_FAILED,

                'verified_at' =>
                    now(),

                'gateway_response' =>
                    $data['gateway_response']
                    ??
                    null,

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
                null,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Confirmation Email + PDF Invoice
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


            /*
            |--------------------------------------------------------------------------
            | Send Mailable
            |--------------------------------------------------------------------------
            */

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

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | NEVER Roll Payment Back Because Mail Server Failed
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
    | Admin Notification
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


            if (
                !$application
            ) {

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

        } catch (
            Throwable $exception
        ) {

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