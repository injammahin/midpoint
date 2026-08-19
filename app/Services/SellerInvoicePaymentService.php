<?php

namespace App\Services;

use App\Mail\SellerPackagePaymentConfirmedMail;
use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerInvoicePayment;
use App\Models\TransactionNotification;
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
        | Load Invoice Relationships
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


        $user =
            $invoice->user;


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
        | Validate Local Relationships
        |--------------------------------------------------------------------------
        */

        $this->validateRelationships(
            $payment,
            $invoice,
            $application
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Paystack Identity
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
        | Paystack Has Not Confirmed Success
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

                'seller_notified' =>
                    false,

                'purchase_type' =>
                    $invoice->purchase_type,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Paid Time
        |--------------------------------------------------------------------------
        */

        $paidAt =
            $this->resolvePaidAt(
                $data
            );


        /*
        |--------------------------------------------------------------------------
        | Save Paystack Success
        |--------------------------------------------------------------------------
        |
        | Paystack has now confirmed the money.
        |
        */

        $this->persistGatewaySuccess(
            $payment->id,
            $data,
            $paidAt
        );


        /*
        |--------------------------------------------------------------------------
        | Fulfill Invoice + Activate Seller Package
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


        $emailSent =
            null;


        $sellerNotified =
            null;


        /*
        |--------------------------------------------------------------------------
        | Post-Payment Communications
        |--------------------------------------------------------------------------
        |
        | CRITICAL:
        |
        | At this point the financial payment and package activation have
        | already completed.
        |
        | An email or notification failure MUST NOT cause:
        |
        | "Payment could not be completed"
        |
        */

        if (
            $newlyPaid
        ) {

            try {

                $communications =
                    $this
                        ->sendSuccessfulPaymentCommunications(
                            $invoice->id
                        );


                $emailSent =
                    (bool) (
                        $communications['email_sent']
                        ??
                        false
                    );


                $sellerNotified =
                    (bool) (
                        $communications['seller_notified']
                        ??
                        false
                    );


            } catch (
                Throwable $exception
            ) {

                Log::error(
                    'Seller package payment succeeded but post-payment communications failed.',
                    [
                        'seller_invoice_id' =>
                            $invoice->id,

                        'reference' =>
                            $reference,

                        'error' =>
                            $exception->getMessage(),
                    ]
                );


                report(
                    $exception
                );


                /*
                 * Payment/package activation remain successful.
                 */

                $emailSent =
                    false;


                $sellerNotified =
                    false;
            }
        }


        return [
            'successful' =>
                true,

            'newly_paid' =>
                $newlyPaid,

            'email_sent' =>
                $emailSent,

            'seller_notified' =>
                $sellerNotified,

            'purchase_type' =>
                $invoice->purchase_type,
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Fulfill Midpoint Wallet / Alternative Payment
    |--------------------------------------------------------------------------
    |
    | Wallet deduction itself is handled by SellerPackageWalletPaymentService.
    |
    | This method handles:
    |
    | invoice paid
    | subscription activation
    |
    */

    public function fulfillAlternativePayment(
        SellerInvoice $invoice,
        string $paymentMethod,
        string $reference,
        ?Carbon $paidAt = null
    ): bool {

        $paidAt =
            $paidAt
            ?:
            now();


        return DB::transaction(
            function () use (
                $invoice,
                $paymentMethod,
                $reference,
                $paidAt
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Invoice
                |--------------------------------------------------------------------------
                */

                $invoice =
                    SellerInvoice::query()

                        ->whereKey(
                            $invoice->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Lock Seller Application
                |--------------------------------------------------------------------------
                */

                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $invoice->seller_application_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Initial Seller Package Cannot Use Wallet
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice->isInitialPurchase()
                ) {

                    throw new RuntimeException(
                        'Initial seller package payment must use Paystack.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Downgrade Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice->purchase_type
                    ===
                    SellerInvoice::TYPE_DOWNGRADE
                ) {

                    throw new RuntimeException(
                        'Seller package downgrades are not allowed.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Already Paid
                |--------------------------------------------------------------------------
                |
                | Makes wallet payment idempotent.
                |
                */

                if (
                    $invoice->status
                    ===
                    'paid'
                ) {

                    /*
                     * Different payment reference means somebody is trying
                     * to fulfill an already-paid invoice with another payment.
                     */

                    if (
                        $invoice->payment_reference
                        &&
                        $invoice->payment_reference
                        !==
                        $reference
                    ) {

                        throw new RuntimeException(
                            'This seller invoice has already been paid using another payment reference.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Repair Missing Subscription
                    |--------------------------------------------------------------------------
                    |
                    | Invoice is paid but subscription somehow does not exist.
                    |
                    */

                    if (
                        !$invoice
                            ->subscription()
                            ->exists()
                    ) {

                        $this->activateForInvoice(
                            $invoice,
                            $application,
                            $reference
                        );
                    }


                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Must Still Be Unpaid
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice->status
                    !==
                    'unpaid'
                ) {

                    throw new RuntimeException(
                        'This seller invoice is no longer payable.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Application Status
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->applicationCanPayInvoice(
                        $application,
                        $invoice
                    )
                ) {

                    throw new RuntimeException(
                        'This seller invoice cannot be activated from application status: '
                        .
                        $application->status
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Payment Values
                |--------------------------------------------------------------------------
                */

                $paymentMethod =
                    trim(
                        $paymentMethod
                    );


                $reference =
                    trim(
                        $reference
                    );


                if (
                    $paymentMethod === ''
                    ||
                    $reference === ''
                ) {

                    throw new RuntimeException(
                        'Payment method and reference are required to fulfill the seller invoice.'
                    );
                }


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
                | Activate Package
                |--------------------------------------------------------------------------
                */

                $this->activateForInvoice(
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
    | Successful Payment Communications
    |--------------------------------------------------------------------------
    |
    | Every communication is isolated.
    |
    | NOTHING inside this method is allowed to cause a successful payment
    | to be treated as unsuccessful.
    |
    */

    public function sendSuccessfulPaymentCommunications(
        int $invoiceId
    ): array {

        $sellerNotified =
            false;


        $emailSent =
            false;


        /*
        |--------------------------------------------------------------------------
        | Seller Dashboard Notification
        |--------------------------------------------------------------------------
        */

        try {

            $sellerNotified =
                $this
                    ->notifySellerInApp(
                        $invoiceId
                    );


        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller package dashboard notification failed after successful payment.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Email + PDF Invoice
        |--------------------------------------------------------------------------
        */

        try {

            $emailSent =
                $this
                    ->sendCustomerConfirmation(
                        $invoiceId
                    );


        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller package confirmation email failed after successful payment.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );


            $emailSent =
                false;
        }


        /*
        |--------------------------------------------------------------------------
        | Admin Notification
        |--------------------------------------------------------------------------
        */

        try {

            $this
                ->notifyAdmins(
                    $invoiceId
                );


        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller package admin notification failed after successful payment.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Never Throw
        |--------------------------------------------------------------------------
        */

        return [
            'seller_notified' =>
                $sellerNotified,

            'email_sent' =>
                $emailSent,
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Validate Local Relationships
    |--------------------------------------------------------------------------
    */

    protected function validateRelationships(
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

    protected function validateGatewayIdentity(
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
        | Payment Amount
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
        | Invoice Amount
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
                        ?:
                        'NGN'
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
        | Seller Email
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

    protected function resolvePaidAt(
        array $data
    ): Carbon {

        $value =
            $data['paid_at']
            ??
            $data['paidAt']
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
    | Persist Gateway Success
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
                        $data['id']
                    )

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
    | Fulfill Verified Paystack Payment
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

                /*
                |--------------------------------------------------------------------------
                | Lock Payment
                |--------------------------------------------------------------------------
                */

                $payment =
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

                $invoice =
                    SellerInvoice::query()

                        ->whereKey(
                            $invoiceId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Lock Application
                |--------------------------------------------------------------------------
                */

                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $applicationId
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Local Payment Must Be Success
                |--------------------------------------------------------------------------
                */

                if (
                    $payment->status
                    !==
                    SellerInvoicePayment::STATUS_SUCCESS
                ) {

                    throw new RuntimeException(
                        'Seller payment is not marked successful locally.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Re-check Amount Under Lock
                |--------------------------------------------------------------------------
                */

                $lockedAmount =
                    (int) round(
                        ((float) $invoice->amount)
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
                | Paystack callbacks/webhooks may arrive more than once.
                |
                */

                if (
                    $invoice->status
                    ===
                    'paid'
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Different Payment Reference
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice->payment_reference
                        &&
                        $invoice->payment_reference
                        !==
                        $reference
                    ) {

                        Log::critical(
                            'Possible duplicate seller package payment detected.',
                            [
                                'seller_invoice_id' =>
                                    $invoice->id,

                                'existing_reference' =>
                                    $invoice->payment_reference,

                                'new_reference' =>
                                    $reference,
                            ]
                        );


                        /*
                         * Do not attempt activation/payment again.
                         */

                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Repair Missing Subscription Only
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$invoice
                            ->subscription()
                            ->exists()
                    ) {

                        $this->activateForInvoice(
                            $invoice,
                            $application,
                            $reference
                        );
                    }


                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Must Be Unpaid
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice->status
                    !==
                    'unpaid'
                ) {

                    throw new RuntimeException(
                        'This seller invoice is no longer payable.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Downgrade Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice->purchase_type
                    ===
                    SellerInvoice::TYPE_DOWNGRADE
                ) {

                    throw new RuntimeException(
                        'Seller package downgrades are not allowed.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Application Status
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->applicationCanPayInvoice(
                        $application,
                        $invoice
                    )
                ) {

                    throw new RuntimeException(
                        'This seller invoice cannot be activated from application status: '
                        .
                        $application->status
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
                | Activate Correct Seller Subscription
                |--------------------------------------------------------------------------
                */

                $this->activateForInvoice(
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

        /*
        |--------------------------------------------------------------------------
        | Initial Purchase
        |--------------------------------------------------------------------------
        */

        if (
            $invoice->isInitialPurchase()
        ) {

            return in_array(
                $application->status,
                [
                    SellerApplication::STATUS_PAYMENT_PENDING,
                    SellerApplication::STATUS_ACTIVE,
                ],
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Renewal / Upgrade
        |--------------------------------------------------------------------------
        |
        | EXPIRED:
        | normal renewal or upgrade after expiration.
        |
        | ACTIVE:
        | active seller upgrading immediately.
        |
        */

        return in_array(
            $application->status,
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

        /*
        |--------------------------------------------------------------------------
        | Initial Seller Package
        |--------------------------------------------------------------------------
        */

        if (
            $invoice->isInitialPurchase()
        ) {

            return $this
                ->subscriptions
                ->activateFromApplication(
                    $application,
                    $reference,
                    $invoice
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Renewal / Upgrade
        |--------------------------------------------------------------------------
        */

        return $this
            ->subscriptions
            ->activateFromRenewalInvoice(
                $invoice,
                $reference
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Failed / Pending Gateway Payment
    |--------------------------------------------------------------------------
    */

    protected function processFailedOrPendingPayment(
        SellerInvoicePayment $payment,
        array $data,
        string $status
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Terminal Failure
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
                    $status,
            ]);


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Still Pending
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
                $invoice->application;


            $user =
                $application
                    ?->user;


            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            */

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
            | Send Existing Midpoint Payment Email
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
                'Seller package payment confirmation email sent.',
                [
                    'seller_invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $user->id,

                    'purchase_type' =>
                        $invoice->purchase_type,

                    'payment_method' =>
                        $invoice->payment_method,
                ]
            );


            return true;


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Do not throw email errors into financial processing.
            |
            */

            Log::error(
                'Seller package payment confirmation email failed.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
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
    | Seller In-App Notification
    |--------------------------------------------------------------------------
    */

    protected function notifySellerInApp(
        int $invoiceId
    ): bool {

        try {

            /*
            |--------------------------------------------------------------------------
            | Invoice
            |--------------------------------------------------------------------------
            */

            $invoice =
                SellerInvoice::query()

                    ->with([
                        'application',
                        'user',
                    ])

                    ->findOrFail(
                        $invoiceId
                    );


            $user =
                $invoice->user;


            $application =
                $invoice->application;


            /*
            |--------------------------------------------------------------------------
            | Seller
            |--------------------------------------------------------------------------
            */

            if (
                !$user
                ||
                !$application
            ) {

                throw new RuntimeException(
                    'Seller package notification recipient is missing.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Notification Title
            |--------------------------------------------------------------------------
            */

            $title =
                match (
                    $invoice->purchase_type
                ) {

                    SellerInvoice::TYPE_RENEWAL =>
                        'Seller package renewed',

                    SellerInvoice::TYPE_UPGRADE =>
                        'Seller package upgraded',

                    default =>
                        'Seller package payment confirmed',
                };


            /*
            |--------------------------------------------------------------------------
            | Notification Action
            |--------------------------------------------------------------------------
            */

            $action =
                match (
                    $invoice->purchase_type
                ) {

                    SellerInvoice::TYPE_RENEWAL =>
                        'renewal',

                    SellerInvoice::TYPE_UPGRADE =>
                        'upgrade',

                    default =>
                        'purchase',
                };


            /*
            |--------------------------------------------------------------------------
            | Create Idempotent Notification
            |--------------------------------------------------------------------------
            |
            | firstOrCreate prevents duplicate callback/webhook notifications.
            |
            */

            TransactionNotification::firstOrCreate(
                [
                    'event_key' =>
                        'seller-invoice:'
                        .
                        $invoice->id
                        .
                        ':seller:paid',
                ],
                [
                    'user_id' =>
                        $user->id,

                    'secure_transaction_id' =>
                        null,

                    'audience' =>
                        'seller',

                    'type' =>
                        'payment',

                    'title' =>
                        $title,

                    'message' =>
                        'Your '
                        .
                        $invoice->effective_package_name
                        .
                        ' package '
                        .
                        $action
                        .
                        ' payment of ₦'
                        .
                        number_format(
                            (float) $invoice->amount,
                            2
                        )
                        .
                        ' has been confirmed. Invoice '
                        .
                        $invoice->invoice_number
                        .
                        ' is paid.',

                    'data' => [
                        'invoice_id' =>
                            $invoice->id,

                        'invoice_number' =>
                            $invoice->invoice_number,

                        'purchase_type' =>
                            $invoice->purchase_type,

                        'package_name' =>
                            $invoice->effective_package_name,

                        'amount' =>
                            (float) $invoice->amount,

                        'payment_method' =>
                            $invoice->payment_method,

                        'payment_reference' =>
                            $invoice->payment_reference,

                        'url' =>
                            route(
                                'verified-sellers'
                            )
                            .
                            '#seller-invoice',
                    ],
                ]
            );


            return true;


        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller package in-app notification failed.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );


            /*
             * Payment is already successful.
             *
             * Never throw from here.
             */

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

            /*
            |--------------------------------------------------------------------------
            | Invoice
            |--------------------------------------------------------------------------
            */

            $invoice =
                SellerInvoice::query()

                    ->with(
                        'application'
                    )

                    ->findOrFail(
                        $invoiceId
                    );


            $application =
                $invoice->application;


            if (
                !$application
            ) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Active Admin Users
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Existing Admin Notification
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Admin notification failure must not affect seller payment.
            |
            */

            Log::error(
                'Admin seller package payment notification failed.',
                [
                    'seller_invoice_id' =>
                        $invoiceId,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );
        }
    }
}