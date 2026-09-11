<?php

namespace App\Support;

use InvalidArgumentException;

final class DisputeRefundAllocation
{
    /**
     * Convert a major currency amount such as ₦250.50
     * into Paystack subunits such as 25050 kobo.
     *
     * String conversion is used so refund calculations
     * do not depend on floating-point multiplication.
     */
    public static function majorToSubunit(
        string|int|float|null $amount
    ): int {
        $normalized = trim((string) $amount);

        if (
            !preg_match(
                '/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/',
                $normalized
            )
        ) {
            throw new InvalidArgumentException(
                'The refund amount must be a valid amount with no more than two decimal places.'
            );
        }

        [$whole, $fraction] = array_pad(
            explode(
                '.',
                $normalized,
                2
            ),
            2,
            ''
        );

        $fraction = str_pad(
            $fraction,
            2,
            '0'
        );

        return
            ((int) $whole * 100)
            +
            (int) $fraction;
    }

    /**
     * Convert Paystack subunits back into a normal amount.
     *
     * Example:
     * 25050 becomes 250.50
     */
    public static function subunitToMajor(
        int $amountSubunit
    ): float {
        return round(
            $amountSubunit / 100,
            2
        );
    }

    /**
     * Calculate the financial allocation after a refund.
     *
     * For a partial refund:
     *
     * buyer refund = approved refund
     * retained amount = paid amount - refund
     * service fee = fee charged on retained amount
     * VAT = VAT charged on service fee
     * seller amount = retained amount - service fee - VAT
     */
    public static function calculate(
        int $paidSubunit,
        int $refundSubunit,
        float $serviceFeePercent,
        float $vatPercent
    ): array {
        if ($paidSubunit <= 0) {
            throw new InvalidArgumentException(
                'The paid amount must be greater than zero.'
            );
        }

        if (
            $refundSubunit < 0
            ||
            $refundSubunit > $paidSubunit
        ) {
            throw new InvalidArgumentException(
                'The refund amount cannot exceed the paid amount.'
            );
        }

        $retainedSubunit =
            $paidSubunit
            -
            $refundSubunit;

        $serviceFeeSubunit = (int) round(
            $retainedSubunit
            *
            max(
                0,
                $serviceFeePercent
            )
            /
            100,
            0,
            PHP_ROUND_HALF_UP
        );

        $vatSubunit = (int) round(
            $serviceFeeSubunit
            *
            max(
                0,
                $vatPercent
            )
            /
            100,
            0,
            PHP_ROUND_HALF_UP
        );

        $sellerSubunit = max(
            0,
            $retainedSubunit
            -
            $serviceFeeSubunit
            -
            $vatSubunit
        );

        return [
            'paid_subunit' =>
                $paidSubunit,

            'refund_subunit' =>
                $refundSubunit,

            'retained_subunit' =>
                $retainedSubunit,

            'service_fee_subunit' =>
                $serviceFeeSubunit,

            'vat_subunit' =>
                $vatSubunit,

            'seller_subunit' =>
                $sellerSubunit,

            'paid' =>
                self::subunitToMajor(
                    $paidSubunit
                ),

            'refund' =>
                self::subunitToMajor(
                    $refundSubunit
                ),

            'retained' =>
                self::subunitToMajor(
                    $retainedSubunit
                ),

            'service_fee' =>
                self::subunitToMajor(
                    $serviceFeeSubunit
                ),

            'vat' =>
                self::subunitToMajor(
                    $vatSubunit
                ),

            'seller_net' =>
                self::subunitToMajor(
                    $sellerSubunit
                ),
        ];
    }
}