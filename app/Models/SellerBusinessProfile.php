<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class SellerBusinessProfile extends Model
{
    protected $fillable = [

        'user_id',

        'profile_image_path',

        'tagline',

        'about',

        'location',

        'phone',

        'business_hours',

        'whatsapp_number',

        'whatsapp_enabled',

        'whatsapp_message',

        'website_url',

        'instagram_url',

        'facebook_url',

        'show_phone',

        'show_email',

    ];


    protected $casts = [

        'whatsapp_enabled' =>
            'boolean',

        'show_phone' =>
            'boolean',

        'show_email' =>
            'boolean',

    ];


    protected $appends = [

        'profile_image_url',

    ];


    /*
    |--------------------------------------------------------------------------
    | Seller
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Image URL
    |--------------------------------------------------------------------------
    */

    public function getProfileImageUrlAttribute(): ?string
    {
        if (
            !$this->profile_image_path
        ) {
            return null;
        }


        return Storage::disk(
            'public'
        )->url(
            $this->profile_image_path
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WhatsApp URL
    |--------------------------------------------------------------------------
    */

    public function whatsappUrl(
        ?string $customMessage = null
    ): ?string {

        if (
            !$this->whatsapp_enabled
            ||
            !$this->whatsapp_number
        ) {
            return null;
        }


        $number =
            preg_replace(
                '/\D+/',
                '',
                $this->whatsapp_number
            );


        if (!$number) {
            return null;
        }


        $businessName =
            optional(
                $this->user
                    ?->activeSellerSubscription
                    ?->application
            )->business_name
            ?:
            $this->user?->name
            ?:
            'Seller';


        $message =
            $customMessage
            ?:
            $this->whatsapp_message
            ?:
            'Hi '
            .
            $businessName
            .
            ', I found your verified business on MidPoint and would like to make an enquiry.';


        return
            'https://wa.me/'
            .
            $number
            .
            '?text='
            .
            rawurlencode(
                $message
            );
    }
}