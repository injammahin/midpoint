<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'seller_wallet_transactions',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId('seller_wallet_id')
                    ->constrained('seller_wallets')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('seller_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Original Midpoint Transaction
                |--------------------------------------------------------------------------
                |
                | Nullable because later a wallet ledger entry can also represent
                | withdrawals, adjustments, refunds, etc.
                |
                */
                $table
                    ->foreignId('secure_transaction_id')
                    ->nullable()
                    ->constrained('secure_transactions')
                    ->nullOnDelete();

                $table
                    ->string(
                        'reference',
                        100
                    )
                    ->unique();

                /*
                |--------------------------------------------------------------------------
                | Ledger Type
                |--------------------------------------------------------------------------
                |
                | Current:
                | transaction_release
                |
                | Future:
                | withdrawal
                | withdrawal_refund
                | admin_adjustment
                |
                */
                $table
                    ->string(
                        'type',
                        50
                    )
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | Credit / Debit
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'direction',
                        20
                    )
                    ->index();

                $table
                    ->string(
                        'status',
                        30
                    )
                    ->default('posted')
                    ->index();

                $table
                    ->string(
                        'currency',
                        3
                    )
                    ->default('NGN');

                $table
                    ->decimal(
                        'amount',
                        18,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | Audit Trail
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'balance_before',
                        18,
                        2
                    );

                $table
                    ->decimal(
                        'balance_after',
                        18,
                        2
                    );

                $table
                    ->string(
                        'description',
                        255
                    )
                    ->nullable();

                $table
                    ->json('meta')
                    ->nullable();

                $table
                    ->dateTime('processed_at')
                    ->nullable()
                    ->index();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | CRITICAL: Prevent Duplicate Transaction Release
                |--------------------------------------------------------------------------
                |
                | The same secure transaction can NEVER be credited twice.
                |
                */
                $table->unique(
                    [
                        'secure_transaction_id',
                        'type',
                    ],
                    'seller_wallet_txn_release_unique'
                );
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'seller_wallet_transactions'
        );
    }
};