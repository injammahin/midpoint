<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Models\SellerPackage;
use App\Models\SellerProduct;
use App\Models\SellerSubscription;
use App\Models\SecureTransaction;
use App\Services\SellerSubscriptionService;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

use Throwable;


class SellerProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Products Page
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
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
        | Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscription =
            SellerSubscription::query()

                ->with([
                    'package',
                    'application',
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
        | Hard Page Lock
        |--------------------------------------------------------------------------
        */

        if (!$subscription) {

            return $this->lockedRedirect(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            SellerProduct::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->latest('id')

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Package Usage
        |--------------------------------------------------------------------------
        */

        $usedProducts =
            $products->count();


        $productLimit =
            (int)
            $subscription
                ->product_limit;


        $remainingProducts =
            max(
                0,
                $productLimit
                -
                $usedProducts
            );


        $usagePercentage =
            $productLimit > 0

                ? min(
                    100,
                    round(
                        (
                            $usedProducts
                            /
                            $productLimit
                        )
                        *
                        100
                    )
                )

                : 0;


        /*
        |--------------------------------------------------------------------------
        | Upgrade Package
        |--------------------------------------------------------------------------
        */

        $upgradePackage =
            SellerPackage::query()

                ->where(
                    'is_active',
                    true
                )

                ->where(
                    'product_limit',
                    '>',
                    $productLimit
                )

                ->orderBy(
                    'product_limit'
                )

                ->orderBy(
                    'price'
                )

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Business Name
        |--------------------------------------------------------------------------
        */

        $businessName =
            optional(
                $subscription
                    ->application
            )->business_name

            ?:
            $user->name;


        return view(
            'seller.products.index',
            compact(
                'subscription',
                'products',
                'usedProducts',
                'productLimit',
                'remainingProducts',
                'usagePercentage',
                'upgradePackage',
                'businessName'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Product
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Ensure Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        $subscription =
            $this->getActiveSubscription(
                $user->id
            );


        if (!$subscription) {

            return $this->lockedRedirect(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'required',
                    'string',
                    'max:20000',
                ],

                'price' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:999999999.99',
                ],

                'stock' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
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
        | Clean Summernote HTML
        |--------------------------------------------------------------------------
        */

        $description =
            $this->sanitizeDescription(
                $validated[
                    'description'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Require Meaningful Description
        |--------------------------------------------------------------------------
        */

        if (
            trim(
                strip_tags(
                    $description
                )
            )
            ===
            ''
        ) {

            throw ValidationException::withMessages([

                'description' =>
                    'Please enter a product description.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Upload Paths
        |--------------------------------------------------------------------------
        */

        $uploadedPaths =
            [];


        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Files First
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

                    $uploadedPaths[] =
                        $file->store(
                            'seller-products',
                            'public'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Database Transaction
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $validated,
                    $description,
                    $uploadedPaths,
                    $user
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Subscription
                    |--------------------------------------------------------------------------
                    */

                    $subscription =
                        SellerSubscription::query()

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->where(
                                'status',
                                SellerSubscription::STATUS_ACTIVE
                            )

                            ->where(
                                function ($query) {

                                    $query
                                        ->whereNull(
                                            'expires_at'
                                        )

                                        ->orWhere(
                                            'expires_at',
                                            '>',
                                            now()
                                        );
                                }
                            )

                            ->latest('id')

                            ->lockForUpdate()

                            ->first();


                    if (!$subscription) {

                        throw ValidationException::withMessages([

                            'package' =>
                                'Your seller package has expired. Renew your package before adding products.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Count Products
                    |--------------------------------------------------------------------------
                    */

                    $usedProducts =
                        SellerProduct::query()

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->count();


                    /*
                    |--------------------------------------------------------------------------
                    | Product Limit
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $usedProducts
                        >=
                        (int)
                        $subscription
                            ->product_limit
                    ) {

                        throw ValidationException::withMessages([

                            'package' =>
                                'Your '
                                .
                                $subscription
                                    ->package_name
                                .
                                ' package allows only '
                                .
                                number_format(
                                    $subscription
                                        ->product_limit
                                )
                                .
                                ' products. Delete a product or upgrade your package.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create
                    |--------------------------------------------------------------------------
                    */

                    SellerProduct::create([

                        'user_id' =>
                            $user->id,

                        'name' =>
                            $validated[
                                'name'
                            ],

                        'slug' =>
                            Str::slug(
                                $validated[
                                    'name'
                                ]
                            )
                            .
                            '-'
                            .
                            Str::lower(
                                Str::random(7)
                            ),

                        'price' =>
                            $validated[
                                'price'
                            ],

                        'stock' =>
                            $validated[
                                'stock'
                            ],

                        'description' =>
                            $description,

                        /*
                        |--------------------------------------------------------------------------
                        | First Image Compatibility
                        |--------------------------------------------------------------------------
                        */

                        'image' =>
                            $uploadedPaths[0]
                            ??
                            null,

                        /*
                        |--------------------------------------------------------------------------
                        | Multiple Images
                        |--------------------------------------------------------------------------
                        */

                        'images' =>
                            $uploadedPaths,

                        'is_active' =>
                            true,

                    ]);
                }
            );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Remove Uploaded Files If Save Failed
            |--------------------------------------------------------------------------
            */

            foreach (
                $uploadedPaths
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


        return redirect()

            ->route(
                'seller.products'
            )

            ->with(
                'success',
                'Product published successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        SellerProduct $sellerProduct,
        SellerSubscriptionService $subscriptions
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int)
            $sellerProduct
                ->user_id
            ===
            (int)
            $user->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Active Subscription Required
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        $subscription =
            $this->getActiveSubscription(
                $user->id
            );


        if (!$subscription) {

            return $this->lockedRedirect(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'required',
                    'string',
                    'max:20000',
                ],

                'price' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:999999999.99',
                ],

                'stock' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],

                /*
                |--------------------------------------------------------------------------
                | New Images
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Existing Images To Remove
                |--------------------------------------------------------------------------
                */

                'remove_images' => [
                    'nullable',
                    'array',
                ],

                'remove_images.*' => [
                    'string',
                    'max:1000',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Existing Images
        |--------------------------------------------------------------------------
        */

        $existingImages =
            $sellerProduct
                ->all_images;


        /*
        |--------------------------------------------------------------------------
        | Requested Removals
        |--------------------------------------------------------------------------
        */

        $requestedRemovals =
            $validated[
                'remove_images'
            ]
            ??
            [];


        /*
        |--------------------------------------------------------------------------
        | Only Allow Product's Own Paths
        |--------------------------------------------------------------------------
        */

        $safeRemovals =
            array_values(
                array_intersect(
                    $existingImages,
                    $requestedRemovals
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Remaining Old Images
        |--------------------------------------------------------------------------
        */

        $remainingExisting =
            array_values(
                array_diff(
                    $existingImages,
                    $safeRemovals
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Number Of New Images
        |--------------------------------------------------------------------------
        */

        $newImageFiles =
            $request->file(
                'images',
                []
            );


        $newImageCount =
            count(
                $newImageFiles
            );


        /*
        |--------------------------------------------------------------------------
        | Maximum Four Total
        |--------------------------------------------------------------------------
        */

        if (
            count(
                $remainingExisting
            )
            +
            $newImageCount
            >
            4
        ) {

            throw ValidationException::withMessages([

                'images' =>
                    'A product can have a maximum of 4 images. Remove an existing image before adding more.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Sanitize Description
        |--------------------------------------------------------------------------
        */

        $description =
            $this->sanitizeDescription(
                $validated[
                    'description'
                ]
            );


        if (
            trim(
                strip_tags(
                    $description
                )
            )
            ===
            ''
        ) {

            throw ValidationException::withMessages([

                'description' =>
                    'Please enter a product description.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Upload New Images
        |--------------------------------------------------------------------------
        */

        $newPaths =
            [];


        try {

            foreach (
                $newImageFiles
                as
                $file
            ) {

                $newPaths[] =
                    $file->store(
                        'seller-products',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Final Image List
            |--------------------------------------------------------------------------
            */

            $finalImages =
                array_values(
                    array_merge(
                        $remainingExisting,
                        $newPaths
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Update Product
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use (
                    $sellerProduct,
                    $validated,
                    $description,
                    $finalImages,
                    $user
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Product
                    |--------------------------------------------------------------------------
                    |
                    | Prevent stock edits racing with a buyer who is completing payment.
                    |
                    */

                    $lockedProduct =
                        SellerProduct::query()

                            ->whereKey(
                                $sellerProduct->id
                            )

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Release Expired Reservations
                    |--------------------------------------------------------------------------
                    */

                    $this
                        ->releaseExpiredStockReservations(
                            $lockedProduct->id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Active Reserved Quantity
                    |--------------------------------------------------------------------------
                    */

                    $reservedQuantity =
                        $this
                            ->activeReservedQuantity(
                                $lockedProduct->id
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Do Not Reduce Stock Below Buyer Reservations
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $validated['stock']
                        <
                        $reservedQuantity
                    ) {

                        throw ValidationException::withMessages([

                            'stock' =>
                                'You cannot reduce stock below '
                                .
                                number_format(
                                    $reservedQuantity
                                )
                                .
                                ' unit(s) because those units are currently reserved by buyers completing payment.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Product
                    |--------------------------------------------------------------------------
                    */

                    $lockedProduct->update([

                        'name' =>
                            $validated[
                                'name'
                            ],

                        /*
                        |--------------------------------------------------------------------------
                        | Keep Existing Slug Stable
                        |--------------------------------------------------------------------------
                        |
                        | We don't change slug during edit because public URLs may
                        | eventually depend on it.
                        |
                        */

                        'price' =>
                            $validated[
                                'price'
                            ],

                        'stock' =>
                            $validated[
                                'stock'
                            ],

                        /*
                        |--------------------------------------------------------------------------
                        | Allow Future Out-of-Stock Alert After Restock
                        |--------------------------------------------------------------------------
                        */

                        'out_of_stock_notified_at' =>
                            (int) $validated['stock'] > 0

                                ? null

                                : $lockedProduct
                                    ->out_of_stock_notified_at,

                        'description' =>
                            $description,

                        'image' =>
                            $finalImages[0]
                            ??
                            null,

                        'images' =>
                            $finalImages,

                    ]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Delete Removed Old Files
            |--------------------------------------------------------------------------
            |
            | Do this after DB save succeeds.
            |
            */

            foreach (
                $safeRemovals
                as
                $path
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $path
                );
            }

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup New Files If Update Failed
            |--------------------------------------------------------------------------
            */

            foreach (
                $newPaths
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


        return redirect()

            ->route(
                'seller.products'
            )

            ->with(
                'success',
                'Product updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        SellerProduct $sellerProduct,
        SellerSubscriptionService $subscriptions
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int)
            $sellerProduct
                ->user_id
            ===
            (int)
            $user->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Paid Feature
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        if (
            !$this->getActiveSubscription(
                $user->id
            )
        ) {

            return $this->lockedRedirect(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Product Safely
        |--------------------------------------------------------------------------
        |
        | A product cannot be deleted while units are reserved by a buyer who is
        | currently completing payment. Otherwise Paystack could charge the buyer
        | after the product row has already been deleted.
        |
        */

        $images =
            [];


        DB::transaction(
            function () use (
                $sellerProduct,
                $user,
                &$images
            ) {

                $lockedProduct =
                    SellerProduct::query()

                        ->whereKey(
                            $sellerProduct->id
                        )

                        ->where(
                            'user_id',
                            $user->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                $this
                    ->releaseExpiredStockReservations(
                        $lockedProduct->id
                    );


                $reservedQuantity =
                    $this
                        ->activeReservedQuantity(
                            $lockedProduct->id
                        );


                if (
                    $reservedQuantity > 0
                ) {

                    throw ValidationException::withMessages([

                        'product' =>
                            'This product cannot be deleted right now because '
                            .
                            number_format(
                                $reservedQuantity
                            )
                            .
                            ' unit(s) are reserved by buyer(s) completing payment. Please try again after the reservation expires or the payment finishes.',

                    ]);
                }


                $images =
                    $lockedProduct
                        ->all_images;


                $lockedProduct
                    ->delete();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Delete Files
        |--------------------------------------------------------------------------
        */

        foreach (
            $images
            as
            $path
        ) {

            Storage::disk(
                'public'
            )->delete(
                $path
            );
        }


        return redirect()

            ->route(
                'seller.products'
            )

            ->with(
                'success',
                'Product deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Release Expired Product Reservations
    |--------------------------------------------------------------------------
    */

    private function releaseExpiredStockReservations(
        int $productId
    ): void {

        SecureTransaction::query()

            ->where(
                'seller_product_id',
                $productId
            )

            ->where(
                'transaction_type',
                'listed'
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
    }


    /*
    |--------------------------------------------------------------------------
    | Active Reserved Quantity
    |--------------------------------------------------------------------------
    */

    private function activeReservedQuantity(
        int $productId
    ): int {

        return (int)
            SecureTransaction::query()

                ->where(
                    'seller_product_id',
                    $productId
                )

                ->where(
                    'transaction_type',
                    'listed'
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
    }


    /*
    |--------------------------------------------------------------------------
    | Get Active Subscription
    |--------------------------------------------------------------------------
    */

    private function getActiveSubscription(
        int $userId
    ): ?SellerSubscription {

        return SellerSubscription::query()

            ->where(
                'user_id',
                $userId
            )

            ->active()

            ->latest('id')

            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Locked Redirect
    |--------------------------------------------------------------------------
    */

    private function lockedRedirect(
        Request $request
    ): RedirectResponse {

        $latestSubscription =
            SellerSubscription::query()

                ->where(
                    'user_id',
                    $request
                        ->user()
                        ->id
                )

                ->latest('id')

                ->first();


        $expired =
            $latestSubscription
            &&
            (
                $latestSubscription
                    ->status
                ===
                SellerSubscription::STATUS_EXPIRED

                ||

                (
                    $latestSubscription
                        ->expires_at

                    &&

                    $latestSubscription
                        ->expires_at
                        ->lte(now())
                )
            );


        if ($expired) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'Your seller package has expired. Renew a package to unlock Listed Products.'
                );
        }


        return redirect()

            ->route(
                'verified-sellers'
            )

            ->with(
                'error',
                'Purchase a seller package to unlock Listed Products.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Sanitize Summernote HTML
    |--------------------------------------------------------------------------
    |
    | We allow formatting elements but remove unsafe attributes / scripts.
    |
    */

    private function sanitizeDescription(
        string $html
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Remove Dangerous Elements Completely
        |--------------------------------------------------------------------------
        */

        $html =
            preg_replace(
                '#<(script|style|iframe|object|embed|form|input|button)[^>]*>.*?</\1>#is',
                '',
                $html
            );


        /*
        |--------------------------------------------------------------------------
        | Allowed Formatting Tags
        |--------------------------------------------------------------------------
        */

        $html =
            strip_tags(
                $html,
                '<p><br><strong><b><em><i><u><s>'
                .
                '<ul><ol><li>'
                .
                '<blockquote>'
                .
                '<h2><h3><h4><h5>'
                .
                '<a>'
                .
                '<hr>'
                .
                '<table><thead><tbody><tfoot><tr><th><td>'
            );


        /*
        |--------------------------------------------------------------------------
        | Remove Attributes From Opening Tags
        |--------------------------------------------------------------------------
        |
        | Links get a sanitized href.
        |
        */

        $html =
            preg_replace_callback(

                '/<([a-z0-9]+)(\s[^>]*)?>/i',

                function ($matches) {

                    $tag =
                        strtolower(
                            $matches[1]
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Link
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $tag === 'a'
                    ) {

                        $attributes =
                            $matches[2]
                            ??
                            '';


                        $href =
                            null;


                        if (
                            preg_match(
                                '/href\s*=\s*(["\'])(.*?)\1/i',
                                $attributes,
                                $hrefMatch
                            )
                        ) {

                            $candidate =
                                trim(
                                    $hrefMatch[2]
                                );


                            if (
                                preg_match(
                                    '#^(https?://|mailto:)#i',
                                    $candidate
                                )
                            ) {

                                $href =
                                    $candidate;
                            }
                        }


                        if ($href) {

                            return '<a href="'
                                .
                                e($href)
                                .
                                '" target="_blank" rel="noopener noreferrer">';
                        }


                        return '<a>';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Normal Formatting Tag Without Attributes
                    |--------------------------------------------------------------------------
                    */

                    return '<'
                        .
                        $tag
                        .
                        '>';
                },

                $html
            );


        return trim(
            $html
        );
    }
}