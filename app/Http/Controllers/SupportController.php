<?php

namespace App\Http\Controllers;

use App\Services\Support\SupportAvailabilityService;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(
        Request $request,
        SupportAvailabilityService $availability
    ) {

        $liveSupport =
            $availability->status();


        return view(
            'frontend.pages.support',
            compact(
                'liveSupport'
            )
        );
    }
}