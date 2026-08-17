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
    public function __construct(
        protected SellerSubscriptionService $subscriptions
    ) {
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

        $status =
            strtolower(
                trim(
                    (string) (
                        $data[
                            'status'
                        ]
                        ??
                        ''
                    )
                )
            );


        $reference =
            trim(
                (string) (
                    $data[
                        'reference'
                    ]
                    ??
                    ''
                )
            );


        $amountSubunit =
            (int) (
                $data[
                    'amount'
                ]
                ??
                0
            );


        $currency =
            strtoupper(
                trim(
                    (string) (
                        $data[
                            'currency'
                        ]
                        ??
                        ''
                    )
                )
            );


        $paystackEmail =
            strtolower(
                trim(
                    (string) (
                        data_get(
                            $data,
                            'customer.email'
                        )
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Load
        |--------------------------------------------------------------------------
        */

        $payment->loadMissing([
            'invoice.application',
            'invoice.user',
        ]);


        $invoice =
            $payment->invoice;


        if (
            !$invoice
        ) {

            throw new RuntimeException(
                'Seller invoice was not found for this payment.'
            );
        }


        $application =
            $invoice
                ->application;


        $user =
            $invoice
                ->user;


        if (
            !$application
        ) {

            throw new RuntimeException(
                'Seller application was not found for this invoice.'
            );
        }


        if (
            !$user
        ) {

            throw new RuntimeException(
                'Seller account was not found for this invoice.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Local Relationships
        |--------------------------------------------------------------------------
        */

        $this
            ->validateRelationships(
                $payment,
                $invoice,
                $application
            );


        /*
        |--------------------------------------------------------------------------
        | Verify Paystack Identity
        |--------------------------------------------------------------------------
        */

        $this
            ->validateGatewayIdentity(
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
            $status
            !==
            'success'
        ) {

            $this
                ->processFailedOrPendingPayment(
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

                'purchase_type' =>
                    $invoice
                        ->purchase_type,
            ];
        }


        $paidAt =
            $this
                ->resolvePaidAt(
                    $data
                );


        /*
        |--------------------------------------------------------------------------
        | Save Gateway Success First
        |--------------------------------------------------------------------------
        */

        $this
            ->persistGatewaySuccess(
                $payment->id,
                $data,
                $paidAt
            );


        /*
        |--------------------------------------------------------------------------
        | Fulfill Invoice
        |--------------------------------------------------------------------------
        */

        $newlyPaid =
            $this
                ->fulfillSuccessfulPayment(
                    $payment->id,
                    $invoice->id,
                    $application->id,
                    $reference,
                    $amountSubunit,
                    $paidAt,
                    $data
                );


        $emailSent =
            null;


        if (
            $newlyPaid
        ) {

            $emailSent =
                $this
                    ->sendCustomerConfirmation(
                        $invoice->id
                    );


            $this
                ->notifyAdmins(
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

            'purchase_type' =>
                $invoice
                    ->purchase_type,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Relationships
    |--------------------------------------------------------------------------
    */

    protected function validateRelationships(
        SellerInvoicePayment $payment,
        SellerInvoice $invoice,
        SellerApplication $application
    ): void {

        if (
            (int)
            $payment
                ->seller_invoice_id
            !==
            (int)
            $invoice
                ->id
        ) {

            throw new RuntimeException(
                'Seller invoice payment relationship is invalid.'
            );
        }


        if (
            (int)
            $payment
                ->seller_application_id
            !==
            (int)
            $application
                ->id
        ) {

            throw new RuntimeException(
                'Seller application payment relationship is invalid.'
            );
        }


        if (
            (int)
            $payment
                ->user_id
            !==
            (int)
            $invoice
                ->user_id
        ) {

            throw new RuntimeException(
                'Seller invoice payment user does not match the invoice owner.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Paystack
    |--------------------------------------------------------------------------
    */

    protected function validateGatewayIdentity(
        SellerInvoicePayment $payment,
        SellerInvoice $invoice,
        User $user,
        string $reference,
        int $amountSubunit,
        string $currency,
        string $paystackEmail
    ): void {

        if (
            $reference === ''
            ||
            !hash_equals(
                (string)
                $payment
                    ->reference,
                $reference
            )
        ) {

            throw new RuntimeException(
                'Paystack reference does not match the Midpoint seller payment reference.'
            );
        }


        if (
            $amountSubunit
            !==
            (int)
            $payment
                ->amount_subunit
        ) {

            throw new RuntimeException(
                'Paystack amount does not match the seller payment amount.'
            );
        }


        $invoiceAmountSubunit =
            (int)
            round(
                (
                    (float)
                    $invoice
                        ->amount
                )
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


        $paymentCurrency =
            strtoupper(
                trim(
                    (string)
                    $payment
                        ->currency
                )
            );


        $invoiceCurrency =
            strtoupper(
                trim(
                    (string) (
                        $invoice
                            ->currency
                        ?:
                        'NGN'
                    )
                )
            );


        if (
            $currency === ''
            ||
            $currency
            !==
            $paymentCurrency
            ||
            $currency
            !==
            $invoiceCurrency
        ) {

            throw new RuntimeException(
                'Paystack currency does not match the seller invoice currency.'
            );
        }


        $localEmail =
            strtolower(
                trim(
                    (string)
                    $user
                        ->email
                )
            );


        if (
            $paystackEmail !== ''
            &&
            $paystackEmail
            !==
            $localEmail
        ) {

            throw new RuntimeException(
                'Paystack customer email does not match the seller account email.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Paid Time
    |--------------------------------------------------------------------------
    */

    protected function resolvePaidAt(
        array $data
    ): Carbon {

        $value =
            $data[
                'paid_at'
            ]

            ??

            $data[
                'paidAt'
            ]

            ??

            null;


        return
            $value

                ? Carbon::parse(
                    $value
                )

                : now();
    }


    /*
    |--------------------------------------------------------------------------
    | Save Gateway Success
    |--------------------------------------------------------------------------
    */

    protected function persistGatewaySuccess(
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
                    isset(
                        $data[
                            'id'
                        ]
                    )

                        ? (string)
                            $data[
                                'id'
                            ]

                        : null,

                'channel' =>
                    $data[
                        'channel'
                    ]
                    ??
                    null,

                'gateway_response' =>
                    $data[
                        'gateway_response'
                    ]
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
    | Fulfill
    |--------------------------------------------------------------------------
    */

    protected function fulfillSuccessfulPayment(
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

                $payment =
                    SellerInvoicePayment::query()

                        ->whereKey(
                            $paymentId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                $invoice =
                    SellerInvoice::query()

                        ->whereKey(
                            $invoiceId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $applicationId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                if (
                    $payment->status
                    !==
                    SellerInvoicePayment::STATUS_SUCCESS
                ) {

                    throw new RuntimeException(
                        'Seller payment is not marked successful locally.'
                    );
                }


                $lockedAmount =
                    (int)
                    round(
                        (
                            (float)
                            $invoice
                                ->amount
                        )
                        *
                        100
                    );


                if (
                    $lockedAmount
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
                | Never reactivate an expired historical subscription merely
                | because an old webhook was delivered again.
                |
                */

                if (
                    $invoice->status
                    ===
                    'paid'
                ) {

                    if (
                        $invoice
                            ->payment_reference
                        &&
                        $invoice
                            ->payment_reference
                        !==
                        $reference
                    ) {

                        Log::critical(
                            'Possible duplicate seller package payment detected.',
                            [
                                'seller_invoice_id' =>
                                    $invoice->id,

                                'existing_reference' =>
                                    $invoice
                                        ->payment_reference,

                                'new_reference' =>
                                    $reference,
                            ]
                        );


                        return false;
                    }


                    /*
                     * Only repair activation if the invoice has NO
                     * subscription record at all.
                     */
                    $subscriptionExists =
                        $invoice
                            ->subscription()
                            ->exists();


                    if (
                        !$subscriptionExists
                    ) {

                        $this
                            ->activateForInvoice(
                                $invoice,
                                $application,
                                $reference
                            );
                    }


                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Application Status
                |--------------------------------------------------------------------------
                */

                if (
                    !$this
                        ->applicationCanPayInvoice(
                            $application,
                            $invoice
                        )
                ) {

                    throw new RuntimeException(
                        'This seller invoice cannot be activated from application status: '
                        .
                        $application
                            ->status
                    );
                }


                $channel =
                    trim(
                        (string) (
                            $data[
                                'channel'
                            ]
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

                $invoice->update([
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
                | Activate Correct Subscription Flow
                |--------------------------------------------------------------------------
                */

                $this
                    ->activateForInvoice(
                        $invoice,
                        $application,
                        $reference
                    );


                return true;
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Application Status Rules
    |--------------------------------------------------------------------------
    */

    protected function applicationCanPayInvoice(
        SellerApplication $application,
        SellerInvoice $invoice
    ): bool {

        if (
            $invoice
                ->isInitialPurchase()
        ) {

            return in_array(
                $application
                    ->status,
                [
                    SellerApplication::STATUS_PAYMENT_PENDING,
                    SellerApplication::STATUS_ACTIVE,
                ],
                true
            );
        }


        /*
         * A renewal invoice is normally paid while the application is
         * expired. ACTIVE is also accepted for safe callback reconciliation.
         */
        return in_array(
            $application
                ->status,
            [
                SellerApplication::STATUS_EXPIRED,
                SellerApplication::STATUS_ACTIVE,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate Subscription For Invoice Type
    |--------------------------------------------------------------------------
    */

    protected function activateForInvoice(
        SellerInvoice $invoice,
        SellerApplication $application,
        string $reference
    ) {

        if (
            $invoice
                ->isInitialPurchase()
        ) {

            return $this
                ->subscriptions
                ->activateFromApplication(
                    $application,
                    $reference,
                    $invoice
                );
        }


        return $this
            ->subscriptions
            ->activateFromRenewalInvoice(
                $invoice,
                $reference
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Failed / Pending
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
                    $data[
                        'gateway_response'
                    ]
                    ??
                    $status,
            ]);


            return;
        }


        $payment->update([
            'status' =>
                SellerInvoicePayment::STATUS_PENDING,

            'verified_at' =>
                now(),

            'gateway_response' =>
                $data[
                    'gateway_response'
                ]
                ??
                $status,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Confirmation Email
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
                $invoice
                    ->application;


            $user =
                $application
                    ?->user;


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
                'Seller package payment confirmation email sent.',
                [
                    'seller_invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $user->id,

                    'purchase_type' =>
                        $invoice
                            ->purchase_type,
                ]
            );


            return true;

        } catch (
            Throwable $exception
        ) {

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

                    ->with(
                        'application'
                    )

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
                $admins
                    ->isNotEmpty()
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