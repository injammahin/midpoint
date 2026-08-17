<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use App\Services\PaystackService;
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
        |--------------------------------------------------------------------------
        | Reconcile Pending
        |--------------------------------------------------------------------------
        */

        $withdrawalService
            ->reconcilePendingForSeller(
                $seller
            );


        /*
        |--------------------------------------------------------------------------
        | Wallet
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Bank Accounts
        |--------------------------------------------------------------------------
        */

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


        $activeAccount =
            $accounts
                ->firstWhere(
                    'is_active',
                    true
                );


        /*
        |--------------------------------------------------------------------------
        | KYC
        |--------------------------------------------------------------------------
        */

        $kyc =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->first();


        $kycApproved =
            $kyc

            &&

            $kyc->status
            ===
            SellerKycVerification::STATUS_APPROVED;


        /*
        |--------------------------------------------------------------------------
        | Automated KYC Bank Match
        |--------------------------------------------------------------------------
        */

        $bankIdentityMatches =
            $kycApproved

            &&

            $activeAccount

            &&

            $kyc->bank_name_match
            !==
            false;


        /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Wallet Ledger
        |--------------------------------------------------------------------------
        */

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
        | Banks
        |--------------------------------------------------------------------------
        */

        $banks =
            [];


        $bankLoadError =
            null;


        try {

            $banks =
                collect(
                    $paystack
                        ->listBanks(
                            'nigeria'
                        )
                )

                    ->filter(
                        fn ($bank) =>
                            !empty(
                                $bank[
                                    'code'
                                ]
                            )

                            &&

                            !empty(
                                $bank[
                                    'name'
                                ]
                            )
                    )

                    ->sortBy(
                        'name'
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
        }


        /*
        |--------------------------------------------------------------------------
        | Minimum
        |--------------------------------------------------------------------------
        */

        $minimumWithdrawal =
            (float)
            config(
                'services.paystack.minimum_withdrawal',
                1000
            );


        /*
        |--------------------------------------------------------------------------
        | Blockers
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
                'Your active bank account does not match your verified identity.';
        }


        if (
            (
                (float)
                $wallet
                    ->available_balance
            )
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

                'kyc',

                'kycApproved',

                'bankIdentityMatches',

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

                        $request
                            ->user(),

                        (float)
                        $validated[
                            'amount'
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Correct User Message
            |--------------------------------------------------------------------------
            */

            $message =
                match (
                    $withdrawal
                        ->status
                ) {

                    SellerWithdrawal::STATUS_SUCCESSFUL =>

                        'Withdrawal completed successfully. ₦'
                        .
                        number_format(
                            (float)
                            $withdrawal
                                ->amount,
                            2
                        )
                        .
                        ' was sent through Paystack to '
                        .
                        $withdrawal
                            ->bank_name
                        .
                        ' ••••'
                        .
                        $withdrawal
                            ->account_number_last4
                        .
                        '.',


                    SellerWithdrawal::STATUS_OTP =>

                        'Automatic payout could not continue because Paystack transfer confirmation/OTP is enabled. Please contact Midpoint support.',


                    SellerWithdrawal::STATUS_FAILED,
                    SellerWithdrawal::STATUS_REVERSED =>

                        'The withdrawal could not be completed. The reserved amount has been returned to your available Midpoint balance.',


                    default =>

                        'Withdrawal submitted successfully. Paystack is processing the bank transfer automatically. No admin approval is required.',
                };


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