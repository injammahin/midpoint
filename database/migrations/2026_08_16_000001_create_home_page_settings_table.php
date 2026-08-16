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
            'home_page_settings',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Hero Section
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'hero_badge',
                    180
                );

                $table->string(
                    'hero_title_before',
                    180
                );

                $table->string(
                    'hero_title_highlight',
                    120
                );

                $table->string(
                    'hero_title_after',
                    30
                )->nullable();

                $table->text(
                    'hero_description'
                );

                $table->string(
                    'hero_primary_button_text',
                    80
                );

                $table->string(
                    'hero_primary_button_url',
                    500
                );

                $table->string(
                    'hero_secondary_button_text',
                    80
                );

                $table->string(
                    'hero_secondary_button_url',
                    500
                );


                /*
                |--------------------------------------------------------------------------
                | Hero Statistics
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'stat_one_value',
                    50
                );

                $table->string(
                    'stat_one_label',
                    100
                );

                $table->string(
                    'stat_two_value',
                    50
                );

                $table->string(
                    'stat_two_label',
                    100
                );

                $table->string(
                    'stat_three_value',
                    50
                );

                $table->string(
                    'stat_three_label',
                    100
                );


                /*
                |--------------------------------------------------------------------------
                | Three Simple Steps
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'steps_eyebrow',
                    100
                );

                $table->string(
                    'steps_title',
                    255
                );

                $table->text(
                    'steps_description'
                );


                $table->string(
                    'step_one_title',
                    150
                );

                $table->text(
                    'step_one_description'
                );


                $table->string(
                    'step_two_title',
                    150
                );

                $table->text(
                    'step_two_description'
                );


                $table->string(
                    'step_three_title',
                    150
                );

                $table->text(
                    'step_three_description'
                );


                /*
                |--------------------------------------------------------------------------
                | Why MidPoint
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'why_eyebrow',
                    100
                );

                $table->string(
                    'why_title',
                    255
                );


                $table->string(
                    'why_one_icon',
                    30
                );

                $table->string(
                    'why_one_title',
                    150
                );

                $table->text(
                    'why_one_description'
                );


                $table->string(
                    'why_two_icon',
                    30
                );

                $table->string(
                    'why_two_title',
                    150
                );

                $table->text(
                    'why_two_description'
                );


                $table->string(
                    'why_three_icon',
                    30
                );

                $table->string(
                    'why_three_title',
                    150
                );

                $table->text(
                    'why_three_description'
                );


                $table->string(
                    'why_four_icon',
                    30
                );

                $table->string(
                    'why_four_title',
                    150
                );

                $table->text(
                    'why_four_description'
                );


                /*
                |--------------------------------------------------------------------------
                | Featured Businesses
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'featured_eyebrow',
                    100
                );

                $table->string(
                    'featured_title',
                    255
                );

                $table->string(
                    'featured_view_all_text',
                    80
                );


                /*
                |--------------------------------------------------------------------------
                | Testimonials Section Heading
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'testimonials_eyebrow',
                    100
                );

                $table->string(
                    'testimonials_title',
                    255
                );


                /*
                |--------------------------------------------------------------------------
                | FAQ Section
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'faq_eyebrow',
                    100
                );

                $table->string(
                    'faq_title',
                    255
                );

                $table->string(
                    'faq_view_all_text',
                    80
                );


                /*
                |--------------------------------------------------------------------------
                | Final CTA
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'final_cta_title',
                    255
                );

                $table->text(
                    'final_cta_description'
                );

                $table->string(
                    'final_cta_button_text',
                    80
                );

                $table->string(
                    'final_cta_button_url',
                    500
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
     * Reverse migrations.
     */
    public function down()
    {
        Schema::dropIfExists(
            'home_page_settings'
        );
    }
};