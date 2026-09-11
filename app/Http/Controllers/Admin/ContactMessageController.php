<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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


    /*
    |--------------------------------------------------------------------------
    | Delete One Contact Message
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ContactMessage $contactMessage
    ) {
        DB::transaction(
            function () use ($contactMessage) {
                $this->deleteRelatedNotifications([
                    (int) $contactMessage->id,
                ]);

                $contactMessage->delete();
            }
        );


        return redirect()
            ->route(
                'admin.support-inquiries.contacts'
            )
            ->with(
                'success',
                'Contact message deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Selected Contact Messages
    |--------------------------------------------------------------------------
    */

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate(
            [
                'message_ids' => [
                    'required',
                    'array',
                    'min:1',
                    'max:100',
                ],

                'message_ids.*' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:contact_messages,id',
                ],
            ],
            [
                'message_ids.required' =>
                    'Select at least one contact message to delete.',

                'message_ids.min' =>
                    'Select at least one contact message to delete.',
            ]
        );


        $messageIds = collect(
            $validated['message_ids']
        )
            ->map(
                fn ($messageId) =>
                    (int) $messageId
            )
            ->unique()
            ->values()
            ->all();


        $deletedCount = DB::transaction(
            function () use ($messageIds) {
                $this->deleteRelatedNotifications(
                    $messageIds
                );

                return ContactMessage::query()
                    ->whereKey($messageIds)
                    ->delete();
            }
        );


        return redirect()
            ->route(
                'admin.support-inquiries.contacts'
            )
            ->with(
                'success',
                $deletedCount === 1
                    ? '1 contact message deleted successfully.'
                    : $deletedCount . ' contact messages deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Notifications Pointing To Deleted Messages
    |--------------------------------------------------------------------------
    */

    private function deleteRelatedNotifications(
        array $messageIds
    ): void {
        DatabaseNotification::query()
            ->whereIn(
                'data->contact_message_id',
                $messageIds
            )
            ->delete();
    }
}
