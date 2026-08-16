<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'secure_transactions',
            function (Blueprint $table) {

                $table
                    ->string(
                        'transaction_source',
                        40
                    )
                    ->nullable()
                    ->after(
                        'transaction_type'
                    )
                    ->index();
            }
        );
    }


    public function down()
    {
        Schema::table(
            'secure_transactions',
            function (Blueprint $table) {

                $table->dropIndex([
                    'transaction_source',
                ]);

                $table->dropColumn(
                    'transaction_source'
                );
            }
        );
    }
};