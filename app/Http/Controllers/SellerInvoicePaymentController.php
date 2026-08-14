<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\SellerInvoicePayment;
use App\Services\PaystackService;
use App\Services\SellerInvoicePaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SellerInvoicePaymentController extends Controller
{
    /**
     * Start or safely resume a seller-package Paystack payment.
     *
     * Important behaviour:
     * - We NEVER blindly reuse a saved Paystack authorization URL.
     * - We first verify the saved reference with the CURRENT Paystack key.
     * - If it already succeeded, we finalize the local invoice/subscription.
     * - If the reference belongs to an old Paystack integration, we invalidate
     *   the stale local attempt and create a fresh checkout with the new key.
     */
    public function initialize(
        Request $request,
        SellerInvoice $invoice,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {
        $user = $request->user();

        abort_unless(
            (int) $invoice->user_id === (int) $user->id,
            403,
            'This seller invoice does not belong to your account.'
        );

        $invoice->loadMissing([
            'application',
            'user',
        ]);

        if ($invoice->status === 'paid') {
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'success',
                    'This seller invoice has already been paid and your seller package is active.'
                );
        }

        $application = $invoice->application;

        abort_unless(
            $application
                && (int) $application->user_id === (int) $user->id,
            403,
            'This seller application does not belong to your account.'
        );

        abort_unless(
            $application->status === SellerApplication::STATUS_PAYMENT_PENDING,
            409,
            'This seller application is not waiting for payment.'
        );

        /*
        |--------------------------------------------------------------------------
        | Reconcile any previous unfinished local attempt FIRST
        |--------------------------------------------------------------------------
        |
        | This fixes the exact bug where an already-completed Paystack checkout URL
        | was being reopened. It also handles an API-key/account change safely.
        |
        */
        $activeAttempt = SellerInvoicePayment::query()
            ->where('seller_invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [
                SellerInvoicePayment::STATUS_INITIALIZED,
                SellerInvoicePayment::STATUS_PENDING,
            ])
            ->latest('id')
            ->first();

        if ($activeAttempt) {
            $reconciliation = $this->reconcileExistingAttempt(
                $activeAttempt,
                $paystack,
                $sellerPayments
            );

            if ($reconciliation !== null) {
                return $reconciliation;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Trusted invoice amount only
        |--------------------------------------------------------------------------
        */
        $amount = round((float) $invoice->amount, 2);
        $amountSubunit = (int) round($amount * 100);

        if ($amountSubunit <= 0) {
            return redirect()
                ->route('verified-sellers')
                ->with('error', 'The seller invoice amount is invalid.');
        }

        $currency = strtoupper($invoice->currency ?: 'NGN');
        $reference = $this->generatePaymentReference($invoice);

        $payment = SellerInvoicePayment::create([
            'seller_invoice_id' => $invoice->id,
            'seller_application_id' => $application->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'reference' => $reference,
            'amount' => $amount,
            'amount_subunit' => $amountSubunit,
            'currency' => $currency,
            'status' => SellerInvoicePayment::STATUS_CREATED,
        ]);

        try {
            $paystackData = $paystack->initializeTransaction([
                'email' => $user->email,
                'amount' => (string) $amountSubunit,
                'currency' => $currency,
                'reference' => $reference,
                'callback_url' => route('seller-invoices.paystack.callback'),
                'metadata' => json_encode([
                    'payment_type' => 'seller_package_invoice',
                    'seller_invoice_payment_id' => $payment->id,
                    'seller_invoice_id' => $invoice->id,
                    'seller_application_id' => $application->id,
                    'seller_user_id' => $user->id,
                    'invoice_number' => $invoice->invoice_number,
                    'application_reference' => $application->reference,
                    'business_name' => $application->business_name,
                    'package_name' => $application->package_name,
                ], JSON_UNESCAPED_SLASHES),
            ]);

            $authorizationUrl = $paystackData['authorization_url'] ?? null;
            $accessCode = $paystackData['access_code'] ?? null;
            $returnedReference = trim((string) ($paystackData['reference'] ?? ''));

            if (!$authorizationUrl || !$accessCode) {
                throw new RuntimeException(
                    'Paystack did not return a valid seller checkout URL.'
                );
            }

            if (
                $returnedReference !== ''
                && !hash_equals($reference, $returnedReference)
            ) {
                throw new RuntimeException(
                    'Paystack returned a different transaction reference than MidPoint generated.'
                );
            }

            $payment->update([
                'access_code' => $accessCode,
                'authorization_url' => $authorizationUrl,
                'status' => SellerInvoicePayment::STATUS_INITIALIZED,
                'initialized_at' => now(),
                'gateway_response' => 'Paystack checkout initialized.',
            ]);

            Log::info('Seller Paystack checkout initialized.', [
                'seller_invoice_id' => $invoice->id,
                'seller_invoice_payment_id' => $payment->id,
                'reference' => $reference,
                'user_id' => $user->id,
                'paystack_mode' => config('services.paystack.mode'),
                'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
            ]);

            return redirect()->away($authorizationUrl);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => SellerInvoicePayment::STATUS_FAILED,
                'authorization_url' => null,
                'access_code' => null,
                'gateway_response' => Str::limit($exception->getMessage(), 255, ''),
                'verified_at' => now(),
            ]);

            Log::error('Paystack seller invoice initialization failed.', [
                'seller_invoice_id' => $invoice->id,
                'seller_application_id' => $application->id,
                'seller_invoice_payment_id' => $payment->id,
                'user_id' => $user->id,
                'reference' => $reference,
                'paystack_mode' => config('services.paystack.mode'),
                'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'We could not start Paystack checkout. Please try again.'
                );
        }
    }

    /**
     * Paystack Redirect API callback for seller-package invoices.
     */
    public function callback(
        Request $request,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {
        $reference = trim((string) (
            $request->query('reference')
                ?: $request->query('trxref')
        ));

        if ($reference === '') {
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'Paystack did not return a seller payment reference.'
                );
        }

        $payment = SellerInvoicePayment::query()
            ->with([
                'invoice.application',
                'invoice.user',
            ])
            ->where('reference', $reference)
            ->first();

        if (!$payment) {
            Log::warning('Unknown seller Paystack callback reference.', [
                'reference' => $reference,
            ]);

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'The returned seller payment reference is not recognized by MidPoint.'
                );
        }

        $invoice = $payment->invoice;

        if (
            $payment->status === SellerInvoicePayment::STATUS_SUCCESS
            && $invoice
            && $invoice->status === 'paid'
        ) {
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'success',
                    'Payment already confirmed. Your verified seller package is active.'
                );
        }

        try {
            $verifiedData = $paystack->verifyTransaction($reference);

            Log::info('Seller Paystack callback verified with gateway.', [
                'reference' => $reference,
                'seller_invoice_payment_id' => $payment->id,
                'gateway_status' => $verifiedData['status'] ?? null,
                'paystack_mode' => config('services.paystack.mode'),
                'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
            ]);

            $result = $sellerPayments->processVerifiedPayment(
                $payment,
                $verifiedData
            );

            if ($result['successful']) {
                if (
                    $result['newly_paid']
                    && $result['email_sent'] === true
                ) {
                    return redirect()
                        ->route('verified-sellers')
                        ->with(
                            'success',
                            'Payment successful. Your seller package is active. A confirmation email with your PDF invoice has been sent to your verified email address.'
                        );
                }

                if (
                    $result['newly_paid']
                    && $result['email_sent'] === false
                ) {
                    return redirect()
                        ->route('verified-sellers')
                        ->with(
                            'success',
                            'Payment successful and your seller package is active. The confirmation email could not be delivered, so please contact support if you need the invoice resent.'
                        );
                }

                return redirect()
                    ->route('verified-sellers')
                    ->with(
                        'success',
                        'Payment already confirmed. Your verified seller package is active.'
                    );
            }

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'warning',
                    'Paystack has not confirmed this seller invoice payment as successful yet. Please do not start another payment while this one is still pending.'
                );
        } catch (Throwable $exception) {
            Log::error('Paystack seller invoice callback verification failed.', [
                'reference' => $reference,
                'seller_invoice_payment_id' => $payment->id,
                'paystack_mode' => config('services.paystack.mode'),
                'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            /*
            |------------------------------------------------------------------
            | Old Paystack integration / changed API key
            |------------------------------------------------------------------
            |
            | A reference created under another Paystack integration cannot be
            | verified with the current integration's secret key. Do not mark it
            | paid locally without a successful server-side verification.
            |
            */
            if ($this->isTransactionNotFoundError($exception)) {
                $this->invalidateStaleAttempt(
                    $payment,
                    'This Paystack reference does not exist on the currently configured integration.'
                );

                return redirect()
                    ->route('verified-sellers')
                    ->with(
                        'error',
                        config('services.paystack.mode') === 'test'
                            ? 'This test payment was created with a previous Paystack integration. The stale checkout has been cleared. Please start one fresh test payment with the current Paystack account.'
                            : 'This payment reference belongs to a different Paystack integration. Please contact support so the payment can be reconciled before attempting another payment.'
                    );
            }

            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'We could not verify this seller payment right now. If you were charged, do not pay again yet.'
                );
        }
    }

    /**
     * Download the seller package invoice after successful payment.
     */
    public function download(
        Request $request,
        SellerInvoice $invoice
    ) {
        abort_unless(
            (int) $invoice->user_id === (int) $request->user()->id,
            403
        );

        abort_unless($invoice->status === 'paid', 404);

        $invoice->load([
            'application.user',
        ]);

        return Pdf::loadView(
            'pdf.seller-package-invoice',
            [
                'user' => $invoice->user,
                'application' => $invoice->application,
                'invoice' => $invoice,
            ]
        )
            ->setPaper('a4', 'portrait')
            ->download($invoice->invoice_number . '.pdf');
    }

    /**
     * Verify and reconcile the last local unfinished checkout before deciding
     * whether the customer is allowed to create another Paystack transaction.
     *
     * Return null only when it is safe to create a brand-new transaction.
     */
    private function reconcileExistingAttempt(
        SellerInvoicePayment $payment,
        PaystackService $paystack,
        SellerInvoicePaymentService $sellerPayments
    ) {
        try {
            $verifiedData = $paystack->verifyTransaction($payment->reference);
            $gatewayStatus = strtolower(trim((string) (
                $verifiedData['status'] ?? ''
            )));

            $result = $sellerPayments->processVerifiedPayment(
                $payment,
                $verifiedData
            );

            if ($result['successful']) {
                return redirect()
                    ->route('verified-sellers')
                    ->with(
                        'success',
                        'Your previous Paystack payment was found and confirmed. Your seller package is now active.'
                    );
            }

            if (in_array($gatewayStatus, [
                'failed',
                'abandoned',
                'reversed',
            ], true)) {
                $this->invalidateStaleAttempt(
                    $payment,
                    'Previous Paystack attempt ended with status: ' . $gatewayStatus
                );

                // Safe to initialize a new transaction.
                return null;
            }

            /*
            | Paystack still considers the old transaction in progress.
            | Never create a duplicate payment while the gateway says it is
            | pending/ongoing/processing/queued.
            */
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'warning',
                    'Your previous Paystack payment is still being processed. Please wait for confirmation and do not pay again yet.'
                );
        } catch (Throwable $exception) {
            if ($this->isTransactionNotFoundError($exception)) {
                Log::warning('Stale seller Paystack checkout invalidated.', [
                    'seller_invoice_payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
                    'error' => $exception->getMessage(),
                ]);

                $this->invalidateStaleAttempt(
                    $payment,
                    'Transaction not found on current Paystack integration. The API key/account may have changed.'
                );

                // Safe to create a fresh transaction on the current integration.
                return null;
            }

            Log::error('Unable to reconcile previous seller Paystack attempt.', [
                'seller_invoice_payment_id' => $payment->id,
                'reference' => $payment->reference,
                'paystack_key_fingerprint' => $paystack->secretKeyFingerprint(),
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            /*
            | Unknown/network error: do NOT create a second charge because the
            | old transaction may actually have succeeded.
            */
            return redirect()
                ->route('verified-sellers')
                ->with(
                    'error',
                    'We could not confirm your previous Paystack payment attempt. Please do not pay again yet.'
                );
        }
    }

    private function invalidateStaleAttempt(
        SellerInvoicePayment $payment,
        string $reason
    ): void {
        $payment->update([
            'status' => SellerInvoicePayment::STATUS_FAILED,
            'authorization_url' => null,
            'access_code' => null,
            'gateway_response' => Str::limit($reason, 255, ''),
            'verified_at' => now(),
        ]);
    }

    /**
     * Paystack documents both "Transaction reference not found" and
     * "Transaction not found" for references that do not exist on the
     * integration authenticated by the current API key.
     */
    private function isTransactionNotFoundError(Throwable $exception): bool
    {
        $message = strtolower(trim($exception->getMessage()));

        return str_contains($message, 'transaction reference not found')
            || str_contains($message, 'transaction not found')
            || str_contains($message, 'reference is invalid');
    }

    private function generatePaymentReference(
        SellerInvoice $invoice
    ): string {
        do {
            $reference = 'MP-SINV-'
                . $invoice->id
                . '-'
                . now()->format('YmdHis')
                . '-'
                . Str::upper(Str::random(8));
        } while (
            SellerInvoicePayment::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
