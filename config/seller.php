<?php

return [

    'service_fee_percent' =>
        (float) env(
            'MIDPOINT_SERVICE_FEE_PERCENT',
            5
        ),

    'fee_vat_percent' =>
        (float) env(
            'MIDPOINT_FEE_VAT_PERCENT',
            7.5
        ),

    'inspection_hours' =>
        (int) env(
            'MIDPOINT_INSPECTION_HOURS',
            8
        ),

    'delivery_auto_complete_hours' =>
        (int) env(
            'MIDPOINT_DELIVERY_AUTO_COMPLETE_HOURS',
            72
        ),

    'link_expiry_days' =>
        (int) env(
            'MIDPOINT_TRANSACTION_LINK_EXPIRY_DAYS',
            7
        ),

];