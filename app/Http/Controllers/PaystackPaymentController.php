<?php

namespace App\Http\Controllers;

use App\Models\SecureTransaction;
use App\Models\SecureTransactionPayment;
use App\Models\SellerInvoicePayment;
use App\Services\PaystackService;
use App\Services\TransactionLifecycleService;
use App\Services\TransactionPaymentCommunicationService;
use App\Services\SellerInvoicePaymentService;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use RuntimeException;
use Throwable;

class PaystackPaymentController extends Controller
{
    protected TransactionPaymentCommunicationService $communications;

    public function __construct(
        TransactionPaymentCommunicationService $communications
    ) {
        $this->communications =
            $communications;
    }


    public function initialize(
        Request $request,
        SecureTransaction $secureTransaction,
        PaystackService $paystack
    ) {
        $buyer =
            $request->user();


        abort_unless(
            $secureTransaction->buyer_id
            &&
            (int) $secureTransaction->buyer_id
            ===
            (int) $buyer->id,
            403,
            'This transaction does not belong to your buyer account.'
        );


        abort_unless(
            strtolower(
                trim(
                    $secureTransaction->buyer_email
                )
            )
            ===
            strtolower(
                trim(
                    $buyer->email
                )
            ),
            403,
            'The buyer email does not match this transaction.'
        );


        abort_if(
            (int) $secureTransaction->seller_id
            ===
            (int) $buyer->id,
            403,
            'The seller cannot pay for their own transaction.'
        );


        if (
            $secureTransaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID
        ) {
            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                )
                ->with(
                    'success',
                    'This transaction has already been paid and secured.'
                );
        }


        if (
            $secureTransaction->isLinkExpired()
        ) {
            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                )
                ->with(
                    'error',
                    'This transaction link has expired and can no longer be paid.'
                );
        }


        if (
            $secureTransaction->status
            !==
            SecureTransaction::STATUS_AWAITING_PAYMENT
        ) {
            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                )
                ->with(
                    'error',
                    'This transaction is no longer awaiting payment.'
                );
        }


        $activeAttempt =
            SecureTransactionPayment::query()
                ->where(
                    'secure_transaction_id',
                    $secureTransaction->id
                )
                ->where(
                    'buyer_id',
                    $buyer->id
                )
                ->whereIn(
                    'status',
                    [
                        SecureTransactionPayment::STATUS_INITIALIZED,
                        SecureTransactionPayment::STATUS_PENDING,
                    ]
                )
                ->whereNotNull(
                    'authorization_url'
                )
                ->latest('id')
                ->first();


        if (
            $activeAttempt
            &&
            $activeAttempt->authorization_url
        ) {
            return redirect()
                ->away(
                    $activeAttempt->authorization_url
                );
        }


        $amount =
            round(
                (float) $secureTransaction->total_amount,
                2
            );


        $amountSubunit =
            (int) round(
                $amount * 100
            );


        if (
            $amountSubunit <= 0
        ) {
            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                )
                ->with(
                    'error',
                    'The transaction amount is invalid.'
                );
        }


        $currency =
            strtoupper(
                $secureTransaction->currency
                ?:
                'NGN'
            );


        $reference =
            $this->generatePaymentReference(
                $secureTransaction
            );


        $payment =
            SecureTransactionPayment::create([

                'secure_transaction_id' =>
                    $secureTransaction->id,

                'buyer_id' =>
                    $buyer->id,

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
                    SecureTransactionPayment::STATUS_CREATED,

            ]);


        $secureTransaction->update([

            'payment_status' =>
                SecureTransaction::PAYMENT_PENDING,

            'paystack_reference' =>
                $reference,

        ]);


        try {
            $paystackData =
                $paystack->initializeTransaction([

                    'email' =>
                        $buyer->email,

                    'amount' =>
                        (string) $amountSubunit,

                    'currency' =>
                        $currency,

                    'reference' =>
                        $reference,

                    'callback_url' =>
                        route(
                            'payments.paystack.callback'
                        ),

                    'metadata' =>
                        json_encode(
                            [
                                'secure_transaction_id' =>
                                    $secureTransaction->id,

                                'midpoint_reference' =>
                                    $secureTransaction->reference,

                                'public_token' =>
                                    $secureTransaction->public_token,

                                'seller_id' =>
                                    $secureTransaction->seller_id,

                                'buyer_id' =>
                                    $buyer->id,

                                'buyer_email' =>
                                    $buyer->email,
                            ],
                            JSON_UNESCAPED_SLASHES
                        ),

                ]);


            $authorizationUrl =
                $paystackData['authorization_url']
                ??
                null;


            $accessCode =
                $paystackData['access_code']
                ??
                null;


            if (
                !$authorizationUrl
                ||
                !$accessCode
            ) {
                throw new RuntimeException(
                    'Paystack did not return a valid checkout URL.'
                );
            }


            $payment->update([

                'access_code' =>
                    $accessCode,

                'authorization_url' =>
                    $authorizationUrl,

                'status' =>
                    SecureTransactionPayment::STATUS_INITIALIZED,

                'initialized_at' =>
                    now(),

            ]);


            return redirect()
                ->away(
                    $authorizationUrl
                );

        } catch (Throwable $exception) {

            $payment->update([

                'status' =>
                    SecureTransactionPayment::STATUS_FAILED,

            ]);


            $secureTransaction->update([

                'payment_status' =>
                    SecureTransaction::PAYMENT_UNPAID,

            ]);


            Log::error(
                'Paystack transaction initialization failed.',
                [
                    'secure_transaction_id' =>
                        $secureTransaction->id,

                    'reference' =>
                        $reference,

                    'buyer_id' =>
                        $buyer->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return redirect()
                ->route(
                    'secure-transactions.show',
                    $secureTransaction
                )
                ->with(
                    'error',
                    'We could not start Paystack checkout. Please try again.'
                );
        }
    }


    public function callback(
        Request $request,
        PaystackService $paystack
    ) {
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


        if (
            $reference === ''
        ) {
            return redirect()
                ->route(
                    'dashboard'
                )
                ->with(
                    'error',
                    'Paystack did not return a payment reference.'
                );
        }


        $payment =
            SecureTransactionPayment::query()
                ->with(
                    'secureTransaction'
                )
                ->where(
                    'reference',
                    $reference
                )
                ->first();


        if (!$payment) {
            return redirect()
                ->route(
                    'dashboard'
                )
                ->with(
                    'error',
                    'The returned payment reference is not recognized by MidPoint.'
                );
        }


        $transaction =
            $payment->secureTransaction;


        if (
            $payment->status
            ===
            SecureTransactionPayment::STATUS_SUCCESS

            &&
            $transaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID
        ) {
            $this->communications->handle(
                $transaction->fresh()
            );


            return redirect()
                ->route(
                    'secure-transactions.show',
                    $transaction
                )
                ->with(
                    'success',
                    'Payment already confirmed and secured.'
                );
        }


        try {
            $data =
                $paystack->verifyTransaction(
                    $reference
                );


            $successful =
                $this->processVerifiedPayment(
                    $payment,
                    $data
                );


            if ($successful) {
                return redirect()
                    ->route(
                        'secure-transactions.show',
                        $transaction
                    )
                    ->with(
                        'success',
                        'Payment successful. Your funds are now secured by MidPoint.'
                    );
            }


            return redirect()
                ->route(
                    'secure-transactions.show',
                    $transaction
                )
                ->with(
                    'warning',
                    'Paystack has not confirmed this payment as successful yet.'
                );

        } catch (Throwable $exception) {

            Log::error(
                'Paystack callback verification failed.',
                [
                    'reference' =>
                        $reference,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return redirect()
                ->route(
                    'secure-transactions.show',
                    $transaction
                )
                ->with(
                    'error',
                    'We could not verify the payment right now. If you were charged, do not pay again yet.'
                );
        }
    }


    public function webhook(
    Request $request,
    PaystackService $paystack,
    TransactionLifecycleService $lifecycle,
    SellerInvoicePaymentService $sellerInvoicePayments
    ) {
        $rawPayload =
            $request->getContent();


        $signature =
            $request->header(
                'x-paystack-signature'
            );


        if (
            !$paystack->verifyWebhookSignature(
                $rawPayload,
                $signature
            )
        ) {
            Log::warning(
                'Rejected invalid Paystack webhook signature.'
            );


            return response(
                'Invalid signature',
                401
            );
        }


        $event =
            $request->json()
                ->all();


        $eventName =
            $event['event']
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | Seller Payout Events
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $eventName,
                [
                    'transfer.success',
                    'transfer.failed',
                    'transfer.reversed',
                ],
                true
            )
        ) {
            return $this->handleTransferWebhook(
                $eventName,
                $event,
                $lifecycle
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ignore Other Events
        |--------------------------------------------------------------------------
        */

        if (
            $eventName
            !==
            'charge.success'
        ) {
            return response(
                'OK',
                200
            );
        }


        $reference =
            $event['data']['reference']
            ??
            null;


        if (!$reference) {
            return response(
                'OK',
                200
            );
        }


        $payment =
            SecureTransactionPayment::query()
                ->with(
                    'secureTransaction'
                )
                ->where(
                    'reference',
                    $reference
                )
                ->first();


        if (!$payment) {

            /*
            |--------------------------------------------------------------------------
            | Seller Package Invoice Payment
            |--------------------------------------------------------------------------
            */

            $sellerInvoicePayment =
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
            | Seller Invoice Payment Found
            |--------------------------------------------------------------------------
            */

            if (
                $sellerInvoicePayment
            ) {

                /*
                |--------------------------------------------------------------------------
                | Already Processed
                |--------------------------------------------------------------------------
                */

                if (
                    $sellerInvoicePayment->status
                    ===
                    SellerInvoicePayment::STATUS_SUCCESS
                ) {

                    return response(
                        'OK',
                        200
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Verify Directly With Paystack
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
                    | Process Seller Invoice
                    |--------------------------------------------------------------------------
                    */

                    $sellerInvoicePayments
                        ->processVerifiedPayment(
                            $sellerInvoicePayment,
                            $verifiedData
                        );


                    return response(
                        'OK',
                        200
                    );


                } catch (
                    Throwable $exception
                ) {

                    Log::error(
                        'Paystack seller invoice webhook verification failed.',
                        [

                            'reference' =>
                                $reference,

                            'seller_invoice_payment_id' =>
                                $sellerInvoicePayment->id,

                            'error' =>
                                $exception
                                    ->getMessage(),

                        ]
                    );


                    report(
                        $exception
                    );


                    return response(
                        'Verification failed',
                        500
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Neither Buyer Transaction Nor Seller Invoice
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Paystack webhook payment reference not found.',
                [

                    'reference' =>
                        $reference,

                ]
            );


            return response(
                'OK',
                200
            );
        }

        if (
            $payment->status
            ===
            SecureTransactionPayment::STATUS_SUCCESS
        ) {
            if ($payment->secureTransaction) {
                $this->communications->handle(
                    $payment
                        ->secureTransaction
                        ->fresh()
                );
            }


            return response(
                'OK',
                200
            );
        }


        try {
            $verifiedData =
                $paystack->verifyTransaction(
                    $reference
                );


            $this->processVerifiedPayment(
                $payment,
                $verifiedData
            );


            return response(
                'OK',
                200
            );

        } catch (Throwable $exception) {

            Log::error(
                'Paystack webhook payment verification failed.',
                [
                    'reference' =>
                        $reference,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return response(
                'Verification failed',
                500
            );
        }
    }


    private function handleTransferWebhook(
        string $eventName,
        array $event,
        TransactionLifecycleService $lifecycle
    ) {
        $reference =
            $event['data']['reference']
            ??
            null;


        if (!$reference) {
            return response(
                'OK',
                200
            );
        }


        $transaction =
            SecureTransaction::query()
                ->where(
                    'paystack_transfer_reference',
                    $reference
                )
                ->first();


        if (!$transaction) {
            Log::warning(
                'Paystack transfer webhook reference not found.',
                [
                    'reference' =>
                        $reference,

                    'event' =>
                        $eventName,
                ]
            );


            return response(
                'OK',
                200
            );
        }


        $status =
            match ($eventName) {

                'transfer.success' =>
                    SecureTransaction::PAYOUT_SUCCESS,

                'transfer.failed' =>
                    SecureTransaction::PAYOUT_FAILED,

                'transfer.reversed' =>
                    SecureTransaction::PAYOUT_REVERSED,

                default =>
                    SecureTransaction::PAYOUT_PENDING,
            };


        try {
            $lifecycle->handleTransferStatus(
                $transaction,
                $status
            );


            return response(
                'OK',
                200
            );

        } catch (Throwable $exception) {

            Log::error(
                'Paystack transfer webhook processing failed.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'reference' =>
                        $reference,

                    'status' =>
                        $status,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return response(
                'Transfer processing failed',
                500
            );
        }
    }


    private function processVerifiedPayment(
        SecureTransactionPayment $payment,
        array $data
    ): bool {
        $status =
            strtolower(
                (string) (
                    $data['status']
                    ??
                    ''
                )
            );


        $reference =
            (string) (
                $data['reference']
                ??
                ''
            );


        $amountSubunit =
            (int) (
                $data['amount']
                ??
                0
            );


        $currency =
            strtoupper(
                (string) (
                    $data['currency']
                    ??
                    ''
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


        if (
            $reference
            !==
            $payment->reference
        ) {
            throw new RuntimeException(
                'Paystack reference does not match MidPoint payment reference.'
            );
        }


        if (
            $amountSubunit
            !==
            (int) $payment->amount_subunit
        ) {
            throw new RuntimeException(
                'Paystack amount does not match MidPoint transaction amount.'
            );
        }


        if (
            $currency
            !==
            strtoupper(
                $payment->currency
            )
        ) {
            throw new RuntimeException(
                'Paystack currency does not match MidPoint transaction currency.'
            );
        }


        $transaction =
            $payment
                ->secureTransaction()
                ->firstOrFail();


        if (
            $paystackEmail !== ''

            &&
            $paystackEmail
            !==
            strtolower(
                trim(
                    $transaction->buyer_email
                )
            )
        ) {
            throw new RuntimeException(
                'Paystack customer email does not match the MidPoint buyer email.'
            );
        }


        if (
            $status
            !==
            'success'
        ) {
            $this->processFailedOrPendingPayment(
                $payment,
                $transaction,
                $data,
                $status
            );


            return false;
        }


        DB::transaction(
            function () use (
                $payment,
                $transaction,
                $data,
                $amountSubunit,
                $reference
            ) {
                $lockedTransaction =
                    SecureTransaction::query()
                        ->whereKey(
                            $transaction->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $lockedPayment =
                    SecureTransactionPayment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $paidAt =
                    !empty(
                        $data['paid_at']
                        ??
                        $data['paidAt']
                        ??
                        null
                    )
                        ? Carbon::parse(
                            $data['paid_at']
                            ??
                            $data['paidAt']
                        )
                        : now();


                $lockedPayment->update([

                    'status' =>
                        SecureTransactionPayment::STATUS_SUCCESS,

                    'paystack_transaction_id' =>
                        $data['id']
                        ??
                        null,

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


                if (
                    $lockedTransaction->payment_status
                    ===
                    SecureTransaction::PAYMENT_PAID
                ) {
                    if (
                        $lockedTransaction->paystack_reference
                        !==
                        $reference
                    ) {
                        Log::critical(
                            'Duplicate successful payment detected for secure transaction.',
                            [
                                'secure_transaction_id' =>
                                    $lockedTransaction->id,

                                'existing_reference' =>
                                    $lockedTransaction->paystack_reference,

                                'new_reference' =>
                                    $reference,
                            ]
                        );
                    }


                    return;
                }


                $serviceFeeRate =
                    (float) config(
                        'secure_transactions.service_fee_percent',
                        5
                    );


                $vatRate =
                    (float) config(
                        'secure_transactions.fee_vat_percent',
                        7.5
                    );


                if (
                    $serviceFeeRate < 0
                    ||
                    $vatRate < 0
                ) {
                    throw new RuntimeException(
                        'MidPoint transaction fee configuration is invalid.'
                    );
                }


                $productSubtotal =
                    round(
                        (float) $lockedTransaction->subtotal,
                        2
                    );


                $paidAmount =
                    round(
                        $amountSubunit / 100,
                        2
                    );


                $serviceFeeAmount =
                    round(
                        $productSubtotal
                        *
                        (
                            $serviceFeeRate / 100
                        ),
                        2
                    );


                $vatAmount =
                    round(
                        $serviceFeeAmount
                        *
                        (
                            $vatRate / 100
                        ),
                        2
                    );


                $sellerNetAmount =
                    round(
                        $paidAmount
                        -
                        $serviceFeeAmount
                        -
                        $vatAmount,
                        2
                    );


                if (
                    $sellerNetAmount < 0
                ) {
                    throw new RuntimeException(
                        'Calculated seller payout amount is invalid.'
                    );
                }


                $inspectionHours =
                    (int) (
                        $lockedTransaction->inspection_hours
                        ?:
                        config(
                            'secure_transactions.inspection_hours',
                            8
                        )
                    );


                $lockedTransaction->update([

                    'payment_status' =>
                        SecureTransaction::PAYMENT_PAID,

                    'status' =>
                        SecureTransaction::STATUS_PAYMENT_SECURED,

                    'paystack_reference' =>
                        $reference,

                    'paystack_transaction_id' =>
                        isset(
                            $data['id']
                        )
                            ? (string) $data['id']
                            : null,

                    'paid_amount' =>
                        $paidAmount,

                    'paid_at' =>
                        $paidAt,

                    'inspection_hours' =>
                        $inspectionHours,

                    'service_fee_rate' =>
                        $serviceFeeRate,

                    'vat_rate' =>
                        $vatRate,

                    'service_fee_amount' =>
                        $serviceFeeAmount,

                    'vat_amount' =>
                        $vatAmount,

                    'seller_net_amount' =>
                        $sellerNetAmount,

                    'payout_status' =>
                        SecureTransaction::PAYOUT_LOCKED,

                ]);
            }
        );


        $this->communications->handle(
            $transaction->fresh()
        );


        return true;
    }


    private function processFailedOrPendingPayment(
        SecureTransactionPayment $payment,
        SecureTransaction $transaction,
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
                    SecureTransactionPayment::STATUS_FAILED,

                'verified_at' =>
                    now(),

                'gateway_response' =>
                    $data['gateway_response']
                    ??
                    null,

            ]);


            if (
                $transaction->payment_status
                !==
                SecureTransaction::PAYMENT_PAID
            ) {
                $transaction->update([

                    'payment_status' =>
                        SecureTransaction::PAYMENT_FAILED,

                ]);
            }


            return;
        }


        $payment->update([

            'status' =>
                SecureTransactionPayment::STATUS_PENDING,

            'verified_at' =>
                now(),

            'gateway_response' =>
                $data['gateway_response']
                ??
                null,

        ]);


        if (
            $transaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {
            $transaction->update([

                'payment_status' =>
                    SecureTransaction::PAYMENT_PENDING,

            ]);
        }
    }


    private function generatePaymentReference(
        SecureTransaction $transaction
    ): string {
        do {
            $reference =
                'MP-PAY-'
                .
                $transaction->id
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
            SecureTransactionPayment::query()
                ->where(
                    'reference',
                    $reference
                )
                ->exists()
        );


        return $reference;
    }
}