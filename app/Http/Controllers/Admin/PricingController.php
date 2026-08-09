<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Pricing Settings
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pricing =
            PricingSetting::current();


        $calculation =
            $pricing->calculations();


        return view(
            'admin.website-settings.pricing',
            compact(
                'pricing',
                'calculation'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Pricing
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ) {

        $validated =
            $request->validate(
                [

                    /*
                    |--------------------------------------------------------------------------
                    | Page
                    |--------------------------------------------------------------------------
                    */

                    'page_eyebrow' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'page_title' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'page_subtitle' => [
                        'required',
                        'string',
                        'max:1000',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Currency / Example
                    |--------------------------------------------------------------------------
                    */

                    'currency_symbol' => [
                        'required',
                        'string',
                        'max:10',
                    ],

                    'example_product_price' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:999999999999',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Seller
                    |--------------------------------------------------------------------------
                    */

                    'seller_badge' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'seller_service_fee_percent' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:100',
                    ],

                    'seller_vat_percent' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:100',
                    ],

                    'seller_description' => [
                        'required',
                        'string',
                        'max:3000',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Buyer
                    |--------------------------------------------------------------------------
                    */

                    'buyer_badge' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'buyer_service_fee_percent' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:100',
                    ],

                    'buyer_description' => [
                        'required',
                        'string',
                        'max:3000',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Labels
                    |--------------------------------------------------------------------------
                    */

                    'product_price_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'seller_fee_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'vat_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'total_charges_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'seller_receive_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'buyer_fee_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],

                    'buyer_total_label' => [
                        'required',
                        'string',
                        'max:150',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Delivery
                    |--------------------------------------------------------------------------
                    */

                    'delivery_label' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'delivery_value' => [
                        'required',
                        'string',
                        'max:100',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Notes
                    |--------------------------------------------------------------------------
                    */

                    'protection_note' => [
                        'required',
                        'string',
                        'max:2000',
                    ],

                    'refund_notice_title' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'refund_notice_text' => [
                        'nullable',
                        'string',
                        'max:5000',
                    ],

                ],
                [

                    'seller_service_fee_percent.required' =>
                        'Please enter the seller service fee.',

                    'seller_vat_percent.required' =>
                        'Please enter the VAT percentage.',

                    'buyer_service_fee_percent.required' =>
                        'Please enter the buyer service fee. Enter 0 if buyers pay no MidPoint fee.',

                ]
            );


        DB::transaction(
            function () use (
                $validated,
                $request
            ) {

                $pricing =
                    PricingSetting::current();


                $pricing->update(
                    array_merge(
                        $validated,
                        [

                            'refund_notice_enabled' =>
                                $request->boolean(
                                    'refund_notice_enabled'
                                ),

                            'updated_by' =>
                                Auth::id(),

                        ]
                    )
                );

            }
        );


        return redirect()
            ->route(
                'admin.website-settings.pricing'
            )
            ->with(
                'success',
                'Pricing settings updated successfully.'
            );
    }
}