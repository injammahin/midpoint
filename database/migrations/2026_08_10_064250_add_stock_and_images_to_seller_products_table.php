<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'seller_products',
            function (Blueprint $table) {

                if (
                    !Schema::hasColumn(
                        'seller_products',
                        'stock'
                    )
                ) {
                    $table
                        ->unsignedInteger('stock')
                        ->default(1)
                        ->after('price');
                }


                if (
                    !Schema::hasColumn(
                        'seller_products',
                        'images'
                    )
                ) {
                    $table
                        ->json('images')
                        ->nullable()
                        ->after('image');
                }
            }
        );
    }


    public function down()
    {
        Schema::table(
            'seller_products',
            function (Blueprint $table) {

                if (
                    Schema::hasColumn(
                        'seller_products',
                        'images'
                    )
                ) {
                    $table->dropColumn('images');
                }


                if (
                    Schema::hasColumn(
                        'seller_products',
                        'stock'
                    )
                ) {
                    $table->dropColumn('stock');
                }
            }
        );
    }
};