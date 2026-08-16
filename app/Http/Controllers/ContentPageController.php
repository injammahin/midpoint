<?php

namespace App\Http\Controllers;

use App\Models\WebsiteContentPage;

class ContentPageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | About
    |--------------------------------------------------------------------------
    */

    public function about()
    {
        $page =
            WebsiteContentPage::page(
                'about'
            );


        $content =
            $page->content;


        return view(
            'frontend.pages.about',
            compact(
                'page',
                'content'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | How It Works
    |--------------------------------------------------------------------------
    */

    public function howItWorks()
    {
        $page =
            WebsiteContentPage::page(
                'how-it-works'
            );


        $content =
            $page->content;


        return view(
            'frontend.pages.how-it-works',
            compact(
                'page',
                'content'
            )
        );
    }
}