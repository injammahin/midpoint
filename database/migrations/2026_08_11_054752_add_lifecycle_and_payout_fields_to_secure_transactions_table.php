<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'secure_transactions',
            function (Blueprint $table) {

                $table
                    ->dateTime('preparing_at')
                    ->nullable()
                    ->after('paid_at');

                $table
                    ->dateTime('in_transit_at')
                    ->nullable()
                    ->after('dispatched_at');

                $table
                    ->dateTime('delivered_at')
                    ->nullable()
                    ->after('in_transit_at');

                $table
                    ->dateTime('inspection_started_at')
                    ->nullable()
                    ->after('delivered_at');

                if (!Schema::hasColumn(
                    'secure_transactions',
                    'inspection_ends_at'
                )) {
                    $table
                        ->dateTime('inspection_ends_at')
                        ->nullable();
                }

                $table
                    ->dateTime('auto_complete_at')
                    ->nullable()
                    ->index();

                $table
                    ->dateTime('release_approved_at')
                    ->nullable();

                $table
                    ->dateTime('funds_released_at')
                    ->nullable();

                $table
                    ->decimal(
                        'service_fee_rate',
                        8,
                        4
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'vat_rate',
                        8,
                        4
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'service_fee_amount',
                        15,
                        2
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'vat_amount',
                        15,
                        2
                    )
                    ->nullable();

                $table
                    ->decimal(
                        'seller_net_amount',
                        15,
                        2
                    )
                    ->nullable();

                $table
                    ->string(
                        'payout_status',
                        40
                    )
                    ->nullable()
                    ->index();

                $table
                    ->string(
                        'paystack_transfer_reference',
                        80
                    )
                    ->nullable()
                    ->unique();

                $table
                    ->string(
                        'paystack_transfer_code',
                        120
                    )
                    ->nullable();

                $table
                    ->dateTime('payout_initiated_at')
                    ->nullable();

                $table
                    ->dateTime('payout_completed_at')
                    ->nullable();
            }
        );
    }

    public function down()
    {
        Schema::table(
            'secure_transactions',
            function (Blueprint $table) {

                $table->dropColumn([
                    'preparing_at',
                    'in_transit_at',
                    'delivered_at',
                    'inspection_started_at',
                    'auto_complete_at',
                    'release_approved_at',
                    'funds_released_at',
                    'service_fee_rate',
                    'vat_rate',
                    'service_fee_amount',
                    'vat_amount',
                    'seller_net_amount',
                    'payout_status',
                    'paystack_transfer_reference',
                    'paystack_transfer_code',
                    'payout_initiated_at',
                    'payout_completed_at',
                ]);
            }
        );
    }
};