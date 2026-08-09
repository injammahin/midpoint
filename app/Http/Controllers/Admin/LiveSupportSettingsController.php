<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportAgentProfile;
use App\Models\SupportChatBlackout;
use App\Models\SupportChatSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveSupportSettingsController extends Controller
{
    public function index()
    {
        return view(
            'admin.live-support.settings',
            [
                'settings' =>
                    SupportChatSetting::current(),

                'blackouts' =>
                    SupportChatBlackout::query()
                        ->latest('starts_at')
                        ->get(),

                'admins' =>
                    User::query()

                        ->where(
                            'role',
                            'admin'
                        )

                        ->with(
                            'supportAgentProfile'
                        )

                        ->orderBy(
                            'name'
                        )

                        ->get(),
            ]
        );
    }


    public function update(
        Request $request
    ) {

        $validated =
            $request->validate(
                [
                    'enabled' =>
                        [
                            'nullable',
                            'boolean',
                        ],

                    'timezone' =>
                        [
                            'required',
                            'timezone',
                        ],

                    'active_days' =>
                        [
                            'required',
                            'array',
                            'min:1',
                        ],

                    'active_days.*' =>
                        [
                            'integer',
                            'between:1,7',
                        ],

                    'opens_at' =>
                        [
                            'required',
                            'date_format:H:i',
                        ],

                    'closes_at' =>
                        [
                            'required',
                            'date_format:H:i',
                            'after:opens_at',
                        ],

                    'welcome_message' =>
                        [
                            'nullable',
                            'string',
                            'max:1000',
                        ],

                    'offline_message' =>
                        [
                            'nullable',
                            'string',
                            'max:1000',
                        ],

                    'queue_message' =>
                        [
                            'nullable',
                            'string',
                            'max:1000',
                        ],
                ]
            );


        $settings =
            SupportChatSetting::current();


        $settings->update(
            [
                'enabled' =>
                    $request->boolean(
                        'enabled'
                    ),

                'timezone' =>
                    $validated[
                        'timezone'
                    ],

                'active_days' =>
                    array_map(
                        'intval',
                        $validated[
                            'active_days'
                        ]
                    ),

                'opens_at' =>
                    $validated[
                        'opens_at'
                    ],

                'closes_at' =>
                    $validated[
                        'closes_at'
                    ],

                'welcome_message' =>
                    $validated[
                        'welcome_message'
                    ]
                    ?? null,

                'offline_message' =>
                    $validated[
                        'offline_message'
                    ]
                    ?? null,

                'queue_message' =>
                    $validated[
                        'queue_message'
                    ]
                    ?? null,

                'updated_by' =>
                    $request->user()->id,
            ]
        );


        return back()->with(
            'success',
            'Live Support settings updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Add Temporary Closure
    |--------------------------------------------------------------------------
    */

    public function blackoutStore(
        Request $request
    ) {

        $settings =
            SupportChatSetting::current();


        $validated =
            $request->validate(
                [
                    'starts_at' =>
                        [
                            'required',
                            'date',
                        ],

                    'ends_at' =>
                        [
                            'required',
                            'date',
                            'after:starts_at',
                        ],

                    'reason' =>
                        [
                            'nullable',
                            'string',
                            'max:500',
                        ],
                ]
            );


        /*
         * Admin enters time in configured support timezone.
         * Store UTC in database.
         */

        $starts =
            Carbon::parse(
                $validated[
                    'starts_at'
                ],
                $settings->timezone
            )
            ->utc();


        $ends =
            Carbon::parse(
                $validated[
                    'ends_at'
                ],
                $settings->timezone
            )
            ->utc();


        SupportChatBlackout::create(
            [
                'starts_at' =>
                    $starts,

                'ends_at' =>
                    $ends,

                'reason' =>
                    $validated[
                        'reason'
                    ]
                    ?? null,

                'is_active' =>
                    true,

                'created_by' =>
                    $request->user()->id,
            ]
        );


        return back()->with(
            'success',
            'Temporary offline period added.'
        );
    }


    public function blackoutDestroy(
        SupportChatBlackout $blackout
    ) {

        $blackout->delete();


        return back()->with(
            'success',
            'Offline period removed.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Support Agent Permissions
    |--------------------------------------------------------------------------
    */

    public function agentUpdate(
        Request $request,
        User $user
    ) {

        abort_unless(
            $user->role === 'admin',
            422
        );


        $validated =
            $request->validate(
                [
                    'is_enabled' =>
                        [
                            'nullable',
                            'boolean',
                        ],

                    'max_active_chats' =>
                        [
                            'required',
                            'integer',
                            'between:1,20',
                        ],
                ]
            );


        SupportAgentProfile::updateOrCreate(
            [
                'user_id' =>
                    $user->id,
            ],
            [
                'is_enabled' =>
                    $request->boolean(
                        'is_enabled'
                    ),

                'max_active_chats' =>
                    $validated[
                        'max_active_chats'
                    ],

                /*
                 * If removed as agent,
                 * make them unavailable too.
                 */
                'is_accepting_chats' =>
                    $request->boolean(
                        'is_enabled'
                    )
                    ?
                        (
                            $user
                                ->supportAgentProfile
                                ?->is_accepting_chats
                            ?? false
                        )
                    :
                        false,
            ]
        );


        return back()->with(
            'success',
            'Support agent settings updated.'
        );
    }
}