<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerPackage;
use App\Models\SellerSubscription;
use App\Models\User;
use App\Services\SellerSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerSubscriptionRenewalController extends Controller
{
    public function store(
        Request $request,
        SellerSubscriptionService $subscriptions
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Validate Package
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'seller_package_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'seller_packages',
                        'id'
                    )->where(
                        'is_active',
                        true
                    ),
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Synchronize Expired Subscription
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Existing Approved Seller Application
        |--------------------------------------------------------------------------
        |
        | Renewal/upgrade does NOT require another seller application.
        |
        */

        $approvedApplication =
            SellerApplication::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->whereNotNull(
                    'approved_at'
                )

                ->whereIn(
                    'status',
                    [
                        SellerApplication::STATUS_ACTIVE,
                        SellerApplication::STATUS_EXPIRED,
                    ]
                )

                ->latest(
                    'approved_at'
                )

                ->latest(
                    'id'
                )

                ->first();


        if (
            !$approvedApplication
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'A previously approved seller application is required before using package renewal or upgrade.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Must Already Have Purchased A Package
        |--------------------------------------------------------------------------
        */

        $latestSubscription =
            SellerSubscription::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->latest(
                    'id'
                )

                ->first();


        if (
            !$latestSubscription
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'Your previous seller subscription could not be found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Renewal / Upgrade Invoice
        |--------------------------------------------------------------------------
        */

        $invoice =
            DB::transaction(
                function () use (
                    $user,
                    $validated,
                    $approvedApplication
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Seller
                    |--------------------------------------------------------------------------
                    */

                    User::query()

                        ->whereKey(
                            $user->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Selected Package
                    |--------------------------------------------------------------------------
                    */

                    $package =
                        SellerPackage::query()

                            ->whereKey(
                                $validated[
                                    'seller_package_id'
                                ]
                            )

                            ->where(
                                'is_active',
                                true
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Approved Seller Application
                    |--------------------------------------------------------------------------
                    */

                    $application =
                        SellerApplication::query()

                            ->whereKey(
                                $approvedApplication->id
                            )

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Current Active Subscription
                    |--------------------------------------------------------------------------
                    */

                    $activeSubscription =
                        SellerSubscription::query()

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->active()

                            ->latest(
                                'id'
                            )

                            ->lockForUpdate()

                            ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Reference Subscription
                    |--------------------------------------------------------------------------
                    |
                    | Active:
                    | use current active package.
                    |
                    | Expired:
                    | use seller's latest historical package.
                    |
                    */

                    $previous =
                        $activeSubscription;


                    if (
                        !$previous
                    ) {

                        $previous =
                            SellerSubscription::query()

                                ->where(
                                    'user_id',
                                    $user->id
                                )

                                ->latest(
                                    'id'
                                )

                                ->lockForUpdate()

                                ->first();
                    }


                    if (
                        !$previous
                    ) {

                        throw ValidationException::withMessages([
                            'seller_package_id' =>
                                'Your previous seller subscription could not be found.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Determine Renewal / Upgrade
                    |--------------------------------------------------------------------------
                    */

                    $purchaseType =
                        $this
                            ->determinePurchaseType(
                                $previous,
                                $package,
                                (bool) $activeSubscription
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Unpaid Recurring Invoice
                    |--------------------------------------------------------------------------
                    |
                    | Seller cannot create several unpaid upgrade invoices.
                    |
                    */

                    $existingInvoice =
                        SellerInvoice::query()

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->where(
                                'status',
                                'unpaid'
                            )

                            ->whereIn(
                                'purchase_type',
                                [
                                    SellerInvoice::TYPE_RENEWAL,
                                    SellerInvoice::TYPE_UPGRADE,
                                ]
                            )

                            ->lockForUpdate()

                            ->latest(
                                'id'
                            )

                            ->first();


                    if (
                        $existingInvoice
                    ) {

                        /*
                         * Same selected package:
                         * return existing invoice instead of creating duplicate.
                         */

                        if (
                            (int)
                            $existingInvoice
                                ->seller_package_id
                            ===
                            (int)
                            $package->id
                        ) {

                            return
                                $existingInvoice;
                        }


                        throw ValidationException::withMessages([
                            'seller_package_id' =>
                                'You already have an unpaid package invoice. Complete that payment before selecting another package.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Target Package Price
                    |--------------------------------------------------------------------------
                    */

                    $packagePrice =
                        round(
                            (float) $package->price,
                            2
                        );


                    $prorationCredit =
                        0.00;


                    $prorationUsedAmount =
                        0.00;


                    $prorationCalculatedAt =
                        null;


                    /*
                    |--------------------------------------------------------------------------
                    | Active Package Upgrade Proration
                    |--------------------------------------------------------------------------
                    |
                    | Only an ACTIVE package gets unused-plan credit.
                    |
                    | If the package is already expired, there is no unused value.
                    |
                    */

                    if (
                        $activeSubscription
                        &&
                        $purchaseType
                        ===
                        SellerInvoice::TYPE_UPGRADE
                    ) {

                        $proration =
                            $this
                                ->calculateUpgradeProration(
                                    $activeSubscription,
                                    $package
                                );


                        $prorationCredit =
                            $proration[
                                'credit'
                            ];


                        $prorationUsedAmount =
                            $proration[
                                'used_amount'
                            ];


                        $prorationCalculatedAt =
                            now();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Final Amount Due
                    |--------------------------------------------------------------------------
                    */

                    $amountDue =
                        round(
                            $packagePrice
                            -
                            $prorationCredit,
                            2
                        );


                    if (
                        $amountDue <= 0
                    ) {

                        throw ValidationException::withMessages([
                            'seller_package_id' =>
                                'The calculated package amount is invalid. Please contact Midpoint support.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create Invoice
                    |--------------------------------------------------------------------------
                    */

                    return SellerInvoice::create([
                        'invoice_number' =>
                            SellerInvoice::generateInvoiceNumber(),

                        'seller_application_id' =>
                            $application->id,

                        'seller_package_id' =>
                            $package->id,

                        'purchase_type' =>
                            $purchaseType,

                        'renewal_of_subscription_id' =>
                            $previous->id,


                        /*
                         * Target package snapshot.
                         */

                        'package_name' =>
                            $package->name,

                        'billing_period' =>
                            $package->billing_period,

                        'product_limit' =>
                            $package->product_limit,

                        'package_price' =>
                            $packagePrice,


                        /*
                         * Upgrade proration snapshot.
                         */

                        'proration_credit' =>
                            $prorationCredit,

                        'proration_used_amount' =>
                            $prorationUsedAmount,

                        'proration_calculated_at' =>
                            $prorationCalculatedAt,


                        'user_id' =>
                            $user->id,


                        /*
                         * Actual amount seller pays.
                         */

                        'amount' =>
                            $amountDue,

                        'currency' =>
                            'NGN',

                        'status' =>
                            'unpaid',

                        'payment_method' =>
                            null,

                        'payment_reference' =>
                            null,

                        'issued_at' =>
                            now(),

                        'due_at' =>
                            now()->addDays(
                                7
                            ),

                        'paid_at' =>
                            null,
                    ]);
                },
                3
            );


        /*
        |--------------------------------------------------------------------------
        | Open Payment Method Modal
        |--------------------------------------------------------------------------
        */

        $label =
            strtolower(
                $invoice
                    ->purchase_type_label
            );


        $message =
            'Your '
            .
            $label
            .
            ' invoice is ready.';


        if (
            (float)
            $invoice
                ->proration_credit
            >
            0
        ) {

            $message .=
                ' An unused-plan credit of ₦'
                .
                number_format(
                    (float)
                    $invoice
                        ->proration_credit,
                    2
                )
                .
                ' has already been applied.';
        }


        return redirect()

            ->to(
                route(
                    'verified-sellers'
                )
                .
                '#seller-invoice'
            )

            ->with(
                'success',
                $message
                .
                ' Choose Midpoint Wallet or Paystack to complete payment.'
            )

            ->with(
                'open_package_payment_modal',
                true
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Determine Purchase Type
    |--------------------------------------------------------------------------
    */

    protected function determinePurchaseType(
        SellerSubscription $previous,
        SellerPackage $package,
        bool $hasActiveSubscription
    ): string {

        $samePackage =
            (int)
            $previous
                ->seller_package_id
            ===
            (int)
            $package->id;


        $oldPrice =
            round(
                (float) (
                    $previous
                        ->package_price

                    ?:

                    $previous
                        ->price
                ),
                2
            );


        $newPrice =
            round(
                (float)
                $package
                    ->price,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Active Same Package
        |--------------------------------------------------------------------------
        |
        | Do not allow early renewal.
        |
        */

        if (
            $hasActiveSubscription
            &&
            $samePackage
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'Your current seller package is still active. You can upgrade now, but renewal of the same package becomes available after it expires.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Same Expired Package = Renewal
        |--------------------------------------------------------------------------
        */

        if (
            $samePackage
        ) {

            return
                SellerInvoice::TYPE_RENEWAL;
        }


        /*
        |--------------------------------------------------------------------------
        | Different Package Must Have Higher Price
        |--------------------------------------------------------------------------
        */

        if (
            $newPrice
            >
            $oldPrice
        ) {

            return
                SellerInvoice::TYPE_UPGRADE;
        }


        /*
        |--------------------------------------------------------------------------
        | Downgrade
        |--------------------------------------------------------------------------
        */

        throw ValidationException::withMessages([
            'seller_package_id' =>
                'Package downgrades are not allowed. You can renew your current package or upgrade to a higher-priced package.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Exact Unused Value
    |--------------------------------------------------------------------------
    |
    | We use exact subscription seconds instead of rounded days.
    |
    */

    protected function calculateUpgradeProration(
        SellerSubscription $current,
        SellerPackage $targetPackage
    ): array {

        $startedAt =
            $current
                ->started_at

            ?:

            $current
                ->starts_at

            ?:

            $current
                ->created_at;


        $expiresAt =
            $current
                ->expires_at;


        if (
            !$startedAt
            ||
            !$expiresAt
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'Midpoint could not calculate the unused value of your current package. Please contact support before upgrading.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Subscription Duration
        |--------------------------------------------------------------------------
        */

        $totalSeconds =
            (int)
            $expiresAt
                ->timestamp
            -
            (int)
            $startedAt
                ->timestamp;


        /*
        |--------------------------------------------------------------------------
        | Remaining Subscription Time
        |--------------------------------------------------------------------------
        */

        $remainingSeconds =
            (int)
            $expiresAt
                ->timestamp
            -
            (int)
            now()
                ->timestamp;


        /*
        |--------------------------------------------------------------------------
        | Current Package Full Price
        |--------------------------------------------------------------------------
        */

        $currentPackagePrice =
            round(
                (float) (
                    $current
                        ->package_price

                    ?:

                    $current
                        ->price
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Target Package Full Price
        |--------------------------------------------------------------------------
        */

        $targetPackagePrice =
            round(
                (float)
                $targetPackage
                    ->price,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Upgrade
        |--------------------------------------------------------------------------
        */

        if (
            $currentPackagePrice <= 0
            ||
            $targetPackagePrice
            <=
            $currentPackagePrice
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'The selected package is not a valid higher-priced upgrade.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Nothing Remaining
        |--------------------------------------------------------------------------
        */

        if (
            $totalSeconds <= 0
            ||
            $remainingSeconds <= 0
        ) {

            return [
                'credit' =>
                    0.00,

                'used_amount' =>
                    $currentPackagePrice,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Protect Against Incorrect Future Dates
        |--------------------------------------------------------------------------
        */

        $remainingSeconds =
            min(
                $remainingSeconds,
                $totalSeconds
            );


        /*
        |--------------------------------------------------------------------------
        | Remaining Ratio
        |--------------------------------------------------------------------------
        */

        $remainingRatio =
            $remainingSeconds
            /
            $totalSeconds;


        /*
        |--------------------------------------------------------------------------
        | Unused Monetary Credit
        |--------------------------------------------------------------------------
        */

        $credit =
            round(
                $currentPackagePrice
                *
                $remainingRatio,
                2
            );


        /*
         * Credit must never exceed original package amount.
         */

        $credit =
            min(
                $currentPackagePrice,
                max(
                    0,
                    $credit
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Amount Already Used
        |--------------------------------------------------------------------------
        */

        $usedAmount =
            round(
                $currentPackagePrice
                -
                $credit,
                2
            );


        return [
            'credit' =>
                $credit,

            'used_amount' =>
                $usedAmount,
        ];
    }
}