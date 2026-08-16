<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Generated Transaction Link Expiry
    |--------------------------------------------------------------------------
    */

    'link_expiry_days' =>

        (int) env(

            'SECURE_TRANSACTION_LINK_EXPIRY_DAYS',

            7

        ),


    /*
    |--------------------------------------------------------------------------
    | MidPoint Service Fee
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | Product = ₦100,000
    | Fee = 5%
    | MidPoint fee = ₦5,000
    |
    */

    'service_fee_percent' =>

        (float) env(

            'MIDPOINT_SERVICE_FEE_PERCENT',

            5

        ),


    /*
    |--------------------------------------------------------------------------
    | VAT On MidPoint Service Fee
    |--------------------------------------------------------------------------
    |
    | VAT is calculated on the MidPoint fee,
    | not directly on the product price.
    |
    */

    'fee_vat_percent' =>

        (float) env(

            'MIDPOINT_FEE_VAT_PERCENT',

            7.5

        ),


    /*
    |--------------------------------------------------------------------------
    | Buyer Inspection Period
    |--------------------------------------------------------------------------
    */

    'inspection_hours' =>

        (int) env(

            'MIDPOINT_INSPECTION_HOURS',

            8

        ),


    /*
    |--------------------------------------------------------------------------
    | Delivery Auto Complete Period
    |--------------------------------------------------------------------------
    |
    | After an order reaches the delivered state,
    | this controls the applicable automatic completion
    | protection timer where your transaction workflow
    | uses this configuration.
    |
    */

    'delivery_auto_complete_hours' =>

        (int) env(

            'MIDPOINT_DELIVERY_AUTO_COMPLETE_HOURS',

            72

        ),

    'stock_reservation_minutes' =>

        (int)
        env(

            'MIDPOINT_STOCK_RESERVATION_MINUTES',

            30

        ),

];