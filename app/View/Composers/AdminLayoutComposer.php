<?php

namespace App\View\Composers;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(
        View $view
    ) {

        $user =
            Auth::user();


        if (
            !$user
            ||
            $user->role !== 'admin'
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Contact Count
        |--------------------------------------------------------------------------
        */

        $adminUnreadContactCount =
            ContactMessage::query()
                ->whereNull(
                    'read_at'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Notification Count
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
                ->limit(8)
                ->get();


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