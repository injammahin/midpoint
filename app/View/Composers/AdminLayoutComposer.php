<?php

namespace App\View\Composers;

use App\Models\ContactMessage;
use App\Models\TransactionDispute;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminLayoutComposer
{
    /*
    |--------------------------------------------------------------------------
    | Compose
    |--------------------------------------------------------------------------
    */

    public function compose(
        View $view
    ) {
        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */

        $adminUnreadContactCount =
            0;


        $adminUnreadNotificationCount =
            0;


        $adminOpenDisputeCount =
            0;
        if (
            Schema::hasTable(
                'transaction_disputes'
            )
        ) {

            $adminOpenDisputeCount =
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_OPEN
                    )

                    ->count();
        }

        $adminLatestNotifications =
            collect();


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user =
            Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Only Administrators
        |--------------------------------------------------------------------------
        */

        if (
            !$user
            ||
            $user->role !== 'admin'
        ) {

            $view->with([

                'adminUnreadContactCount' =>
                    $adminUnreadContactCount,

                'adminUnreadNotificationCount' =>
                    $adminUnreadNotificationCount,

                'adminOpenDisputeCount' =>
                    $adminOpenDisputeCount,

                'adminLatestNotifications' =>
                    $adminLatestNotifications,

            ]);


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Contact Messages
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable(
                'contact_messages'
            )
        ) {

            $adminUnreadContactCount =
                ContactMessage::query()

                    ->whereNull(
                        'read_at'
                    )

                    ->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Open Transaction Disputes
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable(
                'transaction_disputes'
            )
        ) {

            $adminOpenDisputeCount =
                TransactionDispute::query()

                    ->whereIn(
                        'status',
                        [
                            'open',
                            'under_review',
                        ]
                    )

                    ->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Database Notifications
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable(
                'notifications'
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | Unread Count
            |--------------------------------------------------------------------------
            */

            $adminUnreadNotificationCount =
                $user

                    ->unreadNotifications()

                    ->count();


            /*
            |--------------------------------------------------------------------------
            | Latest Notifications
            |--------------------------------------------------------------------------
            */

            $adminLatestNotifications =
                $user

                    ->notifications()

                    ->latest()

                    ->limit(
                        10
                    )

                    ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Send To Admin Layout
        |--------------------------------------------------------------------------
        */

        $view->with([

            'adminUnreadContactCount' =>
                $adminUnreadContactCount,

            'adminUnreadNotificationCount' =>
                $adminUnreadNotificationCount,

            'adminOpenDisputeCount' =>
                $adminOpenDisputeCount,

            'adminLatestNotifications' =>
                $adminLatestNotifications,

        ]);
    }
}