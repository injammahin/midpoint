<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'seller_withdrawals',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'seller_wallet_id'
                )
                    ->constrained('seller_wallets')
                    ->cascadeOnDelete();

                $table->foreignId(
                    'seller_id'
                )
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId(
                    'seller_withdrawal_account_id'
                )
                    ->nullable()
                    ->constrained(
                        'seller_withdrawal_accounts'
                    )
                    ->nullOnDelete();

                $table->string(
                    'reference',
                    120
                )->unique();

                $table->string(
                    'paystack_transfer_reference',
                    120
                )
                    ->nullable()
                    ->unique();

                $table->string(
                    'paystack_transfer_code',
                    120
                )
                    ->nullable()
                    ->index();

                $table->string(
                    'paystack_recipient_code',
                    120
                );

                /*
                 * Snapshot bank details at the moment
                 * the withdrawal is requested.
                 */
                $table->string(
                    'bank_name',
                    180
                );

                $table->string(
                    'account_name',
                    180
                );

                $table->char(
                    'account_number_last4',
                    4
                );

                $table->string(
                    'currency',
                    3
                )->default('NGN');

                $table->decimal(
                    'amount',
                    18,
                    2
                );

                $table->string(
                    'status',
                    30
                )
                    ->default('pending')
                    ->index();

                $table->text(
                    'failure_reason'
                )->nullable();

                $table->json(
                    'meta'
                )->nullable();

                $table->dateTime(
                    'requested_at'
                )
                    ->nullable()
                    ->index();

                $table->dateTime(
                    'initiated_at'
                )->nullable();

                $table->dateTime(
                    'completed_at'
                )->nullable();

                $table->dateTime(
                    'failed_at'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'seller_id',
                    'status',
                ]);
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'seller_withdrawals'
        );
    }
};