<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {

                $table
                    ->string('bank_code', 20)
                    ->nullable()
                    ->after('bank_name');

                $table
                    ->string(
                        'paystack_recipient_code',
                        120
                    )
                    ->nullable()
                    ->after('bank_account_number');
            }
        );
    }

    public function down()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {

                $table->dropColumn([
                    'bank_code',
                    'paystack_recipient_code',
                ]);
            }
        );
    }
};