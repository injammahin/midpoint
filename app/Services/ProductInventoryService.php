<?php

namespace App\Services;

use App\Mail\TransactionStatusUpdateMail;

use App\Models\SecureTransaction;
use App\Models\SellerProduct;
use App\Models\MarketplaceCheckoutIntent;
use App\Models\TransactionNotification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

use RuntimeException;


class ProductInventoryService
{
    public function __construct(
        protected TransactionEmailDeliveryService $emailDelivery
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Reserve Product Before Paystack
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This does NOT decrement seller_products.stock.
    |
    | It only protects inventory while the buyer is completing payment.
    |
    */

    public function reserveForPayment(
        SecureTransaction $transaction
    ): void {

        if (
            !$this->usesListedProductStock(
                $transaction
            )
        ) {
            return;
        }


        DB::transaction(
            function () use (
                $transaction
            ) {

                $lockedTransaction =
                    SecureTransaction::query()

                        ->whereKey(
                            $transaction->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Already Paid / Already Deducted
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedTransaction->payment_status
                    ===
                    SecureTransaction::PAYMENT_PAID

                    ||

                    $lockedTransaction->stock_deducted_at
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Product
                |--------------------------------------------------------------------------
                */

                $product =
                    SellerProduct::query()

                        ->whereKey(
                            $lockedTransaction
                                ->seller_product_id
                        )

                        ->lockForUpdate()

                        ->first();


                if (
                    !$product
                    ||
                    !$product->is_active
                ) {

                    throw ValidationException::withMessages([

                        'product' =>
                            'This product is no longer available for purchase.',

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Release Expired Reservations
                |--------------------------------------------------------------------------
                */

                SecureTransaction::query()

                    ->where(
                        'seller_product_id',
                        $product->id
                    )

                    ->whereNull(
                        'stock_deducted_at'
                    )

                    ->whereNull(
                        'stock_released_at'
                    )

                    ->whereNotNull(
                        'stock_reserved_until'
                    )

                    ->where(
                        'stock_reserved_until',
                        '<=',
                        now()
                    )

                    ->update([

                        'stock_released_at' =>
                            now(),

                    ]);


                $lockedTransaction
                    ->refresh();


                /*
                |--------------------------------------------------------------------------
                | Existing Active Reservation
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedTransaction
                        ->stock_reserved_at

                    &&

                    !$lockedTransaction
                        ->stock_released_at

                    &&

                    $lockedTransaction
                        ->stock_reserved_until

                    &&

                    $lockedTransaction
                        ->stock_reserved_until
                        ->isFuture()
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Already Reserved By Other Buyers
                |--------------------------------------------------------------------------
                */

                $reservedQuantity =
                    (int)
                    SecureTransaction::query()

                        ->where(
                            'seller_product_id',
                            $product->id
                        )

                        ->where(
                            'id',
                            '!=',
                            $lockedTransaction->id
                        )

                        ->whereNull(
                            'stock_deducted_at'
                        )

                        ->whereNull(
                            'stock_released_at'
                        )

                        ->whereNotNull(
                            'stock_reserved_until'
                        )

                        ->where(
                            'stock_reserved_until',
                            '>',
                            now()
                        )

                        ->sum(
                            'quantity'
                        );


                /*
                |--------------------------------------------------------------------------
                | Available Quantity
                |--------------------------------------------------------------------------
                */
$marketplaceReservedQuantity =
    (int)
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
            '>',
            now()
        )

        ->sum(
            'quantity'
        );
                $availableQuantity =
                    max(
                        0,

                        (int)
                        $product->stock

                        -

                        $reservedQuantity

                        -

                        $marketplaceReservedQuantity
                    );

                if (
                    (int)
                    $lockedTransaction->quantity

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
                | Reserve
                |--------------------------------------------------------------------------
                */

                $lockedTransaction->update([

                    'stock_reserved_at' =>
                        now(),

                    'stock_reserved_until' =>
                        now()
                            ->addMinutes(
                                $reservationMinutes
                            ),

                    'stock_released_at' =>
                        null,

                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Release Reservation
    |--------------------------------------------------------------------------
    */

    public function releaseReservation(
        SecureTransaction $transaction
    ): void {

        if (
            !$this->usesListedProductStock(
                $transaction
            )
        ) {
            return;
        }


        DB::transaction(
            function () use (
                $transaction
            ) {

                $lockedTransaction =
                    SecureTransaction::query()

                        ->whereKey(
                            $transaction->id
                        )

                        ->lockForUpdate()

                        ->first();


                if (
                    !$lockedTransaction

                    ||

                    $lockedTransaction
                        ->stock_deducted_at

                    ||

                    $lockedTransaction
                        ->stock_released_at

                    ||

                    !$lockedTransaction
                        ->stock_reserved_at
                ) {
                    return;
                }


                $lockedTransaction->update([

                    'stock_released_at' =>
                        now(),

                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deduct Stock After Successful Payment
    |--------------------------------------------------------------------------
    */

    public function deductForSuccessfulPayment(
        SecureTransaction $transaction
    ): void {

        if (
            !$this->usesListedProductStock(
                $transaction
            )
        ) {
            return;
        }


        DB::transaction(
            function () use (
                $transaction
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Transaction
                |--------------------------------------------------------------------------
                */

                $lockedTransaction =
                    SecureTransaction::query()

                        ->whereKey(
                            $transaction->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Idempotency
                |--------------------------------------------------------------------------
                |
                | Paystack can call callback + webhook multiple times.
                |
                | Stock must NEVER be deducted more than once.
                |
                */

                if (
                    $lockedTransaction
                        ->stock_deducted_at
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Product
                |--------------------------------------------------------------------------
                */

                $product =
                    SellerProduct::query()

                        ->whereKey(
                            $lockedTransaction
                                ->seller_product_id
                        )

                        ->lockForUpdate()

                        ->first();


                if (!$product) {

                    throw new RuntimeException(
                        'The listed product for this transaction no longer exists.'
                    );
                }


                $quantity =
                    (int)
                    $lockedTransaction
                        ->quantity;


                /*
                |--------------------------------------------------------------------------
                | Final Safety Check
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $product->stock

                    <

                    $quantity
                ) {

                    Log::critical(
                        'Successful payment could not deduct product stock.',
                        [

                            'secure_transaction_id' =>
                                $lockedTransaction->id,

                            'seller_product_id' =>
                                $product->id,

                            'required_quantity' =>
                                $quantity,

                            'current_stock' =>
                                (int)
                                $product->stock,

                        ]
                    );


                    throw new RuntimeException(
                        'The product stock changed before payment could be finalized.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | New Stock
                |--------------------------------------------------------------------------
                */

                $newStock =
                    (int)
                    $product->stock

                    -

                    $quantity;


                /*
                |--------------------------------------------------------------------------
                | Update Product
                |--------------------------------------------------------------------------
                */

                $product->update([

                    'stock' =>
                        $newStock,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Mark Transaction
                |--------------------------------------------------------------------------
                */

                $lockedTransaction->update([

                    'stock_deducted_at' =>
                        now(),

                    'stock_released_at' =>
                        now(),

                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notify Seller When Stock Reaches Zero
    |--------------------------------------------------------------------------
    */

    public function notifySellerIfOutOfStock(
        SecureTransaction $transaction
    ): void {

        $transaction->load([

            'product',

            'seller',

        ]);


        $product =
            $transaction
                ->product;


        if (
            !$product

            ||

            (int)
            $product->stock > 0

            ||

            !$transaction->seller
        ) {
            return;
        }


        $product
            ->refresh();


        /*
        |--------------------------------------------------------------------------
        | Already Restocked / Already Notified
        |--------------------------------------------------------------------------
        */

        if (
            (int)
            $product->stock > 0

            ||

            $product
                ->out_of_stock_notified_at
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Event Key
        |--------------------------------------------------------------------------
        */

        $eventKey =
            'transaction:'
            .
            $transaction->id
            .
            ':seller:product-out-of-stock';


        /*
        |--------------------------------------------------------------------------
        | Seller In-App Notification
        |--------------------------------------------------------------------------
        */

        TransactionNotification::firstOrCreate(
            [

                'event_key' =>
                    $eventKey,

            ],
            [

                'user_id' =>
                    $transaction->seller_id,

                'secure_transaction_id' =>
                    $transaction->id,

                'audience' =>
                    'seller',

                'type' =>
                    'inventory',

                'title' =>
                    'Product out of stock',

                'message' =>
                    $product->name
                    .
                    ' is now out of stock. Restock it before buyers can purchase it again.',

                'data' => [

                    'reference' =>
                        $transaction->reference,

                    'public_token' =>
                        $transaction->public_token,

                    'product_id' =>
                        $product->id,

                    'product_name' =>
                        $product->name,

                    'stock' =>
                        0,

                ],

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $emailDelivered =
            true;


        if (
            $transaction
                ->seller
                ->email
        ) {

            $emailDelivered =
                $this
                    ->emailDelivery
                    ->send(

                        $transaction,

                        $eventKey,

                        'seller',

                        $transaction
                            ->seller
                            ->email,

                        'Product out of stock - '
                        .
                        $product->name,

                        new TransactionStatusUpdateMail(

                            $transaction,

                            'Product out of stock',

                            $product->name
                            .
                            ' has reached zero stock after a successful buyer payment. Buyers cannot purchase this item again until you restock it.',

                            'Restock product',

                            route(
                                'seller.products'
                            ),

                            'OUT OF STOCK'

                        )
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Mark Notification Complete
        |--------------------------------------------------------------------------
        |
        | If email failed we leave this NULL so a later verified Paystack event
        | can retry the email without duplicating the in-app notification.
        |
        */

        if (
            $emailDelivered
        ) {

            SellerProduct::query()

                ->whereKey(
                    $product->id
                )

                ->where(
                    'stock',
                    '<=',
                    0
                )

                ->whereNull(
                    'out_of_stock_notified_at'
                )

                ->update([

                    'out_of_stock_notified_at' =>
                        now(),

                ]);
        }
    }
    public function availableStockForProduct(
        SellerProduct $product,
        ?int $currentBuyerId = null
    ): int {

        /*
        |--------------------------------------------------------------------------
        | Expire Old Marketplace Reservations
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

            ->whereNotNull(
                'reserved_until'
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
        | Seller-Created Secure Transaction Reservations
        |--------------------------------------------------------------------------
        |
        | Keep your original secure-transaction reservation protection.
        |
        */

        $secureTransactionReservations =
            (int)
            SecureTransaction::query()

                ->where(
                    'seller_product_id',
                    $product->id
                )

                ->whereNull(
                    'stock_deducted_at'
                )

                ->whereNull(
                    'stock_released_at'
                )

                ->whereNotNull(
                    'stock_reserved_until'
                )

                ->where(
                    'stock_reserved_until',
                    '>',
                    now()
                )

                ->sum(
                    'quantity'
                );


        /*
        |--------------------------------------------------------------------------
        | Marketplace Reservations
        |--------------------------------------------------------------------------
        */

        $marketplaceReservationQuery =
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

                ->whereNotNull(
                    'reserved_until'
                )

                ->where(
                    'reserved_until',
                    '>',
                    now()
                );


        /*
        |--------------------------------------------------------------------------
        | Do NOT Count Current Buyer's Own Reservation
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Stock = 1
        |
        | Buyer A started Paystack but closed it.
        |
        | Buyer A should still see:
        |
        | 1 available
        |
        | But Buyer B should see:
        |
        | 0 available
        |
        */

        if (
            $currentBuyerId
            !==
            null
        ) {

            $marketplaceReservationQuery
                ->where(
                    'buyer_id',
                    '!=',
                    $currentBuyerId
                );
        }


        $marketplaceReservations =
            (int)
            $marketplaceReservationQuery
                ->sum(
                    'quantity'
                );


        /*
        |--------------------------------------------------------------------------
        | Available Quantity
        |--------------------------------------------------------------------------
        */

        return max(

            0,

            (int)
            $product->stock

            -

            $secureTransactionReservations

            -

            $marketplaceReservations

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Uses Product Inventory?
    |--------------------------------------------------------------------------
    */

    protected function usesListedProductStock(
        SecureTransaction $transaction
    ): bool {

        return

            $transaction
                ->transaction_type
            ===
            'listed'

            &&

            !is_null(
                $transaction
                    ->seller_product_id
            );
    }
}