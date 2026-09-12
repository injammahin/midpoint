<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Services\PaystackSellerKycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class SellerKycController extends Controller
{
    public function store(Request $request, PaystackSellerKycService $kycService)
    {
        $limits = [
            'midpoint:kyc:user:' . $request->user()->id => max(1, (int) config('midpoint.kyc.attempts_per_seller_hour', 10)),
            'midpoint:kyc:ip:' . hash('sha256', (string) $request->ip()) => max(1, (int) config('midpoint.kyc.attempts_per_ip_hour', 50)),
        ];
        foreach ($limits as $key => $limit) {
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                throw ValidationException::withMessages([
                    'bvn' => 'Too many verification attempts. Please wait before trying again.',
                ]);
            }
        }
        foreach ($limits as $key => $limit) {
            RateLimiter::hit($key, 3600);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'bvn' => ['required', 'string', 'regex:/\A[0-9]{11}\z/'],
        ]);

        try {
            $kyc = $kycService->startVerification($request->user(), $validated);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            // Do not leak provider errors, SQL bindings, or identity values.
            return redirect()->route('seller.wallet')
                ->withInput($request->only(['first_name', 'middle_name', 'last_name', 'date_of_birth']))
                ->with('error', 'Identity verification is unavailable or its evidence needs review. Contact support. Your KYC has not been newly approved.');
        }

        if ($kyc->status === SellerKycVerification::STATUS_APPROVED) {
            $bank = $this->activeBank($request);
            if (!$kyc->isApprovedForWithdrawalAccount($bank)) {
                return redirect()->route('seller.wallet')->with('error', 'Verification is not valid for the current active bank. Contact support.');
            }

            return redirect()->route('seller.wallet')->with('success', 'Your identity and active withdrawal bank account are verified.');
        }
        if ($kyc->status === SellerKycVerification::STATUS_PROCESSING) {
            return redirect()->route('seller.wallet')->with('success', 'Verification was submitted to Paystack. Approval will follow only after a matching successful verification result.');
        }

        return redirect()->route('seller.wallet')->with('error',
            $kyc->failure_message ?: 'Verification could not be completed. Check your details or contact support.');
    }

    public function status(Request $request, PaystackSellerKycService $kycService)
    {
        $kyc = SellerKycVerification::query()->where('seller_id', $request->user()->id)->first();
        if (!$kyc) {
            return response()->json([
                'status' => SellerKycVerification::STATUS_PENDING,
                'status_label' => 'Not verified',
                'completed' => false, 'approved' => false, 'message' => null,
            ]);
        }
        if ($kyc->status === SellerKycVerification::STATUS_PROCESSING) {
            $kyc = $kycService->releaseIfStale($kyc);
        }
        $approved = $kyc->isApprovedForWithdrawalAccount($this->activeBank($request));
        $status = $kyc->status;
        $label = $kyc->status_label;
        $message = $kyc->failure_message;
        if ($status === SellerKycVerification::STATUS_APPROVED && !$approved) {
            $status = SellerKycVerification::STATUS_PENDING;
            $label = 'Not verified';
            $message = 'Verify your identity for the current active withdrawal bank account.';
        }

        return response()->json([
            'status' => $status, 'status_label' => $label,
            'completed' => in_array($status, [
                SellerKycVerification::STATUS_APPROVED,
                SellerKycVerification::STATUS_REJECTED,
                SellerKycVerification::STATUS_PROVIDER_ERROR,
            ], true),
            'approved' => $approved, 'message' => $message,
        ]);
    }

    private function activeBank(Request $request): ?SellerWithdrawalAccount
    {
        return SellerWithdrawalAccount::query()->where('seller_id', $request->user()->id)
            ->where('is_verified', true)->where('is_active', true)->first();
    }
}
