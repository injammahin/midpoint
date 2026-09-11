<?php

namespace App\Console\Commands;

use App\Services\PaystackSellerKycService;
use Illuminate\Console\Command;

class ReleaseStaleSellerKyc extends Command
{
    protected $signature =
        'seller-kyc:release-stale {--limit=100}';

    protected $description =
        'Release stale Paystack identity checks for retry.';

    public function handle(
        PaystackSellerKycService $kycService
    ): int {
        $released =
            $kycService->releaseStaleProcessing(
                max(
                    1,
                    (int) $this->option('limit')
                )
            );

        $this->info(
            'Released '
            . $released
            . ' stale KYC verification(s).'
        );

        return self::SUCCESS;
    }
}