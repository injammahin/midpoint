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
                ->dateTime('seller_payment_email_sent_at')
                ->nullable()
                ->after('paid_at');

            $table
                ->dateTime('buyer_payment_email_sent_at')
                ->nullable()
                ->after('seller_payment_email_sent_at');
        });
    }

    public function down()
    {
        Schema::table('secure_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'seller_payment_email_sent_at',
                'buyer_payment_email_sent_at',
            ]);
        });
    }
};