<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'transaction_dispute_messages',
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
                    ->foreignId('sender_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->string('sender_role', 30)
                    ->default('system');

                /*
                |--------------------------------------------------------------------------
                | Visibility
                |--------------------------------------------------------------------------
                |
                | all      = buyer + seller + admin
                | buyer    = buyer + admin
                | seller   = seller + admin
                | internal = admin only
                |
                */

                $table
                    ->string('visibility', 20)
                    ->default('all')
                    ->index();

                $table
                    ->text('message')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Private Attachments
                |--------------------------------------------------------------------------
                |
                | JSON contains:
                | original_name, path, mime, size
                |
                | Files are stored on the local/private disk and downloaded
                | only through an authorized controller action.
                |
                */

                $table
                    ->json('attachments')
                    ->nullable();

                $table
                    ->boolean('is_system')
                    ->default(false)
                    ->index();

                $table->timestamps();

                $table->index(
                    [
                        'transaction_dispute_id',
                        'id',
                    ],
                    'tdm_dispute_id_idx'
                );
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'transaction_dispute_messages'
        );
    }
};
