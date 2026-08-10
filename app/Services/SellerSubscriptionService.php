<?php

namespace App\Services;

use App\Models\SellerApplication;
use App\Models\SellerSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SellerSubscriptionService
{
    public function activateFromApplication(
        SellerApplication $application,
        ?string $paymentReference = null
    ): SellerSubscription {

        return DB::transaction(
            function () use (
                $application,
                $paymentReference
            ) {

                $user =
                    User::query()
                        ->whereKey(
                            $application->user_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Cancel Existing Subscription
                |--------------------------------------------------------------------------
                */

                SellerSubscription::query()

                    ->where(
                        'user_id',
                        $user->id
                    )

                    ->where(
                        'status',
                        'active'
                    )

                    ->update([
                        'status' =>
                            'cancelled',

                        'cancelled_at' =>
                            now(),
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Calculate Expiry
                |--------------------------------------------------------------------------
                */

                $expiresAt =
                    $application
                        ->billing_period
                    ===
                    'year'

                        ? now()->addYear()

                        : now()
                            ->addMonthNoOverflow();


                /*
                |--------------------------------------------------------------------------
                | Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    SellerSubscription::create([

                        'user_id' =>
                            $user->id,

                        'seller_package_id' =>
                            $application
                                ->seller_package_id,

                        'package_name' =>
                            $application
                                ->package_name,

                        'package_price' =>
                            $application
                                ->package_price,

                        'billing_period' =>
                            $application
                                ->billing_period,

                        'product_limit' =>
                            $application
                                ->product_limit,

                        'status' =>
                            'active',

                        'payment_reference' =>
                            $paymentReference,

                        'starts_at' =>
                            now(),

                        'expires_at' =>
                            $expiresAt,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Switch Account To Seller Mode
                |--------------------------------------------------------------------------
                */

                $user->update([
                    'preferred_role' =>
                        'seller',
                ]);


                return $subscription;
            }
        );
    }
}