<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\HomePageSetting;
use App\Models\HomeTestimonial;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Show public homepage.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Dynamic Home Page Settings
        |--------------------------------------------------------------------------
        */

        $home =
            HomePageSetting::current();


        /*
        |--------------------------------------------------------------------------
        | Home FAQs
        |--------------------------------------------------------------------------
        */

        $homeFaqs =
            Faq::query()

                ->where(
                    'is_active',
                    true
                )

                ->where(
                    'show_on_home',
                    true
                )

                ->orderBy(
                    'sort_order'
                )

                ->orderBy(
                    'id'
                )

                ->limit(4)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Random Featured Businesses
        |--------------------------------------------------------------------------
        |
        | Maximum 3.
        | Different random businesses on each request.
        |
        */

        $featuredBusinesses =
            User::query()

                ->where(
                    'role',
                    'user'
                )

                ->where(
                    'status',
                    true
                )

                ->whereHas(
                    'activeSellerSubscription'
                )

                ->with([

                    'sellerBusinessProfile',

                    'activeSellerSubscription' =>
                        function (
                            $subscriptionQuery
                        ) {

                            $subscriptionQuery
                                ->with([

                                    'application',

                                    'package',

                                ]);
                        },

                ])

                ->withCount([

                    'sellerProducts as active_products_count' =>
                        function (
                            $productQuery
                        ) {

                            $productQuery
                                ->where(
                                    'is_active',
                                    true
                                );
                        },

                ])

                ->withAvg(
                    'publishedSellerReviews as seller_rating',
                    'rating'
                )

                ->withCount(
                    'publishedSellerReviews as seller_review_count'
                )

                ->inRandomOrder()

                ->limit(3)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Dynamic Homepage Testimonials
        |--------------------------------------------------------------------------
        */

        $homeTestimonials =
            HomeTestimonial::query()

                ->active()

                ->ordered()

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

        return view(
            'frontend.pages.home',
            compact(

                'home',

                'homeFaqs',

                'featuredBusinesses',

                'homeTestimonials'

            )
        );
    }
}