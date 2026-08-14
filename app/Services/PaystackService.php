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
                (string)
                config(
                    'services.paystack.base_url',
                    'https://api.paystack.co'
                ),
                '/'
            );

        $this->secretKey =
            (string)
            config(
                'services.paystack.secret_key'
            );

        if (!$this->secretKey) {
            throw new RuntimeException(
                'Paystack secret key is not configured.'
            );
        }
    }

    /**
     * Non-secret fingerprint useful for logs when diagnosing API-key changes.
     * This never exposes the full Paystack secret key.
     */
    public function secretKeyFingerprint(): string
    {
        return substr(hash('sha256', $this->secretKey), 0, 12);
    }


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

    public function verifyTransaction(
        string $reference
    ): array {
        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
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

    public function verifyTransfer(
        string $reference
    ): array {
        $response =
            Http::withToken(
                $this->secretKey
            )
                ->acceptJson()
                ->timeout(30)
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

    protected function extractData(
        Response $response,
        string $message
    ): array {
        $json =
            $response->json();

        if (
            !$response->successful()
            ||
            !is_array($json)
            ||
            !($json['status'] ?? false)
        ) {
            throw new RuntimeException(
                is_array($json)
                    ? (
                        $json['message']
                        ??
                        $message
                    )
                    : $message
            );
        }

        if (
            !isset($json['data'])
            ||
            !is_array($json['data'])
        ) {
            throw new RuntimeException(
                $message
            );
        }

        return $json['data'];
    }
}