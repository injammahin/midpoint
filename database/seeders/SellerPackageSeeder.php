<?php

namespace Database\Seeders;

use App\Models\SellerPackage;
use Illuminate\Database\Seeder;

class SellerPackageSeeder extends Seeder
{
    public function run()
    {
        SellerPackage::updateOrCreate(
            [
                'slug' =>
                    'starter',
            ],
            [
                'name' =>
                    'Starter',

                'price' =>
                    5000,

                'billing_period' =>
                    'month',

                'product_limit' =>
                    4,

                'description' =>
                    'For new sellers testing the waters',

                'features' => [
                    'Verified badge & Featured listing',
                    'Trust score on your profile',
                    'Buyer reviews',
                ],

                'theme' =>
                    'slate',

                'is_popular' =>
                    false,

                'is_active' =>
                    true,

                'sort_order' =>
                    1,
            ]
        );


        SellerPackage::updateOrCreate(
            [
                'slug' =>
                    'standard',
            ],
            [
                'name' =>
                    'Standard',

                'price' =>
                    10000,

                'billing_period' =>
                    'month',

                'product_limit' =>
                    10,

                'description' =>
                    'For growing businesses',

                'features' => [
                    'Everything in Starter',
                    'Priority placement in Featured Businesses',
                    'Faster support response',
                ],

                'theme' =>
                    'green',

                'is_popular' =>
                    true,

                'is_active' =>
                    true,

                'sort_order' =>
                    2,
            ]
        );


        SellerPackage::updateOrCreate(
            [
                'slug' =>
                    'premium',
            ],
            [
                'name' =>
                    'Premium',

                'price' =>
                    25000,

                'billing_period' =>
                    'month',

                'product_limit' =>
                    30,

                'description' =>
                    'For high-volume sellers',

                'features' => [
                    'Everything in Standard',
                    'Homepage spotlight rotation',
                    'Dedicated account manager',
                ],

                'theme' =>
                    'purple',

                'is_popular' =>
                    false,

                'is_active' =>
                    true,

                'sort_order' =>
                    3,
            ]
        );
    }
}