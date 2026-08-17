<?php

return [



        'dojah' => [

            'app_id' =>
                env(
                    'DOJAH_APP_ID'
                ),

            'secret_key' =>
                env(
                    'DOJAH_SECRET_KEY'
                ),

            'base_url' =>
                env(
                    'DOJAH_BASE_URL',
                    'https://sandbox.dojah.io'
                ),

            /*
            |--------------------------------------------------------------------------
            | LOCAL TEST MODE
            |--------------------------------------------------------------------------
            */

            'fake_kyc' =>
                env(
                    'DOJAH_FAKE_KYC',
                    false
                ),

            /*
            |--------------------------------------------------------------------------
            | Automated Approval Thresholds
            |--------------------------------------------------------------------------
            */

            'face_confidence_min' =>
                (float)
                env(
                    'DOJAH_FACE_CONFIDENCE_MIN',
                    90
                ),

            'liveness_probability_min' =>
                (float)
                env(
                    'DOJAH_LIVENESS_PROBABILITY_MIN',
                    70
                ),

            'name_match_threshold' =>
                (float)
                env(
                    'DOJAH_NAME_MATCH_THRESHOLD',
                    0.80
                ),
        ],
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'paystack' => [

        'mode' =>
            env(
                'PAYSTACK_MODE',
                'test'
            ),

        'public_key' =>
            env(
                'PAYSTACK_PUBLIC_KEY'
            ),

        'secret_key' =>
            env(
                'PAYSTACK_SECRET_KEY'
            ),

        'base_url' =>
            env(
                'PAYSTACK_BASE_URL',
                'https://api.paystack.co'
            ),

        /*
        |--------------------------------------------------------------------------
        | Seller Wallet
        |--------------------------------------------------------------------------
        */

        'minimum_withdrawal' =>
            env(
                'PAYSTACK_MINIMUM_WITHDRAWAL',
                1000
            ),

        /*
        |--------------------------------------------------------------------------
        | Automatic Paystack Server Approval
        |--------------------------------------------------------------------------
        */

        'transfer_approval_token' =>
            env(
                'PAYSTACK_TRANSFER_APPROVAL_TOKEN'
            ),

        /*
        |--------------------------------------------------------------------------
        | Local Testing
        |--------------------------------------------------------------------------
        */

        'fake_bank_verification' =>
            env(
                'PAYSTACK_FAKE_BANK_VERIFICATION',
                false
            ),

        'fake_withdrawals' =>
            env(
                'PAYSTACK_FAKE_WITHDRAWALS',
                false
            ),
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
