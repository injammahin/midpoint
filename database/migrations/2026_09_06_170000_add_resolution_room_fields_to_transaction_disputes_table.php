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
                    ->dateTime('room_activated_at')
                    ->nullable()
                    ->after('admin_note');

                $table
                    ->foreignId('room_activated_by')
                    ->nullable()
                    ->after('room_activated_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('resolution_type', 50)
                    ->nullable()
                    ->after('room_activated_by');

                $table
                    ->string('resolution_status', 50)
                    ->nullable()
                    ->index()
                    ->after('resolution_type');

                $table
                    ->decimal(
                        'refund_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('resolution_status');

                $table
                    ->decimal(
                        'seller_settlement_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('refund_amount');

                $table
                    ->decimal(
                        'resolution_service_fee_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('seller_settlement_amount');

                $table
                    ->decimal(
                        'resolution_vat_amount',
                        18,
                        2
                    )
                    ->nullable()
                    ->after('resolution_service_fee_amount');

                $table
                    ->text('resolution_note')
                    ->nullable()
                    ->after('resolution_vat_amount');

                $table
                    ->foreignId('resolved_by')
                    ->nullable()
                    ->after('resolution_note')
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->dateTime('resolution_initiated_at')
                    ->nullable()
                    ->after('resolved_by');

                $table
                    ->string('paystack_refund_id', 100)
                    ->nullable()
                    ->index()
                    ->after('resolution_initiated_at');

                $table
                    ->string('paystack_refund_reference', 150)
                    ->nullable()
                    ->index()
                    ->after('paystack_refund_id');

                $table
                    ->string('paystack_refund_status', 50)
                    ->nullable()
                    ->index()
                    ->after('paystack_refund_reference');

                $table
                    ->dateTime('refund_expected_at')
                    ->nullable()
                    ->after('paystack_refund_status');

                $table
                    ->dateTime('refund_processed_at')
                    ->nullable()
                    ->after('refund_expected_at');

                $table
                    ->text('refund_error')
                    ->nullable()
                    ->after('refund_processed_at');
            }
        );
    }

    public function down()
    {
        Schema::table(
            'transaction_disputes',
            function (Blueprint $table) {

                $table->dropForeign([
                    'room_activated_by',
                ]);

                $table->dropForeign([
                    'resolved_by',
                ]);

                $table->dropColumn([
                    'room_activated_at',
                    'room_activated_by',
                    'resolution_type',
                    'resolution_status',
                    'refund_amount',
                    'seller_settlement_amount',
                    'resolution_service_fee_amount',
                    'resolution_vat_amount',
                    'resolution_note',
                    'resolved_by',
                    'resolution_initiated_at',
                    'paystack_refund_id',
                    'paystack_refund_reference',
                    'paystack_refund_status',
                    'refund_expected_at',
                    'refund_processed_at',
                    'refund_error',
                ]);
            }
        );
    }
};
