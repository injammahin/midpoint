<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up()
    {
        Schema::create(
            'home_testimonials',
            function (Blueprint $table) {

                $table->id();


                $table->string(
                    'reviewer_name',
                    120
                );


                $table->string(
                    'reviewer_meta',
                    180
                );


                $table->text(
                    'review_text'
                );


                $table
                    ->unsignedTinyInteger(
                        'rating'
                    )
                    ->default(5);


                $table
                    ->string(
                        'avatar_initials',
                        4
                    )
                    ->nullable();


                $table
                    ->string(
                        'avatar_color',
                        7
                    )
                    ->default(
                        '#7A5AF8'
                    );


                $table
                    ->boolean(
                        'is_active'
                    )
                    ->default(true)
                    ->index();


                $table
                    ->unsignedInteger(
                        'sort_order'
                    )
                    ->default(0)
                    ->index();


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
     * Reverse migrations.
     */
    public function down()
    {
        Schema::dropIfExists(
            'home_testimonials'
        );
    }
};