<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class HomeController extends Controller
{
    /**
     * Show public homepage.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Homepage FAQs
        |--------------------------------------------------------------------------
        |
        | Only:
        | - Active FAQs
        | - Marked "Show on homepage"
        | - Ordered by sort_order
        |
        */

        $homeFaqs = Faq::query()
            ->where('is_active', true)
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(4)
            ->get();


        return view(
            'frontend.pages.home',
            compact('homeFaqs')
        );
    }
}