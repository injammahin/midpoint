<?php

namespace App\Services;

use App\Models\MarketplaceCheckoutIntent;
use App\Models\SecureTransaction;
use App\Models\SecureTransactionPayment;
use App\Models\SellerProduct;
use App\Models\User;

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

use RuntimeException;
use Throwable;


class MarketplaceCheckoutPaymentService
{
    public function __construct(

        protected PaystackService $paystack,

        protected ProductInventoryService $inventory,

        protected TransactionPaymentCommunicationService $communications

    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Start Marketplace Checkout
    |--------------------------------------------------------------------------
    |
    | NO SecureTransaction is created here.
    |
    */

    public function start(
        SellerProduct $sellerProduct,
        User $buyer,
        array $validated
    ): MarketplaceCheckoutIntent {


        /*
        |--------------------------------------------------------------------------
        | Create Temporary Checkout Intent + Reserve Quantity
        |--------------------------------------------------------------------------
        */

        $intent =
            DB::transaction(
                function () use (
                    $sellerProduct,
                    $buyer,
                    $validated
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
                    | Product Available?
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$product->is_active
                    ) {

                        throw ValidationException::withMessages([

                            'product' =>
                                'This product is currently unavailable.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Seller Package Active?
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
                        (int) $product->user_id
                        ===
                        (int) $buyer->id
                    ) {

                        abort(
                            403,
                            'You cannot purchase your own listed product.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Expire Old Reservations
                    |--------------------------------------------------------------------------
                    */

                    MarketplaceCheckoutIntent::query()

                        ->where(
                            'seller_product_id',
                            $product->id
                        )

                        ->whereNull(
                            'secure_transaction_id'
                        )

                        ->whereIn(
                            'payment_status',
                            [

                                MarketplaceCheckoutIntent::STATUS_CREATED,

                                MarketplaceCheckoutIntent::STATUS_INITIALIZED,

                                MarketplaceCheckoutIntent::STATUS_PENDING,

                            ]
                        )

                        ->where(
                            'reserved_until',
                            '<=',
                            now()
                        )

                        ->update([

                            'payment_status' =>
                                MarketplaceCheckoutIntent::STATUS_EXPIRED,

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $quantity =
                        (int)
                        $validated[
                            'quantity'
                        ];


                    /*
                    |--------------------------------------------------------------------------
                    | Real Available Stock
                    |--------------------------------------------------------------------------
                    */

                    $availableQuantity =
                        $this
                            ->inventory
                            ->availableStockForProduct(

                                $product,

                                (int) $buyer->id

                            );


                    if (
                        $availableQuantity
                        <=
                        0
                    ) {

                        throw ValidationException::withMessages([

                            'quantity' =>
                                'This product is currently out of stock.',

                        ]);
                    }


                    if (
                        $quantity
                        >
                        $availableQuantity
                    ) {

                        throw ValidationException::withMessages([

                            'quantity' =>
                                'Only '
                                .
                                number_format(
                                    $availableQuantity
                                )
                                .
                                ' unit(s) are currently available. Please reduce the quantity and try again.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Product Price Comes From Database
                    |--------------------------------------------------------------------------
                    */

                    $unitPrice =
                        round(
                            (float)
                            $product->price,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Product Subtotal
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


                    if (
                        $totalAmount
                        <=
                        0
                    ) {

                        throw ValidationException::withMessages([

                            'amount' =>
                                'The checkout amount is invalid.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Reservation Time
                    |--------------------------------------------------------------------------
                    */

                    $reservationMinutes =
                        max(
                            5,
                            (int)
                            config(
                                'secure_transactions.stock_reservation_minutes',
                                30
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Temporary Marketplace Intent
                    |--------------------------------------------------------------------------
                    |
                    | This IS NOT a SecureTransaction.
                    |
                    */
                    $existingIntent =
                        MarketplaceCheckoutIntent::query()

                            ->where(
                                'seller_product_id',
                                $product->id
                            )

                            ->where(
                                'buyer_id',
                                $buyer->id
                            )

                            ->whereNull(
                                'secure_transaction_id'
                            )

                            ->whereIn(
                                'payment_status',
                                [

                                    MarketplaceCheckoutIntent::STATUS_CREATED,

                                    MarketplaceCheckoutIntent::STATUS_INITIALIZED,

                                    MarketplaceCheckoutIntent::STATUS_PENDING,

                                ]
                            )

                            ->where(
                                'reserved_until',
                                '>',
                                now()
                            )

                            ->latest(
                                'id'
                            )

                            ->first();


                    if (
                        $existingIntent
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Is It The Same Order?
                        |--------------------------------------------------------------------------
                        */

                        $sameCheckout =

                            (int) $existingIntent->quantity
                            ===
                            (int) $quantity

                            &&

                            abs(
                                (float) $existingIntent->unit_price
                                -
                                (float) $unitPrice
                            ) < 0.01

                            &&

                            abs(
                                (float) $existingIntent->delivery_fee
                                -
                                (float) $deliveryFee
                            ) < 0.01

                            &&

                            abs(
                                (float) $existingIntent->total_amount
                                -
                                (float) $totalAmount
                            ) < 0.01

                            &&

                            trim(
                                (string) $existingIntent->delivery_address
                            )
                            ===
                            trim(
                                (string) $validated['delivery_address']
                            )

                            &&

                            trim(
                                (string) $existingIntent->buyer_phone
                            )
                            ===
                            trim(
                                (string) $validated['buyer_phone']
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Reopen Existing Paystack Session
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $sameCheckout

                            &&

                            !empty(
                                $existingIntent->authorization_url
                            )
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | Extend This Buyer's Reservation
                            |--------------------------------------------------------------------------
                            */

                            $reservationMinutes =
                                max(
                                    5,
                                    (int)
                                    config(
                                        'secure_transactions.stock_reservation_minutes',
                                        30
                                    )
                                );


                            $existingIntent->update([

                                'reserved_until' =>
                                    now()
                                        ->addMinutes(
                                            $reservationMinutes
                                        ),

                            ]);


                            return $existingIntent->fresh();
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Buyer Changed Checkout Details
                        |--------------------------------------------------------------------------
                        |
                        | The old unpaid attempt should no longer reserve this product.
                        |
                        */

                        $existingIntent->update([

                            'payment_status' =>
                                MarketplaceCheckoutIntent::STATUS_ABANDONED,


                            'reserved_until' =>
                                now(),

                        ]);
                    }
                    return MarketplaceCheckoutIntent::create([

                        'token' =>
                            MarketplaceCheckoutIntent::generateToken(),


                        /*
                        |--------------------------------------------------------------------------
                        | Parties
                        |--------------------------------------------------------------------------
                        */

                        'seller_product_id' =>
                            $product->id,


                        'seller_id' =>
                            $product->user_id,


                        'buyer_id' =>
                            $buyer->id,


                        /*
                        |--------------------------------------------------------------------------
                        | Buyer Snapshot
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


                        'delivery_address' =>
                            trim(
                                $validated[
                                    'delivery_address'
                                ]
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Product Snapshot
                        |--------------------------------------------------------------------------
                        */

                        'product_name' =>
                            $product->name,


                        'product_description' =>
                            $product->description,


                        'product_images' =>
                            array_values(
                                array_filter(
                                    is_array(
                                        $product->all_images
                                    )
                                        ? $product->all_images
                                        : []
                                )
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Amounts
                        |--------------------------------------------------------------------------
                        */

                        'quantity' =>
                            $quantity,


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
                        | Paystack
                        |--------------------------------------------------------------------------
                        */

                        'paystack_reference' =>
                            MarketplaceCheckoutIntent::generatePaystackReference(
                                $product->id
                            ),


                        'payment_status' =>
                            MarketplaceCheckoutIntent::STATUS_CREATED,


                        /*
                        |--------------------------------------------------------------------------
                        | Temporary Stock Reservation
                        |--------------------------------------------------------------------------
                        */

                        'reserved_until' =>
                            now()
                                ->addMinutes(
                                    $reservationMinutes
                                ),

                    ]);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Amount In Kobo
        |--------------------------------------------------------------------------
        */

        $amountSubunit =
            (int)
            round(
                (
                    (float)
                    $intent->total_amount
                )
                *
                100
            );


        /*
        |--------------------------------------------------------------------------
        | Initialize Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $paystackData =
                $this
                    ->paystack
                    ->initializeTransaction([

                        'email' =>
                            $intent->buyer_email,


                        'amount' =>
                            (string)
                            $amountSubunit,


                        'currency' =>
                            $intent->currency,


                        'reference' =>
                            $intent->paystack_reference,


                        /*
                        |--------------------------------------------------------------------------
                        | IMPORTANT
                        |--------------------------------------------------------------------------
                        |
                        | Marketplace has its own callback.
                        |
                        */

                        'callback_url' =>
                            route(
                                'payments.paystack.marketplace.callback'
                            ),


                        'metadata' =>
                            json_encode(
                                [

                                    'payment_flow' =>
                                        'marketplace_checkout',


                                    'marketplace_checkout_intent_id' =>
                                        $intent->id,


                                    'intent_token' =>
                                        $intent->token,


                                    'seller_product_id' =>
                                        $intent->seller_product_id,


                                    'seller_id' =>
                                        $intent->seller_id,


                                    'buyer_id' =>
                                        $intent->buyer_id,


                                    'buyer_email' =>
                                        $intent->buyer_email,

                                ],
                                JSON_UNESCAPED_SLASHES
                            ),

                    ]);


            /*
            |--------------------------------------------------------------------------
            | Paystack Data
            |--------------------------------------------------------------------------
            */

            $authorizationUrl =
                $paystackData[
                    'authorization_url'
                ]
                ??
                null;


            $accessCode =
                $paystackData[
                    'access_code'
                ]
                ??
                null;


            if (
                !$authorizationUrl
                ||
                !$accessCode
            ) {

                throw new RuntimeException(
                    'Paystack did not return a valid checkout URL.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Save Paystack Initialization
            |--------------------------------------------------------------------------
            */

            $intent->update([

                'access_code' =>
                    $accessCode,


                'authorization_url' =>
                    $authorizationUrl,


                'payment_status' =>
                    MarketplaceCheckoutIntent::STATUS_INITIALIZED,


                'initialized_at' =>
                    now(),

            ]);


            return $intent->fresh();


        } catch (
            Throwable $exception
        ) {


            /*
            |--------------------------------------------------------------------------
            | Initialization Failed
            |--------------------------------------------------------------------------
            |
            | Release temporary reservation.
            |
            */

            $intent->update([

                'payment_status' =>
                    MarketplaceCheckoutIntent::STATUS_FAILED,


                'reserved_until' =>
                    now(),

            ]);


            Log::error(
                'Marketplace Paystack initialization failed.',
                [

                    'marketplace_checkout_intent_id' =>
                        $intent->id,


                    'seller_product_id' =>
                        $intent->seller_product_id,


                    'buyer_id' =>
                        $intent->buyer_id,


                    'reference' =>
                        $intent->paystack_reference,


                    'error' =>
                        $exception->getMessage(),

                ]
            );


            throw $exception;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Process Verified Marketplace Payment
    |--------------------------------------------------------------------------
    |
    | SecureTransaction is created ONLY after Paystack returns SUCCESS.
    |
    */

    public function processVerifiedPayment(
        MarketplaceCheckoutIntent $intent,
        array $data
    ): ?SecureTransaction {


        /*
        |--------------------------------------------------------------------------
        | Validate Gateway Data
        |--------------------------------------------------------------------------
        */

        $this->validateVerifiedData(
            $intent,
            $data
        );


        /*
        |--------------------------------------------------------------------------
        | Paystack Status
        |--------------------------------------------------------------------------
        */

        $status =
            strtolower(
                (string)
                (
                    $data[
                        'status'
                    ]
                    ??
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | NOT Successful
        |--------------------------------------------------------------------------
        */

        if (
            $status
            !==
            'success'
        ) {

            $this->recordNonSuccessfulResult(
                $intent,
                $data,
                $status
            );


            /*
            |--------------------------------------------------------------------------
            | NO SecureTransaction
            |--------------------------------------------------------------------------
            */

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Finalized?
        |--------------------------------------------------------------------------
        */

        $intent->refresh();


        if (
            $intent->secure_transaction_id
        ) {

            return SecureTransaction::query()
                ->find(
                    $intent->secure_transaction_id
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Generate REAL Transaction Details
        |--------------------------------------------------------------------------
        */

        $transactionReference =
            SecureTransaction::generateReference();


        $publicToken =
            SecureTransaction::generatePublicToken();


        /*
        |--------------------------------------------------------------------------
        | Copy Product Images Only After Payment Success
        |--------------------------------------------------------------------------
        */

        $copiedImages =
            $this->copySnapshotImages(

                $intent->product_images
                ?:
                [],

                $transactionReference

            );


        $createdNow =
            false;


        try {

            /*
            |--------------------------------------------------------------------------
            | Finalize Atomically
            |--------------------------------------------------------------------------
            */

            $transaction =
                DB::transaction(
                    function () use (

                        $intent,

                        $data,

                        $transactionReference,

                        $publicToken,

                        $copiedImages,

                        &$createdNow

                    ) {


                        /*
                        |--------------------------------------------------------------------------
                        | Lock Intent
                        |--------------------------------------------------------------------------
                        */

                        $lockedIntent =
                            MarketplaceCheckoutIntent::query()

                                ->whereKey(
                                    $intent->id
                                )

                                ->lockForUpdate()

                                ->firstOrFail();


                        /*
                        |--------------------------------------------------------------------------
                        | Idempotency
                        |--------------------------------------------------------------------------
                        |
                        | Callback + webhook may arrive together.
                        |
                        */

                        if (
                            $lockedIntent
                                ->secure_transaction_id
                        ) {

                            return SecureTransaction::query()
                                ->findOrFail(
                                    $lockedIntent
                                        ->secure_transaction_id
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Lock Seller Product
                        |--------------------------------------------------------------------------
                        */

                        $product =
                            SellerProduct::query()

                                ->whereKey(
                                    $lockedIntent
                                        ->seller_product_id
                                )

                                ->lockForUpdate()

                                ->first();


                        if (!$product) {

                            throw new RuntimeException(
                                'The listed product no longer exists. Payment was successful but the order could not be finalized automatically.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Quantity
                        |--------------------------------------------------------------------------
                        */

                        $quantity =
                            (int)
                            $lockedIntent
                                ->quantity;


                        /*
                        |--------------------------------------------------------------------------
                        | Final Stock Safety
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int)
                            $product->stock

                            <

                            $quantity
                        ) {

                            Log::critical(
                                'Successful marketplace payment has insufficient product stock.',
                                [

                                    'marketplace_checkout_intent_id' =>
                                        $lockedIntent->id,


                                    'seller_product_id' =>
                                        $product->id,


                                    'required_quantity' =>
                                        $quantity,


                                    'current_stock' =>
                                        (int)
                                        $product->stock,


                                    'paystack_reference' =>
                                        $lockedIntent
                                            ->paystack_reference,

                                ]
                            );


                            throw new RuntimeException(
                                'The product stock changed before the successful payment could be finalized.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Paid Amount
                        |--------------------------------------------------------------------------
                        */

                        $amountSubunit =
                            (int)
                            (
                                $data[
                                    'amount'
                                ]
                                ??
                                0
                            );


                        $paidAmount =
                            round(
                                $amountSubunit
                                /
                                100,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Paid At
                        |--------------------------------------------------------------------------
                        */

                        $paidAt =
                            !empty(
                                $data[
                                    'paid_at'
                                ]
                                ??
                                $data[
                                    'paidAt'
                                ]
                                ??
                                null
                            )

                                ?

                                Carbon::parse(
                                    $data[
                                        'paid_at'
                                    ]
                                    ??
                                    $data[
                                        'paidAt'
                                    ]
                                )

                                :

                                now();


                        /*
                        |--------------------------------------------------------------------------
                        | Existing Midpoint Fee Configuration
                        |--------------------------------------------------------------------------
                        */

                        $serviceFeeRate =
                            (float)
                            config(
                                'secure_transactions.service_fee_percent',
                                5
                            );


                        $vatRate =
                            (float)
                            config(
                                'secure_transactions.fee_vat_percent',
                                7.5
                            );


                        if (
                            $serviceFeeRate
                            <
                            0

                            ||

                            $vatRate
                            <
                            0
                        ) {

                            throw new RuntimeException(
                                'Midpoint transaction fee configuration is invalid.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | IMPORTANT
                        |--------------------------------------------------------------------------
                        |
                        | Same algorithm your existing Paystack code uses:
                        |
                        | service fee is calculated from PRODUCT SUBTOTAL.
                        |
                        | Delivery is not included in the service fee calculation.
                        |
                        */

                        $productSubtotal =
                            round(
                                (float)
                                $lockedIntent
                                    ->subtotal,
                                2
                            );


                        $serviceFeeAmount =
                            round(
                                $productSubtotal
                                *
                                (
                                    $serviceFeeRate
                                    /
                                    100
                                ),
                                2
                            );


                        $vatAmount =
                            round(
                                $serviceFeeAmount
                                *
                                (
                                    $vatRate
                                    /
                                    100
                                ),
                                2
                            );


                        $sellerNetAmount =
                            round(
                                $paidAmount
                                -
                                $serviceFeeAmount
                                -
                                $vatAmount,
                                2
                            );


                        if (
                            $sellerNetAmount
                            <
                            0
                        ) {

                            throw new RuntimeException(
                                'Calculated seller payout amount is invalid.'
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Inspection
                        |--------------------------------------------------------------------------
                        */

                        $inspectionHours =
                            (int)
                            config(
                                'secure_transactions.inspection_hours',
                                8
                            );


                        $stockTime =
                            now();


                        /*
                        |--------------------------------------------------------------------------
                        | CREATE REAL SECURE TRANSACTION
                        |--------------------------------------------------------------------------
                        |
                        | THIS IS THE FIRST TIME SecureTransaction IS CREATED.
                        |
                        | Paystack has already verified SUCCESS before this.
                        |
                        */

                        $transaction =
                            SecureTransaction::create([

                                /*
                                |--------------------------------------------------------------------------
                                | Transaction
                                |--------------------------------------------------------------------------
                                */

                                'reference' =>
                                    $transactionReference,


                                'public_token' =>
                                    $publicToken,


                                /*
                                |--------------------------------------------------------------------------
                                | Seller / Buyer
                                |--------------------------------------------------------------------------
                                */

                                'seller_id' =>
                                    $lockedIntent
                                        ->seller_id,


                                'buyer_id' =>
                                    $lockedIntent
                                        ->buyer_id,


                                /*
                                |--------------------------------------------------------------------------
                                | Listed Product
                                |--------------------------------------------------------------------------
                                */

                                'seller_product_id' =>
                                    $lockedIntent
                                        ->seller_product_id,


                                'transaction_type' =>
                                    'listed',


                                'transaction_source' =>
                                    'marketplace_checkout',


                                /*
                                |--------------------------------------------------------------------------
                                | Product Snapshot
                                |--------------------------------------------------------------------------
                                */

                                'title' =>
                                    $lockedIntent
                                        ->product_name,


                                'description' =>
                                    $lockedIntent
                                        ->product_description,


                                'images' =>
                                    $copiedImages,


                                /*
                                |--------------------------------------------------------------------------
                                | Quantity / Money
                                |--------------------------------------------------------------------------
                                */

                                'quantity' =>
                                    $lockedIntent
                                        ->quantity,


                                'unit_price' =>
                                    $lockedIntent
                                        ->unit_price,


                                'subtotal' =>
                                    $lockedIntent
                                        ->subtotal,


                                'delivery_fee' =>
                                    $lockedIntent
                                        ->delivery_fee,


                                'total_amount' =>
                                    $lockedIntent
                                        ->total_amount,


                                'currency' =>
                                    $lockedIntent
                                        ->currency,


                                /*
                                |--------------------------------------------------------------------------
                                | Buyer
                                |--------------------------------------------------------------------------
                                */

                                'buyer_email' =>
                                    $lockedIntent
                                        ->buyer_email,


                                'buyer_phone' =>
                                    $lockedIntent
                                        ->buyer_phone,


                                'delivery_note' =>
                                    $lockedIntent
                                        ->delivery_address,


                                /*
                                |--------------------------------------------------------------------------
                                | Lifecycle
                                |--------------------------------------------------------------------------
                                */

                                'inspection_hours' =>
                                    $inspectionHours,


                                /*
                                |--------------------------------------------------------------------------
                                | IMPORTANT
                                |--------------------------------------------------------------------------
                                |
                                | Transaction begins at PAYMENT SECURED.
                                |
                                | It never appears as Awaiting Payment because marketplace
                                | orders are not created until payment succeeds.
                                |
                                */

                                'status' =>
                                    SecureTransaction::STATUS_PAYMENT_SECURED,


                                'payment_status' =>
                                    SecureTransaction::PAYMENT_PAID,


                                /*
                                |--------------------------------------------------------------------------
                                | Paystack
                                |--------------------------------------------------------------------------
                                */

                                'paystack_reference' =>
                                    $lockedIntent
                                        ->paystack_reference,


                                'paystack_transaction_id' =>
                                    isset(
                                        $data[
                                            'id'
                                        ]
                                    )
                                        ? (string)
                                          $data[
                                              'id'
                                          ]
                                        : null,


                                'paid_amount' =>
                                    $paidAmount,


                                'paid_at' =>
                                    $paidAt,


                                /*
                                |--------------------------------------------------------------------------
                                | Midpoint Fee
                                |--------------------------------------------------------------------------
                                */

                                'service_fee_rate' =>
                                    $serviceFeeRate,


                                'vat_rate' =>
                                    $vatRate,


                                'service_fee_amount' =>
                                    $serviceFeeAmount,


                                'vat_amount' =>
                                    $vatAmount,


                                'seller_net_amount' =>
                                    $sellerNetAmount,


                                'payout_status' =>
                                    SecureTransaction::PAYOUT_LOCKED,


                                /*
                                |--------------------------------------------------------------------------
                                | Buyer Already Owns This Transaction
                                |--------------------------------------------------------------------------
                                */

                                'claimed_at' =>
                                    now(),


                                'link_expires_at' =>
                                    now()
                                        ->addDays(
                                            (int)
                                            config(
                                                'secure_transactions.link_expiry_days',
                                                7
                                            )
                                        ),


                                /*
                                |--------------------------------------------------------------------------
                                | Inventory
                                |--------------------------------------------------------------------------
                                */

                                'stock_reserved_at' =>
                                    $lockedIntent
                                        ->created_at,


                                'stock_reserved_until' =>
                                    $lockedIntent
                                        ->reserved_until,


                                /*
                                |--------------------------------------------------------------------------
                                | Successful Payment = Stock Deducted
                                |--------------------------------------------------------------------------
                                */

                                'stock_released_at' =>
                                    $stockTime,


                                'stock_deducted_at' =>
                                    $stockTime,

                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Deduct Actual Seller Product Stock
                        |--------------------------------------------------------------------------
                        |
                        | IMPORTANT:
                        |
                        | This is the first time seller_products.stock is decreased.
                        |
                        */

                        $product->update([

                            'stock' =>

                                (int)
                                $product->stock

                                -

                                $quantity,

                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Create NORMAL Successful SecureTransactionPayment
                        |--------------------------------------------------------------------------
                        |
                        | This lets the rest of your application continue using the
                        | existing payment relationships without any changes.
                        |
                        */

                        SecureTransactionPayment::create([

                            'secure_transaction_id' =>
                                $transaction->id,


                            'buyer_id' =>
                                $lockedIntent
                                    ->buyer_id,


                            'provider' =>
                                'paystack',


                            'reference' =>
                                $lockedIntent
                                    ->paystack_reference,


                            'access_code' =>
                                $lockedIntent
                                    ->access_code,


                            'authorization_url' =>
                                $lockedIntent
                                    ->authorization_url,


                            'amount' =>
                                $lockedIntent
                                    ->total_amount,


                            'amount_subunit' =>
                                $amountSubunit,


                            'currency' =>
                                $lockedIntent
                                    ->currency,


                            'status' =>
                                SecureTransactionPayment::STATUS_SUCCESS,


                            'paystack_transaction_id' =>
                                isset(
                                    $data[
                                        'id'
                                    ]
                                )
                                    ? (string)
                                      $data[
                                          'id'
                                      ]
                                    : null,


                            'channel' =>
                                $data[
                                    'channel'
                                ]
                                ??
                                null,


                            'gateway_response' =>
                                $data[
                                    'gateway_response'
                                ]
                                ??
                                null,


                            'initialized_at' =>
                                $lockedIntent
                                    ->initialized_at,


                            'verified_at' =>
                                now(),


                            'paid_at' =>
                                $paidAt,

                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Finish Temporary Intent
                        |--------------------------------------------------------------------------
                        */

                        $lockedIntent->update([

                            'payment_status' =>
                                MarketplaceCheckoutIntent::STATUS_SUCCESS,


                            'paystack_transaction_id' =>
                                isset(
                                    $data[
                                        'id'
                                    ]
                                )
                                    ? (string)
                                      $data[
                                          'id'
                                      ]
                                    : null,


                            'channel' =>
                                $data[
                                    'channel'
                                ]
                                ??
                                null,


                            'gateway_response' =>
                                $data[
                                    'gateway_response'
                                ]
                                ??
                                null,


                            'verified_at' =>
                                now(),


                            'paid_at' =>
                                $paidAt,


                            'finalized_at' =>
                                now(),


                            'secure_transaction_id' =>
                                $transaction->id,


                            /*
                            |--------------------------------------------------------------------------
                            | Reservation Is Finished
                            |--------------------------------------------------------------------------
                            */

                            'reserved_until' =>
                                now(),

                        ]);


                        $createdNow =
                            true;


                        return $transaction;
                    }
                );


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | DB Failed
            |--------------------------------------------------------------------------
            |
            | Remove copied transaction images.
            |
            */

            $this->deleteCopiedImages(
                $copiedImages
            );


            throw $exception;
        }


        /*
        |--------------------------------------------------------------------------
        | Callback / Webhook Race
        |--------------------------------------------------------------------------
        |
        | Another request finalized first.
        |
        */

        if (
            !$createdNow
        ) {

            $this->deleteCopiedImages(
                $copiedImages
            );


            return $transaction
                ->fresh();
        }


        /*
        |--------------------------------------------------------------------------
        | Send Existing Transaction Communications
        |--------------------------------------------------------------------------
        |
        | Your marketplace seller email logic will now run because:
        |
        | transaction_source = marketplace_checkout
        |
        */

        $freshTransaction =
            $transaction
                ->fresh([

                    'seller',

                    'buyer',

                    'product',

                ]);


        $this
            ->communications
            ->handle(
                $freshTransaction
            );


        /*
        |--------------------------------------------------------------------------
        | Out Of Stock Seller Notification
        |--------------------------------------------------------------------------
        */

        $this
            ->inventory
            ->notifySellerIfOutOfStock(
                $freshTransaction
            );


        return $freshTransaction;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Paystack Data
    |--------------------------------------------------------------------------
    */

    private function validateVerifiedData(
        MarketplaceCheckoutIntent $intent,
        array $data
    ): void {


        $reference =
            (string)
            (
                $data[
                    'reference'
                ]
                ??
                ''
            );


        $amountSubunit =
            (int)
            (
                $data[
                    'amount'
                ]
                ??
                0
            );


        $currency =
            strtoupper(
                (string)
                (
                    $data[
                        'currency'
                    ]
                    ??
                    ''
                )
            );


        $paystackEmail =
            strtolower(
                trim(
                    (string)
                    (
                        $data[
                            'customer'
                        ][
                            'email'
                        ]
                        ??
                        ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        if (
            $reference
            !==
            $intent->paystack_reference
        ) {

            throw new RuntimeException(
                'Paystack reference does not match the marketplace checkout reference.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $expectedAmountSubunit =
            (int)
            round(
                (
                    (float)
                    $intent->total_amount
                )
                *
                100
            );


        if (
            $amountSubunit
            !==
            $expectedAmountSubunit
        ) {

            throw new RuntimeException(
                'Paystack amount does not match the marketplace checkout amount.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        if (
            $currency
            !==
            strtoupper(
                $intent->currency
            )
        ) {

            throw new RuntimeException(
                'Paystack currency does not match the marketplace checkout currency.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer Email
        |--------------------------------------------------------------------------
        */

        if (

            $paystackEmail
            !==
            ''

            &&

            $paystackEmail
            !==
            strtolower(
                trim(
                    $intent
                        ->buyer_email
                )
            )

        ) {

            throw new RuntimeException(
                'Paystack customer email does not match the marketplace buyer email.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Non-Successful Payment
    |--------------------------------------------------------------------------
    |
    | Most important rule:
    |
    | NO SecureTransaction.
    |
    */

    private function recordNonSuccessfulResult(
        MarketplaceCheckoutIntent $intent,
        array $data,
        string $status
    ): void {


        $terminalFailure =
            in_array(
                $status,
                [

                    'failed',

                    'abandoned',

                    'reversed',

                ],
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Intent Status
        |--------------------------------------------------------------------------
        */

        $intentStatus =
            match (
                $status
            ) {

                'abandoned' =>
                    MarketplaceCheckoutIntent::STATUS_ABANDONED,


                'failed',
                'reversed' =>
                    MarketplaceCheckoutIntent::STATUS_FAILED,


                default =>
                    MarketplaceCheckoutIntent::STATUS_PENDING,

            };


        $updates = [

            'payment_status' =>
                $intentStatus,


            'paystack_transaction_id' =>
                isset(
                    $data[
                        'id'
                    ]
                )
                    ? (string)
                      $data[
                          'id'
                      ]
                    : null,


            'channel' =>
                $data[
                    'channel'
                ]
                ??
                null,


            'gateway_response' =>
                $data[
                    'gateway_response'
                ]
                ??
                null,


            'verified_at' =>
                now(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Release Reservation On Permanent Failure
        |--------------------------------------------------------------------------
        */

        if (
            $terminalFailure
        ) {

            $updates[
                'reserved_until'
            ] =
                now();
        }


        MarketplaceCheckoutIntent::query()

            ->whereKey(
                $intent->id
            )

            ->whereNull(
                'secure_transaction_id'
            )

            ->update(
                $updates
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Copy Images After Successful Payment
    |--------------------------------------------------------------------------
    */

    private function copySnapshotImages(
        array $sourceImages,
        string $transactionReference
    ): array {

        $copied =
            [];


        foreach (
            $sourceImages
            as
            $source
        ) {

            $source =
                trim(
                    (string)
                    $source
                );


            if (
                $source
                ===
                ''
            ) {

                continue;
            }


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
                )
                ?:
                'jpg';


            $destination =

                'secure-transactions/'

                .

                $transactionReference

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


    /*
    |--------------------------------------------------------------------------
    | Delete Failed Copies
    |--------------------------------------------------------------------------
    */

    private function deleteCopiedImages(
        array $paths
    ): void {

        foreach (
            $paths
            as
            $path
        ) {

            Storage::disk(
                'public'
            )->delete(
                $path
            );
        }
    }
}