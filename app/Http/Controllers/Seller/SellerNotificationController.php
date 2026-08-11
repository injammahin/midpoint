<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\TransactionNotification;
use Illuminate\Http\Request;

class SellerNotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $allowedFilters = [
            'all',
            'payment',
            'dispatch',
            'inspection',
            'dispute',
        ];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $baseQuery = TransactionNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('audience', 'seller');

        $query = clone $baseQuery;

        if ($filter !== 'all') {
            $query->where('type', $filter);
        }

        $notifications = $query
            ->with('transaction')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'payment' => (clone $baseQuery)->where('type', 'payment')->count(),
            'dispatch' => (clone $baseQuery)->where('type', 'dispatch')->count(),
            'inspection' => (clone $baseQuery)->where('type', 'inspection')->count(),
            'dispute' => (clone $baseQuery)->where('type', 'dispute')->count(),
        ];

        $unreadCount = (clone $baseQuery)
            ->whereNull('read_at')
            ->count();

        return view('seller.notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'counts' => $counts,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function open(
        Request $request,
        TransactionNotification $notification
    ) {
        abort_unless(
            (int) $notification->user_id === (int) $request->user()->id,
            403
        );

        abort_unless(
            $notification->audience === 'seller',
            403
        );

        $notification->markAsRead();

        $notification->loadMissing('transaction');

        if ($notification->transaction) {
            return redirect()->route(
                'seller.transactions.show',
                [
                    'secureTransaction' =>
                        $notification->transaction->public_token,
                ]
            );
        }

        return redirect()->route('seller.notifications');
    }

    public function markAllRead(Request $request)
    {
        TransactionNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('audience', 'seller')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'All notifications have been marked as read.'
        );
    }
}