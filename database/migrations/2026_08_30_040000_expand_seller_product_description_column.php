<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Run Migration
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Make Sure Table Exists
        |--------------------------------------------------------------------------
        */

        if (
            !Schema::hasTable(
                'seller_products'
            )
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | MySQL / MariaDB
        |--------------------------------------------------------------------------
        |
        | TEXT:
        | approximately 65 KB.
        |
        | MEDIUMTEXT:
        | approximately 16 MB.
        |
        | Since Summernote adds HTML around the 20,000 visible characters,
        | MEDIUMTEXT is safer.
        |
        */

        if (
            DB::connection()
                ->getDriverName()
            ===
            'mysql'
        ) {
            DB::statement(
                'ALTER TABLE `seller_products`
                 MODIFY `description` MEDIUMTEXT NULL'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PostgreSQL / SQLite
        |--------------------------------------------------------------------------
        |
        | Their TEXT type can already safely hold this amount.
        |
        */
    }

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        if (
            !Schema::hasTable(
                'seller_products'
            )
        ) {
            return;
        }

        if (
            DB::connection()
                ->getDriverName()
            ===
            'mysql'
        ) {
            DB::statement(
                'ALTER TABLE `seller_products`
                 MODIFY `description` TEXT NULL'
            );
        }
    }
};