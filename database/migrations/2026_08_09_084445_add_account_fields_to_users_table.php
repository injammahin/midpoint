<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | User Mode
            |--------------------------------------------------------------------------
            |
            | role:
            |   admin
            |   user
            |
            | preferred_role:
            |   seller
            |   buyer
            |
            */

            $table->string('preferred_role', 20)
                ->default('seller')
                ->after('role');


            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            |
            | We store HASH(token), never the raw verification token.
            |
            */

            $table->string(
                'email_verification_token',
                64
            )
                ->nullable()
                ->after('email_verified_at');


            $table->timestamp(
                'verification_sent_at'
            )
                ->nullable()
                ->after('email_verification_token');


            /*
            |--------------------------------------------------------------------------
            | Login Tracking
            |--------------------------------------------------------------------------
            */

            $table->timestamp('last_login_at')
                ->nullable();

            $table->string('last_login_ip', 45)
                ->nullable();

        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'preferred_role',
                'email_verification_token',
                'verification_sent_at',
                'last_login_at',
                'last_login_ip',
            ]);

        });
    }
};