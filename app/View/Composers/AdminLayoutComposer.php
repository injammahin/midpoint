<?php

namespace App\View\Composers;

use App\Models\ContactMessage;

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

        $adminUnreadContactCount = 0;

        $adminUnreadNotificationCount = 0;

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
                    ->limit(10)
                    ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Send To Layout
        |--------------------------------------------------------------------------
        */

        $view->with([

            'adminUnreadContactCount' =>
                $adminUnreadContactCount,

            'adminUnreadNotificationCount' =>
                $adminUnreadNotificationCount,

            'adminLatestNotifications' =>
                $adminLatestNotifications,

        ]);
    }
}