<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SellerPackageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Package Management
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $packages =
            SellerPackage::query()
                ->ordered()
                ->get();


        return view(
            'admin.website-settings.become-seller',
            compact(
                'packages'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Package
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $validated =
            $this->validatePackage(
                $request
            );


        SellerPackage::create(
            $this->prepareData(
                $request,
                $validated
            )
        );


        return back()->with(
            'success',
            'Seller package created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Package
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        SellerPackage $sellerPackage
    ) {
        $validated =
            $this->validatePackage(
                $request,
                $sellerPackage->id
            );


        $sellerPackage->update(
            $this->prepareData(
                $request,
                $validated
            )
        );


        return back()->with(
            'success',
            'Seller package updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Package
    |--------------------------------------------------------------------------
    */

    public function toggle(
        SellerPackage $sellerPackage
    ) {
        $sellerPackage->update([
            'is_active' =>
                !$sellerPackage->is_active,
        ]);


        return back()->with(
            'success',
            'Package status updated.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        SellerPackage $sellerPackage
    ) {
        $sellerPackage->delete();


        return back()->with(
            'success',
            'Seller package deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validatePackage(
        Request $request,
        ?int $ignoreId = null
    ): array {

        return $request->validate([

            'name' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'seller_packages',
                    'name'
                )->ignore(
                    $ignoreId
                ),
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'billing_period' => [
                'required',
                Rule::in([
                    'month',
                    'year',
                ]),
            ],

            'product_limit' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'features' => [
                'nullable',
                'string',
            ],

            'theme' => [
                'required',
                Rule::in([
                    'slate',
                    'green',
                    'purple',
                ]),
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Package
    |--------------------------------------------------------------------------
    */

    private function prepareData(
        Request $request,
        array $validated
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Convert feature textarea into JSON array
        |--------------------------------------------------------------------------
        */

        $features =
            collect(
                preg_split(
                    '/\r\n|\r|\n/',
                    $validated['features']
                    ?? ''
                )
            )
                ->map(
                    fn ($feature) =>
                        trim($feature)
                )
                ->filter()
                ->values()
                ->all();


        return [

            'name' =>
                $validated['name'],

            'slug' =>
                Str::slug(
                    $validated['name']
                ),

            'price' =>
                $validated['price'],

            'billing_period' =>
                $validated[
                    'billing_period'
                ],

            'product_limit' =>
                $validated[
                    'product_limit'
                ],

            'description' =>
                $validated['description']
                ?? null,

            'features' =>
                $features,

            'theme' =>
                $validated['theme'],

            'is_popular' =>
                $request->boolean(
                    'is_popular'
                ),

            'is_active' =>
                $request->has(
                    'is_active'
                )
                    ? $request->boolean(
                        'is_active'
                    )
                    : true,

            'sort_order' =>
                $validated[
                    'sort_order'
                ]
                ?? 0,

        ];
    }
}