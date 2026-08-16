<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteContentPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentPageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | About Page Editor
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
            'admin.website-settings.about-page',
            compact(
                'page',
                'content'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save About Page
    |--------------------------------------------------------------------------
    */

    public function updateAbout(
        Request $request
    ) {
        $validated =
            $request->validate([

                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
                */

                'meta_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'meta_description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],


                /*
                |--------------------------------------------------------------------------
                | Hero
                |--------------------------------------------------------------------------
                */

                'hero_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'hero_title' => [
                    'required',
                    'string',
                    'max:500',
                ],

                'hero_description' => [
                    'required',
                    'string',
                    'max:3000',
                ],


                /*
                |--------------------------------------------------------------------------
                | Stats
                |--------------------------------------------------------------------------
                */

                'stats' => [
                    'required',
                    'array',
                    'min:1',
                    'max:12',
                ],

                'stats.*.label' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'stats.*.value' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'stats.*.description' => [
                    'nullable',
                    'string',
                    'max:500',
                ],


                /*
                |--------------------------------------------------------------------------
                | Principles
                |--------------------------------------------------------------------------
                */

                'principles_heading' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'principles' => [
                    'required',
                    'array',
                    'min:1',
                    'max:20',
                ],

                'principles.*.icon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'principles.*.title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'principles.*.description' => [
                    'required',
                    'string',
                    'max:1500',
                ],


                /*
                |--------------------------------------------------------------------------
                | CTA
                |--------------------------------------------------------------------------
                */

                'cta_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'cta_description' => [
                    'required',
                    'string',
                    'max:1200',
                ],

                'cta_button_text' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'cta_button_url' => [
                    'required',
                    'string',
                    'max:500',
                ],

            ]);


        DB::transaction(
            function () use ($validated) {

                $page =
                    WebsiteContentPage::page(
                        'about'
                    );


                $page->update([

                    'meta_title' =>
                        $validated['meta_title'],

                    'meta_description' =>
                        $validated['meta_description']
                        ??
                        '',

                    'content' => [

                        'hero' => [

                            'eyebrow' =>
                                $validated['hero_eyebrow'],

                            'title' =>
                                $validated['hero_title'],

                            'description' =>
                                $validated['hero_description'],

                        ],


                        'stats' =>
                            array_values(
                                $validated['stats']
                            ),


                        'principles_heading' =>
                            $validated['principles_heading'],


                        'principles' =>
                            array_values(
                                $validated['principles']
                            ),


                        'cta' => [

                            'title' =>
                                $validated['cta_title'],

                            'description' =>
                                $validated['cta_description'],

                            'button_text' =>
                                $validated['cta_button_text'],

                            'button_url' =>
                                $validated['cta_button_url'],

                        ],

                    ],


                    'updated_by' =>
                        Auth::id(),

                ]);
            }
        );


        return redirect()
            ->route(
                'admin.website-settings.about-page'
            )
            ->with(
                'success',
                'About page updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | How It Works Editor
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
            'admin.website-settings.how-it-works-page',
            compact(
                'page',
                'content'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save How It Works
    |--------------------------------------------------------------------------
    */

    public function updateHowItWorks(
        Request $request
    ) {
        $validated =
            $request->validate([

                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
                */

                'meta_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'meta_description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],


                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                'hero_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'hero_title' => [
                    'required',
                    'string',
                    'max:500',
                ],

                'hero_description' => [
                    'required',
                    'string',
                    'max:2000',
                ],


                /*
                |--------------------------------------------------------------------------
                | Seller Journey
                |--------------------------------------------------------------------------
                */

                'seller_badge' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'seller_steps' => [
                    'required',
                    'array',
                    'min:1',
                    'max:20',
                ],

                'seller_steps.*.title' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'seller_steps.*.description' => [
                    'required',
                    'string',
                    'max:1500',
                ],


                /*
                |--------------------------------------------------------------------------
                | Buyer Journey
                |--------------------------------------------------------------------------
                */

                'buyer_badge' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'buyer_steps' => [
                    'required',
                    'array',
                    'min:1',
                    'max:20',
                ],

                'buyer_steps.*.title' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'buyer_steps.*.description' => [
                    'required',
                    'string',
                    'max:1500',
                ],


                /*
                |--------------------------------------------------------------------------
                | Delivery
                |--------------------------------------------------------------------------
                */

                'delivery_heading' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'delivery_cards' => [
                    'required',
                    'array',
                    'min:1',
                    'max:12',
                ],

                'delivery_cards.*.icon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'delivery_cards.*.title' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'delivery_cards.*.badge' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'delivery_cards.*.description' => [
                    'required',
                    'string',
                    'max:1500',
                ],


                /*
                |--------------------------------------------------------------------------
                | CTA
                |--------------------------------------------------------------------------
                */

                'cta_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'cta_description' => [
                    'required',
                    'string',
                    'max:1200',
                ],

                'cta_button_text' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'cta_button_url' => [
                    'required',
                    'string',
                    'max:500',
                ],

            ]);


        DB::transaction(
            function () use ($validated) {

                $page =
                    WebsiteContentPage::page(
                        'how-it-works'
                    );


                $page->update([

                    'meta_title' =>
                        $validated['meta_title'],

                    'meta_description' =>
                        $validated['meta_description']
                        ??
                        '',


                    'content' => [

                        'hero' => [

                            'eyebrow' =>
                                $validated['hero_eyebrow'],

                            'title' =>
                                $validated['hero_title'],

                            'description' =>
                                $validated['hero_description'],

                        ],


                        'seller_badge' =>
                            $validated['seller_badge'],


                        'seller_steps' =>
                            array_values(
                                $validated['seller_steps']
                            ),


                        'buyer_badge' =>
                            $validated['buyer_badge'],


                        'buyer_steps' =>
                            array_values(
                                $validated['buyer_steps']
                            ),


                        'delivery_heading' =>
                            $validated['delivery_heading'],


                        'delivery_cards' =>
                            array_values(
                                $validated['delivery_cards']
                            ),


                        'cta' => [

                            'title' =>
                                $validated['cta_title'],

                            'description' =>
                                $validated['cta_description'],

                            'button_text' =>
                                $validated['cta_button_text'],

                            'button_url' =>
                                $validated['cta_button_url'],

                        ],

                    ],


                    'updated_by' =>
                        Auth::id(),

                ]);
            }
        );


        return redirect()
            ->route(
                'admin.website-settings.how-it-works-page'
            )
            ->with(
                'success',
                'How It Works page updated successfully.'
            );
    }
}