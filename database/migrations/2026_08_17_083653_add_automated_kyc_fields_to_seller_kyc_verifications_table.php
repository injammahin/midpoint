<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {

                $table->string(
                    'verification_method',
                    30
                )
                    ->nullable()
                    ->after('status');


                $table->string(
                    'provider',
                    50
                )
                    ->nullable()
                    ->after('verification_method');


                $table->string(
                    'provider_environment',
                    30
                )
                    ->nullable()
                    ->after('provider');


                $table->string(
                    'provider_status',
                    50
                )
                    ->nullable()
                    ->after('provider_environment');


                /*
                |--------------------------------------------------------------------------
                | Government Identity
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'identity_first_name',
                    120
                )
                    ->nullable();


                $table->string(
                    'identity_middle_name',
                    120
                )
                    ->nullable();


                $table->string(
                    'identity_last_name',
                    120
                )
                    ->nullable();


                $table->date(
                    'identity_date_of_birth'
                )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Automated Checks
                |--------------------------------------------------------------------------
                */

                $table->boolean(
                    'liveness_passed'
                )
                    ->nullable();


                $table->decimal(
                    'liveness_probability',
                    6,
                    2
                )
                    ->nullable();


                $table->boolean(
                    'face_match'
                )
                    ->nullable();


                $table->decimal(
                    'face_confidence',
                    6,
                    2
                )
                    ->nullable();


                $table->boolean(
                    'name_match'
                )
                    ->nullable();


                $table->boolean(
                    'dob_match'
                )
                    ->nullable();


                $table->boolean(
                    'bank_name_match'
                )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Bank Used During KYC
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'seller_withdrawal_account_id'
                )
                    ->nullable()
                    ->constrained(
                        'seller_withdrawal_accounts'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Failure / Audit
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'failure_code',
                    100
                )
                    ->nullable();


                $table->text(
                    'failure_message'
                )
                    ->nullable();


                /*
                 * Only sanitized provider data.
                 * Do NOT save provider photo/base64 here.
                 */
                $table->json(
                    'provider_response'
                )
                    ->nullable();


                $table->unsignedInteger(
                    'verification_attempts'
                )
                    ->default(0);


                $table->dateTime(
                    'auto_verified_at'
                )
                    ->nullable();


                $table->dateTime(
                    'last_verification_attempt_at'
                )
                    ->nullable();
            }
        );
    }


    public function down()
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {

                $table->dropConstrainedForeignId(
                    'seller_withdrawal_account_id'
                );


                $table->dropColumn([
                    'verification_method',
                    'provider',
                    'provider_environment',
                    'provider_status',

                    'identity_first_name',
                    'identity_middle_name',
                    'identity_last_name',
                    'identity_date_of_birth',

                    'liveness_passed',
                    'liveness_probability',

                    'face_match',
                    'face_confidence',

                    'name_match',
                    'dob_match',
                    'bank_name_match',

                    'failure_code',
                    'failure_message',

                    'provider_response',
                    'verification_attempts',

                    'auto_verified_at',
                    'last_verification_attempt_at',
                ]);
            }
        );
    }
};