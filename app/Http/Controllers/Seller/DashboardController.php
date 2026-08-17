<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Models\TransactionNotification;
use Illuminate\Http\Request;
use App\Models\SellerWallet;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ) {
        $user = $request->user();

        $request
            ->session()
            ->put(
                'account_view',
                'seller'
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Identity
        |--------------------------------------------------------------------------
        */

        $user->loadMissing([
            'sellerBusinessProfile',
            'activeSellerSubscription.application',
        ]);


        $application =
            $user
                ->activeSellerSubscription
                ?->application;


        $businessProfile =
            $user
                ->sellerBusinessProfile;


        $seller = [

            'business_name' =>
                $application?->business_name
                ?: $user->name,

            'location' =>
                $businessProfile?->location
                ?: $application?->location
                ?: $user->city
                ?: 'Location not set',

        ];
        $wallet =
            SellerWallet::query()
                ->where(
                    'seller_id',
                    $user->id
                )
                ->first();


        $walletSummary = [

            /*
            |--------------------------------------------------------------------------
            | Available For Future Withdrawal
            |--------------------------------------------------------------------------
            */

            'available_balance' =>
                (float) (
                    $wallet?->available_balance
                    ?: 0
                ),

            'formatted_available_balance' =>
                $this->money(
                    (float) (
                        $wallet?->available_balance
                        ?: 0
                    )
                ),


            /*
            |--------------------------------------------------------------------------
            | Lifetime Released
            |--------------------------------------------------------------------------
            */

            'total_credited' =>
                (float) (
                    $wallet?->total_credited
                    ?: 0
                ),

            'formatted_total_credited' =>
                $this->money(
                    (float) (
                        $wallet?->total_credited
                        ?: 0
                    )
                ),


            /*
            |--------------------------------------------------------------------------
            | Reserved For Future Withdrawal Feature
            |--------------------------------------------------------------------------
            */

            'pending_withdrawal_balance' =>
                (float) (
                    $wallet?->pending_withdrawal_balance
                    ?: 0
                ),

            'formatted_pending_withdrawal_balance' =>
                $this->money(
                    (float) (
                        $wallet?->pending_withdrawal_balance
                        ?: 0
                    )
                ),


            'currency' =>
                $wallet?->currency
                ?: 'NGN',

        ];

        /*
        |--------------------------------------------------------------------------
        | Transaction Base
        |--------------------------------------------------------------------------
        */

        $sellerTransactions =
            SecureTransaction::query()
                ->where(
                    'seller_id',
                    $user->id
                );


        $paidTransactions =
            (clone $sellerTransactions)
                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                );


        $terminalStatuses = [

            SecureTransaction::STATUS_COMPLETED,

            SecureTransaction::STATUS_CANCELLED,

            SecureTransaction::STATUS_EXPIRED,

        ];


        $activePaidTransactions =
            (clone $paidTransactions)
                ->whereNotIn(
                    'status',
                    $terminalStatuses
                );


        /*
        |--------------------------------------------------------------------------
        | Featured Transaction
        |--------------------------------------------------------------------------
        |
        | First priority:
        |
        | Find an order where seller can actually perform the next
        | fulfilment action.
        |
        */

        $sellerActionStatuses = [

            SecureTransaction::STATUS_PAYMENT_SECURED,

            SecureTransaction::STATUS_PREPARING_ITEM,

            SecureTransaction::STATUS_DISPATCHED,

            SecureTransaction::STATUS_IN_TRANSIT,

        ];


        $featuredTransaction =
            SecureTransaction::query()

                ->with([
                    'buyer',
                    'dispute',
                ])

                ->where(
                    'seller_id',
                    $user->id
                )

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->whereIn(
                    'status',
                    $sellerActionStatuses
                )

                ->latest(
                    'paid_at'
                )

                ->latest(
                    'id'
                )

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Fallback Active Transaction
        |--------------------------------------------------------------------------
        */

        if (
            !$featuredTransaction
        ) {

            $featuredTransaction =
                SecureTransaction::query()

                    ->with([
                        'buyer',
                        'dispute',
                    ])

                    ->where(
                        'seller_id',
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
        | Held In Escrow
        |--------------------------------------------------------------------------
        */

        $heldInEscrow =
            (float)
            (clone $activePaidTransactions)
                ->sum(
                    'total_amount'
                );


        $activeDeals =
            (clone $activePaidTransactions)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------------------
        */

        $monthStart =
            now()
                ->copy()
                ->startOfMonth();


        $monthEnd =
            now()
                ->copy()
                ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | Previous Month
        |--------------------------------------------------------------------------
        */

        $previousMonthStart =
            now()
                ->copy()
                ->subMonthNoOverflow()
                ->startOfMonth();


        $previousMonthEnd =
            now()
                ->copy()
                ->subMonthNoOverflow()
                ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | Current Month Released Transactions
        |--------------------------------------------------------------------------
        |
        | This data also powers the revenue chart.
        |
        */

        $monthlyReleasedTransactions =
            (clone $sellerTransactions)

                ->whereBetween(
                    'funds_released_at',
                    [
                        $monthStart,
                        $monthEnd,
                    ]
                )

                ->get([
                    'id',
                    'funds_released_at',
                    'seller_net_amount',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Released This Month
        |--------------------------------------------------------------------------
        */

        $releasedThisMonth =
            (float)
            $monthlyReleasedTransactions
                ->sum(
                    fn ($transaction) =>
                        (float)
                        $transaction
                            ->seller_net_amount
                );


        /*
        |--------------------------------------------------------------------------
        | Previous Month Released
        |--------------------------------------------------------------------------
        */

        $releasedPreviousMonth =
            (float)
            (clone $sellerTransactions)

                ->whereBetween(
                    'funds_released_at',
                    [
                        $previousMonthStart,
                        $previousMonthEnd,
                    ]
                )

                ->sum(
                    'seller_net_amount'
                );


        /*
        |--------------------------------------------------------------------------
        | Comparison
        |--------------------------------------------------------------------------
        */

        $releasedComparison =
            $this->monthComparisonText(
                $releasedThisMonth,
                $releasedPreviousMonth,
                $previousMonthStart->format(
                    'M'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Charges Paid
        |--------------------------------------------------------------------------
        */

        $serviceFeesPaid =
            (float)
            (clone $sellerTransactions)

                ->whereNotNull(
                    'funds_released_at'
                )

                ->sum(
                    'service_fee_amount'
                );


        $vatPaid =
            (float)
            (clone $sellerTransactions)

                ->whereNotNull(
                    'funds_released_at'
                )

                ->sum(
                    'vat_amount'
                );


        $chargesPaid =
            $serviceFeesPaid
            +
            $vatPaid;


        /*
        |--------------------------------------------------------------------------
        | Completed Deals
        |--------------------------------------------------------------------------
        */

        $completedDeals =
            (clone $sellerTransactions)

                ->where(
                    'status',
                    SecureTransaction::STATUS_COMPLETED
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Seller Trust Score
        |--------------------------------------------------------------------------
        |
        | Uses actual published seller reviews.
        |
        */

        $reviewCount =
            $user
                ->publishedSellerReviews()
                ->count();


        $ratingAverage =
            $reviewCount > 0

                ? (float)
                    $user
                        ->publishedSellerReviews()
                        ->avg(
                            'rating'
                        )

                : null;


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            [
                'label' =>
                    'Held in escrow',

                'value' =>
                    $this->money(
                        $heldInEscrow
                    ),

                'note' =>
                    $activeDeals
                    .
                    ' active '
                    .
                    (
                        $activeDeals === 1
                            ? 'deal'
                            : 'deals'
                    ),

                'class' =>
                    'positive',
            ],


            [
                'label' =>
                    'Released this month',

                'value' =>
                    $this->compactMoney(
                        $releasedThisMonth
                    ),

                'note' =>
                    $releasedComparison[
                        'text'
                    ],

                'class' =>
                    $releasedComparison[
                        'class'
                    ],
            ],


            [
                'label' =>
                    'Charges paid',

                'value' =>
                    $this->money(
                        $chargesPaid
                    ),

                'note' =>
                    'Service fees + VAT',

                'class' =>
                    '',
            ],


            [
                'label' =>
                    'Trust score',

                'value' =>
                    $ratingAverage !== null

                        ? number_format(
                            $ratingAverage,
                            1
                        )

                        : 'New',

                'suffix' =>
                    $ratingAverage !== null
                        ? '/5'
                        : null,

                'note' =>
                    $completedDeals
                    .
                    ' completed '
                    .
                    (
                        $completedDeals === 1
                            ? 'deal'
                            : 'deals'
                    ),

                'class' =>
                    'positive',
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Revenue Chart
        |--------------------------------------------------------------------------
        |
        | Four bars:
        |
        | Week 1 = days 1 - 7
        | Week 2 = days 8 - 14
        | Week 3 = days 15 - 21
        | Week 4 = days 22 - end
        |
        */

        $weekValues = [

            1 => 0.0,

            2 => 0.0,

            3 => 0.0,

            4 => 0.0,

        ];


        foreach (
            $monthlyReleasedTransactions
            as
            $transaction
        ) {

            if (
                !$transaction
                    ->funds_released_at
            ) {
                continue;
            }


            $weekNumber =
                min(
                    4,

                    intdiv(

                        max(
                            0,
                            $transaction
                                ->funds_released_at
                                ->day
                            -
                            1
                        ),

                        7

                    )
                    +
                    1
                );


            $weekValues[
                $weekNumber
            ] +=
                (float)
                $transaction
                    ->seller_net_amount;
        }


        /*
        |--------------------------------------------------------------------------
        | Highest Chart Value
        |--------------------------------------------------------------------------
        */

        $chartMaximum =
            max(
                1,
                ...array_values(
                    $weekValues
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Current Week
        |--------------------------------------------------------------------------
        */

        $currentWeek =
            min(
                4,

                intdiv(
                    now()->day - 1,
                    7
                )
                +
                1
            );


        $revenueChart = [];


        foreach (
            $weekValues
            as
            $week => $value
        ) {

            $revenueChart[] = [

                'label' =>
                    'Wk ' . $week,

                'value' =>
                    $value,

                'formatted' =>
                    $this->money(
                        $value
                    ),

                'height' =>
                    $value > 0

                        ? max(
                            8,

                            round(
                                (
                                    $value
                                    /
                                    $chartMaximum
                                )
                                *
                                100,
                                2
                            )
                        )

                        : 0,

                'strong' =>
                    $week === $currentWeek,

            ];
        }


        $revenueSummary = [

            'month' =>
                $monthStart->format(
                    'F'
                ),

            'total' =>
                $releasedThisMonth,

            'formatted_total' =>
                $this->money(
                    $releasedThisMonth
                ),

            'bars' =>
                $revenueChart,

        ];


        /*
        |--------------------------------------------------------------------------
        | Recent Transactions
        |--------------------------------------------------------------------------
        */

        $transactions =
            SecureTransaction::query()

                ->with([
                    'buyer',
                    'dispute',
                ])

                ->where(
                    'seller_id',
                    $user->id
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

                            'buyer' =>
                                $transaction
                                    ->buyer
                                    ?->name
                                ?:
                                $transaction
                                    ->buyer_email,

                            'amount' =>
                                $this->money(
                                    (float)
                                    $transaction
                                        ->total_amount
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
                                    'seller.transactions.show',
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
                    'seller'
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
                                    'seller.notifications.open',
                                    $notification
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
            'seller.dashboard',

            compact(
                'user',
                'seller',
                'featuredTransaction',
                'statistics',
                'transactions',
                'notifications',
                'walletSummary',
                'unreadNotificationCount',
                'revenueSummary'
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
    | Monthly Comparison
    |--------------------------------------------------------------------------
    */

    private function monthComparisonText(
        float $current,
        float $previous,
        string $previousMonthName
    ): array {

        if (
            $previous <= 0
        ) {

            if (
                $current > 0
            ) {

                return [

                    'text' =>
                        'New payouts this month',

                    'class' =>
                        'positive',

                ];
            }


            return [

                'text' =>
                    'No payouts yet',

                'class' =>
                    '',

            ];
        }


        $percentage =
            (
                (
                    $current
                    -
                    $previous
                )
                /
                $previous
            )
            *
            100;


        $rounded =
            (int)
            round(
                abs(
                    $percentage
                )
            );


        if (
            $percentage > 0
        ) {

            return [

                'text' =>
                    '▲ '
                    .
                    $rounded
                    .
                    '% vs '
                    .
                    $previousMonthName,

                'class' =>
                    'positive',

            ];
        }


        if (
            $percentage < 0
        ) {

            return [

                'text' =>
                    '▼ '
                    .
                    $rounded
                    .
                    '% vs '
                    .
                    $previousMonthName,

                'class' =>
                    'negative',

            ];
        }


        return [

            'text' =>
                'No change vs '
                .
                $previousMonthName,

            'class' =>
                '',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Status CSS
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