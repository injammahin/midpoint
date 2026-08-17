<?php

namespace App\Services;

use App\Models\SecureTransaction;

class TransactionTimelineService
{
    public function build(
        SecureTransaction $transaction
    ): array {
        $rank = [
            SecureTransaction::STATUS_AWAITING_PAYMENT => 1,
            SecureTransaction::STATUS_PAYMENT_SECURED => 2,
            SecureTransaction::STATUS_PREPARING_ITEM => 3,
            SecureTransaction::STATUS_DISPATCHED => 4,
            SecureTransaction::STATUS_IN_TRANSIT => 5,
            SecureTransaction::STATUS_DELIVERED => 6,
            SecureTransaction::STATUS_INSPECTION => 7,
            SecureTransaction::STATUS_RELEASE_APPROVED => 8,
            SecureTransaction::STATUS_PAYOUT_PENDING => 8,
            SecureTransaction::STATUS_COMPLETED => 9,
        ];

        $current =
            $rank[
                $transaction->status
            ]
            ??
            0;

        $state =
            function (
                int $step
            ) use (
                $current,
                $transaction
            ) {
                if (
                    $transaction->status
                    ===
                    SecureTransaction::STATUS_DISPUTED
                ) {
                    return $step <= 6
                        ? 'done'
                        : 'pending';
                }

                if ($current > $step) {
                    return 'done';
                }

                if ($current === $step) {
                    return 'active';
                }

                return 'pending';
            };

        return [
            [
                'title' =>
                    'Transaction created',

                'state' =>
                    'done',

                'meta' =>
                    $transaction
                        ->created_at
                        ->format(
                            'd M, h:i A'
                        )
                    .
                    ' — Secure invite created',
            ],

            [
                'title' =>
                    'Invitation accepted',

                'state' =>
                    $transaction->claimed_at
                        ? 'done'
                        : 'pending',

                'meta' =>
                    $transaction->claimed_at
                        ? $transaction
                            ->claimed_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Buyer joined the transaction'
                        : 'Waiting for buyer',
            ],

            [
                'title' =>
                    'Payment received',

                'state' =>
                    $transaction->payment_status
                    ===
                    SecureTransaction::PAYMENT_PAID
                        ? 'done'
                        : 'active',

                'meta' =>
                    $transaction->paid_at
                        ? $transaction
                            ->paid_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — ₦'
                            .
                            number_format(
                                (float)
                                $transaction->paid_amount,
                                2
                            )
                            .
                            ' secured by Midpoint'
                        : 'Waiting for payment',
            ],

            [
                'title' =>
                    'Preparing item',

                'state' =>
                    $state(3),

                'meta' =>
                    $transaction->preparing_at
                        ? $transaction
                            ->preparing_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Seller started preparing item'
                        : 'Waiting for seller',
            ],

            [
                'title' =>
                    'Dispatched',

                'state' =>
                    $state(4),

                'meta' =>
                    $transaction->dispatched_at
                        ? $transaction
                            ->dispatched_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Seller marked item dispatched'
                        : 'Waiting for dispatch',
            ],

            [
                'title' =>
                    'In transit',

                'state' =>
                    $state(5),

                'meta' =>
                    $transaction->in_transit_at
                        ? $transaction
                            ->in_transit_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Delivery in progress'
                        : 'Starts after dispatch',
            ],

            [
                'title' =>
                    'Delivered',

                'state' =>
                    $state(6),

                'meta' =>
                    $transaction->delivered_at
                        ? $transaction
                            ->delivered_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Seller marked order delivered'
                        : 'Waiting for delivery confirmation',
            ],

            [
                'title' =>
                    'Inspection started',

                'state' =>
                    $transaction->status
                    ===
                    SecureTransaction::STATUS_INSPECTION
                        ? 'active'
                        : (
                            $transaction->inspection_started_at
                                ? 'done'
                                : 'pending'
                        ),

                'meta' =>
                    $transaction->inspection_ends_at
                        ? $transaction
                            ->inspection_hours
                            .
                            '-hour window — ends '
                            .
                            $transaction
                                ->inspection_ends_at
                                ->format(
                                    'd M, h:i A'
                                )
                        : 'Optional inspection after delivery',
            ],

            [
                'title' =>
                    'Funds released',

                'state' =>
                    $transaction->funds_released_at
                        ? 'done'
                        : (
                            in_array(
                                $transaction->status,
                                [
                                    SecureTransaction::STATUS_RELEASE_APPROVED,
                                    SecureTransaction::STATUS_PAYOUT_PENDING,
                                ],
                                true
                            )
                                ? 'active'
                                : 'pending'
                        ),

                'meta' =>
                    $transaction->funds_released_at
                        ? '₦'
                            .
                            number_format(
                                (float)
                                $transaction->seller_net_amount,
                                2
                            )
                            .
                            ' released to seller Midpoint balance'

                        : 'Released to seller Midpoint balance after acceptance or protection window',
            ],

            [
                'title' =>
                    'Completed',

                'state' =>
                    $transaction->status
                    ===
                    SecureTransaction::STATUS_COMPLETED
                        ? 'done'
                        : 'pending',

                'meta' =>
                    $transaction->completed_at
                        ? $transaction
                            ->completed_at
                            ->format(
                                'd M, h:i A'
                            )
                            .
                            ' — Transaction completed'
                        : 'Completes after successful seller payout',
            ],
        ];
    }
}