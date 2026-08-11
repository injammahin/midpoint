<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_notifications', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table
                ->foreignId('secure_transaction_id')
                ->nullable()
                ->constrained('secure_transactions')
                ->cascadeOnDelete();

            $table
                ->string('event_key', 180)
                ->unique();

            $table
                ->string('audience', 20)
                ->index();

            $table
                ->string('type', 30)
                ->index();

            $table
                ->string('title');

            $table
                ->text('message')
                ->nullable();

            $table
                ->json('data')
                ->nullable();

            $table
                ->dateTime('read_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'audience',
                'read_at',
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_notifications');
    }
};