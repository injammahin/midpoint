<?php

namespace App\Services;

use App\Exceptions\SellerPayoutSetupRequiredException;
use App\Models\SecureTransaction;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class SellerPayoutService
{
    public function __construct(
        protected PaystackService $paystack
    ) {
    }

    public function initiate(
        SecureTransaction $transaction
    ): array {
        $transaction->loadMissing('seller');

        $seller =
            $transaction->seller;

        if (!$seller) {
            throw new RuntimeException(
                'Seller account could not be found.'
            );
        }

        $recipientCode =
            $this->recipientCode(
                $seller
            );

        $amount =
            (float)
            $transaction->seller_net_amount;

        if ($amount <= 0) {
            throw new RuntimeException(
                'Seller payout amount is invalid.'
            );
        }

        $reference =
            $this->generateReference(
                $transaction
            );

        $data =
            $this->paystack
                ->initiateTransfer([
                    'source' =>
                        'balance',

                    'amount' =>
                        (int)
                        round(
                            $amount * 100
                        ),

                    'recipient' =>
                        $recipientCode,

                    'reference' =>
                        $reference,

                    'reason' =>
                        'MidPoint transaction '
                        . $transaction->reference,

                    'currency' =>
                        $transaction->currency
                        ?:
                        'NGN',
                ]);

        return [
            'reference' =>
                $reference,

            'transfer_code' =>
                $data['transfer_code']
                ??
                null,

            'status' =>
                strtolower(
                    (string) (
                        $data['status']
                        ??
                        'pending'
                    )
                ),

            'data' =>
                $data,
        ];
    }

    public function verify(
        SecureTransaction $transaction
    ): array {
        if (
            !$transaction->paystack_transfer_reference
        ) {
            throw new RuntimeException(
                'Seller payout reference is missing.'
            );
        }

        return $this
            ->paystack
            ->verifyTransfer(
                $transaction
                    ->paystack_transfer_reference
            );
    }

    protected function recipientCode(
        User $seller
    ): string {
        if (
            $seller->paystack_recipient_code
        ) {
            return $seller
                ->paystack_recipient_code;
        }

        if (
            !$seller->bank_account_number
            ||
            !$seller->bank_code
        ) {
            throw new SellerPayoutSetupRequiredException(
                'Seller payout bank setup is incomplete.'
            );
        }

        $data =
            $this->paystack
                ->createTransferRecipient([
                    'type' =>
                        'nuban',

                    'name' =>
                        $seller->bank_account_name
                        ?:
                        $seller->name,

                    'account_number' =>
                        $seller->bank_account_number,

                    'bank_code' =>
                        $seller->bank_code,

                    'currency' =>
                        'NGN',

                    'description' =>
                        'MidPoint seller payout account',
                ]);

        $recipientCode =
            $data['recipient_code']
            ??
            null;

        if (!$recipientCode) {
            throw new RuntimeException(
                'Paystack did not return a transfer recipient code.'
            );
        }

        $seller->forceFill([
            'paystack_recipient_code' =>
                $recipientCode,
        ])->save();

        return $recipientCode;
    }

    protected function generateReference(
        SecureTransaction $transaction
    ): string {
        return strtolower(
            'mp-payout-'
            . $transaction->id
            . '-'
            . now()->format('ymdHis')
            . '-'
            . Str::random(6)
        );
    }
}