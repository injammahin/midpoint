<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ContactMessage;
use App\Models\SupportAgentProfile;
use App\Models\SupportChatSession;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeStatusHistory;

use Carbon\Carbon;

use Illuminate\Http\Request;


class StaffDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Staff Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Main Admin Uses Existing Financial Dashboard
        |--------------------------------------------------------------------------
        */

        if ($user->isAdmin()) {

            return redirect()
                ->route(
                    'admin.dashboard'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Only Restricted Admin Staff
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->isAdminStaff(),
            403,
            'This dashboard is only available to administration staff.'
        );


        /*
        |--------------------------------------------------------------------------
        | Chart Period
        |--------------------------------------------------------------------------
        |
        | 1  = current month
        | 6  = last 6 months
        | 12 = last 12 months
        |
        */

        $period = (int) $request->get(
            'period',
            1
        );


        if (
            !in_array(
                $period,
                [
                    1,
                    6,
                    12,
                ],
                true
            )
        ) {

            $period = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Period Range
        |--------------------------------------------------------------------------
        */

        if ($period === 1) {

            $periodStart =
                now()
                    ->copy()
                    ->startOfMonth();

        } else {

            $periodStart =
                now()
                    ->copy()
                    ->startOfMonth()
                    ->subMonths(
                        $period - 1
                    );
        }


        $periodEnd =
            now()
                ->copy()
                ->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | Support Agent Profile
        |--------------------------------------------------------------------------
        */

        $profile =
            SupportAgentProfile::firstOrCreate(
                [
                    'user_id' =>
                        $user->id,
                ],
                [
                    'is_enabled' =>
                        true,

                    'is_accepting_chats' =>
                        false,

                    'max_active_chats' =>
                        3,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | LIVE SUPPORT - TEAM QUEUE
        |--------------------------------------------------------------------------
        */

        $waitingChats =
            SupportChatSession::query()

                ->where(
                    'status',
                    'waiting'
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | LIVE SUPPORT - CURRENT STAFF
        |--------------------------------------------------------------------------
        */

        $myActiveChats =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $user->id
                )

                ->where(
                    'status',
                    'active'
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | My Resolved Chats - All Time
        |--------------------------------------------------------------------------
        |
        | We use resolved_at rather than status because after the customer
        | rates the session its status becomes "closed".
        |
        */

        $myResolvedChats =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $user->id
                )

                ->whereNotNull(
                    'resolved_at'
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | My Resolved Chats - Selected Period
        |--------------------------------------------------------------------------
        */

        $myResolvedChatsPeriod =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $user->id
                )

                ->whereNotNull(
                    'resolved_at'
                )

                ->whereBetween(
                    'resolved_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | MY SUPPORT RATING
        |--------------------------------------------------------------------------
        |
        | CRITICAL:
        |
        | agent_id = current logged in staff user.
        |
        | Therefore:
        |
        | Staff 1 sees ONLY Staff 1's ratings.
        | Staff 2 sees ONLY Staff 2's ratings.
        |
        */

        $myRatingQuery =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $user->id
                )

                ->whereNotNull(
                    'rating'
                );


        $myAverageRating =
            round(
                (float) (
                    (clone $myRatingQuery)
                        ->avg(
                            'rating'
                        )
                    ??
                    0
                ),
                2
            );


        $myRatingCount =
            (clone $myRatingQuery)
                ->count();


        $myFiveStarRatings =
            (clone $myRatingQuery)

                ->where(
                    'rating',
                    5
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Average Chat Resolution Time
        |--------------------------------------------------------------------------
        */

        $averageResolutionMinutes =
            (float) (
                SupportChatSession::query()

                    ->where(
                        'agent_id',
                        $user->id
                    )

                    ->whereNotNull(
                        'assigned_at'
                    )

                    ->whereNotNull(
                        'resolved_at'
                    )

                    ->selectRaw(
                        '
                        AVG(
                            TIMESTAMPDIFF(
                                MINUTE,
                                assigned_at,
                                resolved_at
                            )
                        ) AS average_minutes
                        '
                    )

                    ->value(
                        'average_minutes'
                    )

                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | DISPUTE QUEUE
        |--------------------------------------------------------------------------
        */

        $newDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    TransactionDispute::STATUS_OPEN
                )

                ->count();


        $unresolvedDisputes =
            TransactionDispute::query()

                ->where(
                    'status',
                    '!=',
                    TransactionDispute::STATUS_RESOLVED
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Disputes Resolved By THIS Staff
        |--------------------------------------------------------------------------
        |
        | Your existing dispute history already stores:
        |
        | admin_id
        | from_status
        | to_status
        |
        | This gives us accurate staff attribution.
        |
        */

        $myResolvedDisputes =
            TransactionDisputeStatusHistory::query()

                ->where(
                    'admin_id',
                    $user->id
                )

                ->where(
                    'to_status',
                    TransactionDispute::STATUS_RESOLVED
                )

                ->distinct()

                ->count(
                    'transaction_dispute_id'
                );


        /*
        |--------------------------------------------------------------------------
        | My Resolved Disputes - Selected Period
        |--------------------------------------------------------------------------
        */

        $myResolvedDisputesPeriod =
            TransactionDisputeStatusHistory::query()

                ->where(
                    'admin_id',
                    $user->id
                )

                ->where(
                    'to_status',
                    TransactionDispute::STATUS_RESOLVED
                )

                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )

                ->distinct()

                ->count(
                    'transaction_dispute_id'
                );


        /*
        |--------------------------------------------------------------------------
        | CONTACT / SUPPORT REQUESTS
        |--------------------------------------------------------------------------
        */

        $newContactRequests =
            ContactMessage::query()

                ->where(
                    'status',
                    'new'
                )

                ->count();


        $inProgressContactRequests =
            ContactMessage::query()

                ->where(
                    'status',
                    'in_progress'
                )

                ->count();


        $unreadContactRequests =
            ContactMessage::query()

                ->whereNull(
                    'read_at'
                )

                ->count();


        /*
        |--------------------------------------------------------------------------
        | Personal Resolution Totals
        |--------------------------------------------------------------------------
        */

        $myResolvedCases =
            $myResolvedChats
            +
            $myResolvedDisputes;


        $myResolvedCasesPeriod =
            $myResolvedChatsPeriod
            +
            $myResolvedDisputesPeriod;


        /*
        |--------------------------------------------------------------------------
        | Shared Unresolved Work Queue
        |--------------------------------------------------------------------------
        |
        | Disputes currently do not contain an explicit assigned_staff_id.
        |
        | Therefore unresolved work is correctly presented as the TEAM QUEUE,
        | while resolved performance is personal to the logged-in staff.
        |
        */

        $unresolvedWorkQueue =
            $waitingChats
            +
            $unresolvedDisputes
            +
            $newContactRequests
            +
            $inProgressContactRequests;


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'waiting_chats' =>
                $waitingChats,

            'my_active_chats' =>
                $myActiveChats,

            'my_resolved_chats' =>
                $myResolvedChats,

            'my_resolved_chats_period' =>
                $myResolvedChatsPeriod,

            'new_disputes' =>
                $newDisputes,

            'unresolved_disputes' =>
                $unresolvedDisputes,

            'my_resolved_disputes' =>
                $myResolvedDisputes,

            'my_resolved_disputes_period' =>
                $myResolvedDisputesPeriod,

            'new_contacts' =>
                $newContactRequests,

            'in_progress_contacts' =>
                $inProgressContactRequests,

            'unread_contacts' =>
                $unreadContactRequests,

            'my_resolved_cases' =>
                $myResolvedCases,

            'my_resolved_cases_period' =>
                $myResolvedCasesPeriod,

            'unresolved_work_queue' =>
                $unresolvedWorkQueue,

            'average_rating' =>
                $myAverageRating,

            'rating_count' =>
                $myRatingCount,

            'five_star_ratings' =>
                $myFiveStarRatings,

            'average_resolution_minutes' =>
                round(
                    $averageResolutionMinutes
                ),

        ];


        /*
        |--------------------------------------------------------------------------
        | Performance Charts
        |--------------------------------------------------------------------------
        */

        $performanceChart =
            $this->buildPerformanceChart(
                $user->id,
                $period,
                $periodStart,
                $periodEnd
            );


        /*
        |--------------------------------------------------------------------------
        | Rating Distribution
        |--------------------------------------------------------------------------
        */

        $ratingRows =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $user->id
                )

                ->whereNotNull(
                    'rating'
                )

                ->select(
                    'rating'
                )

                ->selectRaw(
                    'COUNT(*) AS total'
                )

                ->groupBy(
                    'rating'
                )

                ->get()

                ->keyBy(
                    'rating'
                );


        $ratingDistribution = [

            'labels' => [
                '5 Stars',
                '4 Stars',
                '3 Stars',
                '2 Stars',
                '1 Star',
            ],

            'series' => [

                (int) optional(
                    $ratingRows->get(5)
                )->total,

                (int) optional(
                    $ratingRows->get(4)
                )->total,

                (int) optional(
                    $ratingRows->get(3)
                )->total,

                (int) optional(
                    $ratingRows->get(2)
                )->total,

                (int) optional(
                    $ratingRows->get(1)
                )->total,

            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Recent Customer Ratings
        |--------------------------------------------------------------------------
        */

        $recentRatings =
            SupportChatSession::query()

                ->with([
                    'user',
                ])

                ->where(
                    'agent_id',
                    $user->id
                )

                ->whereNotNull(
                    'rating'
                )

                ->latest(
                    'rated_at'
                )

                ->limit(6)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Open Disputes
        |--------------------------------------------------------------------------
        */

        $recentDisputes =
            TransactionDispute::query()

                ->with([
                    'transaction',
                    'buyer',
                    'seller',
                ])

                ->where(
                    'status',
                    '!=',
                    TransactionDispute::STATUS_RESOLVED
                )

                ->orderByRaw(
                    "
                    CASE status

                        WHEN 'open'
                            THEN 1

                        WHEN 'under_review'
                            THEN 2

                        WHEN 'awaiting_buyer'
                            THEN 3

                        WHEN 'awaiting_seller'
                            THEN 4

                        ELSE 5

                    END
                    "
                )

                ->orderByDesc(
                    'opened_at'
                )

                ->limit(6)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Waiting Support Chats
        |--------------------------------------------------------------------------
        */

        $recentWaitingChats =
            SupportChatSession::query()

                ->with([
                    'user',
                ])

                ->where(
                    'status',
                    'waiting'
                )

                ->orderByRaw(
                    'queue_position IS NULL'
                )

                ->orderBy(
                    'queue_position'
                )

                ->orderBy(
                    'created_at'
                )

                ->limit(6)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Contact Requests
        |--------------------------------------------------------------------------
        */

        $recentContacts =
            ContactMessage::query()

                ->whereIn(
                    'status',
                    [
                        'new',
                        'in_progress',
                    ]
                )

                ->orderByRaw(
                    'read_at IS NULL DESC'
                )

                ->latest()

                ->limit(6)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.staff-dashboard.index',
            compact(
                'user',
                'profile',
                'period',
                'periodStart',
                'periodEnd',
                'stats',
                'performanceChart',
                'ratingDistribution',
                'recentRatings',
                'recentDisputes',
                'recentWaitingChats',
                'recentContacts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Performance Chart
    |--------------------------------------------------------------------------
    */

    private function buildPerformanceChart(
        int $userId,
        int $period,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Bucket Format
        |--------------------------------------------------------------------------
        */

        $mysqlFormat =
            $period === 1
                ? '%Y-%m-%d'
                : '%Y-%m';


        /*
        |--------------------------------------------------------------------------
        | Resolved Chats
        |--------------------------------------------------------------------------
        */

        $chatRows =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $userId
                )

                ->whereNotNull(
                    'resolved_at'
                )

                ->whereBetween(
                    'resolved_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )

                ->selectRaw(
                    "
                    DATE_FORMAT(
                        resolved_at,
                        '{$mysqlFormat}'
                    ) AS bucket
                    "
                )

                ->selectRaw(
                    'COUNT(*) AS total'
                )

                ->groupBy(
                    'bucket'
                )

                ->get()

                ->keyBy(
                    'bucket'
                );


        /*
        |--------------------------------------------------------------------------
        | Resolved Disputes
        |--------------------------------------------------------------------------
        */

        $disputeRows =
            TransactionDisputeStatusHistory::query()

                ->where(
                    'admin_id',
                    $userId
                )

                ->where(
                    'to_status',
                    TransactionDispute::STATUS_RESOLVED
                )

                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )

                ->selectRaw(
                    "
                    DATE_FORMAT(
                        created_at,
                        '{$mysqlFormat}'
                    ) AS bucket
                    "
                )

                ->selectRaw(
                    '
                    COUNT(
                        DISTINCT transaction_dispute_id
                    ) AS total
                    '
                )

                ->groupBy(
                    'bucket'
                )

                ->get()

                ->keyBy(
                    'bucket'
                );


        /*
        |--------------------------------------------------------------------------
        | Rating Trend
        |--------------------------------------------------------------------------
        */

        $ratingRows =
            SupportChatSession::query()

                ->where(
                    'agent_id',
                    $userId
                )

                ->whereNotNull(
                    'rating'
                )

                ->whereNotNull(
                    'rated_at'
                )

                ->whereBetween(
                    'rated_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )

                ->selectRaw(
                    "
                    DATE_FORMAT(
                        rated_at,
                        '{$mysqlFormat}'
                    ) AS bucket
                    "
                )

                ->selectRaw(
                    '
                    ROUND(
                        AVG(rating),
                        2
                    ) AS average_rating
                    '
                )

                ->selectRaw(
                    'COUNT(*) AS rating_count'
                )

                ->groupBy(
                    'bucket'
                )

                ->get()

                ->keyBy(
                    'bucket'
                );


        /*
        |--------------------------------------------------------------------------
        | Build Complete Timeline
        |--------------------------------------------------------------------------
        */

        $chart = [

            'labels' =>
                [],

            'resolved_chats' =>
                [],

            'resolved_disputes' =>
                [],

            'total_resolved' =>
                [],

            'average_rating' =>
                [],

            'rating_count' =>
                [],

        ];


        /*
        |--------------------------------------------------------------------------
        | Current Month - Daily
        |--------------------------------------------------------------------------
        */

        if ($period === 1) {

            $cursor =
                $periodStart
                    ->copy()
                    ->startOfDay();


            $lastDay =
                now()
                    ->copy()
                    ->endOfDay();


            while (
                $cursor->lte(
                    $lastDay
                )
            ) {

                $key =
                    $cursor->format(
                        'Y-m-d'
                    );


                $chat =
                    $chatRows->get(
                        $key
                    );


                $dispute =
                    $disputeRows->get(
                        $key
                    );


                $rating =
                    $ratingRows->get(
                        $key
                    );


                $chatCount =
                    (int) (
                        $chat->total
                        ??
                        0
                    );


                $disputeCount =
                    (int) (
                        $dispute->total
                        ??
                        0
                    );


                $chart['labels'][] =
                    $cursor->format(
                        'd M'
                    );


                $chart['resolved_chats'][] =
                    $chatCount;


                $chart['resolved_disputes'][] =
                    $disputeCount;


                $chart['total_resolved'][] =
                    $chatCount
                    +
                    $disputeCount;


                $chart['average_rating'][] =
                    isset(
                        $rating->average_rating
                    )
                        ? (float) $rating->average_rating
                        : null;


                $chart['rating_count'][] =
                    (int) (
                        $rating->rating_count
                        ??
                        0
                    );


                $cursor->addDay();
            }


            return $chart;
        }


        /*
        |--------------------------------------------------------------------------
        | 6 / 12 Months - Monthly
        |--------------------------------------------------------------------------
        */

        $cursor =
            $periodStart
                ->copy()
                ->startOfMonth();


        $lastMonth =
            $periodEnd
                ->copy()
                ->startOfMonth();


        while (
            $cursor->lte(
                $lastMonth
            )
        ) {

            $key =
                $cursor->format(
                    'Y-m'
                );


            $chat =
                $chatRows->get(
                    $key
                );


            $dispute =
                $disputeRows->get(
                    $key
                );


            $rating =
                $ratingRows->get(
                    $key
                );


            $chatCount =
                (int) (
                    $chat->total
                    ??
                    0
                );


            $disputeCount =
                (int) (
                    $dispute->total
                    ??
                    0
                );


            $chart['labels'][] =
                $cursor->format(
                    'M Y'
                );


            $chart['resolved_chats'][] =
                $chatCount;


            $chart['resolved_disputes'][] =
                $disputeCount;


            $chart['total_resolved'][] =
                $chatCount
                +
                $disputeCount;


            $chart['average_rating'][] =
                isset(
                    $rating->average_rating
                )
                    ? (float) $rating->average_rating
                    : null;


            $chart['rating_count'][] =
                (int) (
                    $rating->rating_count
                    ??
                    0
                );


            $cursor->addMonthNoOverflow();
        }


        return $chart;
    }
}