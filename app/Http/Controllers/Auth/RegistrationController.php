<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration Page
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'frontend.pages.register'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Register User
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated =
            $request->validate(
                [

                    'preferred_role' => [
                        'required',

                        Rule::in([
                            'seller',
                            'buyer',
                        ]),
                    ],


                    'full_name' => [
                        'required',
                        'string',
                        'max:150',
                    ],


                    'phone' => [
                        'required',
                        'string',
                        'max:30',
                        'unique:users,phone',
                    ],


                    'email' => [
                        'required',
                        'email',
                        'max:255',
                        'unique:users,email',
                    ],


                    'password' => [
                        'required',
                        'confirmed',

                        Password::min(8),
                    ],

                ],
                [

                    'password.confirmed' =>
                        'Password confirmation does not match.',

                ]
            );


        $user = DB::transaction(
            function () use (
                $validated,
                $request
            ) {

                return User::create([

                    'name' =>
                        trim(
                            $validated['full_name']
                        ),

                    'phone' =>
                        trim(
                            $validated['phone']
                        ),

                    'email' =>
                        strtolower(
                            trim(
                                $validated['email']
                            )
                        ),

                    'password' =>
                        Hash::make(
                            $validated['password']
                        ),


                    /*
                    |--------------------------------------------------------------------------
                    | One Account - Both Views
                    |--------------------------------------------------------------------------
                    */

                    'role' =>
                        'user',

                    'preferred_role' =>
                        $validated['preferred_role'],


                    /*
                    |--------------------------------------------------------------------------
                    | Active But Not Yet Email-Verified
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                        true,

                    'email_verified_at' =>
                        null,

                ]);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Log User In
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user
        );


        $request
            ->session()
            ->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Send Verification Email
        |--------------------------------------------------------------------------
        */

        $user
            ->sendEmailVerificationNotification();


        return redirect()
            ->route(
                'verification.notice'
            );
    }
}