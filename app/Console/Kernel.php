<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /*
    |--------------------------------------------------------------------------
    | Scheduler
    |--------------------------------------------------------------------------
    */

    protected function schedule(
        Schedule $schedule
    ) {
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


    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    */

    protected function commands()
    {
        $this->load(
            __DIR__
            .
            '/Commands'
        );


        require base_path(
            'routes/console.php'
        );
    }
}