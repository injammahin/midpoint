<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Upgrade Paystack Transaction ID Columns
    |--------------------------------------------------------------------------
    |
    | Paystack transaction IDs can exceed the maximum value supported by
    | MySQL INT / INT UNSIGNED.
    |
    | Example:
    |
    | Paystack transaction ID:
    | 6456921497
    |
    | Maximum INT UNSIGNED:
    | 4294967295
    |
    | Therefore these columns MUST use BIGINT UNSIGNED.
    |
    */

    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Seller Package / Invoice Payments
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('seller_invoice_payments')
            &&
            Schema::hasColumn(
                'seller_invoice_payments',
                'paystack_transaction_id'
            )
        ) {

            DB::statement(
                '
                ALTER TABLE `seller_invoice_payments`
                MODIFY `paystack_transaction_id`
                BIGINT UNSIGNED NULL
                '
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Secure Transaction Payments
        |--------------------------------------------------------------------------
        |
        | Fix this proactively as well because buyer payments can receive the
        | same large Paystack transaction IDs.
        |
        */

        if (
            Schema::hasTable('secure_transaction_payments')
            &&
            Schema::hasColumn(
                'secure_transaction_payments',
                'paystack_transaction_id'
            )
        ) {

            DB::statement(
                '
                ALTER TABLE `secure_transaction_payments`
                MODIFY `paystack_transaction_id`
                BIGINT UNSIGNED NULL
                '
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Down
    |--------------------------------------------------------------------------
    |
    | Deliberately do NOT convert BIGINT back to INT.
    |
    | Doing so could destroy valid Paystack transaction IDs that are larger
    | than the INT range.
    |
    */

    public function down()
    {
        //
    }
};