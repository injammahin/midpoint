<?php

namespace App\Services;

use App\Models\SecureTransaction;
use App\Models\SecureTransactionPayment;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\TransactionDisputeStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class DisputeResolutionService
{
    public function __construct(
        protected PaystackService $paystack,
        protected SellerWalletService $wallets,
        protected DisputeRoomCommunicationService $communications
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Decision
    |--------------------------------------------------------------------------
    */

    public function resolve(
        User $admin,
        TransactionDispute $dispute,
        string $resolutionType,
        ?float $requestedRefundAmount,
        string $note
    ): TransactionDispute {

        $dispute->loadMissing([
            'transaction.successfulPayment',
            'transaction.buyer',
            'transaction.seller',
        ]);


        $transaction =
            $dispute->transaction;


        if (
            !$transaction
            ||
            $transaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'Only a paid Midpoint transaction can be resolved financially.',
            ]);
        }


        if (
            !$dispute->isRoomActive()
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'Activate the dispute resolution room before making a final decision.',
            ]);
        }


        if (
            $dispute->isResolved()
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'This dispute has already been resolved.',
            ]);
        }


        if (
            $transaction->funds_released_at
            ||
            $transaction->payout_status
            ===
            SecureTransaction::PAYOUT_WALLET_CREDITED
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'The seller funds have already been released. Do not initiate a Paystack refund from this dispute workflow.',
            ]);
        }


        return match ($resolutionType) {

            TransactionDispute::RESOLUTION_FULL_REFUND =>
                $this->startRefund(
                    $admin,
                    $dispute,
                    true,
                    null,
                    $note
                ),

            TransactionDispute::RESOLUTION_PARTIAL_REFUND =>
                $this->startRefund(
                    $admin,
                    $dispute,
                    false,
                    $requestedRefundAmount,
                    $note
                ),

            TransactionDispute::RESOLUTION_RELEASE_TO_SELLER =>
                $this->releaseToSeller(
                    $admin,
                    $dispute,
                    $note
                ),

            TransactionDispute::RESOLUTION_RESUME_TRANSACTION =>
                $this->resumeTransaction(
                    $admin,
                    $dispute,
                    $note
                ),

            default =>
                throw ValidationException::withMessages([
                    'resolution_type' =>
                        'The selected dispute resolution is not supported.',
                ]),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Start Full / Partial Paystack Refund
    |--------------------------------------------------------------------------
    */

    protected function startRefund(
        User $admin,
        TransactionDispute $dispute,
        bool $fullRefund,
        ?float $requestedRefundAmount,
        string $note
    ): TransactionDispute {

        $transaction =
            $dispute->transaction;


        $payment =
            $transaction
                ->successfulPayment;


        if (
            !$payment
            ||
            empty(
                $payment->reference
            )
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The successful Paystack payment reference could not be found.',
            ]);
        }


        $paidAmount =
            round(
                (float)
                (
                    $transaction->paid_amount
                    ?:
                    $payment->amount
                    ?:
                    $transaction->total_amount
                ),
                2
            );


        if (
            $paidAmount
            <=
            0
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The paid amount is invalid.',
            ]);
        }


        $refundAmount =
            $fullRefund
                ? $paidAmount
                : round(
                    (float)
                    $requestedRefundAmount,
                    2
                );


        if (
            !$fullRefund
            &&
            (
                $refundAmount <= 0
                ||
                $refundAmount >= $paidAmount
            )
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'A partial refund must be greater than ₦0 and less than the full buyer payment.',
            ]);
        }


        if (
            $refundAmount
            >
            $paidAmount
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The refund cannot be greater than the original buyer payment.',
            ]);
        }


        $retainedGross =
            round(
                max(
                    0,
                    $paidAmount
                    -
                    $refundAmount
                ),
                2
            );


        $settlement =
            $this->calculateSellerSettlement(
                $retainedGross
            );


        /*
        |--------------------------------------------------------------------------
        | Reserve The Resolution Before Calling Paystack
        |--------------------------------------------------------------------------
        |
        | If the HTTP request times out after Paystack accepted the refund,
        | we DO NOT immediately retry and risk creating a duplicate refund.
        | Instead the case moves to refund_sync_required.
        |
        */

        DB::transaction(
            function () use (
                $admin,
                $dispute,
                $fullRefund,
                $refundAmount,
                $settlement,
                $note
            ) {

                $lockedDispute =
                    TransactionDispute::query()
                        ->whereKey(
                            $dispute->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                if (
                    $lockedDispute->resolution_status
                    &&
                    !in_array(
                        $lockedDispute->resolution_status,
                        [
                            TransactionDispute::RESOLUTION_STATUS_REFUND_FAILED,
                        ],
                        true
                    )
                ) {

                    throw ValidationException::withMessages([
                        'resolution_type' =>
                            'A dispute resolution is already in progress for this case.',
                    ]);
                }


                $lockedDispute->forceFill([

                    'resolution_type' =>
                        $fullRefund
                            ? TransactionDispute::RESOLUTION_FULL_REFUND
                            : TransactionDispute::RESOLUTION_PARTIAL_REFUND,

                    'resolution_status' =>
                        TransactionDispute::RESOLUTION_STATUS_INITIATING,

                    'refund_amount' =>
                        $refundAmount,

                    'seller_settlement_amount' =>
                        $settlement['seller_net'],

                    'resolution_service_fee_amount' =>
                        $settlement['service_fee'],

                    'resolution_vat_amount' =>
                        $settlement['vat'],

                    'resolution_note' =>
                        $note,

                    'resolved_by' =>
                        $admin->id,

                    'resolution_initiated_at' =>
                        now(),

                    'refund_error' =>
                        null,

                ])->save();


                $this->createSystemMessage(
                    $lockedDispute,
                    'Midpoint has made a financial decision on this dispute. A '
                    .
                    (
                        $fullRefund
                            ? 'full'
                            : 'partial'
                    )
                    .
                    ' Paystack refund is being initiated. The case will remain financially locked until Paystack confirms the refund outcome.'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Call Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $currency =
                strtoupper(
                    $payment->currency
                    ?:
                    $transaction->currency
                    ?:
                    'NGN'
                );


            $refundData =
                $this->paystack->createRefund(
                    $payment->reference,
                    (int)
                    round(
                        $refundAmount
                        *
                        100
                    ),
                    $currency,
                    'Midpoint dispute refund for '
                    .
                    $transaction->reference,
                    'Dispute #'
                    .
                    $dispute->id
                    .
                    ' resolved by Midpoint admin #'
                    .
                    $admin->id
                );


            $dispute =
                $this->applyRefundGatewayData(
                    $dispute,
                    $refundData
                );


            /*
            |--------------------------------------------------------------------------
            | Paystack May Return Processed Immediately
            |--------------------------------------------------------------------------
            */

            if (
                strtolower(
                    (string)
                    (
                        $refundData['status']
                        ??
                        ''
                    )
                )
                ===
                'processed'
            ) {

                $dispute =
                    $this->finalizeProcessedRefund(
                        $dispute,
                        $refundData
                    );
            }


            $this->communications->resolutionUpdate(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ]),
                'dispute-refund-initiated-'
                .
                $dispute->id,
                'Midpoint initiated your dispute refund',
                'Midpoint has initiated a '
                .
                (
                    $fullRefund
                        ? 'full'
                        : 'partial'
                )
                .
                ' refund of ₦'
                .
                number_format(
                    $refundAmount,
                    2
                )
                .
                ' through Paystack for transaction '
                .
                $transaction->reference
                .
                '. Paystack will send further processing updates. Seller payout remains locked until the refund reaches a final state.',
                'Refund initiated'
            );


            return $dispute->fresh();

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Important: Do Not Blindly Retry
            |--------------------------------------------------------------------------
            |
            | A network timeout can occur after Paystack has accepted the refund.
            | Mark the case for sync so an admin can reconcile with Paystack.
            |
            */

            $dispute->forceFill([

                'resolution_status' =>
                    TransactionDispute::RESOLUTION_STATUS_REFUND_SYNC_REQUIRED,

                'refund_error' =>
                    mb_substr(
                        $exception->getMessage(),
                        0,
                        5000
                    ),

            ])->save();


            Log::error(
                'Paystack dispute refund initiation requires reconciliation.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'transaction_id' =>
                        $transaction->id,

                    'reference' =>
                        $payment->reference,

                    'refund_amount' =>
                        $refundAmount,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            throw new RuntimeException(
                'The refund request could not be confirmed safely. Do not click refund again yet. Use "Sync Paystack refund" on the dispute page to reconcile the transaction. Paystack response: '
                .
                $exception->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Release Entire Seller Entitlement
    |--------------------------------------------------------------------------
    */

    protected function releaseToSeller(
        User $admin,
        TransactionDispute $dispute,
        string $note
    ): TransactionDispute {

        $transaction =
            $dispute->transaction;


        $sellerAmount =
            round(
                (float)
                $transaction->seller_net_amount,
                2
            );


        if (
            $sellerAmount
            <=
            0
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'The seller settlement amount is invalid.',
            ]);
        }


        DB::transaction(
            function () use (
                $admin,
                $dispute,
                $transaction,
                $sellerAmount,
                $note
            ) {

                $lockedDispute =
                    TransactionDispute::query()
                        ->whereKey(
                            $dispute->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $lockedTransaction =
                    SecureTransaction::query()
                        ->whereKey(
                            $transaction->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $oldStatus =
                    $lockedDispute->status;


                $lockedDispute->forceFill([

                    'status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'resolution_type' =>
                        TransactionDispute::RESOLUTION_RELEASE_TO_SELLER,

                    'resolution_status' =>
                        TransactionDispute::RESOLUTION_STATUS_COMPLETED,

                    'refund_amount' =>
                        0,

                    'seller_settlement_amount' =>
                        $sellerAmount,

                    'resolution_service_fee_amount' =>
                        (float)
                        $lockedTransaction->service_fee_amount,

                    'resolution_vat_amount' =>
                        (float)
                        $lockedTransaction->vat_amount,

                    'resolution_note' =>
                        $note,

                    'resolved_by' =>
                        $admin->id,

                    'resolution_initiated_at' =>
                        now(),

                    'resolved_at' =>
                        now(),

                ])->save();


                TransactionDisputeStatusHistory::create([

                    'transaction_dispute_id' =>
                        $lockedDispute->id,

                    'secure_transaction_id' =>
                        $lockedDispute->secure_transaction_id,

                    'admin_id' =>
                        $admin->id,

                    'from_status' =>
                        $oldStatus,

                    'to_status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'note' =>
                        $note,

                ]);


                $this->wallets->creditDisputeSettlement(
                    $lockedTransaction,
                    $sellerAmount,
                    $lockedDispute,
                    [
                        'resolution_type' =>
                            TransactionDispute::RESOLUTION_RELEASE_TO_SELLER,

                        'refund_amount' =>
                            0,
                    ]
                );


                $this->createSystemMessage(
                    $lockedDispute,
                    'Midpoint resolved this dispute in favour of releasing the seller entitlement. The approved seller amount is ₦'
                    .
                    number_format(
                        $sellerAmount,
                        2
                    )
                    .
                    '.'
                );
            }
        );


        $fresh =
            $dispute->fresh([
                'transaction.buyer',
                'transaction.seller',
            ]);


        $this->communications->resolutionUpdate(
            $fresh,
            'dispute-release-seller-'
            .
            $fresh->id,
            'Midpoint resolved the dispute',
            'Midpoint has resolved transaction '
            .
            $fresh->transaction->reference
            .
            ' and approved the seller settlement of ₦'
            .
            number_format(
                $sellerAmount,
                2
            )
            .
            '. Resolution note: '
            .
            $note,
            'Resolved'
        );


        return $fresh;
    }


    /*
    |--------------------------------------------------------------------------
    | Resume Normal Transaction
    |--------------------------------------------------------------------------
    */

    protected function resumeTransaction(
        User $admin,
        TransactionDispute $dispute,
        string $note
    ): TransactionDispute {

        DB::transaction(
            function () use (
                $admin,
                $dispute,
                $note
            ) {

                $lockedDispute =
                    TransactionDispute::query()
                        ->whereKey(
                            $dispute->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $lockedTransaction =
                    SecureTransaction::query()
                        ->whereKey(
                            $lockedDispute->secure_transaction_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $oldStatus =
                    $lockedDispute->status;


                $resumeStatus =
                    $lockedTransaction->inspection_started_at

                        ? SecureTransaction::STATUS_INSPECTION

                        : SecureTransaction::STATUS_DELIVERED;


                $lockedTransaction->forceFill([

                    'status' =>
                        $resumeStatus,

                    'payout_status' =>
                        SecureTransaction::PAYOUT_LOCKED,

                    /*
                    |--------------------------------------------------------------------------
                    | Manual Buyer Acceptance Policy
                    |--------------------------------------------------------------------------
                    */

                    'auto_complete_at' =>
                        null,

                ])->save();


                $lockedDispute->forceFill([

                    'status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'resolution_type' =>
                        TransactionDispute::RESOLUTION_RESUME_TRANSACTION,

                    'resolution_status' =>
                        TransactionDispute::RESOLUTION_STATUS_COMPLETED,

                    'refund_amount' =>
                        0,

                    'seller_settlement_amount' =>
                        0,

                    'resolution_service_fee_amount' =>
                        0,

                    'resolution_vat_amount' =>
                        0,

                    'resolution_note' =>
                        $note,

                    'resolved_by' =>
                        $admin->id,

                    'resolution_initiated_at' =>
                        now(),

                    'resolved_at' =>
                        now(),

                ])->save();


                TransactionDisputeStatusHistory::create([

                    'transaction_dispute_id' =>
                        $lockedDispute->id,

                    'secure_transaction_id' =>
                        $lockedDispute->secure_transaction_id,

                    'admin_id' =>
                        $admin->id,

                    'from_status' =>
                        $oldStatus,

                    'to_status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'note' =>
                        $note,

                ]);


                $this->createSystemMessage(
                    $lockedDispute,
                    'Midpoint resolved the dispute and returned the order to the normal transaction flow. Seller funds remain locked until the buyer explicitly accepts the order.'
                );
            }
        );


        $fresh =
            $dispute->fresh([
                'transaction.buyer',
                'transaction.seller',
            ]);


        $this->communications->resolutionUpdate(
            $fresh,
            'dispute-resume-'
            .
            $fresh->id,
            'Midpoint resolved the dispute',
            'Midpoint has completed the dispute review for '
            .
            $fresh->transaction->reference
            .
            ' and returned the transaction to its normal protected flow. Seller funds remain locked until buyer acceptance. Resolution note: '
            .
            $note,
            'Resolved'
        );


        return $fresh;
    }


    /*
    |--------------------------------------------------------------------------
    | Apply Paystack API Data
    |--------------------------------------------------------------------------
    */

    protected function applyRefundGatewayData(
        TransactionDispute $dispute,
        array $data
    ): TransactionDispute {

        $status =
            strtolower(
                trim(
                    (string)
                    (
                        $data['status']
                        ??
                        'pending'
                    )
                )
            );


        $resolutionStatus =
            $this->mapPaystackRefundStatus(
                $status
            );


        $expectedAt =
            $this->parseDate(
                $data['expected_at']
                ??
                null
            );


        $dispute->forceFill([

            'resolution_status' =>
                $resolutionStatus,

            'paystack_refund_id' =>
                isset(
                    $data['id']
                )
                    ? (string)
                    $data['id']
                    : $dispute->paystack_refund_id,

            'paystack_refund_reference' =>
                isset(
                    $data['refund_reference']
                )
                    ? (string)
                    $data['refund_reference']
                    : (
                        isset(
                            $data['reference']
                        )
                            ? (string)
                            $data['reference']
                            : $dispute->paystack_refund_reference
                    ),

            'paystack_refund_status' =>
                $status,

            'refund_expected_at' =>
                $expectedAt
                ?:
                $dispute->refund_expected_at,

            'refund_error' =>
                null,

        ])->save();


        return $dispute->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Paystack Refund Webhook
    |--------------------------------------------------------------------------
    */

    public function handlePaystackWebhook(
        string $eventName,
        array $data
    ): void {

        $status =
            match ($eventName) {

                'refund.pending' =>
                    'pending',

                'refund.processing' =>
                    'processing',

                'refund.needs-attention' =>
                    'needs-attention',

                'refund.processed' =>
                    'processed',

                'refund.failed' =>
                    'failed',

                default =>
                    null,
            };


        if (!$status) {
            return;
        }


        $transactionReference =
            trim(
                (string)
                (
                    $data['transaction_reference']
                    ??
                    data_get(
                        $data,
                        'transaction.reference'
                    )
                    ??
                    ''
                )
            );


        if (
            $transactionReference
            ===
            ''
        ) {

            Log::warning(
                'Paystack refund webhook missing transaction reference.',
                [
                    'event' =>
                        $eventName,
                ]
            );


            return;
        }


        $payment =
            SecureTransactionPayment::query()

                ->where(
                    'reference',
                    $transactionReference
                )

                ->where(
                    'status',
                    SecureTransactionPayment::STATUS_SUCCESS
                )

                ->latest('id')

                ->first();


        if (!$payment) {

            Log::warning(
                'Paystack refund webhook payment not found.',
                [
                    'event' =>
                        $eventName,

                    'reference' =>
                        $transactionReference,
                ]
            );


            return;
        }


        $dispute =
            TransactionDispute::query()

                ->where(
                    'secure_transaction_id',
                    $payment->secure_transaction_id
                )

                ->whereIn(
                    'resolution_type',
                    [
                        TransactionDispute::RESOLUTION_FULL_REFUND,
                        TransactionDispute::RESOLUTION_PARTIAL_REFUND,
                    ]
                )

                ->first();


        if (!$dispute) {

            Log::warning(
                'Paystack refund webhook dispute not found.',
                [
                    'event' =>
                        $eventName,

                    'secure_transaction_id' =>
                        $payment->secure_transaction_id,
                ]
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Amount Integrity Check
        |--------------------------------------------------------------------------
        */

        $gatewayAmountSubunit =
            (int)
            (
                $data['amount']
                ??
                0
            );


        $expectedAmountSubunit =
            (int)
            round(
                (float)
                $dispute->refund_amount
                *
                100
            );


        if (
            $gatewayAmountSubunit > 0
            &&
            $gatewayAmountSubunit
            !==
            $expectedAmountSubunit
        ) {

            throw new RuntimeException(
                'Paystack refund webhook amount does not match the Midpoint dispute resolution amount.'
            );
        }


        $dispute =
            $this->applyRefundGatewayData(
                $dispute,
                array_merge(
                    $data,
                    [
                        'status' =>
                            $status,
                    ]
                )
            );


        if (
            $status
            ===
            'processed'
        ) {

            $this->finalizeProcessedRefund(
                $dispute,
                $data
            );


            return;
        }


        if (
            $status
            ===
            'failed'
        ) {

            $dispute->forceFill([

                'resolution_status' =>
                    TransactionDispute::RESOLUTION_STATUS_REFUND_FAILED,

                'refund_error' =>
                    (string)
                    (
                        $data['reason']
                        ??
                        'Paystack reported that the refund failed.'
                    ),

            ])->save();


            $this->communications->resolutionUpdate(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ]),
                'dispute-refund-failed-'
                .
                $dispute->id,
                'Refund processing needs attention',
                'Paystack reported that the refund for transaction '
                .
                $dispute->transaction->reference
                .
                ' could not be completed. Midpoint Support is reviewing the case. Seller payout remains locked.',
                'Refund issue'
            );


            return;
        }


        if (
            $status
            ===
            'needs-attention'
        ) {

            $this->communications->resolutionUpdate(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ]),
                'dispute-refund-needs-attention-'
                .
                $dispute->id,
                'Refund requires additional processing',
                'Paystack needs additional refund information before the refund for transaction '
                .
                $dispute->transaction->reference
                .
                ' can complete. Midpoint Support will continue the refund process. Seller payout remains locked.',
                'Refund pending'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Manual Refund Sync
    |--------------------------------------------------------------------------
    */

    public function syncRefund(
        TransactionDispute $dispute
    ): TransactionDispute {

        $dispute->loadMissing(
            'transaction.successfulPayment'
        );


        $payment =
            $dispute
                ->transaction
                ?->successfulPayment;


        if (
            !$payment
            ||
            !$payment->paystack_transaction_id
        ) {

            throw new RuntimeException(
                'The Paystack transaction ID is missing, so this refund cannot be reconciled automatically.'
            );
        }


        $refunds =
            $this->paystack->listRefunds(
                (string)
                $payment->paystack_transaction_id
            );


        $expectedSubunit =
            (int)
            round(
                (float)
                $dispute->refund_amount
                *
                100
            );


        $candidate =
            collect(
                $refunds
            )
                ->filter(
                    function (
                        $refund
                    ) use (
                        $expectedSubunit
                    ) {

                        if (
                            !is_array(
                                $refund
                            )
                        ) {

                            return false;
                        }


                        return
                            (int)
                            (
                                $refund['amount']
                                ??
                                0
                            )
                            ===
                            $expectedSubunit;
                    }
                )
                ->sortByDesc(
                    fn (
                        array $refund
                    ) =>
                        $refund['id']
                        ??
                        0
                )
                ->first();


        if (!$candidate) {

            throw new RuntimeException(
                'No matching Paystack refund was found for this transaction and amount. Do not initiate another refund until this is reconciled.'
            );
        }


        $dispute =
            $this->applyRefundGatewayData(
                $dispute,
                $candidate
            );


        if (
            strtolower(
                (string)
                (
                    $candidate['status']
                    ??
                    ''
                )
            )
            ===
            'processed'
        ) {

            $dispute =
                $this->finalizeProcessedRefund(
                    $dispute,
                    $candidate
                );
        }


        return $dispute->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Finalize Processed Refund
    |--------------------------------------------------------------------------
    */

    protected function finalizeProcessedRefund(
        TransactionDispute $dispute,
        array $gatewayData = []
    ): TransactionDispute {

        return DB::transaction(
            function () use (
                $dispute,
                $gatewayData
            ) {

                $lockedDispute =
                    TransactionDispute::query()

                        ->whereKey(
                            $dispute->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                $lockedTransaction =
                    SecureTransaction::query()

                        ->whereKey(
                            $lockedDispute
                                ->secure_transaction_id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Idempotent Final State
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedDispute->isResolved()
                    &&
                    $lockedDispute->refund_processed_at
                ) {

                    return $lockedDispute;
                }


                $oldStatus =
                    $lockedDispute->status;


                $isFullRefund =
                    $lockedDispute->resolution_type
                    ===
                    TransactionDispute::RESOLUTION_FULL_REFUND;


                $processedAt =
                    $this->parseDate(
                        $gatewayData['refunded_at']
                        ??
                        null
                    )
                    ?:
                    now();


                /*
                |--------------------------------------------------------------------------
                | Full Refund
                |--------------------------------------------------------------------------
                */

                if ($isFullRefund) {

                    $lockedTransaction->forceFill([

                        'status' =>
                            SecureTransaction::STATUS_CANCELLED,

                        'payout_status' =>
                            SecureTransaction::PAYOUT_LOCKED,

                        'service_fee_amount' =>
                            0,

                        'vat_amount' =>
                            0,

                        'seller_net_amount' =>
                            0,

                        'funds_released_at' =>
                            null,

                        'completed_at' =>
                            null,

                        'auto_complete_at' =>
                            null,

                    ])->save();

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Partial Refund
                    |--------------------------------------------------------------------------
                    |
                    | Midpoint fees are recalculated ONLY on the amount retained by
                    | the seller side of the transaction. The refunded amount has no
                    | Midpoint service fee.
                    |
                    */

                    $lockedTransaction->forceFill([

                        'service_fee_amount' =>
                            (float)
                            $lockedDispute
                                ->resolution_service_fee_amount,

                        'vat_amount' =>
                            (float)
                            $lockedDispute
                                ->resolution_vat_amount,

                        'seller_net_amount' =>
                            (float)
                            $lockedDispute
                                ->seller_settlement_amount,

                        'auto_complete_at' =>
                            null,

                    ])->save();
                }


                $lockedDispute->forceFill([

                    'status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'resolution_status' =>
                        TransactionDispute::RESOLUTION_STATUS_REFUND_PROCESSED,

                    'paystack_refund_status' =>
                        'processed',

                    'refund_processed_at' =>
                        $processedAt,

                    'refund_error' =>
                        null,

                    'resolved_at' =>
                        $processedAt,

                ])->save();


                TransactionDisputeStatusHistory::create([

                    'transaction_dispute_id' =>
                        $lockedDispute->id,

                    'secure_transaction_id' =>
                        $lockedDispute->secure_transaction_id,

                    'admin_id' =>
                        $lockedDispute->resolved_by,

                    'from_status' =>
                        $oldStatus,

                    'to_status' =>
                        TransactionDispute::STATUS_RESOLVED,

                    'note' =>
                        $lockedDispute->resolution_note,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Credit Seller Only After Partial Refund Is Processed
                |--------------------------------------------------------------------------
                */

                if (
                    !$isFullRefund
                    &&
                    (float)
                    $lockedDispute->seller_settlement_amount
                    >
                    0
                ) {

                    $this->wallets->creditDisputeSettlement(
                        $lockedTransaction,
                        (float)
                        $lockedDispute
                            ->seller_settlement_amount,
                        $lockedDispute,
                        [
                            'resolution_type' =>
                                TransactionDispute::RESOLUTION_PARTIAL_REFUND,

                            'refund_amount' =>
                                (float)
                                $lockedDispute->refund_amount,

                            'paystack_refund_id' =>
                                $lockedDispute->paystack_refund_id,
                        ]
                    );
                }


                $this->createSystemMessage(
                    $lockedDispute,
                    $isFullRefund

                        ? 'Paystack confirmed that the full refund of ₦'
                            .
                            number_format(
                                (float)
                                $lockedDispute->refund_amount,
                                2
                            )
                            .
                            ' was processed. This dispute is resolved and no seller payout was credited.'

                        : 'Paystack confirmed that the partial refund of ₦'
                            .
                            number_format(
                                (float)
                                $lockedDispute->refund_amount,
                                2
                            )
                            .
                            ' was processed. The approved seller settlement of ₦'
                            .
                            number_format(
                                (float)
                                $lockedDispute->seller_settlement_amount,
                                2
                            )
                            .
                            ' has been credited to the seller Midpoint balance.'
                );


                $fresh =
                    $lockedDispute->fresh([
                        'transaction.buyer',
                        'transaction.seller',
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Communicate After Database State Is Consistent
                |--------------------------------------------------------------------------
                */

                $this->communications->resolutionUpdate(
                    $fresh,
                    'dispute-refund-processed-'
                    .
                    $fresh->id,
                    'Your Midpoint dispute has been resolved',
                    $isFullRefund

                        ? 'Paystack confirmed the full refund of ₦'
                            .
                            number_format(
                                (float)
                                $fresh->refund_amount,
                                2
                            )
                            .
                            ' for transaction '
                            .
                            $fresh->transaction->reference
                            .
                            '. The seller received no Midpoint payout for the refunded transaction.'

                        : 'Paystack confirmed the partial refund of ₦'
                            .
                            number_format(
                                (float)
                                $fresh->refund_amount,
                                2
                            )
                            .
                            ' for transaction '
                            .
                            $fresh->transaction->reference
                            .
                            '. The remaining approved seller settlement is ₦'
                            .
                            number_format(
                                (float)
                                $fresh->seller_settlement_amount,
                                2
                            )
                            .
                            '.',
                    'Resolved'
                );


                return $fresh;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Settlement Calculation
    |--------------------------------------------------------------------------
    |
    | Midpoint service fee is NOT charged on the refunded portion.
    | It is charged only on the transaction amount that remains with the seller.
    |
    */

    protected function calculateSellerSettlement(
        float $retainedGross
    ): array {

        $retainedGross =
            round(
                max(
                    0,
                    $retainedGross
                ),
                2
            );


        if (
            $retainedGross
            <=
            0
        ) {

            return [
                'service_fee' =>
                    0.0,

                'vat' =>
                    0.0,

                'seller_net' =>
                    0.0,
            ];
        }


        $serviceFeeRate =
            max(
                0,
                (float)
                config(
                    'secure_transactions.service_fee_percent',
                    5
                )
            );


        $vatRate =
            max(
                0,
                (float)
                config(
                    'secure_transactions.fee_vat_percent',
                    7.5
                )
            );


        $serviceFee =
            round(
                $retainedGross
                *
                (
                    $serviceFeeRate
                    /
                    100
                ),
                2
            );


        $vat =
            round(
                $serviceFee
                *
                (
                    $vatRate
                    /
                    100
                ),
                2
            );


        $sellerNet =
            round(
                max(
                    0,
                    $retainedGross
                    -
                    $serviceFee
                    -
                    $vat
                ),
                2
            );


        return [
            'service_fee' =>
                $serviceFee,

            'vat' =>
                $vat,

            'seller_net' =>
                $sellerNet,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Map Paystack Refund Status
    |--------------------------------------------------------------------------
    */

    protected function mapPaystackRefundStatus(
        string $status
    ): string {

        return match (
            strtolower(
                trim(
                    $status
                )
            )
        ) {

            'processing' =>
                TransactionDispute::RESOLUTION_STATUS_REFUND_PROCESSING,

            'needs-attention' =>
                TransactionDispute::RESOLUTION_STATUS_REFUND_NEEDS_ATTENTION,

            'failed' =>
                TransactionDispute::RESOLUTION_STATUS_REFUND_FAILED,

            'processed' =>
                TransactionDispute::RESOLUTION_STATUS_REFUND_PROCESSED,

            default =>
                TransactionDispute::RESOLUTION_STATUS_REFUND_PENDING,
        };
    }


    protected function parseDate(
        mixed $value
    ): ?Carbon {

        if (
            !$value
        ) {

            return null;
        }


        try {

            return Carbon::parse(
                $value
            );

        } catch (
            Throwable
        ) {

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | System Message
    |--------------------------------------------------------------------------
    */

    protected function createSystemMessage(
        TransactionDispute $dispute,
        string $message
    ): void {

        TransactionDisputeMessage::create([

            'transaction_dispute_id' =>
                $dispute->id,

            'secure_transaction_id' =>
                $dispute->secure_transaction_id,

            'sender_id' =>
                null,

            'sender_role' =>
                TransactionDisputeMessage::ROLE_SYSTEM,

            'visibility' =>
                TransactionDisputeMessage::VISIBILITY_ALL,

            'message' =>
                $message,

            'attachments' =>
                null,

            'is_system' =>
                true,

        ]);
    }
}
