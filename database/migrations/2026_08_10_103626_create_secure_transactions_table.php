<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'secure_transactions',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Public References
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('reference', 50)
                    ->unique();


                $table
                    ->string('public_token', 100)
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | Participants
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('seller_id')
                    ->constrained('users')
                    ->cascadeOnDelete();


                $table
                    ->foreignId('buyer_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Optional Listed Product
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('seller_product_id')
                    ->nullable()
                    ->constrained('seller_products')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Transaction Type
                |--------------------------------------------------------------------------
                |
                | listed
                | custom
                |
                */

                $table
                    ->string(
                        'transaction_type',
                        20
                    );


                /*
                |--------------------------------------------------------------------------
                | Item Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('title');


                $table
                    ->text('description')
                    ->nullable();


                $table
                    ->json('images')
                    ->nullable();


                $table
                    ->unsignedInteger('quantity')
                    ->default(1);


                /*
                |--------------------------------------------------------------------------
                | Money
                |--------------------------------------------------------------------------
                */

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
                    ->default(0);


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
                    ->default('NGN');


                /*
                |--------------------------------------------------------------------------
                | Buyer Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('buyer_email');


                $table
                    ->string('buyer_phone', 40)
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Delivery
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('delivery_note')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Inspection
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedSmallInteger(
                        'inspection_hours'
                    )
                    ->default(8);


                /*
                |--------------------------------------------------------------------------
                | Main Transaction Status
                |--------------------------------------------------------------------------
                |
                | awaiting_payment
                | payment_secured
                | dispatched
                | inspection
                | disputed
                | release_approved
                | completed
                | cancelled
                | expired
                |
                */

                $table
                    ->string(
                        'status',
                        40
                    )
                    ->default(
                        'awaiting_payment'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Payment Status
                |--------------------------------------------------------------------------
                |
                | Paystack will use these later.
                |
                */

                $table
                    ->string(
                        'payment_status',
                        30
                    )
                    ->default('unpaid')
                    ->index();


                $table
                    ->string(
                        'paystack_reference'
                    )
                    ->nullable()
                    ->unique();


                $table
                    ->string(
                        'paystack_transaction_id'
                    )
                    ->nullable()
                    ->unique();


                $table
                    ->decimal(
                        'paid_amount',
                        15,
                        2
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Important Times
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime('link_expires_at')
                    ->nullable()
                    ->index();


                $table
                    ->dateTime('claimed_at')
                    ->nullable();


                $table
                    ->dateTime('paid_at')
                    ->nullable();


                $table
                    ->dateTime('dispatched_at')
                    ->nullable();


                $table
                    ->dateTime('received_at')
                    ->nullable();


                $table
                    ->dateTime('inspection_ends_at')
                    ->nullable();


                $table
                    ->dateTime('completed_at')
                    ->nullable();


                $table->timestamps();
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'secure_transactions'
        );
    }
};