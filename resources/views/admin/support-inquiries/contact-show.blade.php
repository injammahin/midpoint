@extends('admin.layouts.app')


@section(
    'title',
    'Contact Message'
)


@section(
    'page-title',
    'Contact Message'
)


@section('content')

    {{-- Back --}}
    <div class="admin-contact-detail-back">

        <a
            href="{{ route('admin.support-inquiries.contacts') }}"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to contact messages

        </a>

    </div>


    @if (session('success'))

        <div class="admin-success-alert">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    <div class="admin-contact-detail-grid">

        {{-- =========================================================
            MESSAGE
        ========================================================== --}}
        <article
            class="admin-card
                   admin-contact-detail-card"
        >

            <div class="admin-contact-detail-header">

                <div class="admin-contact-detail-user">

                    <span class="admin-contact-avatar">

                        {{
                            strtoupper(
                                substr(
                                    $contactMessage->name,
                                    0,
                                    1
                                )
                            )
                        }}

                    </span>


                    <div>

                        <h2>
                            {{ $contactMessage->name }}
                        </h2>


                        <a
                            href="mailto:{{ $contactMessage->email }}"
                        >
                            {{ $contactMessage->email }}
                        </a>

                    </div>

                </div>


                <span
                    class="admin-contact-status
                           status-{{ $contactMessage->status }}"
                >

                    {{
                        match ($contactMessage->status) {
                            'new' => 'New',
                            'in_progress' => 'In Progress',
                            'resolved' => 'Resolved',
                            default => ucfirst($contactMessage->status),
                        }
                    }}

                </span>

            </div>


            <div class="admin-contact-message-meta">

                <div>

                    <span>
                        Reference
                    </span>

                    <strong>
                        {{ $contactMessage->reference }}
                    </strong>

                </div>


                <div>

                    <span>
                        Topic
                    </span>

                    <strong>
                        {{ $contactMessage->topic_label }}
                    </strong>

                </div>


                <div>

                    <span>
                        Received
                    </span>

                    <strong>
                        {{
                            $contactMessage
                                ->created_at
                                ->format('M d, Y · h:i A')
                        }}
                    </strong>

                </div>

            </div>


            <div class="admin-contact-message-body">

                {!! nl2br(
                    e(
                        $contactMessage->message
                    )
                ) !!}

            </div>


            <div class="admin-contact-message-actions">

                <a
                    href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($contactMessage->topic_label) }}"
                    class="admin-contact-reply-button"
                >

                    <i class="fa-solid fa-reply"></i>

                    Reply by email

                </a>

            </div>

        </article>


        {{-- =========================================================
            ACTION PANEL
        ========================================================== --}}
        <aside
            class="admin-card
                   admin-contact-action-card"
        >

            <h3>
                Message Status
            </h3>


            <p>
                Update the current progress of this inquiry.
            </p>


            <form
                method="POST"
                action="{{ route(
                    'admin.support-inquiries.contacts.status',
                    $contactMessage
                ) }}"
            >

                @csrf

                @method('PATCH')


                <label>
                    Status
                </label>


                <select name="status">

                    <option
                        value="new"
                        {{ $contactMessage->status === 'new' ? 'selected' : '' }}
                    >
                        New
                    </option>


                    <option
                        value="in_progress"
                        {{ $contactMessage->status === 'in_progress' ? 'selected' : '' }}
                    >
                        In Progress
                    </option>


                    <option
                        value="resolved"
                        {{ $contactMessage->status === 'resolved' ? 'selected' : '' }}
                    >
                        Resolved
                    </option>

                </select>


                <button type="submit">

                    Update Status

                </button>

            </form>


            <hr>


            <div class="admin-contact-read-info">

                <i class="fa-regular fa-eye"></i>

                <div>

                    <span>
                        First opened
                    </span>

                    <strong>

                        @if ($contactMessage->read_at)

                            {{
                                $contactMessage
                                    ->read_at
                                    ->format('M d, Y · h:i A')
                            }}

                        @else

                            Not opened

                        @endif

                    </strong>

                </div>

            </div>

        </aside>

    </div>

@endsection