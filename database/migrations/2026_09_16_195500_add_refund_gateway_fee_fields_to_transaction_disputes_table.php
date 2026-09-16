<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'transaction_disputes',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Refund Breakdown
                |--------------------------------------------------------------------------
                |
                | approved_refund_amount:
                | Amount admin approved BEFORE Paystack fee deduction.
                |
                | refund_gateway_fee_amount:
                | Actual original Paystack processing fee deducted.
                |
                | Existing refund_amount:
                | NET amount actually sent to Paystack/buyer.
                |
                */

                $table
                    ->decimal(
                        'approved_refund_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('refund_amount');


                $table
                    ->unsignedBigInteger(
                        'approved_refund_amount_subunit'
                    )
                    ->nullable()
                    ->after('refund_amount_subunit');


                $table
                    ->decimal(
                        'refund_gateway_fee_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('approved_refund_amount');


                $table
                    ->unsignedBigInteger(
                        'refund_gateway_fee_subunit'
                    )
                    ->nullable()
                    ->after('approved_refund_amount_subunit');
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'transaction_disputes',
            function (Blueprint $table) {

                $table->dropColumn([
                    'approved_refund_amount',
                    'approved_refund_amount_subunit',
                    'refund_gateway_fee_amount',
                    'refund_gateway_fee_subunit',
                ]);
            }
        );
    }
};