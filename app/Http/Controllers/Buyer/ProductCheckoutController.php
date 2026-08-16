<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;
use App\Models\SellerProduct;

use App\Services\ProductInventoryService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

use Throwable;


class ProductCheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Checkout Page
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        SellerProduct $sellerProduct
    ) {

        $buyer =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Product / Seller
        |--------------------------------------------------------------------------
        */

        $sellerProduct->load([

            'user.sellerBusinessProfile',

            'user.activeSellerSubscription.application',

            'user.activeSellerSubscription.package',

        ]);


        $seller =
            $sellerProduct
                ->user;


        /*
        |--------------------------------------------------------------------------
        | Product Must Still Exist And Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(

            $seller

            &&

            $sellerProduct
                ->is_active,

            404

        );


        /*
        |--------------------------------------------------------------------------
        | Seller Must Still Have Active Package
        |--------------------------------------------------------------------------
        */

        abort_unless(

            $seller
                ->activeSellerSubscription,

            404

        );


        /*
        |--------------------------------------------------------------------------
        | Admin Cannot Buy
        |--------------------------------------------------------------------------
        */

        abort_if(

            $buyer
                ->canAccessAdminPanel(),

            403,

            'Administrator accounts cannot participate as transaction buyers.'

        );


        /*
        |--------------------------------------------------------------------------
        | Seller Cannot Buy Own Product
        |--------------------------------------------------------------------------
        */

        abort_if(

            (int)
            $seller->id

            ===

            (int)
            $buyer->id,

            403,

            'You cannot purchase your own listed product.'

        );


        /*
        |--------------------------------------------------------------------------
        | Business Information
        |--------------------------------------------------------------------------
        */

        $businessName =

            optional(
                optional(
                    $seller
                        ->activeSellerSubscription
                )->application
            )->business_name

            ?:

            $seller->name;


        $businessLocation =

            optional(
                $seller
                    ->sellerBusinessProfile
            )->location

            ?:

            optional(
                optional(
                    $seller
                        ->activeSellerSubscription
                )->application
            )->location;


        return view(
            'frontend.products.checkout',
            compact(
                'sellerProduct',
                'seller',
                'buyer',
                'businessName',
                'businessLocation'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Transaction From Listed Product
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        SellerProduct $sellerProduct,
        ProductInventoryService $inventory
    ) {

        $buyer =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Admin Cannot Buy
        |--------------------------------------------------------------------------
        */

        abort_if(

            $buyer
                ->canAccessAdminPanel(),

            403,

            'Administrator accounts cannot participate as transaction buyers.'

        );


        /*
        |--------------------------------------------------------------------------
        | Checkout Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [

                    'quantity' => [
                        'required',
                        'integer',
                        'min:1',
                        'max:100',
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | REQUIRED
                    |--------------------------------------------------------------------------
                    |
                    | 0 is valid when there is no delivery fee.
                    |
                    */

                    'delivery_fee' => [
                        'required',
                        'numeric',
                        'min:0',
                        'max:999999999.99',
                    ],


                    'delivery_address' => [
                        'required',
                        'string',
                        'max:2000',
                    ],


                    'buyer_phone' => [
                        'required',
                        'string',
                        'max:40',
                    ],

                ],
                [

                    'delivery_fee.required' =>
                        'Delivery price as discussed with seller is required. Enter 0 if there is no delivery charge.',

                    'delivery_address.required' =>
                        'Please enter the delivery address.',

                    'buyer_phone.required' =>
                        'Please enter the rider/delivery phone number.',

                ]
            );


        /*
        |--------------------------------------------------------------------------
        | References
        |--------------------------------------------------------------------------
        */

        $reference =
            SecureTransaction::generateReference();


        $publicToken =
            SecureTransaction::generatePublicToken();


        /*
        |--------------------------------------------------------------------------
        | Copy Product Images
        |--------------------------------------------------------------------------
        |
        | Same idea your seller transaction creation already uses.
        |
        | We snapshot the product image so deleting/changing the seller product
        | later does not change transaction evidence.
        |
        */

        $storedImages =
            $this
                ->copyProductImages(

                    $sellerProduct,

                    $reference

                );


        try {

            /*
            |--------------------------------------------------------------------------
            | Create Existing SecureTransaction
            |--------------------------------------------------------------------------
            */

            $transaction =
                DB::transaction(
                    function () use (

                        $sellerProduct,

                        $buyer,

                        $validated,

                        $reference,

                        $publicToken,

                        $storedImages

                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Lock Product
                        |--------------------------------------------------------------------------
                        */

                        $product =
                            SellerProduct::query()

                                ->with([

                                    'user.activeSellerSubscription',

                                ])

                                ->whereKey(
                                    $sellerProduct->id
                                )

                                ->lockForUpdate()

                                ->firstOrFail();


                        /*
                        |--------------------------------------------------------------------------
                        | Active Product
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$product
                                ->is_active
                        ) {

                            throw ValidationException::withMessages([

                                'product' =>
                                    'This product is currently unavailable.',

                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Active Seller Package
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$product
                                ->user
                                ?->activeSellerSubscription
                        ) {

                            throw ValidationException::withMessages([

                                'product' =>
                                    'This seller is not currently available for marketplace purchases.',

                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Cannot Buy Own Product
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int)
                            $product->user_id

                            ===

                            (int)
                            $buyer->id
                        ) {

                            abort(
                                403,
                                'You cannot purchase your own listed product.'
                            );
                        }


                        $quantity =
                            (int)
                            $validated[
                                'quantity'
                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | Out Of Stock
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int)
                            $product->stock
                            <=
                            0
                        ) {

                            throw ValidationException::withMessages([

                                'quantity' =>
                                    'This product is currently out of stock.',

                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Requested Quantity
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $quantity

                            >

                            (int)
                            $product->stock
                        ) {

                            throw ValidationException::withMessages([

                                'quantity' =>
                                    'Only '
                                    .
                                    number_format(
                                        $product->stock
                                    )
                                    .
                                    ' unit(s) are currently in stock.',

                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | IMPORTANT: Product Price Comes From Database
                        |--------------------------------------------------------------------------
                        |
                        | Never trust a price sent from JavaScript/browser.
                        |
                        */

                        $unitPrice =
                            round(
                                (float)
                                $product->price,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Subtotal
                        |--------------------------------------------------------------------------
                        */

                        $subtotal =
                            round(
                                $unitPrice
                                *
                                $quantity,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Delivery
                        |--------------------------------------------------------------------------
                        */

                        $deliveryFee =
                            round(
                                (float)
                                $validated[
                                    'delivery_fee'
                                ],
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Total
                        |--------------------------------------------------------------------------
                        */

                        $totalAmount =
                            round(
                                $subtotal
                                +
                                $deliveryFee,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Existing Secure Transaction
                        |--------------------------------------------------------------------------
                        */

                        return SecureTransaction::create([

                            'reference' =>
                                $reference,


                            'public_token' =>
                                $publicToken,


                            /*
                            |--------------------------------------------------------------------------
                            | Seller / Buyer
                            |--------------------------------------------------------------------------
                            */

                            'seller_id' =>
                                $product->user_id,


                            /*
                            |--------------------------------------------------------------------------
                            | Buyer Is Already Logged In
                            |--------------------------------------------------------------------------
                            */

                            'buyer_id' =>
                                $buyer->id,


                            /*
                            |--------------------------------------------------------------------------
                            | Existing Product
                            |--------------------------------------------------------------------------
                            */

                            'seller_product_id' =>
                                $product->id,


                            'transaction_type' =>
                                'listed',


                            /*
                            |--------------------------------------------------------------------------
                            | Transaction Source
                            |--------------------------------------------------------------------------
                            |
                            | This transaction was created because a buyer selected a product from
                            | the seller's public/listed products and checked out directly.
                            |
                            */

                            'transaction_source' =>
                                SecureTransaction::SOURCE_MARKETPLACE_CHECKOUT,


                            /*
                            |--------------------------------------------------------------------------
                            | Product Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'title' =>
                                $product->name,


                            'description' =>
                                $product->description,


                            'images' =>
                                $storedImages,


                            /*
                            |--------------------------------------------------------------------------
                            | Quantity
                            |--------------------------------------------------------------------------
                            */

                            'quantity' =>
                                $quantity,


                            /*
                            |--------------------------------------------------------------------------
                            | Amounts
                            |--------------------------------------------------------------------------
                            */

                            'unit_price' =>
                                $unitPrice,


                            'subtotal' =>
                                $subtotal,


                            'delivery_fee' =>
                                $deliveryFee,


                            'total_amount' =>
                                $totalAmount,


                            'currency' =>
                                'NGN',


                            /*
                            |--------------------------------------------------------------------------
                            | Buyer
                            |--------------------------------------------------------------------------
                            */

                            'buyer_email' =>
                                strtolower(
                                    trim(
                                        $buyer->email
                                    )
                                ),


                            'buyer_phone' =>
                                trim(
                                    $validated[
                                        'buyer_phone'
                                    ]
                                ),


                            /*
                            |--------------------------------------------------------------------------
                            | Delivery Address
                            |--------------------------------------------------------------------------
                            |
                            | Your existing transaction architecture already uses
                            | delivery_note, therefore we intentionally reuse it.
                            |
                            */

                            'delivery_note' =>
                                trim(
                                    $validated[
                                        'delivery_address'
                                    ]
                                ),


                            /*
                            |--------------------------------------------------------------------------
                            | Inspection
                            |--------------------------------------------------------------------------
                            */

                            'inspection_hours' =>
                                (int)
                                config(
                                    'secure_transactions.inspection_hours',
                                    8
                                ),


                            /*
                            |--------------------------------------------------------------------------
                            | Initial Status
                            |--------------------------------------------------------------------------
                            */

                            'status' =>
                                SecureTransaction::STATUS_AWAITING_PAYMENT,


                            'payment_status' =>
                                SecureTransaction::PAYMENT_UNPAID,


                            /*
                            |--------------------------------------------------------------------------
                            | Already Claimed
                            |--------------------------------------------------------------------------
                            */

                            'claimed_at' =>
                                now(),


                            /*
                            |--------------------------------------------------------------------------
                            | Link
                            |--------------------------------------------------------------------------
                            */

                            'link_expires_at' =>
                                now()
                                    ->addDays(
                                        (int)
                                        config(
                                            'secure_transactions.link_expiry_days',
                                            7
                                        )
                                    ),

                        ]);
                    }
                );

        } catch (Throwable $exception) {

            foreach (
                $storedImages
                as
                $path
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $path
                );
            }


            throw $exception;
        }


        /*
        |--------------------------------------------------------------------------
        | Reserve Quantity
        |--------------------------------------------------------------------------
        |
        | The real product stock is still NOT changed.
        |
        */

        try {

            $inventory
                ->reserveForPayment(
                    $transaction
                );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Remove Created Unpaid Transaction
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $transaction
                ) {

                    $transaction
                        ->delete();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Remove Snapshot Files
            |--------------------------------------------------------------------------
            */

            foreach (
                $storedImages
                as
                $path
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $path
                );
            }


            throw $exception;
        }


        /*
        |--------------------------------------------------------------------------
        | Continue Into EXISTING Paystack Algorithm
        |--------------------------------------------------------------------------
        */

        return view(
            'frontend.products.redirect-to-payment',
            compact(
                'transaction'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Copy Product Images
    |--------------------------------------------------------------------------
    */

    private function copyProductImages(
        SellerProduct $product,
        string $reference
    ): array {

        $copied =
            [];


        foreach (
            $product->all_images
            as
            $source
        ) {

            if (
                !Storage::disk(
                    'public'
                )->exists(
                    $source
                )
            ) {
                continue;
            }


            $extension =
                pathinfo(
                    $source,
                    PATHINFO_EXTENSION
                );


            if (!$extension) {

                $extension =
                    'jpg';

            }


            $destination =
                'secure-transactions/'
                .
                $reference
                .
                '/'
                .
                Str::uuid()
                .
                '.'
                .
                strtolower(
                    $extension
                );


            if (
                Storage::disk(
                    'public'
                )->copy(
                    $source,
                    $destination
                )
            ) {

                $copied[] =
                    $destination;

            }
        }


        return $copied;
    }
}