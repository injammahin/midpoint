<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    protected string $baseUrl;

    protected string $secretKey;


    public function __construct()
    {
        $this->baseUrl =
            rtrim(
                (string) config(
                    'services.paystack.base_url',
                    'https://api.paystack.co'
                ),
                '/'
            );


        $this->secretKey =
            trim(
                (string) config(
                    'services.paystack.secret_key'
                )
            );


        if ($this->secretKey === '') {
            throw new RuntimeException(
                'Paystack secret key is not configured.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Initialize Transaction
    |--------------------------------------------------------------------------
    */

    public function initializeTransaction(
        array $payload
    ): array {

        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->post(
                    $this->baseUrl
                    .
                    '/transaction/initialize',
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to initialize payment.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Transaction
    |--------------------------------------------------------------------------
    */

    public function verifyTransaction(
        string $reference
    ): array {

        $reference =
            trim(
                $reference
            );


        if ($reference === '') {
            throw new RuntimeException(
                'Paystack transaction reference is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->get(
                    $this->baseUrl
                    .
                    '/transaction/verify/'
                    .
                    rawurlencode(
                        $reference
                    )
                );


        return $this->extractData(
            $response,
            'Unable to verify payment.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | List Banks
    |--------------------------------------------------------------------------
    */

    public function listBanks(
        string $country = 'nigeria'
    ): array {

        $banks = [];

        $next = null;


        for (
            $page = 0;
            $page < 5;
            $page++
        ) {

            $query = [

                'country' =>
                    $country,

                'perPage' =>
                    100,

                'use_cursor' =>
                    'true',

            ];


            if ($next) {
                $query['next'] =
                    $next;
            }


            $response =
                Http::withToken(
                    $this->secretKey
                )
                    ->acceptJson()
                    ->timeout(30)
                    ->retry(
                        2,
                        300
                    )
                    ->get(
                        $this->baseUrl
                        .
                        '/bank',
                        $query
                    );


            $data =
                $this->extractData(
                    $response,
                    'Unable to load supported banks.'
                );


            $banks =
                array_merge(
                    $banks,
                    $data
                );


            $json =
                $response->json();


            $next =
                is_array(
                    $json
                )
                    ? data_get(
                        $json,
                        'meta.next'
                    )
                    : null;


            if (!$next) {
                break;
            }
        }


        return collect(
            $banks
        )
            ->filter(
                fn ($bank) =>
                    is_array(
                        $bank
                    )
                    &&
                    !empty(
                        $bank['code']
                    )
            )
            ->unique(
                fn ($bank) =>
                    (string)
                    $bank['code']
            )
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Bank Account
    |--------------------------------------------------------------------------
    */

    public function resolveBankAccount(
        string $accountNumber,
        string $bankCode
    ): array {

        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->get(
                    $this->baseUrl
                    .
                    '/bank/resolve',
                    [

                        'account_number' =>
                            $accountNumber,

                        'bank_code' =>
                            $bankCode,

                    ]
                );


        return $this->extractData(
            $response,
            'Unable to verify this bank account.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Customer
    |--------------------------------------------------------------------------
    */

    public function fetchCustomer(
        string $emailOrCode
    ): ?array {

        $emailOrCode =
            trim(
                $emailOrCode
            );


        if ($emailOrCode === '') {
            throw new RuntimeException(
                'Paystack customer email or code is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->get(
                    $this->baseUrl
                    .
                    '/customer/'
                    .
                    rawurlencode(
                        $emailOrCode
                    )
                );


        /*
        |--------------------------------------------------------------------------
        | Customer Does Not Exist
        |--------------------------------------------------------------------------
        */

        if (
            $response->status()
            ===
            404
        ) {
            return null;
        }


        return $this->extractData(
            $response,
            'Unable to fetch Paystack customer.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Customer
    |--------------------------------------------------------------------------
    */

    public function createCustomer(
        array $payload
    ): array {

        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post(
                    $this->baseUrl
                    .
                    '/customer',
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to create Paystack customer.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Customer
    |--------------------------------------------------------------------------
    */

    public function updateCustomer(
        string $customerCode,
        array $payload
    ): array {

        $customerCode =
            trim(
                $customerCode
            );


        if ($customerCode === '') {
            throw new RuntimeException(
                'Paystack customer code is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->put(
                    $this->baseUrl
                    .
                    '/customer/'
                    .
                    rawurlencode(
                        $customerCode
                    ),
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to update Paystack customer.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Customer Identity
    |--------------------------------------------------------------------------
    |
    | Paystack performs this asynchronously.
    |
    | The final status comes through:
    |
    | customeridentification.success
    | customeridentification.failed
    |
    */

    public function validateCustomerIdentity(
        string $customerCode,
        array $payload
    ): array {

        $customerCode =
            trim(
                $customerCode
            );


        if ($customerCode === '') {
            throw new RuntimeException(
                'Paystack customer code is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post(
                    $this->baseUrl
                    .
                    '/customer/'
                    .
                    rawurlencode(
                        $customerCode
                    )
                    .
                    '/identification',
                    $payload
                );


        /*
         * This endpoint normally returns:
         *
         * {
         *     "status": true,
         *     "message": "Customer Identification in progress"
         * }
         *
         * There is no data object.
         */

        return $this->extractEnvelope(
            $response,
            'Unable to start Paystack identity verification.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Transfer Recipient
    |--------------------------------------------------------------------------
    */

    public function createTransferRecipient(
        array $payload
    ): array {

        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->post(
                    $this->baseUrl
                    .
                    '/transferrecipient',
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to create Paystack transfer recipient.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Initiate Transfer
    |--------------------------------------------------------------------------
    */

    public function initiateTransfer(
        array $payload
    ): array {

        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->post(
                    $this->baseUrl
                    .
                    '/transfer',
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to initiate seller payout.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Transfer
    |--------------------------------------------------------------------------
    */

    public function verifyTransfer(
        string $reference
    ): array {

        $reference =
            trim(
                $reference
            );


        if ($reference === '') {
            throw new RuntimeException(
                'Paystack transfer reference is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->retry(
                    2,
                    300
                )
                ->get(
                    $this->baseUrl
                    .
                    '/transfer/verify/'
                    .
                    rawurlencode(
                        $reference
                    )
                );


        return $this->extractData(
            $response,
            'Unable to verify seller payout.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Refund
    |--------------------------------------------------------------------------
    |
    | Paystack expects the refund amount in subunits.
    |
    | We always pass an explicit amount, even for a full refund. This makes the
    | Midpoint financial decision auditable and prevents accidental over-refund.
    |
    */

    public function createRefund(
        string $transactionReference,
        int $amountSubunit,
        string $currency = 'NGN',
        ?string $customerNote = null,
        ?string $merchantNote = null
    ): array {

        $transactionReference =
            trim(
                $transactionReference
            );


        if (
            $transactionReference
            ===
            ''
        ) {

            throw new RuntimeException(
                'Paystack transaction reference is missing for the refund.'
            );
        }


        if (
            $amountSubunit
            <=
            0
        ) {

            throw new RuntimeException(
                'Paystack refund amount must be greater than zero.'
            );
        }


        $payload = [

            'transaction' =>
                $transactionReference,

            'amount' =>
                $amountSubunit,

            'currency' =>
                strtoupper(
                    trim(
                        $currency
                    )
                ),

        ];


        if (
            $customerNote
        ) {

            $payload['customer_note'] =
                $customerNote;
        }


        if (
            $merchantNote
        ) {

            $payload['merchant_note'] =
                $merchantNote;
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post(
                    $this->baseUrl
                    .
                    '/refund',
                    $payload
                );


        return $this->extractData(
            $response,
            'Unable to initiate Paystack refund.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | List Refunds
    |--------------------------------------------------------------------------
    |
    | Used to safely reconcile a refund if the create-refund HTTP request
    | timed out and Midpoint cannot know whether Paystack accepted it.
    |
    */

    public function listRefunds(
        ?string $paystackTransactionId = null
    ): array {

        $query = [

            'perPage' =>
                50,

            'page' =>
                1,

        ];


        if (
            $paystackTransactionId
            !==
            null
            &&
            trim(
                $paystackTransactionId
            )
            !==
            ''
        ) {

            $query['transaction'] =
                trim(
                    $paystackTransactionId
                );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->get(
                    $this->baseUrl
                    .
                    '/refund',
                    $query
                );


        return $this->extractData(
            $response,
            'Unable to load Paystack refunds.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Refund
    |--------------------------------------------------------------------------
    */

    public function fetchRefund(
        string $refundId
    ): array {

        $refundId =
            trim(
                $refundId
            );


        if (
            $refundId
            ===
            ''
        ) {

            throw new RuntimeException(
                'Paystack refund ID is missing.'
            );
        }


        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
                ->get(
                    $this->baseUrl
                    .
                    '/refund/'
                    .
                    rawurlencode(
                        $refundId
                    )
                );


        return $this->extractData(
            $response,
            'Unable to fetch Paystack refund.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Webhook Signature
    |--------------------------------------------------------------------------
    */

    public function verifyWebhookSignature(
        string $rawPayload,
        ?string $signature
    ): bool {

        if (!$signature) {
            return false;
        }


        $expected =
            hash_hmac(
                'sha512',
                $rawPayload,
                $this->secretKey
            );


        return hash_equals(
            $expected,
            $signature
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Secret Key Fingerprint
    |--------------------------------------------------------------------------
    */

    public function secretKeyFingerprint(): string
    {
        return substr(
            hash(
                'sha256',
                $this->secretKey
            ),
            0,
            12
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Data
    |--------------------------------------------------------------------------
    */

    protected function extractData(
        Response $response,
        string $fallbackMessage
    ): array {

        $json =
            $this->validatedJson(
                $response,
                $fallbackMessage
            );


        if (
            !isset(
                $json['data']
            )
            ||
            !is_array(
                $json['data']
            )
        ) {

            throw new RuntimeException(
                $fallbackMessage
            );
        }


        return $json['data'];
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Envelope
    |--------------------------------------------------------------------------
    */

    protected function extractEnvelope(
        Response $response,
        string $fallbackMessage
    ): array {

        return $this->validatedJson(
            $response,
            $fallbackMessage
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Paystack Response
    |--------------------------------------------------------------------------
    */

    protected function validatedJson(
        Response $response,
        string $fallbackMessage
    ): array {

        $json =
            $response->json();


        if (
            !$response->successful()
            ||
            !is_array(
                $json
            )
            ||
            !(
                $json['status']
                ??
                false
            )
        ) {

            $message =
                is_array(
                    $json
                )
                    ? (string) (
                        $json['message']
                        ??
                        $fallbackMessage
                    )
                    : $fallbackMessage;


            throw new RuntimeException(
                $message
                .
                ' [HTTP '
                .
                $response->status()
                .
                ']'
            );
        }


        return $json;
    }
}