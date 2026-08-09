<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Open Notification
    |--------------------------------------------------------------------------
    */

    public function open(
        Request $request,
        string $notification
    ) {

        $notification =
            $request
                ->user()
                ->notifications()
                ->where(
                    'id',
                    $notification
                )
                ->firstOrFail();


        if (
            is_null(
                $notification->read_at
            )
        ) {

            $notification
                ->markAsRead();

        }


        $url =
            data_get(
                $notification->data,
                'url'
            );


        return redirect(
            $url
            ?: route(
                'admin.dashboard'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All Read
    |--------------------------------------------------------------------------
    */

    public function markAllRead(
        Request $request
    ) {

        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();


        return back();
    }
}