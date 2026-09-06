<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (
            !Schema::hasTable(
                'seller_reviews'
            )
        ) {
            return;
        }


        if (
            !Schema::hasColumn(
                'seller_reviews',
                'secure_transaction_id'
            )
        ) {

            Schema::table(
                'seller_reviews',
                function (Blueprint $table) {

                    /*
                    |--------------------------------------------------------------------------
                    | One Review Per Secure Transaction
                    |--------------------------------------------------------------------------
                    |
                    | Nullable keeps any old/manual review rows valid.
                    | Every new transaction review created by the buyer flow will
                    | always populate this field.
                    |
                    */

                    $table
                        ->foreignId(
                            'secure_transaction_id'
                        )
                        ->nullable()
                        ->after(
                            'buyer_id'
                        )
                        ->constrained(
                            'secure_transactions'
                        )
                        ->cascadeOnDelete();


                    $table
                        ->unique(
                            'secure_transaction_id',
                            'seller_reviews_secure_transaction_unique'
                        );
                }
            );
        }
    }


    public function down()
    {
        if (
            !Schema::hasTable(
                'seller_reviews'
            )
            ||
            !Schema::hasColumn(
                'seller_reviews',
                'secure_transaction_id'
            )
        ) {
            return;
        }


        Schema::table(
            'seller_reviews',
            function (Blueprint $table) {

                $table->dropUnique(
                    'seller_reviews_secure_transaction_unique'
                );


                $table->dropForeign([
                    'secure_transaction_id',
                ]);


                $table->dropColumn(
                    'secure_transaction_id'
                );
            }
        );
    }
};
