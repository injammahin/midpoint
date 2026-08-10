<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Notification Feed
    |--------------------------------------------------------------------------
    |
    | Used by JavaScript so new notifications appear without refreshing.
    |
    */

    public function feed(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user
            &&
            $user->role === 'admin',
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Unread Count
        |--------------------------------------------------------------------------
        */

        $unreadCount =
            $user
                ->unreadNotifications()
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Latest Notifications
        |--------------------------------------------------------------------------
        */

        $notifications =
            $user
                ->notifications()
                ->latest()
                ->limit(10)
                ->get()
                ->map(
                    function (
                        $notification
                    ) {

                        return [

                            'id' =>
                                $notification->id,

                            'title' =>
                                data_get(
                                    $notification->data,
                                    'title',
                                    'Notification'
                                ),

                            'message' =>
                                data_get(
                                    $notification->data,
                                    'message',
                                    ''
                                ),

                            'icon' =>
                                data_get(
                                    $notification->data,
                                    'icon',
                                    'fa-bell'
                                ),

                            'unread' =>
                                is_null(
                                    $notification->read_at
                                ),

                            'created_at' =>
                                $notification
                                    ->created_at
                                    ->diffForHumans(),

                            /*
                            |--------------------------------------------------------------------------
                            | Always Open Through Controller
                            |--------------------------------------------------------------------------
                            |
                            | This automatically marks it read.
                            |
                            */

                            'open_url' =>
                                route(
                                    'admin.notifications.open',
                                    $notification->id
                                ),

                        ];
                    }
                );


        return response()->json([

            'success' =>
                true,

            'unread_count' =>
                $unreadCount,

            'notifications' =>
                $notifications,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Open Notification
    |--------------------------------------------------------------------------
    */

    public function open(
        Request $request,
        string $notification
    ) {
        /*
        |--------------------------------------------------------------------------
        | Find Only Current Admin's Notification
        |--------------------------------------------------------------------------
        */

        $notification =
            $request
                ->user()
                ->notifications()
                ->where(
                    'id',
                    $notification
                )
                ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Mark Read
        |--------------------------------------------------------------------------
        */

        if (
            is_null(
                $notification->read_at
            )
        ) {

            $notification
                ->markAsRead();
        }


        /*
        |--------------------------------------------------------------------------
        | Destination
        |--------------------------------------------------------------------------
        */

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
            ->unreadNotifications()
            ->update([
                'read_at' =>
                    now(),
            ]);


        return back();
    }
}