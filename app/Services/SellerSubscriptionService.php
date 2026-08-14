<?php

namespace App\Services;

use App\Models\SellerApplication;
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
    | Activate Subscription
    |--------------------------------------------------------------------------
    */

    public function activateFromApplication(
        SellerApplication $application,
        ?string $paymentReference = null
    ): SellerSubscription {

        /*
        |--------------------------------------------------------------------------
        | Make Database Problems Explicit
        |--------------------------------------------------------------------------
        */

        $this->assertRequiredSchema();


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
                | Existing Subscription For This Application
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
                | Existing Active Subscription
                |--------------------------------------------------------------------------
                */

                if (
                    $existing
                    &&
                    $existing
                        ->isCurrentlyActive()
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Repair Application Status If Necessary
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $application->status
                        !==
                        SellerApplication::STATUS_ACTIVE
                    ) {

                        $application->update([

                            'status' =>
                                SellerApplication::STATUS_ACTIVE,

                            'activated_at' =>
                                $application->activated_at
                                ?:
                                now(),

                        ]);
                    }


                    return $existing;
                }


                /*
                |--------------------------------------------------------------------------
                | Expire Older Active Packages
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


                if ($existing) {

                    $oldSubscriptions
                        ->where(
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
                | Subscription Dates
                |--------------------------------------------------------------------------
                */

                $startedAt =
                    now();


                $expiresAt =
                    $this->calculateExpiration(

                        (string)
                        $application
                            ->billing_period,

                        $startedAt

                    );


                /*
                |--------------------------------------------------------------------------
                | Package Price
                |--------------------------------------------------------------------------
                */

                $packagePrice =
                    (float)
                    $application
                        ->package_price;


                /*
                |--------------------------------------------------------------------------
                | Subscription Values
                |--------------------------------------------------------------------------
                */

                $values = [

                    'user_id' =>
                        $application->user_id,

                    'seller_package_id' =>
                        $application
                            ->seller_package_id,

                    'package_name' =>
                        $application
                            ->package_name,

                    /*
                    |--------------------------------------------------------------------------
                    | Support Both Old And New DB Columns
                    |--------------------------------------------------------------------------
                    */

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


                /*
                |--------------------------------------------------------------------------
                | Legacy starts_at Column
                |--------------------------------------------------------------------------
                */

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


                /*
                |--------------------------------------------------------------------------
                | Create Or Repair Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    SellerSubscription::updateOrCreate(

                        [
                            'seller_application_id' =>
                                $application->id,
                        ],

                        $values

                    );


                /*
                |--------------------------------------------------------------------------
                | Activate Application
                |--------------------------------------------------------------------------
                */

                $application->update([

                    'status' =>
                        SellerApplication::STATUS_ACTIVE,

                    'activated_at' =>
                        $application->activated_at
                        ?:
                        $startedAt,

                ]);


                return $subscription
                    ->fresh();

            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Live Database Schema
    |--------------------------------------------------------------------------
    */

    private function assertRequiredSchema(): void
    {
        $required = [

            'seller_application_id',

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


        $missing = [];


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


        if ($missing) {

            throw new RuntimeException(
                'seller_subscriptions schema is missing: '
                .
                implode(
                    ', ',
                    $missing
                )
                .
                '. Run the latest production migrations.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Expiration
    |--------------------------------------------------------------------------
    */

    private function calculateExpiration(
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
                    $subscriptions
                ) use (
                    &$expiredCount
                ) {

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
    | Expire User's Due Subscriptions
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

                $subscription =
                    SellerSubscription::query()

                        ->whereKey(
                            $subscription->id
                        )

                        ->lockForUpdate()

                        ->first();


                if (!$subscription) {

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

            },
            3
        );
    }
}