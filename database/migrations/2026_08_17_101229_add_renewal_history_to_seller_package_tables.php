<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Seller Invoices
        |--------------------------------------------------------------------------
        |
        | Current schema allows only ONE invoice per seller application.
        |
        | We need:
        |
        | Initial application
        |   -> Invoice #1
        |
        | Renewal
        |   -> Invoice #2
        |
        | Upgrade
        |   -> Invoice #3
        |
        | All can safely reference the same originally-approved application.
        |
        */

        Schema::table(
            'seller_invoices',
            function (Blueprint $table) {

                /*
                 * Add a normal index before removing the old unique index.
                 *
                 * seller_application_id is also used by a foreign key,
                 * so it should remain indexed.
                 */

                $table->index(
                    'seller_application_id',
                    'seller_invoices_application_lookup_index'
                );
            }
        );


        Schema::table(
            'seller_invoices',
            function (Blueprint $table) {

                /*
                 * Original migration created:
                 *
                 * seller_invoices_seller_application_id_unique
                 */

                $table->dropUnique(
                    'seller_invoices_seller_application_id_unique'
                );
            }
        );


        Schema::table(
            'seller_invoices',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Package Snapshot
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'seller_package_id'
                    )
                    ->nullable()
                    ->after(
                        'seller_application_id'
                    )
                    ->constrained(
                        'seller_packages'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Purchase Type
                |--------------------------------------------------------------------------
                |
                | initial
                | renewal
                | upgrade
                | downgrade
                |
                */

                $table
                    ->string(
                        'purchase_type',
                        30
                    )
                    ->default(
                        'initial'
                    )
                    ->after(
                        'seller_package_id'
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Previous Subscription
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId(
                        'renewal_of_subscription_id'
                    )
                    ->nullable()
                    ->after(
                        'purchase_type'
                    )
                    ->constrained(
                        'seller_subscriptions'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Package Snapshot
                |--------------------------------------------------------------------------
                |
                | Very important.
                |
                | The original application may have been Premium.
                | Later the seller may renew with Standard.
                |
                | Therefore invoice package details must NOT come from
                | the old application.
                |
                */

                $table
                    ->string(
                        'package_name',
                        150
                    )
                    ->nullable()
                    ->after(
                        'renewal_of_subscription_id'
                    );


                $table
                    ->string(
                        'billing_period',
                        30
                    )
                    ->nullable()
                    ->after(
                        'package_name'
                    );


                $table
                    ->unsignedInteger(
                        'product_limit'
                    )
                    ->nullable()
                    ->after(
                        'billing_period'
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Initial Invoices
        |--------------------------------------------------------------------------
        */

        DB::statement(
            "
            UPDATE seller_invoices AS invoice
            INNER JOIN seller_applications AS application
                ON application.id = invoice.seller_application_id
            SET
                invoice.seller_package_id = application.seller_package_id,
                invoice.package_name = application.package_name,
                invoice.billing_period = application.billing_period,
                invoice.product_limit = application.product_limit,
                invoice.purchase_type = 'initial'
            WHERE invoice.package_name IS NULL
            "
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Subscriptions
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'seller_subscriptions',
            function (Blueprint $table) {

                /*
                 * Which invoice created this subscription?
                 */
                $table
                    ->foreignId(
                        'seller_invoice_id'
                    )
                    ->nullable()
                    ->after(
                        'seller_application_id'
                    )
                    ->constrained(
                        'seller_invoices'
                    )
                    ->nullOnDelete();


                /*
                 * initial / renewal / upgrade / downgrade
                 */
                $table
                    ->string(
                        'purchase_type',
                        30
                    )
                    ->default(
                        'initial'
                    )
                    ->after(
                        'seller_invoice_id'
                    )
                    ->index();


                /*
                 * Subscription this purchase renewed/replaced.
                 */
                $table
                    ->foreignId(
                        'renewed_from_subscription_id'
                    )
                    ->nullable()
                    ->after(
                        'purchase_type'
                    )
                    ->constrained(
                        'seller_subscriptions'
                    )
                    ->nullOnDelete();


                /*
                 * Purchase #1, #2, #3...
                 */
                $table
                    ->unsignedInteger(
                        'renewal_sequence'
                    )
                    ->default(1)
                    ->after(
                        'renewed_from_subscription_id'
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Backfill Original Subscription -> Original Invoice
        |--------------------------------------------------------------------------
        */

        DB::statement(
            "
            UPDATE seller_subscriptions AS subscription
            INNER JOIN seller_invoices AS invoice
                ON invoice.seller_application_id =
                   subscription.seller_application_id
               AND invoice.purchase_type = 'initial'
            SET
                subscription.seller_invoice_id = invoice.id,
                subscription.purchase_type = 'initial',
                subscription.renewal_sequence = 1
            WHERE subscription.seller_invoice_id IS NULL
            "
        );
    }


    public function down()
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Kept
        |--------------------------------------------------------------------------
        |
        | Once renewals exist, restoring UNIQUE(seller_application_id)
        | would destroy/invalidates historical invoice data.
        |
        | This project already uses production compatibility migrations
        | that preserve financial history, so this rollback is deliberately
        | non-destructive.
        |
        */
    }
};