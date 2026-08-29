<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\SecureTransactionInvitationMail;
use App\Models\SecureTransaction;
use App\Models\SellerProduct;
use App\Models\SellerSubscription;
use App\Services\SellerSubscriptionService;
use App\Support\RichTextSanitizer;
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
    */

    public function create(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        $user = $request->user();

        /*
         * Synchronize expired subscriptions.
         *
         * A subscription is not required for custom transactions.
         */
        $subscriptions->expireDueSubscriptionsForUser($user);

        $subscription = SellerSubscription::query()
            ->with([
                'application',
                'package',
            ])
            ->where('user_id', $user->id)
            ->active()
            ->latest('id')
            ->first();

        $canUseListedProducts = $subscription !== null;

        /*
         * Only sellers with an active package can use listed products.
         */
        if ($canUseListedProducts) {
            $products = SellerProduct::query()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->latest('id')
                ->get();
        } else {
            $products = collect();
        }

        $businessName = optional(
            optional($subscription)->application
        )->business_name ?: $user->name;

        /*
         * These rates are passed to the Blade page for the seller
         * earnings breakdown in the transaction summary.
         */
        $serviceFeeRate = max(
            0,
            (float) config(
                'secure_transactions.service_fee_percent',
                5
            )
        );

        $vatRate = max(
            0,
            (float) config(
                'secure_transactions.fee_vat_percent',
                7.5
            )
        );

        return view(
            'seller.transactions.create',
            compact(
                'subscription',
                'canUseListedProducts',
                'products',
                'businessName',
                'serviceFeeRate',
                'vatRate'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Transaction
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        $user = $request->user();

        $subscriptions->expireDueSubscriptionsForUser($user);

        $subscription = SellerSubscription::query()
            ->where('user_id', $user->id)
            ->active()
            ->latest('id')
            ->first();

        $canUseListedProducts = $subscription !== null;


        /*
        |--------------------------------------------------------------------------
        | Request Validation
        |--------------------------------------------------------------------------
        |
        | The raw HTML limit is higher than the visible-text limit because
        | Summernote adds HTML tags around the seller's content.
        |
        */

        $validated = $request->validate([
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
                'max:' . RichTextSanitizer::MAX_HTML_LENGTH,
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
                'max:' . RichTextSanitizer::MAX_DELIVERY_HTML_LENGTH,
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
        ], [
            'description.required' =>
                'Please enter the item description or agreed condition.',

            'description.max' =>
                'The submitted item description contains too much formatted content.',

            'delivery_note.max' =>
                'The submitted delivery arrangement contains too much formatted content.',

            'images.max' =>
                'You may upload a maximum of 4 transaction images.',

            'images.*.image' =>
                'Every uploaded transaction file must be an image.',

            'images.*.mimes' =>
                'Transaction images must be JPG, JPEG, PNG, or WEBP files.',

            'images.*.max' =>
                'Each transaction image may not be larger than 5 MB.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Sanitize Item Description
        |--------------------------------------------------------------------------
        */

        $description = RichTextSanitizer::sanitize(
            $validated['description']
        );

        $descriptionLength = RichTextSanitizer::textLength(
            $description
        );

        /*
         * Summernote can submit HTML such as <p><br></p> even when the editor
         * looks empty. Therefore, visible text must be checked separately.
         */
        if ($descriptionLength === 0) {
            throw ValidationException::withMessages([
                'description' =>
                    'Please enter the item description or agreed condition.',
            ]);
        }

        if (
            $descriptionLength >
            RichTextSanitizer::MAX_TEXT_LENGTH
        ) {
            throw ValidationException::withMessages([
                'description' =>
                    'The item description may not be greater than '
                    . number_format(RichTextSanitizer::MAX_TEXT_LENGTH)
                    . ' characters.',
            ]);
        }

        $validated['description'] = $description;


        /*
        |--------------------------------------------------------------------------
        | Sanitize Delivery Arrangement
        |--------------------------------------------------------------------------
        */

        $deliveryNote = null;

        if (
            isset($validated['delivery_note']) &&
            trim((string) $validated['delivery_note']) !== ''
        ) {
            $deliveryNote = RichTextSanitizer::sanitize(
                $validated['delivery_note']
            );

            $deliveryNoteLength = RichTextSanitizer::textLength(
                $deliveryNote
            );

            if (
                $deliveryNoteLength >
                RichTextSanitizer::MAX_DELIVERY_TEXT_LENGTH
            ) {
                throw ValidationException::withMessages([
                    'delivery_note' =>
                        'The delivery arrangement may not be greater than '
                        . number_format(
                            RichTextSanitizer::MAX_DELIVERY_TEXT_LENGTH
                        )
                        . ' characters.',
                ]);
            }

            /*
             * Treat an editor containing only empty formatting tags as null.
             */
            if ($deliveryNoteLength === 0) {
                $deliveryNote = null;
            }
        }

        $validated['delivery_note'] = $deliveryNote;


        /*
        |--------------------------------------------------------------------------
        | Normalize Buyer Email
        |--------------------------------------------------------------------------
        */

        $buyerEmail = strtolower(
            trim($validated['buyer_email'])
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Cannot Buy From Their Own Account
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(trim((string) $user->email)) ===
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
        */

        if (
            $validated['transaction_type'] === 'listed' &&
            !$canUseListedProducts
        ) {
            throw ValidationException::withMessages([
                'transaction_type' =>
                    'An active seller package is required when using a listed product. You can create a custom transaction without a package.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Find and Validate Listed Product
        |--------------------------------------------------------------------------
        */

        $product = null;

        if ($validated['transaction_type'] === 'listed') {
            if (empty($validated['seller_product_id'])) {
                throw ValidationException::withMessages([
                    'seller_product_id' =>
                        'Please choose one of your listed products.',
                ]);
            }

            $product = SellerProduct::query()
                ->whereKey($validated['seller_product_id'])
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    'seller_product_id' =>
                        'The selected product is not available.',
                ]);
            }

            if (
                (int) $validated['quantity'] >
                (int) $product->stock
            ) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Only '
                        . number_format((int) $product->stock)
                        . ' unit(s) are currently available.',
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Transaction Amounts on the Server
        |--------------------------------------------------------------------------
        */

        $quantity = (int) $validated['quantity'];

        $unitPrice = round(
            (float) $validated['unit_price'],
            2
        );

        $deliveryFee = round(
            (float) ($validated['delivery_fee'] ?? 0),
            2
        );

        $subtotal = round(
            $unitPrice * $quantity,
            2
        );

        $totalAmount = round(
            $subtotal + $deliveryFee,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Generate Transaction Identifiers
        |--------------------------------------------------------------------------
        */

        $reference = SecureTransaction::generateReference();

        $publicToken = SecureTransaction::generatePublicToken();

        $storedImages = [];


        /*
        |--------------------------------------------------------------------------
        | Store Images and Create Transaction
        |--------------------------------------------------------------------------
        */

        try {
            /*
             * If the seller uploads custom images, use those images.
             */
            if ($request->hasFile('images')) {
                $uploadedImages = $request->file('images');

                foreach ($uploadedImages as $file) {
                    /*
                     * Defensive server-side protection in addition to the
                     * Laravel max:4 validation rule.
                     */
                    if (count($storedImages) >= 4) {
                        break;
                    }

                    $storedImages[] = $file->store(
                        'secure-transactions/' . $reference,
                        'public'
                    );
                }
            } elseif ($product) {
                /*
                 * If no new images were uploaded for a listed product,
                 * copy a maximum of four existing product images.
                 */
                $storedImages = $this->copyProductImages(
                    $product,
                    $reference
                );
            }

            $transaction = DB::transaction(
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
                        'reference' => $reference,

                        'public_token' => $publicToken,

                        'seller_id' => $user->id,

                        /*
                         * Buyer is connected after opening the link
                         * and signing in with the assigned email.
                         */
                        'buyer_id' => null,

                        'seller_product_id' => $product?->id,

                        'transaction_type' =>
                            $validated['transaction_type'],

                        'transaction_source' =>
                            SecureTransaction::SOURCE_SELLER_LINK,

                        /*
                         * Product snapshot.
                         */
                        'title' => trim($validated['title']),

                        /*
                         * This is already sanitized Summernote HTML.
                         */
                        'description' =>
                            $validated['description'],

                        'images' => $storedImages,

                        'quantity' => $quantity,

                        'unit_price' => $unitPrice,

                        'subtotal' => $subtotal,

                        'delivery_fee' => $deliveryFee,

                        'total_amount' => $totalAmount,

                        'currency' => 'NGN',

                        'buyer_email' => $buyerEmail,

                        'buyer_phone' =>
                            !empty($validated['buyer_phone'])
                                ? trim($validated['buyer_phone'])
                                : null,

                        /*
                         * This is either sanitized Summernote HTML or null.
                         */
                        'delivery_note' =>
                            $validated['delivery_note'],

                        'inspection_hours' => (int) config(
                            'secure_transactions.inspection_hours',
                            8
                        ),

                        'status' =>
                            SecureTransaction::STATUS_AWAITING_PAYMENT,

                        'payment_status' =>
                            SecureTransaction::PAYMENT_UNPAID,

                        'link_expires_at' => now()->addDays(
                            (int) config(
                                'secure_transactions.link_expiry_days',
                                7
                            )
                        ),
                    ]);
                }
            );
        } catch (Throwable $exception) {
            /*
             * Remove any images uploaded or copied before a database error.
             */
            foreach ($storedImages as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }


        /*
        |--------------------------------------------------------------------------
        | Send Secure Transaction Email to Buyer
        |--------------------------------------------------------------------------
        |
        | An email error must not delete an already-created transaction.
        |
        */

        $emailSent = false;

        try {
            $transaction->loadMissing([
                'seller',
            ]);

            Mail::to(
                $transaction->buyer_email
            )->send(
                new SecureTransactionInvitationMail($transaction)
            );

            $emailSent = true;
        } catch (Throwable $mailException) {
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

            report($mailException);
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect to Generated Transaction Page
        |--------------------------------------------------------------------------
        */

        $redirect = redirect()->route(
            'seller.transactions.generated',
            $transaction
        );

        if ($emailSent) {
            return $redirect->with(
                'success',
                'Secure transaction created successfully. The secure link has also been emailed to '
                . $transaction->buyer_email
                . '.'
            );
        }

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
         * Prevent one seller from viewing another seller's transaction.
         */
        abort_unless(
            (int) $secureTransaction->seller_id ===
            (int) $request->user()->id,
            403
        );

        $secureTransaction->load([
            'product',
            'buyer',
            'seller',
        ]);

        return view(
            'seller.transactions.generated',
            [
                'transaction' => $secureTransaction,
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
        $copied = [];

        foreach ($product->all_images as $source) {
            /*
             * A secure transaction can have a maximum of four images.
             */
            if (count($copied) >= 4) {
                break;
            }

            if (
                !is_string($source) ||
                trim($source) === ''
            ) {
                continue;
            }

            if (
                !Storage::disk('public')->exists($source)
            ) {
                continue;
            }

            $extension = strtolower(
                pathinfo(
                    $source,
                    PATHINFO_EXTENSION
                )
            );

            /*
             * Only copy supported image types.
             */
            if (
                !in_array(
                    $extension,
                    [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                    ],
                    true
                )
            ) {
                continue;
            }

            $destination =
                'secure-transactions/'
                . $reference
                . '/'
                . Str::uuid()
                . '.'
                . $extension;

            if (
                Storage::disk('public')->copy(
                    $source,
                    $destination
                )
            ) {
                $copied[] = $destination;
            }
        }

        return $copied;
    }
}