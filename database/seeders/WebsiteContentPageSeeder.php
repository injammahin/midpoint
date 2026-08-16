<?php

namespace Database\Seeders;

use App\Models\WebsiteContentPage;
use Illuminate\Database\Seeder;

class WebsiteContentPageSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | About
        |--------------------------------------------------------------------------
        */

        $about =
            WebsiteContentPage::defaults(
                'about'
            );


        WebsiteContentPage::updateOrCreate(

            [
                'slug' =>
                    'about',
            ],

            [
                'meta_title' =>
                    $about['meta_title'],

                'meta_description' =>
                    $about['meta_description'],

                'content' =>
                    $about['content'],

                'updated_by' =>
                    null,
            ]

        );


        /*
        |--------------------------------------------------------------------------
        | How It Works
        |--------------------------------------------------------------------------
        */

        $how =
            WebsiteContentPage::defaults(
                'how-it-works'
            );


        WebsiteContentPage::updateOrCreate(

            [
                'slug' =>
                    'how-it-works',
            ],

            [
                'meta_title' =>
                    $how['meta_title'],

                'meta_description' =>
                    $how['meta_description'],

                'content' =>
                    $how['content'],

                'updated_by' =>
                    null,
            ]

        );
    }
}