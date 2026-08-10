<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo Seller Payment
    |--------------------------------------------------------------------------
    |
    | Turn this OFF once a real payment gateway is integrated.
    |
    */

    'demo_payment_enabled' =>
        env(
            'SELLER_DEMO_PAYMENT',
            false
        ),

];