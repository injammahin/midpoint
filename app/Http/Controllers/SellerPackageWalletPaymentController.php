<?php

namespace App\Http\Controllers;

use App\Models\SellerInvoice;
use App\Services\SellerPackageWalletPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SellerPackageWalletPaymentController extends Controller
{
    public function store(
        Request $request,
        SellerInvoice $invoice,
        SellerPackageWalletPaymentService $walletPayments
    ) {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Invoice Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $invoice->user_id === (int) $user->id,
            403,
            'This seller invoice does not belong to your account.'
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | Process Wallet Payment
            |--------------------------------------------------------------------------
            */

            $result =
                $walletPayments->pay(
                    $user,
                    $invoice
                );


            /*
            |--------------------------------------------------------------------------
            | Already Paid
            |--------------------------------------------------------------------------
            |
            | This can happen if the seller refreshes/resubmits after the first
            | successful payment.
            |
            */

            if (
                !$result['newly_paid']
            ) {

                return redirect()
                    ->route(
                        'verified-sellers'
                    )
                    ->with(
                        'success',
                        'This seller package invoice was already paid from your Midpoint Wallet. Your seller package is active.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Main Success Message
            |--------------------------------------------------------------------------
            */

            $message =
                'Payment successful. Your Midpoint Wallet was charged and your seller package is active.';


            /*
            |--------------------------------------------------------------------------
            | Email Status
            |--------------------------------------------------------------------------
            |
            | Email failure must NOT be shown as payment failure.
            |
            */

            if (
                ($result['email_sent'] ?? null)
                ===
                true
            ) {

                $message .=
                    ' A confirmation email with your paid PDF invoice has been sent.';

            } elseif (
                array_key_exists(
                    'email_sent',
                    $result
                )
            ) {

                $message .=
                    ' The confirmation email could not be delivered, but your payment and package activation were successful.';
            }


            return redirect()
                ->route(
                    'verified-sellers'
                )
                ->with(
                    'success',
                    $message
                );


        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Re-check Invoice
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | The financial transaction might already have committed before a
            | secondary email/notification exception happened.
            |
            */

            $freshInvoice =
                SellerInvoice::query()
                    ->find(
                        $invoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Payment Was Actually Successful
            |--------------------------------------------------------------------------
            |
            | Never display "Payment failed" when:
            |
            | status = paid
            | payment_method = midpoint_wallet
            |
            */

            if (
                $freshInvoice
                &&
                $freshInvoice->status === 'paid'
                &&
                $freshInvoice->payment_method === 'midpoint_wallet'
            ) {

                Log::warning(
                    'Midpoint Wallet payment succeeded but a post-payment step failed.',
                    [
                        'seller_invoice_id' =>
                            $invoice->id,

                        'user_id' =>
                            $user->id,

                        'error' =>
                            $exception->getMessage(),
                    ]
                );


                report(
                    $exception
                );


                return redirect()
                    ->route(
                        'verified-sellers'
                    )
                    ->with(
                        'success',
                        'Payment successful. Your Midpoint Wallet was charged and your seller package is active. A post-payment email or notification could not be completed, but you must not pay this invoice again.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Real Financial Failure
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Midpoint Wallet seller package payment failed.',
                [
                    'seller_invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $user->id,

                    'error' =>
                        $exception->getMessage(),
                ]
            );


            report(
                $exception
            );


            return redirect()
                ->to(
                    route(
                        'verified-sellers'
                    )
                    .
                    '#seller-invoice'
                )
                ->with(
                    'error',
                    $exception->getMessage()
                )
                ->with(
                    'open_package_payment_modal',
                    true
                );
        }
    }
}