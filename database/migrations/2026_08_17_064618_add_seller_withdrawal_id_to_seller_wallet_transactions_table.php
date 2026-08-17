<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'seller_wallet_transactions',
            function (Blueprint $table) {

                $table->foreignId(
                    'seller_withdrawal_id'
                )
                    ->nullable()
                    ->after(
                        'secure_transaction_id'
                    )
                    ->constrained(
                        'seller_withdrawals'
                    )
                    ->nullOnDelete();

                $table->unique(
                    [
                        'seller_withdrawal_id',
                        'type',
                    ],
                    'seller_wallet_withdrawal_type_unique'
                );
            }
        );
    }

    public function down()
    {
        Schema::table(
            'seller_wallet_transactions',
            function (Blueprint $table) {

                $table->dropUnique(
                    'seller_wallet_withdrawal_type_unique'
                );

                $table->dropConstrainedForeignId(
                    'seller_withdrawal_id'
                );
            }
        );
    }
};