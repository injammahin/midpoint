<?php

namespace App\Services;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerSubscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SellerSubscriptionService
{
    /*
    |--------------------------------------------------------------------------
    | Activate Initial Subscription
    |--------------------------------------------------------------------------
    */

    public function activateFromApplication(
        SellerApplication $application,
        ?string $paymentReference = null,
        ?SellerInvoice $invoice = null
    ): SellerSubscription {

        $this
            ->assertRequiredSchema();


        return DB::transaction(
            function () use (
                $application,
                $paymentReference,
                $invoice
            ) {

                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $application->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Resolve Initial Invoice
                |--------------------------------------------------------------------------
                */

                if (
                    $invoice
                ) {

                    $invoice =
                        SellerInvoice::query()

                            ->whereKey(
                                $invoice->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();

                } else {

                    $invoice =
                        SellerInvoice::query()

                            ->where(
                                'seller_application_id',
                                $application->id
                            )

                            ->where(
                                'purchase_type',
                                SellerInvoice::TYPE_INITIAL
                            )

                            ->where(
                                'status',
                                'paid'
                            )

                            ->latest(
                                'id'
                            )

                            ->lockForUpdate()

                            ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Existing Initial Subscription
                |--------------------------------------------------------------------------
                */

                $existing =
                    SellerSubscription::query()

                        ->where(
                            'seller_application_id',
                            $application->id
                        )

                        ->where(
                            'purchase_type',
                            SellerInvoice::TYPE_INITIAL
                        )

                        ->lockForUpdate()

                        ->first();


                /*
                 * Critical idempotency:
                 *
                 * If the old initial subscription already exists,
                 * do NOT create a second initial subscription.
                 */
                if (
                    $existing
                ) {

                    if (
                        $existing
                            ->isCurrentlyActive()
                        &&
                        $application->status
                        !==
                        SellerApplication::STATUS_ACTIVE
                    ) {

                        $application->update([
                            'status' =>
                                SellerApplication::STATUS_ACTIVE,
                        ]);
                    }


                    return $existing;
                }


                /*
                |--------------------------------------------------------------------------
                | Expire Other Active Subscription
                |--------------------------------------------------------------------------
                */

                SellerSubscription::query()

                    ->where(
                        'user_id',
                        $application->user_id
                    )

                    ->where(
                        'status',
                        SellerSubscription::STATUS_ACTIVE
                    )

                    ->update([
                        'status' =>
                            SellerSubscription::STATUS_EXPIRED,

                        'expires_at' =>
                            now(),
                    ]);


                $startedAt =
                    now();


                $expiresAt =
                    $this
                        ->calculateExpiration(
                            (string)
                            $application
                                ->billing_period,
                            $startedAt
                        );


                $packagePrice =
                    (float)
                    $application
                        ->package_price;


                $values = [
                    'user_id' =>
                        $application->user_id,

                    'seller_package_id' =>
                        $application
                            ->seller_package_id,

                    'seller_application_id' =>
                        $application->id,

                    'seller_invoice_id' =>
                        $invoice
                            ?->id,

                    'purchase_type' =>
                        SellerInvoice::TYPE_INITIAL,

                    'renewed_from_subscription_id' =>
                        null,

                    'renewal_sequence' =>
                        1,

                    'package_name' =>
                        $application
                            ->package_name,

                    'package_price' =>
                        $packagePrice,

                    'price' =>
                        $packagePrice,

                    'billing_period' =>
                        $application
                            ->billing_period,

                    'product_limit' =>
                        $application
                            ->product_limit,

                    'status' =>
                        SellerSubscription::STATUS_ACTIVE,

                    'payment_reference' =>
                        $paymentReference,

                    'started_at' =>
                        $startedAt,

                    'expires_at' =>
                        $expiresAt,
                ];


                if (
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'starts_at'
                    )
                ) {

                    $values[
                        'starts_at'
                    ] =
                        $startedAt;
                }


                $subscription =
                    SellerSubscription::create(
                        $values
                    );


                $application->update([
                    'status' =>
                        SellerApplication::STATUS_ACTIVE,

                    'activated_at' =>
                        $application->activated_at
                        ?:
                        $startedAt,
                ]);


                return
                    $subscription
                        ->fresh();
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate Renewal / Upgrade
    |--------------------------------------------------------------------------
    */

    public function activateFromRenewalInvoice(
        SellerInvoice $invoice,
        ?string $paymentReference = null
    ): SellerSubscription {

        $this
            ->assertRequiredSchema();


        return DB::transaction(
            function () use (
                $invoice,
                $paymentReference
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Invoice
                |--------------------------------------------------------------------------
                */

                $invoice =
                    SellerInvoice::query()

                        ->with(
                            'application'
                        )

                        ->whereKey(
                            $invoice->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                if (
                    $invoice
                        ->isInitialPurchase()
                ) {

                    throw new RuntimeException(
                        'Initial seller invoice cannot use the renewal activation flow.'
                    );
                }


                if (
                    $invoice->status
                    !==
                    'paid'
                ) {

                    throw new RuntimeException(
                        'Renewal invoice must be paid before subscription activation.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Idempotency
                |--------------------------------------------------------------------------
                |
                | Callback and webhook can both process the same payment.
                |
                */

                $existing =
                    SellerSubscription::query()

                        ->where(
                            'seller_invoice_id',
                            $invoice->id
                        )

                        ->lockForUpdate()

                        ->first();


                if (
                    $existing
                ) {

                    return $existing;
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Seller
                |--------------------------------------------------------------------------
                */

                User::query()

                    ->whereKey(
                        $invoice->user_id
                    )

                    ->lockForUpdate()

                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Previous Subscription
                |--------------------------------------------------------------------------
                */

                $previous =
                    null;


                if (
                    $invoice
                        ->renewal_of_subscription_id
                ) {

                    $previous =
                        SellerSubscription::query()

                            ->whereKey(
                                $invoice
                                    ->renewal_of_subscription_id
                            )

                            ->where(
                                'user_id',
                                $invoice->user_id
                            )

                            ->lockForUpdate()

                            ->first();
                }


                if (
                    !$previous
                ) {

                    $previous =
                        SellerSubscription::query()

                            ->where(
                                'user_id',
                                $invoice->user_id
                            )

                            ->latest(
                                'id'
                            )

                            ->lockForUpdate()

                            ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Expire Any Old Active Plan
                |--------------------------------------------------------------------------
                */

                SellerSubscription::query()

                    ->where(
                        'user_id',
                        $invoice->user_id
                    )

                    ->where(
                        'status',
                        SellerSubscription::STATUS_ACTIVE
                    )

                    ->update([
                        'status' =>
                            SellerSubscription::STATUS_EXPIRED,

                        'expires_at' =>
                            now(),
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Renewal Sequence
                |--------------------------------------------------------------------------
                */

                $sequence =
                    (
                        (int)
                        SellerSubscription::query()

                            ->where(
                                'user_id',
                                $invoice->user_id
                            )

                            ->max(
                                'renewal_sequence'
                            )
                    )
                    +
                    1;


                $startedAt =
                    now();


                $billingPeriod =
                    $invoice
                        ->effective_billing_period;


                $expiresAt =
                    $this
                        ->calculateExpiration(
                            $billingPeriod,
                            $startedAt
                        );


                /*
                |--------------------------------------------------------------------------
                | Create New Historical Subscription
                |--------------------------------------------------------------------------
                */

                $values = [
                    'user_id' =>
                        $invoice->user_id,

                    'seller_package_id' =>
                        $invoice
                            ->seller_package_id,

                    'seller_application_id' =>
                        $invoice
                            ->seller_application_id,

                    'seller_invoice_id' =>
                        $invoice->id,

                    'purchase_type' =>
                        $invoice
                            ->purchase_type,

                    'renewed_from_subscription_id' =>
                        $previous
                            ?->id,

                    'renewal_sequence' =>
                        $sequence,

                    'package_name' =>
                        $invoice
                            ->effective_package_name,

                    'package_price' =>
                        (float)
                        $invoice
                            ->effective_package_price,

                    'price' =>
                        (float)
                        $invoice
                            ->effective_package_price,

                    'billing_period' =>
                        $billingPeriod,

                    'product_limit' =>
                        $invoice
                            ->effective_product_limit,

                    'status' =>
                        SellerSubscription::STATUS_ACTIVE,

                    'payment_reference' =>
                        $paymentReference,

                    'started_at' =>
                        $startedAt,

                    'expires_at' =>
                        $expiresAt,
                ];


                if (
                    Schema::hasColumn(
                        'seller_subscriptions',
                        'starts_at'
                    )
                ) {

                    $values[
                        'starts_at'
                    ] =
                        $startedAt;
                }


                $subscription =
                    SellerSubscription::create(
                        $values
                    );


                /*
                |--------------------------------------------------------------------------
                | Reactivate Original Approved Application
                |--------------------------------------------------------------------------
                |
                | We do NOT replace its original package/business snapshot.
                | It remains the verification record.
                |
                */

                if (
                    $invoice->application
                ) {

                    $invoice
                        ->application
                        ->update([
                            'status' =>
                                SellerApplication::STATUS_ACTIVE,
                        ]);
                }


                return
                    $subscription
                        ->fresh();
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Package For User
    |--------------------------------------------------------------------------
    */

    public function activeForUser(
        User $user
    ): ?SellerSubscription {

        $this
            ->expireDueSubscriptionsForUser(
                $user
            );


        return SellerSubscription::query()

            ->with([
                'package',
                'application',
                'invoice',
            ])

            ->where(
                'user_id',
                $user->id
            )

            ->active()

            ->latest(
                'id'
            )

            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Expire All Due Subscriptions
    |--------------------------------------------------------------------------
    */

    public function expireDueSubscriptions(): int
    {
        $expiredCount =
            0;


        SellerSubscription::query()

            ->where(
                'status',
                SellerSubscription::STATUS_ACTIVE
            )

            ->whereNotNull(
                'expires_at'
            )

            ->where(
                'expires_at',
                '<=',
                now()
            )

            ->chunkById(
                100,
                function (
                    $items
                ) use (
                    &$expiredCount
                ) {

                    foreach (
                        $items
                        as
                        $subscription
                    ) {

                        if (
                            $this
                                ->expireSubscription(
                                    $subscription
                                )
                        ) {

                            $expiredCount++;
                        }
                    }
                }
            );


        return $expiredCount;
    }


    /*
    |--------------------------------------------------------------------------
    | Expire User Packages
    |--------------------------------------------------------------------------
    */

    public function expireDueSubscriptionsForUser(
        User $user
    ): int {

        $items =
            SellerSubscription::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->where(
                    'status',
                    SellerSubscription::STATUS_ACTIVE
                )

                ->whereNotNull(
                    'expires_at'
                )

                ->where(
                    'expires_at',
                    '<=',
                    now()
                )

                ->get();


        $count =
            0;


        foreach (
            $items
            as
            $subscription
        ) {

            if (
                $this
                    ->expireSubscription(
                        $subscription
                    )
            ) {

                $count++;
            }
        }


        return $count;
    }


    /*
    |--------------------------------------------------------------------------
    | Expire One Subscription
    |--------------------------------------------------------------------------
    */

    protected function expireSubscription(
        SellerSubscription $subscription
    ): bool {

        return DB::transaction(
            function () use (
                $subscription
            ) {

                $subscription =
                    SellerSubscription::query()

                        ->whereKey(
                            $subscription->id
                        )

                        ->lockForUpdate()

                        ->first();


                if (
                    !$subscription
                ) {

                    return false;
                }


                if (
                    $subscription->status
                    !==
                    SellerSubscription::STATUS_ACTIVE
                ) {

                    return false;
                }


                if (
                    !$subscription->expires_at
                    ||
                    $subscription
                        ->expires_at
                        ->isFuture()
                ) {

                    return false;
                }


                $subscription->update([
                    'status' =>
                        SellerSubscription::STATUS_EXPIRED,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Only Expire Application If User Has No Other Active Package
                |--------------------------------------------------------------------------
                */

                $hasOtherActive =
                    SellerSubscription::query()

                        ->where(
                            'user_id',
                            $subscription
                                ->user_id
                        )

                        ->where(
                            'id',
                            '!=',
                            $subscription
                                ->id
                        )

                        ->active()

                        ->exists();


                if (
                    !$hasOtherActive
                    &&
                    $subscription
                        ->seller_application_id
                ) {

                    SellerApplication::query()

                        ->whereKey(
                            $subscription
                                ->seller_application_id
                        )

                        ->where(
                            'status',
                            SellerApplication::STATUS_ACTIVE
                        )

                        ->update([
                            'status' =>
                                SellerApplication::STATUS_EXPIRED,
                        ]);
                }


                return true;
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expiration Date
    |--------------------------------------------------------------------------
    */

    protected function calculateExpiration(
        string $billingPeriod,
        Carbon $startedAt
    ): Carbon {

        $period =
            strtolower(
                trim(
                    $billingPeriod
                )
            );


        if (
            in_array(
                $period,
                [
                    'year',
                    'yearly',
                    'annual',
                    'annually',
                ],
                true
            )
        ) {

            return $startedAt
                ->copy()
                ->addYear();
        }


        return $startedAt
            ->copy()
            ->addMonthNoOverflow();
    }


    /*
    |--------------------------------------------------------------------------
    | Database Schema
    |--------------------------------------------------------------------------
    */

    protected function assertRequiredSchema(): void
    {
        $required = [
            'seller_application_id',
            'seller_invoice_id',
            'purchase_type',
            'renewed_from_subscription_id',
            'renewal_sequence',
            'package_name',
            'package_price',
            'price',
            'billing_period',
            'product_limit',
            'status',
            'payment_reference',
            'started_at',
            'expires_at',
        ];


        $missing =
            [];


        foreach (
            $required
            as
            $column
        ) {

            if (
                !Schema::hasColumn(
                    'seller_subscriptions',
                    $column
                )
            ) {

                $missing[] =
                    $column;
            }
        }


        if (
            $missing
        ) {

            throw new RuntimeException(
                'seller_subscriptions schema is missing: '
                .
                implode(
                    ', ',
                    $missing
                )
                .
                '. Run php artisan migrate.'
            );
        }
    }
}