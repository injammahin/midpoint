<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('phone', 30)
                ->nullable()
                ->unique()
                ->after('email');

            $table->string('role', 30)
                ->default('user')
                ->index()
                ->after('password');

            $table->boolean('status')
                ->default(true)
                ->index()
                ->after('role');
        });
    }


    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'status',
            ]);
        });
    }
};