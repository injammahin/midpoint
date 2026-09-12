<?php

return [
    'logo_path' =>
        env(
            'MIDPOINT_APP_LOGO_PATH',
            null
        ),

    'kyc' => [
        /*
         * Cross-account identity reuse is intentionally disabled. Each seller
         * and active bank must receive its own signed Paystack result.
         */
        'allow_verified_identity_reuse' =>
            false,

        'fingerprint_key' =>
            env('MIDPOINT_KYC_FINGERPRINT_KEY')
            ?: env('APP_KEY'),

        'processing_timeout_minutes' =>
            max(
                15,
                (int) env(
                    'MIDPOINT_KYC_PROCESSING_TIMEOUT_MINUTES',
                    30
                )
            ),
    ],
];
