<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Profile
                |--------------------------------------------------------------------------
                */

                if (
                    !Schema::hasColumn(
                        'users',
                        'city'
                    )
                ) {

                    $table
                        ->string('city', 150)
                        ->nullable()
                        ->after('phone');
                }


                /*
                |--------------------------------------------------------------------------
                | Bank
                |--------------------------------------------------------------------------
                */

                if (
                    !Schema::hasColumn(
                        'users',
                        'bank_name'
                    )
                ) {

                    $table
                        ->string('bank_name', 150)
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'bank_account_name'
                    )
                ) {

                    $table
                        ->string(
                            'bank_account_name',
                            180
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'bank_account_number'
                    )
                ) {

                    $table
                        ->string(
                            'bank_account_number',
                            30
                        )
                        ->nullable();
                }


                /*
                |--------------------------------------------------------------------------
                | Two Factor Authentication
                |--------------------------------------------------------------------------
                */

                if (
                    !Schema::hasColumn(
                        'users',
                        'two_factor_secret'
                    )
                ) {

                    /*
                    | Encrypted TOTP secret.
                    */

                    $table
                        ->text(
                            'two_factor_secret'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'two_factor_recovery_codes'
                    )
                ) {

                    /*
                    | JSON containing hashes of recovery codes.
                    */

                    $table
                        ->text(
                            'two_factor_recovery_codes'
                        )
                        ->nullable();
                }


                if (
                    !Schema::hasColumn(
                        'users',
                        'two_factor_confirmed_at'
                    )
                ) {

                    $table
                        ->timestamp(
                            'two_factor_confirmed_at'
                        )
                        ->nullable();
                }

            }
        );
    }


    public function down()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {

                $columns = [

                    'city',

                    'bank_name',

                    'bank_account_name',

                    'bank_account_number',

                    'two_factor_secret',

                    'two_factor_recovery_codes',

                    'two_factor_confirmed_at',

                ];


                foreach (
                    $columns
                    as
                    $column
                ) {

                    if (
                        Schema::hasColumn(
                            'users',
                            $column
                        )
                    ) {

                        $table->dropColumn(
                            $column
                        );
                    }
                }

            }
        );
    }
};