<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerApplicationDocument;
use App\Models\SellerPackage;
use App\Models\User;

use App\Notifications\SellerApplicationAdminNotification;
use App\Notifications\SellerApplicationSubmittedNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SellerApplicationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Store Seller Application
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Logged-in User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        |
        | The route already uses:
        |
        | auth
        | active
        | verified
        |
        | but we also protect the controller directly.
        |
        */

        if (!$user) {

            return redirect()
                ->route(
                    'login',
                    [
                        'redirect' =>
                            route(
                                'verified-sellers'
                            )
                            .
                            '#verified-application',
                    ]
                );

        }


        if (
            !$user->hasVerifiedEmail()
        ) {

            return redirect()
                ->route(
                    'verification.notice'
                )
                ->with(
                    'error',
                    'Please verify your email address before submitting a seller application.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Application
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'seller_package_id' => [
                    'required',
                    'integer',
                    'exists:seller_packages,id',
                ],

                'business_name' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'category' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'location' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'cac_or_bvn' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'store_link' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'description' => [
                    'required',
                    'string',
                    'min:10',
                    'max:5000',
                ],

                /*
                |--------------------------------------------------------------------------
                | Verification Documents
                |--------------------------------------------------------------------------
                */

                'documents' => [
                    'required',
                    'array',
                    'min:1',
                    'max:5',
                ],

                'documents.*' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                    'max:10240',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Selected Package
        |--------------------------------------------------------------------------
        |
        | Package must still be active.
        |
        | Never trust only the package information coming from the browser.
        |
        */

        $package =
            SellerPackage::query()

                ->whereKey(
                    $validated[
                        'seller_package_id'
                    ]
                )

                ->where(
                    'is_active',
                    true
                )

                ->first();


        if (!$package) {

            throw ValidationException::withMessages([

                'seller_package_id' =>
                    'The selected seller package is no longer available. Please choose another package.',

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Application
        |--------------------------------------------------------------------------
        */

        $latestApplication =
            SellerApplication::query()

                ->where(
                    'user_id',
                    $user->id
                )

                ->latest('id')

                ->first();


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Workflow
        |--------------------------------------------------------------------------
        |
        | submitted
        |     Admin is reviewing it.
        |
        | payment_pending
        |     Admin approved it and user needs to pay.
        |
        | active
        |     Seller is already active.
        |
        */

        if (
            $latestApplication
            &&
            in_array(
                $latestApplication->status,
                [
                    SellerApplication::STATUS_SUBMITTED,
                    SellerApplication::STATUS_PAYMENT_PENDING,
                    SellerApplication::STATUS_ACTIVE,
                ],
                true
            )
        ) {

            $message =
                match(
                    $latestApplication->status
                ) {

                    SellerApplication::STATUS_SUBMITTED =>
                        'Your seller application is already under review.',

                    SellerApplication::STATUS_PAYMENT_PENDING =>
                        'Your seller application has already been approved. Please complete the pending invoice payment.',

                    SellerApplication::STATUS_ACTIVE =>
                        'Your seller account is already active.',

                    default =>
                        'You already have an active seller application.',

                };


            return redirect()
                ->route(
                    'verified-sellers'
                )
                ->with(
                    'error',
                    $message
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Save Application
        |--------------------------------------------------------------------------
        */

        try {

            $application =
                DB::transaction(
                    function () use (
                        $request,
                        $validated,
                        $package,
                        $user
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Lock User
                        |--------------------------------------------------------------------------
                        |
                        | Helps prevent two application submissions at almost
                        | exactly the same time.
                        |
                        */

                        User::query()

                            ->whereKey(
                                $user->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();


                        /*
                        |--------------------------------------------------------------------------
                        | Check Current Application Again Inside Transaction
                        |--------------------------------------------------------------------------
                        */

                        $currentApplication =
                            SellerApplication::query()

                                ->where(
                                    'user_id',
                                    $user->id
                                )

                                ->latest('id')

                                ->lockForUpdate()

                                ->first();


                        /*
                        |--------------------------------------------------------------------------
                        | Duplicate Protection
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $currentApplication
                            &&
                            in_array(
                                $currentApplication->status,
                                [
                                    SellerApplication::STATUS_SUBMITTED,
                                    SellerApplication::STATUS_PAYMENT_PENDING,
                                    SellerApplication::STATUS_ACTIVE,
                                ],
                                true
                            )
                        ) {

                            throw ValidationException::withMessages([

                                'application' =>
                                    'You already have an active seller application.',

                            ]);

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Previous Revision
                        |--------------------------------------------------------------------------
                        |
                        | The user was asked to apply again from the beginning.
                        |
                        | Their previous application remains in history but becomes
                        | superseded.
                        |
                        */

                        if (
                            $currentApplication
                            &&
                            $currentApplication->status
                            ===
                            SellerApplication::STATUS_REVISION_REQUIRED
                        ) {

                            $currentApplication->update([

                                'status' =>
                                    SellerApplication::STATUS_SUPERSEDED,

                            ]);

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Create Application
                        |--------------------------------------------------------------------------
                        */

                        $application =
                            SellerApplication::create([

                                /*
                                |--------------------------------------------------------------------------
                                | Reference
                                |--------------------------------------------------------------------------
                                */

                                'reference' =>
                                    SellerApplication::generateReference(),


                                /*
                                |--------------------------------------------------------------------------
                                | User
                                |--------------------------------------------------------------------------
                                */

                                'user_id' =>
                                    $user->id,


                                /*
                                |--------------------------------------------------------------------------
                                | Selected Package
                                |--------------------------------------------------------------------------
                                */

                                'seller_package_id' =>
                                    $package->id,


                                /*
                                |--------------------------------------------------------------------------
                                | Package Snapshot
                                |--------------------------------------------------------------------------
                                |
                                | This is important.
                                |
                                | If admin changes the package later, this application
                                | still keeps the package details selected at the time
                                | of application.
                                |
                                */

                                'package_name' =>
                                    $package->name,

                                'package_price' =>
                                    $package->price,

                                'billing_period' =>
                                    $package->billing_period,

                                'product_limit' =>
                                    $package->product_limit,


                                /*
                                |--------------------------------------------------------------------------
                                | Business Details
                                |--------------------------------------------------------------------------
                                */

                                'business_name' =>
                                    $validated[
                                        'business_name'
                                    ],

                                'category' =>
                                    $validated[
                                        'category'
                                    ],

                                'location' =>
                                    $validated[
                                        'location'
                                    ],

                                'phone' =>
                                    $validated[
                                        'phone'
                                    ],

                                'cac_or_bvn' =>
                                    $validated[
                                        'cac_or_bvn'
                                    ],

                                'store_link' =>
                                    $validated[
                                        'store_link'
                                    ]
                                    ?? null,

                                'description' =>
                                    $validated[
                                        'description'
                                    ],


                                /*
                                |--------------------------------------------------------------------------
                                | Workflow
                                |--------------------------------------------------------------------------
                                */

                                'status' =>
                                    SellerApplication::STATUS_SUBMITTED,

                                'revision_note' =>
                                    null,

                                'reviewed_by' =>
                                    null,

                                'reviewed_at' =>
                                    null,

                                'approved_at' =>
                                    null,

                                'activated_at' =>
                                    null,

                                'submitted_at' =>
                                    now(),

                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | Verification Documents
                        |--------------------------------------------------------------------------
                        |
                        | Stored on the private "local" disk.
                        |
                        | They are NOT publicly accessible by URL.
                        |
                        */

                        $documents =
                            $request->file(
                                'documents',
                                []
                            );


                        foreach (
                            $documents
                            as
                            $file
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | Extension
                            |--------------------------------------------------------------------------
                            */

                            $extension =
                                strtolower(
                                    $file
                                        ->getClientOriginalExtension()
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Safe Filename
                            |--------------------------------------------------------------------------
                            */

                            $filename =
                                (string)
                                Str::uuid();


                            if ($extension) {

                                $filename .=
                                    '.'
                                    .
                                    $extension;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Private Storage
                            |--------------------------------------------------------------------------
                            */

                            $path =
                                $file->storeAs(

                                    'seller-applications/'
                                    .
                                    $application->reference,

                                    $filename,

                                    'local'

                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Save Document Information
                            |--------------------------------------------------------------------------
                            */

                            SellerApplicationDocument::create([

                                'seller_application_id' =>
                                    $application->id,

                                'disk' =>
                                    'local',

                                'path' =>
                                    $path,

                                'original_name' =>
                                    $file
                                        ->getClientOriginalName(),

                                'mime_type' =>
                                    $file
                                        ->getMimeType(),

                                'size' =>
                                    $file
                                        ->getSize(),

                            ]);

                        }


                        return $application;

                    }
                );

        } catch (
            ValidationException $exception
        ) {

            throw $exception;

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Actual Error
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );


            return back()

                ->withInput(
                    $request->except([
                        'documents',
                    ])
                )

                ->with(
                    'error',
                    'We could not submit your seller application. Please try again.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Reload Relations
        |--------------------------------------------------------------------------
        */

        $application->load([

            'user',

            'documents',

            'package',

        ]);


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | USER CONFIRMATION EMAIL
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | There is no email field in the seller application form.
        |
        | Laravel sends this notification to:
        |
        | $user->email
        |
        | which is the same email address the customer verified when
        | registering their MidPoint account.
        |
        */

        $confirmationEmailSent =
            false;


        try {

            $user->notify(
                new SellerApplicationSubmittedNotification(
                    $application
                )
            );


            $confirmationEmailSent =
                true;

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Do Not Delete Application
            |--------------------------------------------------------------------------
            |
            | The application has already been successfully stored.
            |
            | SMTP failure must not destroy the user's application.
            |
            */

            report(
                $exception
            );

        }


        /*
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        | ADMIN DATABASE NOTIFICATION
        |--------------------------------------------------------------------------
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | Find Active Admins
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Notify
            |--------------------------------------------------------------------------
            */

            if (
                $admins->isNotEmpty()
            ) {

                Notification::send(

                    $admins,

                    new SellerApplicationAdminNotification(
                        $application
                    )

                );

            }

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Do Not Fail Application
            |--------------------------------------------------------------------------
            */

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
            $confirmationEmailSent
        ) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'success',
                    'Your seller application has been submitted successfully. A confirmation email has been sent to your verified email address.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | SMTP Failed But Application Was Stored
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'verified-sellers'
            )

            ->with(
                'success',
                'Your seller application has been submitted successfully and is now under review.'
            );
    }
}