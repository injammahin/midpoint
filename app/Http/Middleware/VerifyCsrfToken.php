<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;


class VerifyCsrfToken extends Middleware
{
    /*
    |--------------------------------------------------------------------------
    | CSRF Exceptions
    |--------------------------------------------------------------------------
    */

    protected $except = [

        /*
        |--------------------------------------------------------------------------
        | Paystack Server-To-Server Webhook
        |--------------------------------------------------------------------------
        |
        | Security is handled using x-paystack-signature instead.
        |
        */

        'webhooks/paystack',

    ];
}