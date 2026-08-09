<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\SupportChatSession;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }


public function boot()
{
    View::composer(
        'admin.partials.sidebar.modules.support-inquiries',
        function ($view) {

            $count =
                0;


            if (
                Schema::hasTable(
                    'support_chat_sessions'
                )
            ) {

                $count =
                    SupportChatSession::where(
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