<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ) {

        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Current View
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'account_view',
                'seller'
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Identity
        |--------------------------------------------------------------------------
        |
        | We will replace these fallbacks with the Business Profile model
        | when that module is built.
        |
        */

        $seller = [

            'business_name' =>
                data_get(
                    $user,
                    'business_name'
                )
                ?: 'Temi Gadgets',

            'location' =>
                data_get(
                    $user,
                    'city'
                )
                ?: 'Ikeja, Lagos',

        ];


        /*
        |--------------------------------------------------------------------------
        | Featured Transaction
        |--------------------------------------------------------------------------
        |
        | Demo/dashboard presentation data for now.
        |
        */

        $featuredTransaction = [

            'reference' =>
                'MP-88214',

            'product' =>
                'iPhone 12, 128GB',

            'amount' =>
                145000,

            'buyer' =>
                'Chiamaka Nwosu',

            'paid_ago' =>
                '2 hours ago',

            'delivery_address' =>
                '12 Awolowo Avenue, Bodija, Ibadan, Oyo State',

            'delivery_phone' =>
                '0803 552 7741',

            'payout' =>
                137206,

        ];


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            [
                'label' =>
                    'Held in escrow',

                'value' =>
                    '₦412,500',

                'note' =>
                    '3 active deals',

                'class' =>
                    'positive',
            ],

            [
                'label' =>
                    'Released this month',

                'value' =>
                    '₦1.28M',

                'note' =>
                    '▲ 18% vs June',

                'class' =>
                    'positive',
            ],

            [
                'label' =>
                    'Charges paid',

                'value' =>
                    '₦68,908',

                'note' =>
                    '5% fee + 7.5% VAT',

                'class' =>
                    '',
            ],

            [
                'label' =>
                    'Trust score',

                'value' =>
                    '4.9',

                'suffix' =>
                    '/5',

                'note' =>
                    '62 completed deals',

                'class' =>
                    'positive',
            ],

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

                'reference' =>
                    'MP-88214',

                'buyer' =>
                    'Chiamaka N.',

                'amount' =>
                    '₦145,000',

                'status' =>
                    'Inspection',

                'status_class' =>
                    'purple',
            ],

            [
                'product' =>
                    'PS5 Slim + 2 pads',

                'reference' =>
                    'MP-88190',

                'buyer' =>
                    'Emeka O.',

                'amount' =>
                    '₦520,000',

                'status' =>
                    'In transit',

                'status_class' =>
                    'amber',
            ],

            [
                'product' =>
                    'Oraimo FreePods 4',

                'reference' =>
                    'MP-88171',

                'buyer' =>
                    'Fatima S.',

                'amount' =>
                    '₦28,500',

                'status' =>
                    'Funds released',

                'status_class' =>
                    'green',
            ],

            [
                'product' =>
                    'HP EliteBook 840 G7',

                'reference' =>
                    'MP-88123',

                'buyer' =>
                    'Yusuf D.',

                'amount' =>
                    '₦385,000',

                'status' =>
                    'Completed',

                'status_class' =>
                    'green',
            ],

            [
                'product' =>
                    'Samsung A54 case (x20)',

                'reference' =>
                    'MP-88099',

                'buyer' =>
                    'Blessing A.',

                'amount' =>
                    '₦46,000',

                'status' =>
                    'Awaiting buyer',

                'status_class' =>
                    'slate',
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
                    'fa-money-bill-transfer',

                'title' =>
                    'Payment received',

                'message' =>
                    'Chiamaka paid ₦145,000 for iPhone 12.',
            ],

            [
                'icon' =>
                    'fa-box',

                'title' =>
                    'Reminder to dispatch',

                'message' =>
                    'Payment held for PS5 Slim. Mark it dispatched once it is sent.',
            ],

            [
                'icon' =>
                    'fa-stopwatch',

                'title' =>
                    'Inspection reminder',

                'message' =>
                    'Buyer window on MP-88214 ends in 3h.',
            ],

        ];


        return view(
            'seller.dashboard',
            compact(
                'user',
                'seller',
                'featuredTransaction',
                'statistics',
                'transactions',
                'notifications'
            )
        );
    }
}