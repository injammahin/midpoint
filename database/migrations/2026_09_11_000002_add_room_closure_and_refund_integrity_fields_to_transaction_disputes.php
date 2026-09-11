<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'transaction_disputes',
            function (Blueprint $table) {

                $table
                    ->dateTime('room_closed_at')
                    ->nullable()
                    ->index()
                    ->after('room_activated_by');

                $table
                    ->foreignId('room_closed_by')
                    ->nullable()
                    ->after('room_closed_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('room_close_type', 30)
                    ->nullable()
                    ->after('room_closed_by');

                $table
                    ->text('room_close_reason')
                    ->nullable()
                    ->after('room_close_type');

                /*
                |--------------------------------------------------------------
                | Refund amount integrity
                |--------------------------------------------------------------
                |
                | Keep both the amount Midpoint requested and the amount
                | Paystack returned, in currency subunits (kobo for NGN).
                |
                */

                $table
                    ->unsignedBigInteger('refund_amount_subunit')
                    ->nullable()
                    ->after('refund_amount');

                $table
                    ->unsignedBigInteger('paystack_refund_amount_subunit')
                    ->nullable()
                    ->after('paystack_refund_status');

                $table
                    ->dateTime('paystack_refund_requested_at')
                    ->nullable()
                    ->after('paystack_refund_amount_subunit');
            }
        );
    }


    public function down()
    {
        Schema::table(
            'transaction_disputes',
            function (Blueprint $table) {

                $table->dropForeign([
                    'room_closed_by',
                ]);

                $table->dropColumn([
                    'room_closed_at',
                    'room_closed_by',
                    'room_close_type',
                    'room_close_reason',
                    'refund_amount_subunit',
                    'paystack_refund_amount_subunit',
                    'paystack_refund_requested_at',
                ]);
            }
        );
    }
};
