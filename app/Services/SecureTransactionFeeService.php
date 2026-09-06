<?php

namespace App\Services;

use RuntimeException;

class SecureTransactionFeeService
{
    /*
    |--------------------------------------------------------------------------
    | Calculate Secure Transaction Seller Charges
    |--------------------------------------------------------------------------
    |
    | Midpoint's service fee is charged on the full transaction amount held
    | in escrow:
    |
    |     product subtotal + delivery fee
    |
    | VAT is then calculated only on the Midpoint service fee.
    |
    */

    public function calculate(
        float $subtotal,
        float $deliveryFee,
        ?float $paidAmount = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Fee Configuration
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Validate Configuration
        |--------------------------------------------------------------------------
        */

        if (
            $serviceFeeRate < 0
            ||
            $vatRate < 0
        ) {

            throw new RuntimeException(
                'Midpoint transaction fee configuration is invalid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Money
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Fee Base
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The Midpoint service fee is calculated from:
        |
        | Product subtotal + Delivery fee
        |
        */

        $feeBaseAmount =
            round(
                $subtotal
                +
                $deliveryFee,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Actual Paid Amount
        |--------------------------------------------------------------------------
        |
        | Normally this is exactly the same as the fee base amount.
        |
        | We still use the Paystack verified paid amount for the final seller
        | payout so that the financial record is based on the verified payment.
        |
        */

        $paidAmount =
            $paidAmount === null

                ?

                $feeBaseAmount

                :

                round(
                    $paidAmount,
                    2
                );


        /*
        |--------------------------------------------------------------------------
        | Midpoint Service Fee
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | VAT
        |--------------------------------------------------------------------------
        |
        | VAT is ONLY on the Midpoint service fee.
        |
        */

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


        /*
        |--------------------------------------------------------------------------
        | Seller Net Amount
        |--------------------------------------------------------------------------
        */

        $sellerNetAmount =
            round(
                $paidAmount
                -
                $serviceFeeAmount
                -
                $vatAmount,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        */

        if (
            $sellerNetAmount < 0
        ) {

            throw new RuntimeException(
                'Calculated seller payout amount is invalid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

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