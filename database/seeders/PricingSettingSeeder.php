<?php

namespace Database\Seeders;

use App\Models\PricingSetting;
use Illuminate\Database\Seeder;

class PricingSettingSeeder extends Seeder
{
    public function run()
    {
        PricingSetting::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                /*
                |--------------------------------------------------------------------------
                | Page
                |--------------------------------------------------------------------------
                */

                'page_eyebrow' =>
                    'Pricing',

                'page_title' =>
                    'One simple fee. Paid by the seller.',

                'page_subtitle' =>
                    'Buyers never pay Midpoint anything — only the product price agreed with the seller.',


                /*
                |--------------------------------------------------------------------------
                | Currency / Example
                |--------------------------------------------------------------------------
                */

                'currency_symbol' =>
                    '₦',

                'example_product_price' =>
                    20000,


                /*
                |--------------------------------------------------------------------------
                | Seller
                |--------------------------------------------------------------------------
                */

                'seller_badge' =>
                    'Sellers',

                'seller_service_fee_percent' =>
                    5,

                'seller_vat_percent' =>
                    7.5,

                'seller_description' =>
                    'Midpoint Service Fee, deducted from your payout when funds are released. Nigerian VAT of 7.5% applies to the service fee only — never to your product price. No signup fee, no monthly fee, no listing fee.',


                /*
                |--------------------------------------------------------------------------
                | Buyer
                |--------------------------------------------------------------------------
                */

                'buyer_badge' =>
                    'Buyers',

                'buyer_service_fee_percent' =>
                    0,

                'buyer_description' =>
                    'No Midpoint fees, ever. You only pay:',


                /*
                |--------------------------------------------------------------------------
                | Labels
                |--------------------------------------------------------------------------
                */

                'product_price_label' =>
                    'Product price',

                'seller_fee_label' =>
                    'Midpoint Service Fee',

                'vat_label' =>
                    'VAT',

                'total_charges_label' =>
                    'Total charges',

                'seller_receive_label' =>
                    "You'll receive",

                'buyer_fee_label' =>
                    'Midpoint Service Fee',

                'buyer_total_label' =>
                    'Total you pay',


                /*
                |--------------------------------------------------------------------------
                | Delivery
                |--------------------------------------------------------------------------
                */

                'delivery_label' =>
                    'Delivery (arranged & agreed with the seller)',

                'delivery_value' =>
                    'Outside Midpoint',


                /*
                |--------------------------------------------------------------------------
                | Protection
                |--------------------------------------------------------------------------
                */

                'protection_note' =>
                    'Every payment is protected until the buyer accepts the item or the 8-hour inspection window closes.',


                /*
                |--------------------------------------------------------------------------
                | Refund Notice
                |--------------------------------------------------------------------------
                */

                'refund_notice_enabled' =>
                    true,

                'refund_notice_title' =>
                    'One thing to know about refunds.',

                'refund_notice_text' =>
                    'When a buyer funds a transaction, the payment gateway charges a processing fee. This gateway processing fee may be non-refundable if the transaction is later refunded or cancelled.',


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                'updated_by' =>
                    null,
            ]
        );
    }
}