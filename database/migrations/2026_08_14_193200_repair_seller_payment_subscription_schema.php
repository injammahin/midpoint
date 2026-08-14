<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | UP
    |--------------------------------------------------------------------------
    */

    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Seller Subscriptions
        |--------------------------------------------------------------------------
        |
        | Your original project created:
        |
        | package_price
        | starts_at
        |
        | But later code uses:
        |
        | price
        | started_at
        | seller_application_id
        |
        | This migration makes LIVE and LOCAL schemas compatible.
        |
        */

        if (
            !Schema::hasTable(
                'seller_subscriptions'
            )
        ) {

            Schema::create(
                'seller_subscriptions',
                function (
                    Blueprint $table
                ) {

                    $table->id();


                    $table
                        ->unsignedBigInteger(
                            'user_id'
                        );


                    $table
                        ->unsignedBigInteger(
                            'seller_package_id'
                        )
                        ->nullable();


                    $table
                        ->unsignedBigInteger(
                            'seller_application_id'
                        )
                        ->nullable();


                    $table
                        ->string(
                            'package_name',
                            150
                        )
                        ->nullable();


                    $table
                        ->decimal(
                            'package_price',
                            15,
                            2
                        )
                        ->default(0);


                    $table
                        ->decimal(
                            'price',
                            15,
                            2
                        )
                        ->default(0);


                    $table
                        ->string(
                            'billing_period',
                            30
                        )
                        ->default('month');


                    $table
                        ->unsignedInteger(
                            'product_limit'
                        )
                        ->default(1);


                    $table
                        ->string(
                            'status',
                            30
                        )
                        ->default('active');


                    $table
                        ->string(
                            'payment_reference',
                            150
                        )
                        ->nullable();


                    /*
                    |--------------------------------------------------------------------------
                    | Keep Both For Backward Compatibility
                    |--------------------------------------------------------------------------
                    */

                    $table
                        ->dateTime(
                            'starts_at'
                        )
                        ->nullable();


                    $table
                        ->dateTime(
                            'started_at'
                        )
                        ->nullable();


                    $table
                        ->dateTime(
                            'expires_at'
                        )
                        ->nullable();


                    $table
                        ->dateTime(
                            'cancelled_at'
                        )
                        ->nullable();


                    $table->timestamps();


                    $table->index(
                        'user_id'
                    );


                    $table->index(
                        'seller_application_id'
                    );


                    $table->index(
                        'status'
                    );


                    $table->index(
                        'expires_at'
                    );
                }
            );


        } else {


            /*
            |--------------------------------------------------------------------------
            | Check Existing Columns
            |--------------------------------------------------------------------------
            */

            $columns = [

                'seller_application_id' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'seller_application_id'
                    ),

                'package_name' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'package_name'
                    ),

                'package_price' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'package_price'
                    ),

                'price' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'price'
                    ),

                'billing_period' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'billing_period'
                    ),

                'product_limit' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'product_limit'
                    ),

                'status' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'status'
                    ),

                'payment_reference' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'payment_reference'
                    ),

                'starts_at' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'starts_at'
                    ),

                'started_at' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'started_at'
                    ),

                'expires_at' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'expires_at'
                    ),

                'cancelled_at' =>
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'cancelled_at'
                    ),

            ];


            Schema::table(
                'seller_subscriptions',
                function (
                    Blueprint $table
                ) use (
                    $columns
                ) {

                    if (
                        !$columns[
                            'seller_application_id'
                        ]
                    ) {

                        $table
                            ->unsignedBigInteger(
                                'seller_application_id'
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'package_name'
                        ]
                    ) {

                        $table
                            ->string(
                                'package_name',
                                150
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'package_price'
                        ]
                    ) {

                        $table
                            ->decimal(
                                'package_price',
                                15,
                                2
                            )
                            ->default(0);
                    }


                    if (
                        !$columns[
                            'price'
                        ]
                    ) {

                        $table
                            ->decimal(
                                'price',
                                15,
                                2
                            )
                            ->default(0);
                    }


                    if (
                        !$columns[
                            'billing_period'
                        ]
                    ) {

                        $table
                            ->string(
                                'billing_period',
                                30
                            )
                            ->default(
                                'month'
                            );
                    }


                    if (
                        !$columns[
                            'product_limit'
                        ]
                    ) {

                        $table
                            ->unsignedInteger(
                                'product_limit'
                            )
                            ->default(1);
                    }


                    if (
                        !$columns[
                            'status'
                        ]
                    ) {

                        $table
                            ->string(
                                'status',
                                30
                            )
                            ->default(
                                'active'
                            );
                    }


                    if (
                        !$columns[
                            'payment_reference'
                        ]
                    ) {

                        $table
                            ->string(
                                'payment_reference',
                                150
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'starts_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'starts_at'
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'started_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'started_at'
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'expires_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'expires_at'
                            )
                            ->nullable();
                    }


                    if (
                        !$columns[
                            'cancelled_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'cancelled_at'
                            )
                            ->nullable();
                    }

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Synchronize starts_at -> started_at
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'seller_subscriptions',
                'started_at'
            )
            &&
            Schema::hasColumn(
                'seller_subscriptions',
                'starts_at'
            )
        ) {

            DB::table(
                'seller_subscriptions'
            )

                ->whereNull(
                    'started_at'
                )

                ->whereNotNull(
                    'starts_at'
                )

                ->update([

                    'started_at' =>
                        DB::raw(
                            'starts_at'
                        ),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Synchronize started_at -> starts_at
            |--------------------------------------------------------------------------
            */

            DB::table(
                'seller_subscriptions'
            )

                ->whereNull(
                    'starts_at'
                )

                ->whereNotNull(
                    'started_at'
                )

                ->update([

                    'starts_at' =>
                        DB::raw(
                            'started_at'
                        ),

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Synchronize package_price -> price
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn(
                'seller_subscriptions',
                'price'
            )
            &&
            Schema::hasColumn(
                'seller_subscriptions',
                'package_price'
            )
        ) {

            DB::table(
                'seller_subscriptions'
            )

                ->where(
                    function (
                        $query
                    ) {

                        $query
                            ->whereNull(
                                'price'
                            )

                            ->orWhere(
                                'price',
                                0
                            );
                    }
                )

                ->whereNotNull(
                    'package_price'
                )

                ->update([

                    'price' =>
                        DB::raw(
                            'package_price'
                        ),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Synchronize price -> package_price
            |--------------------------------------------------------------------------
            */

            DB::table(
                'seller_subscriptions'
            )

                ->where(
                    function (
                        $query
                    ) {

                        $query
                            ->whereNull(
                                'package_price'
                            )

                            ->orWhere(
                                'package_price',
                                0
                            );
                    }
                )

                ->where(
                    'price',
                    '>',
                    0
                )

                ->update([

                    'package_price' =>
                        DB::raw(
                            'price'
                        ),

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Invoice Payments Compatibility
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable(
                'seller_invoice_payments'
            )
        ) {

            $paymentColumns = [

                'authorization_url' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'authorization_url'
                    ),

                'amount_subunit' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'amount_subunit'
                    ),

                'paystack_transaction_id' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'paystack_transaction_id'
                    ),

                'initialized_at' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'initialized_at'
                    ),

                'verified_at' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'verified_at'
                    ),

                'paid_at' =>
                    Schema::hasColumn(
                        'seller_invoice_payments',
                        'paid_at'
                    ),

            ];


            Schema::table(
                'seller_invoice_payments',
                function (
                    Blueprint $table
                ) use (
                    $paymentColumns
                ) {

                    if (
                        !$paymentColumns[
                            'authorization_url'
                        ]
                    ) {

                        $table
                            ->text(
                                'authorization_url'
                            )
                            ->nullable();
                    }


                    if (
                        !$paymentColumns[
                            'amount_subunit'
                        ]
                    ) {

                        $table
                            ->unsignedBigInteger(
                                'amount_subunit'
                            )
                            ->default(0);
                    }


                    if (
                        !$paymentColumns[
                            'paystack_transaction_id'
                        ]
                    ) {

                        $table
                            ->unsignedBigInteger(
                                'paystack_transaction_id'
                            )
                            ->nullable();
                    }


                    if (
                        !$paymentColumns[
                            'initialized_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'initialized_at'
                            )
                            ->nullable();
                    }


                    if (
                        !$paymentColumns[
                            'verified_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'verified_at'
                            )
                            ->nullable();
                    }


                    if (
                        !$paymentColumns[
                            'paid_at'
                        ]
                    ) {

                        $table
                            ->dateTime(
                                'paid_at'
                            )
                            ->nullable();
                    }

                }
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DOWN
    |--------------------------------------------------------------------------
    */

    public function down()
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Empty
        |--------------------------------------------------------------------------
        |
        | This is a production compatibility migration.
        |
        | We do not want rollback to delete historical payment/subscription data.
        |
        */
    }
};