<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    use HasFactory;


    protected $fillable = [

        'page_eyebrow',
        'page_title',
        'page_subtitle',

        'currency_symbol',
        'example_product_price',

        'seller_badge',
        'seller_service_fee_percent',
        'seller_vat_percent',
        'seller_description',

        'buyer_badge',
        'buyer_service_fee_percent',
        'buyer_description',

        'product_price_label',
        'seller_fee_label',
        'vat_label',
        'total_charges_label',
        'seller_receive_label',
        'buyer_fee_label',
        'buyer_total_label',

        'delivery_label',
        'delivery_value',

        'protection_note',

        'refund_notice_enabled',
        'refund_notice_title',
        'refund_notice_text',

        'updated_by',

    ];


    protected $casts = [

        'example_product_price' =>
            'decimal:2',

        'seller_service_fee_percent' =>
            'decimal:3',

        'seller_vat_percent' =>
            'decimal:3',

        'buyer_service_fee_percent' =>
            'decimal:3',

        'refund_notice_enabled' =>
            'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    */

    public static function defaults(): array
    {
        return [

            'page_eyebrow' =>
                'Pricing',

            'page_title' =>
                'One simple fee. Paid by the seller.',

            'page_subtitle' =>
                'Buyers never pay MidPoint anything — only the product price agreed with the seller.',


            'currency_symbol' =>
                '₦',

            'example_product_price' =>
                20000,


            'seller_badge' =>
                'Sellers',

            'seller_service_fee_percent' =>
                5,

            'seller_vat_percent' =>
                7.5,

            'seller_description' =>
                'MidPoint Service Fee, deducted from your payout when funds are released. Nigerian VAT applies to the service fee only — never to your product price. No signup fee, no monthly fee, no listing fee.',


            'buyer_badge' =>
                'Buyers',

            'buyer_service_fee_percent' =>
                0,

            'buyer_description' =>
                'No MidPoint fees, ever. You only pay:',


            'product_price_label' =>
                'Product price',

            'seller_fee_label' =>
                'MidPoint Service Fee',

            'vat_label' =>
                'VAT',

            'total_charges_label' =>
                'Total charges',

            'seller_receive_label' =>
                "You'll receive",

            'buyer_fee_label' =>
                'MidPoint Service Fee',

            'buyer_total_label' =>
                'Total you pay',


            'delivery_label' =>
                'Delivery (arranged & agreed with the seller)',

            'delivery_value' =>
                'Outside MidPoint',


            'protection_note' =>
                'Every payment is protected until the buyer accepts the item or the 8-hour inspection window closes.',


            'refund_notice_enabled' =>
                true,

            'refund_notice_title' =>
                'One thing to know about refunds.',

            'refund_notice_text' =>
                'When a buyer funds a transaction, the payment gateway charges a processing fee. This gateway processing fee may be non-refundable if the transaction is later refunded or cancelled.',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current Pricing
    |--------------------------------------------------------------------------
    |
    | This system currently uses one pricing configuration.
    |
    */

    public static function current(): self
    {
        return static::firstOrCreate(
            [
                'id' => 1,
            ],
            static::defaults()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Calculations
    |--------------------------------------------------------------------------
    */

    public function calculations(): array
    {
        $price =
            (float) $this->example_product_price;


        $sellerFee =
            $price
            * (
                (float) $this->seller_service_fee_percent
                / 100
            );


        $vat =
            $sellerFee
            * (
                (float) $this->seller_vat_percent
                / 100
            );


        $sellerCharges =
            $sellerFee + $vat;


        $sellerReceives =
            max(
                0,
                $price - $sellerCharges
            );


        $buyerFee =
            $price
            * (
                (float) $this->buyer_service_fee_percent
                / 100
            );


        $buyerTotal =
            $price + $buyerFee;


        return [

            'product_price' =>
                $price,

            'seller_fee' =>
                $sellerFee,

            'vat' =>
                $vat,

            'seller_total_charges' =>
                $sellerCharges,

            'seller_receives' =>
                $sellerReceives,

            'buyer_fee' =>
                $buyerFee,

            'buyer_total' =>
                $buyerTotal,

        ];
    }
}