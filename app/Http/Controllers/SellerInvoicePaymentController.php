<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerInvoice;
use App\Models\User;

use App\Notifications\SellerApplicationUserNotification;
use App\Notifications\SellerPaymentReceivedAdminNotification;

use App\Services\SellerSubscriptionService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use Throwable;

class SellerInvoicePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Pay Seller Invoice
    |--------------------------------------------------------------------------
    */

    public function pay(
        Request $request,
        SellerInvoice $invoice,
        SellerSubscriptionService $subscriptions
    ) {
        /*
        |--------------------------------------------------------------------------
        | Demo Payment Enabled
        |--------------------------------------------------------------------------
        */

        abort_unless(
            config(
                'seller.demo_payment_enabled'
            ),
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Invoice Ownership
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int)
            $invoice->user_id
            ===
            (int)
            $request
                ->user()
                ->id,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Already Paid
        |--------------------------------------------------------------------------
        */

        if (
            $invoice->status
            ===
            'paid'
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'success',
                    'This invoice has already been paid.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Demo Card Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'cardholder_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'card_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'expiry' => [
                    'required',
                    'regex:/^\d{2}\/\d{2}$/',
                ],

                'cvv' => [
                    'required',
                    'digits_between:3,4',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Card
        |--------------------------------------------------------------------------
        */

        $cardNumber =
            preg_replace(
                '/\D/',
                '',
                $validated[
                    'card_number'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Demo Card
        |--------------------------------------------------------------------------
        */

        if (
            $cardNumber
            !==
            '4242424242424242'
        ) {

            throw ValidationException::withMessages([

                'card_number' =>
                    'Use demo card 4242 4242 4242 4242.',

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Database Transaction
        |--------------------------------------------------------------------------
        */

        $application =
            DB::transaction(
                function () use (
                    $invoice
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Invoice
                    |--------------------------------------------------------------------------
                    */

                    $lockedInvoice =
                        SellerInvoice::query()

                            ->whereKey(
                                $invoice
                                    ->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Already Paid By Concurrent Request
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedInvoice
                            ->status
                        ===
                        'paid'
                    ) {

                        return $lockedInvoice
                            ->application;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Application
                    |--------------------------------------------------------------------------
                    */

                    $application =
                        SellerApplication::query()

                            ->whereKey(
                                $lockedInvoice
                                    ->seller_application_id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                    /*
                    |--------------------------------------------------------------------------
                    | Must Be Waiting For Payment
                    |--------------------------------------------------------------------------
                    */

                    abort_unless(
                        $application
                            ->status
                        ===
                        SellerApplication::STATUS_PAYMENT_PENDING,
                        409,
                        'This application is not waiting for payment.'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Payment Reference
                    |--------------------------------------------------------------------------
                    */

                    $paymentReference =
                        'DEMO-'
                        .
                        Str::upper(
                            Str::random(12)
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Mark Invoice Paid
                    |--------------------------------------------------------------------------
                    |
                    | Never save:
                    |
                    | card number
                    | CVV
                    |
                    */

                    $lockedInvoice->update([

                        'status' =>
                            'paid',

                        'payment_method' =>
                            'demo_card',

                        'payment_reference' =>
                            $paymentReference,

                        'paid_at' =>
                            now(),

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Activate Application
                    |--------------------------------------------------------------------------
                    */

                    $application->update([

                        'status' =>
                            SellerApplication::STATUS_ACTIVE,

                        'activated_at' =>
                            now(),

                    ]);


                    return $application;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Reload Invoice
        |--------------------------------------------------------------------------
        */

        $invoice->refresh();


        /*
        |--------------------------------------------------------------------------
        | Activate Seller Subscription
        |--------------------------------------------------------------------------
        */

        $subscriptions
            ->activateFromApplication(
                $application,
                $invoice
                    ->payment_reference
            );


        /*
        |--------------------------------------------------------------------------
        | Reload Complete Application
        |--------------------------------------------------------------------------
        */

        $application->refresh();


        $application->load([
            'user',
            'invoice',
        ]);


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | USER PAYMENT CONFIRMATION EMAIL
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        $customerEmailSent =
            false;


        try {

            /*
            |--------------------------------------------------------------------------
            | User Check
            |--------------------------------------------------------------------------
            */

            if (
                !$application->user
            ) {

                throw new \RuntimeException(
                    'Seller application user not found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Email Check
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $application
                        ->user
                        ->email
                )
            ) {

                throw new \RuntimeException(
                    'Seller application user email is missing.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Send Payment Confirmation
            |--------------------------------------------------------------------------
            */

            $application
                ->user
                ->notify(

                    new SellerApplicationUserNotification(
                        $application,
                        'payment_successful'
                    )

                );


            $customerEmailSent =
                true;


            /*
            |--------------------------------------------------------------------------
            | Log Success
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Seller payment confirmation email sent.',
                [
                    'application_id' =>
                        $application->id,

                    'invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $application
                            ->user
                            ->id,

                    'email' =>
                        $application
                            ->user
                            ->email,

                    'payment_reference' =>
                        $invoice
                            ->payment_reference,
                ]
            );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Seller payment confirmation email failed.',
                [
                    'application_id' =>
                        $application->id,

                    'invoice_id' =>
                        $invoice->id,

                    'user_id' =>
                        $application
                            ->user_id,

                    'email' =>
                        $application->user
                            ? $application
                                ->user
                                ->email
                            : null,

                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception
                            ->getMessage(),

                    'file' =>
                        $exception
                            ->getFile(),

                    'line' =>
                        $exception
                            ->getLine(),
                ]
            );


            report(
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | ADMIN PAYMENT NOTIFICATION
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        try {

            $admins =
                User::query()

                    ->where(
                        'role',
                        'admin'
                    )

                    ->where(
                        'status',
                        true
                    )

                    ->get();


            if (
                $admins->isNotEmpty()
            ) {

                Notification::send(

                    $admins,

                    new SellerPaymentReceivedAdminNotification(
                        $application,
                        $invoice
                    )

                );
            }

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Admin seller payment notification failed.',
                [
                    'application_id' =>
                        $application->id,

                    'invoice_id' =>
                        $invoice->id,

                    'message' =>
                        $exception
                            ->getMessage(),
                ]
            );


            report(
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        if (
            $customerEmailSent
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'success',
                    'Payment successful. Your seller package is active and a payment confirmation email has been sent to your verified email address.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Worked But Mail Failed
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'verified-sellers'
            )

            ->with(
                'success',
                'Payment successful and your seller package is active. However, the confirmation email could not be delivered.'
            );
    }
}