<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'transaction_email_deliveries',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('secure_transaction_id')
                    ->constrained('secure_transactions')
                    ->cascadeOnDelete();

                $table
                    ->string('event_key', 190);

                $table
                    ->string('audience', 30);

                $table
                    ->string('email');

                $table
                    ->string('subject')
                    ->nullable();

                $table
                    ->unsignedInteger('attempts')
                    ->default(0);

                $table
                    ->dateTime('last_attempt_at')
                    ->nullable();

                $table
                    ->dateTime('sent_at')
                    ->nullable()
                    ->index();

                $table
                    ->dateTime('failed_at')
                    ->nullable();

                $table
                    ->text('last_error')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'secure_transaction_id',
                        'event_key',
                        'audience',
                        'email',
                    ],
                    'txn_email_delivery_unique'
                );
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'transaction_email_deliveries'
        );
    }
};