<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Seller Invoice Pricing Snapshot
        |--------------------------------------------------------------------------
        |
        | amount = actual amount seller pays
        |
        | package_price = full target package price
        |
        | Example:
        |
        | Premium full price = 25,000
        | Existing plan credit = 9,333.33
        | amount = 15,666.67
        | package_price = 25,000
        |
        */

        Schema::table(
            'seller_invoices',
            function (Blueprint $table) {
                $table
                    ->decimal('package_price', 12, 2)
                    ->nullable()
                    ->after('product_limit');

                $table
                    ->decimal('proration_credit', 12, 2)
                    ->default(0)
                    ->after('package_price');

                $table
                    ->decimal('proration_used_amount', 12, 2)
                    ->default(0)
                    ->after('proration_credit');

                $table
                    ->dateTime('proration_calculated_at')
                    ->nullable()
                    ->after('proration_used_amount');
            }
        );


        /*
         * Existing invoices were created before package_price existed.
         *
         * For those invoices amount represented the complete package
         * price, therefore it is a valid historical fallback.
         */

        DB::table('seller_invoices')
            ->whereNull('package_price')
            ->update([
                'package_price' => DB::raw('amount'),
            ]);


        /*
         * Downgrades are no longer supported.
         *
         * Keep paid historical records untouched.
         * Cancel only old unpaid downgrade invoices.
         */

        DB::table('seller_invoices')
            ->where('purchase_type', 'downgrade')
            ->where('status', 'unpaid')
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | Seller Wallet Spending
        |--------------------------------------------------------------------------
        |
        | total_withdrawn remains bank withdrawals.
        |
        | total_spent is for package purchases from wallet.
        |
        */

        Schema::table(
            'seller_wallets',
            function (Blueprint $table) {
                $table
                    ->decimal('total_spent', 18, 2)
                    ->default(0)
                    ->after('total_withdrawn');
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Wallet Transaction -> Seller Invoice
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'seller_wallet_transactions',
            function (Blueprint $table) {
                $table
                    ->foreignId('seller_invoice_id')
                    ->nullable()
                    ->after('secure_transaction_id')
                    ->constrained('seller_invoices')
                    ->nullOnDelete();

                /*
                 * One invoice cannot be charged from wallet twice.
                 */

                $table->unique(
                    [
                        'seller_invoice_id',
                        'type',
                    ],
                    'seller_wallet_invoice_type_unique'
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
                    'seller_wallet_invoice_type_unique'
                );

                $table->dropConstrainedForeignId(
                    'seller_invoice_id'
                );
            }
        );


        Schema::table(
            'seller_wallets',
            function (Blueprint $table) {
                $table->dropColumn(
                    'total_spent'
                );
            }
        );


        Schema::table(
            'seller_invoices',
            function (Blueprint $table) {
                $table->dropColumn([
                    'package_price',
                    'proration_credit',
                    'proration_used_amount',
                    'proration_calculated_at',
                ]);
            }
        );
    }
};