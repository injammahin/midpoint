<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Services\PaystackSellerKycService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SellerKycController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Start Paystack KYC
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        PaystackSellerKycService $kycService
    ) {

        $seller =
            $request->user();


        $validated =
            $request->validate([

                'first_name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                ],


                'middle_name' => [
                    'nullable',
                    'string',
                    'max:100',
                ],


                'last_name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                ],


                /*
                 * Kept for Midpoint's KYC record.
                 *
                 * Paystack's customer identification endpoint itself
                 * validates BVN + bank account + name.
                 */

                'date_of_birth' => [
                    'required',
                    'date',
                    'before:today',
                ],


                'bvn' => [
                    'required',
                    'regex:/^[0-9]{11}$/',
                ],

            ]);


        try {

            $kyc =
                $kycService
                    ->startVerification(
                        $seller,
                        $validated
                    );


        } catch (
            ValidationException $exception
        ) {

            throw $exception;


        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return redirect()
                ->route(
                    'seller.wallet'
                )
                ->withInput(
                    $request
                        ->except(
                            'bvn'
                        )
                )
                ->with(
                    'error',
                    'Identity verification could not be started. '
                    .
                    $exception
                        ->getMessage()
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Already Approved
        |--------------------------------------------------------------------------
        */

        if (
            $kyc->status
            ===
            SellerKycVerification::STATUS_APPROVED
        ) {

            return redirect()
                ->route(
                    'seller.wallet'
                )
                ->with(
                    'success',
                    'Your identity and active withdrawal bank account are already verified.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Async Processing
        |--------------------------------------------------------------------------
        */

        if (
            $kyc->status
            ===
            SellerKycVerification::STATUS_PROCESSING
        ) {

            return redirect()
                ->route(
                    'seller.wallet'
                )
                ->with(
                    'success',
                    'Identity verification has been submitted to Paystack. It will complete automatically when Paystack sends the verification result.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Rejected
        |--------------------------------------------------------------------------
        */

        if (
            $kyc->status
            ===
            SellerKycVerification::STATUS_REJECTED
        ) {

            return redirect()
                ->route(
                    'seller.wallet'
                )
                ->with(
                    'error',
                    $kyc
                        ->failure_message
                    ?:
                    'Paystack could not verify your BVN and bank-account details.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Provider Error
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'seller.wallet'
            )
            ->with(
                'error',
                $kyc
                    ->failure_message
                ?:
                'Paystack identity verification is temporarily unavailable. Please try again.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX KYC Status
    |--------------------------------------------------------------------------
    |
    | The wallet UI polls this while waiting for Paystack's webhook.
    |
    */

    public function status(
        Request $request,
        PaystackSellerKycService $kycService
    ) {

        $kyc =
            SellerKycVerification::query()
                ->where(
                    'seller_id',
                    $request
                        ->user()
                        ->id
                )
                ->first();


        if (!$kyc) {

            return response()->json([

                'status' =>
                    SellerKycVerification::STATUS_PENDING,


                'status_label' =>
                    'Not verified',


                'completed' =>
                    false,


                'approved' =>
                    false,


                'message' =>
                    null,

            ]);
        }


        /*
         * Prevent a missed Paystack webhook from leaving the seller in an
         * endless processing state. A late signed webhook remains valid and
         * can still approve the record after it has been released for retry.
         */
        if (
            $kyc->status
            ===
            SellerKycVerification::STATUS_PROCESSING
        ) {

            $kyc =
                $kycService
                    ->releaseIfStale(
                        $kyc
                    );
        }


        $activeAccount =
            SellerWithdrawalAccount::query()
                ->where(
                    'seller_id',
                    $request->user()->id
                )
                ->where(
                    'is_verified',
                    true
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        $approvedForActiveBank =
            $kyc
                ->isApprovedForWithdrawalAccount(
                    $activeAccount
                );


        $effectiveStatus =
            $kyc->status;


        $effectiveStatusLabel =
            $kyc->status_label;


        $message =
            $kyc->failure_message;


        if (
            $kyc->status
                ===
                SellerKycVerification::STATUS_APPROVED
            && !$approvedForActiveBank
        ) {

            $effectiveStatus =
                SellerKycVerification::STATUS_PENDING;


            $effectiveStatusLabel =
                'Not verified';


            $message =
                'Verify your identity for the current active withdrawal bank account.';
        }


        $completed =
            in_array(
                $effectiveStatus,
                [

                    SellerKycVerification::STATUS_APPROVED,

                    SellerKycVerification::STATUS_REJECTED,

                    SellerKycVerification::STATUS_PROVIDER_ERROR,

                ],
                true
            );


        return response()->json([

            'status' =>
                $effectiveStatus,


            'status_label' =>
                $effectiveStatusLabel,


            'completed' =>
                $completed,


            'approved' =>
                $approvedForActiveBank,


            'message' =>
                $message,

        ]);
    }
}
