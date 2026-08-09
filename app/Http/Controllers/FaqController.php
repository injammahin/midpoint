<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public FAQ Page
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $faqs =
            Faq::query()
                ->active()
                ->ordered()
                ->get();


        return view(
            'frontend.pages.faqs',
            compact(
                'faqs'
            )
        );
    }
}