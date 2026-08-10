<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SellerApplication;
use App\Models\SellerApplicationDocument;
use App\Models\SellerInvoice;

use App\Notifications\SellerApplicationUserNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Throwable;

class SellerApplicationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Applications
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $applications =
            SellerApplication::query()

                ->with([
                    'user',
                    'invoice',
                ])

                ->when(
                    $request->filled(
                        'status'
                    ),
                    function ($query) use ($request) {

                        $query->where(
                            'status',
                            $request
                                ->status
                        );
                    }
                )

                ->latest('id')

                ->paginate(20)

                ->withQueryString();


        return view(
            'admin.website-settings.seller-applications.index',
            compact(
                'applications'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Application
    |--------------------------------------------------------------------------
    */

    public function show(
        SellerApplication $sellerApplication
    ) {
        $sellerApplication->load([
            'user',
            'documents',
            'invoice',
            'reviewer',
        ]);


        return view(
            'admin.website-settings.seller-applications.show',
            [
                'application' =>
                    $sellerApplication,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Request Revision
    |--------------------------------------------------------------------------
    */

    public function requestRevision(
        Request $request,
        SellerApplication $sellerApplication
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'revision_note' => [
                    'required',
                    'string',
                    'min:5',
                    'max:5000',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Must Be Submitted
        |--------------------------------------------------------------------------
        */

        if (
            $sellerApplication
                ->status
            !==
            SellerApplication::STATUS_SUBMITTED
        ) {

            return back()->with(
                'error',
                'Only submitted applications can be sent for revision.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $sellerApplication->update([

            'status' =>
                SellerApplication::STATUS_REVISION_REQUIRED,

            'revision_note' =>
                $validated[
                    'revision_note'
                ],

            'reviewed_by' =>
                $request
                    ->user()
                    ->id,

            'reviewed_at' =>
                now(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Reload Fresh Data
        |--------------------------------------------------------------------------
        */

        $sellerApplication->refresh();


        $sellerApplication->load([
            'user',
            'invoice',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Applicant Check
        |--------------------------------------------------------------------------
        */

        if (
            !$sellerApplication
                ->user
        ) {

            Log::error(
                'Revision email failed because seller application user does not exist.',
                [
                    'application_id' =>
                        $sellerApplication->id,

                    'user_id' =>
                        $sellerApplication->user_id,
                ]
            );


            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Revision was saved, but the applicant account could not be found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Email Check
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $sellerApplication
                    ->user
                    ->email
            )
        ) {

            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Revision was saved, but the applicant does not have an email address.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Send Revision Email
        |--------------------------------------------------------------------------
        */

        $emailSent =
            $this
                ->sendApplicationEmail(
                    $sellerApplication,
                    'revision_required'
                );


        /*
        |--------------------------------------------------------------------------
        | Failed
        |--------------------------------------------------------------------------
        */

        if (!$emailSent) {

            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Revision was saved, but the revision email failed. Check storage/logs/laravel.log.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'admin.website-settings.seller-applications.show',
                $sellerApplication
            )

            ->with(
                'success',
                'Revision request saved and emailed successfully to '
                .
                $sellerApplication
                    ->user
                    ->email
                .
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        Request $request,
        SellerApplication $sellerApplication
    ) {
        /*
        |--------------------------------------------------------------------------
        | Status Check
        |--------------------------------------------------------------------------
        */

        if (
            $sellerApplication
                ->status
            !==
            SellerApplication::STATUS_SUBMITTED
        ) {

            return back()->with(
                'error',
                'This application cannot be approved in its current status.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Approve And Create Invoice
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $sellerApplication
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Application
                |--------------------------------------------------------------------------
                */

                $application =
                    SellerApplication::query()

                        ->whereKey(
                            $sellerApplication
                                ->id
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Recheck Status
                |--------------------------------------------------------------------------
                */

                if (
                    $application
                        ->status
                    !==
                    SellerApplication::STATUS_SUBMITTED
                ) {

                    abort(
                        409,
                        'Application status changed before approval.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Approve
                |--------------------------------------------------------------------------
                */

                $application->update([

                    'status' =>
                        SellerApplication::STATUS_PAYMENT_PENDING,

                    'revision_note' =>
                        null,

                    'reviewed_by' =>
                        $request
                            ->user()
                            ->id,

                    'reviewed_at' =>
                        now(),

                    'approved_at' =>
                        now(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Create Invoice
                |--------------------------------------------------------------------------
                */

                SellerInvoice::firstOrCreate(

                    [
                        'seller_application_id' =>
                            $application->id,
                    ],

                    [
                        'invoice_number' =>
                            SellerInvoice::generateInvoiceNumber(),

                        'user_id' =>
                            $application
                                ->user_id,

                        'amount' =>
                            $application
                                ->package_price,

                        'currency' =>
                            'NGN',

                        'status' =>
                            'unpaid',

                        'payment_method' =>
                            null,

                        'payment_reference' =>
                            null,

                        'issued_at' =>
                            now(),

                        'due_at' =>
                            now()
                                ->addDays(7),

                        'paid_at' =>
                            null,

                    ]
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $sellerApplication->refresh();


        $sellerApplication->load([
            'user',
            'invoice',
        ]);


        /*
        |--------------------------------------------------------------------------
        | User Check
        |--------------------------------------------------------------------------
        */

        if (
            !$sellerApplication
                ->user
        ) {

            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Application approved and invoice generated, but the applicant account could not be found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | User Email Check
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $sellerApplication
                    ->user
                    ->email
            )
        ) {

            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Application approved and invoice generated, but the applicant has no email address.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Approval Email
        |--------------------------------------------------------------------------
        */

        $emailSent =
            $this
                ->sendApplicationEmail(
                    $sellerApplication,
                    'approved'
                );


        /*
        |--------------------------------------------------------------------------
        | Failed
        |--------------------------------------------------------------------------
        */

        if (!$emailSent) {

            return redirect()

                ->route(
                    'admin.website-settings.seller-applications.show',
                    $sellerApplication
                )

                ->with(
                    'error',
                    'Application approved and invoice generated, but the approval email failed. Check storage/logs/laravel.log.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(
                'admin.website-settings.seller-applications.show',
                $sellerApplication
            )

            ->with(
                'success',
                'Application approved, invoice generated, and approval email sent to '
                .
                $sellerApplication
                    ->user
                    ->email
                .
                '.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Send Application Email
    |--------------------------------------------------------------------------
    */

    private function sendApplicationEmail(
        SellerApplication $application,
        string $type
    ): bool {

        try {

            /*
            |--------------------------------------------------------------------------
            | Always Reload Relations
            |--------------------------------------------------------------------------
            */

            $application->load([
                'user',
                'invoice',
            ]);


            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $user =
                $application
                    ->user;


            if (!$user) {

                Log::error(
                    'Seller application email failed: user missing.',
                    [
                        'application_id' =>
                            $application->id,

                        'user_id' =>
                            $application->user_id,

                        'type' =>
                            $type,
                    ]
                );


                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Email
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $user->email
                )
            ) {

                Log::error(
                    'Seller application email failed: email missing.',
                    [
                        'application_id' =>
                            $application->id,

                        'user_id' =>
                            $user->id,

                        'type' =>
                            $type,
                    ]
                );


                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Send Immediately
            |--------------------------------------------------------------------------
            |
            | SellerApplicationUserNotification does NOT implement ShouldQueue,
            | therefore Laravel sends the email immediately.
            |
            */

            $user->notify(

                new SellerApplicationUserNotification(
                    $application,
                    $type
                )

            );


            /*
            |--------------------------------------------------------------------------
            | Log Success
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Seller application email sent.',
                [
                    'application_id' =>
                        $application->id,

                    'user_id' =>
                        $user->id,

                    'email' =>
                        $user->email,

                    'type' =>
                        $type,
                ]
            );


            return true;

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Exact Error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Seller application email failed with exception.',
                [
                    'application_id' =>
                        $application->id,

                    'user_id' =>
                        $application
                            ->user_id,

                    'type' =>
                        $type,

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


            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download Document
    |--------------------------------------------------------------------------
    */

    public function document(
        SellerApplicationDocument $document
    ) {
        abort_unless(
            Storage::disk(
                $document
                    ->disk
            )->exists(
                $document
                    ->path
            ),
            404
        );


        return Storage::disk(
            $document
                ->disk
        )->download(
            $document
                ->path,
            $document
                ->original_name
        );
    }
}