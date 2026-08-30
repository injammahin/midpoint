<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {

                $table->string(
                    'paystack_customer_code',
                    100
                )
                    ->nullable()
                    ->index()
                    ->after('provider_status');


                $table->string(
                    'paystack_customer_id',
                    100
                )
                    ->nullable()
                    ->after('paystack_customer_code');


                $table->string(
                    'paystack_identification_status',
                    50
                )
                    ->nullable()
                    ->index()
                    ->after('paystack_customer_id');


                $table->dateTime(
                    'paystack_identification_requested_at'
                )
                    ->nullable()
                    ->after('paystack_identification_status');


                $table->dateTime(
                    'paystack_identification_completed_at'
                )
                    ->nullable()
                    ->after('paystack_identification_requested_at');
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {

                $table->dropColumn([
                    'paystack_customer_code',
                    'paystack_customer_id',
                    'paystack_identification_status',
                    'paystack_identification_requested_at',
                    'paystack_identification_completed_at',
                ]);
            }
        );
    }
};