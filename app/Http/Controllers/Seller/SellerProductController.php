<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerProduct;
use App\Models\SellerSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Active Package
        |--------------------------------------------------------------------------
        */

        $subscription =
            SellerSubscription::query()

                ->with(
                    'package'
                )

                ->where(
                    'user_id',
                    $user->id
                )

                ->active()

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Seller Products
        |--------------------------------------------------------------------------
        */

        $products =
            SellerProduct::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->latest()

                ->get();


        $usedProducts =
            $products->count();


        $productLimit =
            $subscription
                ? $subscription
                    ->product_limit
                : 0;


        $remainingProducts =
            max(
                0,
                $productLimit
                -
                $usedProducts
            );


        return view(
            'seller.products.index',
            compact(
                'subscription',
                'products',
                'usedProducts',
                'productLimit',
                'remainingProducts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Product
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

            ]);


        $user =
            $request->user();


        DB::transaction(
            function () use (
                $request,
                $validated,
                $user
            ) {

                /*
                |--------------------------------------------------------------------------
                | LOCK SUBSCRIPTION
                |--------------------------------------------------------------------------
                |
                | This makes the limit safe even if the seller sends multiple
                | product requests almost simultaneously.
                |
                */

                $subscription =
                    SellerSubscription::query()

                        ->where(
                            'user_id',
                            $user->id
                        )

                        ->where(
                            'status',
                            'active'
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


                /*
                |--------------------------------------------------------------------------
                | No Package
                |--------------------------------------------------------------------------
                */

                if (!$subscription) {

                    throw ValidationException::withMessages([
                        'package' =>
                            'You need an active seller package before adding products.',
                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Count Existing Products
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
                | PRODUCT LIMIT ENFORCEMENT
                |--------------------------------------------------------------------------
                */

                if (
                    $usedProducts
                    >=
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
                            $subscription
                                ->product_limit
                            .
                            ' products. Delete a product or upgrade your package.',
                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Upload Image
                |--------------------------------------------------------------------------
                */

                $imagePath =
                    null;


                if (
                    $request->hasFile(
                        'image'
                    )
                ) {

                    $imagePath =
                        $request
                            ->file('image')
                            ->store(
                                'seller-products',
                                'public'
                            );

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
                        .
                        '-'
                        .
                        Str::lower(
                            Str::random(6)
                        ),

                    'price' =>
                        $validated['price'],

                    'description' =>
                        $validated['description']
                        ?? null,

                    'image' =>
                        $imagePath,

                    'is_active' =>
                        true,

                ]);

            }
        );


        return back()->with(
            'success',
            'Product added successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        SellerProduct $sellerProduct
    ) {
        abort_unless(
            $sellerProduct->user_id
            ===
            $request->user()->id,
            403
        );


        $sellerProduct->delete();


        return back()->with(
            'success',
            'Product deleted successfully.'
        );
    }
}