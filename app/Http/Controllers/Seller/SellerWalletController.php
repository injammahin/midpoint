<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use App\Services\PaystackService;
use App\Support\IdentityNameMatcher;
use App\Services\SellerWithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SellerWalletController extends Controller
{
    public function index(
        Request $request,
        PaystackService $paystack,
        SellerWithdrawalService $withdrawalService
    ) {

        $seller =
            $request->user();


        /*
         * Opportunistically reconcile a few pending withdrawals
         * when seller opens wallet.
         */
        $withdrawalService
            ->reconcilePendingForSeller(
                $seller
            );


        $wallet =
            SellerWallet::query()
                ->firstOrCreate(
                    [
                        'seller_id' =>
                            $seller->id,
                    ],
                    [
                        'currency' =>
                            'NGN',

                        'available_balance' =>
                            0,

                        'pending_withdrawal_balance' =>
                            0,

                        'total_credited' =>
                            0,

                        'total_withdrawn' =>
                            0,
                    ]
                );


        $accounts =
            SellerWithdrawalAccount::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->orderByDesc(
                    'is_active'
                )

                ->latest(
                    'id'
                )

                ->get();


        $kyc =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->first();


        $withdrawals =
            SellerWithdrawal::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->latest(
                    'id'
                )

                ->paginate(
                    10
                )

                ->withQueryString();


        $ledger =
            SellerWalletTransaction::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->latest(
                    'id'
                )

                ->limit(
                    15
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Supported Banks
        |--------------------------------------------------------------------------
        */

        $banks = [];

        $bankLoadError =
            null;


        $banks = [];

        $bankLoadError =
            null;


        try {

            $banksCollection =
                collect(
                    $paystack
                        ->listBanks(
                            'nigeria'
                        )
                )

                    ->filter(
                        function (
                            $bank
                        ) {

                            return
                                !empty(
                                    $bank['code']
                                )
                                &&
                                !empty(
                                    $bank['name']
                                );
                        }
                    );


            /*
            |--------------------------------------------------------------------------
            | Add Paystack Test Bank
            |--------------------------------------------------------------------------
            |
            | Only show this fake/test bank when using sk_test_...
            |
            */

            $isPaystackTestMode =
                \Illuminate\Support\Str::startsWith(
                    (string)
                    config(
                        'services.paystack.secret_key'
                    ),
                    'sk_test_'
                );


            if (
                $isPaystackTestMode
            ) {

                $hasTestBank =
                    $banksCollection
                        ->contains(
                            function (
                                $bank
                            ) {

                                return
                                    (string) (
                                        $bank['code']
                                        ??
                                        ''
                                    )
                                    ===
                                    '001';
                            }
                        );


                if (
                    !$hasTestBank
                ) {

                    $banksCollection
                        ->prepend([
                            'name' =>
                                'Paystack Test Bank',

                            'code' =>
                                '001',
                        ]);
                }
            }


            $banks =
                $banksCollection

                    ->sortBy(
                        function (
                            $bank
                        ) {

                            /*
                            * Keep test bank at top during development.
                            */
                            if (
                                (string)
                                $bank['code']
                                ===
                                '001'
                            ) {

                                return
                                    '000000';
                            }


                            return
                                strtolower(
                                    $bank['name']
                                );
                        }
                    )

                    ->values()

                    ->all();

        } catch (
            Throwable $exception
        ) {

            $bankLoadError =
                'Supported banks could not be loaded right now. Please refresh and try again.';


            Log::warning(
                'Paystack bank list failed on seller wallet.',
                [
                    'seller_id' =>
                        $seller->id,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );
        } catch (
            Throwable $exception
        ) {

            $bankLoadError =
                'Supported banks could not be loaded right now. Please refresh and try again.';


            Log::warning(
                'Paystack bank list failed on seller wallet.',
                [
                    'seller_id' =>
                        $seller->id,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );
        }


        $activeAccount =
            $accounts
                ->firstWhere(
                    'is_active',
                    true
                );


        $kycApproved =
            $kyc
            &&
            $kyc->status
            ===
            SellerKycVerification::STATUS_APPROVED;

        $bankIdentityMatches =
            $kycApproved
            &&
            $activeAccount
            &&
            IdentityNameMatcher::matches(
                $kyc->verified_full_name,
                $activeAccount->account_name
            );

        $minimumWithdrawal =
            (float)
            config(
                'services.paystack.minimum_withdrawal',
                1000
            );


        /*
        |--------------------------------------------------------------------------
        | Tooltip blockers
        |--------------------------------------------------------------------------
        */

        $withdrawalBlockers =
            [];


        if (
            !$activeAccount
            ||
            !$activeAccount
                ->is_verified
        ) {

            $withdrawalBlockers[] =
                'Add, verify, and activate a bank account.';
        }


        if (
            !$kycApproved
        ) {

            $withdrawalBlockers[] =
                'Complete automated identity verification.';

        } elseif (
            $activeAccount
            &&
            !$bankIdentityMatches
        ) {

            $withdrawalBlockers[] =
                'Your active bank account name does not match your verified identity.';
        }


        if (
            (float)
            $wallet
                ->available_balance
            <
            $minimumWithdrawal
        ) {

            $withdrawalBlockers[] =
                'Minimum available balance is ₦'
                .
                number_format(
                    $minimumWithdrawal,
                    0
                )
                .
                '.';
        }


        return view(
            'seller.wallet.index',
            compact(
                'seller',
                'wallet',
                'accounts',
                'activeAccount',
                'bankIdentityMatches',
                'kyc',
                'kycApproved',
                'withdrawals',
                'ledger',
                'banks',
                'bankLoadError',
                'minimumWithdrawal',
                'withdrawalBlockers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Withdraw
    |--------------------------------------------------------------------------
    */

    public function withdraw(
        Request $request,
        SellerWithdrawalService $withdrawals
    ) {

        $validated =
            $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],
            ]);


        try {

            $withdrawal =
                $withdrawals
                    ->requestWithdrawal(
                        $request->user(),
                        (float)
                        $validated[
                            'amount'
                        ]
                    );


            $message =
                $withdrawal->status
                ===
                SellerWithdrawal::STATUS_OTP

                    ? 'Withdrawal created, but Paystack requires transfer OTP/approval. Complete the transfer from your Paystack transfer controls.'

                    : 'Withdrawal submitted successfully. The amount is now reserved while Paystack processes the transfer.';


            return redirect()
                ->route(
                    'seller.wallet'
                )
                ->with(
                    'success',
                    $message
                );

        } catch (
            Throwable $exception
        ) {

            /*
             * Preserve Laravel's normal validation response.
             */
            if (
                $exception
                instanceof
                \Illuminate\Validation\ValidationException
            ) {

                throw $exception;
            }


            Log::error(
                'Seller withdrawal request failed.',
                [
                    'seller_id' =>
                        $request
                            ->user()
                            ->id,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );


            return back()

                ->withInput()

                ->with(
                    'error',
                    'The withdrawal could not be started. Please refresh your wallet before trying again. '
                    .
                    $exception
                        ->getMessage()
                );
        }
    }
}