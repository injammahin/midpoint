<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SecureTransaction;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeMessage;
use App\Models\TransactionDisputeStatusHistory;

use App\Services\DisputeResolutionService;
use App\Services\DisputeRoomCommunicationService;
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

            'roomActivator',

            'roomCloser',

            'resolver',

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
        | Latest Resolution Room Messages
        |--------------------------------------------------------------------------
        */

        $messages =
            TransactionDisputeMessage::query()

                ->where(
                    'transaction_dispute_id',
                    $dispute->id
                )

                ->with(
                    'sender'
                )

                ->latest(
                    'id'
                )

                ->limit(
                    100
                )

                ->get()

                ->reverse()

                ->values();


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

                'messages' =>
                    $messages,

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
                    | Active Dispute Workflow
                    |--------------------------------------------------------------------------
                    |
                    | Under Review / Awaiting Buyer / Awaiting Seller always keeps
                    | seller payout locked. Final financial resolution is handled
                    | only by DisputeResolutionService.
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

                            'payout_status' =>
                                SecureTransaction::PAYOUT_LOCKED,

                            'auto_complete_at' =>
                                null,

                        ])->save();
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
                'Dispute status changed to '
                .
                $dispute->status_label
                .
                '.'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Activate Resolution Room
    |--------------------------------------------------------------------------
    */

    public function activateRoom(
        Request $request,
        TransactionDispute $dispute,
        DisputeRoomCommunicationService $communications
    ) {

        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        abort_unless(
            $dispute->transaction
            &&
            $dispute->transaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,
            404
        );


        if (
            $dispute->isResolved()
        ) {

            return back()->with(
                'error',
                'This dispute is already resolved.'
            );
        }


        $activatedNow =
            DB::transaction(
                function () use (
                    $request,
                    $dispute
                ) {

                    $lockedDispute =
                        TransactionDispute::query()

                            ->whereKey(
                                $dispute->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    if (
                        $lockedDispute->room_activated_at
                    ) {

                        return false;
                    }


                    $lockedTransaction =
                        SecureTransaction::query()

                            ->whereKey(
                                $lockedDispute
                                    ->secure_transaction_id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    $oldStatus =
                        $lockedDispute->status;


                    $newStatus =
                        $oldStatus
                        ===
                        TransactionDispute::STATUS_OPEN

                            ? TransactionDispute::STATUS_UNDER_REVIEW

                            : $oldStatus;


                    $lockedDispute->forceFill([

                        'room_activated_at' =>
                            now(),

                        'room_activated_by' =>
                            $request->user()->id,

                        'status' =>
                            $newStatus,

                    ])->save();


                    $lockedTransaction->forceFill([

                        'status' =>
                            SecureTransaction::STATUS_DISPUTED,

                        'payout_status' =>
                            SecureTransaction::PAYOUT_LOCKED,

                        'auto_complete_at' =>
                            null,

                    ])->save();


                    if (
                        $oldStatus
                        !==
                        $newStatus
                    ) {

                        TransactionDisputeStatusHistory::create([

                            'transaction_dispute_id' =>
                                $lockedDispute->id,

                            'secure_transaction_id' =>
                                $lockedDispute
                                    ->secure_transaction_id,

                            'admin_id' =>
                                $request->user()->id,

                            'from_status' =>
                                $oldStatus,

                            'to_status' =>
                                $newStatus,

                            'note' =>
                                'Midpoint activated the dispute resolution room.',

                        ]);
                    }


                    TransactionDisputeMessage::create([

                        'transaction_dispute_id' =>
                            $lockedDispute->id,

                        'secure_transaction_id' =>
                            $lockedDispute
                                ->secure_transaction_id,

                        'sender_id' =>
                            null,

                        'sender_role' =>
                            TransactionDisputeMessage::ROLE_SYSTEM,

                        'visibility' =>
                            TransactionDisputeMessage::VISIBILITY_ALL,

                        'message' =>
                            'Midpoint Support opened this dispute resolution room. The buyer and seller each have a private conversation with Midpoint Support and cannot see each other\'s messages. Seller payout remains locked while the case is active.',

                        'attachments' =>
                            null,

                        'is_system' =>
                            true,

                    ]);


                    return true;
                }
            );


        if (
            $activatedNow
        ) {

            $communications->roomActivated(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ])
            );
        }


        return redirect()

            ->route(
                'admin.disputes.show',
                $dispute
            )

            ->with(
                'success',
                $activatedNow

                    ? 'Dispute resolution room activated. Buyer and seller were notified by Midpoint and email.'

                    : 'The dispute resolution room is already active.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Close Resolution Room Manually
    |--------------------------------------------------------------------------
    */

    public function closeRoom(
        Request $request,
        TransactionDispute $dispute,
        DisputeRoomCommunicationService $communications
    ) {

        $validated =
            $request->validate([
                'close_reason' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ]);


        $dispute->loadMissing([
            'transaction.buyer',
            'transaction.seller',
        ]);


        abort_unless(
            $dispute->transaction
            &&
            $dispute->transaction->payment_status
            ===
            SecureTransaction::PAYMENT_PAID,
            404
        );


        if (!$dispute->isRoomActivated()) {

            return back()->with(
                'error',
                'The dispute resolution room has not been activated.'
            );
        }


        $reason =
            trim(
                (string)
                (
                    $validated['close_reason']
                    ??
                    ''
                )
            );


        if ($reason === '') {
            $reason =
                'Midpoint Support closed this room. Any final decision or further status update will appear on the transaction page.';
        }


        $closedNow =
            DB::transaction(
                function () use (
                    $request,
                    $dispute,
                    $reason
                ) {

                    $lockedDispute =
                        TransactionDispute::query()
                            ->whereKey($dispute->id)
                            ->lockForUpdate()
                            ->firstOrFail();


                    if ($lockedDispute->isRoomClosed()) {
                        return false;
                    }


                    $lockedTransaction =
                        SecureTransaction::query()
                            ->whereKey(
                                $lockedDispute->secure_transaction_id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();


                    $lockedDispute->forceFill([
                        'room_closed_at' =>
                            now(),

                        'room_closed_by' =>
                            $request->user()->id,

                        'room_close_type' =>
                            TransactionDispute::ROOM_CLOSE_MANUAL,

                        'room_close_reason' =>
                            $reason,
                    ])->save();


                    if (!$lockedDispute->isResolved()) {

                        $lockedTransaction->forceFill([
                            'status' =>
                                SecureTransaction::STATUS_DISPUTED,

                            'payout_status' =>
                                SecureTransaction::PAYOUT_LOCKED,

                            'auto_complete_at' =>
                                null,
                        ])->save();
                    }


                    TransactionDisputeMessage::create([
                        'transaction_dispute_id' =>
                            $lockedDispute->id,

                        'secure_transaction_id' =>
                            $lockedDispute->secure_transaction_id,

                        'sender_id' =>
                            null,

                        'sender_role' =>
                            TransactionDisputeMessage::ROLE_SYSTEM,

                        'visibility' =>
                            TransactionDisputeMessage::VISIBILITY_ALL,

                        'message' =>
                            $reason,

                        'attachments' =>
                            null,

                        'is_system' =>
                            true,
                    ]);


                    return true;
                }
            );


        if ($closedNow) {

            $communications->roomClosed(
                $dispute->fresh([
                    'transaction.buyer',
                    'transaction.seller',
                ])
            );
        }


        return redirect()
            ->route(
                'admin.disputes.show',
                $dispute
            )
            ->with(
                'success',
                $closedNow
                    ? 'The dispute room was closed. Buyer and seller were redirected to the transaction.'
                    : 'The dispute room is already closed.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Final Resolution
    |--------------------------------------------------------------------------
    */

    public function resolve(
        Request $request,
        TransactionDispute $dispute,
        DisputeResolutionService $resolutions
    ) {

        $validated =
            $request->validate([

                'resolution_type' => [
                    'required',
                    Rule::in([
                        TransactionDispute::RESOLUTION_FULL_REFUND,
                        TransactionDispute::RESOLUTION_PARTIAL_REFUND,
                        TransactionDispute::RESOLUTION_RELEASE_TO_SELLER,
                        TransactionDispute::RESOLUTION_RESUME_TRANSACTION,
                    ]),
                ],

                'refund_amount' => [
                    'nullable',
                    'required_if:resolution_type,partial_refund',
                    'numeric',
                    'min:0.01',
                    'regex:/^\d+(?:\.\d{1,2})?$/',
                ],

                'resolution_note' => [
                    'required',
                    'string',
                    'min:20',
                    'max:5000',
                ],
            ]);


        try {

            $resolved =
                $resolutions->resolve(
                    $request->user(),
                    $dispute,
                    $validated['resolution_type'],
                    isset(
                        $validated['refund_amount']
                    )
                        ? (string)
                        $validated['refund_amount']
                        : null,
                    trim(
                        $validated['resolution_note']
                    )
                );


            $message =
                $resolved->isResolved()

                    ? 'Dispute resolved successfully.'

                    : 'Midpoint recorded the decision and initiated the Paystack refund. The case will finalize after Paystack confirms the refund.';


            return redirect()

                ->route(
                    'admin.disputes.show',
                    $resolved
                )

                ->with(
                    'success',
                    $message
                );

        } catch (
            ValidationException $exception
        ) {

            throw $exception;

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Dispute resolution failed.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'admin_id' =>
                        $request->user()->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return redirect()

                ->route(
                    'admin.disputes.show',
                    $dispute
                )

                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Sync Paystack Refund
    |--------------------------------------------------------------------------
    */

    public function syncRefund(
        Request $request,
        TransactionDispute $dispute,
        DisputeResolutionService $resolutions
    ) {

        try {

            $resolutions->syncRefund(
                $dispute
            );


            return redirect()

                ->route(
                    'admin.disputes.show',
                    $dispute
                )

                ->with(
                    'success',
                    'Paystack refund status synchronized successfully.'
                );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Paystack dispute refund sync failed.',
                [
                    'dispute_id' =>
                        $dispute->id,

                    'admin_id' =>
                        $request->user()->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            return redirect()

                ->route(
                    'admin.disputes.show',
                    $dispute
                )

                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
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

            ],


            /*
            |--------------------------------------------------------------------------
            | Awaiting Buyer
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_AWAITING_BUYER => [

                TransactionDispute::STATUS_UNDER_REVIEW,

                TransactionDispute::STATUS_AWAITING_SELLER,

            ],


            /*
            |--------------------------------------------------------------------------
            | Awaiting Seller
            |--------------------------------------------------------------------------
            */

            TransactionDispute::STATUS_AWAITING_SELLER => [

                TransactionDispute::STATUS_UNDER_REVIEW,

                TransactionDispute::STATUS_AWAITING_BUYER,

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
