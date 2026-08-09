@extends('admin.layouts.app')


@section(
    'title',
    'Contact Messages'
)


@section(
    'page-title',
    'Contact Messages'
)


@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="admin-module-header">

        <div>

            <h2>
                Contact Messages
            </h2>

            <p>
                Messages submitted through the public Contact page.
            </p>

        </div>

    </div>


    {{-- =========================================================
        FILTERS
    ========================================================== --}}
    <div
        class="admin-card
               admin-contact-toolbar"
    >

        <form
            method="GET"
            action="{{ route('admin.support-inquiries.contacts') }}"
            class="admin-contact-filter-form"
        >

            <div class="admin-contact-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email or message..."
                >

            </div>


            <select name="filter">

                <option value="">
                    All messages
                </option>

                <option
                    value="unread"
                    {{ request('filter') === 'unread' ? 'selected' : '' }}
                >
                    Unread
                </option>

                <option
                    value="read"
                    {{ request('filter') === 'read' ? 'selected' : '' }}
                >
                    Read
                </option>

            </select>


            <select name="status">

                <option value="">
                    All statuses
                </option>

                <option
                    value="new"
                    {{ request('status') === 'new' ? 'selected' : '' }}
                >
                    New
                </option>

                <option
                    value="in_progress"
                    {{ request('status') === 'in_progress' ? 'selected' : '' }}
                >
                    In Progress
                </option>

                <option
                    value="resolved"
                    {{ request('status') === 'resolved' ? 'selected' : '' }}
                >
                    Resolved
                </option>

            </select>


            <button
                type="submit"
                class="admin-filter-button"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            @if (
                request()->hasAny([
                    'search',
                    'filter',
                    'status',
                ])
            )

                <a
                    href="{{ route('admin.support-inquiries.contacts') }}"
                    class="admin-filter-reset"
                >
                    Reset
                </a>

            @endif

        </form>

    </div>


    {{-- =========================================================
        MESSAGES
    ========================================================== --}}
    <div
        class="admin-card
               admin-contact-list"
    >

        @forelse ($messages as $message)

            <a
                href="{{ route(
                    'admin.support-inquiries.contacts.show',
                    $message
                ) }}"
                class="admin-contact-row
                       {{ $message->isUnread() ? 'unread' : '' }}"
            >

                {{-- Avatar --}}
                <span class="admin-contact-avatar">

                    {{
                        strtoupper(
                            substr(
                                $message->name,
                                0,
                                1
                            )
                        )
                    }}

                </span>


                {{-- Sender --}}
                <span class="admin-contact-person">

                    <strong>

                        @if ($message->isUnread())

                            <span
                                class="admin-contact-unread-dot"
                            ></span>

                        @endif

                        {{ $message->name }}

                    </strong>


                    <small>
                        {{ $message->email }}
                    </small>

                </span>


                {{-- Topic --}}
                <span class="admin-contact-topic">

                    {{ $message->topic_label }}

                </span>


                {{-- Date --}}
                <span class="admin-contact-date">

                    {{
                        $message
                            ->created_at
                            ->diffForHumans()
                    }}

                </span>


                {{-- Status --}}
                <span
                    class="admin-contact-status
                           status-{{ $message->status }}"
                >

                    {{
                        match ($message->status) {
                            'new' => 'New',
                            'in_progress' => 'In Progress',
                            'resolved' => 'Resolved',
                            default => ucfirst($message->status),
                        }
                    }}

                </span>


                <span class="admin-contact-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>

            </a>

        @empty

            <div class="admin-contact-empty">

                <span>
                    <i class="fa-solid fa-envelope-open"></i>
                </span>

                <strong>
                    No contact messages
                </strong>

                <p>
                    Contact form submissions will appear here.
                </p>

            </div>

        @endforelse

    </div>


    {{-- Pagination --}}
    @if ($messages->hasPages())

        <div class="admin-contact-pagination">

            {{ $messages->links() }}

        </div>

    @endif

@endsection