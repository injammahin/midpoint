<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Contact Page
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'frontend.pages.contact'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Contact Message
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Simple honeypot spam protection
        |--------------------------------------------------------------------------
        */

        if ($request->filled('website')) {

            return redirect()
                ->route('contact.thank-you')
                ->with(
                    'contact_reference',
                    'Submitted'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [

                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:190',
                ],

                'topic' => [
                    'required',
                    'in:transaction_help,delivery_dispatch,business_verification,partnership,other',
                ],

                'message' => [
                    'required',
                    'string',
                    'min:5',
                    'max:5000',
                ],

            ],
            [

                'name.required' =>
                    'Please enter your full name.',

                'email.required' =>
                    'Please enter your email address.',

                'email.email' =>
                    'Please enter a valid email address.',

                'topic.required' =>
                    'Please select a topic.',

                'message.required' =>
                    'Please enter your message.',

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Save Message
        |--------------------------------------------------------------------------
        */

        $contactMessage =
            ContactMessage::create([

                'name' =>
                    $validated['name'],

                'email' =>
                    $validated['email'],

                'topic' =>
                    $validated['topic'],

                'message' =>
                    $validated['message'],

                'status' =>
                    'new',

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),

            ]);


        /*
        |--------------------------------------------------------------------------
        | Notify All Active Administrators
        |--------------------------------------------------------------------------
        */

        $admins = User::query()
            ->where(
                'role',
                'admin'
            )
            ->where(
                'status',
                true
            )
            ->get();


        if ($admins->isNotEmpty()) {

            Notification::send(
                $admins,
                new ContactMessageSubmitted(
                    $contactMessage
                )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Thank You Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'contact.thank-you'
            )
            ->with(
                'contact_reference',
                $contactMessage->reference
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Thank You
    |--------------------------------------------------------------------------
    */

    public function thankYou(Request $request)
    {
        if (
            !$request
                ->session()
                ->has('contact_reference')
        ) {

            return redirect()
                ->route('contact');
        }


        return view(
            'frontend.pages.contact-thank-you',
            [
                'reference' =>
                    session(
                        'contact_reference'
                    ),
            ]
        );
    }
}