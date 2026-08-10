<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Mail\SecureTransactionInvitationMail;

use App\Models\SecureTransaction;
use App\Models\SellerProduct;
use App\Models\SellerSubscription;

use App\Services\SellerSubscriptionService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use Throwable;


class SellerSecureTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Create Transaction Page
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | A seller package is NOT required for a custom transaction.
    |
    | Package is required only when using a Listed Product.
    |
    */

    public function create(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        /*
        |--------------------------------------------------------------------------
        | Logged-in User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Synchronize Expired Subscription
        |--------------------------------------------------------------------------
        |
        | This does NOT block custom transactions.
        |
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Optional Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscription =
            SellerSubscription::query()

                ->with([
                    'application',
                    'package',
                ])

                ->where(
                    'user_id',
                    $user->id
                )

                ->active()

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Can Use Listed Products?
        |--------------------------------------------------------------------------
        */

        $canUseListedProducts =
            !is_null(
                $subscription
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        |
        | Only package holders can create transactions from listed products.
        |
        */

        if ($canUseListedProducts) {

            $products =
                SellerProduct::query()

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->where(
                        'is_active',
                        true
                    )

                    ->where(
                        'stock',
                        '>',
                        0
                    )

                    ->latest('id')

                    ->get();

        } else {

            $products =
                collect();
        }


        /*
        |--------------------------------------------------------------------------
        | Seller / Business Name
        |--------------------------------------------------------------------------
        */

        $businessName =
            optional(
                optional(
                    $subscription
                )->application
            )->business_name

            ?:

            $user->name;


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'seller.transactions.create',
            compact(
                'subscription',
                'canUseListedProducts',
                'products',
                'businessName'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store / Generate Secure Transaction
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Synchronize Expired Package
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Optional Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscription =
            SellerSubscription::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->active()

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Can Use Listed Products?
        |--------------------------------------------------------------------------
        */

        $canUseListedProducts =
            !is_null(
                $subscription
            );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'transaction_type' => [
                    'required',

                    Rule::in([
                        'listed',
                        'custom',
                    ]),
                ],


                'seller_product_id' => [
                    'nullable',
                    'integer',
                ],


                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],


                'description' => [
                    'required',
                    'string',
                    'max:5000',
                ],


                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                ],


                'unit_price' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:999999999.99',
                ],


                'delivery_fee' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999.99',
                ],


                'buyer_email' => [
                    'required',
                    'email',
                    'max:255',
                ],


                'buyer_phone' => [
                    'nullable',
                    'string',
                    'max:40',
                ],


                'delivery_note' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],


                'images' => [
                    'nullable',
                    'array',
                    'max:4',
                ],


                'images.*' => [
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Buyer Email
        |--------------------------------------------------------------------------
        */

        $buyerEmail =
            strtolower(
                trim(
                    $validated['buyer_email']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Cannot Buy From Themself
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                trim(
                    $user->email
                )
            )
            ===
            $buyerEmail
        ) {

            throw ValidationException::withMessages([

                'buyer_email' =>
                    'The buyer email cannot be the same as your seller account email.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Listed Product Requires Active Package
        |--------------------------------------------------------------------------
        |
        | CUSTOM:
        | No package required.
        |
        | LISTED:
        | Package required.
        |
        */

        if (
            $validated['transaction_type'] === 'listed'
            &&
            !$canUseListedProducts
        ) {

            throw ValidationException::withMessages([

                'transaction_type' =>
                    'An active seller package is required only when using a listed product. You can create a custom transaction without a package.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        $product =
            null;


        /*
        |--------------------------------------------------------------------------
        | Listed Product Validation
        |--------------------------------------------------------------------------
        */

        if (
            $validated['transaction_type']
            ===
            'listed'
        ) {

            /*
            |--------------------------------------------------------------------------
            | Product Required
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $validated['seller_product_id']
                )
            ) {

                throw ValidationException::withMessages([

                    'seller_product_id' =>
                        'Please choose one of your listed products.',

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Find Seller Product
            |--------------------------------------------------------------------------
            */

            $product =
                SellerProduct::query()

                    ->whereKey(
                        $validated['seller_product_id']
                    )

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->where(
                        'is_active',
                        true
                    )

                    ->first();


            if (!$product) {

                throw ValidationException::withMessages([

                    'seller_product_id' =>
                        'The selected product is not available.',

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */

            if (
                (int)
                $validated['quantity']
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
                        ' unit(s) are currently available.',

                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        $quantity =
            (int)
            $validated['quantity'];


        /*
        |--------------------------------------------------------------------------
        | Unit Price
        |--------------------------------------------------------------------------
        */

        $unitPrice =
            round(
                (float)
                $validated['unit_price'],
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
                (
                    $validated['delivery_fee']
                    ??
                    0
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Server-side Subtotal
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
        | Server-side Total
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
        | Generate Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            SecureTransaction::generateReference();


        /*
        |--------------------------------------------------------------------------
        | Generate Public Token
        |--------------------------------------------------------------------------
        */

        $publicToken =
            SecureTransaction::generatePublicToken();


        /*
        |--------------------------------------------------------------------------
        | Images
        |--------------------------------------------------------------------------
        */

        $storedImages =
            [];


        try {

            /*
            |--------------------------------------------------------------------------
            | Custom Transaction Images
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'images'
                )
            ) {

                foreach (
                    $request->file(
                        'images'
                    )
                    as
                    $file
                ) {

                    $storedImages[] =
                        $file->store(
                            'secure-transactions/'
                            .
                            $reference,
                            'public'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Listed Product Images
            |--------------------------------------------------------------------------
            */

            elseif ($product) {

                $storedImages =
                    $this
                        ->copyProductImages(
                            $product,
                            $reference
                        );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Transaction
            |--------------------------------------------------------------------------
            */

            $transaction =
                DB::transaction(
                    function () use (
                        $validated,
                        $reference,
                        $publicToken,
                        $user,
                        $product,
                        $storedImages,
                        $buyerEmail,
                        $quantity,
                        $unitPrice,
                        $subtotal,
                        $deliveryFee,
                        $totalAmount
                    ) {

                        return SecureTransaction::create([

                            /*
                            |--------------------------------------------------------------------------
                            | Reference
                            |--------------------------------------------------------------------------
                            */

                            'reference' =>
                                $reference,


                            'public_token' =>
                                $publicToken,


                            /*
                            |--------------------------------------------------------------------------
                            | Users
                            |--------------------------------------------------------------------------
                            */

                            'seller_id' =>
                                $user->id,


                            /*
                            |--------------------------------------------------------------------------
                            | Buyer is connected after login
                            |--------------------------------------------------------------------------
                            */

                            'buyer_id' =>
                                null,


                            /*
                            |--------------------------------------------------------------------------
                            | Optional Listed Product
                            |--------------------------------------------------------------------------
                            */

                            'seller_product_id' =>
                                $product?->id,


                            /*
                            |--------------------------------------------------------------------------
                            | Type
                            |--------------------------------------------------------------------------
                            */

                            'transaction_type' =>
                                $validated['transaction_type'],


                            /*
                            |--------------------------------------------------------------------------
                            | Product Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'title' =>
                                trim(
                                    $validated['title']
                                ),


                            'description' =>
                                trim(
                                    $validated['description']
                                ),


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
                                $buyerEmail,


                            'buyer_phone' =>
                                !empty(
                                    $validated['buyer_phone']
                                )
                                    ? trim(
                                        $validated['buyer_phone']
                                    )
                                    : null,


                            /*
                            |--------------------------------------------------------------------------
                            | Delivery
                            |--------------------------------------------------------------------------
                            */

                            'delivery_note' =>
                                !empty(
                                    $validated['delivery_note']
                                )
                                    ? trim(
                                        $validated['delivery_note']
                                    )
                                    : null,


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
                            | Link Expiration
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

            /*
            |--------------------------------------------------------------------------
            | Delete Transaction Files If DB Save Failed
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
        | Send Email To Buyer
        |--------------------------------------------------------------------------
        |
        | Transaction is already created.
        |
        | Email failure must NOT delete transaction.
        |
        */

        $emailSent =
            false;


        try {

            /*
            |--------------------------------------------------------------------------
            | Load Seller
            |--------------------------------------------------------------------------
            */

            $transaction->loadMissing([
                'seller',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Send Custom Email
            |--------------------------------------------------------------------------
            */

            Mail::to(
                $transaction->buyer_email
            )->send(
                new SecureTransactionInvitationMail(
                    $transaction
                )
            );


            $emailSent =
                true;

        } catch (Throwable $mailException) {

            /*
            |--------------------------------------------------------------------------
            | Record Failure In Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Secure transaction buyer invitation email failed.',
                [

                    'transaction_id' =>
                        $transaction->id,

                    'transaction_reference' =>
                        $transaction->reference,

                    'buyer_email' =>
                        $transaction->buyer_email,

                    'error' =>
                        $mailException->getMessage(),

                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Laravel Exception Log
            |--------------------------------------------------------------------------
            */

            report(
                $mailException
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        $redirect =
            redirect()
                ->route(
                    'seller.transactions.generated',
                    $transaction
                );


        /*
        |--------------------------------------------------------------------------
        | Email Sent
        |--------------------------------------------------------------------------
        */

        if ($emailSent) {

            return $redirect
                ->with(
                    'success',
                    'Secure transaction created successfully. The secure link has also been emailed to '
                    .
                    $transaction->buyer_email
                    .
                    '.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Email Failed
        |--------------------------------------------------------------------------
        */

        return $redirect

            ->with(
                'success',
                'Secure transaction created successfully.'
            )

            ->with(
                'warning',
                'The buyer invitation email could not be delivered. You can still copy and share the secure transaction link manually.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Generated Transaction Page
    |--------------------------------------------------------------------------
    */

    public function generated(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        /*
        |--------------------------------------------------------------------------
        | Seller Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int)
            $secureTransaction->seller_id
            ===
            (int)
            $request->user()->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([
            'product',
            'buyer',
            'seller',
        ]);


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'seller.transactions.generated',
            [

                'transaction' =>
                    $secureTransaction,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Copy Listed Product Images
    |--------------------------------------------------------------------------
    */

    private function copyProductImages(
        SellerProduct $product,
        string $reference
    ): array {

        $copied =
            [];


        /*
        |--------------------------------------------------------------------------
        | Images
        |--------------------------------------------------------------------------
        */

        foreach (
            $product->all_images
            as
            $source
        ) {

            /*
            |--------------------------------------------------------------------------
            | File Exists?
            |--------------------------------------------------------------------------
            */

            if (
                !Storage::disk(
                    'public'
                )->exists(
                    $source
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Extension
            |--------------------------------------------------------------------------
            */

            $extension =
                pathinfo(
                    $source,
                    PATHINFO_EXTENSION
                );


            if (!$extension) {

                $extension =
                    'jpg';
            }


            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Copy Image
            |--------------------------------------------------------------------------
            */

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