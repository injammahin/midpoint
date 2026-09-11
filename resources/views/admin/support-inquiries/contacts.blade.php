@extends('admin.layouts.app')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@section('content')

    <div class="admin-module-header">
        <div>
            <h2>Contact Messages</h2>
            <p>Messages submitted through the public Contact page.</p>
        </div>
    </div>


    @if (session('success'))
        <div class="admin-success-alert">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif


    @if ($errors->any())
        <div class="admin-contact-error-alert">
            <i class="fa-solid fa-circle-exclamation"></i>

            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif


    <div class="admin-card admin-contact-toolbar">
        <form method="GET" action="{{ route('admin.support-inquiries.contacts') }}" class="admin-contact-filter-form">
            <div class="admin-contact-search">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Search name, email or message...">
            </div>


            <select name="filter">
                <option value="">All messages</option>
                <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>
                    Unread
                </option>
                <option value="read" {{ request('filter') === 'read' ? 'selected' : '' }}>
                    Read
                </option>
            </select>


            <select name="status">
                <option value="">All statuses</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>
                    New
                </option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>
                    In Progress
                </option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>
                    Resolved
                </option>
            </select>


            <button type="submit" class="admin-filter-button">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>


            @if (request()->hasAny(['search', 'filter', 'status']))
                <a href="{{ route('admin.support-inquiries.contacts') }}" class="admin-filter-reset">
                    Reset
                </a>
            @endif
        </form>
    </div>


    @if ($messages->isNotEmpty())
        <div class="admin-card admin-contact-bulk-bar">
            <div class="admin-contact-bulk-selection">
                <button type="button" id="contact-select-all" class="admin-contact-select-all" aria-pressed="false">
                    <i class="fa-regular fa-square-check"></i>
                    <span>Select all</span>
                </button>

                <span id="contact-selected-count">0 selected</span>
            </div>


            <form id="contact-bulk-delete-form" method="POST"
                action="{{ route('admin.support-inquiries.contacts.bulk-destroy') }}">
                @csrf
                @method('DELETE')

                <button type="submit" id="contact-bulk-delete" class="admin-contact-bulk-delete" disabled>
                    <i class="fa-solid fa-trash-can"></i>
                    Delete selected
                </button>
            </form>
        </div>
    @endif


    <div class="admin-card admin-contact-list">
        @forelse ($messages as $message)
            <div class="admin-contact-row {{ $message->isUnread() ? 'unread' : '' }}" data-contact-row>
                <label class="admin-contact-checkbox-wrap" for="contact-message-{{ $message->id }}"
                    title="Select {{ $message->name }}">
                    <input id="contact-message-{{ $message->id }}" class="admin-contact-checkbox" type="checkbox"
                        name="message_ids[]" value="{{ $message->id }}" form="contact-bulk-delete-form">
                    <span aria-hidden="true"></span>
                </label>


                <a href="{{ route(
                'admin.support-inquiries.contacts.show',
                $message
            ) }}" class="admin-contact-row-link">
                    <span class="admin-contact-avatar">
                        {{ strtoupper(substr($message->name, 0, 1)) }}
                    </span>


                    <span class="admin-contact-person">
                        <strong>
                            @if ($message->isUnread())
                                <span class="admin-contact-unread-dot"></span>
                            @endif

                            {{ $message->name }}
                        </strong>

                        <small>{{ $message->email }}</small>
                    </span>


                    <span class="admin-contact-topic">
                        {{ $message->topic_label }}
                    </span>


                    <span class="admin-contact-date">
                        {{ $message->created_at->diffForHumans() }}
                    </span>


                    <span class="admin-contact-status status-{{ $message->status }}">
                        {{
                match ($message->status) {
                    'new' => 'New',
                    'in_progress' => 'In Progress',
                    'resolved' => 'Resolved',
                    default => ucfirst($message->status),
                }
                                        }}
                    </span>


                    <span class="admin-contact-arrow" title="View message">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>


                <form method="POST" action="{{ route(
                'admin.support-inquiries.contacts.destroy',
                $message
            ) }}" class="admin-contact-single-delete-form"
                    onsubmit="return confirm('Permanently delete this contact message? This cannot be undone.');">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="admin-contact-delete-icon" title="Delete message"
                        aria-label="Delete message from {{ $message->name }}">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        @empty
            <div class="admin-contact-empty">
                <span>
                    <i class="fa-solid fa-envelope-open"></i>
                </span>

                <strong>No contact messages</strong>
                <p>Contact form submissions will appear here.</p>
            </div>
        @endforelse
    </div>


    @if ($messages->hasPages())
        <div class="admin-contact-pagination">
            {{ $messages->links() }}
        </div>
    @endif

@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllButton = document.getElementById('contact-select-all');
            const bulkDeleteForm = document.getElementById('contact-bulk-delete-form');
            const bulkDeleteButton = document.getElementById('contact-bulk-delete');
            const selectedCount = document.getElementById('contact-selected-count');
            const checkboxes = Array.from(
                document.querySelectorAll('.admin-contact-checkbox')
            );

            if (
                !selectAllButton
                || !bulkDeleteForm
                || !bulkDeleteButton
                || !selectedCount
                || checkboxes.length === 0
            ) {
                return;
            }

            function syncSelection() {
                const selected = checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                });
                const allSelected = selected.length === checkboxes.length;

                selectedCount.textContent = selected.length + ' selected';
                bulkDeleteButton.disabled = selected.length === 0;
                selectAllButton.setAttribute('aria-pressed', allSelected ? 'true' : 'false');
                selectAllButton.querySelector('span').textContent = allSelected
                    ? 'Clear all'
                    : 'Select all';

                checkboxes.forEach(function (checkbox) {
                    const row = checkbox.closest('[data-contact-row]');

                    if (row) {
                        row.classList.toggle('selected', checkbox.checked);
                    }
                });
            }

            selectAllButton.addEventListener('click', function () {
                const shouldSelectAll = !checkboxes.every(function (checkbox) {
                    return checkbox.checked;
                });

                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = shouldSelectAll;
                });

                syncSelection();
            });

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', syncSelection);
            });

            bulkDeleteForm.addEventListener('submit', function (event) {
                const totalSelected = checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (totalSelected === 0) {
                    event.preventDefault();
                    return;
                }

                const confirmed = window.confirm(
                    'Permanently delete ' + totalSelected +
                    ' selected contact message' +
                    (totalSelected === 1 ? '?' : 's?') +
                    ' This cannot be undone.'
                );

                if (!confirmed) {
                    event.preventDefault();
                }
            });

            syncSelection();
        });
    </script>
@endpush