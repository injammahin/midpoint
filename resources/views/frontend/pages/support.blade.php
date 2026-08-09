@extends('frontend.layouts.app')


@section(
    'title',
    'Support Centre | MidPoint'
)


@section(
    'meta_description',
    'Get help with MidPoint payments, delivery, disputes, inspection periods, business verification, KYC and account security.'
)


@section('content')


@php

    $supportItems = [

        [
            'icon' => '💳',
            'title' => 'Payments & payouts',
            'description' => 'When money is held, released, and how sellers get paid.',
            'background' => '#E8F7EF',
            'keywords' => 'payment payments payout payouts money escrow release seller paid',
        ],

        [
            'icon' => '🚚',
            'title' => 'Delivery & dispatch',
            'description' => 'How sellers arrange delivery and mark items dispatched.',
            'background' => '#F1EDFE',
            'keywords' => 'delivery dispatch courier rider shipping item dispatched',
        ],

        [
            'icon' => '⚖️',
            'title' => 'Disputes',
            'description' => 'Opening a dispute, evidence, and how resolutions work.',
            'background' => '#FEF4E6',
            'keywords' => 'dispute refund evidence resolution problem return',
        ],

        [
            'icon' => '⏱️',
            'title' => 'Inspection period',
            'description' => 'Your 8-hour window and auto-release explained.',
            'background' => '#E8F7EF',
            'keywords' => 'inspection 8 hour window auto release timer',
        ],

        [
            'icon' => '🏪',
            'title' => 'Business verification',
            'description' => 'Getting the verified badge and improving trust score.',
            'background' => '#F1EDFE',
            'keywords' => 'business verification verified badge trust seller',
        ],

        [
            'icon' => '🔐',
            'title' => 'Account & security',
            'description' => 'Login issues, password resets and account safety.',
            'background' => '#FDECEC',
            'keywords' => 'account security login password reset 2fa safety',
        ],

        [
            'icon' => '⚖️',
            'title' => 'Legal & policies',
            'description' => 'Terms, Privacy Policy, Escrow Policy and auto-release rules.',
            'background' => '#E8F7EF',
            'keywords' => 'legal terms privacy escrow policy rules',
            'href' => route('terms-and-conditions'),
        ],

        [
            'icon' => '🪪',
            'title' => 'Identity verification (KYC)',
            'description' => 'BVN checks, name matching and payout eligibility.',
            'background' => '#F1EDFE',
            'keywords' => 'kyc identity verification bvn name matching payout bank',
        ],

    ];


    $loginRedirect =
        route(
            'support',
            [
                'open_chat' => 1,
            ]
        );

@endphp


<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap !max-w-[860px]">


            {{-- Header --}}
            <div
                class="mp-section-head
                       mx-auto
                       text-center"
            >

                <div class="mp-eyebrow justify-center">
                    Support Centre
                </div>


                <h1>
                    How can we help?
                </h1>

            </div>



            {{-- Search --}}
            <div
                class="relative
                       mx-auto
                       mb-[34px]
                       max-w-[520px]"
            >

                <span
                    class="pointer-events-none
                           absolute
                           left-[18px]
                           top-1/2
                           -translate-y-1/2"
                >
                    🔍
                </span>


                <input
                    id="support-search"
                    type="search"
                    placeholder="Search help articles, e.g. 'inspection period'"
                    class="w-full
                           rounded-[14px]
                           border-[1.5px]
                           border-[#E4EAE6]
                           bg-white
                           py-[15px]
                           pl-[48px]
                           pr-[18px]
                           text-[14.5px]
                           outline-none
                           transition
                           focus:border-[#12B76A]
                           focus:shadow-[0_0_0_3px_#E8F7EF]"
                >

            </div>



            {{-- Topics --}}
            <div
                id="support-grid"
                class="grid
                       grid-cols-1
                       gap-5
                       sm:grid-cols-2
                       lg:grid-cols-3"
            >

                @foreach ($supportItems as $item)

                    @if (!empty($item['href']))

                        <a
                            href="{{ $item['href'] }}"
                            class="support-card
                                   mp-card
                                   block
                                   cursor-pointer
                                   p-[22px]
                                   transition
                                   hover:-translate-y-[2px]"
                            data-search="{{
                                strtolower(
                                    $item['title']
                                    .' '.
                                    $item['description']
                                    .' '.
                                    $item['keywords']
                                )
                            }}"
                        >

                    @else

                        <button
                            type="button"
                            class="support-card
                                   mp-card
                                   cursor-pointer
                                   p-[22px]
                                   text-left
                                   transition
                                   hover:-translate-y-[2px]"
                            data-search="{{
                                strtolower(
                                    $item['title']
                                    .' '.
                                    $item['description']
                                    .' '.
                                    $item['keywords']
                                )
                            }}"
                        >

                    @endif


                        <div
                            class="grid
                                   h-11
                                   w-11
                                   place-items-center
                                   rounded-[13px]
                                   text-[20px]"
                            style="
                                background:
                                {{ $item['background'] }}
                            "
                        >
                            {{ $item['icon'] }}
                        </div>


                        <h2
                            class="mb-1
                                   mt-3
                                   font-['Bricolage_Grotesque']
                                   text-[16px]
                                   font-bold"
                        >
                            {{ $item['title'] }}
                        </h2>


                        <p
                            class="text-[13px]
                                   leading-[1.65]
                                   text-[#5A6660]"
                        >
                            {{ $item['description'] }}
                        </p>


                    @if (!empty($item['href']))

                        </a>

                    @else

                        </button>

                    @endif

                @endforeach

            </div>



            {{-- Nothing found --}}
            <div
                id="support-empty"
                class="mt-5
                       hidden
                       rounded-[18px]
                       border
                       border-dashed
                       border-[#E4EAE6]
                       bg-white
                       p-8
                       text-center"
            >

                <div class="text-[28px]">
                    🔍
                </div>


                <h3
                    class="mt-2
                           font-['Bricolage_Grotesque']
                           font-bold"
                >
                    No help topic found
                </h3>


                <p
                    class="mt-1
                           text-[13px]
                           text-[#5A6660]"
                >
                    Try another search or contact
                    our support team.
                </p>

            </div>



            {{-- =================================================
                LIVE SUPPORT
            ================================================== --}}

            <div
                class="mp-card
                       mt-6
                       flex
                       flex-col
                       gap-[14px]
                       p-[26px]
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >

                <div>

                    <div
                        class="flex
                               items-center
                               gap-2"
                    >

                        <strong>
                            Need help with a live transaction?
                        </strong>


                        @if ($liveSupport['available'])

                            <span
                                class="inline-flex
                                       items-center
                                       gap-1
                                       rounded-full
                                       bg-[#E8F7EF]
                                       px-2
                                       py-1
                                       text-[11px]
                                       font-semibold
                                       text-[#0E7A4C]"
                            >

                                <span
                                    class="h-2
                                           w-2
                                           rounded-full
                                           bg-[#12B76A]"
                                ></span>

                                Online

                            </span>

                        @else

                            <span
                                class="rounded-full
                                       bg-[#F1F3F2]
                                       px-2
                                       py-1
                                       text-[11px]
                                       font-semibold
                                       text-[#68746E]"
                            >
                                Offline
                            </span>

                        @endif

                    </div>


                    <div
                        class="mt-1
                               text-[13px]
                               text-[#5A6660]"
                    >

                        @if ($liveSupport['available'])

                            Talk directly with a MidPoint
                            support specialist.

                        @else

                            {{ $liveSupport['message'] }}

                            @if (
                                $liveSupport[
                                    'next_available_label'
                                ]
                            )

                                <div
                                    class="mt-1
                                           font-medium
                                           text-[#0B3D2E]"
                                >
                                    Next available:
                                    {{
                                        $liveSupport[
                                            'next_available_label'
                                        ]
                                    }}
                                </div>

                            @endif

                        @endif

                    </div>

                </div>



                <div
                    class="flex
                           min-w-[170px]
                           flex-col
                           gap-[10px]"
                >

                    @if (
                        $liveSupport['available']
                        &&
                        auth()->check()
                    )

                        <button
                            type="button"
                            id="live-chat-button"
                            class="mp-btn
                                   mp-btn-primary"
                        >
                            <i class="fa-solid fa-comments"></i>

                            Start live chat
                        </button>


                    @elseif (
                        $liveSupport['available']
                    )

                        <a
                            href="{{
                                route(
                                    'login',
                                    [
                                        'redirect' =>
                                            $loginRedirect,
                                    ]
                                )
                            }}"
                            class="mp-btn
                                   mp-btn-primary"
                        >

                            <i class="fa-solid fa-right-to-bracket"></i>

                            Log in to chat

                        </a>


                    @else

                        <button
                            type="button"
                            disabled
                            class="mp-btn
                                   cursor-not-allowed
                                   border
                                   border-[#D9DFDB]
                                   bg-[#EEF1EF]
                                   text-[#87928C]
                                   opacity-80"
                        >

                            <i class="fa-regular fa-clock"></i>

                            Live chat offline

                        </button>

                    @endif


                    <a
                        href="{{ route('contact') }}"
                        class="mp-btn
                               mp-btn-outline"
                    >
                        Email us
                    </a>

                </div>

            </div>

        </div>

    </section>

</div>



{{-- =========================================================
    CUSTOMER CHAT PANEL
========================================================== --}}

@if(auth()->check())

    <div
        id="liveSupportWidget"
        class="ls-widget"
        data-start-url="{{
            route(
                'support.chat.start'
            )
        }}"
        data-session-base="{{
            url(
                '/support/chat/sessions'
            )
        }}"
        data-auto-open="{{
            request()->boolean(
                'open_chat'
            )
                ? '1'
                : '0'
        }}"
        data-user-id="{{
            auth()->id()
        }}"
        data-user-name="{{
            auth()->user()->name
        }}"
    >

        <div
            id="lsPanel"
            class="ls-panel"
        >

            {{-- Header --}}
            <div class="ls-header">

                <div>

                    <div class="ls-header-status">

                        <span class="ls-online-dot"></span>

                        MIDPOINT SUPPORT

                    </div>


                    <strong>
                        Live Support
                    </strong>

                </div>


                <button
                    type="button"
                    id="lsClose"
                    class="ls-close"
                    aria-label="Close chat"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>



            {{-- Agent/Queue Status --}}
            <div
                id="lsStatus"
                class="ls-status"
            >
                Connecting...
            </div>



            {{-- Messages --}}
            <div
                id="lsMessages"
                class="ls-messages"
            ></div>



            {{-- Attachments selected --}}
            <div
                id="lsSelectedFiles"
                class="ls-selected-files"
            ></div>



            {{-- Composer --}}
            <form
                id="lsComposer"
                class="ls-composer"
            >

                <div class="ls-input-wrap">

                    <textarea
                        id="lsInput"
                        rows="1"
                        maxlength="5000"
                        placeholder="Type your message..."
                    ></textarea>


                    <label
                        for="lsFiles"
                        class="ls-attachment-button"
                        title="Attach image, video or document"
                    >

                        <i class="fa-solid fa-paperclip"></i>

                    </label>


                    <input
                        id="lsFiles"
                        type="file"
                        multiple
                        hidden
                        accept="
                            image/jpeg,
                            image/png,
                            image/webp,
                            video/mp4,
                            video/webm,
                            video/quicktime,
                            application/pdf,
                            .doc,
                            .docx,
                            .xls,
                            .xlsx,
                            .csv,
                            .txt,
                            .zip
                        "
                    >


                    <button
                        type="submit"
                        id="lsSend"
                        class="ls-send"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                    </button>

                </div>

            </form>



            {{-- Rating --}}
            <div
                id="lsRating"
                class="ls-rating"
                hidden
            >

                <div class="ls-rating-icon">
                    ✨
                </div>


                <strong>
                    How was your support experience?
                </strong>


                <p>
                    Your feedback helps us improve.
                </p>


                <div
                    id="lsStars"
                    class="ls-stars"
                >

                    @for ($i = 1; $i <= 5; $i++)

                        <button
                            type="button"
                            data-rating="{{ $i }}"
                        >
                            ★
                        </button>

                    @endfor

                </div>


                <textarea
                    id="lsReview"
                    rows="3"
                    maxlength="2000"
                    placeholder="Anything you'd like us to know? (optional)"
                ></textarea>


                <button
                    type="button"
                    id="lsSubmitRating"
                    class="ls-rating-submit"
                >
                    Submit feedback
                </button>


                <button
                    type="button"
                    id="lsSkipRating"
                    class="ls-rating-skip"
                >
                    Skip
                </button>

            </div>

        </div>

    </div>

@endif



@push('scripts')

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const search =
            document.getElementById(
                'support-search'
            );

        const cards =
            document.querySelectorAll(
                '.support-card'
            );

        const empty =
            document.getElementById(
                'support-empty'
            );


        search?.addEventListener(
            'input',
            function () {

                const query =
                    this.value
                        .trim()
                        .toLowerCase();


                let visible =
                    0;


                cards.forEach(
                    function (card) {

                        const content =
                            card.dataset.search
                            || '';


                        const matched =
                            !query
                            ||
                            content.includes(
                                query
                            );


                        card.classList.toggle(
                            'hidden',
                            !matched
                        );


                        if (matched) {
                            visible++;
                        }

                    }
                );


                empty?.classList.toggle(
                    'hidden',
                    visible !== 0
                );

            }
        );

    }
);
</script>

@endpush


@endsection