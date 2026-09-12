<?php

return [
    'logo_path' => env('MIDPOINT_APP_LOGO_PATH', null),
    'kyc' => [
        // Only the new full-BVN/exact-bank registry may reuse evidence.
        'allow_verified_identity_reuse' => env('MIDPOINT_ALLOW_VERIFIED_IDENTITY_REUSE', true),
        'fingerprint_key' => env('MIDPOINT_KYC_FINGERPRINT_KEY') ?: env('APP_KEY'),
        'processing_timeout_minutes' => max(15, (int) env('MIDPOINT_KYC_PROCESSING_TIMEOUT_MINUTES', 30)),
        'attempts_per_seller_hour' => 10,
        'attempts_per_ip_hour' => 50,
    ],
];
