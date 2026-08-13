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


            $table->unsignedBigInteger(
                'transaction_dispute_id'
            );


            $table->unsignedBigInteger(
                'secure_transaction_id'
            );


            $table->unsignedBigInteger(
                'admin_id'
            )->nullable();


            $table->string(
                'from_status',
                50
            )->nullable();


            $table->string(
                'to_status',
                50
            );


            $table->text(
                'note'
            )->nullable();


            $table->timestamps();



            $table
                ->foreign(
                    'transaction_dispute_id',
                    'tdsh_dispute_fk'
                )
                ->references('id')
                ->on('transaction_disputes')
                ->cascadeOnDelete();



            $table
                ->foreign(
                    'secure_transaction_id',
                    'tdsh_transaction_fk'
                )
                ->references('id')
                ->on('secure_transactions')
                ->cascadeOnDelete();



            $table
                ->foreign(
                    'admin_id',
                    'tdsh_admin_fk'
                )
                ->references('id')
                ->on('users')
                ->nullOnDelete();



            $table->index(
                [
                    'transaction_dispute_id',
                    'created_at'
                ],
                'tdsh_dispute_created_idx'
            );


            $table->index(
                [
                    'to_status',
                    'created_at'
                ],
                'tdsh_status_created_idx'
            );

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