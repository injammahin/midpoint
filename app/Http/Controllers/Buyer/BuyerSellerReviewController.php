<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use App\Models\SellerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuyerSellerReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Store Seller Review
    |--------------------------------------------------------------------------
    |
    | A buyer may review a seller only when:
    |
    | - the transaction belongs to this buyer
    | - payment was completed
    | - the transaction is completed
    | - there is a real seller attached
    | - this transaction has not already been reviewed
    |
    | One review is allowed per secure transaction.
    |
    */

    public function store(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        $buyer =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Validate Review Input
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate(
                [
                    'rating' => [
                        'required',
                        'integer',
                        'between:1,5',
                    ],

                    'review' => [
                        'required',
                        'string',
                        'max:10000',
                    ],
                ],
                [
                    'rating.required' =>
                        'Please select a star rating.',

                    'rating.integer' =>
                        'The selected rating is invalid.',

                    'rating.between' =>
                        'The rating must be between 1 and 5 stars.',

                    'review.required' =>
                        'Please write a review for the seller.',

                    'review.max' =>
                        'Your review cannot exceed 10,000 characters.',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Buyer Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int)
            $secureTransaction->buyer_id
            ===
            (int)
            $buyer->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Seller Required
        |--------------------------------------------------------------------------
        */

        if (
            !$secureTransaction->seller_id
        ) {

            throw ValidationException::withMessages([
                'review' =>
                    'This transaction does not have a seller to review.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction Must Be Fully Completed
        |--------------------------------------------------------------------------
        */

        if (
            $secureTransaction->payment_status
            !==
            SecureTransaction::PAYMENT_PAID
            ||
            $secureTransaction->status
            !==
            SecureTransaction::STATUS_COMPLETED
        ) {

            throw ValidationException::withMessages([
                'review' =>
                    'You can review this seller only after the transaction is completed.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer Cannot Review Themself
        |--------------------------------------------------------------------------
        */

        abort_if(
            (int)
            $secureTransaction->seller_id
            ===
            (int)
            $buyer->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Create Review Safely
        |--------------------------------------------------------------------------
        |
        | We lock the secure transaction and use firstOrCreate together with
        | the database unique key. This prevents double reviews if the buyer
        | double-clicks the submit button or two requests arrive together.
        |
        */

        $created =
            false;


        DB::transaction(
            function () use (
                $secureTransaction,
                $buyer,
                $validated,
                &$created
            ) {

                $lockedTransaction =
                    SecureTransaction::query()

                        ->whereKey(
                            $secureTransaction->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Re-check State Inside Lock
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $lockedTransaction->buyer_id
                    !==
                    (int)
                    $buyer->id
                ) {

                    abort(
                        403
                    );
                }


                if (
                    $lockedTransaction->payment_status
                    !==
                    SecureTransaction::PAYMENT_PAID
                    ||
                    $lockedTransaction->status
                    !==
                    SecureTransaction::STATUS_COMPLETED
                ) {

                    throw ValidationException::withMessages([
                        'review' =>
                            'You can review this seller only after the transaction is completed.',
                    ]);
                }


                $review =
                    SellerReview::query()
                        ->firstOrCreate(
                            [
                                'secure_transaction_id' =>
                                    $lockedTransaction->id,
                            ],
                            [
                                'seller_id' =>
                                    $lockedTransaction->seller_id,

                                'buyer_id' =>
                                    $buyer->id,

                                'seller_product_id' =>
                                    $lockedTransaction->seller_product_id,

                                'rating' =>
                                    (int)
                                    $validated['rating'],

                                'review' =>
                                    trim(
                                        $validated['review']
                                    ),

                                'is_published' =>
                                    true,
                            ]
                        );


                $created =
                    $review->wasRecentlyCreated;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Already Reviewed
        |--------------------------------------------------------------------------
        */

        if (
            !$created
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
                    'success',
                    'You have already reviewed this transaction.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

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
                'Thank you. Your seller review has been published.'
            );
    }
}
