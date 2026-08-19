<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SellerInvoice;
use App\Models\SellerPackage;

use Illuminate\Http\Request;

class SellerInvoiceController extends Controller
{
    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query =
            SellerInvoice::query()

                ->with([
                    'user',
                    'application',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'search'
            )
        ) {

            $search =
                trim(
                    $request->search
                );


            $query->where(
                function ($query) use ($search) {

                    $query

                        ->where(
                            'invoice_number',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhere(
                            'payment_reference',
                            'like',
                            '%'
                            .
                            $search
                            .
                            '%'
                        )

                        ->orWhereHas(
                            'user',
                            function ($query) use ($search) {

                                $query

                                    ->where(
                                        'name',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    )

                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    );
                            }
                        )

                        ->orWhereHas(
                            'application',
                            function ($query) use ($search) {

                                $query

                                    ->where(
                                        'reference',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    )

                                    ->orWhere(
                                        'business_name',
                                        'like',
                                        '%'
                                        .
                                        $search
                                        .
                                        '%'
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'status'
            )
        ) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Package
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'package_id'
            )
        ) {

            $query->where(
                'seller_package_id',
                $request->package_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_from'
            )
        ) {

            $query->whereDate(
                'issued_at',
                '>=',
                $request->date_from
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'date_to'
            )
        ) {

            $query->whereDate(
                'issued_at',
                '<=',
                $request->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $invoices =
            $query

                ->latest(
                    'id'
                )

                ->paginate(25)

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */

        $packages =
            SellerPackage::query()

                ->orderBy(
                    'sort_order'
                )

                ->orderBy(
                    'name'
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                SellerInvoice::count(),

            'paid' =>
                SellerInvoice::where(
                    'status',
                    'paid'
                )->count(),

            'unpaid' =>
                SellerInvoice::where(
                    'status',
                    'unpaid'
                )->count(),

            'revenue' =>
                SellerInvoice::where(
                    'status',
                    'paid'
                )->sum(
                    'amount'
                ),

        ];


        return view(
            'admin.billing.invoices.index',
            compact(
                'invoices',
                'packages',
                'stats'
            )
        );
    }
}