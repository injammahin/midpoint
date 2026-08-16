<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeStatusHistory;

use App\Services\TransactionCommunicationService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use Throwable;

class AdminDisputeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dispute List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
            TransactionDispute::query()

                ->whereHas(
                    'transaction',
                    function ($transactionQuery) {

                        $transactionQuery
                            ->where(
                                'payment_status',
                                SecureTransaction::PAYMENT_PAID
                            );
                    }
                )

                ->with([
                    'transaction',
                    'buyer',
                    'seller',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                (string) $request->get(
                    'search'
                )
            );


        if (
            $search !== ''
        ) {

            $query->where(
                function ($builder) use ($search) {

                    $builder

                        ->where(
                            'description',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhereHas(
                            'transaction',
                            function ($transactionQuery) use ($search) {

                                $transactionQuery

                                    ->where(
                                        'reference',
                                        'like',
                                        '%' . $search . '%'
                                    )

                                    ->orWhere(
                                        'title',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        )

                        ->orWhereHas(
                            'buyer',
                            function ($buyerQuery) use ($search) {

                                $buyerQuery

                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        )

                        ->orWhereHas(
                            'seller',
                            function ($sellerQuery) use ($search) {

                                $sellerQuery

                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'status'
            )
        ) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reason Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'reason'
            )
        ) {

            $query->where(
                'reason',
                $request->reason
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_from'
            )
        ) {

            $query->whereDate(
                'opened_at',
                '>=',
                $request->date_from
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_to'
            )
        ) {

            $query->whereDate(
                'opened_at',
                '<=',
                $request->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                TransactionDispute::query()
                    ->count(),


            'open' =>
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_OPEN
                    )

                    ->count(),


            'under_review' =>
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_UNDER_REVIEW
                    )

                    ->count(),


            'awaiting_buyer' =>
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_AWAITING_BUYER
                    )

                    ->count(),


            'awaiting_seller' =>
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_AWAITING_SELLER
                    )

                    ->count(),


            'resolved' =>
                TransactionDispute::query()

                    ->where(
                        'status',
                        TransactionDispute::STATUS_RESOLVED
                    )

                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $disputes =
            $query

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

                        WHEN 'resolved'
                            THEN 5

                        ELSE 6

                    END
                    "
                )

                ->orderByDesc(
                    'opened_at'
                )

                ->paginate(
                    20
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.disputes.index',
            [

                'disputes' =>
                    $disputes,

                'stats' =>
                    $stats,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Dispute
    |--------------------------------------------------------------------------
    */

    public function show(
        TransactionDispute $dispute
    ) {
        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        $dispute->load([

            'buyer',

            'seller',

            'transaction.successfulPayment',

            'statusHistories' =>
                function ($query) {

                    $query

                        ->with(
                            'admin'
                        )

                        ->orderByDesc(
                            'id'
                        );
                },

        ]);


        /*
        |--------------------------------------------------------------------------
        | Only Paid Transactions
        |--------------------------------------------------------------------------
        */

        abort_unless(

            $dispute->transaction

            &&

            $dispute
                ->transaction
                ->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,

            404

        );


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.disputes.show',
            [

                'dispute' =>
                    $dispute,

                'transaction' =>
                    $dispute->transaction,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Dispute Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        TransactionDispute $dispute,
        TransactionCommunicationService $communications
    ) {
        /*
        |--------------------------------------------------------------------------
        | Load Transaction
        |--------------------------------------------------------------------------
        */

        $dispute->loadMissing([

            'transaction.buyer',

            'transaction.seller',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Must Be Paid Transaction
        |--------------------------------------------------------------------------
        */

        abort_unless(

            $dispute->transaction

            &&

            $dispute
                ->transaction
                ->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,

            404

        );


        /*
        |--------------------------------------------------------------------------
        | Resolved Is Terminal
        |--------------------------------------------------------------------------
        */

        if (
            $dispute->status
            ===
            TransactionDispute::STATUS_RESOLVED
        ) {

            return back()->with(
                'error',
                'This dispute has already been resolved.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'status' => [

                    'required',

                    Rule::in([

                        TransactionDispute::STATUS_UNDER_REVIEW,

                        TransactionDispute::STATUS_AWAITING_BUYER,

                        TransactionDispute::STATUS_AWAITING_SELLER,

                        TransactionDispute::STATUS_RESOLVED,

                    ]),
                ],


                'note' => [

                    'nullable',

                    'string',

                    'max:5000',

                ],

            ]);


        $newStatus =
            $validated['status'];


        $note =
            trim(
                (string) (
                    $validated['note']
                    ??
                    ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Transition
        |--------------------------------------------------------------------------
        */

        $this->validateStatusTransition(

            $dispute->status,

            $newStatus

        );


        /*
        |--------------------------------------------------------------------------
        | Require Note
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $newStatus,
                [

                    TransactionDispute::STATUS_AWAITING_BUYER,

                    TransactionDispute::STATUS_AWAITING_SELLER,

                    TransactionDispute::STATUS_RESOLVED,

                ],
                true
            )

            &&

            mb_strlen(
                $note
            ) < 5
        ) {

            throw ValidationException::withMessages([

                'note' =>
                    'Please provide a message explaining what is required or how the dispute was resolved.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Save Status + Transaction State
        |--------------------------------------------------------------------------
        */

        $history =
            DB::transaction(
                function () use (
                    $request,
                    $dispute,
                    $newStatus,
                    $note
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Dispute
                    |--------------------------------------------------------------------------
                    */

                    $lockedDispute =
                        TransactionDispute::query()

                            ->whereKey(
                                $dispute->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Transaction
                    |--------------------------------------------------------------------------
                    */

                    $lockedTransaction =
                        SecureTransaction::query()

                            ->whereKey(
                                $lockedDispute
                                    ->secure_transaction_id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Concurrent Update Protection
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedDispute->status
                        !==
                        $dispute->status
                    ) {

                        throw ValidationException::withMessages([

                            'status' =>
                                'The dispute status was changed by another administrator. Please refresh the page.',

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Old Status
                    |--------------------------------------------------------------------------
                    */

                    $oldStatus =
                        $lockedDispute->status;


                    /*
                    |--------------------------------------------------------------------------
                    | Dispute Updates
                    |--------------------------------------------------------------------------
                    */

                    $disputeUpdates = [

                        'status' =>
                            $newStatus,

                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | Admin Note
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $note !== ''
                    ) {

                        $disputeUpdates['admin_note'] =
                            $note;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RESOLVED
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | When the dispute was opened we changed:
                    |
                    | secure_transactions.status = disputed
                    | auto_complete_at = null
                    |
                    | Therefore simply resolving transaction_disputes is not
                    | enough. We MUST resume the parent transaction too.
                    |
                    */

                    if (
                        $newStatus
                        ===
                        TransactionDispute::STATUS_RESOLVED
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Mark Dispute Resolved
                        |--------------------------------------------------------------------------
                        */

                        $disputeUpdates['resolved_at'] =
                            now();


                        /*
                        |--------------------------------------------------------------------------
                        | Determine Previous Transaction Stage
                        |--------------------------------------------------------------------------
                        |
                        | A dispute can only be created from:
                        |
                        | delivered
                        | inspection
                        |
                        | If inspection_started_at exists, the buyer had already
                        | started inspection when they opened the dispute.
                        |
                        */

                        $wasInspection =
                            !is_null(
                                $lockedTransaction
                                    ->inspection_started_at
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Resume Inspection
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $wasInspection
                        ) {

                            $inspectionHours =
                                (int) config(
                                    'secure_transactions.inspection_hours',
                                    8
                                );


                            $inspectionEndsAt =
                                now()->addHours(
                                    $inspectionHours
                                );


                            $lockedTransaction->forceFill([

                                'status' =>
                                    SecureTransaction::STATUS_INSPECTION,


                                /*
                                |--------------------------------------------------------------------------
                                | Fresh protection period
                                |--------------------------------------------------------------------------
                                |
                                | Time spent while Midpoint reviewed the dispute
                                | must NOT count against the buyer.
                                |
                                */

                                'inspection_ends_at' =>
                                    $inspectionEndsAt,


                                'auto_complete_at' =>
                                    $inspectionEndsAt,

                            ])->save();

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Resume Delivered Stage
                            |--------------------------------------------------------------------------
                            */

                            $deliveryHours =
                                (int) config(
                                    'secure_transactions.delivery_auto_complete_hours',
                                    72
                                );


                            $lockedTransaction->forceFill([

                                'status' =>
                                    SecureTransaction::STATUS_DELIVERED,


                                /*
                                |--------------------------------------------------------------------------
                                | Fresh protection countdown
                                |--------------------------------------------------------------------------
                                */

                                'auto_complete_at' =>
                                    now()->addHours(
                                        $deliveryHours
                                    ),

                            ])->save();
                        }

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | Active Dispute
                        |--------------------------------------------------------------------------
                        |
                        | Under Review / Awaiting Buyer / Awaiting Seller
                        | must continue blocking payout.
                        |
                        */

                        if (
                            $lockedTransaction->status
                            !==
                            SecureTransaction::STATUS_DISPUTED
                        ) {

                            $lockedTransaction->forceFill([

                                'status' =>
                                    SecureTransaction::STATUS_DISPUTED,

                                'auto_complete_at' =>
                                    null,

                            ])->save();
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Save Dispute
                    |--------------------------------------------------------------------------
                    */

                    $lockedDispute->update(
                        $disputeUpdates
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | History
                    |--------------------------------------------------------------------------
                    */

                    return TransactionDisputeStatusHistory::create([

                        'transaction_dispute_id' =>
                            $lockedDispute->id,

                        'secure_transaction_id' =>
                            $lockedDispute
                                ->secure_transaction_id,

                        'admin_id' =>
                            $request
                                ->user()
                                ->id,

                        'from_status' =>
                            $oldStatus,

                        'to_status' =>
                            $newStatus,

                        'note' =>
                            $note !== ''
                                ? $note
                                : null,

                    ]);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Reload Latest Data
        |--------------------------------------------------------------------------
        */

        $dispute

            ->refresh()

            ->load([

                'transaction.buyer',

                'transaction.seller',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Email / Notification
        |--------------------------------------------------------------------------
        */

        try {

            $communications
                ->disputeStatusChanged(

                    $dispute->transaction,

                    $dispute,

                    $history

                );

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Do not roll back status because email failed
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Dispute status communication failed.',
                [

                    'dispute_id' =>
                        $dispute->id,

                    'transaction_id' =>
                        $dispute
                            ->secure_transaction_id,

                    'status' =>
                        $newStatus,

                    'history_id' =>
                        $history->id,

                    'error' =>
                        $exception
                            ->getMessage(),

                ]
            );


            report(
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'admin.disputes.show',
                $dispute
            )

            ->with(
                'success',

                $newStatus
                ===
                TransactionDispute::STATUS_RESOLVED

                    ? 'Dispute resolved successfully. The transaction has resumed and both buyer and seller have been notified.'

                    : 'Dispute status changed to '
                        .
                        $dispute->status_label
                        .
                        '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Workflow Transition
    |--------------------------------------------------------------------------
    */

    protected function validateStatusTransition(
        string $currentStatus,
        string $newStatus
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Allowed Workflow
        |--------------------------------------------------------------------------
        */

        $allowed = [

            /*
            |--------------------------------------------------------------------------
            | Open
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_OPEN => [

                TransactionDispute::STATUS_UNDER_REVIEW,

            ],


            /*
            |--------------------------------------------------------------------------
            | Under Review
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_UNDER_REVIEW => [

                TransactionDispute::STATUS_AWAITING_BUYER,

                TransactionDispute::STATUS_AWAITING_SELLER,

                TransactionDispute::STATUS_RESOLVED,

            ],


            /*
            |--------------------------------------------------------------------------
            | Awaiting Buyer
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_AWAITING_BUYER => [

                TransactionDispute::STATUS_UNDER_REVIEW,

                TransactionDispute::STATUS_AWAITING_SELLER,

                TransactionDispute::STATUS_RESOLVED,

            ],


            /*
            |--------------------------------------------------------------------------
            | Awaiting Seller
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_AWAITING_SELLER => [

                TransactionDispute::STATUS_UNDER_REVIEW,

                TransactionDispute::STATUS_AWAITING_BUYER,

                TransactionDispute::STATUS_RESOLVED,

            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Allowed Next States
        |--------------------------------------------------------------------------
        */

        $possible =
            $allowed[
                $currentStatus
            ]
            ??
            [];


        /*
        |--------------------------------------------------------------------------
        | Invalid
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $newStatus,
                $possible,
                true
            )
        ) {

            throw ValidationException::withMessages([

                'status' =>
                    'You cannot change this dispute from '
                    .
                    ucwords(
                        str_replace(
                            '_',
                            ' ',
                            $currentStatus
                        )
                    )
                    .
                    ' to '
                    .
                    ucwords(
                        str_replace(
                            '_',
                            ' ',
                            $newStatus
                        )
                    )
                    .
                    '.',

            ]);
        }
    }
}