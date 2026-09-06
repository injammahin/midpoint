<?php

namespace App\Http\Controllers;

use App\Models\SecureTransaction;
use App\Models\SellerApplication;
use App\Models\SellerBusinessProfile;
use App\Models\User;

use App\Services\FeaturedBusinessRankingService;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class FeaturedBusinessController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Featured Business Directory
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        FeaturedBusinessRankingService $ranking
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


        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        |
        | Recommended is now the marketplace default.
        |
        | Recommended:
        | - 75% Premium exposure
        | - 17% Standard exposure
        | - 8% Basic/Starter exposure
        | - quality ranking inside each package tier
        |
        | Explicit user-selected sorting does NOT force Premium to the top.
        |
        */

        $sort =
            (string)
            $request->get(
                'sort',
                'recommended'
            );


        if (
            !in_array(
                $sort,
                [
                    'recommended',
                    'rating',
                    'orders',
                    'products',
                    'newest',
                    'name',
                ],
                true
            )
        ) {
            $sort =
                'recommended';
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


        $page =
            max(
                1,
                (int)
                $request->get(
                    'page',
                    1
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Base Eligible Sellers Query
        |--------------------------------------------------------------------------
        |
        | Only:
        | - normal user role
        | - active account
        | - active, non-expired seller package
        |
        */

        $query =
            $this->directoryQuery();


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
                    $searchQuery->where(
                        'name',
                        'like',
                        '%'
                        .
                        $search
                        .
                        '%'
                    );


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
        | Category Filter
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
        | Location Filter
        |--------------------------------------------------------------------------
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
        | Recommended Marketplace Ranking
        |--------------------------------------------------------------------------
        |
        | We intentionally rank the filtered candidate set in the service.
        | The service creates a stable page-by-page 9 / 2 / 1 mix for 12 results,
        | while dynamically backfilling missing tiers.
        |
        */

        if (
            $sort === 'recommended'
        ) {
            $candidates =
                $query->get();


            $queryForPaginator =
                $request->query();


            unset(
                $queryForPaginator['page']
            );


            $sellers =
                $ranking->paginate(
                    $candidates,
                    $perPage,
                    $page,
                    $request->url(),
                    $queryForPaginator
                );
        } else {
            /*
            |--------------------------------------------------------------------------
            | Explicit User Sorts
            |--------------------------------------------------------------------------
            |
            | These remain honest. Example: "Highest rating" really means rating,
            | not Premium package first.
            |
            */

            switch (
                $sort
            ) {
                case 'rating':

                    $query
                        ->orderByRaw(
                            'CASE WHEN seller_rating IS NULL THEN 1 ELSE 0 END'
                        )
                        ->orderByDesc(
                            'seller_rating'
                        )
                        ->orderByDesc(
                            'seller_review_count'
                        )
                        ->orderByDesc(
                            'completed_orders_count'
                        )
                        ->orderByDesc(
                            'users.id'
                        );

                    break;


                case 'orders':

                    $query
                        ->orderByDesc(
                            'completed_orders_count'
                        )
                        ->orderByDesc(
                            'seller_rating'
                        )
                        ->orderByDesc(
                            'seller_review_count'
                        )
                        ->orderByDesc(
                            'users.id'
                        );

                    break;


                case 'products':

                    $query
                        ->orderByDesc(
                            'active_products_count'
                        )
                        ->orderByDesc(
                            'completed_orders_count'
                        )
                        ->orderByDesc(
                            'users.id'
                        );

                    break;


                case 'name':

                    $query
                        ->orderBy(
                            'name'
                        )
                        ->orderBy(
                            'users.id'
                        );

                    break;


                case 'newest':
                default:

                    $query
                        ->orderByDesc(
                            'users.id'
                        );

                    break;
            }


            $sellers =
                $query
                    ->paginate(
                        $perPage
                    )
                    ->withQueryString();
        }


        /*
        |--------------------------------------------------------------------------
        | Category Options
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
        | Location Options
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
    | Directory Query
    |--------------------------------------------------------------------------
    */

    private function directoryQuery(): Builder
    {
        return User::query()
            ->select(
                'users.*'
            )
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
                    function ($subscriptionQuery) {
                        $subscriptionQuery->with([
                            'application',
                            'package',
                        ]);
                    },
            ])
            ->withCount([
                'sellerProducts as active_products_count' =>
                    function ($productQuery) {
                        $productQuery->where(
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
            ->addSelect([
                /*
                |------------------------------------------------------------------
                | Successfully Completed Orders
                |------------------------------------------------------------------
                */

                'completed_orders_count' =>
                    SecureTransaction::query()
                        ->selectRaw(
                            'COUNT(*)'
                        )
                        ->whereColumn(
                            'secure_transactions.seller_id',
                            'users.id'
                        )
                        ->where(
                            'secure_transactions.status',
                            SecureTransaction::STATUS_COMPLETED
                        ),


                /*
                |------------------------------------------------------------------
                | All Paid Orders
                |------------------------------------------------------------------
                |
                | Used only for the reliability component of Recommended ranking.
                |
                */

                'paid_orders_count' =>
                    SecureTransaction::query()
                        ->selectRaw(
                            'COUNT(*)'
                        )
                        ->whereColumn(
                            'secure_transactions.seller_id',
                            'users.id'
                        )
                        ->where(
                            'secure_transactions.payment_status',
                            SecureTransaction::PAYMENT_PAID
                        ),


                /*
                |------------------------------------------------------------------
                | Recent Completed Orders (90 Days)
                |------------------------------------------------------------------
                */

                'recent_completed_orders_count' =>
                    SecureTransaction::query()
                        ->selectRaw(
                            'COUNT(*)'
                        )
                        ->whereColumn(
                            'secure_transactions.seller_id',
                            'users.id'
                        )
                        ->where(
                            'secure_transactions.status',
                            SecureTransaction::STATUS_COMPLETED
                        )
                        ->whereNotNull(
                            'secure_transactions.completed_at'
                        )
                        ->where(
                            'secure_transactions.completed_at',
                            '>=',
                            now()
                                ->subDays(
                                    90
                                )
                        ),
            ]);
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
        |
        | Only the first 5 reviews are rendered initially. Additional reviews
        | are loaded in batches of 5 through the public reviews endpoint.
        |
        */

        $reviewCount =
            $seller
                ->publishedSellerReviews()
                ->count();


        $averageRating =

            $reviewCount > 0

                ? round(
                    (float)
                    $seller
                        ->publishedSellerReviews()
                        ->avg(
                            'rating'
                        ),
                    1
                )

                : null;


        $reviews =
            $seller
                ->publishedSellerReviews()

                ->with([
                    'buyer',
                    'product',
                ])

                ->latest()

                ->take(
                    5
                )

                ->get();


        $hasMoreReviews =
            $reviewCount
            >
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
                'reviewCount',
                'hasMoreReviews'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Load More Public Seller Reviews
    |--------------------------------------------------------------------------
    |
    | Returns 5 published reviews at a time for the public seller shop.
    |
    */

    public function reviews(
        Request $request,
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
        | Active Verified Seller Package Required
        |--------------------------------------------------------------------------
        */

        $seller->load([
            'activeSellerSubscription.application',
        ]);


        abort_unless(
            $seller->activeSellerSubscription
            &&
            $seller
                ->activeSellerSubscription
                ->application,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Page
        |--------------------------------------------------------------------------
        */

        $page =
            max(
                1,
                (int)
                $request->query(
                    'page',
                    1
                )
            );


        $perPage =
            5;


        /*
        |--------------------------------------------------------------------------
        | Total Published Reviews
        |--------------------------------------------------------------------------
        */

        $reviewCount =
            $seller
                ->publishedSellerReviews()
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Review Batch
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

                ->skip(
                    ($page - 1)
                    *
                    $perPage
                )

                ->take(
                    $perPage
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Render Existing Review Card Partial
        |--------------------------------------------------------------------------
        */

        $html =
            view(
                'frontend.businesses.partials.review-cards',
                compact(
                    'reviews'
                )
            )
                ->render();


        /*
        |--------------------------------------------------------------------------
        | JSON Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'html' =>
                $html,

            'loaded' =>
                $reviews->count(),

            'has_more' =>
                (
                    $page
                    *
                    $perPage
                )
                <
                $reviewCount,

            'next_page' =>
                $page
                +
                1,

        ]);
    }

}