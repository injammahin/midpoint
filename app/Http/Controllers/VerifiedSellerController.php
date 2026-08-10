<?php

namespace App\Http\Controllers;

use App\Models\SellerApplication;
use App\Models\SellerPackage;
use Illuminate\Http\Request;

class VerifiedSellerController extends Controller
{
    public function index(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */

        $packages =
            SellerPackage::query()

                ->where(
                    'is_active',
                    true
                )

                ->orderBy(
                    'sort_order'
                )

                ->orderBy(
                    'id'
                )

                ->get();


        /*
        |--------------------------------------------------------------------------
        | Requested Package
        |--------------------------------------------------------------------------
        */

        $requestedPackage =
            $request->integer(
                'package'
            );


        $defaultPackage =
            $packages->firstWhere(
                'id',
                $requestedPackage
            )
            ??
            $packages->firstWhere(
                'is_popular',
                true
            )
            ??
            $packages->first();


        /*
        |--------------------------------------------------------------------------
        | Application State
        |--------------------------------------------------------------------------
        */

        $latestApplication =
            null;


        $pendingInvoice =
            null;


        if (
            auth()->check()
        ) {

            $latestApplication =
                SellerApplication::query()

                    ->with([
                        'invoice',
                    ])

                    ->where(
                        'user_id',
                        auth()->id()
                    )

                    ->latest('id')

                    ->first();


            if (
                $latestApplication
                &&
                $latestApplication->invoice
                &&
                $latestApplication
                    ->invoice
                    ->status
                ===
                'unpaid'
            ) {

                $pendingInvoice =
                    $latestApplication
                        ->invoice;

            }

        }


        return view(
            'frontend.pages.verified-sellers',
            compact(
                'packages',
                'defaultPackage',
                'latestApplication',
                'pendingInvoice'
            )
        );
    }
}