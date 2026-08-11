<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Models\TransactionDispute;
use App\Services\TransactionCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BuyerTransactionDisputeController extends Controller
{
    public function create(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        $this->authorizeBuyer(
            $request,
            $secureTransaction
        );

        if (
            !in_array(
                $secureTransaction->status,
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $secureTransaction->public_token,
                    ]
                )
                ->with(
                    'error',
                    'A dispute can only be opened after delivery or during inspection.'
                );
        }

        if ($secureTransaction->dispute) {
            return redirect()
                ->route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $secureTransaction->public_token,
                    ]
                )
                ->with(
                    'error',
                    'A dispute already exists for this transaction.'
                );
        }

        $secureTransaction->loadMissing([
            'seller',
            'buyer',
        ]);

        return view(
            'buyer.transactions.dispute',
            [
                'transaction' =>
                    $secureTransaction,
            ]
        );
    }

    public function store(
        Request $request,
        SecureTransaction $secureTransaction,
        TransactionCommunicationService $communications
    ) {
        $this->authorizeBuyer(
            $request,
            $secureTransaction
        );

        if ($secureTransaction->dispute) {
            throw ValidationException::withMessages([
                'transaction' =>
                    'A dispute already exists for this transaction.',
            ]);
        }

        if (
            !in_array(
                $secureTransaction->status,
                [
                    SecureTransaction::STATUS_DELIVERED,
                    SecureTransaction::STATUS_INSPECTION,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'transaction' =>
                    'This transaction can no longer be disputed.',
            ]);
        }

        $validated =
            $request->validate([
                'reason' => [
                    'required',
                    Rule::in([
                        'not_received',
                        'not_as_described',
                        'damaged',
                        'wrong_item',
                        'missing_parts',
                        'other',
                    ]),
                ],

                'description' => [
                    'required',
                    'string',
                    'min:20',
                    'max:5000',
                ],

                'desired_outcome' => [
                    'required',
                    Rule::in([
                        'full_refund',
                        'partial_refund',
                        'replacement',
                    ]),
                ],

                'evidence' => [
                    'required',
                    'array',
                    'min:2',
                    'max:6',
                ],

                'evidence.*' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,mp4,mov',
                    'max:20480',
                ],

                'return_method' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'return_proof' => [
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf',
                    'max:10240',
                ],
            ]);

        $evidencePaths = [];

        foreach (
            $request->file(
                'evidence',
                []
            )
            as
            $file
        ) {
            $evidencePaths[] =
                $file->store(
                    'transaction-disputes/'
                    . $secureTransaction->reference,
                    'public'
                );
        }

        $returnProofPath =
            null;

        if (
            $request->hasFile(
                'return_proof'
            )
        ) {
            $returnProofPath =
                $request
                    ->file(
                        'return_proof'
                    )
                    ->store(
                        'transaction-disputes/'
                        . $secureTransaction->reference
                        . '/returns',
                        'public'
                    );
        }

        DB::transaction(
            function () use (
                $request,
                $secureTransaction,
                $validated,
                $evidencePaths,
                $returnProofPath
            ) {
                TransactionDispute::create([
                    'secure_transaction_id' =>
                        $secureTransaction->id,

                    'buyer_id' =>
                        $request->user()->id,

                    'seller_id' =>
                        $secureTransaction->seller_id,

                    'reason' =>
                        $validated['reason'],

                    'description' =>
                        $validated['description'],

                    'desired_outcome' =>
                        $validated['desired_outcome'],

                    'evidence' =>
                        $evidencePaths,

                    'return_method' =>
                        $validated['return_method']
                        ??
                        null,

                    'return_proof_path' =>
                        $returnProofPath,

                    'status' =>
                        'open',

                    'opened_at' =>
                        now(),
                ]);

                $secureTransaction->forceFill([
                    'status' =>
                        SecureTransaction::STATUS_DISPUTED,

                    'auto_complete_at' =>
                        null,
                ])->save();
            }
        );

        $secureTransaction
            ->refresh()
            ->load([
                'dispute',
                'buyer',
                'seller',
            ]);

        $communications->buyer(
            $secureTransaction,
            'dispute-opened',
            'Your dispute was submitted',
            'Your dispute has been received. Automatic seller payout has been paused while MidPoint reviews the case.'
        );

        $communications->seller(
            $secureTransaction,
            'dispute-opened',
            'Buyer opened a dispute',
            'The buyer opened a dispute for this transaction. Seller payout has been paused while MidPoint reviews the case.'
        );

        $communications->adminsForDispute(
            $secureTransaction
        );

        return redirect()
            ->route(
                'buyer.transactions.show',
                [
                    'secureTransaction' =>
                        $secureTransaction->public_token,
                ]
            )
            ->with(
                'success',
                'Your dispute has been submitted successfully.'
            );
    }

    protected function authorizeBuyer(
        Request $request,
        SecureTransaction $transaction
    ): void {
        abort_unless(
            (int) $transaction->buyer_id
            ===
            (int) $request->user()->id,
            403
        );
    }
}