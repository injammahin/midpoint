<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ) {

        $user = $request->user();


        $request
            ->session()
            ->put(
                'account_view',
                'buyer'
            );


        /*
        |--------------------------------------------------------------------------
        | Buyer
        |--------------------------------------------------------------------------
        */

        $buyer = [

            'location' =>
                data_get(
                    $user,
                    'city'
                )
                ?: 'Ibadan, Oyo',

        ];


        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            'escrow' =>
                '₦172,500',

            'purchases_in_progress' =>
                2,

            'trust_score' =>
                '5.0',

            'purchases' =>
                11,

            'protected_lifetime' =>
                '₦1.4M',

        ];


        /*
        |--------------------------------------------------------------------------
        | Featured Active Transaction
        |--------------------------------------------------------------------------
        */

        $featuredTransaction = [

            'product' =>
                'iPhone 12, 128GB',

            'amount' =>
                '₦145,000',

            'seller' =>
                'Temi Gadgets',

            'delivery_type' =>
                'Seller-arranged delivery',

            'reference' =>
                'MP-88214',

            'escrow_amount' =>
                '₦148,500',

        ];


        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        $transactions = [

            [
                'product' =>
                    'iPhone 12, 128GB',

                'seller' =>
                    'Temi Gadgets',

                'amount' =>
                    '₦145,000',

                'status' =>
                    'Inspection',

                'status_class' =>
                    'purple',
            ],

            [
                'product' =>
                    'Ankara two-piece set',

                'seller' =>
                    'Zaria Stitches',

                'amount' =>
                    '₦24,000',

                'status' =>
                    'Dispatched',

                'status_class' =>
                    'amber',
            ],

            [
                'product' =>
                    'Air fryer, 5.5L',

                'seller' =>
                    'HomePlus NG',

                'amount' =>
                    '₦68,000',

                'status' =>
                    'Completed',

                'status_class' =>
                    'green',
            ],

            [
                'product' =>
                    '12" human-hair wig',

                'seller' =>
                    'Crowned Hair Empire',

                'amount' =>
                    '₦95,000',

                'status' =>
                    'Completed',

                'status_class' =>
                    'green',
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $notifications = [

            [
                'icon' =>
                    'fa-box',

                'title' =>
                    'Delivered',

                'message' =>
                    'Your iPhone 12 arrived. Inspection has started.',
            ],

            [
                'icon' =>
                    'fa-box',

                'title' =>
                    'Item dispatched',

                'message' =>
                    'Zaria Stitches has sent your Ankara set.',
            ],

            [
                'icon' =>
                    'fa-square-check',

                'title' =>
                    'Funds released',

                'message' =>
                    'HomePlus NG has been paid for your air fryer.',
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Featured Businesses
        |--------------------------------------------------------------------------
        */

        $businesses = [

            [
                'initials' =>
                    'TG',

                'name' =>
                    'Temi Gadgets',

                'category' =>
                    'Phones & Electronics',

                'trust' =>
                    '4.9',

                'style' =>
                    'green',
            ],

            [
                'initials' =>
                    'CH',

                'name' =>
                    'Crowned Hair Empire',

                'category' =>
                    'Beauty & Hair',

                'trust' =>
                    '4.8',

                'style' =>
                    'purple',
            ],

            [
                'initials' =>
                    'ZS',

                'name' =>
                    'Zaria Stitches',

                'category' =>
                    'Fashion & Tailoring',

                'trust' =>
                    '4.7',

                'style' =>
                    'orange',
            ],

        ];


        return view(
            'buyer.dashboard',
            compact(
                'user',
                'buyer',
                'statistics',
                'featuredTransaction',
                'transactions',
                'notifications',
                'businesses'
            )
        );
    }
}