<?php

namespace App\Services;

use RuntimeException;

class SecureTransactionFeeService
{
    public function calculate(
        float $subtotal,
        float $deliveryFee,
        ?float $paidAmount = null
    ): array {

        $serviceFeeRate =
            (float)
            config(
                'secure_transactions.service_fee_percent',
                5
            );


        $vatRate =
            (float)
            config(
                'secure_transactions.fee_vat_percent',
                7.5
            );


        if (
            $serviceFeeRate < 0
            ||
            $vatRate < 0
        ) {

            throw new RuntimeException(
                'Midpoint transaction fee configuration is invalid.'
            );
        }


        $subtotal =
            round(
                max(
                    0,
                    $subtotal
                ),
                2
            );


        $deliveryFee =
            round(
                max(
                    0,
                    $deliveryFee
                ),
                2
            );


        $feeBaseAmount =
            round(
                $subtotal
                +
                $deliveryFee,
                2
            );


        $paidAmount =
            $paidAmount === null

                ? $feeBaseAmount

                : round(
                    $paidAmount,
                    2
                );


        $serviceFeeAmount =
            round(
                $feeBaseAmount
                *
                (
                    $serviceFeeRate
                    /
                    100
                ),
                2
            );


        $vatAmount =
            round(
                $serviceFeeAmount
                *
                (
                    $vatRate
                    /
                    100
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


        return [

            'fee_base_amount' =>
                $feeBaseAmount,

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

        ];
    }
}
