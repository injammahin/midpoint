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
    public function approve(Request $request, string $token)
    {
        /*
        |--------------------------------------------------------------------------
        | Verify Approval URL Secret
        |--------------------------------------------------------------------------
        */

        $configuredToken = trim(
            (string) config(
                'services.paystack.transfer_approval_token'
            )
        );

        if (
            $configuredToken === ''
            ||
            !hash_equals($configuredToken, $token)
        ) {
            Log::warning(
                'Rejected Paystack transfer approval request with invalid token.',
                [
                    'request_method' => $request->method(),
                    'request_ip' => $request->ip(),
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Read Paystack Transfer Payload
        |--------------------------------------------------------------------------
        |
        | Laravel normally parses JSON and form-data automatically. However,
        | this also supports raw JSON, raw URL-encoded data and payloads wrapped
        | inside data, transfer or payload objects.
        |
        */

        $rawBody = (string) $request->getContent();

        $payload = $request->all();

        /*
         * Try Laravel's JSON request bag.
         */
        if (empty($payload)) {
            $jsonPayload = $request->json()->all();

            if (is_array($jsonPayload) && !empty($jsonPayload)) {
                $payload = $jsonPayload;
            }
        }

        /*
         * Try decoding the raw request body as JSON.
         */
        if (empty($payload) && $rawBody !== '') {
            $decodedPayload = json_decode($rawBody, true);

            if (
                json_last_error() === JSON_ERROR_NONE
                &&
                is_array($decodedPayload)
            ) {
                $payload = $decodedPayload;
            }
        }

        /*
         * Try decoding the raw request body as URL-encoded form data.
         */
        if (empty($payload) && $rawBody !== '') {
            $formPayload = [];

            parse_str($rawBody, $formPayload);

            if (is_array($formPayload) && !empty($formPayload)) {
                $payload = $formPayload;
            }
        }

        /*
         * Paystack may wrap the actual transfer information.
         */
        foreach (['data', 'transfer', 'payload'] as $wrapper) {
            if (!array_key_exists($wrapper, $payload)) {
                continue;
            }

            $wrappedPayload = $payload[$wrapper];

            if (is_array($wrappedPayload)) {
                $payload = $wrappedPayload;
                break;
            }

            if (is_string($wrappedPayload) && $wrappedPayload !== '') {
                $decodedWrappedPayload = json_decode(
                    $wrappedPayload,
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    &&
                    is_array($decodedWrappedPayload)
                ) {
                    $payload = $decodedWrappedPayload;
                    break;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Extract Paystack Transfer Approval Envelope
        |--------------------------------------------------------------------------
        |
        | Paystack sends transfer approval requests using an envelope:
        |
        | {
        |     "integration": ...,
        |     "domain": "live",
        |     "details": {...},
        |     "transfers": [
        |         {
        |             "reference": "...",
        |             "amount": 50000,
        |             "recipient": "..."
        |         }
        |     ]
        | }
        |
        | Midpoint initiates one withdrawal per request, so exactly one transfer
        | must be present. Rejecting multiple transfers prevents an unrelated
        | batch from being approved accidentally.
        |
        */

        if (array_key_exists('transfers', $payload)) {
            $transfers = $payload['transfers'];

            if (
                !is_array($transfers)
                ||
                count($transfers) !== 1
            ) {
                Log::warning(
                    'Rejected Paystack approval because transfer batch was invalid.',
                    [
                        'transfer_count' => is_array($transfers)
                            ? count($transfers)
                            : 0,

                        'payload_keys' => array_keys($payload),
                        'request_ip' => $request->ip(),
                    ]
                );

                return response()->json([], 400);
            }

            /*
            * array_values supports both numeric and associative array indexes.
            */
            $transferPayload = array_values($transfers)[0];

            if (!is_array($transferPayload)) {
                Log::warning(
                    'Rejected Paystack approval because transfer item was invalid.',
                    [
                        'request_ip' => $request->ip(),
                    ]
                );

                return response()->json([], 400);
            }

            $approvalDetails = [];

            if (
                isset($payload['details'])
                &&
                is_array($payload['details'])
            ) {
                $approvalDetails = $payload['details'];
            }

            /*
            * Transfer-specific values take priority over envelope details.
            */
            $payload = array_merge(
                $approvalDetails,
                $transferPayload
            );

            /*
            * Paystack's approval envelope may omit source from each transfer.
            * Paystack currently supports "balance" as the transfer source.
            */
            if (!isset($payload['source'])) {
                $payload['source'] =
                    $approvalDetails['source']
                    ??
                    'balance';
            }

            /*
            * Midpoint withdrawals use NGN unless Paystack provides a currency.
            */
            if (!isset($payload['currency'])) {
                $payload['currency'] =
                    $approvalDetails['currency']
                    ??
                    'NGN';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Payload Values
        |--------------------------------------------------------------------------
        |
        | This must run after the Paystack approval envelope has been extracted.
        |
        */

        $reference = trim(
            (string) ($payload['reference'] ?? '')
        );

        $amountInKobo = (int) (
            $payload['amount'] ?? 0
        );

        $sourceValue = $payload['source'] ?? '';

        if (is_array($sourceValue)) {
            $sourceValue =
                $sourceValue['source']
                ??
                $sourceValue['type']
                ??
                '';
        }

        $source = strtolower(
            trim((string) $sourceValue)
        );

        $currency = strtoupper(
            trim(
                (string) ($payload['currency'] ?? 'NGN')
            )
        );

        $recipientValue = $payload['recipient'] ?? '';

        if (is_array($recipientValue)) {
            $recipientValue =
                $recipientValue['recipient_code']
                ??
                $recipientValue['code']
                ??
                $recipientValue['id']
                ??
                '';
        }

        $recipientFromPayload = trim(
            (string) $recipientValue
        );

        /*
        |--------------------------------------------------------------------------
        | Basic Payload Validation
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
                    'reference' => $reference,
                    'amount' => $amountInKobo,
                    'source' => $source,
                    'request_method' => $request->method(),
                    'content_type' => $request->header('Content-Type'),
                    'content_length' => $request->header('Content-Length'),
                    'raw_body_length' => strlen($rawBody),
                    'payload_keys' => array_keys($payload),
                    'user_agent' => $request->userAgent(),
                    'request_ip' => $request->ip(),
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Withdrawal Must Already Exist
        |--------------------------------------------------------------------------
        */

        $withdrawal = SellerWithdrawal::query()
            ->where(
                'paystack_transfer_reference',
                $reference
            )
            ->first();

        if (!$withdrawal) {
            Log::warning(
                'Rejected Paystack approval because withdrawal was not found.',
                [
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Match Amount
        |--------------------------------------------------------------------------
        */

        $expectedAmountInKobo = (int) round(
            (float) $withdrawal->amount * 100
        );

        if ($amountInKobo !== $expectedAmountInKobo) {
            Log::warning(
                'Rejected Paystack approval because amount did not match.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                    'expected' => $expectedAmountInKobo,
                    'received' => $amountInKobo,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Match Currency
        |--------------------------------------------------------------------------
        */

        $expectedCurrency = strtoupper(
            (string) ($withdrawal->currency ?: 'NGN')
        );

        if ($currency !== $expectedCurrency) {
            Log::warning(
                'Rejected Paystack approval because currency did not match.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                    'expected' => $expectedCurrency,
                    'received' => $currency,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Never Approve a Final Withdrawal Again
        |--------------------------------------------------------------------------
        */

        if ($withdrawal->isFinal()) {
            Log::warning(
                'Rejected Paystack approval for final withdrawal.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                    'status' => $withdrawal->status,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Seller KYC Must Be Approved
        |--------------------------------------------------------------------------
        */

        $kycApproved = SellerKycVerification::query()
            ->where(
                'seller_id',
                $withdrawal->seller_id
            )
            ->where(
                'status',
                SellerKycVerification::STATUS_APPROVED
            )
            ->where(
                'seller_withdrawal_account_id',
                $withdrawal->seller_withdrawal_account_id
            )
            ->where(
                'bank_name_match',
                true
            )
            ->exists();

        if (!$kycApproved) {
            Log::warning(
                'Rejected Paystack approval because KYC is not approved.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'seller_id' => $withdrawal->seller_id,
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Withdrawal Bank Must Still Be Valid
        |--------------------------------------------------------------------------
        */

        $account = SellerWithdrawalAccount::query()
            ->whereKey(
                $withdrawal->seller_withdrawal_account_id
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
                    'withdrawal_id' => $withdrawal->id,
                    'seller_id' => $withdrawal->seller_id,
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Stored Recipient Must Match Withdrawal Recipient
        |--------------------------------------------------------------------------
        */

        $withdrawalRecipient = (string) (
            $withdrawal->paystack_recipient_code ?? ''
        );

        $accountRecipient = (string) (
            $account->paystack_recipient_code ?? ''
        );

        if (
            $withdrawalRecipient === ''
            ||
            $accountRecipient === ''
            ||
            !hash_equals(
                $withdrawalRecipient,
                $accountRecipient
            )
        ) {
            Log::warning(
                'Rejected Paystack approval because recipient changed.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Match Paystack Recipient When It Returns an RCP Code
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                strtoupper($recipientFromPayload),
                'RCP_'
            )
            &&
            !hash_equals(
                $withdrawalRecipient,
                $recipientFromPayload
            )
        ) {
            Log::warning(
                'Rejected Paystack approval because request recipient did not match.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Wallet Reservation Must Still Exist
        |--------------------------------------------------------------------------
        */

        $wallet = SellerWallet::query()
            ->whereKey(
                $withdrawal->seller_wallet_id
            )
            ->where(
                'seller_id',
                $withdrawal->seller_id
            )
            ->first();

        $pendingWithdrawalBalance = $wallet
            ? (float) $wallet->pending_withdrawal_balance
            : 0.0;

        $withdrawalAmount = (float) $withdrawal->amount;

        if (
            !$wallet
            ||
            $pendingWithdrawalBalance + 0.001
                <
            $withdrawalAmount
        ) {
            Log::warning(
                'Rejected Paystack approval because wallet reservation is missing.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                    'required_amount' => $withdrawalAmount,
                    'pending_withdrawal_balance' =>
                        $pendingWithdrawalBalance,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Wallet Withdrawal Ledger Must Exist
        |--------------------------------------------------------------------------
        */

        $ledgerExists = SellerWalletTransaction::query()
            ->where(
                'seller_withdrawal_id',
                $withdrawal->id
            )
            ->where(
                'type',
                SellerWalletTransaction::TYPE_WITHDRAWAL_REQUEST
            )
            ->exists();

        if (!$ledgerExists) {
            Log::warning(
                'Rejected Paystack approval because withdrawal ledger is missing.',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $reference,
                ]
            );

            return response()->json([], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Approved
        |--------------------------------------------------------------------------
        |
        | Do not make external HTTP requests here. Paystack requires the
        | approval endpoint to respond quickly.
        |
        */

        Log::info(
            'Approved automatic Paystack seller withdrawal.',
            [
                'withdrawal_id' => $withdrawal->id,
                'seller_id' => $withdrawal->seller_id,
                'reference' => $reference,
                'amount' => $withdrawal->amount,
                'currency' => $withdrawal->currency,
            ]
        );

        return response()->json([], 200);
    }
}
