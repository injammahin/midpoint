<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\SecureTransaction;
use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerSubscription;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\TransactionDispute;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Staff Dashboard
        |--------------------------------------------------------------------------
        */

        if (
            $request
                ->user()
                ->isAdminStaff()
        ) {

            return redirect()
                ->route(
                    'admin.staff-dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Super Admin Only
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $request
                ->user()
                ->isAdmin(),
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Chart Period
        |--------------------------------------------------------------------------
        */

        $period =
            (int)
            $request->get(
                'period',
                12
            );


        $period =
            in_array(
                $period,
                [
                    6,
                    12,
                ],
                true
            )
                ? $period
                : 12;


        $chartStart =
            now()
                ->copy()
                ->startOfMonth()
                ->subMonths(
                    $period - 1
                );


        $chartEnd =
            now()
                ->copy()
                ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | Base Queries
        |--------------------------------------------------------------------------
        */

        $paidQuery =
            $this
                ->paidTransactions();


        $walletReleasedQuery =
            $this
                ->walletReleasedTransactions();


        /*
        |--------------------------------------------------------------------------
        | BUYER PAYMENTS
        |--------------------------------------------------------------------------
        */

        $totalBuyerPaid =
            $this
                ->sumBuyerPaid(
                    clone $paidQuery
                );


        /*
        |--------------------------------------------------------------------------
        | RELEASED TO SELLER WALLET
        |--------------------------------------------------------------------------
        |
        | This is NOT a bank withdrawal.
        |
        | It represents money that has finished escrow and has been credited
        | to the seller's Midpoint wallet.
        |
        */

        $totalSellerWalletReleased =
            (float)
            SellerWalletTransaction::query()

                ->where(
                    'type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->where(
                    'direction',
                    SellerWalletTransaction::DIRECTION_CREDIT
                )

                ->sum(
                    'amount'
                );


        $sellerWalletReleaseCount =
            SellerWalletTransaction::query()

                ->where(
                    'type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | SUCCESSFULLY WITHDRAWN TO SELLER BANKS
        |--------------------------------------------------------------------------
        */

        $totalSellerWithdrawn =
            (float)
            SellerWithdrawal::query()

                ->where(
                    'status',
                    SellerWithdrawal::STATUS_SUCCESSFUL
                )

                ->sum(
                    'amount'
                );


        $successfulWithdrawalCount =
            SellerWithdrawal::query()

                ->where(
                    'status',
                    SellerWithdrawal::STATUS_SUCCESSFUL
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | PENDING WITHDRAWALS
        |--------------------------------------------------------------------------
        */

        $pendingWithdrawalStatuses = [
            SellerWithdrawal::STATUS_PENDING,
            SellerWithdrawal::STATUS_PROCESSING,
            SellerWithdrawal::STATUS_OTP,
        ];


        $pendingWithdrawalAmount =
            (float)
            SellerWithdrawal::query()

                ->whereIn(
                    'status',
                    $pendingWithdrawalStatuses
                )

                ->sum(
                    'amount'
                );


        $pendingWithdrawalCount =
            SellerWithdrawal::query()

                ->whereIn(
                    'status',
                    $pendingWithdrawalStatuses
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | FAILED / REVERSED WITHDRAWALS
        |--------------------------------------------------------------------------
        */

        $failedWithdrawalCount =
            SellerWithdrawal::query()

                ->whereIn(
                    'status',
                    [
                        SellerWithdrawal::STATUS_FAILED,
                        SellerWithdrawal::STATUS_REVERSED,
                    ]
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | SELLER WALLET LIABILITY
        |--------------------------------------------------------------------------
        |
        | Money already belonging to sellers but still held by Midpoint.
        |
        | available_balance
        | +
        | pending_withdrawal_balance
        |
        */

        $sellerAvailableBalance =
            (float)
            SellerWallet::query()
                ->sum(
                    'available_balance'
                );


        $sellerPendingWalletBalance =
            (float)
            SellerWallet::query()
                ->sum(
                    'pending_withdrawal_balance'
                );


        $sellerWalletLiability =
            round(
                $sellerAvailableBalance
                +
                $sellerPendingWalletBalance,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL WALLET CREDITED / WITHDRAWN
        |--------------------------------------------------------------------------
        */

        $walletTotalCredited =
            (float)
            SellerWallet::query()
                ->sum(
                    'total_credited'
                );


        $walletTotalWithdrawn =
            (float)
            SellerWallet::query()
                ->sum(
                    'total_withdrawn'
                );


        /*
        |--------------------------------------------------------------------------
        | CURRENTLY PROTECTED / ESCROW
        |--------------------------------------------------------------------------
        |
        | Paid buyer transactions that have NOT yet received a posted
        | transaction_release wallet entry.
        |
        | Once a transaction is credited into seller wallet it is no longer
        | escrow protected. It becomes seller wallet liability instead.
        |
        */

        $protectedQuery =
            SecureTransaction::query()

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->whereNotIn(
                    'status',
                    [
                        SecureTransaction::STATUS_CANCELLED,
                        SecureTransaction::STATUS_EXPIRED,
                    ]
                )

                ->whereDoesntHave(
                    'walletRelease',
                    function (
                        $query
                    ) {

                        $query
                            ->where(
                                'status',
                                SellerWalletTransaction::STATUS_POSTED
                            );
                    }
                );


        $escrowProtected =
            $this
                ->sumBuyerPaid(
                    clone $protectedQuery
                );


        $protectedTransactionCount =
            (clone $protectedQuery)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | PLATFORM SERVICE FEE
        |--------------------------------------------------------------------------
        |
        | Service fee becomes realized when the seller transaction amount has
        | actually been released to the seller wallet.
        |
        */

        $serviceFeeRevenue =
            $this
                ->sumColumn(
                    clone $walletReleasedQuery,
                    'service_fee_amount'
                );


        $vatCollected =
            $this
                ->sumColumn(
                    clone $walletReleasedQuery,
                    'vat_amount'
                );


        /*
        |--------------------------------------------------------------------------
        | SELLER PACKAGE REVENUE
        |--------------------------------------------------------------------------
        */

        $packageRevenue =
            (float)
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->sum(
                    'amount'
                );


        $packagePurchases =
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->count();


        $packageCustomers =
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->distinct()

                ->count(
                    'user_id'
                );


        /*
        |--------------------------------------------------------------------------
        | PLATFORM PROFIT
        |--------------------------------------------------------------------------
        |
        | VAT is not counted as platform profit.
        |
        */

        $grossPlatformProfit =
            round(
                $serviceFeeRevenue
                +
                $packageRevenue,
                2
            );


        $grossBusinessVolume =
            round(
                $totalBuyerPaid
                +
                $packageRevenue,
                2
            );


        $grossProfitMargin =
            $grossBusinessVolume > 0

                ? round(
                    (
                        $grossPlatformProfit
                        /
                        $grossBusinessVolume
                    )
                    *
                    100,
                    2
                )

                : 0.0;


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION COUNTS
        |--------------------------------------------------------------------------
        */

        $paidTransactionCount =
            (clone $paidQuery)
                ->count();


        $completedTransactions =
            (clone $paidQuery)

                ->where(
                    'status',
                    SecureTransaction::STATUS_COMPLETED
                )

                ->count();


        $disputedTransactions =
            (clone $paidQuery)

                ->where(
                    'status',
                    SecureTransaction::STATUS_DISPUTED
                )

                ->count();


        $cancelledTransactions =
            (clone $paidQuery)

                ->where(
                    'status',
                    SecureTransaction::STATUS_CANCELLED
                )

                ->count();


        $runningTransactions =
            (clone $paidQuery)

                ->whereNotIn(
                    'status',
                    [
                        SecureTransaction::STATUS_COMPLETED,
                        SecureTransaction::STATUS_CANCELLED,
                        SecureTransaction::STATUS_EXPIRED,
                        SecureTransaction::STATUS_DISPUTED,
                    ]
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Awaiting Wallet Release
        |--------------------------------------------------------------------------
        */

        $awaitingWalletReleaseTransactions =
            (clone $protectedQuery)
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Unpaid Generated Links
        |--------------------------------------------------------------------------
        */

        $unpaidGeneratedLinks =
            SecureTransaction::query()

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_UNPAID
                )

                ->where(
                    'status',
                    SecureTransaction::STATUS_AWAITING_PAYMENT
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        $totalUsers =
            User::query()

                ->where(
                    'role',
                    'user'
                )

                ->count();


        $activeUsers =
            User::query()

                ->where(
                    'role',
                    'user'
                )

                ->where(
                    'status',
                    true
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Paying Buyers
        |--------------------------------------------------------------------------
        */

        $paidBuyers =
            SecureTransaction::query()

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->whereNotNull(
                    'buyer_id'
                )

                ->distinct()

                ->count(
                    'buyer_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Active Sellers
        |--------------------------------------------------------------------------
        */

        $activeSellers =
            SellerSubscription::query()

                ->active()

                ->distinct()

                ->count(
                    'user_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Seller Applications
        |--------------------------------------------------------------------------
        */

        $pendingSellerApplications =
            SellerApplication::query()

                ->where(
                    'status',
                    SellerApplication::STATUS_SUBMITTED
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | DISPUTES / SUPPORT
        |--------------------------------------------------------------------------
        */

        $openDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    'open'
                )

                ->count();


        $underReviewDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    'under_review'
                )

                ->count();


        $awaitingBuyerDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    'awaiting_buyer'
                )

                ->count();


        $awaitingSellerDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    'awaiting_seller'
                )

                ->count();


        $resolvedDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    'resolved'
                )

                ->count();


        $unreadInquiries =
            ContactMessage::query()

                ->whereNull(
                    'read_at'
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | MONTH-OVER-MONTH
        |--------------------------------------------------------------------------
        */

        $currentStart =
            now()
                ->copy()
                ->startOfMonth();


        $currentEnd =
            now()
                ->copy()
                ->endOfMonth();


        $previousStart =
            now()
                ->copy()
                ->subMonthNoOverflow()
                ->startOfMonth();


        $previousEnd =
            now()
                ->copy()
                ->subMonthNoOverflow()
                ->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | Buyer Payment Growth
        |--------------------------------------------------------------------------
        */

        $currentBuyerPaid =
            $this
                ->sumBuyerPaid(

                    $this
                        ->paidTransactions()

                        ->whereBetween(
                            'paid_at',
                            [
                                $currentStart,
                                $currentEnd,
                            ]
                        )
                );


        $previousBuyerPaid =
            $this
                ->sumBuyerPaid(

                    $this
                        ->paidTransactions()

                        ->whereBetween(
                            'paid_at',
                            [
                                $previousStart,
                                $previousEnd,
                            ]
                        )
                );


        /*
        |--------------------------------------------------------------------------
        | Package Revenue Growth
        |--------------------------------------------------------------------------
        */

        $currentPackageRevenue =
            (float)
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->whereBetween(
                    'paid_at',
                    [
                        $currentStart,
                        $currentEnd,
                    ]
                )

                ->sum(
                    'amount'
                );


        $previousPackageRevenue =
            (float)
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->whereBetween(
                    'paid_at',
                    [
                        $previousStart,
                        $previousEnd,
                    ]
                )

                ->sum(
                    'amount'
                );


        /*
        |--------------------------------------------------------------------------
        | Realized Service Fees Growth
        |--------------------------------------------------------------------------
        */

        $currentServiceFees =
            $this
                ->sumRealizedServiceFeesBetween(
                    $currentStart,
                    $currentEnd
                );


        $previousServiceFees =
            $this
                ->sumRealizedServiceFeesBetween(
                    $previousStart,
                    $previousEnd
                );


        $currentProfit =
            $currentServiceFees
            +
            $currentPackageRevenue;


        $previousProfit =
            $previousServiceFees
            +
            $previousPackageRevenue;


        /*
        |--------------------------------------------------------------------------
        | MONTHLY BUYER PAYMENTS
        |--------------------------------------------------------------------------
        */

        $paidByMonth =
            $this
                ->paidTransactions()

                ->whereNotNull(
                    'paid_at'
                )

                ->whereBetween(
                    'paid_at',
                    [
                        $chartStart,
                        $chartEnd,
                    ]
                )

                ->selectRaw(
                    "DATE_FORMAT(paid_at, '%Y-%m') AS month"
                )

                ->selectRaw(
                    'COUNT(*) AS transaction_count'
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(
                            COALESCE(
                                paid_amount,
                                total_amount
                            )
                        ),
                        0
                    ) AS buyer_paid
                    '
                )

                ->groupBy(
                    'month'
                )

                ->get()

                ->keyBy(
                    'month'
                );


        /*
        |--------------------------------------------------------------------------
        | MONTHLY WALLET RELEASES
        |--------------------------------------------------------------------------
        */

        $walletReleaseDate =
            DB::raw(
                'COALESCE(processed_at, created_at)'
            );


        $walletReleasedByMonth =
            SellerWalletTransaction::query()

                ->where(
                    'type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->whereBetween(
                    $walletReleaseDate,
                    [
                        $chartStart,
                        $chartEnd,
                    ]
                )

                ->selectRaw(
                    "
                    DATE_FORMAT(
                        COALESCE(
                            processed_at,
                            created_at
                        ),
                        '%Y-%m'
                    ) AS month
                    "
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS seller_wallet_released
                    '
                )

                ->groupBy(
                    'month'
                )

                ->get()

                ->keyBy(
                    'month'
                );


        /*
        |--------------------------------------------------------------------------
        | MONTHLY SUCCESSFUL BANK WITHDRAWALS
        |--------------------------------------------------------------------------
        */

        $withdrawnByMonth =
            SellerWithdrawal::query()

                ->where(
                    'status',
                    SellerWithdrawal::STATUS_SUCCESSFUL
                )

                ->whereNotNull(
                    'completed_at'
                )

                ->whereBetween(
                    'completed_at',
                    [
                        $chartStart,
                        $chartEnd,
                    ]
                )

                ->selectRaw(
                    "DATE_FORMAT(completed_at, '%Y-%m') AS month"
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS seller_withdrawn
                    '
                )

                ->groupBy(
                    'month'
                )

                ->get()

                ->keyBy(
                    'month'
                );


        /*
        |--------------------------------------------------------------------------
        | MONTHLY REALIZED SERVICE FEES
        |--------------------------------------------------------------------------
        */

        $serviceFeesByMonth =
            SellerWalletTransaction::query()

                ->join(
                    'secure_transactions',
                    'secure_transactions.id',
                    '=',
                    'seller_wallet_transactions.secure_transaction_id'
                )

                ->where(
                    'seller_wallet_transactions.type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'seller_wallet_transactions.status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->whereBetween(
                    DB::raw(
                        '
                        COALESCE(
                            seller_wallet_transactions.processed_at,
                            seller_wallet_transactions.created_at
                        )
                        '
                    ),
                    [
                        $chartStart,
                        $chartEnd,
                    ]
                )

                ->selectRaw(
                    "
                    DATE_FORMAT(
                        COALESCE(
                            seller_wallet_transactions.processed_at,
                            seller_wallet_transactions.created_at
                        ),
                        '%Y-%m'
                    ) AS month
                    "
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(
                            COALESCE(
                                secure_transactions.service_fee_amount,
                                0
                            )
                        ),
                        0
                    ) AS service_fee_revenue
                    '
                )

                ->groupBy(
                    'month'
                )

                ->get()

                ->keyBy(
                    'month'
                );


        /*
        |--------------------------------------------------------------------------
        | MONTHLY PACKAGE REVENUE
        |--------------------------------------------------------------------------
        */

        $packagesByMonth =
            SellerInvoice::query()

                ->where(
                    'status',
                    'paid'
                )

                ->whereNotNull(
                    'paid_at'
                )

                ->whereBetween(
                    'paid_at',
                    [
                        $chartStart,
                        $chartEnd,
                    ]
                )

                ->selectRaw(
                    "DATE_FORMAT(paid_at, '%Y-%m') AS month"
                )

                ->selectRaw(
                    'COUNT(*) AS purchase_count'
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS package_revenue
                    '
                )

                ->groupBy(
                    'month'
                )

                ->get()

                ->keyBy(
                    'month'
                );


        /*
        |--------------------------------------------------------------------------
        | MAIN CHART
        |--------------------------------------------------------------------------
        */

        $chart = [
            'labels' => [],

            'buyer_paid' => [],

            'seller_wallet_released' => [],

            'seller_withdrawn' => [],

            'service_fee_revenue' => [],

            'package_revenue' => [],

            'platform_profit' => [],

            'transaction_count' => [],
        ];


        $cursor =
            $chartStart
                ->copy();


        while (
            $cursor->lte(
                $chartEnd
            )
        ) {

            $key =
                $cursor
                    ->format(
                        'Y-m'
                    );


            $paid =
                $paidByMonth
                    ->get(
                        $key
                    );


            $walletReleased =
                $walletReleasedByMonth
                    ->get(
                        $key
                    );


            $withdrawn =
                $withdrawnByMonth
                    ->get(
                        $key
                    );


            $serviceFee =
                $serviceFeesByMonth
                    ->get(
                        $key
                    );


            $package =
                $packagesByMonth
                    ->get(
                        $key
                    );


            $fee =
                (float) (
                    $serviceFee
                        ->service_fee_revenue
                    ??
                    0
                );


            $packageIncome =
                (float) (
                    $package
                        ->package_revenue
                    ??
                    0
                );


            $chart[
                'labels'
            ][] =
                $cursor
                    ->format(
                        'M Y'
                    );


            $chart[
                'buyer_paid'
            ][] =
                round(
                    (float) (
                        $paid
                            ->buyer_paid
                        ??
                        0
                    ),
                    2
                );


            $chart[
                'seller_wallet_released'
            ][] =
                round(
                    (float) (
                        $walletReleased
                            ->seller_wallet_released
                        ??
                        0
                    ),
                    2
                );


            $chart[
                'seller_withdrawn'
            ][] =
                round(
                    (float) (
                        $withdrawn
                            ->seller_withdrawn
                        ??
                        0
                    ),
                    2
                );


            $chart[
                'service_fee_revenue'
            ][] =
                round(
                    $fee,
                    2
                );


            $chart[
                'package_revenue'
            ][] =
                round(
                    $packageIncome,
                    2
                );


            $chart[
                'platform_profit'
            ][] =
                round(
                    $fee
                    +
                    $packageIncome,
                    2
                );


            $chart[
                'transaction_count'
            ][] =
                (int) (
                    $paid
                        ->transaction_count
                    ??
                    0
                );


            $cursor
                ->addMonthNoOverflow();
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION STATUS CHART
        |--------------------------------------------------------------------------
        */

        $transactionStatusChart = [
            'labels' => [
                'Running',
                'Completed',
                'Disputed',
                'Cancelled',
            ],

            'series' => [
                $runningTransactions,
                $completedTransactions,
                $disputedTransactions,
                $cancelledTransactions,
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | WITHDRAWAL STATUS CHART
        |--------------------------------------------------------------------------
        */

        $withdrawalStatusChart = [
            'labels' => [
                'Successful',
                'Processing',
                'Failed / Reversed',
            ],

            'series' => [
                $successfulWithdrawalCount,
                $pendingWithdrawalCount,
                $failedWithdrawalCount,
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | PROFIT COMPOSITION
        |--------------------------------------------------------------------------
        */

        $profitCompositionChart = [
            'labels' => [
                'Transaction service fees',
                'Seller package revenue',
            ],

            'series' => [
                round(
                    $serviceFeeRevenue,
                    2
                ),

                round(
                    $packageRevenue,
                    2
                ),
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | PACKAGE MIX
        |--------------------------------------------------------------------------
        */

        $packageMix =
            SellerInvoice::query()

                ->join(
                    'seller_applications',
                    'seller_applications.id',
                    '=',
                    'seller_invoices.seller_application_id'
                )

                ->where(
                    'seller_invoices.status',
                    'paid'
                )

                ->select(
                    'seller_applications.package_name'
                )

                ->selectRaw(
                    'COUNT(*) AS purchases'
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(
                            seller_invoices.amount
                        ),
                        0
                    ) AS revenue
                    '
                )

                ->groupBy(
                    'seller_applications.package_name'
                )

                ->orderByDesc(
                    'revenue'
                )

                ->get();


        $packageChart = [
            'labels' =>
                $packageMix
                    ->pluck(
                        'package_name'
                    )
                    ->values()
                    ->all(),

            'series' =>
                $packageMix
                    ->map(
                        fn ($item) =>
                            (int)
                            $item
                                ->purchases
                    )
                    ->values()
                    ->all(),

            'revenue' =>
                $packageMix
                    ->map(
                        fn ($item) =>
                            round(
                                (float)
                                $item
                                    ->revenue,
                                2
                            )
                    )
                    ->values()
                    ->all(),
        ];


        /*
        |--------------------------------------------------------------------------
        | RECENT PAID TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $recentTransactions =
            SecureTransaction::query()

                ->with([
                    'buyer',
                    'seller',
                    'successfulPayment',
                    'dispute',
                    'walletRelease',
                ])

                ->where(
                    'payment_status',
                    SecureTransaction::PAYMENT_PAID
                )

                ->orderByDesc(
                    'paid_at'
                )

                ->orderByDesc(
                    'id'
                )

                ->limit(
                    7
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | RECENT SELLER WITHDRAWALS
        |--------------------------------------------------------------------------
        */

        $recentWithdrawals =
            SellerWithdrawal::query()

                ->with([
                    'seller',
                ])

                ->latest(
                    'id'
                )

                ->limit(
                    7
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | RECENT PACKAGE PURCHASES
        |--------------------------------------------------------------------------
        */

        $recentPackagePurchases =
            SellerInvoice::query()

                ->with([
                    'user',
                    'application',
                ])

                ->where(
                    'status',
                    'paid'
                )

                ->orderByDesc(
                    'paid_at'
                )

                ->orderByDesc(
                    'id'
                )

                ->limit(
                    5
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | TOP SELLERS BY WALLET RELEASE
        |--------------------------------------------------------------------------
        */

        $topSellerRows =
            SellerWalletTransaction::query()

                ->where(
                    'type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->whereNotNull(
                    'seller_id'
                )

                ->select(
                    'seller_id'
                )

                ->selectRaw(
                    'COUNT(*) AS transaction_count'
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS wallet_released
                    '
                )

                ->groupBy(
                    'seller_id'
                )

                ->orderByDesc(
                    'wallet_released'
                )

                ->limit(
                    5
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Withdrawn Per Top Seller
        |--------------------------------------------------------------------------
        */

        $topSellerWithdrawals =
            SellerWithdrawal::query()

                ->whereIn(
                    'seller_id',
                    $topSellerRows
                        ->pluck(
                            'seller_id'
                        )
                )

                ->where(
                    'status',
                    SellerWithdrawal::STATUS_SUCCESSFUL
                )

                ->select(
                    'seller_id'
                )

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS withdrawn
                    '
                )

                ->groupBy(
                    'seller_id'
                )

                ->get()

                ->keyBy(
                    'seller_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Seller Users
        |--------------------------------------------------------------------------
        */

        $sellerUsers =
            User::query()

                ->whereIn(
                    'id',
                    $topSellerRows
                        ->pluck(
                            'seller_id'
                        )
                )

                ->get()

                ->keyBy(
                    'id'
                );


        /*
        |--------------------------------------------------------------------------
        | Final Top Seller Array
        |--------------------------------------------------------------------------
        */

        $topSellers =
            $topSellerRows
                ->map(
                    function (
                        $row
                    ) use (
                        $sellerUsers,
                        $topSellerWithdrawals
                    ) {

                        $seller =
                            $sellerUsers
                                ->get(
                                    $row->seller_id
                                );


                        $withdrawal =
                            $topSellerWithdrawals
                                ->get(
                                    $row->seller_id
                                );


                        return [
                            'id' =>
                                (int)
                                $row
                                    ->seller_id,

                            'name' =>
                                $seller
                                    ?->name
                                ??
                                'Seller #'
                                .
                                $row
                                    ->seller_id,

                            'email' =>
                                $seller
                                    ?->email,

                            'transactions' =>
                                (int)
                                $row
                                    ->transaction_count,

                            'wallet_released' =>
                                (float)
                                $row
                                    ->wallet_released,

                            'withdrawn' =>
                                (float) (
                                    $withdrawal
                                        ->withdrawn
                                    ??
                                    0
                                ),
                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | STATS
        |--------------------------------------------------------------------------
        */

        $stats = [
            'users' =>
                $totalUsers,

            'active_users' =>
                $activeUsers,

            'active_sellers' =>
                $activeSellers,

            'paid_buyers' =>
                $paidBuyers,

            'pending_seller_applications' =>
                $pendingSellerApplications,

            'paid_transactions' =>
                $paidTransactionCount,

            'running_transactions' =>
                $runningTransactions,

            'completed_transactions' =>
                $completedTransactions,

            'disputed_transactions' =>
                $disputedTransactions,

            'cancelled_transactions' =>
                $cancelledTransactions,

            'awaiting_wallet_release_transactions' =>
                $awaitingWalletReleaseTransactions,

            'protected_transaction_count' =>
                $protectedTransactionCount,

            'wallet_release_count' =>
                $sellerWalletReleaseCount,

            'successful_withdrawal_count' =>
                $successfulWithdrawalCount,

            'pending_withdrawal_count' =>
                $pendingWithdrawalCount,

            'failed_withdrawal_count' =>
                $failedWithdrawalCount,

            'unpaid_generated_links' =>
                $unpaidGeneratedLinks,

            'open_disputes' =>
                $openDisputes,

            'under_review_disputes' =>
                $underReviewDisputes,

            'awaiting_buyer_disputes' =>
                $awaitingBuyerDisputes,

            'awaiting_seller_disputes' =>
                $awaitingSellerDisputes,

            'resolved_disputes' =>
                $resolvedDisputes,

            'unread_inquiries' =>
                $unreadInquiries,

            'package_purchases' =>
                $packagePurchases,

            'package_customers' =>
                $packageCustomers,
        ];


        /*
        |--------------------------------------------------------------------------
        | MONEY
        |--------------------------------------------------------------------------
        */

        $money = [
            /*
             * Buyer side
             */
            'buyer_paid' =>
                $totalBuyerPaid,

            'escrow_protected' =>
                $escrowProtected,


            /*
             * Seller wallet side
             */
            'seller_wallet_released' =>
                $totalSellerWalletReleased,

            'seller_available_balance' =>
                $sellerAvailableBalance,

            'seller_pending_wallet_balance' =>
                $sellerPendingWalletBalance,

            'seller_wallet_liability' =>
                $sellerWalletLiability,

            'wallet_total_credited' =>
                $walletTotalCredited,

            'wallet_total_withdrawn' =>
                $walletTotalWithdrawn,


            /*
             * Seller bank side
             */
            'seller_withdrawn' =>
                $totalSellerWithdrawn,

            'pending_withdrawals' =>
                $pendingWithdrawalAmount,


            /*
             * Platform side
             */
            'service_fee_revenue' =>
                $serviceFeeRevenue,

            'vat_collected' =>
                $vatCollected,

            'package_revenue' =>
                $packageRevenue,

            'gross_platform_profit' =>
                $grossPlatformProfit,

            'gross_business_volume' =>
                $grossBusinessVolume,

            'gross_profit_margin' =>
                $grossProfitMargin,


            /*
             * Growth
             */
            'current_month_buyer_paid' =>
                $currentBuyerPaid,

            'current_month_profit' =>
                $currentProfit,

            'buyer_paid_growth' =>
                $this
                    ->percentageChange(
                        $currentBuyerPaid,
                        $previousBuyerPaid
                    ),

            'profit_growth' =>
                $this
                    ->percentageChange(
                        $currentProfit,
                        $previousProfit
                    ),
        ];


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.dashboard.index',
            compact(
                'period',
                'stats',
                'money',
                'chart',
                'transactionStatusChart',
                'withdrawalStatusChart',
                'profitCompositionChart',
                'packageChart',
                'packageMix',
                'recentTransactions',
                'recentWithdrawals',
                'recentPackagePurchases',
                'topSellers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Paid Transactions
    |--------------------------------------------------------------------------
    */

    private function paidTransactions(): Builder
    {
        return SecureTransaction::query()

            ->where(
                'payment_status',
                SecureTransaction::PAYMENT_PAID
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Transactions Released To Seller Wallet
    |--------------------------------------------------------------------------
    */

    private function walletReleasedTransactions(): Builder
    {
        return $this
            ->paidTransactions()

            ->whereHas(
                'walletRelease',
                function (
                    $query
                ) {

                    $query
                        ->where(
                            'status',
                            SellerWalletTransaction::STATUS_POSTED
                        );
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Sum Buyer Paid
    |--------------------------------------------------------------------------
    */

    private function sumBuyerPaid(
        Builder $query
    ): float {

        return (float) (
            $query

                ->selectRaw(
                    '
                    COALESCE(
                        SUM(
                            COALESCE(
                                paid_amount,
                                total_amount
                            )
                        ),
                        0
                    ) AS aggregate
                    '
                )

                ->value(
                    'aggregate'
                )
            ??
            0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sum Secure Transaction Column
    |--------------------------------------------------------------------------
    */

    private function sumColumn(
        Builder $query,
        string $column
    ): float {

        $allowed = [
            'seller_net_amount',
            'service_fee_amount',
            'vat_amount',
        ];


        if (
            !in_array(
                $column,
                $allowed,
                true
            )
        ) {

            return 0.0;
        }


        return (float) (
            $query

                ->selectRaw(
                    "
                    COALESCE(
                        SUM(
                            COALESCE(
                                {$column},
                                0
                            )
                        ),
                        0
                    ) AS aggregate
                    "
                )

                ->value(
                    'aggregate'
                )
            ??
            0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Realized Service Fees Between Dates
    |--------------------------------------------------------------------------
    */

    private function sumRealizedServiceFeesBetween(
        Carbon $start,
        Carbon $end
    ): float {

        return (float) (
            SellerWalletTransaction::query()

                ->join(
                    'secure_transactions',
                    'secure_transactions.id',
                    '=',
                    'seller_wallet_transactions.secure_transaction_id'
                )

                ->where(
                    'seller_wallet_transactions.type',
                    SellerWalletTransaction::TYPE_TRANSACTION_RELEASE
                )

                ->where(
                    'seller_wallet_transactions.status',
                    SellerWalletTransaction::STATUS_POSTED
                )

                ->whereBetween(
                    DB::raw(
                        '
                        COALESCE(
                            seller_wallet_transactions.processed_at,
                            seller_wallet_transactions.created_at
                        )
                        '
                    ),
                    [
                        $start,
                        $end,
                    ]
                )

                ->sum(
                    'secure_transactions.service_fee_amount'
                )
            ??
            0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Percentage Change
    |--------------------------------------------------------------------------
    */

    private function percentageChange(
        float $current,
        float $previous
    ): float {

        if (
            $previous <= 0
        ) {

            return
                $current > 0
                    ? 100.0
                    : 0.0;
        }


        return round(
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
            100,
            1
        );
    }
}