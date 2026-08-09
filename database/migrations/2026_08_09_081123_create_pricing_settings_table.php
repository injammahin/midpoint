<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pricing_settings', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Page Content
            |--------------------------------------------------------------------------
            */

            $table->string(
                'page_eyebrow',
                100
            );

            $table->string(
                'page_title',
                255
            );

            $table->text(
                'page_subtitle'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Currency / Example Price
            |--------------------------------------------------------------------------
            */

            $table->string(
                'currency_symbol',
                10
            );

            $table->decimal(
                'example_product_price',
                15,
                2
            )->default(20000);


            /*
            |--------------------------------------------------------------------------
            | Seller Pricing
            |--------------------------------------------------------------------------
            */

            $table->string(
                'seller_badge',
                100
            );

            $table->decimal(
                'seller_service_fee_percent',
                8,
                3
            )->default(5);

            $table->decimal(
                'seller_vat_percent',
                8,
                3
            )->default(7.5);

            $table->text(
                'seller_description'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Buyer Pricing
            |--------------------------------------------------------------------------
            */

            $table->string(
                'buyer_badge',
                100
            );

            $table->decimal(
                'buyer_service_fee_percent',
                8,
                3
            )->default(0);

            $table->text(
                'buyer_description'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Pricing Labels
            |--------------------------------------------------------------------------
            */

            $table->string(
                'product_price_label',
                150
            );

            $table->string(
                'seller_fee_label',
                150
            );

            $table->string(
                'vat_label',
                150
            );

            $table->string(
                'total_charges_label',
                150
            );

            $table->string(
                'seller_receive_label',
                150
            );

            $table->string(
                'buyer_fee_label',
                150
            );

            $table->string(
                'buyer_total_label',
                150
            );


            /*
            |--------------------------------------------------------------------------
            | Delivery
            |--------------------------------------------------------------------------
            */

            $table->string(
                'delivery_label',
                255
            );

            $table->string(
                'delivery_value',
                100
            );


            /*
            |--------------------------------------------------------------------------
            | Protection / Refund Notice
            |--------------------------------------------------------------------------
            */

            $table->text(
                'protection_note'
            )->nullable();

            $table->boolean(
                'refund_notice_enabled'
            )->default(true);

            $table->string(
                'refund_notice_title',
                255
            )->nullable();

            $table->text(
                'refund_notice_text'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'updated_by'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

        });
    }


    public function down()
    {
        Schema::dropIfExists(
            'pricing_settings'
        );
    }
};