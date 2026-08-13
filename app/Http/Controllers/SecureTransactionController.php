<?php

namespace App\Http\Controllers;

use App\Models\SecureTransaction;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SecureTransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Buyer Opens Secure Link
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        SecureTransaction $secureTransaction
    ) {
        /*
        |--------------------------------------------------------------------------
        | Guest
        |--------------------------------------------------------------------------
        |
        | Send them to MidPoint login and remember this exact transaction.
        |
        */

        if (!Auth::check()) {

            $returnUrl =
                route(
                    'secure-transactions.show',
                    $secureTransaction
                );


            $request
                ->session()
                ->put(
                    'url.intended',
                    $returnUrl
                );


            return redirect()
                ->route(
                    'login',
                    [
                        'redirect' =>
                            $returnUrl,
                    ]
                )
                ->with(
                    'success',
                    'Log in to continue to your secure MidPoint transaction.'
                );
        }


        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Active Account Required
        |--------------------------------------------------------------------------
        */

        if (
            !$user->status
        ) {

            Auth::logout();


            $request
                ->session()
                ->invalidate();


            $request
                ->session()
                ->regenerateToken();


            return redirect()
                ->route(
                    'login'
                )
                ->withErrors([

                    'login' =>
                        'Your MidPoint account is currently inactive.',

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Admin Cannot Be Buyer
        |--------------------------------------------------------------------------
        */

        abort_if(
            $user->canAccessAdminPanel(),
            403,
            'Administrator accounts cannot participate as transaction buyers.'
        );


        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */

        if (
            !$user->hasVerifiedEmail()
        ) {

            $request
                ->session()
                ->put(
                    'url.intended',
                    route(
                        'secure-transactions.show',
                        $secureTransaction
                    )
                );


            return redirect()
                ->route(
                    'verification.notice'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Seller Opened Their Own Buyer Link
        |--------------------------------------------------------------------------
        */

        if (
            (int)
            $secureTransaction
                ->seller_id
            ===
            (int)
            $user->id
        ) {

            return redirect()
                ->route(
                    'seller.transactions.generated',
                    $secureTransaction
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer Email Must Match
        |--------------------------------------------------------------------------
        |
        | This prevents a leaked link from being claimed by another MidPoint user.
        |
        */

        if (
            strtolower(
                trim(
                    $user->email
                )
            )
            !==
            strtolower(
                trim(
                    $secureTransaction
                        ->buyer_email
                )
            )
        ) {

            return response()
                ->view(
                    'frontend.pages.secure-transaction-email-mismatch',
                    [
                        'transaction' =>
                            $secureTransaction,

                        'maskedEmail' =>
                            $this->maskEmail(
                                $secureTransaction
                                    ->buyer_email
                            ),
                    ],
                    403
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Link Expiry
        |--------------------------------------------------------------------------
        */

        if (
            $secureTransaction
                ->isLinkExpired()

            &&

            $secureTransaction
                ->payment_status
            ===
            SecureTransaction::PAYMENT_UNPAID
        ) {

            return response()
                ->view(
                    'frontend.pages.secure-transaction-expired',
                    [
                        'transaction' =>
                            $secureTransaction,
                    ],
                    410
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer Claim
        |--------------------------------------------------------------------------
        |
        | The first verified matching buyer account becomes permanently attached.
        |
        */

        $secureTransaction =
            DB::transaction(
                function () use (
                    $secureTransaction,
                    $user
                ) {

                    $locked =
                        SecureTransaction::query()

                            ->whereKey(
                                $secureTransaction
                                    ->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Already Claimed By Someone Else
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $locked->buyer_id
                        &&
                        (int)
                        $locked->buyer_id
                        !==
                        (int)
                        $user->id
                    ) {

                        abort(
                            403,
                            'This secure transaction belongs to another buyer account.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Claim
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$locked
                            ->buyer_id
                    ) {

                        $locked->update([

                            'buyer_id' =>
                                $user->id,

                            'claimed_at' =>
                                now(),

                        ]);
                    }


                    return $locked;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Seller Data
        |--------------------------------------------------------------------------
        */

        $secureTransaction->load([

            'seller.sellerBusinessProfile',

            'seller.activeSellerSubscription.application',

            'seller.activeSellerSubscription.package',

            'product',

            'buyer',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Seller Must Still Have Active Package
        |--------------------------------------------------------------------------
        */


        $secureTransaction->load([

            'seller.sellerBusinessProfile',

            'seller.activeSellerSubscription.application',

            'seller.activeSellerSubscription.package',

            'product',

            'buyer',

            'successfulPayment',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Seller Package Is Optional
        |--------------------------------------------------------------------------
        */

        $sellerPlan =
            $secureTransaction
                ->seller
                ->activeSellerSubscription;


        /*
        |--------------------------------------------------------------------------
        | Buyer Transaction Page
        |--------------------------------------------------------------------------
        */

        return view(
            'frontend.pages.secure-transaction',
            [

                'transaction' =>
                    $secureTransaction,

                'sellerPlan' =>
                    $sellerPlan,

            ]
        );


        return view(
            'frontend.pages.secure-transaction',
            [
                'transaction' =>
                    $secureTransaction,

                'sellerPlan' =>
                    $sellerPlan,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mask Buyer Email
    |--------------------------------------------------------------------------
    */

    private function maskEmail(
        string $email
    ): string {

        [$name, $domain] =
            array_pad(
                explode(
                    '@',
                    $email,
                    2
                ),
                2,
                ''
            );


        if (
            strlen(
                $name
            )
            <=
            2
        ) {

            $maskedName =
                substr(
                    $name,
                    0,
                    1
                )
                .
                '**';

        } else {

            $maskedName =
                substr(
                    $name,
                    0,
                    2
                )
                .
                str_repeat(
                    '*',
                    max(
                        3,
                        strlen(
                            $name
                        )
                        -
                        2
                    )
                );
        }


        return
            $maskedName
            .
            '@'
            .
            $domain;
    }
}