<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (
            Schema::hasTable(
                'seller_business_profiles'
            )
        ) {
            return;
        }


        Schema::create(
            'seller_business_profiles',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Seller
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Branding
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('profile_image_path')
                    ->nullable();


                $table
                    ->string('tagline', 150)
                    ->nullable();


                $table
                    ->text('about')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Public Business Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('location', 180)
                    ->nullable();


                $table
                    ->string('phone', 40)
                    ->nullable();


                $table
                    ->string('business_hours', 255)
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | WhatsApp
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('whatsapp_number', 30)
                    ->nullable();


                $table
                    ->boolean('whatsapp_enabled')
                    ->default(false);


                $table
                    ->string(
                        'whatsapp_message',
                        500
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Online Presence
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('website_url')
                    ->nullable();


                $table
                    ->string('instagram_url')
                    ->nullable();


                $table
                    ->string('facebook_url')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Privacy
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('show_phone')
                    ->default(true);


                $table
                    ->boolean('show_email')
                    ->default(false);


                $table->timestamps();
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'seller_business_profiles'
        );
    }
};