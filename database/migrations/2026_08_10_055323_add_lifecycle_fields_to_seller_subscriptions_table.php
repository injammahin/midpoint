<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Create Table If It Doesn't Exist
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('seller_subscriptions')) {

            Schema::create(
                'seller_subscriptions',
                function (Blueprint $table) {

                    $table->id();

                    $table->unsignedBigInteger(
                        'user_id'
                    );

                    $table
                        ->unsignedBigInteger(
                            'seller_package_id'
                        )
                        ->nullable();

                    $table
                        ->unsignedBigInteger(
                            'seller_application_id'
                        )
                        ->nullable();

                    $table
                        ->string(
                            'package_name',
                            150
                        );

                    $table
                        ->decimal(
                            'price',
                            15,
                            2
                        )
                        ->default(0);

                    $table
                        ->string(
                            'billing_period',
                            30
                        )
                        ->default('month');

                    $table
                        ->unsignedInteger(
                            'product_limit'
                        )
                        ->default(1);

                    $table
                        ->string(
                            'status',
                            30
                        )
                        ->default('active');

                    $table
                        ->string(
                            'payment_reference',
                            150
                        )
                        ->nullable();

                    $table
                        ->dateTime(
                            'started_at'
                        )
                        ->nullable();

                    $table
                        ->dateTime(
                            'expires_at'
                        )
                        ->nullable();

                    $table->timestamps();

                    $table->index(
                        'user_id'
                    );

                    $table->index(
                        'seller_package_id'
                    );

                    $table->index(
                        'seller_application_id'
                    );

                    $table->index(
                        'status'
                    );

                    $table->index(
                        'expires_at'
                    );
                }
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Table
        |--------------------------------------------------------------------------
        */

        $hasApplicationId =
            Schema::hasColumn(
                'seller_subscriptions',
                'seller_application_id'
            );

        $hasPackageName =
            Schema::hasColumn(
                'seller_subscriptions',
                'package_name'
            );

        $hasPrice =
            Schema::hasColumn(
                'seller_subscriptions',
                'price'
            );

        $hasBillingPeriod =
            Schema::hasColumn(
                'seller_subscriptions',
                'billing_period'
            );

        $hasProductLimit =
            Schema::hasColumn(
                'seller_subscriptions',
                'product_limit'
            );

        $hasStatus =
            Schema::hasColumn(
                'seller_subscriptions',
                'status'
            );

        $hasPaymentReference =
            Schema::hasColumn(
                'seller_subscriptions',
                'payment_reference'
            );

        $hasStartedAt =
            Schema::hasColumn(
                'seller_subscriptions',
                'started_at'
            );

        $hasExpiresAt =
            Schema::hasColumn(
                'seller_subscriptions',
                'expires_at'
            );


        Schema::table(
            'seller_subscriptions',
            function (Blueprint $table) use (
                $hasApplicationId,
                $hasPackageName,
                $hasPrice,
                $hasBillingPeriod,
                $hasProductLimit,
                $hasStatus,
                $hasPaymentReference,
                $hasStartedAt,
                $hasExpiresAt
            ) {

                if (!$hasApplicationId) {

                    $table
                        ->unsignedBigInteger(
                            'seller_application_id'
                        )
                        ->nullable();
                }


                if (!$hasPackageName) {

                    $table
                        ->string(
                            'package_name',
                            150
                        )
                        ->nullable();
                }


                if (!$hasPrice) {

                    $table
                        ->decimal(
                            'price',
                            15,
                            2
                        )
                        ->default(0);
                }


                if (!$hasBillingPeriod) {

                    $table
                        ->string(
                            'billing_period',
                            30
                        )
                        ->default('month');
                }


                if (!$hasProductLimit) {

                    $table
                        ->unsignedInteger(
                            'product_limit'
                        )
                        ->default(1);
                }


                if (!$hasStatus) {

                    $table
                        ->string(
                            'status',
                            30
                        )
                        ->default('active');
                }


                if (!$hasPaymentReference) {

                    $table
                        ->string(
                            'payment_reference',
                            150
                        )
                        ->nullable();
                }


                if (!$hasStartedAt) {

                    $table
                        ->dateTime(
                            'started_at'
                        )
                        ->nullable();
                }


                if (!$hasExpiresAt) {

                    $table
                        ->dateTime(
                            'expires_at'
                        )
                        ->nullable();
                }
            }
        );
    }


    public function down()
    {
        /*
        |--------------------------------------------------------------------------
        | Keep History
        |--------------------------------------------------------------------------
        |
        | Intentionally left empty because this migration may be upgrading
        | an existing seller_subscriptions table.
        |
        */
    }
};