<?php

namespace App\Http\Controllers;

use App\Models\PricingSetting;

class PricingController extends Controller
{
    public function index()
    {
        $pricing =
            PricingSetting::current();


        $calculation =
            $pricing->calculations();


        return view(
            'frontend.pages.pricing',
            compact(
                'pricing',
                'calculation'
            )
        );
    }
}