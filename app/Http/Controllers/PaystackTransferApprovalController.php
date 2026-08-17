<?php

namespace App\Http\Controllers;

use App\Models\SellerKycVerification;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackTransferApprovalController extends Controller
{
    public function approve(
        Request $request,
        string $token
    ) {

        /*
        |--------------------------------------------------------------------------
        | Verify Approval URL Secret
        |--------------------------------------------------------------------------
        */

        $configuredToken =
            trim(
                (string) config(
                    'services.paystack.transfer_approval_token'
                )
            );


        if (
            $configuredToken === ''
            ||
            !hash_equals(
                $configuredToken,
                $token
            )
        ) {

            Log::warning(
                'Rejected Paystack transfer approval request with invalid token.'
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Paystack Transfer Payload
        |--------------------------------------------------------------------------
        */

        $reference =
            trim(
                (string) $request->input(
                    'reference',
                    ''
                )
            );


        $amountInKobo =
            (int) $request->input(
                'amount',
                0
            );


        $source =
            strtolower(
                trim(
                    (string) $request->input(
                        'source',
                        ''
                    )
                )
            );


        $currency =
            strtoupper(
                trim(
                    (string) $request->input(
                        'currency',
                        'NGN'
                    )
                )
            );


        $recipientFromPayload =
            trim(
                (string) $request->input(
                    'recipient',
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        if (
            $reference === ''
            ||
            $amountInKobo <= 0
            ||
            $source !== 'balance'
        ) {

            Log::warning(
                'Rejected malformed Paystack transfer approval payload.',
                [
                    'reference' =>
                        $reference,

                    'amount' =>
                        $amountInKobo,

                    'source' =>
                        $source,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Withdrawal Must Already Exist
        |--------------------------------------------------------------------------
        */

        $withdrawal =
            SellerWithdrawal::query()

                ->where(
                    'paystack_transfer_reference',
                    $reference
                )

                ->first();


        if (
            !$withdrawal
        ) {

            Log::warning(
                'Rejected Paystack approval because withdrawal was not found.',
                [
                    'reference' =>
                        $reference,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Match Amount
        |--------------------------------------------------------------------------
        */

        $expectedAmountInKobo =
            (int) round(
                (
                    (float) $withdrawal->amount
                )
                *
                100
            );


        if (
            $amountInKobo !==
            $expectedAmountInKobo
        ) {

            Log::warning(
                'Rejected Paystack approval because amount did not match.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,

                    'expected' =>
                        $expectedAmountInKobo,

                    'received' =>
                        $amountInKobo,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Match Currency
        |--------------------------------------------------------------------------
        */

        if (
            $currency !==
            strtoupper(
                $withdrawal->currency
                ?:
                'NGN'
            )
        ) {

            Log::warning(
                'Rejected Paystack approval because currency did not match.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Never Approve Final Withdrawal Again
        |--------------------------------------------------------------------------
        */

        if (
            $withdrawal->isFinal()
        ) {

            Log::warning(
                'Rejected Paystack approval for final withdrawal.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,

                    'status' =>
                        $withdrawal->status,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Seller KYC Must Be Approved
        |--------------------------------------------------------------------------
        */

        $kycApproved =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $withdrawal->seller_id
                )

                ->where(
                    'status',
                    SellerKycVerification::STATUS_APPROVED
                )

                ->exists();


        if (
            !$kycApproved
        ) {

            Log::warning(
                'Rejected Paystack approval because KYC is not approved.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,

                    'seller_id' =>
                        $withdrawal->seller_id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Withdrawal Bank Must Still Be Valid
        |--------------------------------------------------------------------------
        */

        $account =
            SellerWithdrawalAccount::query()

                ->whereKey(
                    $withdrawal
                        ->seller_withdrawal_account_id
                )

                ->where(
                    'seller_id',
                    $withdrawal->seller_id
                )

                ->where(
                    'is_verified',
                    true
                )

                ->first();


        if (
            !$account
            ||
            !$account->paystack_recipient_code
        ) {

            Log::warning(
                'Rejected Paystack approval because bank account is invalid.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Stored Recipient Must Match Withdrawal Recipient
        |--------------------------------------------------------------------------
        */

        if (
            !hash_equals(
                (string)
                $withdrawal
                    ->paystack_recipient_code,

                (string)
                $account
                    ->paystack_recipient_code
            )
        ) {

            Log::warning(
                'Rejected Paystack approval because recipient changed.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Match Paystack Recipient If It Returns RCP_ Code
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $recipientFromPayload,
                'RCP_'
            )
            &&
            !hash_equals(
                (string)
                $withdrawal
                    ->paystack_recipient_code,

                $recipientFromPayload
            )
        ) {

            Log::warning(
                'Rejected Paystack approval because request recipient did not match.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Wallet Reservation Must Still Exist
        |--------------------------------------------------------------------------
        */

        $wallet =
            SellerWallet::query()

                ->whereKey(
                    $withdrawal
                        ->seller_wallet_id
                )

                ->where(
                    'seller_id',
                    $withdrawal->seller_id
                )

                ->first();


        if (
            !$wallet
            ||
            (
                (float)
                $wallet
                    ->pending_withdrawal_balance
            )
            +
            0.001
            <
            (
                (float)
                $withdrawal
                    ->amount
            )
        ) {

            Log::warning(
                'Rejected Paystack approval because wallet reservation is missing.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Wallet Ledger Must Exist
        |--------------------------------------------------------------------------
        */

        $ledgerExists =
            SellerWalletTransaction::query()

                ->where(
                    'seller_withdrawal_id',
                    $withdrawal->id
                )

                ->where(
                    'type',
                    SellerWalletTransaction::TYPE_WITHDRAWAL_REQUEST
                )

                ->exists();


        if (
            !$ledgerExists
        ) {

            Log::warning(
                'Rejected Paystack approval because withdrawal ledger is missing.',
                [
                    'withdrawal_id' =>
                        $withdrawal->id,
                ]
            );


            return response()->json(
                [],
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | APPROVED
        |--------------------------------------------------------------------------
        |
        | Do NOT run any external HTTP requests here.
        |
        | Paystack expects a very fast response.
        |
        */

        Log::info(
            'Approved automatic Paystack seller withdrawal.',
            [
                'withdrawal_id' =>
                    $withdrawal->id,

                'seller_id' =>
                    $withdrawal->seller_id,

                'reference' =>
                    $reference,

                'amount' =>
                    $withdrawal->amount,
            ]
        );


        return response()->json(
            [],
            200
        );
    }
}