<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /*
        |--------------------------------------------------------------------------
        | Main Live Support Settings
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'support_chat_settings',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->boolean('enabled')
                    ->default(true);

                $table
                    ->string('timezone', 100)
                    ->default('Africa/Lagos');

                /*
                 * ISO weekday:
                 *
                 * 1 = Monday
                 * 2 = Tuesday
                 * ...
                 * 7 = Sunday
                 */
                $table
                    ->json('active_days')
                    ->nullable();

                $table
                    ->time('opens_at')
                    ->default('08:00:00');

                $table
                    ->time('closes_at')
                    ->default('20:00:00');

                $table
                    ->string(
                        'welcome_message',
                        1000
                    )
                    ->nullable();

                $table
                    ->string(
                        'offline_message',
                        1000
                    )
                    ->nullable();

                $table
                    ->string(
                        'queue_message',
                        1000
                    )
                    ->nullable();

                $table
                    ->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Support Agents
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'support_agent_profiles',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table
                    ->boolean('is_enabled')
                    ->default(true);

                /*
                 * Agent manually says:
                 * "I am available to receive chats."
                 */
                $table
                    ->boolean('is_accepting_chats')
                    ->default(false);

                $table
                    ->unsignedInteger(
                        'max_active_chats'
                    )
                    ->default(3);

                $table
                    ->timestamp('last_seen_at')
                    ->nullable();

                $table->timestamps();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Temporary Offline Periods
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Dec 24 00:00 -> Dec 27 08:00
        |
        | Admin can temporarily close Live Support without changing
        | weekly opening hours.
        |
        */

        Schema::create(
            'support_chat_blackouts',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->timestamp('starts_at');

                $table
                    ->timestamp('ends_at');

                $table
                    ->string('reason')
                    ->nullable();

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index([
                    'starts_at',
                    'ends_at',
                    'is_active',
                ]);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Live Support Sessions
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'support_chat_sessions',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique();

                $table
                    ->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->foreignId('agent_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * waiting
                 * active
                 * resolved
                 * closed
                 * cancelled
                 */
                $table
                    ->string('status', 30)
                    ->default('waiting')
                    ->index();

                $table
                    ->string('topic')
                    ->nullable();

                $table
                    ->unsignedInteger(
                        'queue_position'
                    )
                    ->nullable();

                $table
                    ->timestamp('queue_entered_at')
                    ->nullable();

                $table
                    ->timestamp('assigned_at')
                    ->nullable();

                $table
                    ->timestamp('resolved_at')
                    ->nullable();

                $table
                    ->timestamp('closed_at')
                    ->nullable();

                $table
                    ->timestamp('last_message_at')
                    ->nullable()
                    ->index();

                $table
                    ->string(
                        'resolution_code',
                        100
                    )
                    ->nullable();

                $table
                    ->text('resolution_note')
                    ->nullable();

                /*
                 * Customer feedback
                 */
                $table
                    ->unsignedTinyInteger('rating')
                    ->nullable();

                $table
                    ->text('review')
                    ->nullable();

                $table
                    ->timestamp('rated_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'agent_id',
                    'status',
                ]);

                $table->index([
                    'user_id',
                    'status',
                ]);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Chat Messages
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'support_chat_messages',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId('support_chat_session_id')
                    ->constrained(
                        'support_chat_sessions'
                    )
                    ->cascadeOnDelete();

                /*
                 * NULL = system generated message
                 */
                $table
                    ->foreignId('sender_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * text
                 * attachment
                 * system
                 */
                $table
                    ->string('type', 30)
                    ->default('text');

                $table
                    ->text('body')
                    ->nullable();

                $table
                    ->timestamp('read_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'support_chat_session_id',
                    'created_at',
                ]);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Message Attachments
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'support_chat_attachments',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId(
                        'support_chat_message_id'
                    )
                    ->constrained(
                        'support_chat_messages'
                    )
                    ->cascadeOnDelete();

                /*
                 * image
                 * video
                 * file
                 */
                $table
                    ->string('kind', 30);

                $table
                    ->string('disk', 50)
                    ->default('local');

                $table
                    ->string('path');

                $table
                    ->string('original_name');

                $table
                    ->string('mime_type', 150)
                    ->nullable();

                $table
                    ->unsignedBigInteger('size')
                    ->default(0);

                $table->timestamps();

            }
        );
    }


    public function down()
    {
        Schema::dropIfExists(
            'support_chat_attachments'
        );

        Schema::dropIfExists(
            'support_chat_messages'
        );

        Schema::dropIfExists(
            'support_chat_sessions'
        );

        Schema::dropIfExists(
            'support_chat_blackouts'
        );

        Schema::dropIfExists(
            'support_agent_profiles'
        );

        Schema::dropIfExists(
            'support_chat_settings'
        );
    }
};