<?php

namespace App\Providers;

use App\Models\SupportChatSession;
use App\View\Composers\AdminLayoutComposer;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register()
    {
        //
    }


    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Admin Layout Data
        |--------------------------------------------------------------------------
        |
        | This is the important missing part.
        |
        | It provides:
        |
        | $adminUnreadContactCount
        | $adminUnreadNotificationCount
        | $adminLatestNotifications
        |
        | to the admin layout and therefore the header/sidebar.
        |
        */

        View::composer(
            'admin.layouts.app',
            AdminLayoutComposer::class
        );


        /*
        |--------------------------------------------------------------------------
        | Live Support Waiting Count
        |--------------------------------------------------------------------------
        */

        View::composer(
            'admin.partials.sidebar.modules.support-inquiries',
            function ($view) {

                $count = 0;


                if (
                    Schema::hasTable(
                        'support_chat_sessions'
                    )
                ) {

                    $count =
                        SupportChatSession::query()
                            ->where(
                                'status',
                                'waiting'
                            )
                            ->count();
                }


                $view->with(
                    'liveSupportWaitingCount',
                    $count
                );
            }
        );
    }
}