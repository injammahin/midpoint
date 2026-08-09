<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountModeController extends Controller
{
    public function switch(
        Request $request,
        string $mode
    ) {

        validator(
            [
                'mode' => $mode,
            ],
            [
                'mode' => [
                    'required',

                    Rule::in([
                        'buyer',
                        'seller',
                    ]),
                ],
            ]
        )->validate();


        $request
            ->user()
            ->update([

                'preferred_role' =>
                    $mode,

            ]);


        return redirect()
            ->route(
                'dashboard'
            );
    }
}