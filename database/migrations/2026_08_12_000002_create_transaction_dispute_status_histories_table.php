<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'transaction_dispute_status_histories',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('transaction_dispute_id')
                    ->constrained('transaction_disputes')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('secure_transaction_id')
                    ->constrained('secure_transactions')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('admin_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('from_status', 50)
                    ->nullable();

                $table
                    ->string('to_status', 50);

                /*
                |--------------------------------------------------------------------------
                | Customer-facing/admin status message
                |--------------------------------------------------------------------------
                |
                | For awaiting_buyer / awaiting_seller / resolved this message
                | can be included in the email sent to the relevant party.
                |
                */

                $table
                    ->text('note')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'transaction_dispute_id',
                    'created_at',
                ]);

                $table->index([
                    'to_status',
                    'created_at',
                ]);
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'transaction_dispute_status_histories'
        );
    }
};