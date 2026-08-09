<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contact_messages', function (Blueprint $table) {

            $table->id();

            $table->string('name', 150);

            $table->string('email', 190);

            $table->string('topic', 80);

            $table->text('message');

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('new')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Read Status
            |--------------------------------------------------------------------------
            */

            $table->timestamp('read_at')
                ->nullable()
                ->index();

            $table->foreignId('read_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Request Information
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address', 45)
                ->nullable();

            $table->text('user_agent')
                ->nullable();


            $table->timestamps();


            $table->index([
                'status',
                'read_at',
            ]);

        });
    }


    public function down()
    {
        Schema::dropIfExists('contact_messages');
    }
};