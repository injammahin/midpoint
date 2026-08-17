<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerKycController extends Controller
{
    public function index(
        Request $request
    ) {

        $status =
            $request->get(
                'status'
            );


        $kycs =
            SellerKycVerification::query()

                ->with([
                    'seller',
                    'reviewer',
                ])

                ->when(
                    in_array(
                        $status,
                        [
                            SellerKycVerification::STATUS_PENDING,
                            SellerKycVerification::STATUS_APPROVED,
                            SellerKycVerification::STATUS_REJECTED,
                        ],
                        true
                    ),

                    fn ($query) =>
                        $query->where(
                            'status',
                            $status
                        )
                )

                ->latest(
                    'id'
                )

                ->paginate(
                    20
                )

                ->withQueryString();


        return view(
            'admin.kyc.index',
            compact(
                'kycs',
                'status'
            )
        );
    }


    public function show(
        SellerKycVerification $kyc
    ) {

        $kyc->load([
            'seller',
            'reviewer',
        ]);


        return view(
            'admin.kyc.show',
            compact(
                'kyc'
            )
        );
    }


    public function approve(
        Request $request,
        SellerKycVerification $kyc
    ) {

        if (
            $kyc->status
            !==
            SellerKycVerification::STATUS_PENDING
        ) {

            return back()
                ->with(
                    'error',
                    'Only pending KYC submissions can be approved.'
                );
        }


        $kyc->forceFill([
            'status' =>
                SellerKycVerification::STATUS_APPROVED,

            'rejection_reason' =>
                null,

            'reviewed_by' =>
                $request->user()->id,

            'reviewed_at' =>
                now(),

            'approved_at' =>
                now(),

            'rejected_at' =>
                null,
        ])->save();


        return back()
            ->with(
                'success',
                'Seller KYC verified successfully.'
            );
    }


    public function reject(
        Request $request,
        SellerKycVerification $kyc
    ) {

        if (
            $kyc->status
            !==
            SellerKycVerification::STATUS_PENDING
        ) {

            return back()
                ->with(
                    'error',
                    'Only pending KYC submissions can be rejected.'
                );
        }


        $validated =
            $request->validate([
                'rejection_reason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:2000',
                ],
            ]);


        $kyc->forceFill([
            'status' =>
                SellerKycVerification::STATUS_REJECTED,

            'rejection_reason' =>
                $validated[
                    'rejection_reason'
                ],

            'reviewed_by' =>
                $request->user()->id,

            'reviewed_at' =>
                now(),

            'approved_at' =>
                null,

            'rejected_at' =>
                now(),
        ])->save();


        return back()
            ->with(
                'success',
                'Seller KYC rejected. The seller can correct and resubmit it.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Download Private KYC Document
    |--------------------------------------------------------------------------
    */

    public function document(
        SellerKycVerification $kyc,
        string $type
    ) {

        $path =
            match (
                $type
            ) {

                'front' =>
                    $kyc
                        ->document_front_path,

                'back' =>
                    $kyc
                        ->document_back_path,

                'selfie' =>
                    $kyc
                        ->selfie_path,

                default =>
                    null,
            };


        abort_unless(
            $path
            &&
            Storage::disk(
                'local'
            )->exists(
                $path
            ),
            404
        );


        return Storage::disk(
            'local'
        )
            ->download(
                $path
            );
    }
}