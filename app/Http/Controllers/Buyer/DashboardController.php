<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Models\TransactionNotification;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Current Account View
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->put(
                'account_view',
                'buyer'
            );


        /*
        |--------------------------------------------------------------------------
        | Buyer
        |--------------------------------------------------------------------------
        */

        $buyer = [

            'location' =>
                $user->city
                ?: 'Location not set',

        ];


        /*
        |--------------------------------------------------------------------------
        | Transaction Base
        |--------------------------------------------------------------------------
        */

        $buyerTransactions =
            SecureTransaction::query()

                ->where(
                    'buyer_id',
                    $user->id
                );


        $paidTransactions =
            (clone $buyerTransactions)

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                );


        $terminalStatuses = [

            SecureTransaction::STATUS_COMPLETED,

            SecureTransaction::STATUS_CANCELLED,

            SecureTransaction::STATUS_EXPIRED,

        ];


        $activeTransactions =
            (clone $paidTransactions)

                ->whereNotIn(
                    'status',
                    $terminalStatuses
                );


        /*
        |--------------------------------------------------------------------------
        | Buyer Statistics
        |--------------------------------------------------------------------------
        */

        $escrowAmount =
            (float)
            (clone $activeTransactions)
                ->sum(
                    'total_amount'
                );


        $purchasesInProgress =
            (clone $activeTransactions)
                ->count();


        $purchaseCount =
            (clone $paidTransactions)
                ->count();


        $protectedLifetime =
            (float)
            (clone $paidTransactions)
                ->sum(
                    'total_amount'
                );


        /*
        |--------------------------------------------------------------------------
        | Trust Score
        |--------------------------------------------------------------------------
        |
        | There is currently no dedicated buyer-rating table in your project.
        |
        | Because buyer and seller are the same MidPoint user account,
        | if this user has actual published seller reviews, we use that
        | account rating.
        |
        | Otherwise "New" is shown instead of inventing a fake 5.0.
        |
        */

        $publishedReviewCount =
            $user
                ->publishedSellerReviews()
                ->count();


        $trustScore =
            $publishedReviewCount > 0

                ? (float)
                    $user
                        ->publishedSellerReviews()
                        ->avg(
                            'rating'
                        )

                : null;


        $statistics = [

            'escrow' =>
                $this->money(
                    $escrowAmount
                ),

            'purchases_in_progress' =>
                $purchasesInProgress,

            'trust_score' =>
                $trustScore !== null

                    ? number_format(
                        $trustScore,
                        1
                    )

                    : 'New',

            'trust_score_suffix' =>
                $trustScore !== null
                    ? '/5'
                    : null,

            'purchases' =>
                $purchaseCount,

            'protected_lifetime' =>
                $this->compactMoney(
                    $protectedLifetime
                ),

        ];


        /*
        |--------------------------------------------------------------------------
        | Featured Transaction Relations
        |--------------------------------------------------------------------------
        */

        $featuredRelations = [

            'seller.sellerBusinessProfile',

            'seller.activeSellerSubscription.application',

            'dispute',

        ];


        /*
        |--------------------------------------------------------------------------
        | Priority 1: Delivered
        |--------------------------------------------------------------------------
        */

        $featuredTransaction =
            SecureTransaction::query()

                ->with(
                    $featuredRelations
                )

                ->where(
                    'buyer_id',
                    $user->id
                )

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->where(
                    'status',
                    SecureTransaction::STATUS_DELIVERED
                )

                ->latest(
                    'delivered_at'
                )

                ->latest(
                    'id'
                )

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Priority 2: Inspection
        |--------------------------------------------------------------------------
        */

        if (
            !$featuredTransaction
        ) {

            $featuredTransaction =
                SecureTransaction::query()

                    ->with(
                        $featuredRelations
                    )

                    ->where(
                        'buyer_id',
                        $user->id
                    )

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->where(
                        'status',
                        SecureTransaction::STATUS_INSPECTION
                    )

                    ->latest(
                        'inspection_started_at'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Priority 3: Disputed
        |--------------------------------------------------------------------------
        */

        if (
            !$featuredTransaction
        ) {

            $featuredTransaction =
                SecureTransaction::query()

                    ->with(
                        $featuredRelations
                    )

                    ->where(
                        'buyer_id',
                        $user->id
                    )

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->where(
                        'status',
                        SecureTransaction::STATUS_DISPUTED
                    )

                    ->latest(
                        'updated_at'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Priority 4: Any Active Paid Transaction
        |--------------------------------------------------------------------------
        */

        if (
            !$featuredTransaction
        ) {

            $featuredTransaction =
                SecureTransaction::query()

                    ->with(
                        $featuredRelations
                    )

                    ->where(
                        'buyer_id',
                        $user->id
                    )

                    ->where(
                        'payment_status',
                        SecureTransaction::PAYMENT_PAID
                    )

                    ->whereNotIn(
                        'status',
                        $terminalStatuses
                    )

                    ->latest(
                        'updated_at'
                    )

                    ->latest(
                        'id'
                    )

                    ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Recent Transactions
        |--------------------------------------------------------------------------
        */

        $transactions =
            SecureTransaction::query()

                ->with([

                    'seller.sellerBusinessProfile',

                    'seller.activeSellerSubscription.application',

                    'dispute',

                ])

                ->where(
                    'buyer_id',
                    $user->id
                )

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->latest(
                    'updated_at'
                )

                ->latest(
                    'id'
                )

                ->limit(
                    5
                )

                ->get()

                ->map(
                    function (
                        $transaction
                    ) {

                        return [

                            'product' =>
                                $transaction
                                    ->title,

                            'reference' =>
                                $transaction
                                    ->reference,

                            'seller' =>
                                $this->sellerBusinessName(
                                    $transaction
                                        ->seller
                                ),

                            'amount' =>
                                $this->money(
                                    (float)
                                    (
                                        $transaction
                                            ->paid_amount
                                        ?:
                                        $transaction
                                            ->total_amount
                                    )
                                ),

                            'status' =>
                                $transaction
                                    ->status_label,

                            'status_class' =>
                                $this->statusClass(
                                    $transaction
                                        ->status
                                ),

                            'url' =>
                                route(
                                    'buyer.transactions.show',
                                    $transaction
                                ),

                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $notificationBase =
            TransactionNotification::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->where(
                    'audience',
                    'buyer'
                );


        $unreadNotificationCount =
            (clone $notificationBase)

                ->whereNull(
                    'read_at'
                )

                ->count();


        $notifications =
            (clone $notificationBase)

                ->latest(
                    'created_at'
                )

                ->limit(
                    3
                )

                ->get()

                ->map(
                    function (
                        $notification
                    ) {

                        return [

                            'id' =>
                                $notification
                                    ->id,

                            'icon' =>
                                $this->notificationIcon(
                                    $notification
                                        ->type
                                ),

                            'title' =>
                                $notification
                                    ->title,

                            'message' =>
                                $notification
                                    ->message,

                            'unread' =>
                                $notification
                                    ->read_at
                                ===
                                null,

                            'url' =>
                                route(
                                    'buyer.notifications.open',
                                    $notification
                                ),

                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Real Featured Businesses
        |--------------------------------------------------------------------------
        */

        $businesses =
            User::query()

                ->where(
                    'role',
                    'user'
                )

                ->where(
                    'status',
                    true
                )

                ->where(
                    'id',
                    '!=',
                    $user->id
                )

                ->whereHas(
                    'activeSellerSubscription'
                )

                ->with([

                    'sellerBusinessProfile',

                    'activeSellerSubscription.application',

                ])

                ->withAvg(
                    'publishedSellerReviews as seller_rating',
                    'rating'
                )

                ->withCount(
                    'publishedSellerReviews as seller_review_count'
                )

                ->orderByDesc(
                    'seller_rating'
                )

                ->latest(
                    'id'
                )

                ->limit(
                    3
                )

                ->get()

                ->values()

                ->map(
                    function (
                        $seller,
                        $index
                    ) {

                        $businessName =
                            $this->sellerBusinessName(
                                $seller
                            );


                        $application =
                            $seller
                                ->activeSellerSubscription
                                ?->application;


                        return [

                            'initials' =>
                                $this->initials(
                                    $businessName
                                ),

                            'name' =>
                                $businessName,

                            'category' =>
                                $application?->category
                                ?: 'Verified business',

                            'trust' =>
                                $seller
                                    ->seller_rating
                                !==
                                null

                                    ? number_format(
                                        (float)
                                        $seller
                                            ->seller_rating,
                                        1
                                    )

                                    : 'New',

                            'style' =>
                                [
                                    'green',
                                    'purple',
                                    'orange',
                                ][
                                    $index % 3
                                ],

                            'url' =>
                                route(
                                    'featured-businesses.show',
                                    $seller
                                ),

                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'buyer.dashboard',

            compact(
                'user',
                'buyer',
                'statistics',
                'featuredTransaction',
                'transactions',
                'notifications',
                'unreadNotificationCount',
                'businesses'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    private function money(
        float $amount
    ): string {

        return
            '₦'
            .
            number_format(
                $amount,
                0
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Compact Currency
    |--------------------------------------------------------------------------
    */

    private function compactMoney(
        float $amount
    ): string {

        $absoluteAmount =
            abs(
                $amount
            );


        if (
            $absoluteAmount
            >=
            1000000
        ) {

            return
                '₦'
                .
                rtrim(
                    rtrim(
                        number_format(
                            $amount / 1000000,
                            2,
                            '.',
                            ''
                        ),
                        '0'
                    ),
                    '.'
                )
                .
                'M';
        }


        if (
            $absoluteAmount
            >=
            1000
        ) {

            return
                '₦'
                .
                rtrim(
                    rtrim(
                        number_format(
                            $amount / 1000,
                            1,
                            '.',
                            ''
                        ),
                        '0'
                    ),
                    '.'
                )
                .
                'K';
        }


        return $this->money(
            $amount
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seller Business Name
    |--------------------------------------------------------------------------
    */

    private function sellerBusinessName(
        ?User $seller
    ): string {

        if (
            !$seller
        ) {

            return 'Seller';
        }


        return
            $seller
                ->activeSellerSubscription
                ?->application
                ?->business_name
            ?:
            $seller->name
            ?:
            'Seller';
    }


    /*
    |--------------------------------------------------------------------------
    | Business Initials
    |--------------------------------------------------------------------------
    */

    private function initials(
        string $name
    ): string {

        $parts =
            collect(
                preg_split(
                    '/\s+/',
                    trim(
                        $name
                    )
                )
            )

                ->filter()

                ->values();


        if (
            $parts->isEmpty()
        ) {

            return 'B';
        }


        if (
            $parts->count()
            ===
            1
        ) {

            return strtoupper(
                substr(
                    $parts->first(),
                    0,
                    2
                )
            );
        }


        return strtoupper(

            substr(
                $parts->first(),
                0,
                1
            )

            .

            substr(
                $parts->last(),
                0,
                1
            )

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status CSS
    |--------------------------------------------------------------------------
    */

    private function statusClass(
        string $status
    ): string {

        return match (
            $status
        ) {

            SecureTransaction::STATUS_COMPLETED,
            SecureTransaction::STATUS_RELEASE_APPROVED,
            SecureTransaction::STATUS_PAYOUT_PENDING =>
                'green',


            SecureTransaction::STATUS_INSPECTION =>
                'purple',


            SecureTransaction::STATUS_PAYMENT_SECURED,
            SecureTransaction::STATUS_PREPARING_ITEM,
            SecureTransaction::STATUS_DISPATCHED,
            SecureTransaction::STATUS_IN_TRANSIT,
            SecureTransaction::STATUS_DELIVERED =>
                'amber',


            SecureTransaction::STATUS_DISPUTED =>
                'red',


            default =>
                'slate',

        };
    }


    /*
    |--------------------------------------------------------------------------
    | Notification Icon
    |--------------------------------------------------------------------------
    */

    private function notificationIcon(
        ?string $type
    ): string {

        return match (
            $type
        ) {

            'payment' =>
                'fa-money-bill-transfer',

            'dispatch' =>
                'fa-box',

            'inspection' =>
                'fa-stopwatch',

            'dispute' =>
                'fa-scale-balanced',

            default =>
                'fa-bell',

        };
    }
}