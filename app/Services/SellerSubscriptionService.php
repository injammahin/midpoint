<?php

namespace App\Services;

use App\Models\SellerApplication;
use App\Models\SellerSubscription;
use App\Models\User;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SellerSubscriptionService
{
    /*
    |--------------------------------------------------------------------------
    | Activate Seller Subscription
    |--------------------------------------------------------------------------
    */

    public function activateFromApplication(
        SellerApplication $application,
        ?string $paymentReference = null
    ): SellerSubscription {

        return DB::transaction(
            function () use (
                $application,
                $paymentReference
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Application
                |--------------------------------------------------------------------------
                */

                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $application->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Existing Subscription For Same Application
                |--------------------------------------------------------------------------
                */

                $existing =
                    SellerSubscription::query()

                        ->where(
                            'seller_application_id',
                            $application->id
                        )

                        ->lockForUpdate()

                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Already Active
                |--------------------------------------------------------------------------
                */

                if (
                    $existing
                    &&
                    $existing->isCurrentlyActive()
                ) {

                    return $existing;
                }


                /*
                |--------------------------------------------------------------------------
                | Expire Previous Active Subscription
                |--------------------------------------------------------------------------
                */

                $oldSubscriptions =
                    SellerSubscription::query()

                        ->where(
                            'user_id',
                            $application->user_id
                        )

                        ->where(
                            'status',
                            SellerSubscription::STATUS_ACTIVE
                        );


                /*
                |--------------------------------------------------------------------------
                | Do Not Expire Current Record
                |--------------------------------------------------------------------------
                */

                if (
                    $existing
                ) {

                    $oldSubscriptions->where(
                        'id',
                        '!=',
                        $existing->id
                    );
                }


                $oldSubscriptions->update([

                    'status' =>
                        SellerSubscription::STATUS_EXPIRED,

                    'expires_at' =>
                        now(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Start Date
                |--------------------------------------------------------------------------
                */

                $startedAt =
                    now();


                /*
                |--------------------------------------------------------------------------
                | Expiration Date
                |--------------------------------------------------------------------------
                */

                $expiresAt =
                    $this->calculateExpiration(
                        $application->billing_period,
                        $startedAt
                    );


                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                $packagePrice =
                    (float)
                    $application->package_price;


                /*
                |--------------------------------------------------------------------------
                | Create / Update Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    SellerSubscription::updateOrCreate(

                        [
                            'seller_application_id' =>
                                $application->id,
                        ],

                        [
                            'user_id' =>
                                $application->user_id,

                            'seller_package_id' =>
                                $application->seller_package_id,

                            'package_name' =>
                                $application->package_name,


                            /*
                            |--------------------------------------------------------------------------
                            | Existing Database Compatibility
                            |--------------------------------------------------------------------------
                            */

                            'package_price' =>
                                $packagePrice,

                            'price' =>
                                $packagePrice,


                            'billing_period' =>
                                $application->billing_period,

                            'product_limit' =>
                                $application->product_limit,

                            'status' =>
                                SellerSubscription::STATUS_ACTIVE,

                            'payment_reference' =>
                                $paymentReference,

                            'started_at' =>
                                $startedAt,

                            'expires_at' =>
                                $expiresAt,

                        ]
                    );


                /*
                |--------------------------------------------------------------------------
                | Activate Seller Application
                |--------------------------------------------------------------------------
                */

                $application->update([

                    'status' =>
                        SellerApplication::STATUS_ACTIVE,

                    'activated_at' =>
                        $startedAt,

                ]);


                return $subscription;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Expiration
    |--------------------------------------------------------------------------
    */

    private function calculateExpiration(
        string $billingPeriod,
        Carbon $startedAt
    ): Carbon {

        /*
        |--------------------------------------------------------------------------
        | Yearly
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                $billingPeriod
            )
            ===
            'year'
        ) {

            return $startedAt
                ->copy()
                ->addYear();
        }


        /*
        |--------------------------------------------------------------------------
        | Monthly
        |--------------------------------------------------------------------------
        */

        return $startedAt
            ->copy()
            ->addMonthNoOverflow();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current Active Plan
    |--------------------------------------------------------------------------
    */

    public function activeForUser(
        User $user
    ): ?SellerSubscription {

        /*
        |--------------------------------------------------------------------------
        | Expire Due Plan First
        |--------------------------------------------------------------------------
        */

        $this
            ->expireDueSubscriptionsForUser(
                $user
            );


        /*
        |--------------------------------------------------------------------------
        | Get Active
        |--------------------------------------------------------------------------
        */

        return SellerSubscription::query()

            ->with([
                'package',
                'application',
            ])

            ->where(
                'user_id',
                $user->id
            )

            ->active()

            ->latest('id')

            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Expire All Due Plans
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
                function ($subscriptions) use (&$expiredCount) {

                    foreach (
                        $subscriptions
                        as
                        $subscription
                    ) {

                        if (
                            $this->expireSubscription(
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
    | Expire User's Due Plans
    |--------------------------------------------------------------------------
    */

    public function expireDueSubscriptionsForUser(
        User $user
    ): int {

        $subscriptions =
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
            $subscriptions
            as
            $subscription
        ) {

            if (
                $this->expireSubscription(
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

    private function expireSubscription(
        SellerSubscription $subscription
    ): bool {

        return DB::transaction(
            function () use (
                $subscription
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock
                |--------------------------------------------------------------------------
                */

                $subscription =
                    SellerSubscription::query()

                        ->whereKey(
                            $subscription->id
                        )

                        ->lockForUpdate()

                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Missing
                |--------------------------------------------------------------------------
                */

                if (
                    !$subscription
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Already Expired
                |--------------------------------------------------------------------------
                */

                if (
                    $subscription->status
                    !==
                    SellerSubscription::STATUS_ACTIVE
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Still Active By Date
                |--------------------------------------------------------------------------
                */

                if (
                    !$subscription->expires_at
                    ||
                    $subscription
                        ->expires_at
                        ->isFuture()
                ) {

                    return false;
                }


                /*
                |--------------------------------------------------------------------------
                | Expire Subscription
                |--------------------------------------------------------------------------
                */

                $subscription->update([

                    'status' =>
                        SellerSubscription::STATUS_EXPIRED,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Expire Related Seller Application
                |--------------------------------------------------------------------------
                */

                if (
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
            }
        );
    }
}