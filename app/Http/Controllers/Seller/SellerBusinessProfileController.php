<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

use App\Models\SellerBusinessProfile;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;


class SellerBusinessProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Business Profile Page
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Load Active Seller Package
        |--------------------------------------------------------------------------
        */

        $user->load([
            'activeSellerSubscription.application',
            'activeSellerSubscription.package',
            'sellerBusinessProfile',
        ]);


        $subscription =
            $user->activeSellerSubscription;


        /*
        |--------------------------------------------------------------------------
        | Business Profile Is For Active Verified Sellers
        |--------------------------------------------------------------------------
        */

        if (!$subscription) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'You need an active seller package to manage your business profile.'
                );
        }


        $application =
            $subscription->application;


        if (!$application) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'Your verified seller application could not be found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create Initial Profile From Approved Application
        |--------------------------------------------------------------------------
        */

        $profile =
            SellerBusinessProfile::query()

                ->firstOrCreate(

                    [
                        'user_id' =>
                            $user->id,
                    ],

                    [
                        'about' =>
                            $application
                                ->description,

                        'location' =>
                            $application
                                ->location,

                        'phone' =>
                            $application
                                ->phone,

                        'whatsapp_enabled' =>
                            false,

                        'show_phone' =>
                            true,

                        'show_email' =>
                            false,
                    ]

                );


        /*
        |--------------------------------------------------------------------------
        | Reload Relationships
        |--------------------------------------------------------------------------
        */

        $profile->load(
            'user'
        );


        $businessName =
            $application
                ->business_name
            ?:
            $user->name;


        $category =
            $application
                ->category;


        return view(
            'seller.business-profile.index',
            compact(
                'user',
                'subscription',
                'application',
                'profile',
                'businessName',
                'category'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Business Profile
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request
    ) {
        $user =
            $request->user();


        /*
        |--------------------------------------------------------------------------
        | Require Active Seller Package
        |--------------------------------------------------------------------------
        */

        $subscription =
            $user
                ->activeSellerSubscription()
                ->with(
                    'application'
                )
                ->first();


        if (!$subscription) {

            return redirect()

                ->route(
                    'verified-sellers'
                )

                ->with(
                    'error',
                    'Your seller package is not active.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                'profile_image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:3072',
                ],


                /*
                |--------------------------------------------------------------------------
                | Business Information
                |--------------------------------------------------------------------------
                */

                'tagline' => [
                    'nullable',
                    'string',
                    'max:150',
                ],


                'about' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],


                'location' => [
                    'nullable',
                    'string',
                    'max:180',
                ],


                'phone' => [
                    'nullable',
                    'string',
                    'max:40',
                ],


                'business_hours' => [
                    'nullable',
                    'string',
                    'max:255',
                ],


                /*
                |--------------------------------------------------------------------------
                | WhatsApp
                |--------------------------------------------------------------------------
                */

                'whatsapp_number' => [

                    Rule::requiredIf(
                        $request->boolean(
                            'whatsapp_enabled'
                        )
                    ),

                    'nullable',
                    'string',
                    'max:40',
                ],


                'whatsapp_message' => [
                    'nullable',
                    'string',
                    'max:500',
                ],


                /*
                |--------------------------------------------------------------------------
                | Social / Website
                |--------------------------------------------------------------------------
                */

                'website_url' => [
                    'nullable',
                    'url',
                    'max:255',
                ],


                'instagram_url' => [
                    'nullable',
                    'url',
                    'max:255',
                ],


                'facebook_url' => [
                    'nullable',
                    'url',
                    'max:255',
                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize WhatsApp Number
        |--------------------------------------------------------------------------
        */

        $whatsappNumber =
            null;


        if (
            !empty(
                $validated[
                    'whatsapp_number'
                ]
            )
        ) {

            $whatsappNumber =
                preg_replace(
                    '/\D+/',
                    '',
                    $validated[
                        'whatsapp_number'
                    ]
                );


            /*
            |--------------------------------------------------------------------------
            | International phone numbers are max 15 digits
            |--------------------------------------------------------------------------
            */

            if (
                strlen(
                    $whatsappNumber
                )
                <
                8

                ||

                strlen(
                    $whatsappNumber
                )
                >
                15
            ) {

                throw ValidationException::withMessages([

                    'whatsapp_number' =>
                        'Enter the WhatsApp number with country code. Example: 2348035521194.',

                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Find / Create Business Profile
        |--------------------------------------------------------------------------
        */

        $profile =
            SellerBusinessProfile::query()

                ->firstOrCreate([

                    'user_id' =>
                        $user->id,

                ]);


        /*
        |--------------------------------------------------------------------------
        | Upload New Profile Picture
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'profile_image'
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | Remove Old Image
            |--------------------------------------------------------------------------
            */

            if (
                $profile
                    ->profile_image_path
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $profile
                        ->profile_image_path
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Store New Image
            |--------------------------------------------------------------------------
            */

            $profileImagePath =
                $request
                    ->file(
                        'profile_image'
                    )
                    ->store(
                        'seller-business-profiles/'
                        .
                        $user->id,
                        'public'
                    );


            $profile
                ->profile_image_path =
                $profileImagePath;
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $profile->fill([

            'tagline' =>
                !empty(
                    $validated['tagline']
                )
                    ? trim(
                        $validated['tagline']
                    )
                    : null,


            'about' =>
                !empty(
                    $validated['about']
                )
                    ? trim(
                        $validated['about']
                    )
                    : null,


            'location' =>
                !empty(
                    $validated['location']
                )
                    ? trim(
                        $validated['location']
                    )
                    : null,


            'phone' =>
                !empty(
                    $validated['phone']
                )
                    ? trim(
                        $validated['phone']
                    )
                    : null,


            'business_hours' =>
                !empty(
                    $validated[
                        'business_hours'
                    ]
                )
                    ? trim(
                        $validated[
                            'business_hours'
                        ]
                    )
                    : null,


            /*
            |--------------------------------------------------------------------------
            | WhatsApp
            |--------------------------------------------------------------------------
            */

            'whatsapp_number' =>
                $whatsappNumber,


            'whatsapp_enabled' =>
                $request->boolean(
                    'whatsapp_enabled'
                ),


            'whatsapp_message' =>
                !empty(
                    $validated[
                        'whatsapp_message'
                    ]
                )
                    ? trim(
                        $validated[
                            'whatsapp_message'
                        ]
                    )
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Links
            |--------------------------------------------------------------------------
            */

            'website_url' =>
                $validated[
                    'website_url'
                ]
                ??
                null,


            'instagram_url' =>
                $validated[
                    'instagram_url'
                ]
                ??
                null,


            'facebook_url' =>
                $validated[
                    'facebook_url'
                ]
                ??
                null,


            /*
            |--------------------------------------------------------------------------
            | Privacy
            |--------------------------------------------------------------------------
            */

            'show_phone' =>
                $request->boolean(
                    'show_phone'
                ),


            'show_email' =>
                $request->boolean(
                    'show_email'
                ),

        ]);


        $profile->save();


        return back()
            ->with(
                'success',
                'Business profile updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Profile Picture
    |--------------------------------------------------------------------------
    */

    public function destroyProfileImage(
        Request $request
    ) {
        $profile =
            SellerBusinessProfile::query()

                ->where(
                    'user_id',
                    $request
                        ->user()
                        ->id
                )

                ->first();


        if (
            !$profile
            ||
            !$profile
                ->profile_image_path
        ) {

            return back()
                ->with(
                    'success',
                    'There is no profile picture to remove.'
                );
        }


        Storage::disk(
            'public'
        )->delete(
            $profile
                ->profile_image_path
        );


        $profile->update([

            'profile_image_path' =>
                null,

        ]);


        return back()
            ->with(
                'success',
                'Business profile picture removed.'
            );
    }
}