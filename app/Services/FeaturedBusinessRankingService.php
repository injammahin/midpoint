<?php

namespace App\Services;

use App\Models\User;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


class FeaturedBusinessRankingService
{
    /*
    |--------------------------------------------------------------------------
    | Build Recommended Directory Pagination
    |--------------------------------------------------------------------------
    |
    | Recommended exposure target:
    |
    | 12 sellers => 9 Premium / 2 Standard / 1 Basic
    |
    | Larger page sizes keep approximately the same 75 / 17 / 8 split.
    | If a tier does not have enough sellers, unused slots are automatically
    | backfilled by the remaining tiers so the page never contains empty slots.
    |
    */

    public function paginate(
        Collection $sellers,
        int $perPage,
        int $page,
        string $path,
        array $query = []
    ): LengthAwarePaginator {
        $prepared =
            $sellers
                ->map(
                    function (User $seller) {
                        $seller->setAttribute(
                            'directory_package_tier',
                            $this->resolvePackageTier(
                                $seller
                            )
                        );

                        $seller->setAttribute(
                            'directory_quality_score',
                            $this->calculateQualityScore(
                                $seller
                            )
                        );

                        return $seller;
                    }
                );


        $groups = [
            'premium' =>
                $this->sortTier(
                    $prepared->where(
                        'directory_package_tier',
                        'premium'
                    )
                ),

            'standard' =>
                $this->sortTier(
                    $prepared->where(
                        'directory_package_tier',
                        'standard'
                    )
                ),

            'basic' =>
                $this->sortTier(
                    $prepared->where(
                        'directory_package_tier',
                        'basic'
                    )
                ),
        ];


        $ordered =
            $this->buildRecommendedSequence(
                $groups,
                $perPage
            );


        $total =
            $ordered->count();


        $offset =
            max(
                0,
                ($page - 1)
                *
                $perPage
            );


        $items =
            $ordered
                ->slice(
                    $offset,
                    $perPage
                )
                ->values();


        $paginator =
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' =>
                        $path,

                    'pageName' =>
                        'page',
                ]
            );


        if (
            !empty(
                $query
            )
        ) {
            $paginator->appends(
                $query
            );
        }


        return $paginator;
    }


    /*
    |--------------------------------------------------------------------------
    | Quality Ranking
    |--------------------------------------------------------------------------
    |
    | Package tier controls EXPOSURE quota.
    | Quality controls POSITION inside that tier.
    |
    | Score:
    |
    | 40% Bayesian/weighted rating
    | 30% completed orders (diminishing returns)
    | 15% review volume (diminishing returns)
    | 10% completed orders in the last 90 days
    |  5% completion reliability
    |
    | Plus:
    | - temporary new seller discovery boost
    | - tiny deterministic daily rotation factor
    |
    */

    public function calculateQualityScore(
        User $seller
    ): float {
        $rating =
            max(
                0,
                min(
                    5,
                    (float)
                    (
                        $seller->seller_rating
                        ??
                        0
                    )
                )
            );


        $reviews =
            max(
                0,
                (int)
                (
                    $seller->seller_review_count
                    ??
                    0
                )
            );


        $completedOrders =
            max(
                0,
                (int)
                (
                    $seller->completed_orders_count
                    ??
                    0
                )
            );


        $paidOrders =
            max(
                0,
                (int)
                (
                    $seller->paid_orders_count
                    ??
                    0
                )
            );


        $recentCompletedOrders =
            max(
                0,
                (int)
                (
                    $seller->recent_completed_orders_count
                    ??
                    0
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Weighted Rating
        |--------------------------------------------------------------------------
        |
        | Prevent one 5-star review from beating a seller with many strong reviews.
        |
        */

        $ratingPrior =
            (float)
            config(
                'featured_businesses.ranking.rating_prior',
                3.8
            );


        $ratingPriorWeight =
            max(
                1,
                (int)
                config(
                    'featured_businesses.ranking.rating_prior_weight',
                    5
                )
            );


        $weightedRating =
            (
                (
                    $rating
                    *
                    $reviews
                )
                +
                (
                    $ratingPrior
                    *
                    $ratingPriorWeight
                )
            )
            /
            (
                $reviews
                +
                $ratingPriorWeight
            );


        $ratingScore =
            (
                $weightedRating
                /
                5
            )
            *
            40;


        /*
        |--------------------------------------------------------------------------
        | Completed Orders — Diminishing Returns
        |--------------------------------------------------------------------------
        */

        $orderReference =
            max(
                1,
                (int)
                config(
                    'featured_businesses.ranking.completed_order_reference',
                    100
                )
            );


        $ordersScore =
            min(
                1,
                log(
                    1
                    +
                    $completedOrders
                )
                /
                log(
                    1
                    +
                    $orderReference
                )
            )
            *
            30;


        /*
        |--------------------------------------------------------------------------
        | Review Volume — Diminishing Returns
        |--------------------------------------------------------------------------
        */

        $reviewReference =
            max(
                1,
                (int)
                config(
                    'featured_businesses.ranking.review_reference',
                    50
                )
            );


        $reviewScore =
            min(
                1,
                log(
                    1
                    +
                    $reviews
                )
                /
                log(
                    1
                    +
                    $reviewReference
                )
            )
            *
            15;


        /*
        |--------------------------------------------------------------------------
        | Recent Activity
        |--------------------------------------------------------------------------
        */

        $recentReference =
            max(
                1,
                (int)
                config(
                    'featured_businesses.ranking.recent_order_reference',
                    10
                )
            );


        $recentScore =
            min(
                1,
                $recentCompletedOrders
                /
                $recentReference
            )
            *
            10;


        /*
        |--------------------------------------------------------------------------
        | Reliability
        |--------------------------------------------------------------------------
        |
        | No completed/paid history = neutral score rather than punishment.
        |
        */

        if (
            $paidOrders > 0
        ) {
            $completionRate =
                min(
                    1,
                    $completedOrders
                    /
                    $paidOrders
                );
        } else {
            $completionRate =
                0.50;
        }


        $reliabilityScore =
            $completionRate
            *
            5;


        /*
        |--------------------------------------------------------------------------
        | New Seller Discovery Boost
        |--------------------------------------------------------------------------
        */

        $newSellerBoost =
            $this->newSellerBoost(
                $seller
            );


        /*
        |--------------------------------------------------------------------------
        | Stable Daily Rotation
        |--------------------------------------------------------------------------
        |
        | Small enough not to overpower quality, but prevents identical sellers
        | from owning exactly the same position forever when scores are close.
        |
        */

        $rotationBonus =
            $this->rotationBonus(
                $seller
            );


        return round(
            $ratingScore
            +
            $ordersScore
            +
            $reviewScore
            +
            $recentScore
            +
            $reliabilityScore
            +
            $newSellerBoost
            +
            $rotationBonus,
            6
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Package Tier
    |--------------------------------------------------------------------------
    |
    | Supports both "Basic" and the older "Starter" package name.
    |
    */

    public function resolvePackageTier(
        User $seller
    ): string {
        $subscription =
            $seller
                ->activeSellerSubscription;


        $packageName =
            strtolower(
                trim(
                    (string)
                    (
                        optional(
                            $subscription
                        )->package_name
                        ?:
                        optional(
                            optional(
                                $subscription
                            )->package
                        )->name
                        ?:
                        optional(
                            optional(
                                $subscription
                            )->application
                        )->package_name
                        ?:
                        ''
                    )
                )
            );


        if (
            str_contains(
                $packageName,
                'premium'
            )
        ) {
            return 'premium';
        }


        if (
            str_contains(
                $packageName,
                'standard'
            )
        ) {
            return 'standard';
        }


        if (
            str_contains(
                $packageName,
                'basic'
            )
            ||
            str_contains(
                $packageName,
                'starter'
            )
        ) {
            return 'basic';
        }


        /*
        |--------------------------------------------------------------------------
        | Product Limit Fallback
        |--------------------------------------------------------------------------
        */

        $productLimit =
            (int)
            (
                optional(
                    $subscription
                )->product_limit
                ?:
                optional(
                    optional(
                        $subscription
                    )->package
                )->product_limit
                ?:
                optional(
                    optional(
                        $subscription
                    )->application
                )->product_limit
                ?:
                0
            );


        if (
            $productLimit >= 20
        ) {
            return 'premium';
        }


        if (
            $productLimit >= 10
        ) {
            return 'standard';
        }


        return 'basic';
    }


    /*
    |--------------------------------------------------------------------------
    | Sort One Tier
    |--------------------------------------------------------------------------
    */

    private function sortTier(
        Collection $sellers
    ): Collection {
        return $sellers
            ->sort(
                function (
                    User $a,
                    User $b
                ) {
                    $scoreCompare =
                        (float)
                        $b->directory_quality_score
                        <=>
                        (float)
                        $a->directory_quality_score;


                    if (
                        $scoreCompare !== 0
                    ) {
                        return $scoreCompare;
                    }


                    $orderCompare =
                        (int)
                        $b->completed_orders_count
                        <=>
                        (int)
                        $a->completed_orders_count;


                    if (
                        $orderCompare !== 0
                    ) {
                        return $orderCompare;
                    }


                    $ratingCompare =
                        (float)
                        $b->seller_rating
                        <=>
                        (float)
                        $a->seller_rating;


                    if (
                        $ratingCompare !== 0
                    ) {
                        return $ratingCompare;
                    }


                    $reviewCompare =
                        (int)
                        $b->seller_review_count
                        <=>
                        (int)
                        $a->seller_review_count;


                    if (
                        $reviewCompare !== 0
                    ) {
                        return $reviewCompare;
                    }


                    return
                        (int)
                        $b->id
                        <=>
                        (int)
                        $a->id;
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Build Full Recommended Sequence
    |--------------------------------------------------------------------------
    */

    private function buildRecommendedSequence(
        array $groups,
        int $perPage
    ): Collection {
        $queues = [
            'premium' =>
                $groups['premium']
                    ->values()
                    ->all(),

            'standard' =>
                $groups['standard']
                    ->values()
                    ->all(),

            'basic' =>
                $groups['basic']
                    ->values()
                    ->all(),
        ];


        $ordered =
            collect();


        while (
            $this->remainingCount(
                $queues
            )
            >
            0
        ) {
            $pageSize =
                min(
                    $perPage,
                    $this->remainingCount(
                        $queues
                    )
                );


            $targets =
                $this->targetMix(
                    $perPage
                );


            $selected = [
                'premium' => [],
                'standard' => [],
                'basic' => [],
            ];


            foreach (
                [
                    'premium',
                    'standard',
                    'basic',
                ]
                as
                $tier
            ) {
                $take =
                    min(
                        $targets[$tier],
                        count(
                            $queues[$tier]
                        )
                    );


                for (
                    $i = 0;
                    $i < $take;
                    $i++
                ) {
                    $selected[$tier][] =
                        array_shift(
                            $queues[$tier]
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Backfill Missing Tier Slots
            |--------------------------------------------------------------------------
            |
            | Premium remains the first fallback priority, then Standard, then Basic.
            |
            */

            while (
                $this->selectedCount(
                    $selected
                )
                <
                $pageSize
            ) {
                $filled =
                    false;


                foreach (
                    [
                        'premium',
                        'standard',
                        'basic',
                    ]
                    as
                    $tier
                ) {
                    if (
                        empty(
                            $queues[$tier]
                        )
                    ) {
                        continue;
                    }


                    $selected[$tier][] =
                        array_shift(
                            $queues[$tier]
                        );


                    $filled =
                        true;


                    if (
                        $this->selectedCount(
                            $selected
                        )
                        >=
                        $pageSize
                    ) {
                        break;
                    }
                }


                if (
                    !$filled
                ) {
                    break;
                }
            }


            $pageItems =
                $this->interleavePage(
                    $selected
                );


            foreach (
                $pageItems
                as
                $seller
            ) {
                $ordered->push(
                    $seller
                );
            }
        }


        return $ordered;
    }


    /*
    |--------------------------------------------------------------------------
    | Target Exposure Mix
    |--------------------------------------------------------------------------
    */

    private function targetMix(
        int $perPage
    ): array {
        $premiumRatio =
            (float)
            config(
                'featured_businesses.exposure.premium',
                0.75
            );


        $standardRatio =
            (float)
            config(
                'featured_businesses.exposure.standard',
                0.17
            );


        $premium =
            max(
                0,
                (int)
                round(
                    $perPage
                    *
                    $premiumRatio
                )
            );


        $standard =
            max(
                0,
                (int)
                round(
                    $perPage
                    *
                    $standardRatio
                )
            );


        if (
            $premium
            +
            $standard
            >
            $perPage
        ) {
            $standard =
                max(
                    0,
                    $perPage
                    -
                    $premium
                );
        }


        $basic =
            max(
                0,
                $perPage
                -
                $premium
                -
                $standard
            );


        return [
            'premium' =>
                $premium,

            'standard' =>
                $standard,

            'basic' =>
                $basic,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Interleave One Page
    |--------------------------------------------------------------------------
    |
    | For 12 results the preferred shape is:
    |
    | P P P P S P P P P S P B
    |
    | This gives Premium the majority while avoiding a visibly pay-to-win block
    | where every Standard/Basic seller is buried at the very bottom.
    |
    */

    private function interleavePage(
        array $selected
    ): array {
        $pattern = [
            'premium',
            'premium',
            'premium',
            'premium',
            'standard',
            'premium',
            'premium',
            'premium',
            'premium',
            'standard',
            'premium',
            'basic',
        ];


        $total =
            $this->selectedCount(
                $selected
            );


        $output =
            [];


        $patternIndex =
            0;


        while (
            count(
                $output
            )
            <
            $total
        ) {
            $preferredTier =
                $pattern[
                    $patternIndex
                    %
                    count(
                        $pattern
                    )
                ];


            $patternIndex++;


            if (
                !empty(
                    $selected[$preferredTier]
                )
            ) {
                $output[] =
                    array_shift(
                        $selected[$preferredTier]
                    );

                continue;
            }


            $fallbackAdded =
                false;


            foreach (
                [
                    'premium',
                    'standard',
                    'basic',
                ]
                as
                $fallbackTier
            ) {
                if (
                    empty(
                        $selected[$fallbackTier]
                    )
                ) {
                    continue;
                }


                $output[] =
                    array_shift(
                        $selected[$fallbackTier]
                    );


                $fallbackAdded =
                    true;

                break;
            }


            if (
                !$fallbackAdded
            ) {
                break;
            }
        }


        return $output;
    }


    /*
    |--------------------------------------------------------------------------
    | New Seller Boost
    |--------------------------------------------------------------------------
    */

    private function newSellerBoost(
        User $seller
    ): float {
        $days =
            max(
                1,
                (int)
                config(
                    'featured_businesses.ranking.new_seller_days',
                    30
                )
            );


        $maxBoost =
            max(
                0,
                (float)
                config(
                    'featured_businesses.ranking.new_seller_boost',
                    3
                )
            );


        $subscription =
            $seller
                ->activeSellerSubscription;


        $startedAt =
            optional(
                $subscription
            )->created_at;


        if (
            !$startedAt
        ) {
            return 0;
        }


        $ageDays =
            max(
                0,
                $startedAt
                    ->diffInDays(
                        now()
                    )
            );


        if (
            $ageDays >= $days
        ) {
            return 0;
        }


        return
            $maxBoost
            *
            (
                1
                -
                (
                    $ageDays
                    /
                    $days
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Stable Daily Rotation Bonus
    |--------------------------------------------------------------------------
    */

    private function rotationBonus(
        User $seller
    ): float {
        $maxBonus =
            max(
                0,
                (float)
                config(
                    'featured_businesses.ranking.rotation_bonus',
                    1.25
                )
            );


        if (
            $maxBonus <= 0
        ) {
            return 0;
        }


        $hash =
            sha1(
                now()
                    ->format(
                        'Y-m-d'
                    )
                .
                '|'
                .
                $seller->id
            );


        $number =
            hexdec(
                substr(
                    $hash,
                    0,
                    6
                )
            );


        $fraction =
            $number
            /
            0xFFFFFF;


        return
            $fraction
            *
            $maxBonus;
    }


    private function remainingCount(
        array $queues
    ): int {
        return
            count(
                $queues['premium']
            )
            +
            count(
                $queues['standard']
            )
            +
            count(
                $queues['basic']
            );
    }


    private function selectedCount(
        array $selected
    ): int {
        return
            count(
                $selected['premium']
            )
            +
            count(
                $selected['standard']
            )
            +
            count(
                $selected['basic']
            );
    }
}
