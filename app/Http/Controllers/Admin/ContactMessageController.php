<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactMessageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Contact Inbox
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query =
            ContactMessage::query()
                ->latest();


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search =
                trim(
                    $request->search
                );


            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'message',
                        'like',
                        "%{$search}%"
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Read Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filter === 'unread') {

            $query->whereNull(
                'read_at'
            );

        }


        if ($request->filter === 'read') {

            $query->whereNotNull(
                'read_at'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status')
            &&
            in_array(
                $request->status,
                [
                    'new',
                    'in_progress',
                    'resolved',
                ]
            )
        ) {

            $query->where(
                'status',
                $request->status
            );

        }


        $messages =
            $query
                ->paginate(15)
                ->withQueryString();


        return view(
            'admin.support-inquiries.contacts',
            compact(
                'messages'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | View Message
    |--------------------------------------------------------------------------
    */

    public function show(
        ContactMessage $contactMessage
    ) {

        /*
        |--------------------------------------------------------------------------
        | Mark Contact As Read
        |--------------------------------------------------------------------------
        */

        if (
            is_null(
                $contactMessage->read_at
            )
        ) {

            $contactMessage->update([

                'read_at' =>
                    now(),

                'read_by' =>
                    Auth::id(),

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Current Admin's Related Notification Read
        |--------------------------------------------------------------------------
        */

        Auth::user()
            ->unreadNotifications()
            ->where(
                'data->contact_message_id',
                $contactMessage->id
            )
            ->update([
                'read_at' => now(),
            ]);


        return view(
            'admin.support-inquiries.contact-show',
            compact(
                'contactMessage'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        ContactMessage $contactMessage
    ) {

        $validated =
            $request->validate([

                'status' => [
                    'required',
                    'in:new,in_progress,resolved',
                ],

            ]);


        $contactMessage->update([

            'status' =>
                $validated['status'],

        ]);


        return back()->with(
            'success',
            'Contact message status updated successfully.'
        );
    }
}