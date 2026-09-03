<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Admin 1
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            [
                'email' => 'mahin@gmail.com',
            ],
            [
                'name' => 'Mahin',

                'phone' => null,

                'password' => Hash::make('Mahin@5507'),

                'role' => 'admin',

                'status' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Admin 2
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            [
                'email' => 'admin@midpoint.ng',
            ],
            [
                'name' => 'Midpoint Administrator',

                'phone' => null,

                'password' => Hash::make('BarcaM1d&01'),

                'role' => 'admin',

                'status' => true,
            ]
        );
    }
}