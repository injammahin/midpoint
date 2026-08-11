<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'secure_transaction_payments',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Secure Transaction
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'secure_transaction_id'
                    )
                    ->constrained(
                        'secure_transactions'
                    )
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Buyer
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'buyer_id'
                    )
                    ->nullable()
                    ->constrained(
                        'users'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Payment Provider
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'provider',
                        30
                    )
                    ->default(
                        'paystack'
                    );


                /*
                |--------------------------------------------------------------------------
                | Unique Paystack Reference
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'reference',
                        120
                    )
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | Paystack Checkout
                |--------------------------------------------------------------------------
                */

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
                | Amount
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'amount',
                        15,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Amount In Kobo
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedBigInteger(
                        'amount_subunit'
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
                | Status
                |--------------------------------------------------------------------------
                |
                | created
                | initialized
                | pending
                | success
                | failed
                |
                */

                $table
                    ->string(
                        'status',
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
                    ->unsignedBigInteger(
                        'paystack_transaction_id'
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
                | Important Dates
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


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Useful Index
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'secure_transaction_id',
                    'status',
                ]);
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'secure_transaction_payments'
        );
    }
};