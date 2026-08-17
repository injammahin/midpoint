<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'seller_wallets',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('seller_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->string('currency', 3)
                    ->default('NGN');

                /*
                |--------------------------------------------------------------------------
                | Money Available For Future Withdrawal
                |--------------------------------------------------------------------------
                |
                | Every successful fund release is added here.
                |
                */
                $table
                    ->decimal(
                        'available_balance',
                        18,
                        2
                    )
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Reserved For Withdrawal
                |--------------------------------------------------------------------------
                |
                | We are not implementing withdrawal now.
                | Later, when seller requests withdrawal, amount can move:
                |
                | available_balance
                |      ->
                | pending_withdrawal_balance
                |
                */
                $table
                    ->decimal(
                        'pending_withdrawal_balance',
                        18,
                        2
                    )
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Lifetime Totals
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'total_credited',
                        18,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'total_withdrawn',
                        18,
                        2
                    )
                    ->default(0);

                $table->timestamps();
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'seller_wallets'
        );
    }
};