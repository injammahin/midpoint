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
                'user_notification_preferences'
            )
        ) {
            return;
        }


        Schema::create(
            'user_notification_preferences',
            function (Blueprint $table) {

                $table->id();


                $table
                    ->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Seller Notifications
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean(
                        'payment_alerts'
                    )
                    ->default(true);


                $table
                    ->boolean(
                        'dispatch_updates'
                    )
                    ->default(true);


                $table
                    ->boolean(
                        'inspection_reminders'
                    )
                    ->default(true);


                $table
                    ->boolean(
                        'whatsapp_notifications'
                    )
                    ->default(true);


                $table
                    ->boolean(
                        'marketing_emails'
                    )
                    ->default(false);


                $table->timestamps();

            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'user_notification_preferences'
        );
    }
};