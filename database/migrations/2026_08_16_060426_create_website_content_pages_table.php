<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'website_content_pages',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Page Identity
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'slug',
                        100
                    )
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'meta_title',
                        255
                    );


                $table
                    ->text(
                        'meta_description'
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Dynamic Page Content
                |--------------------------------------------------------------------------
                |
                | Stores structured page sections as JSON.
                |
                */

                $table
                    ->json(
                        'content'
                    );


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'updated_by'
                    )
                    ->nullable()
                    ->constrained(
                        'users'
                    )
                    ->nullOnDelete();


                $table->timestamps();
            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(
            'website_content_pages'
        );
    }
};