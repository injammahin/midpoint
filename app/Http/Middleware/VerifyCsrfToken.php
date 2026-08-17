<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [

        /*
        |--------------------------------------------------------------------------
        | Paystack Webhook
        |--------------------------------------------------------------------------
        */

        'webhooks/paystack',


        /*
        |--------------------------------------------------------------------------
        | Paystack Automatic Transfer Server Approval
        |--------------------------------------------------------------------------
        */

        'paystack/transfer-approval/*',

    ];
}