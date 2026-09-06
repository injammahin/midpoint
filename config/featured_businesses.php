<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recommended Exposure Mix
    |--------------------------------------------------------------------------
    |
    | For 12 sellers this produces:
    |
    | Premium  = 9
    | Standard = 2
    | Basic    = 1
    |
    | "Starter" is treated as Basic for backward compatibility.
    |
    */

    'exposure' => [

        'premium' =>
            0.75,

        'standard' =>
            0.17,

        'basic' =>
            0.08,

    ],


    /*
    |--------------------------------------------------------------------------
    | Quality Ranking
    |--------------------------------------------------------------------------
    */

    'ranking' => [

        /* Bayesian rating prior */
        'rating_prior' =>
            3.8,

        'rating_prior_weight' =>
            5,


        /* Diminishing-return reference points */
        'completed_order_reference' =>
            100,

        'review_reference' =>
            50,

        'recent_order_reference' =>
            10,


        /* New seller discovery support */
        'new_seller_days' =>
            30,

        'new_seller_boost' =>
            3.0,


        /* Small stable per-day rotation */
        'rotation_bonus' =>
            1.25,

    ],

];
