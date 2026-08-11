<?php

namespace App\Services;

use App\Exceptions\SellerPayoutSetupRequiredException;
use App\Models\SecureTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransactionLifecycleService
{
    public function __construct(
        protected TransactionCommunicationService $communications,
        protected SellerPayoutService $payouts
    ) {
    }

    public function sellerUpdate(
        SecureTransaction $transaction,
        User $seller,
        string $nextStatus
    ): void {
        abort_unless(
            (int) $transaction->seller_id
            ===
            (int) $seller->id,
            403
        );

        if (
            $transaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'The buyer payment must be secured first.',
            ]);
        }

        if (
            $transaction->status
            ===
            SecureTransaction::STATUS_DISPUTED
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'This transaction is currently disputed.',
            ]);
        }

        $allowed = [
            SecureTransaction::STATUS_PAYMENT_SECURED =>
                SecureTransaction::STATUS_PREPARING_ITEM,

            SecureTransaction::STATUS_PREPARING_ITEM =>
                SecureTransaction::STATUS_DISPATCHED,

            SecureTransaction::STATUS_DISPATCHED =>
                SecureTransaction::STATUS_IN_TRANSIT,

            SecureTransaction::STATUS_IN_TRANSIT =>
                SecureTransaction::STATUS_DELIVERED,
        ];

        if (
            !isset(
                $allowed[
                    $transaction->status
                ]
            )
            ||
            $allowed[
                $transaction->status
            ]
            !==
            $nextStatus
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'That transaction status change is not allowed.',
            ]);
        }

        $updates = [
            'status' =>
                $nextStatus,
        ];

        if (
            $nextStatus
            ===
            SecureTransaction::STATUS_PREPARING_ITEM
        ) {
            $updates['preparing_at'] =
                now();
        }

        if (
            $nextStatus
            ===
            SecureTransaction::STATUS_DISPATCHED
        ) {
            $updates['dispatched_at'] =
                now();
        }

        if (
            $nextStatus
            ===
            SecureTransaction::STATUS_IN_TRANSIT
        ) {
            $updates['in_transit_at'] =
                now();
        }

        if (
            $nextStatus
            ===
            SecureTransaction::STATUS_DELIVERED
        ) {
            $hours =
                (int)
                config(
                    'secure_transactions.delivery_auto_complete_hours',
                    72
                );

            $updates['delivered_at'] =
                now();

            $updates['auto_complete_at'] =
                now()->addHours(
                    $hours
                );
        }

        $transaction->forceFill(
            $updates
        )->save();

        $transaction->refresh();

        $this->notifyBuyerStatus(
            $transaction
        );
    }

    public function startInspection(
        SecureTransaction $transaction,
        User $buyer
    ): void {
        $this->authorizeBuyer(
            $transaction,
            $buyer
        );

        if (
            $transaction->status
            !==
            SecureTransaction::STATUS_DELIVERED
        ) {
            throw ValidationException::withMessages([
                'transaction' =>
                    'Inspection can only start after the seller marks the order delivered.',
            ]);
        }

        $hours =
            (int)
            config(
                'secure_transactions.inspection_hours',
                8
            );

        $now =
            now();

        $transaction->forceFill([
            'status' =>
                SecureTransaction::STATUS_INSPECTION,

            'inspection_started_at' =>
                $now,

            'inspection_ends_at' =>
                $now->copy()->addHours(
                    $hours
                ),

            'auto_complete_at' =>
                $now->copy()->addHours(
                    $hours
                ),
        ])->save();

        $transaction->refresh();

        $this->communications->buyer(
            $transaction,
            'inspection-started',
            'Your inspection period has started',
            'Your '
            . $hours
            . '-hour inspection period is now active. You can accept the item or open a dispute before the countdown ends.'
        );

        $this->communications->seller(
            $transaction,
            'buyer-inspection-started',
            'Buyer started inspection',
            'The buyer started the '
            . $hours
            . '-hour inspection period for '
            . $transaction->title
            . '.'
        );
    }

    public function acceptAndRelease(
        SecureTransaction $transaction,
        User $buyer
    ): void {
        $this->authorizeBuyer(
            $transaction,
            $buyer
        );

        $transaction->loadMissing(
            'dispute'
        );

        if (
            !in_array(
                $transaction->status,
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'transaction' =>
                    'This transaction cannot currently be accepted.',
            ]);
        }

        if (
            $transaction->dispute
            &&
            $transaction->dispute->status
            !==
            'resolved'
        ) {
            throw ValidationException::withMessages([
                'transaction' =>
                    'This transaction currently has an open dispute.',
            ]);
        }

        $this->releaseFunds(
            $transaction,
            'buyer_accept'
        );
    }

    public function autoRelease(
        SecureTransaction $transaction
    ): void {
        $transaction->loadMissing(
            'dispute'
        );

        if (
            !$transaction->auto_complete_at
            ||
            $transaction
                ->auto_complete_at
                ->isFuture()
        ) {
            return;
        }

        if (
            !in_array(
                $transaction->status,
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ],
                true
            )
        ) {
            return;
        }

        if (
            $transaction->dispute
            &&
            $transaction->dispute->status
            !==
            'resolved'
        ) {
            return;
        }

        $source =
            $transaction->status
            ===
            SecureTransaction::STATUS_INSPECTION
                ? 'inspection_expired'
                : 'delivery_window_expired';

        $this->releaseFunds(
            $transaction,
            $source
        );
    }

    public function releaseFunds(
        SecureTransaction $transaction,
        string $source
    ): void {
        DB::transaction(
            function () use (
                $transaction
            ) {
                $locked =
                    SecureTransaction::query()
                        ->whereKey(
                            $transaction->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    in_array(
                        $locked->status,
                        [
                            SecureTransaction::STATUS_RELEASE_APPROVED,
                            SecureTransaction::STATUS_PAYOUT_PENDING,
                            SecureTransaction::STATUS_COMPLETED,
                        ],
                        true
                    )
                ) {
                    return;
                }

                $locked->forceFill([
                    'status' =>
                        SecureTransaction::STATUS_RELEASE_APPROVED,

                    'release_approved_at' =>
                        now(),

                    'auto_complete_at' =>
                        null,

                    'payout_status' =>
                        'initializing',
                ])->save();
            }
        );

        $transaction->refresh();

        $this->notifyReleaseApproved(
            $transaction,
            $source
        );

        $this->attemptPayout(
            $transaction
        );
    }

    public function retryPayout(
        SecureTransaction $transaction
    ): void {
        if (
            $transaction->status
            !==
            SecureTransaction::STATUS_RELEASE_APPROVED
        ) {
            return;
        }

        $this->attemptPayout(
            $transaction
        );
    }

    protected function attemptPayout(
        SecureTransaction $transaction
    ): void {
        try {
            $result =
                $this->payouts
                    ->initiate(
                        $transaction
                    );

            $status =
                strtolower(
                    $result['status']
                    ??
                    'pending'
                );

            if ($status === 'otp') {
                $status =
                    'pending';
            }

            $transaction->forceFill([
                'status' =>
                    SecureTransaction::STATUS_PAYOUT_PENDING,

                'paystack_transfer_reference' =>
                    $result['reference'],

                'paystack_transfer_code' =>
                    $result['transfer_code'],

                'payout_status' =>
                    $status,

                'payout_initiated_at' =>
                    now(),
            ])->save();

            $transaction->refresh();

            $this->communications->seller(
                $transaction,
                'seller-payout-processing',
                'Your payout is processing',
                'MidPoint has approved this transaction for release. Your payout of ₦'
                . number_format(
                    (float)
                    $transaction->seller_net_amount,
                    2
                )
                . ' is being processed.'
            );

            if ($status === 'success') {
                $this->completePayout(
                    $transaction
                );
            }

        } catch (
            SellerPayoutSetupRequiredException
            $exception
        ) {
            $transaction->forceFill([
                'status' =>
                    SecureTransaction::STATUS_RELEASE_APPROVED,

                'payout_status' =>
                    'seller_setup_required',
            ])->save();

            $this->communications->seller(
                $transaction,
                'payout-bank-details-required',
                'Payout setup required',
                'The buyer has approved this order, but MidPoint cannot send your payout until your bank account and bank code are configured.'
            );

            Log::warning(
                'Seller payout waiting for bank setup.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'seller_id' =>
                        $transaction->seller_id,
                ]
            );

        } catch (Throwable $exception) {
            $transaction->forceFill([
                'status' =>
                    SecureTransaction::STATUS_RELEASE_APPROVED,

                'payout_status' =>
                    'failed',
            ])->save();

            Log::error(
                'Seller payout initiation failed.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            $this->communications->seller(
                $transaction,
                'seller-payout-failed',
                'Your payout needs attention',
                'The transaction was approved, but the payout could not be completed yet. Your funds remain pending while MidPoint retries or reviews the payout.'
            );
        }
    }

    public function handleTransferStatus(
        SecureTransaction $transaction,
        string $status
    ): void {
        $status =
            strtolower(
                $status
            );

        if ($status === 'success') {
            $this->completePayout(
                $transaction
            );

            return;
        }

        if (
            in_array(
                $status,
                [
                    'failed',
                    'reversed',
                ],
                true
            )
        ) {
            $transaction->forceFill([
                'status' =>
                    SecureTransaction::STATUS_RELEASE_APPROVED,

                'payout_status' =>
                    $status,
            ])->save();

            $this->communications->seller(
                $transaction,
                'payout-' . $status,
                'Payout requires attention',
                'Paystack reported the seller payout as '
                . $status
                . '. MidPoint will keep the transaction open while the payout is resolved.'
            );

            return;
        }

        $transaction->forceFill([
            'status' =>
                SecureTransaction::STATUS_PAYOUT_PENDING,

            'payout_status' =>
                $status,
        ])->save();
    }

    public function completePayout(
        SecureTransaction $transaction
    ): void {
        $transaction->refresh();

        if (
            $transaction->status
            ===
            SecureTransaction::STATUS_COMPLETED
        ) {
            return;
        }

        $transaction->forceFill([
            'status' =>
                SecureTransaction::STATUS_COMPLETED,

            'payout_status' =>
                'success',

            'funds_released_at' =>
                now(),

            'payout_completed_at' =>
                now(),

            'completed_at' =>
                now(),

            'auto_complete_at' =>
                null,
        ])->save();

        $transaction->refresh();

        $this->communications->seller(
            $transaction,
            'transaction-completed',
            'Transaction completed',
            'Your seller payout of ₦'
            . number_format(
                (float)
                $transaction->seller_net_amount,
                2
            )
            . ' has been successfully processed.'
        );

        $this->communications->buyer(
            $transaction,
            'transaction-completed',
            'Transaction completed',
            'The transaction has been completed successfully.'
        );
    }

    protected function notifyReleaseApproved(
        SecureTransaction $transaction,
        string $source
    ): void {
        if (
            $source
            ===
            'buyer_accept'
        ) {
            $buyerTitle =
                'Order accepted successfully';

            $buyerMessage =
                'You accepted the item. Seller payout has now been approved.';

            $sellerTitle =
                'Buyer accepted the order';

            $sellerMessage =
                'The buyer accepted '
                .
                $transaction->title
                .
                '. The transaction has been approved for seller payout.';

        } elseif (
            $source
            ===
            'inspection_expired'
        ) {
            $buyerTitle =
                'Inspection period completed';

            $buyerMessage =
                'Your inspection period ended without a dispute. The transaction has been automatically approved for seller payout.';

            $sellerTitle =
                'Inspection period completed';

            $sellerMessage =
                'The buyer did not open a dispute during the inspection period. The transaction has been automatically approved for payout.';

        } else {
            $buyerTitle =
                'Transaction automatically approved';

            $buyerMessage =
                'The 3-day delivery protection period ended without buyer action or a dispute. Seller payout has been approved.';

            $sellerTitle =
                'Transaction automatically approved';

            $sellerMessage =
                'The 3-day delivery protection period ended without a dispute. Your transaction has been automatically approved for payout.';
        }


        $this->communications->buyer(
            $transaction,
            'release-' . $source,
            $buyerTitle,
            $buyerMessage
        );


        $this->communications->seller(
            $transaction,
            'release-' . $source,
            $sellerTitle,
            $sellerMessage
        );
    }

    protected function notifyBuyerStatus(
        SecureTransaction $transaction
    ): void {
        [$title, $message] =
            match ($transaction->status) {

                SecureTransaction::STATUS_PREPARING_ITEM => [
                    'Seller is preparing your item',
                    'The seller has started preparing '
                    . $transaction->title
                    . ' for delivery.',
                ],

                SecureTransaction::STATUS_DISPATCHED => [
                    'Your order has been dispatched',
                    'The seller marked '
                    . $transaction->title
                    . ' as dispatched.',
                ],

                SecureTransaction::STATUS_IN_TRANSIT => [
                    'Your order is in transit',
                    'Your order is currently in transit.',
                ],

                SecureTransaction::STATUS_DELIVERED => [
                    'Seller marked your order delivered',
                    'The seller marked your order as delivered. Your 3-day protection countdown has started. You can accept the item, begin the 8-hour inspection period, or open a dispute.',
                ],

                default => [
                    'Transaction updated',
                    'Your MidPoint transaction has been updated.',
                ],
            };

        $this->communications->buyer(
            $transaction,
            $transaction->status,
            $title,
            $message
        );
    }

    protected function authorizeBuyer(
        SecureTransaction $transaction,
        User $buyer
    ): void {
        abort_unless(
            (int) $transaction->buyer_id
            ===
            (int) $buyer->id,
            403
        );
    }
}