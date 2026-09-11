<?php

return [
    'logo_path' =>
        env(
            'MIDPOINT_APP_LOGO_PATH',
            null
        ),

    'kyc' => [
        'allow_verified_identity_reuse' =>
            filter_var(
                env(
                    'MIDPOINT_KYC_ALLOW_VERIFIED_IDENTITY_REUSE',
                    false
                ),
                FILTER_VALIDATE_BOOL
            ),

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