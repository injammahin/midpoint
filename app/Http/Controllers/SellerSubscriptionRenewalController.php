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
                    )
                        ->where(
                            'is_active',
                            true
                        ),
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Synchronize Expiry
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->expireDueSubscriptionsForUser(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Do Not Renew While Current Plan Is Active
        |--------------------------------------------------------------------------
        */

        $activeSubscription =
            $subscriptions
                ->activeForUser(
                    $user
                );


        if (
            $activeSubscription
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'Your current seller package is still active. Renewal becomes available after it expires.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Package
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

                ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Originally Approved Seller Application
        |--------------------------------------------------------------------------
        |
        | No new application.
        |
        | No new documents.
        |
        | No admin review.
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
                    'A previously approved seller application is required before using quick renewal.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Previous Subscription
        |--------------------------------------------------------------------------
        */

        $previousSubscription =
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
            !$previousSubscription
        ) {

            throw ValidationException::withMessages([
                'seller_package_id' =>
                    'Your previous seller subscription could not be found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Renewal Invoice
        |--------------------------------------------------------------------------
        */

        $invoice =
            DB::transaction(
                function () use (
                    $user,
                    $package,
                    $approvedApplication,
                    $previousSubscription
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock User
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
                    | Recheck Active Package
                    |--------------------------------------------------------------------------
                    */

                    $stillActive =
                        SellerSubscription::query()

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->active()

                            ->lockForUpdate()

                            ->exists();


                    if (
                        $stillActive
                    ) {

                        throw ValidationException::withMessages([
                            'seller_package_id' =>
                                'Your current seller package is still active.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Unpaid Renewal Invoice
                    |--------------------------------------------------------------------------
                    |
                    | Prevent two checkout links for different package renewals
                    | from being active simultaneously.
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
                                    SellerInvoice::TYPE_DOWNGRADE,
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

                        if (
                            (int)
                            $existingInvoice
                                ->seller_package_id
                            ===
                            (int)
                            $package->id
                        ) {

                            return $existingInvoice;
                        }


                        throw ValidationException::withMessages([
                            'seller_package_id' =>
                                'You already have an unpaid package renewal invoice. Complete that payment before selecting another package.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Previous Subscription
                    |--------------------------------------------------------------------------
                    */

                    $previous =
                        SellerSubscription::query()

                            ->whereKey(
                                $previousSubscription->id
                            )

                            ->where(
                                'user_id',
                                $user->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Determine Purchase Type
                    |--------------------------------------------------------------------------
                    */

                    $purchaseType =
                        $this
                            ->determinePurchaseType(
                                $previous,
                                $package
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Invoice
                    |--------------------------------------------------------------------------
                    */

                    return SellerInvoice::create([
                        'invoice_number' =>
                            SellerInvoice::generateInvoiceNumber(),

                        'seller_application_id' =>
                            $approvedApplication->id,

                        'seller_package_id' =>
                            $package->id,

                        'purchase_type' =>
                            $purchaseType,

                        'renewal_of_subscription_id' =>
                            $previous->id,

                        /*
                         * Snapshot current package configuration.
                         */
                        'package_name' =>
                            $package->name,

                        'billing_period' =>
                            $package->billing_period,

                        'product_limit' =>
                            $package->product_limit,

                        'user_id' =>
                            $user->id,

                        'amount' =>
                            $package->price,

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
                            now()
                                ->addDays(
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
        | Show Invoice
        |--------------------------------------------------------------------------
        */

        $label =
            strtolower(
                $invoice
                    ->purchase_type_label
            );


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
                'Your '
                .
                $label
                .
                ' invoice has been created. Complete the Paystack payment below. No new seller application or admin approval is required.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Determine Purchase Type
    |--------------------------------------------------------------------------
    */

    protected function determinePurchaseType(
        SellerSubscription $previous,
        SellerPackage $package
    ): string {

        /*
         * Same package.
         */
        if (
            (int)
            $previous
                ->seller_package_id
            ===
            (int)
            $package->id
        ) {

            return
                SellerInvoice::TYPE_RENEWAL;
        }


        $oldPrice =
            (float) (
                $previous->price
                ?:
                $previous->package_price
            );


        $newPrice =
            (float)
            $package->price;


        if (
            $newPrice >
            $oldPrice
        ) {

            return
                SellerInvoice::TYPE_UPGRADE;
        }


        return
            SellerInvoice::TYPE_DOWNGRADE;
    }
}