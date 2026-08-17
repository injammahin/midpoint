<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(
        Schedule $schedule
    ) {
        $schedule
            ->command(
                'seller-withdrawals:process'
            )
            ->everyFiveMinutes()
            ->withoutOverlapping();

        /*
        |--------------------------------------------------------------------------
        | Secure Transaction Processor
        |--------------------------------------------------------------------------
        */

        $schedule
            ->command(
                'transactions:process'
            )
            ->everyMinute()
            ->withoutOverlapping();


        /*
        |--------------------------------------------------------------------------
        | Seller Subscription Expiration
        |--------------------------------------------------------------------------
        */

        $schedule
            ->command(
                'seller-subscriptions:expire'
            )
            ->everyMinute()
            ->withoutOverlapping();
    }


    protected function commands()
    {
        $this->load(
            __DIR__ . '/Commands'
        );


        require base_path(
            'routes/console.php'
        );
    }
}