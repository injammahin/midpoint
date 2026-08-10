<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (
            Schema::hasTable(
                'seller_reviews'
            )
        ) {
            return;
        }


        Schema::create(
            'seller_reviews',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Seller
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('seller_id')
                    ->constrained('users')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Buyer
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('buyer_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Product
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('seller_product_id')
                    ->nullable()
                    ->constrained('seller_products')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Rating
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedTinyInteger('rating');


                /*
                |--------------------------------------------------------------------------
                | Review
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('review');


                /*
                |--------------------------------------------------------------------------
                | Published
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('is_published')
                    ->default(true);


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'seller_id',
                    'is_published',
                ]);

            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'seller_reviews'
        );
    }
};