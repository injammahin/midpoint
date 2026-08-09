<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SupportChatSetting;
use App\Models\SupportAgentProfile;
use Illuminate\Database\Seeder;

class SupportChatSeeder extends Seeder
{
    public function run()
    {
        SupportChatSetting::current();


        $admin =
            User::where(
                'email',
                'admin@gmail.com'
            )
            ->first();


        if ($admin) {

            SupportAgentProfile::updateOrCreate(
                [
                    'user_id' =>
                        $admin->id,
                ],
                [
                    'is_enabled' =>
                        true,

                    'is_accepting_chats' =>
                        false,

                    'max_active_chats' =>
                        3,
                ]
            );

        }
    }
}