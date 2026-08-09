<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SupportInquiryController extends Controller
{
    public function contacts()
    {
        return view(
            'admin.support-inquiries.contacts'
        );
    }


    public function supportMessages()
    {
        return view(
            'admin.support-inquiries.support-messages'
        );
    }
}