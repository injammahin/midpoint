<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(
            'seller_kyc_verifications',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('seller_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string(
                    'legal_name',
                    180
                );

                $table->date(
                    'date_of_birth'
                );

                $table->char(
                    'country_code',
                    2
                )->default('NG');

                $table->string(
                    'id_type',
                    50
                );

                /*
                 * Full KYC number is encrypted.
                 */
                $table->text(
                    'id_number_encrypted'
                );

                $table->string(
                    'id_number_last4',
                    10
                );

                $table->string(
                    'document_front_path'
                );

                $table->string(
                    'document_back_path'
                )->nullable();

                $table->string(
                    'selfie_path'
                );

                $table->string(
                    'status',
                    30
                )
                    ->default('pending')
                    ->index();

                $table->text(
                    'rejection_reason'
                )->nullable();

                $table->foreignId(
                    'reviewed_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->dateTime(
                    'submitted_at'
                )->nullable();

                $table->dateTime(
                    'reviewed_at'
                )->nullable();

                $table->dateTime(
                    'approved_at'
                )->nullable();

                $table->dateTime(
                    'rejected_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists(
            'seller_kyc_verifications'
        );
    }
};