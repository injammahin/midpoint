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
        | Seller Applications
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_applications',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->string('reference')
                    ->unique();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('seller_package_id')
                    ->nullable()
                    ->constrained('seller_packages')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Package Snapshot
                |--------------------------------------------------------------------------
                |
                | The package may later be edited by admin.
                | The application must retain what the customer applied for.
                |
                */

                $table->string(
                    'package_name'
                );

                $table->decimal(
                    'package_price',
                    12,
                    2
                );

                $table->string(
                    'billing_period',
                    30
                );

                $table->unsignedInteger(
                    'product_limit'
                );


                /*
                |--------------------------------------------------------------------------
                | Business Information
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'business_name'
                );

                $table->string(
                    'category'
                );

                $table->string(
                    'location'
                );

                $table->string(
                    'phone',
                    50
                );

                $table->string(
                    'cac_or_bvn'
                );

                $table->string(
                    'store_link'
                )->nullable();

                $table->text(
                    'description'
                );


                /*
                |--------------------------------------------------------------------------
                | Workflow Status
                |--------------------------------------------------------------------------
                |
                | submitted
                | revision_required
                | superseded
                | payment_pending
                | active
                |
                */

                $table
                    ->string(
                        'status',
                        40
                    )
                    ->default(
                        'submitted'
                    );

                $table
                    ->text(
                        'revision_note'
                    )
                    ->nullable();

                $table
                    ->foreignId(
                        'reviewed_by'
                    )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->dateTime(
                        'submitted_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'reviewed_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'approved_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'activated_at'
                    )
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'status',
                ]);

                $table->index([
                    'status',
                    'created_at',
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Verification Documents
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_application_documents',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId(
                        'seller_application_id'
                    )
                    ->constrained(
                        'seller_applications'
                    )
                    ->cascadeOnDelete();

                $table
                    ->string(
                        'disk',
                        50
                    )
                    ->default('local');

                $table->string(
                    'path'
                );

                $table->string(
                    'original_name'
                );

                $table->string(
                    'mime_type',
                    150
                )->nullable();

                $table
                    ->unsignedBigInteger(
                        'size'
                    )
                    ->default(0);

                $table->timestamps();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Package Invoices
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'seller_invoices',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->string('invoice_number')
                    ->unique();

                $table
                    ->foreignId(
                        'seller_application_id'
                    )
                    ->unique()
                    ->constrained(
                        'seller_applications'
                    )
                    ->cascadeOnDelete();

                $table
                    ->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->decimal(
                        'amount',
                        12,
                        2
                    );

                $table
                    ->string(
                        'currency',
                        10
                    )
                    ->default('NGN');

                /*
                |--------------------------------------------------------------------------
                | unpaid / paid / cancelled
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'status',
                        30
                    )
                    ->default('unpaid');

                $table
                    ->string(
                        'payment_method',
                        50
                    )
                    ->nullable();

                $table
                    ->string(
                        'payment_reference'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'issued_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'due_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'paid_at'
                    )
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'status',
                ]);
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'seller_invoices'
        );

        Schema::dropIfExists(
            'seller_application_documents'
        );

        Schema::dropIfExists(
            'seller_applications'
        );
    }
};