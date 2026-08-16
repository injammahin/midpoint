<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'marketplace_checkout_intents',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Temporary Checkout Token
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'token',
                        64
                    )
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | Parties / Product
                |--------------------------------------------------------------------------
                |
                | These are intentionally indexed IDs.
                |
                | This table is a temporary payment/audit record, not a real
                | SecureTransaction.
                |
                */

                $table
                    ->unsignedBigInteger(
                        'seller_product_id'
                    )
                    ->index();


                $table
                    ->unsignedBigInteger(
                        'seller_id'
                    )
                    ->index();


                $table
                    ->unsignedBigInteger(
                        'buyer_id'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Buyer Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'buyer_email'
                    );


                $table
                    ->string(
                        'buyer_phone',
                        40
                    );


                $table
                    ->text(
                        'delivery_address'
                    );


                /*
                |--------------------------------------------------------------------------
                | Product Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'product_name'
                    );


                $table
                    ->longText(
                        'product_description'
                    )
                    ->nullable();


                $table
                    ->json(
                        'product_images'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Quantity / Price
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedInteger(
                        'quantity'
                    );


                $table
                    ->decimal(
                        'unit_price',
                        15,
                        2
                    );


                $table
                    ->decimal(
                        'subtotal',
                        15,
                        2
                    );


                $table
                    ->decimal(
                        'delivery_fee',
                        15,
                        2
                    )
                    ->default(
                        0
                    );


                $table
                    ->decimal(
                        'total_amount',
                        15,
                        2
                    );


                $table
                    ->string(
                        'currency',
                        3
                    )
                    ->default(
                        'NGN'
                    );


                /*
                |--------------------------------------------------------------------------
                | Paystack
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'paystack_reference',
                        120
                    )
                    ->unique();


                $table
                    ->string(
                        'access_code'
                    )
                    ->nullable();


                $table
                    ->text(
                        'authorization_url'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Payment Status
                |--------------------------------------------------------------------------
                |
                | created
                | initialized
                | pending
                | success
                | failed
                | abandoned
                | expired
                |
                */

                $table
                    ->string(
                        'payment_status',
                        30
                    )
                    ->default(
                        'created'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Paystack Result
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'paystack_transaction_id',
                        100
                    )
                    ->nullable();


                $table
                    ->string(
                        'channel',
                        50
                    )
                    ->nullable();


                $table
                    ->string(
                        'gateway_response',
                        255
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Reservation
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime(
                        'reserved_until'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Dates
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime(
                        'initialized_at'
                    )
                    ->nullable();


                $table
                    ->dateTime(
                        'verified_at'
                    )
                    ->nullable();


                $table
                    ->dateTime(
                        'paid_at'
                    )
                    ->nullable();


                $table
                    ->dateTime(
                        'finalized_at'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Real Transaction
                |--------------------------------------------------------------------------
                |
                | NULL until Paystack payment succeeds.
                |
                */

                $table
                    ->unsignedBigInteger(
                        'secure_transaction_id'
                    )
                    ->nullable()
                    ->unique();


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Reservation Lookup
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [

                        'seller_product_id',

                        'payment_status',

                        'reserved_until',

                    ],
                    'marketplace_intent_stock_lookup'
                );
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'marketplace_checkout_intents'
        );
    }
};