<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\SecureTransaction;
use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerSubscription;
use App\Models\TransactionDispute;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = (int) $request->get('period', 12);
        $period = in_array($period, [6, 12], true) ? $period : 12;

        $chartStart = now()->copy()->startOfMonth()->subMonths($period - 1);
        $chartEnd = now()->copy()->endOfMonth();

        $paidQuery = $this->paidTransactions();
        $releasedQuery = $this->releasedTransactions();

        /* Financial totals */
        $totalBuyerPaid = $this->sumBuyerPaid(clone $paidQuery);
        $totalSellerReleased = $this->sumColumn(clone $releasedQuery, 'seller_net_amount');
        $serviceFeeRevenue = $this->sumColumn(clone $releasedQuery, 'service_fee_amount');
        $vatCollected = $this->sumColumn(clone $releasedQuery, 'vat_amount');
        $packageRevenue = (float) SellerInvoice::query()->where('status', 'paid')->sum('amount');

        // Gross platform revenue/profit before operating expenses. VAT is excluded.
        $grossPlatformProfit = $serviceFeeRevenue + $packageRevenue;
        $grossBusinessVolume = $totalBuyerPaid + $packageRevenue;
        $grossProfitMargin = $grossBusinessVolume > 0
            ? round(($grossPlatformProfit / $grossBusinessVolume) * 100, 2)
            : 0.0;

        // Buyer-paid money whose seller payout has not completed successfully yet.
        $escrowProtected = $this->sumBuyerPaid(
            SecureTransaction::query()
                ->where('payment_status', SecureTransaction::PAYMENT_PAID)
                ->whereNotIn('status', [
                    SecureTransaction::STATUS_CANCELLED,
                    SecureTransaction::STATUS_EXPIRED,
                ])
                ->where(function ($query) {
                    $query->whereNull('payout_status')
                        ->orWhere('payout_status', '!=', SecureTransaction::PAYOUT_SUCCESS);
                })
        );

        /* Transaction counts */
        $paidTransactionCount = (clone $paidQuery)->count();
        $completedTransactions = (clone $paidQuery)
            ->where('status', SecureTransaction::STATUS_COMPLETED)
            ->count();
        $disputedTransactions = (clone $paidQuery)
            ->where('status', SecureTransaction::STATUS_DISPUTED)
            ->count();
        $cancelledTransactions = (clone $paidQuery)
            ->where('status', SecureTransaction::STATUS_CANCELLED)
            ->count();
        $runningTransactions = (clone $paidQuery)
            ->whereNotIn('status', [
                SecureTransaction::STATUS_COMPLETED,
                SecureTransaction::STATUS_CANCELLED,
                SecureTransaction::STATUS_EXPIRED,
                SecureTransaction::STATUS_DISPUTED,
            ])
            ->count();
        $awaitingPayoutTransactions = (clone $paidQuery)
            ->where(function ($query) {
                $query->whereIn('status', [
                    SecureTransaction::STATUS_RELEASE_APPROVED,
                    SecureTransaction::STATUS_PAYOUT_PENDING,
                ])->orWhereIn('payout_status', [
                    SecureTransaction::PAYOUT_INITIALIZING,
                    SecureTransaction::PAYOUT_PENDING,
                    SecureTransaction::PAYOUT_FAILED,
                ]);
            })
            ->count();
        $unpaidGeneratedLinks = SecureTransaction::query()
            ->where('payment_status', SecureTransaction::PAYMENT_UNPAID)
            ->where('status', SecureTransaction::STATUS_AWAITING_PAYMENT)
            ->count();

        /* Users / sellers / package stats */
        $totalUsers = User::query()->where('role', '!=', 'admin')->count();
        $activeUsers = User::query()->where('role', '!=', 'admin')->where('status', true)->count();
        $paidBuyers = SecureTransaction::query()
            ->where('payment_status', SecureTransaction::PAYMENT_PAID)
            ->whereNotNull('buyer_id')
            ->distinct()
            ->count('buyer_id');
        $activeSellers = SellerSubscription::query()->active()->distinct()->count('user_id');
        $pendingSellerApplications = SellerApplication::query()
            ->where('status', SellerApplication::STATUS_SUBMITTED)
            ->count();
        $packagePurchases = SellerInvoice::query()->where('status', 'paid')->count();
        $packageCustomers = SellerInvoice::query()->where('status', 'paid')->distinct()->count('user_id');

        /* Disputes / support */
        $openDisputes = TransactionDispute::query()->where('status', 'open')->count();
        $underReviewDisputes = TransactionDispute::query()->where('status', 'under_review')->count();
        $awaitingBuyerDisputes = TransactionDispute::query()->where('status', 'awaiting_buyer')->count();
        $awaitingSellerDisputes = TransactionDispute::query()->where('status', 'awaiting_seller')->count();
        $resolvedDisputes = TransactionDispute::query()->where('status', 'resolved')->count();
        $unreadInquiries = ContactMessage::query()->whereNull('read_at')->count();

        /* Month-over-month */
        $currentStart = now()->copy()->startOfMonth();
        $currentEnd = now()->copy()->endOfMonth();
        $previousStart = now()->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = now()->copy()->subMonthNoOverflow()->endOfMonth();

        $currentBuyerPaid = $this->sumBuyerPaid(
            $this->paidTransactions()->whereBetween('paid_at', [$currentStart, $currentEnd])
        );
        $previousBuyerPaid = $this->sumBuyerPaid(
            $this->paidTransactions()->whereBetween('paid_at', [$previousStart, $previousEnd])
        );
        $currentPackageRevenue = (float) SellerInvoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$currentStart, $currentEnd])
            ->sum('amount');
        $previousPackageRevenue = (float) SellerInvoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$previousStart, $previousEnd])
            ->sum('amount');
        $currentProfit = $this->sumRealizedServiceFeesBetween($currentStart, $currentEnd) + $currentPackageRevenue;
        $previousProfit = $this->sumRealizedServiceFeesBetween($previousStart, $previousEnd) + $previousPackageRevenue;

        /* Monthly chart queries */
        $paidByMonth = $this->paidTransactions()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$chartStart, $chartEnd])
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') AS month")
            ->selectRaw('COUNT(*) AS transaction_count')
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, total_amount)), 0) AS buyer_paid')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $payoutDateExpression = DB::raw('COALESCE(payout_completed_at, funds_released_at, completed_at)');
        $payoutByMonth = $this->releasedTransactions()
            ->whereBetween($payoutDateExpression, [$chartStart, $chartEnd])
            ->selectRaw("DATE_FORMAT(COALESCE(payout_completed_at, funds_released_at, completed_at), '%Y-%m') AS month")
            ->selectRaw('COALESCE(SUM(COALESCE(seller_net_amount, 0)), 0) AS seller_released')
            ->selectRaw('COALESCE(SUM(COALESCE(service_fee_amount, 0)), 0) AS service_fee_revenue')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $packagesByMonth = SellerInvoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$chartStart, $chartEnd])
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') AS month")
            ->selectRaw('COUNT(*) AS purchase_count')
            ->selectRaw('COALESCE(SUM(amount), 0) AS package_revenue')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $chart = [
            'labels' => [],
            'buyer_paid' => [],
            'seller_released' => [],
            'service_fee_revenue' => [],
            'package_revenue' => [],
            'platform_profit' => [],
            'transaction_count' => [],
        ];

        $cursor = $chartStart->copy();
        while ($cursor->lte($chartEnd)) {
            $key = $cursor->format('Y-m');
            $paid = $paidByMonth->get($key);
            $payout = $payoutByMonth->get($key);
            $package = $packagesByMonth->get($key);
            $fee = (float) ($payout->service_fee_revenue ?? 0);
            $packageIncome = (float) ($package->package_revenue ?? 0);

            $chart['labels'][] = $cursor->format('M Y');
            $chart['buyer_paid'][] = round((float) ($paid->buyer_paid ?? 0), 2);
            $chart['seller_released'][] = round((float) ($payout->seller_released ?? 0), 2);
            $chart['service_fee_revenue'][] = round($fee, 2);
            $chart['package_revenue'][] = round($packageIncome, 2);
            $chart['platform_profit'][] = round($fee + $packageIncome, 2);
            $chart['transaction_count'][] = (int) ($paid->transaction_count ?? 0);

            $cursor->addMonthNoOverflow();
        }

        $transactionStatusChart = [
            'labels' => ['Running', 'Completed', 'Disputed', 'Cancelled'],
            'series' => [
                $runningTransactions,
                $completedTransactions,
                $disputedTransactions,
                $cancelledTransactions,
            ],
        ];

        $profitCompositionChart = [
            'labels' => ['Transaction service fees', 'Seller package revenue'],
            'series' => [round($serviceFeeRevenue, 2), round($packageRevenue, 2)],
        ];

        /* Package purchase mix */
        $packageMix = SellerInvoice::query()
            ->join('seller_applications', 'seller_applications.id', '=', 'seller_invoices.seller_application_id')
            ->where('seller_invoices.status', 'paid')
            ->select('seller_applications.package_name')
            ->selectRaw('COUNT(*) AS purchases')
            ->selectRaw('COALESCE(SUM(seller_invoices.amount), 0) AS revenue')
            ->groupBy('seller_applications.package_name')
            ->orderByDesc('revenue')
            ->get();

        $packageChart = [
            'labels' => $packageMix->pluck('package_name')->values()->all(),
            'series' => $packageMix->map(fn ($item) => (int) $item->purchases)->values()->all(),
            'revenue' => $packageMix->map(fn ($item) => round((float) $item->revenue, 2))->values()->all(),
        ];

        /* Recent activity */
        $recentTransactions = SecureTransaction::query()
            ->with(['buyer', 'seller', 'successfulPayment', 'dispute'])
            ->where('payment_status', SecureTransaction::PAYMENT_PAID)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        $recentPackagePurchases = SellerInvoice::query()
            ->with(['user', 'application'])
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /* Top sellers based on successfully released transaction volume */
        $topSellerRows = $this->releasedTransactions()
            ->whereNotNull('seller_id')
            ->select('seller_id')
            ->selectRaw('COUNT(*) AS transaction_count')
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, total_amount)), 0) AS gross_volume')
            ->selectRaw('COALESCE(SUM(COALESCE(seller_net_amount, 0)), 0) AS seller_received')
            ->groupBy('seller_id')
            ->orderByDesc('gross_volume')
            ->limit(5)
            ->get();

        $sellerUsers = User::query()
            ->whereIn('id', $topSellerRows->pluck('seller_id'))
            ->get()
            ->keyBy('id');

        $topSellers = $topSellerRows->map(function ($row) use ($sellerUsers) {
            $seller = $sellerUsers->get($row->seller_id);

            return [
                'id' => (int) $row->seller_id,
                'name' => $seller?->name ?? 'Seller #' . $row->seller_id,
                'email' => $seller?->email,
                'transactions' => (int) $row->transaction_count,
                'gross_volume' => (float) $row->gross_volume,
                'seller_received' => (float) $row->seller_received,
            ];
        });

        $stats = [
            'users' => $totalUsers,
            'active_users' => $activeUsers,
            'active_sellers' => $activeSellers,
            'paid_buyers' => $paidBuyers,
            'pending_seller_applications' => $pendingSellerApplications,
            'paid_transactions' => $paidTransactionCount,
            'running_transactions' => $runningTransactions,
            'completed_transactions' => $completedTransactions,
            'disputed_transactions' => $disputedTransactions,
            'awaiting_payout_transactions' => $awaitingPayoutTransactions,
            'unpaid_generated_links' => $unpaidGeneratedLinks,
            'open_disputes' => $openDisputes,
            'under_review_disputes' => $underReviewDisputes,
            'awaiting_buyer_disputes' => $awaitingBuyerDisputes,
            'awaiting_seller_disputes' => $awaitingSellerDisputes,
            'resolved_disputes' => $resolvedDisputes,
            'unread_inquiries' => $unreadInquiries,
            'package_purchases' => $packagePurchases,
            'package_customers' => $packageCustomers,
        ];

        $money = [
            'buyer_paid' => $totalBuyerPaid,
            'seller_released' => $totalSellerReleased,
            'escrow_protected' => $escrowProtected,
            'service_fee_revenue' => $serviceFeeRevenue,
            'vat_collected' => $vatCollected,
            'package_revenue' => $packageRevenue,
            'gross_platform_profit' => $grossPlatformProfit,
            'gross_business_volume' => $grossBusinessVolume,
            'gross_profit_margin' => $grossProfitMargin,
            'current_month_buyer_paid' => $currentBuyerPaid,
            'current_month_profit' => $currentProfit,
            'buyer_paid_growth' => $this->percentageChange($currentBuyerPaid, $previousBuyerPaid),
            'profit_growth' => $this->percentageChange($currentProfit, $previousProfit),
        ];

        return view('admin.dashboard.index', compact(
            'period',
            'stats',
            'money',
            'chart',
            'transactionStatusChart',
            'profitCompositionChart',
            'packageChart',
            'packageMix',
            'recentTransactions',
            'recentPackagePurchases',
            'topSellers'
        ));
    }

    private function paidTransactions(): Builder
    {
        return SecureTransaction::query()
            ->where('payment_status', SecureTransaction::PAYMENT_PAID);
    }

    private function releasedTransactions(): Builder
    {
        return $this->paidTransactions()
            ->where(function ($query) {
                $query->where('payout_status', SecureTransaction::PAYOUT_SUCCESS)
                    ->orWhereNotNull('payout_completed_at');
            });
    }

    private function sumBuyerPaid(Builder $query): float
    {
        return (float) ($query
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, total_amount)), 0) AS aggregate')
            ->value('aggregate') ?? 0);
    }

    private function sumColumn(Builder $query, string $column): float
    {
        $allowed = ['seller_net_amount', 'service_fee_amount', 'vat_amount'];

        if (!in_array($column, $allowed, true)) {
            return 0.0;
        }

        return (float) ($query
            ->selectRaw("COALESCE(SUM(COALESCE({$column}, 0)), 0) AS aggregate")
            ->value('aggregate') ?? 0);
    }

    private function sumRealizedServiceFeesBetween(Carbon $start, Carbon $end): float
    {
        return (float) ($this->releasedTransactions()
            ->whereBetween(
                DB::raw('COALESCE(payout_completed_at, funds_released_at, completed_at)'),
                [$start, $end]
            )
            ->selectRaw('COALESCE(SUM(COALESCE(service_fee_amount, 0)), 0) AS aggregate')
            ->value('aggregate') ?? 0);
    }

    private function percentageChange(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
