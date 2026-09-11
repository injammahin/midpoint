<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
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
        }if (
            $kyc->status
            ===
            SellerKycVerification::STATUS_PROCESSING
        ) {
            $kyc =
                $kycService->releaseIfStale($kyc);
        }


        $completed =
            in_array(
                $kyc->status,
                [

                    SellerKycVerification::STATUS_APPROVED,

                    SellerKycVerification::STATUS_REJECTED,

                    SellerKycVerification::STATUS_PROVIDER_ERROR,

                ],
                true
            );


        return response()->json([

            'status' =>
                $kyc->status,


            'status_label' =>
                $kyc
                    ->status_label,


            'completed' =>
                $completed,


            'approved' =>
                $kyc->status
                ===
                SellerKycVerification::STATUS_APPROVED,


            'message' =>
                $kyc
                    ->failure_message,

        ]);
    }
}