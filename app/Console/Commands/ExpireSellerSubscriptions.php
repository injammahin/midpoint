<?php

namespace App\Console\Commands;

use App\Services\SellerSubscriptionService;

use Illuminate\Console\Command;

class ExpireSellerSubscriptions extends Command
{
    protected $signature =
        'seller-subscriptions:expire';


    protected $description =
        'Expire seller subscriptions that reached their expiration time.';


    public function handle(
        SellerSubscriptionService $subscriptions
    ): int {

        $count =
            $subscriptions
                ->expireDueSubscriptions();


        $this->info(
            $count
            .
            ' seller subscription(s) expired.'
        );


        return self::SUCCESS;
    }
}