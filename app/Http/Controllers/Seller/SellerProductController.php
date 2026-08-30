<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerPackage;
use App\Models\SellerProduct;
use App\Models\SellerSubscription;
use App\Models\SecureTransaction;
use App\Services\SellerSubscriptionService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Synchronize Expired Package
        |--------------------------------------------------------------------------
        */

        $subscriptions->expireDueSubscriptionsForUser($user);

        /*
        |--------------------------------------------------------------------------
        | Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscription = SellerSubscription::query()
            ->with([
                'package',
                'application',
            ])
            ->where('user_id', $user->id)
            ->active()
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Hard Page Lock
        |--------------------------------------------------------------------------
        */

        if (!$subscription) {
            return $this->lockedRedirect($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products = SellerProduct::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Package Usage
        |--------------------------------------------------------------------------
        */

        $usedProducts = $products->count();

        $productLimit = (int) $subscription->product_limit;

        $remainingProducts = max(
            0,
            $productLimit - $usedProducts
        );

        $usagePercentage = $productLimit > 0
            ? min(
                100,
                round(
                    ($usedProducts / $productLimit) * 100
                )
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Upgrade Package
        |--------------------------------------------------------------------------
        */

        $upgradePackage = SellerPackage::query()
            ->where('is_active', true)
            ->where(
                'product_limit',
                '>',
                $productLimit
            )
            ->orderBy('product_limit')
            ->orderBy('price')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Business Name
        |--------------------------------------------------------------------------
        */

        $businessName =
            optional($subscription->application)->business_name
            ?: $user->name;

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
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Ensure Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscriptions->expireDueSubscriptionsForUser($user);

        $subscription = $this->getActiveSubscription(
            $user->id
        );

        if (!$subscription) {
            return $this->lockedRedirect($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Summernote submits HTML.
        |
        | We DO NOT use:
        |
        |     max:20000
        |
        | directly on the Summernote HTML because HTML tags such as:
        |
        | <p>
        | <strong>
        | <ul>
        | <li>
        |
        | would also be counted.
        |
        | Raw Summernote HTML can be up to 120,000 characters.
        | The actual visible description is checked below and cannot
        | exceed 20,000 characters.
        |
        */

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:' . RichTextSanitizer::MAX_HTML_LENGTH,
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

        $description = RichTextSanitizer::sanitize(
            $validated['description']
        );

        /*
        |--------------------------------------------------------------------------
        | Count Actual Visible Characters
        |--------------------------------------------------------------------------
        */

        $descriptionLength =
            RichTextSanitizer::textLength(
                $description
            );

        /*
        |--------------------------------------------------------------------------
        | Empty Description Protection
        |--------------------------------------------------------------------------
        |
        | Summernote can technically submit HTML such as:
        |
        | <p><br></p>
        |
        | which is not a real description.
        |
        */

        if ($descriptionLength === 0) {
            throw ValidationException::withMessages([
                'description' =>
                    'Please enter a product description.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum 20,000 Actual Characters
        |--------------------------------------------------------------------------
        */

        if (
            $descriptionLength
            >
            RichTextSanitizer::MAX_TEXT_LENGTH
        ) {
            throw ValidationException::withMessages([
                'description' =>
                    'The product description may not be greater than '
                    . number_format(
                        RichTextSanitizer::MAX_TEXT_LENGTH
                    )
                    . ' characters.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Uploaded Image Paths
        |--------------------------------------------------------------------------
        */

        $uploadedPaths = [];

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Images
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('images')) {

                foreach (
                    $request->file('images')
                    as $file
                ) {
                    $uploadedPaths[] = $file->store(
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
                    | Lock Active Subscription
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
                                        ->whereNull('expires_at')
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

                    /*
                    |--------------------------------------------------------------------------
                    | Package Expired
                    |--------------------------------------------------------------------------
                    */

                    if (!$subscription) {
                        throw ValidationException::withMessages([
                            'package' =>
                                'Your seller package has expired. Renew your package before adding products.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Current Product Count
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
                        (int) $subscription->product_limit
                    ) {
                        throw ValidationException::withMessages([
                            'package' =>
                                'Your '
                                . $subscription->package_name
                                . ' package allows only '
                                . number_format(
                                    $subscription->product_limit
                                )
                                . ' products. Delete a product or upgrade your package.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create Product
                    |--------------------------------------------------------------------------
                    */

                    SellerProduct::create([

                        'user_id' =>
                            $user->id,

                        'name' =>
                            $validated['name'],

                        'slug' =>
                            Str::slug(
                                $validated['name']
                            )
                            . '-'
                            . Str::lower(
                                Str::random(7)
                            ),

                        'price' =>
                            $validated['price'],

                        'stock' =>
                            $validated['stock'],

                        'description' =>
                            $description,

                        /*
                        |--------------------------------------------------------------------------
                        | First Image Compatibility
                        |--------------------------------------------------------------------------
                        */

                        'image' =>
                            $uploadedPaths[0] ?? null,

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
            | Delete Uploaded Files If Database Save Failed
            |--------------------------------------------------------------------------
            */

            foreach ($uploadedPaths as $path) {
                Storage::disk('public')
                    ->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('seller.products')
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
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Ownership Check
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $sellerProduct->user_id
            ===
            (int) $user->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Active Subscription Required
        |--------------------------------------------------------------------------
        */

        $subscriptions->expireDueSubscriptionsForUser(
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

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            |
            | Summernote HTML is allowed to contain formatting overhead.
            | The actual visible content is limited to 20,000 characters below.
            |
            */

            'description' => [
                'required',
                'string',
                'max:' . RichTextSanitizer::MAX_HTML_LENGTH,
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
            $sellerProduct->all_images;

        /*
        |--------------------------------------------------------------------------
        | Requested Image Removals
        |--------------------------------------------------------------------------
        */

        $requestedRemovals =
            $validated['remove_images'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        |
        | A seller is only allowed to remove image paths that already belong
        | to this product.
        |
        */

        $safeRemovals = array_values(
            array_intersect(
                $existingImages,
                $requestedRemovals
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Existing Images Remaining
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
        | New Images
        |--------------------------------------------------------------------------
        */

        $newImageFiles =
            $request->file(
                'images',
                []
            );

        $newImageCount =
            count($newImageFiles);

        /*
        |--------------------------------------------------------------------------
        | Maximum Four Total Images
        |--------------------------------------------------------------------------
        */

        if (
            count($remainingExisting)
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
        | Sanitize Summernote Description
        |--------------------------------------------------------------------------
        */

        $description =
            RichTextSanitizer::sanitize(
                $validated['description']
            );

        /*
        |--------------------------------------------------------------------------
        | Count Visible Description Characters
        |--------------------------------------------------------------------------
        */

        $descriptionLength =
            RichTextSanitizer::textLength(
                $description
            );

        /*
        |--------------------------------------------------------------------------
        | Empty Description
        |--------------------------------------------------------------------------
        */

        if ($descriptionLength === 0) {
            throw ValidationException::withMessages([
                'description' =>
                    'Please enter a product description.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum 20,000 Actual Characters
        |--------------------------------------------------------------------------
        */

        if (
            $descriptionLength
            >
            RichTextSanitizer::MAX_TEXT_LENGTH
        ) {
            throw ValidationException::withMessages([
                'description' =>
                    'The product description may not be greater than '
                    . number_format(
                        RichTextSanitizer::MAX_TEXT_LENGTH
                    )
                    . ' characters.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | New Uploaded Paths
        |--------------------------------------------------------------------------
        */

        $newPaths = [];

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload New Images
            |--------------------------------------------------------------------------
            */

            foreach ($newImageFiles as $file) {
                $newPaths[] =
                    $file->store(
                        'seller-products',
                        'public'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Build Final Image List
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
                    | Prevent stock editing while a buyer is completing a
                    | transaction for this product.
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
                    | Stock Protection
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
                                . number_format(
                                    $reservedQuantity
                                )
                                . ' unit(s) because those units are currently reserved by buyers completing payment.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update
                    |--------------------------------------------------------------------------
                    */

                    $lockedProduct->update([

                        'name' =>
                            $validated['name'],

                        /*
                        |--------------------------------------------------------------------------
                        | Keep Existing Slug
                        |--------------------------------------------------------------------------
                        |
                        | We don't regenerate the slug during editing because
                        | public URLs may depend on the existing slug.
                        |
                        */

                        'price' =>
                            $validated['price'],

                        'stock' =>
                            $validated['stock'],

                        /*
                        |--------------------------------------------------------------------------
                        | Reset Out-of-Stock Notification After Restock
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
                            $finalImages[0] ?? null,

                        'images' =>
                            $finalImages,
                    ]);
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Delete Removed Old Images
            |--------------------------------------------------------------------------
            |
            | Only delete them after the database update succeeds.
            |
            */

            foreach ($safeRemovals as $path) {
                Storage::disk('public')
                    ->delete($path);
            }

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup New Uploads If Update Fails
            |--------------------------------------------------------------------------
            */

            foreach ($newPaths as $path) {
                Storage::disk('public')
                    ->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('seller.products')
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
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $sellerProduct->user_id
            ===
            (int) $user->id,
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
        | We must not delete a listed product while its stock is temporarily
        | reserved by a buyer completing a payment.
        |
        */

        $images = [];

        DB::transaction(
            function () use (
                $sellerProduct,
                $user,
                &$images
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Product
                |--------------------------------------------------------------------------
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
                | Check Current Reservations
                |--------------------------------------------------------------------------
                */

                $reservedQuantity =
                    $this
                        ->activeReservedQuantity(
                            $lockedProduct->id
                        );

                if ($reservedQuantity > 0) {
                    throw ValidationException::withMessages([
                        'product' =>
                            'This product cannot be deleted right now because '
                            . number_format(
                                $reservedQuantity
                            )
                            . ' unit(s) are reserved by buyer(s) completing payment. Please try again after the reservation expires or the payment finishes.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Get Images Before Deleting Product
                |--------------------------------------------------------------------------
                */

                $images =
                    $lockedProduct->all_images;

                /*
                |--------------------------------------------------------------------------
                | Delete Product
                |--------------------------------------------------------------------------
                */

                $lockedProduct->delete();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Delete Physical Images
        |--------------------------------------------------------------------------
        */

        foreach ($images as $path) {
            Storage::disk('public')
                ->delete($path);
        }

        return redirect()
            ->route('seller.products')
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
                ->sum('quantity');
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
                    $request->user()->id
                )
                ->latest('id')
                ->first();

        $expired =
            $latestSubscription
            &&
            (
                $latestSubscription->status
                ===
                SellerSubscription::STATUS_EXPIRED

                ||

                (
                    $latestSubscription->expires_at
                    &&
                    $latestSubscription
                        ->expires_at
                        ->lte(now())
                )
            );

        if ($expired) {
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'Your seller package has expired. Renew a package to unlock Listed Products.'
                );
        }

        return redirect()
            ->route('verified-sellers')
            ->with(
                'error',
                'Purchase a seller package to unlock Listed Products.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitize Summernote HTML
    |--------------------------------------------------------------------------
    */

    private function sanitizeDescription(
        string $html
    ): string {
        return RichTextSanitizer::sanitize(
            $html
        );
    }
}