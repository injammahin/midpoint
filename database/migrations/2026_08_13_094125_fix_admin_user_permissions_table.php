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
        /*
        |--------------------------------------------------------------------------
        | Rebuild Admin Permission Table
        |--------------------------------------------------------------------------
        |
        | The previous duplicate migration left this table with an incorrect
        | structure. Role management is new, so we safely rebuild ONLY this
        | table. The users table is not modified here.
        |
        */

        Schema::dropIfExists('admin_user_permissions');


        Schema::create('admin_user_permissions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->string('permission', 100);

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Foreign Key
            |--------------------------------------------------------------------------
            */

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Permission
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'user_id',
                    'permission',
                ],
                'admin_user_permission_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Permission Lookup Index
            |--------------------------------------------------------------------------
            */

            $table->index(
                'permission',
                'admin_user_permission_index'
            );
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(
            'admin_user_permissions'
        );
    }
};