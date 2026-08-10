<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerBusinessProfile;
use App\Models\User;

use Illuminate\Http\Request;


class FeaturedBusinessController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Featured Business Directory
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                (string)
                $request->get(
                    'search',
                    ''
                )
            );


        $category =
            trim(
                (string)
                $request->get(
                    'category',
                    ''
                )
            );


        $location =
            trim(
                (string)
                $request->get(
                    'location',
                    ''
                )
            );


        $sort =
            (string)
            $request->get(
                'sort',
                'newest'
            );


        if (
            !in_array(
                $sort,
                [
                    'newest',
                    'name',
                    'rating',
                    'products',
                ],
                true
            )
        ) {
            $sort =
                'newest';
        }


        $perPage =
            (int)
            $request->get(
                'per_page',
                12
            );


        if (
            !in_array(
                $perPage,
                [
                    12,
                    30,
                    45,
                ],
                true
            )
        ) {
            $perPage =
                12;
        }


        /*
        |--------------------------------------------------------------------------
        | Sellers
        |--------------------------------------------------------------------------
        |
        | Only:
        |
        | - active users
        | - users with an ACTIVE, non-expired seller subscription
        |
        */

        $query =
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


                /*
                |--------------------------------------------------------------------------
                | Load Everything Needed By Directory
                |--------------------------------------------------------------------------
                */

                ->with([

                    'sellerBusinessProfile',

                    'activeSellerSubscription' =>
                        function ($subscriptionQuery) {

                            $subscriptionQuery
                                ->with([
                                    'application',
                                    'package',
                                ]);
                        },

                ])


                /*
                |--------------------------------------------------------------------------
                | Product Count
                |--------------------------------------------------------------------------
                */

                ->withCount([

                    'sellerProducts as active_products_count' =>
                        function ($productQuery) {

                            $productQuery->where(
                                'is_active',
                                true
                            );
                        },

                ])


                /*
                |--------------------------------------------------------------------------
                | Rating
                |--------------------------------------------------------------------------
                */

                ->withAvg(
                    'publishedSellerReviews as seller_rating',
                    'rating'
                )


                /*
                |--------------------------------------------------------------------------
                | Reviews
                |--------------------------------------------------------------------------
                */

                ->withCount(
                    'publishedSellerReviews as seller_review_count'
                );


        /*
        |--------------------------------------------------------------------------
        | Flexible Search
        |--------------------------------------------------------------------------
        */

        if (
            $search !== ''
        ) {

            $query->where(
                function ($searchQuery) use (
                    $search
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | User Name
                    |--------------------------------------------------------------------------
                    */

                    $searchQuery->where(
                        'name',
                        'like',
                        '%'
                        .
                        $search
                        .
                        '%'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Business Profile
                    |--------------------------------------------------------------------------
                    */

                    $searchQuery->orWhereHas(
                        'sellerBusinessProfile',
                        function ($profileQuery) use (
                            $search
                        ) {

                            $profileQuery
                                ->where(
                                    'tagline',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                )

                                ->orWhere(
                                    'about',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                )

                                ->orWhere(
                                    'location',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Verified Application
                    |--------------------------------------------------------------------------
                    */

                    $searchQuery->orWhereHas(
                        'activeSellerSubscription.application',
                        function ($applicationQuery) use (
                            $search
                        ) {

                            $applicationQuery
                                ->where(
                                    'business_name',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                )

                                ->orWhere(
                                    'category',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                )

                                ->orWhere(
                                    'location',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                )

                                ->orWhere(
                                    'description',
                                    'like',
                                    '%'
                                    .
                                    $search
                                    .
                                    '%'
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Product Search
                    |--------------------------------------------------------------------------
                    |
                    | Allows:
                    |
                    | android watch
                    | iphone
                    | laptop
                    | gaming console
                    |
                    */

                    $searchQuery->orWhereHas(
                        'sellerProducts',
                        function ($productQuery) use (
                            $search
                        ) {

                            $productQuery

                                ->where(
                                    'is_active',
                                    true
                                )

                                ->where(
                                    function ($q) use (
                                        $search
                                    ) {

                                        $q->where(
                                            'name',
                                            'like',
                                            '%'
                                            .
                                            $search
                                            .
                                            '%'
                                        )

                                        ->orWhere(
                                            'description',
                                            'like',
                                            '%'
                                            .
                                            $search
                                            .
                                            '%'
                                        );
                                    }
                                );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if (
            $category !== ''
        ) {

            $query->whereHas(
                'activeSellerSubscription.application',
                function ($applicationQuery) use (
                    $category
                ) {

                    $applicationQuery->where(
                        'category',
                        $category
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Location
        |--------------------------------------------------------------------------
        |
        | Search both editable business profile and verified application.
        |
        */

        if (
            $location !== ''
        ) {

            $query->where(
                function ($locationQuery) use (
                    $location
                ) {

                    $locationQuery->whereHas(
                        'sellerBusinessProfile',
                        function ($profileQuery) use (
                            $location
                        ) {

                            $profileQuery->where(
                                'location',
                                $location
                            );
                        }
                    );


                    $locationQuery->orWhereHas(
                        'activeSellerSubscription.application',
                        function ($applicationQuery) use (
                            $location
                        ) {

                            $applicationQuery->where(
                                'location',
                                $location
                            );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        switch (
            $sort
        ) {

            case 'rating':

                $query
                    ->orderByDesc(
                        'seller_rating'
                    )
                    ->orderByDesc(
                        'id'
                    );

                break;


            case 'products':

                $query
                    ->orderByDesc(
                        'active_products_count'
                    )
                    ->orderByDesc(
                        'id'
                    );

                break;


            case 'name':

                $query
                    ->orderBy(
                        'name'
                    );

                break;


            case 'newest':
            default:

                $query
                    ->orderByDesc(
                        'id'
                    );

                break;
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $sellers =
            $query
                ->paginate(
                    $perPage
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories =
            SellerApplication::query()

                ->where(
                    'status',
                    SellerApplication::STATUS_ACTIVE
                )

                ->whereNotNull(
                    'category'
                )

                ->where(
                    'category',
                    '<>',
                    ''
                )

                ->distinct()

                ->orderBy(
                    'category'
                )

                ->pluck(
                    'category'
                );


        /*
        |--------------------------------------------------------------------------
        | Locations
        |--------------------------------------------------------------------------
        */

        $applicationLocations =
            SellerApplication::query()

                ->where(
                    'status',
                    SellerApplication::STATUS_ACTIVE
                )

                ->whereNotNull(
                    'location'
                )

                ->where(
                    'location',
                    '<>',
                    ''
                )

                ->pluck(
                    'location'
                );


        $profileLocations =
            SellerBusinessProfile::query()

                ->whereNotNull(
                    'location'
                )

                ->where(
                    'location',
                    '<>',
                    ''
                )

                ->pluck(
                    'location'
                );


        $locations =
            $applicationLocations

                ->merge(
                    $profileLocations
                )

                ->filter()

                ->unique()

                ->sort()

                ->values();


        return view(
            'frontend.pages.featured-businesses',
            compact(
                'sellers',
                'categories',
                'locations',
                'search',
                'category',
                'location',
                'sort',
                'perPage'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Public Profile
    |--------------------------------------------------------------------------
    */

    public function show(
        User $seller
    ) {
        /*
        |--------------------------------------------------------------------------
        | Seller Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $seller->role === 'user'
            &&
            (bool)
            $seller->status,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Load Seller Public Data
        |--------------------------------------------------------------------------
        */

        $seller->load([

            'sellerBusinessProfile',

            'activeSellerSubscription' =>
                function ($query) {

                    $query->with([
                        'application',
                        'package',
                    ]);
                },

        ]);


        /*
        |--------------------------------------------------------------------------
        | Active Subscription Required
        |--------------------------------------------------------------------------
        */

        $subscription =
            $seller
                ->activeSellerSubscription;


        abort_unless(
            $subscription,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Approved Application
        |--------------------------------------------------------------------------
        */

        $application =
            $subscription
                ->application;


        abort_unless(
            $application,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Business Profile
        |--------------------------------------------------------------------------
        */

        $businessProfile =
            $seller
                ->sellerBusinessProfile;


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            $seller
                ->sellerProducts()

                ->where(
                    'is_active',
                    true
                )

                ->latest()

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        $reviews =
            $seller
                ->publishedSellerReviews()

                ->with([
                    'buyer',
                    'product',
                ])

                ->latest()

                ->get();


        $averageRating =
            $reviews
                ->isNotEmpty()

                ? round(
                    (float)
                    $reviews->avg(
                        'rating'
                    ),
                    1
                )

                : null;


        $reviewCount =
            $reviews->count();


        return view(
            'frontend.businesses.show',
            compact(
                'seller',
                'subscription',
                'application',
                'businessProfile',
                'products',
                'reviews',
                'averageRating',
                'reviewCount'
            )
        );
    }
}