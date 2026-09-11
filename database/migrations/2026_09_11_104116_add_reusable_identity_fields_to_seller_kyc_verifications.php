<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {
                /*
                 * HMAC fingerprint of the BVN.
                 * It is not unique because multiple authorised accounts
                 * may use the same identity.
                 */
                $table->char('identity_fingerprint', 64)
                    ->nullable()
                    ->after('id_number_last4');

                $table->unsignedBigInteger('reused_from_kyc_id')
                    ->nullable()
                    ->after('identity_fingerprint');

                $table->dateTime('identity_reused_at')
                    ->nullable()
                    ->after('reused_from_kyc_id');

                $table->index(
                    ['identity_fingerprint', 'status'],
                    'seller_kyc_identity_status_idx'
                );

                $table->foreign(
                    'reused_from_kyc_id',
                    'seller_kyc_reused_from_fk'
                )
                    ->references('id')
                    ->on('seller_kyc_verifications')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'seller_kyc_verifications',
            function (Blueprint $table) {
                $table->dropForeign('seller_kyc_reused_from_fk');

                $table->dropIndex(
                    'seller_kyc_identity_status_idx'
                );

                $table->dropColumn([
                    'identity_fingerprint',
                    'reused_from_kyc_id',
                    'identity_reused_at',
                ]);
            }
        );
    }
};