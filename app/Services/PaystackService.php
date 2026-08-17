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
        /*
        |--------------------------------------------------------------------------
        | API URL
        |--------------------------------------------------------------------------
        */

        $this->baseUrl =
            rtrim(
                (string) config(
                    'services.paystack.base_url',
                    'https://api.paystack.co'
                ),
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | Secret Key
        |--------------------------------------------------------------------------
        */

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

                ->timeout(
                    30
                )

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
                (string) $bank['code']
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

            ->timeout(
                30
            )

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
    | Initiate Seller Transfer
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
    | Verify Seller Transfer
    |--------------------------------------------------------------------------
    */

    public function verifyTransfer(
        string $reference
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
    |
    | Safe for logs.
    |
    | This DOES NOT reveal the actual Paystack secret key.
    |
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
    | Extract Paystack Response Data
    |--------------------------------------------------------------------------
    */

    protected function extractData(
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
}