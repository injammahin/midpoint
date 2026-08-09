<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class WebsiteSettingsController extends Controller
{
    public function appSettings()
    {
        return view(
            'admin.website-settings.app-settings'
        );
    }


    public function faqs()
    {
        return view(
            'admin.website-settings.faqs'
        );
    }


    public function pricing()
    {
        return view(
            'admin.website-settings.pricing'
        );
    }


    public function becomeSeller()
    {
        return view(
            'admin.website-settings.become-seller'
        );
    }
}