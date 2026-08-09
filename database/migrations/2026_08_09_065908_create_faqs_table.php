<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {

            $table->id();

            $table->string('question', 500);

            $table->text('answer');

            /*
            |--------------------------------------------------------------------------
            | Display
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Homepage
            |--------------------------------------------------------------------------
            |
            | The full FAQ page shows all active FAQs.
            | Homepage can show only selected FAQs.
            |
            */

            $table->boolean('show_on_home')
                ->default(false)
                ->index();


            /*
            |--------------------------------------------------------------------------
            | Administration Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->timestamps();

            $table->softDeletes();


            $table->index([
                'is_active',
                'sort_order',
            ]);

        });
    }


    public function down()
    {
        Schema::dropIfExists('faqs');
    }
};