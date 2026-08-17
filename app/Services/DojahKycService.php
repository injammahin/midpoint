<?php

namespace App\Services;

use App\Exceptions\DojahKycException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class DojahKycService
{
    protected string $baseUrl;

    protected ?string $appId;

    protected ?string $secretKey;


    public function __construct()
    {
        $this->baseUrl =
            rtrim(
                (string)
                config(
                    'services.dojah.base_url',
                    'https://sandbox.dojah.io'
                ),
                '/'
            );


        $this->appId =
            config(
                'services.dojah.app_id'
            );


        $this->secretKey =
            config(
                'services.dojah.secret_key'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Liveness Check
    |--------------------------------------------------------------------------
    */

    public function checkLiveness(
        string $selfieBase64
    ): array {

        return $this->post(
            '/api/v1/ml/liveness',
            [
                'image' =>
                    $selfieBase64,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NIN/BVN + Selfie Verification
    |--------------------------------------------------------------------------
    */

    public function verifyIdentityWithSelfie(
        string $type,
        string $identifier,
        string $selfieBase64
    ): array {

        $type =
            strtolower(
                trim(
                    $type
                )
            );


        if (
            !in_array(
                $type,
                [
                    'nin',
                    'bvn',
                ],
                true
            )
        ) {

            throw new DojahKycException(
                'Unsupported identity type.'
            );
        }


        $endpoint =
            $type
            ===
            'nin'

                ? '/api/v1/kyc/nin/verify'

                : '/api/v1/kyc/bvn/verify';


        return $this->post(
            $endpoint,
            [
                $type =>
                    $identifier,

                'selfie_image' =>
                    $selfieBase64,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | POST Request
    |--------------------------------------------------------------------------
    */

    protected function post(
        string $endpoint,
        array $payload
    ): array {

        $this->ensureConfigured();


        try {

            /*
             * Do not use withToken().
             *
             * Dojah expects:
             *
             * Authorization: SECRET
             *
             * not:
             *
             * Authorization: Bearer SECRET
             */

            $response =
                Http::withHeaders([
                    'Authorization' =>
                        $this->secretKey,

                    'AppId' =>
                        $this->appId,

                    'Content-Type' =>
                        'application/json',

                    'Accept' =>
                        'application/json',
                ])

                    ->timeout(
                        60
                    )

                    ->post(
                        $this->baseUrl
                        .
                        $endpoint,
                        $payload
                    );


        } catch (
            Throwable $exception
        ) {

            throw new DojahKycException(
                'The identity verification provider could not be reached.',
                null,
                true,
                $exception
            );
        }


        return $this->handleResponse(
            $response
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Response
    |--------------------------------------------------------------------------
    */

    protected function handleResponse(
        Response $response
    ): array {

        $json =
            $response->json();


        if (
            $response->successful()
        ) {

            if (
                !is_array(
                    $json
                )
            ) {

                throw new DojahKycException(
                    'The identity provider returned an invalid response.',
                    $response->status(),
                    true
                );
            }


            return $json;
        }


        $status =
            $response->status();


        $message =
            data_get(
                $json,
                'message'
            );


        if (
            !$message
        ) {

            $message =
                data_get(
                    $json,
                    'error'
                );
        }


        if (
            is_array(
                $message
            )
        ) {

            $message =
                json_encode(
                    $message
                );
        }


        $message =
            trim(
                (string)
                $message
            );


        if (
            $message === ''
        ) {

            $message =
                'Identity verification failed.';
        }


        /*
        |--------------------------------------------------------------------------
        | Retryable Errors
        |--------------------------------------------------------------------------
        */

        $retryable =
            in_array(
                $status,
                [
                    408,
                    424,
                    429,
                    500,
                    502,
                    503,
                    504,
                ],
                true
            );


        throw new DojahKycException(
            $message,
            $status,
            $retryable
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Config Check
    |--------------------------------------------------------------------------
    */

    protected function ensureConfigured(): void
    {
        if (
            empty(
                $this->appId
            )
            ||
            empty(
                $this->secretKey
            )
        ) {

            throw new DojahKycException(
                'Dojah KYC credentials have not been configured.',
                null,
                false
            );
        }
    }
}