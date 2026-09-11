@extends('frontend.layouts.app')

@section('title', 'Contact | Midpoint')

@section(
    'meta_description',
    'Contact Midpoint for transaction support, business verification, partnerships and general enquiries.'
)

@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap !max-w-[960px]">

            <div class="mp-grid-2 items-start">

                {{-- =========================================
                    CONTACT INFORMATION
                ========================================== --}}
                <div>

                    <div class="mp-eyebrow">
                        Contact
                    </div>


                    <h1
                        class="mb-3
                               max-w-[480px]
                               font-['Bricolage_Grotesque']
                               text-[clamp(26px,3.2vw,36px)]
                               font-extrabold
                               leading-[1.15]"
                    >
                        Talk to a human at Midpoint.
                    </h1>


                    <p
                        class="max-w-[520px]
                               text-[15px]
                               leading-[1.65]
                               text-[#5A6660]"
                    >
                        Questions about a transaction, partnerships,
                        verification or press — we're here.
                    </p>


                    <div
                        class="mt-[26px]
                               flex flex-col gap-4"
                    >



                        {{-- Email --}}
                        <div class="flex items-start gap-[14px]">

                            <div
                                class="grid h-11 w-11
                                       shrink-0 place-items-center
                                       rounded-[13px]
                                       bg-[#E8F7EF]
                                       text-[20px]"
                            >
                                ✉️
                            </div>

                            <div>

                                <h2
                                    class="font-['Bricolage_Grotesque']
                                           text-[16px]
                                           font-bold"
                                >
                                    Email
                                </h2>

                                <div
                                    class="flex flex-wrap
                                           text-[13px]
                                           leading-[1.6]
                                           text-[#5A6660]"
                                >

                                    <a
                                        href="mailto:support@midpoint.ng"
                                        class="transition hover:text-[#12B76A]"
                                    >
                                        support@midpoint.ng
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =========================================
                    CONTACT FORM
                ========================================== --}}
                <div class="mp-card p-7">

                    <form
                        id="contact-form"
                        method="POST"
                        action="{{ route('contact.store') }}"
                    >
                        @csrf


                        {{-- Spam Honeypot --}}
                        <div
                            style="
                                position:absolute;
                                left:-9999px;
                                width:1px;
                                height:1px;
                                overflow:hidden;
                            "
                            aria-hidden="true"
                        >

                            <label>
                                Website

                                <input
                                    type="text"
                                    name="website"
                                    tabindex="-1"
                                    autocomplete="off"
                                >

                            </label>

                        </div>


                        {{-- Validation Errors --}}
                        @if ($errors->any())

                            <div
                                class="mb-5 rounded-[12px]
                                    border border-red-200
                                    bg-red-50
                                    px-4 py-3"
                            >

                                <div
                                    class="mb-1
                                        text-[13px]
                                        font-semibold
                                        text-red-600"
                                >
                                    Please check the information below.
                                </div>


                                <ul
                                    class="list-inside list-disc
                                        text-[12px]
                                        leading-[1.7]
                                        text-red-500"
                                >

                                    @foreach ($errors->all() as $error)

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif


                        {{-- Full Name --}}
                        <div class="mp-field">

                            <label for="contact-name">
                                Full name
                            </label>

                            <input
                                id="contact-name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="e.g. Ngozi Adeyemi"
                                autocomplete="name"
                                required
                            >

                        </div>


                        {{-- Email --}}
                        <div class="mp-field">

                            <label for="contact-email">
                                Email
                            </label>

                            <input
                                id="contact-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@example.com"
                                autocomplete="email"
                                required
                            >

                        </div>


                        {{-- Topic --}}
                        <div class="mp-field">

                            <label for="contact-topic">
                                Topic
                            </label>

                            <select
                                id="contact-topic"
                                name="topic"
                                required
                            >

                                <option
                                    value="transaction_help"
                                    {{ old('topic') === 'transaction_help' ? 'selected' : '' }}
                                >
                                    Transaction help
                                </option>


                                <option
                                    value="delivery_dispatch"
                                    {{ old('topic') === 'delivery_dispatch' ? 'selected' : '' }}
                                >
                                    Delivery & dispatch
                                </option>


                                <option
                                    value="business_verification"
                                    {{ old('topic') === 'business_verification' ? 'selected' : '' }}
                                >
                                    Business verification
                                </option>


                                <option
                                    value="partnership"
                                    {{ old('topic') === 'partnership' ? 'selected' : '' }}
                                >
                                    Partnership
                                </option>


                                <option
                                    value="other"
                                    {{ old('topic') === 'other' ? 'selected' : '' }}
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        {{-- Message --}}
                        <div class="mp-field">

                            <label for="contact-message">
                                Message
                            </label>

                            <textarea
                                id="contact-message"
                                name="message"
                                rows="4"
                                placeholder="How can we help?"
                                required
                            >{{ old('message') }}</textarea>

                        </div>


                        {{-- Cloudflare Turnstile CAPTCHA --}}
                        <div class="mb-5">

                            <label
                                class="mb-2 block text-[13px] font-semibold text-[#0D120F]"
                            >
                                Security verification
                            </label>


                            @if ($turnstileSiteKey !== '')

                                <div
                                    class="cf-turnstile"
                                    data-sitekey="{{ $turnstileSiteKey }}"
                                    data-action="{{ $turnstileAction }}"
                                    data-theme="light"
                                    data-size="flexible"
                                ></div>

                            @else

                                <div
                                    class="rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-[12px] leading-[1.6] text-red-600"
                                >
                                    The contact form is temporarily unavailable because security verification is not configured.
                                </div>

                            @endif


                            @error('cf-turnstile-response')

                                <p class="mt-2 text-[12px] text-red-500">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <button
                            type="submit"
                            id="contact-submit"
                            {{ $turnstileSiteKey === '' ? 'disabled' : '' }}
                            class="mp-btn
                                mp-btn-primary
                                w-full"
                        >

                            <span>
                                Send message
                            </span>

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>

</div>


@push('scripts')

@if ($turnstileSiteKey !== '')
    <script
        src="https://challenges.cloudflare.com/turnstile/v0/api.js"
        async
        defer
    ></script>
@endif

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.querySelector(
                'form[action="{{ route('contact.store') }}"]'
            );

        const button =
            document.getElementById(
                'contact-submit'
            );


        if (!form || !button) {
            return;
        }


        form.addEventListener(
            'submit',
            function () {

                if (!form.checkValidity()) {
                    return;
                }


                button.disabled = true;

                button.classList.add(
                    'opacity-70',
                    'cursor-not-allowed'
                );


                button.innerHTML = `
                    <span
                        class="inline-block
                               h-4 w-4
                               animate-spin
                               rounded-full
                               border-2
                               border-white/40
                               border-t-white"
                    ></span>

                    Sending...
                `;

            }
        );

    }
);

</script>

@endpush
@endsection
