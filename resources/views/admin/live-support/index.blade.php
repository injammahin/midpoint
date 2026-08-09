@extends('admin.layouts.app')


@section('title', 'Live Support')


@section('page-title', 'Live Support')


@section('content')

<div
    id="adminLiveSupport"
    class="als-page"

    data-feed-url="{{
        route(
            'admin.live-support.feed'
        )
    }}"

    data-session-base="{{
        url(
            '/support/chat/sessions'
        )
    }}"

    data-claim-base="{{
        url(
            '/admin/support-inquiries/live-support/sessions'
        )
    }}"

    data-availability-url="{{
        route(
            'admin.live-support.availability'
        )
    }}"

    data-heartbeat-url="{{
        route(
            'admin.live-support.heartbeat'
        )
    }}"
>


    {{-- =====================================================
        PAGE HEADER
    ====================================================== --}}

    <div class="als-topbar">

        <div class="als-title-area">

            <div class="als-eyebrow">

                <span></span>

                LIVE SUPPORT CENTRE

            </div>


            <h2>
                Live Support
            </h2>


            <p>
                Manage customer conversations,
                waiting queues and realtime support.
            </p>

        </div>



        <div class="als-top-actions">

            {{-- Settings --}}
            <a
                href="{{
                    route(
                        'admin.live-support.settings'
                    )
                }}"
                class="als-settings-button"
            >

                <i class="fa-solid fa-gear"></i>

                <span>
                    Settings
                </span>

            </a>



            {{-- Availability --}}
            <label
                class="als-availability"
                for="alsAvailable"
            >

                <input
                    id="alsAvailable"
                    type="checkbox"
                    {{
                        $profile->is_accepting_chats
                            ? 'checked'
                            : ''
                    }}
                >


                <span class="als-switch">
                    <span></span>
                </span>


                <strong
                    id="alsAvailabilityText"
                >
                    {{
                        $profile->is_accepting_chats
                            ? 'Available'
                            : 'Unavailable'
                    }}
                </strong>

            </label>

        </div>

    </div>



    {{-- =====================================================
        STAT CARDS
    ====================================================== --}}

    <div class="als-stats">


        {{-- Waiting --}}
        <div class="als-stat-card">

            <div class="als-stat-content">

                <span class="als-stat-label">
                    Waiting
                </span>


                <strong
                    id="alsWaitingCount"
                    class="als-stat-value"
                >
                    {{ $waitingCount }}
                </strong>


                <small>
                    Customers in queue
                </small>

            </div>


            <div class="als-stat-icon waiting">

                <i class="fa-regular fa-clock"></i>

            </div>

        </div>



        {{-- Active --}}
        <div class="als-stat-card">

            <div class="als-stat-content">

                <span class="als-stat-label">
                    My active chats
                </span>


                <strong
                    id="alsActiveCount"
                    class="als-stat-value"
                >
                    {{ $activeCount }}
                </strong>


                <small>
                    Conversations assigned to you
                </small>

            </div>


            <div class="als-stat-icon active">

                <i class="fa-solid fa-comments"></i>

            </div>

        </div>



        {{-- Capacity --}}
        <div class="als-stat-card">

            <div class="als-stat-content">

                <span class="als-stat-label">
                    Capacity
                </span>


                <strong class="als-stat-value">

                    {{
                        $profile->max_active_chats
                    }}

                </strong>


                <small>
                    Maximum concurrent chats
                </small>

            </div>


            <div class="als-stat-icon capacity">

                <i class="fa-solid fa-headset"></i>

            </div>

        </div>

    </div>



    {{-- =====================================================
        CHAT WORKSPACE
    ====================================================== --}}

    <div class="als-workspace">


        {{-- =================================================
            LEFT SIDEBAR / INBOX
        ================================================== --}}

        <aside class="als-inbox">


            {{-- Inbox Header --}}
            <div class="als-inbox-head">

                <div>

                    <strong>
                        Conversations
                    </strong>

                    <span>
                        Customer support inbox
                    </span>

                </div>

            </div>



            {{-- Tabs --}}
            <div class="als-inbox-tabs">

                <button
                    type="button"
                    class="active"
                    data-support-tab="waiting"
                >

                    <i class="fa-regular fa-clock"></i>

                    Queue

                    @if($waitingCount > 0)

                        <span>
                            {{ $waitingCount }}
                        </span>

                    @endif

                </button>


                <button
                    type="button"
                    data-support-tab="active"
                >

                    <i class="fa-regular fa-comments"></i>

                    My chats

                    @if($activeCount > 0)

                        <span>
                            {{ $activeCount }}
                        </span>

                    @endif

                </button>

            </div>



            {{-- Waiting --}}
            <div
                id="alsWaitingList"
                class="als-list"
            >

                <div class="als-list-loading">

                    <i class="fa-solid fa-circle-notch fa-spin"></i>

                    Loading queue...

                </div>

            </div>



            {{-- Active --}}
            <div
                id="alsActiveList"
                class="als-list"
                hidden
            >

                <div class="als-list-loading">

                    <i class="fa-solid fa-circle-notch fa-spin"></i>

                    Loading conversations...

                </div>

            </div>

        </aside>



        {{-- =================================================
            CONVERSATION AREA
        ================================================== --}}

        <section class="als-chat">


            {{-- Empty --}}
            <div
                id="alsEmptyChat"
                class="als-empty-chat"
            >

                <div class="als-empty-icon">

                    <i class="fa-solid fa-comments"></i>

                </div>


                <strong>
                    Select a conversation
                </strong>


                <p>
                    Waiting customers and your active
                    conversations will appear here.
                </p>


                <span class="als-empty-hint">

                    <i class="fa-solid fa-arrow-left"></i>

                    Choose a conversation from the inbox

                </span>

            </div>



            {{-- =================================================
                ACTIVE CONVERSATION
            ================================================== --}}

            <div
                id="alsConversation"
                class="als-conversation"
                hidden
            >


                {{-- Header --}}
                <header class="als-chat-header">

                    <div class="als-customer">

                        <div
                            id="alsCustomerAvatar"
                            class="als-customer-avatar"
                        >
                            C
                        </div>


                        <div class="als-customer-details">

                            <strong
                                id="alsCustomerName"
                            >
                                Customer
                            </strong>


                            <span
                                id="alsCustomerEmail"
                            ></span>

                        </div>

                    </div>



                    <div class="als-chat-header-actions">


                        {{-- Accept --}}
                        <button
                            id="alsClaim"
                            type="button"
                            class="als-accept-button"
                            hidden
                        >

                            <i class="fa-solid fa-check"></i>

                            Accept chat

                        </button>



                        {{-- Resolve --}}
                        <button
                            id="alsResolve"
                            type="button"
                            class="als-resolve-button"
                            hidden
                        >

                            <i class="fa-solid fa-circle-check"></i>

                            Resolve

                        </button>

                    </div>

                </header>



                {{-- Messages --}}
                <div
                    id="alsMessages"
                    class="als-messages"
                ></div>



                {{-- Selected Files --}}
                <div
                    id="alsSelectedFiles"
                    class="als-selected-files"
                ></div>



                {{-- =================================================
                    COMPOSER
                ================================================== --}}

                <form
                    id="alsComposer"
                    class="als-composer"
                    hidden
                >

                    <div class="als-composer-box">

                        <textarea
                            id="alsInput"
                            rows="2"
                            placeholder="Write a reply..."
                        ></textarea>


                        <div class="als-composer-bottom">


                            {{-- Attachment --}}
                            <label
                                for="alsFiles"
                                class="als-file-button"
                                title="Attach a file"
                            >

                                <i class="fa-solid fa-paperclip"></i>

                                <span>
                                    Attachment
                                </span>

                            </label>


                            <input
                                id="alsFiles"
                                type="file"
                                multiple
                                hidden
                                accept="
                                    image/*,
                                    video/*,
                                    .pdf,
                                    .doc,
                                    .docx,
                                    .xls,
                                    .xlsx,
                                    .csv,
                                    .txt,
                                    .zip
                                "
                            >



                            {{-- Send --}}
                            <button
                                type="submit"
                                class="als-send-button"
                            >

                                <span>
                                    Send
                                </span>

                                <i class="fa-solid fa-paper-plane"></i>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </section>

    </div>



    {{-- =====================================================
        RESOLUTION MODAL
    ====================================================== --}}

    <div
        id="alsResolveModal"
        class="als-modal"
        hidden
    >

        <div
            class="als-modal-overlay"
            data-close-resolve-modal
        ></div>


        <div class="als-modal-card">


            {{-- Modal Header --}}
            <div class="als-modal-title">

                <div class="als-modal-title-left">

                    <span class="als-modal-icon">

                        <i class="fa-solid fa-circle-check"></i>

                    </span>


                    <div>

                        <strong>
                            Resolve conversation
                        </strong>


                        <p>
                            Select the outcome before
                            closing this support case.
                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    id="alsResolveClose"
                    aria-label="Close"
                >

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>



            {{-- Resolution --}}
            <div class="als-modal-field">

                <label for="alsResolutionCode">

                    Resolution

                </label>


                <select id="alsResolutionCode">

                    <option value="resolved">
                        Issue resolved
                    </option>

                    <option value="explained">
                        Information provided
                    </option>

                    <option value="transaction_issue">
                        Transaction issue resolved
                    </option>

                    <option value="account_issue">
                        Account issue resolved
                    </option>

                    <option value="other">
                        Other
                    </option>

                </select>

            </div>



            {{-- Note --}}
            <div class="als-modal-field">

                <label for="alsResolutionNote">

                    Internal / closing note

                </label>


                <textarea
                    id="alsResolutionNote"
                    rows="4"
                    placeholder="What was done to resolve the customer's issue?"
                ></textarea>

            </div>



            {{-- Action --}}
            <div class="als-modal-actions">

                <button
                    id="alsConfirmResolve"
                    type="button"
                    class="als-confirm-resolve"
                >

                    <i class="fa-solid fa-check"></i>

                    Resolve conversation

                </button>

            </div>

        </div>

    </div>



    {{-- =====================================================
        TOAST
    ====================================================== --}}

    <div
        id="alsToast"
        class="als-toast"
        role="status"
    ></div>

</div>

@endsection