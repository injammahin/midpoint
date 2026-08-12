<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'seller_invoice_payments',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('seller_invoice_id')
                    ->constrained('seller_invoices')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('seller_application_id')
                    ->constrained('seller_applications')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->string('provider', 30)
                    ->default('paystack');

                $table
                    ->string('reference', 120)
                    ->unique();

                $table
                    ->string('access_code')
                    ->nullable();

                $table
                    ->text('authorization_url')
                    ->nullable();

                $table
                    ->decimal('amount', 15, 2);

                $table
                    ->unsignedBigInteger('amount_subunit');

                $table
                    ->string('currency', 3)
                    ->default('NGN');

                $table
                    ->string('status', 30)
                    ->default('created')
                    ->index();

                $table
                    ->unsignedBigInteger('paystack_transaction_id')
                    ->nullable();

                $table
                    ->string('channel', 50)
                    ->nullable();

                $table
                    ->string('gateway_response', 255)
                    ->nullable();

                $table
                    ->dateTime('initialized_at')
                    ->nullable();

                $table
                    ->dateTime('verified_at')
                    ->nullable();

                $table
                    ->dateTime('paid_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'seller_invoice_id',
                    'status',
                ]);

                $table->index([
                    'user_id',
                    'status',
                ]);
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'seller_invoice_payments'
        );
    }
};