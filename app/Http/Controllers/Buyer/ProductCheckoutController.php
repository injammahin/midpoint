<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

use App\Models\MarketplaceCheckoutIntent;
use App\Models\SellerProduct;

use App\Services\MarketplaceCheckoutPaymentService;
use App\Services\PaystackService;
use App\Services\ProductInventoryService;

use Illuminate\Http\Request;
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
        SellerProduct $sellerProduct,
        ProductInventoryService $inventory
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
        | Active Product
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
        | Seller Package
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
        | Own Product
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
        | Business Name
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


        /*
        |--------------------------------------------------------------------------
        | Business Location
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | REAL Available Stock
        |--------------------------------------------------------------------------
        |
        | stock itself is only decreased after payment.
        |
        | Therefore temporary active reservations must be deducted here.
        |
        */

        $availableStock =
            $inventory
                ->availableStockForProduct(

                    $sellerProduct,

                    (int) $buyer->id

                );


        return view(
            'frontend.products.checkout',
            compact(
                'sellerProduct',
                'seller',
                'buyer',
                'businessName',
                'businessLocation',
                'availableStock'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Start Marketplace Checkout
    |--------------------------------------------------------------------------
    |
    | NO SecureTransaction is created here.
    |
    */

    public function store(
        Request $request,
        SellerProduct $sellerProduct,
        MarketplaceCheckoutPaymentService $marketplacePayments
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
        | Validate Checkout
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
        | Start Temporary Checkout + Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $intent =
                $marketplacePayments
                    ->start(

                        $sellerProduct,

                        $buyer,

                        $validated

                    );


        } catch (
            ValidationException $exception
        ) {

            throw $exception;


        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return back()

                ->withInput()

                ->with(
                    'error',
                    'We could not open Paystack checkout. No transaction was created. Please try again.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | DIRECT PAYSTACK REDIRECT
        |--------------------------------------------------------------------------
        |
        | This removes your second screenshot:
        |
        | "Opening secure payment"
        | "Continue to secure payment"
        |
        */

        return redirect()
            ->away(
                $intent
                    ->authorization_url
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Marketplace Paystack Callback
    |--------------------------------------------------------------------------
    */

    public function paystackCallback(
        Request $request,
        PaystackService $paystack,
        MarketplaceCheckoutPaymentService $marketplacePayments
    ) {

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            trim(
                (string)
                (
                    $request->query(
                        'reference'
                    )

                    ?:

                    $request->query(
                        'trxref'
                    )
                )
            );


        if (
            $reference
            ===
            ''
        ) {

            return redirect()

                ->route(
                    'home'
                )

                ->with(
                    'error',
                    'Paystack did not return a payment reference. No transaction was created.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Temporary Intent
        |--------------------------------------------------------------------------
        */

        $intent =
            MarketplaceCheckoutIntent::query()

                ->where(
                    'paystack_reference',
                    $reference
                )

                ->first();


        if (!$intent) {

            return redirect()

                ->route(
                    'home'
                )

                ->with(
                    'error',
                    'The returned marketplace payment reference is not recognized.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Finalized?
        |--------------------------------------------------------------------------
        */

        if (
            $intent
                ->secure_transaction_id
        ) {

            $transaction =
                $intent
                    ->secureTransaction()
                    ->first();


            if (
                $transaction
            ) {

                return redirect()

                    ->route(
                        'secure-transactions.show',
                        $transaction
                    )

                    ->with(
                        'success',
                        'Payment already confirmed and your order is secured.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Verify Directly With Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $data =
                $paystack
                    ->verifyTransaction(
                        $reference
                    );


            /*
            |--------------------------------------------------------------------------
            | Create Real Transaction ONLY If Successful
            |--------------------------------------------------------------------------
            */

            $transaction =
                $marketplacePayments
                    ->processVerifiedPayment(

                        $intent,

                        $data

                    );


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            if (
                $transaction
            ) {

                return redirect()

                    ->route(
                        'secure-transactions.show',
                        $transaction
                    )

                    ->with(
                        'success',
                        'Payment successful. Your order has now been created and secured by Midpoint.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Failed / Pending / Abandoned
            |--------------------------------------------------------------------------
            */

            return $this
                ->redirectAfterUnsuccessfulPayment(

                    $request,

                    $intent,

                    'Payment was not successful. No transaction was created.'

                );


        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return $this
                ->redirectAfterUnsuccessfulPayment(

                    $request,

                    $intent,

                    'We could not verify the payment right now. No transaction has been created yet. If you were charged, do not pay again until the payment is verified.'

                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Return Buyer After Failed Payment
    |--------------------------------------------------------------------------
    */

    private function redirectAfterUnsuccessfulPayment(
        Request $request,
        MarketplaceCheckoutIntent $intent,
        string $message
    ) {

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Buyer Still Logged In
        |--------------------------------------------------------------------------
        */

        if (

            $user

            &&

            (int)
            $user->id

            ===

            (int)
            $intent->buyer_id

            &&

            SellerProduct::query()

                ->whereKey(
                    $intent
                        ->seller_product_id
                )

                ->exists()

        ) {

            return redirect()

                ->route(
                    'buyer.products.checkout',
                    $intent
                        ->seller_product_id
                )

                ->with(
                    'error',
                    $message
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'home'
            )

            ->with(
                'error',
                $message
            );
    }
}