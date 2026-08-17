<?php

namespace App\Services;

use App\Models\SecureTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionLifecycleService
{
    public function __construct(
        protected TransactionCommunicationService $communications,
        protected SellerWalletService $wallets
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Fulfilment Status
    |--------------------------------------------------------------------------
    */

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


        /*
        |--------------------------------------------------------------------------
        | Preserve Your Existing Fulfilment Order
        |--------------------------------------------------------------------------
        */

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


        $transaction
            ->forceFill(
                $updates
            )
            ->save();


        $transaction->refresh();


        $this->notifyBuyerStatus(
            $transaction
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Starts Inspection
    |--------------------------------------------------------------------------
    */

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


        $transaction
            ->forceFill([

                'status' =>
                    SecureTransaction::STATUS_INSPECTION,

                'inspection_started_at' =>
                    $now,

                'inspection_ends_at' =>
                    $now
                        ->copy()
                        ->addHours(
                            $hours
                        ),

                'auto_complete_at' =>
                    $now
                        ->copy()
                        ->addHours(
                            $hours
                        ),

            ])
            ->save();


        $transaction->refresh();


        $this->communications->buyer(
            $transaction,
            'inspection-started',
            'Your inspection period has started',
            'Your '
            .
            $hours
            .
            '-hour inspection period is now active. You can accept the item or open a dispute before the countdown ends.'
        );


        $this->communications->seller(
            $transaction,
            'buyer-inspection-started',
            'Buyer started inspection',
            'The buyer started the '
            .
            $hours
            .
            '-hour inspection period for '
            .
            $transaction->title
            .
            '.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Accepts Order
    |--------------------------------------------------------------------------
    */

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


        /*
        |--------------------------------------------------------------------------
        | Buyer Acceptance Immediately Releases To Wallet
        |--------------------------------------------------------------------------
        */

        $this->releaseFunds(
            $transaction,
            'buyer_accept'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Automatic Release
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Approve + Release Funds
    |--------------------------------------------------------------------------
    */

    public function releaseFunds(
        SecureTransaction $transaction,
        string $source
    ): void {

        $notifyReleaseApproval =
            false;


        $canCreditWallet =
            false;


        DB::transaction(
            function () use (
                $transaction,
                &$notifyReleaseApproval,
                &$canCreditWallet
            ) {

                $locked =
                    SecureTransaction::query()
                        ->whereKey(
                            $transaction->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Already Finished
                |--------------------------------------------------------------------------
                */

                if (
                    $locked->status
                    ===
                    SecureTransaction::STATUS_COMPLETED
                    ||
                    $locked->funds_released_at
                ) {

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Legacy Bank Transfer Safety
                |--------------------------------------------------------------------------
                |
                | If your old application already initialized a bank transfer,
                | don't add the same transaction to the wallet.
                |
                */

                if (
                    $locked
                        ->paystack_transfer_reference
                ) {

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Was Already Release Approved
                |--------------------------------------------------------------------------
                |
                | This is important for your CURRENT stuck transaction.
                |
                */

                if (
                    $locked->status
                    ===
                    SecureTransaction::STATUS_RELEASE_APPROVED
                ) {

                    $locked
                        ->forceFill([

                            'payout_status' =>
                                SecureTransaction::PAYOUT_WALLET_PENDING,

                            'auto_complete_at' =>
                                null,

                        ])
                        ->save();


                    $canCreditWallet =
                        true;


                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | New Release
                |--------------------------------------------------------------------------
                */

                $locked
                    ->forceFill([

                        'status' =>
                            SecureTransaction::STATUS_RELEASE_APPROVED,

                        'release_approved_at' =>
                            $locked->release_approved_at
                            ?: now(),

                        'auto_complete_at' =>
                            null,

                        'payout_status' =>
                            SecureTransaction::PAYOUT_WALLET_PENDING,

                    ])
                    ->save();


                $notifyReleaseApproval =
                    true;


                $canCreditWallet =
                    true;
            }
        );


        $transaction->refresh();


        /*
        |--------------------------------------------------------------------------
        | Notify Approval
        |--------------------------------------------------------------------------
        */

        if (
            $notifyReleaseApproval
        ) {

            $this->notifyReleaseApproved(
                $transaction,
                $source
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Add Money To Seller Midpoint Wallet
        |--------------------------------------------------------------------------
        */

        if (
            $canCreditWallet
        ) {

            $this->creditApprovedReleaseToWallet(
                $transaction
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Credit Already-Approved Release To Wallet
    |--------------------------------------------------------------------------
    |
    | Public because scheduled processing can recover a transaction that was
    | release_approved but was not completed because an earlier request ended.
    |
    */

    public function creditApprovedReleaseToWallet(
        SecureTransaction $transaction
    ): void {

        $transaction->refresh();


        /*
        |--------------------------------------------------------------------------
        | Already Completed
        |--------------------------------------------------------------------------
        */

        if (
            $transaction->status
            ===
            SecureTransaction::STATUS_COMPLETED
            &&
            $transaction->funds_released_at
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Only Release-Approved Transactions
        |--------------------------------------------------------------------------
        */

        if (
            $transaction->status
            !==
            SecureTransaction::STATUS_RELEASE_APPROVED
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Touch Legacy Bank Transfer
        |--------------------------------------------------------------------------
        */

        if (
            $transaction
                ->paystack_transfer_reference
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Credit Seller Wallet
        |--------------------------------------------------------------------------
        */

        $result =
            $this->wallets
                ->creditTransactionRelease(
                    $transaction
                );


        $transaction->refresh();


        /*
        |--------------------------------------------------------------------------
        | Don't Repeat Notifications
        |--------------------------------------------------------------------------
        */

        if (
            !(
                $result['completed_now']
                ??
                false
            )
        ) {

            return;
        }


        $amount =
            number_format(
                (float)
                $transaction->seller_net_amount,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Notification
        |--------------------------------------------------------------------------
        */

        $this->communications->seller(
            $transaction,
            'funds-added-to-midpoint-wallet',
            'Funds added to your Midpoint balance',
            '₦'
            .
            $amount
            .
            ' has been released from escrow and added to your Midpoint balance.'
        );


        /*
        |--------------------------------------------------------------------------
        | Buyer Notification
        |--------------------------------------------------------------------------
        */

        $this->communications->buyer(
            $transaction,
            'transaction-completed',
            'Transaction completed',
            'The transaction is complete. The seller funds have been released from escrow to the seller\'s Midpoint balance.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Legacy Paystack Transfer Handler
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Do not remove this yet.
    |
    | Existing transactions may already have real Paystack transfers created
    | using your previous direct-bank algorithm.
    |
    */

    public function handleTransferStatus(
        SecureTransaction $transaction,
        string $status
    ): void {

        $status =
            strtolower(
                $status
            );


        if (
            $status
            ===
            'success'
        ) {

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

            $transaction
                ->forceFill([

                    'status' =>
                        SecureTransaction::STATUS_RELEASE_APPROVED,

                    'payout_status' =>
                        $status,

                ])
                ->save();


            $this->communications->seller(
                $transaction,
                'payout-' . $status,
                'Legacy payout requires attention',
                'Paystack reported the previously initiated seller bank transfer as '
                .
                $status
                .
                '. Midpoint will keep this legacy transaction open while it is resolved.'
            );


            return;
        }


        $transaction
            ->forceFill([

                'status' =>
                    SecureTransaction::STATUS_PAYOUT_PENDING,

                'payout_status' =>
                    $status,

            ])
            ->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Old Direct-Bank Payout
    |--------------------------------------------------------------------------
    |
    | Used only for transfers that were created BEFORE this wallet change.
    |
    */

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


        $transaction
            ->forceFill([

                'status' =>
                    SecureTransaction::STATUS_COMPLETED,

                'payout_status' =>
                    SecureTransaction::PAYOUT_SUCCESS,

                'funds_released_at' =>
                    now(),

                'payout_completed_at' =>
                    now(),

                'completed_at' =>
                    now(),

                'auto_complete_at' =>
                    null,

            ])
            ->save();


        $transaction->refresh();


        $this->communications->seller(
            $transaction,
            'transaction-completed',
            'Transaction completed',
            'Your previously initiated seller bank payout of ₦'
            .
            number_format(
                (float)
                $transaction->seller_net_amount,
                2
            )
            .
            ' has been successfully processed.'
        );


        $this->communications->buyer(
            $transaction,
            'transaction-completed',
            'Transaction completed',
            'The transaction has been completed successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Release Notifications
    |--------------------------------------------------------------------------
    */

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
                'You accepted the item. The seller funds are now being released to the seller\'s Midpoint balance.';


            $sellerTitle =
                'Buyer accepted the order';


            $sellerMessage =
                'The buyer accepted '
                .
                $transaction->title
                .
                '. Your net funds are now being released from escrow to your Midpoint balance.';

        } elseif (
            $source
            ===
            'inspection_expired'
        ) {

            $buyerTitle =
                'Inspection period completed';


            $buyerMessage =
                'Your inspection period ended without a dispute. The transaction has been automatically approved for release to the seller\'s Midpoint balance.';


            $sellerTitle =
                'Inspection period completed';


            $sellerMessage =
                'The buyer did not open a dispute during the inspection period. Your net funds are being released to your Midpoint balance.';

        } else {

            $buyerTitle =
                'Transaction automatically approved';


            $buyerMessage =
                'The 3-day delivery protection period ended without buyer action or a dispute. The seller funds have been approved for release to the seller\'s Midpoint balance.';


            $sellerTitle =
                'Transaction automatically approved';


            $sellerMessage =
                'The 3-day delivery protection period ended without a dispute. Your net funds are being released to your Midpoint balance.';
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


    /*
    |--------------------------------------------------------------------------
    | Buyer Fulfilment Notification
    |--------------------------------------------------------------------------
    */

    protected function notifyBuyerStatus(
        SecureTransaction $transaction
    ): void {

        [$title, $message] =
            match (
                $transaction->status
            ) {

                SecureTransaction::STATUS_PREPARING_ITEM => [

                    'Seller is preparing your item',

                    'The seller has started preparing '
                    .
                    $transaction->title
                    .
                    ' for delivery.',

                ],


                SecureTransaction::STATUS_DISPATCHED => [

                    'Your order has been dispatched',

                    'The seller marked '
                    .
                    $transaction->title
                    .
                    ' as dispatched.',

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

                    'Your Midpoint transaction has been updated.',

                ],
            };


        $this->communications->buyer(
            $transaction,
            $transaction->status,
            $title,
            $message
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Authorization
    |--------------------------------------------------------------------------
    */

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