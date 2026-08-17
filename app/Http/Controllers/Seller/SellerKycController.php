<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Services\AutomatedSellerKycService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SellerKycController extends Controller
{
    public function store(
        Request $request,
        AutomatedSellerKycService $automatedKyc
    ) {

        $seller =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Already Verified
        |--------------------------------------------------------------------------
        */

        $existing =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->first();


        if (
            $existing
            &&
            $existing->status
            ===
            SellerKycVerification::STATUS_APPROVED
        ) {

            return redirect()

                ->route(
                    'seller.wallet'
                )

                ->with(
                    'success',
                    'Your identity is already verified.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'legal_name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:180',
                ],

                'date_of_birth' => [
                    'required',
                    'date',
                    'before:today',
                ],

                'id_type' => [
                    'required',

                    Rule::in([
                        'nin',
                        'bvn',
                    ]),
                ],

                /*
                 * Nigerian NIN and BVN are both 11 digits.
                 */
                'id_number' => [
                    'required',
                    'regex:/^[0-9]{11}$/',
                ],

                'selfie' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png',
                    'max:5120',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Automatic Verification
        |--------------------------------------------------------------------------
        */

        try {

            $kyc =
                $automatedKyc
                    ->verify(
                        $seller,
                        $validated,
                        $request
                            ->file(
                                'selfie'
                            )
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

                ->with(
                    'error',
                    'Identity verification could not be completed. Please try again.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Approved
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
                    'Identity verified successfully. Your KYC was approved automatically and withdrawals are now available.'
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
                    $kyc->failure_message
                    ?:
                    'Identity verification failed. Please correct the information and try again.'
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
                $kyc->failure_message
                ?:
                'Identity verification is temporarily unavailable. Please try again.'
            );
    }
}