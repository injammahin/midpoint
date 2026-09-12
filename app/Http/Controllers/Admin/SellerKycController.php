<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerKycController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $kycs = SellerKycVerification::query()
            ->with([
                'seller',
                'reviewer',
                'withdrawalAccount',
            ])
            ->when(
                in_array(
                    $status,
                    [
                        SellerKycVerification::STATUS_PENDING,
                        SellerKycVerification::STATUS_PROCESSING,
                        SellerKycVerification::STATUS_APPROVED,
                        SellerKycVerification::STATUS_REJECTED,
                        SellerKycVerification::STATUS_PROVIDER_ERROR,
                    ],
                    true
                ),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.kyc.index',
            compact('kycs', 'status')
        );
    }


    public function show(SellerKycVerification $kyc)
    {
        $kyc->load([
            'seller',
            'reviewer',
            'withdrawalAccount',
        ]);

        return view(
            'admin.kyc.show',
            compact('kyc')
        );
    }


    public function approve(
        Request $request,
        SellerKycVerification $kyc
    ) {
        if (
            $kyc->provider === 'paystack'
            || $kyc->verification_method === 'paystack_bvn_bank_account'
        ) {
            return back()->with(
                'error',
                'Paystack KYC cannot be approved manually. A matching signed Paystack verification webhook is required.'
            );
        }

        if ($kyc->status !== SellerKycVerification::STATUS_PENDING) {
            return back()->with(
                'error',
                'Only pending non-Paystack KYC submissions can be approved.'
            );
        }

        if (!$kyc->withdrawalAccount || !$kyc->withdrawalAccount->is_verified) {
            return back()->with(
                'error',
                'The KYC record is not connected to a verified withdrawal account.'
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

        return back()->with(
            'success',
            'Seller KYC verified successfully.'
        );
    }


    public function reject(
        Request $request,
        SellerKycVerification $kyc
    ) {
        if (
            !in_array(
                $kyc->status,
                [
                    SellerKycVerification::STATUS_PENDING,
                    SellerKycVerification::STATUS_PROCESSING,
                    SellerKycVerification::STATUS_PROVIDER_ERROR,
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'This KYC record cannot be rejected in its current state.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        $response = is_array($kyc->provider_response)
            ? $kyc->provider_response
            : [];

        $response['exact_bvn_confirmed'] = false;
        $response['admin_rejected_at'] = now()->toIso8601String();

        $kyc->forceFill([
            'status' =>
                SellerKycVerification::STATUS_REJECTED,

            'provider_status' =>
                'admin_rejected',

            'paystack_identification_status' =>
                $kyc->provider === 'paystack'
                    ? 'admin_rejected'
                    : $kyc->paystack_identification_status,

            'name_match' =>
                null,

            'bank_name_match' =>
                null,

            'failure_code' =>
                'admin_rejected',

            'failure_message' =>
                $validated['rejection_reason'],

            'rejection_reason' =>
                $validated['rejection_reason'],

            'reviewed_by' =>
                $request->user()->id,

            'reviewed_at' =>
                now(),

            'approved_at' =>
                null,

            'auto_verified_at' =>
                null,

            'rejected_at' =>
                now(),

            'provider_response' =>
                $response,
        ])->save();

        return back()->with(
            'success',
            'Seller KYC rejected. The seller may submit a new verification.'
        );
    }


    public function document(
        SellerKycVerification $kyc,
        string $type
    ) {
        $path = match ($type) {
            'front' => $kyc->document_front_path,
            'back' => $kyc->document_back_path,
            'selfie' => $kyc->selfie_path,
            default => null,
        };

        abort_unless(
            $path
            && Storage::disk('local')->exists($path),
            404
        );

        return Storage::disk('local')->download($path);
    }
}
