<?php

namespace App\Services;

use App\Models\SecureTransaction;
use App\Models\SecureTransactionPayment;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\TransactionDisputeStatusHistory;
use App\Models\User;
use App\Support\DisputeRefundAllocation;
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
        string|float|null $requestedRefundAmount,
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
            !$dispute->isRoomActivated()
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
            $dispute->resolution_type
            ||
            $dispute->resolution_status
        ) {

            throw ValidationException::withMessages([
                'resolution_type' =>
                    'A final decision has already been recorded for this dispute. Do not submit a second financial decision; reconcile the existing Paystack refund instead.',
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
        string|float|null $requestedRefundAmount,
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


        $paidAmountSubunit =
            (int)
            $payment->amount_subunit;


        if ($paidAmountSubunit <= 0) {

            $paidAmountSubunit =
                DisputeRefundAllocation::majorToSubunit(
                    $payment->amount
                    ?:
                    $transaction->paid_amount
                    ?:
                    $transaction->total_amount
                );
        }


        if (
            $paidAmountSubunit
            <=
            0
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The paid amount is invalid.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Gross Refund Approved By Admin
        |--------------------------------------------------------------------------
        |
        | This is the amount selected by Midpoint before deducting Paystack's
        | original non-refundable processing fee.
        |
        | Seller settlement must be calculated using this GROSS approved amount.
        | It must never use the lower net refund that is sent to Paystack.
        |
        */

        $approvedRefundAmountSubunit =
            $fullRefund
                ? $paidAmountSubunit
                : DisputeRefundAllocation::majorToSubunit(
                    $requestedRefundAmount
                );


        if (
            !$fullRefund
            &&
            (
                $approvedRefundAmountSubunit <= 0
                ||
                $approvedRefundAmountSubunit >= $paidAmountSubunit
            )
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'A partial refund must be greater than ₦0 and less than the full buyer payment.',
            ]);
        }


        if (
            $approvedRefundAmountSubunit
            >
            $paidAmountSubunit
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The refund cannot be greater than the original buyer payment.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Original Paystack Payment
        |--------------------------------------------------------------------------
        |
        | We read the REAL processing fee returned by Paystack instead of
        | hard-coding any percentage or flat charge.
        |
        */

        try {

            $verifiedPayment =
                $this->paystack->verifyTransaction(
                    $payment->reference
                );

        } catch (Throwable $exception) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The original Paystack transaction could not be verified, so no refund was started. Paystack response: '
                    .
                    $exception->getMessage(),
            ]);
        }


        if (
            strtolower(
                trim(
                    (string)
                    (
                        $verifiedPayment['status']
                        ??
                        ''
                    )
                )
            )
            !==
            'success'
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The original Paystack transaction is not verified as successful, so no refund was started.',
            ]);
        }


        $verifiedAmountSubunit =
            (int)
            (
                $verifiedPayment['amount']
                ??
                0
            );


        if (
            $verifiedAmountSubunit <= 0
            ||
            $verifiedAmountSubunit !== $paidAmountSubunit
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The amount returned by Paystack does not match Midpoint\'s recorded successful payment amount. No refund was started.',
            ]);
        }


        if (
            !array_key_exists(
                'fees',
                $verifiedPayment
            )
            ||
            $verifiedPayment['fees'] === null
            ||
            !is_numeric(
                $verifiedPayment['fees']
            )
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'Paystack did not return a verifiable processing fee for the original payment. No refund was started.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Original Paystack Fee To Recover From Buyer Refund
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Original payment = ₦5,000
        | Paystack fee     = ₦175
        |
        | Full approved refund    ₦5,000 -> buyer receives ₦4,825
        | Partial approved refund ₦2,500 -> buyer receives ₦2,325
        |
        */

        $refundGatewayFeeSubunit =
            max(
                0,
                (int)
                $verifiedPayment['fees']
            );


        if (
            $approvedRefundAmountSubunit
            <=
            $refundGatewayFeeSubunit
        ) {

            throw ValidationException::withMessages([
                'refund_amount' =>
                    'The approved refund must be greater than the non-refundable Paystack processing fee of ₦'
                    .
                    number_format(
                        DisputeRefundAllocation::subunitToMajor(
                            $refundGatewayFeeSubunit
                        ),
                        2
                    )
                    .
                    '.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Net Refund Actually Sent To Paystack
        |--------------------------------------------------------------------------
        */

        $refundAmountSubunit =
            $approvedRefundAmountSubunit
            -
            $refundGatewayFeeSubunit;


        /*
        |--------------------------------------------------------------------------
        | Seller Settlement
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Use the GROSS approved refund here, not the net Paystack refund.
        |
        | This prevents the withheld gateway fee from accidentally becoming
        | extra seller money.
        |
        */

        $settlement =
            DisputeRefundAllocation::calculate(
                $paidAmountSubunit,
                $approvedRefundAmountSubunit,
                (float)
                config(
                    'secure_transactions.service_fee_percent',
                    5
                ),
                (float)
                config(
                    'secure_transactions.fee_vat_percent',
                    7.5
                )
            );


        $approvedRefundAmount =
            DisputeRefundAllocation::subunitToMajor(
                $approvedRefundAmountSubunit
            );


        $refundGatewayFeeAmount =
            DisputeRefundAllocation::subunitToMajor(
                $refundGatewayFeeSubunit
            );


        $refundAmount =
            DisputeRefundAllocation::subunitToMajor(
                $refundAmountSubunit
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
                $approvedRefundAmount,
                $approvedRefundAmountSubunit,
                $refundGatewayFeeAmount,
                $refundGatewayFeeSubunit,
                $refundAmount,
                $refundAmountSubunit,
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

                    /*
                    |------------------------------------------------------------------
                    | NET amount actually sent to Paystack / buyer
                    |------------------------------------------------------------------
                    */

                    'refund_amount' =>
                        $refundAmount,

                    'refund_amount_subunit' =>
                        $refundAmountSubunit,

                    /*
                    |------------------------------------------------------------------
                    | GROSS amount approved by Midpoint admin
                    |------------------------------------------------------------------
                    */

                    'approved_refund_amount' =>
                        $approvedRefundAmount,

                    'approved_refund_amount_subunit' =>
                        $approvedRefundAmountSubunit,

                    /*
                    |------------------------------------------------------------------
                    | Paystack processing fee withheld from approved refund
                    |------------------------------------------------------------------
                    */

                    'refund_gateway_fee_amount' =>
                        $refundGatewayFeeAmount,

                    'refund_gateway_fee_subunit' =>
                        $refundGatewayFeeSubunit,

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

                    'paystack_refund_requested_at' =>
                        now(),

                    'room_closed_at' =>
                        now(),

                    'room_closed_by' =>
                        $admin->id,

                    'room_close_type' =>
                        TransactionDispute::ROOM_CLOSE_FINAL_DECISION,

                    'room_close_reason' =>
                        'Midpoint Support made the final financial decision. The room is now read-only; follow the result on the transaction page.',

                    'refund_error' =>
                        null,

                ])->save();


                $this->createSystemMessage(
                    $lockedDispute,
                    'Midpoint approved a '
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
                        $approvedRefundAmount,
                        2
                    )
                    .
                    '. Non-refundable Paystack processing fee deducted: ₦'
                    .
                    number_format(
                        $refundGatewayFeeAmount,
                        2
                    )
                    .
                    '. Net amount being returned to the buyer: ₦'
                    .
                    number_format(
                        $refundAmount,
                        2
                    )
                    .
                    '. This room is now closed. The case will remain financially locked until Paystack confirms the refund outcome.'
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
                    $refundAmountSubunit,
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


            $gatewayAmountSubunit =
                (int)
                (
                    $refundData['amount']
                    ??
                    0
                );


            if (
                $gatewayAmountSubunit <= 0
                ||
                $gatewayAmountSubunit !== $refundAmountSubunit
            ) {

                $dispute->forceFill([
                    'paystack_refund_amount_subunit' =>
                        $gatewayAmountSubunit > 0
                            ? $gatewayAmountSubunit
                            : null,

                    'paystack_refund_id' =>
                        isset($refundData['id'])
                            ? (string) $refundData['id']
                            : $dispute->paystack_refund_id,

                    'paystack_refund_reference' =>
                        isset($refundData['refund_reference'])
                            ? (string) $refundData['refund_reference']
                            : $dispute->paystack_refund_reference,

                    'paystack_refund_status' =>
                        isset($refundData['status'])
                            ? strtolower((string) $refundData['status'])
                            : $dispute->paystack_refund_status,
                ])->save();

                throw new RuntimeException(
                    'Paystack returned a refund amount that does not exactly match the net refund amount Midpoint submitted. Seller settlement remains locked for manual reconciliation.'
                );
            }


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

            $processedImmediately =
                strtolower(
                    (string)
                    (
                        $refundData['status']
                        ??
                        ''
                    )
                )
                ===
                'processed';


            if ($processedImmediately) {

                $dispute =
                    $this->finalizeProcessedRefund(
                        $dispute,
                        $refundData
                    );
            }


            if (!$processedImmediately) {

                $this->communications->resolutionUpdate(
                    $dispute->fresh([
                        'transaction.buyer',
                        'transaction.seller',
                    ]),
                    'dispute-refund-initiated-'
                    .
                    $dispute->id,
                    'Midpoint initiated the dispute refund',
                    'For transaction '
                    .
                    $transaction->reference
                    .
                    ', Midpoint approved a '
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
                        $approvedRefundAmount,
                        2
                    )
                    .
                    '. Non-refundable Paystack processing fee deducted from the approved refund: ₦'
                    .
                    number_format(
                        $refundGatewayFeeAmount,
                        2
                    )
                    .
                    '. Net refund being returned to the buyer: ₦'
                    .
                    number_format(
                        $refundAmount,
                        2
                    )
                    .
                    '. The room is now closed. Paystack says a processed refund can still take up to 10 business days to appear in the buyer\'s bank account. Seller payout remains locked until the refund reaches a final state.',
                    'Refund initiated'
                );
            }


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

                    'approved_refund_amount' =>
                        $approvedRefundAmount,

                    'refund_gateway_fee_amount' =>
                        $refundGatewayFeeAmount,

                    'net_refund_amount' =>
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

                    'refund_amount_subunit' =>
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

                    'room_closed_at' =>
                        now(),

                    'room_closed_by' =>
                        $admin->id,

                    'room_close_type' =>
                        TransactionDispute::ROOM_CLOSE_FINAL_DECISION,

                    'room_close_reason' =>
                        'Midpoint Support made the final decision to release the seller entitlement. The room is now read-only.',

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

                    'refund_amount_subunit' =>
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

                    'room_closed_at' =>
                        now(),

                    'room_closed_by' =>
                        $admin->id,

                    'room_close_type' =>
                        TransactionDispute::ROOM_CLOSE_FINAL_DECISION,

                    'room_close_reason' =>
                        'Midpoint Support made the final decision to return this order to the protected transaction flow. The room is now read-only.',

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

            'paystack_refund_amount_subunit' =>
                isset(
                    $data['amount']
                )
                    ? (int)
                    $data['amount']
                    : $dispute->paystack_refund_amount_subunit,

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
            (
                $dispute->refund_amount_subunit
                ?:
                DisputeRefundAllocation::majorToSubunit(
                    $dispute->refund_amount
                )
            );


        if (
            $gatewayAmountSubunit > 0
            &&
            $gatewayAmountSubunit
            !==
            $expectedAmountSubunit
        ) {

            $dispute->forceFill([
                'resolution_status' =>
                    TransactionDispute::RESOLUTION_STATUS_REFUND_SYNC_REQUIRED,

                'paystack_refund_amount_subunit' =>
                    $gatewayAmountSubunit,

                'refund_error' =>
                    'Paystack reported '
                    .
                    $gatewayAmountSubunit
                    .
                    ' subunits, but Midpoint expected net refund '
                    .
                    $expectedAmountSubunit
                    .
                    ' subunits. No seller settlement was credited.',
            ])->save();


            Log::critical(
                'Paystack refund amount mismatch; automatic settlement blocked.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'expected_amount_subunit' =>
                        $expectedAmountSubunit,

                    'gateway_amount_subunit' =>
                        $gatewayAmountSubunit,
                ]
            );


            $this->communications->resolutionUpdate(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ]),
                'dispute-refund-amount-review-' . $dispute->id,
                'Refund amount requires Midpoint review',
                'Midpoint detected an amount mismatch in the Paystack refund update. The seller payout remains locked and no seller settlement has been credited while Support reconciles it.',
                'Refund review'
            );


            return;
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
            (
                $dispute->refund_amount_subunit
                ?:
                DisputeRefundAllocation::majorToSubunit(
                    $dispute->refund_amount
                )
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

        /*
        |--------------------------------------------------------------------------
        | The expected Paystack amount is the NET buyer refund
        |--------------------------------------------------------------------------
        */

        $expectedAmountSubunit =
            (int)
            (
                $dispute->refund_amount_subunit
                ?:
                DisputeRefundAllocation::majorToSubunit(
                    $dispute->refund_amount
                )
            );


        $gatewayAmountSubunit =
            (int)
            (
                $gatewayData['amount']
                ??
                $dispute->paystack_refund_amount_subunit
                ??
                0
            );


        if (
            $expectedAmountSubunit <= 0
            ||
            $gatewayAmountSubunit !== $expectedAmountSubunit
        ) {

            throw new RuntimeException(
                'Refund finalization was blocked because the Paystack amount does not exactly match the net refund amount Midpoint submitted.'
            );
        }


        return DB::transaction(
            function () use (
                $dispute,
                $gatewayData,
                $gatewayAmountSubunit
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
                    | the seller side of the transaction. The approved buyer refund
                    | remains the original gross admin decision, even though the
                    | Paystack fee is withheld from the buyer's returned amount.
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

                    'paystack_refund_amount_subunit' =>
                        $gatewayAmountSubunit,

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

                            /* Actual net amount Paystack returned to buyer. */
                            'refund_amount' =>
                                (float)
                                $lockedDispute->refund_amount,

                            /* Gross amount originally approved by Midpoint. */
                            'approved_refund_amount' =>
                                (float)
                                (
                                    $lockedDispute->approved_refund_amount
                                    ??
                                    $lockedDispute->refund_amount
                                ),

                            'refund_gateway_fee_amount' =>
                                (float)
                                (
                                    $lockedDispute->refund_gateway_fee_amount
                                    ??
                                    0
                                ),

                            'paystack_refund_id' =>
                                $lockedDispute->paystack_refund_id,
                        ]
                    );
                }


                $approvedRefundAmount =
                    (float)
                    (
                        $lockedDispute->approved_refund_amount
                        ??
                        $lockedDispute->refund_amount
                    );


                $refundGatewayFeeAmount =
                    (float)
                    (
                        $lockedDispute->refund_gateway_fee_amount
                        ??
                        0
                    );


                $netRefundAmount =
                    (float)
                    $lockedDispute->refund_amount;


                $this->createSystemMessage(
                    $lockedDispute,
                    'Paystack confirmed the refund. Approved refund: ₦'
                    .
                    number_format(
                        $approvedRefundAmount,
                        2
                    )
                    .
                    '. Non-refundable Paystack processing fee deducted: ₦'
                    .
                    number_format(
                        $refundGatewayFeeAmount,
                        2
                    )
                    .
                    '. Net amount processed to the buyer: ₦'
                    .
                    number_format(
                        $netRefundAmount,
                        2
                    )
                    .
                    (
                        $isFullRefund

                            ? '. No seller payout was credited.'

                            : '. The approved seller settlement of ₦'
                                .
                                number_format(
                                    (float)
                                    $lockedDispute->seller_settlement_amount,
                                    2
                                )
                                .
                                ' has been credited to the seller Midpoint balance.'
                    )
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
                    'Paystack confirmed the refund for transaction '
                    .
                    $fresh->transaction->reference
                    .
                    '. Approved refund: ₦'
                    .
                    number_format(
                        (float)
                        (
                            $fresh->approved_refund_amount
                            ??
                            $fresh->refund_amount
                        ),
                        2
                    )
                    .
                    '. Non-refundable Paystack processing fee deducted: ₦'
                    .
                    number_format(
                        (float)
                        (
                            $fresh->refund_gateway_fee_amount
                            ??
                            0
                        ),
                        2
                    )
                    .
                    '. Net refund processed to the buyer: ₦'
                    .
                    number_format(
                        (float)
                        $fresh->refund_amount,
                        2
                    )
                    .
                    (
                        $isFullRefund

                            ? '. No seller payout was credited.'

                            : '. The remaining approved seller settlement is ₦'
                                .
                                number_format(
                                    (float)
                                    $fresh->seller_settlement_amount,
                                    2
                                )
                                .
                                '.'
                    ),
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