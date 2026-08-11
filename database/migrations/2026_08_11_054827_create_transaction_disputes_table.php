<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'transaction_disputes',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('secure_transaction_id')
                    ->unique()
                    ->constrained('secure_transactions')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('buyer_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('seller_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->string('reason');

                $table
                    ->text('description');

                $table
                    ->string('desired_outcome');

                $table
                    ->json('evidence')
                    ->nullable();

                $table
                    ->string('return_method')
                    ->nullable();

                $table
                    ->string('return_proof_path')
                    ->nullable();

                $table
                    ->string('status')
                    ->default('open')
                    ->index();

                $table
                    ->text('admin_note')
                    ->nullable();

                $table
                    ->dateTime('opened_at');

                $table
                    ->dateTime('resolved_at')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'transaction_disputes'
        );
    }
};