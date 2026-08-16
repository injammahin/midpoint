<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            [
                'email' => 'admin@gmail.com',
            ],
            [
                'name' => 'Midpoint Administrator',

                'phone' => null,

                'password' => Hash::make('123456'),

                'role' => 'admin',

                'status' => true,
            ]
        );
    }
}