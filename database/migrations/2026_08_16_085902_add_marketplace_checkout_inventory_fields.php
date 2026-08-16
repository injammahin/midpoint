<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('secure_transactions', function (Blueprint $table) {

            $table
                ->dateTime('stock_reserved_at')
                ->nullable()
                ->after('claimed_at');

            $table
                ->dateTime('stock_reserved_until')
                ->nullable()
                ->index()
                ->after('stock_reserved_at');

            $table
                ->dateTime('stock_released_at')
                ->nullable()
                ->after('stock_reserved_until');

            $table
                ->dateTime('stock_deducted_at')
                ->nullable()
                ->after('stock_released_at');
        });


        Schema::table('seller_products', function (Blueprint $table) {

            $table
                ->dateTime('out_of_stock_notified_at')
                ->nullable()
                ->after('stock');
        });
    }


    public function down()
    {
        Schema::table('seller_products', function (Blueprint $table) {

            $table->dropColumn(
                'out_of_stock_notified_at'
            );
        });


        Schema::table('secure_transactions', function (Blueprint $table) {

            $table->dropColumn([
                'stock_reserved_at',
                'stock_reserved_until',
                'stock_released_at',
                'stock_deducted_at',
            ]);
        });
    }
};