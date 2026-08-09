<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Temporary dashboard data
        |--------------------------------------------------------------------------
        |
        | Later these will come from:
        |
        | users
        | transactions
        | contact_messages
        | support_messages
        | verified_seller_applications
        |
        */

        $stats = [

            'users' => 1240,

            'sellers' => 186,

            'transactions' => 842,

            'inquiries' => 24,

        ];


        $monthlyTransactions = [
            125,
            178,
            214,
            292,
            347,
            426,
            562,
            842,
        ];


        $transactionStatuses = [

            'active' => 18,

            'completed' => 61,

            'disputed' => 7,

            'cancelled' => 14,

        ];


        $recentInquiries = [

            [
                'name' => 'Chiamaka Nwosu',
                'email' => 'chiamaka@example.com',
                'type' => 'Transaction Help',
                'status' => 'New',
            ],

            [
                'name' => 'Ade Bello',
                'email' => 'ade@example.com',
                'type' => 'Seller Verification',
                'status' => 'In Progress',
            ],

            [
                'name' => 'Tunde Usman',
                'email' => 'tunde@example.com',
                'type' => 'Payment',
                'status' => 'Resolved',
            ],

        ];


        return view(
            'admin.dashboard.index',
            compact(
                'stats',
                'monthlyTransactions',
                'transactionStatuses',
                'recentInquiries'
            )
        );
    }
}