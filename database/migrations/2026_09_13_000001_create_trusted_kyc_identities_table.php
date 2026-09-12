<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trusted_kyc_identities', function (Blueprint $table) {
            $table->id();
            $table->string('environment', 8);
            $table->char('bank_fingerprint', 64);
            $table->char('identity_fingerprint', 64);
            $table->longText('payload_encrypted');
            $table->char('payload_digest', 64);
            $table->string('origin', 32);
            $table->timestamp('verified_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['environment', 'bank_fingerprint'], 'trusted_kyc_bank_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_kyc_identities');
    }
};
