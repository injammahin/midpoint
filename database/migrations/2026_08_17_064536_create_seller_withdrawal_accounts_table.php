<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seller_withdrawal_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('bank_name', 180);
            $table->string('bank_code', 50);
            $table->string('account_name', 180);

            /*
             * Never store the seller's full account number as plain text.
             */
            $table->text('account_number_encrypted');
            $table->char('account_number_hash', 64);
            $table->char('account_number_last4', 4);

            $table->string(
                'paystack_recipient_code',
                100
            )->nullable()->index();

            $table->boolean('is_verified')
                ->default(false)
                ->index();

            $table->boolean('is_active')
                ->default(false)
                ->index();

            $table->dateTime('verified_at')
                ->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'seller_id',
                    'bank_code',
                    'account_number_hash',
                ],
                'seller_withdrawal_account_unique'
            );

            $table->index(
                [
                    'seller_id',
                    'is_active',
                ],
                'seller_withdrawal_account_active_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists(
            'seller_withdrawal_accounts'
        );
    }
};