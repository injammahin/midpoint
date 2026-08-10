<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Seller Packages
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_packages',
            function (Blueprint $table) {

                $table->id();

                $table->string('name');

                $table
                    ->string('slug')
                    ->unique();

                $table
                    ->decimal(
                        'price',
                        12,
                        2
                    )
                    ->default(0);

                $table
                    ->string(
                        'billing_period',
                        30
                    )
                    ->default('month');

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                |
                | This determines how many products a seller may add.
                |
                */

                $table
                    ->unsignedInteger(
                        'product_limit'
                    )
                    ->default(1);

                $table
                    ->string('description')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Features
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | [
                |   "Verified badge & Featured listing",
                |   "Buyer reviews",
                |   "Faster support"
                | ]
                |
                */

                $table
                    ->json('features')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Card Styling
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'theme',
                        30
                    )
                    ->default('green');

                $table
                    ->boolean(
                        'is_popular'
                    )
                    ->default(false);

                $table
                    ->boolean(
                        'is_active'
                    )
                    ->default(true);

                $table
                    ->unsignedInteger(
                        'sort_order'
                    )
                    ->default(0);

                $table->timestamps();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Subscriptions
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_subscriptions',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->foreignId(
                        'seller_package_id'
                    )
                    ->nullable()
                    ->constrained(
                        'seller_packages'
                    )
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Package Snapshot
                |--------------------------------------------------------------------------
                |
                | These are copied when the user buys.
                |
                | If admin later changes Starter from 5 products to 10 products,
                | an old paid subscription can remain at 5 until renewal.
                |
                */

                $table
                    ->string(
                        'package_name'
                    );

                $table
                    ->decimal(
                        'package_price',
                        12,
                        2
                    );

                $table
                    ->string(
                        'billing_period',
                        30
                    );

                $table
                    ->unsignedInteger(
                        'product_limit'
                    );

                /*
                |--------------------------------------------------------------------------
                | Subscription Status
                |--------------------------------------------------------------------------
                |
                | pending
                | active
                | expired
                | cancelled
                |
                */

                $table
                    ->string(
                        'status',
                        30
                    )
                    ->default('pending');

                $table
                    ->string(
                        'payment_reference'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'starts_at'
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

                $table->index([
                    'user_id',
                    'status',
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Products
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_products',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId(
                        'user_id'
                    )
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('name');

                $table->string('slug');

                $table
                    ->decimal(
                        'price',
                        12,
                        2
                    );

                $table
                    ->text('description')
                    ->nullable();

                $table
                    ->string('image')
                    ->nullable();

                $table
                    ->boolean(
                        'is_active'
                    )
                    ->default(true);

                $table->timestamps();

                $table->index([
                    'user_id',
                    'is_active',
                ]);
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'seller_products'
        );

        Schema::dropIfExists(
            'seller_subscriptions'
        );

        Schema::dropIfExists(
            'seller_packages'
        );
    }
};