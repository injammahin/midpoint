@extends('frontend.layouts.app')

@section('title', 'Create Account | Midpoint')

@section('hide_footer', '1')


@section('content')

<div class="mp-page">

    <div class="mp-auth">

        {{-- =====================================================
            LEFT
        ====================================================== --}}
        <section class="mp-auth-art">

            <a
                href="{{ route('home') }}"

                class="
                    mb-auto
                    flex
                    items-center
                    gap-[9px]
                    font-['Bricolage_Grotesque']
                    text-[20px]
                    font-extrabold
                "
            >

                <x-midpoint-brand variant="auth" />

            </a>


            <div>

                <h1
                    class="mb-3
                           font-['Bricolage_Grotesque']
                           text-[34px]
                           font-extrabold
                           text-white"
                >
                    Your next deal, fully protected.
                </h1>


                <div class="mp-timeline mt-[18px]">

                    <div class="mp-timeline-item done">

                        <div class="mp-timeline-dot">
                            ✓
                        </div>

                        <div class="mp-timeline-title text-white">
                            Free to join
                        </div>

                        <div class="mp-timeline-text !text-[#9DBBAF]">
                            Buyers pay no Midpoint fees at all
                        </div>

                    </div>


                    <div class="mp-timeline-item done">

                        <div class="mp-timeline-dot">
                            ✓
                        </div>

                        <div class="mp-timeline-title text-white">
                            One account, both roles
                        </div>

                        <div class="mp-timeline-text !text-[#9DBBAF]">
                            Buy and sell from the same profile
                        </div>

                    </div>


                    <div class="mp-timeline-item done">

                        <div class="mp-timeline-dot">
                            ✓
                        </div>

                        <div class="mp-timeline-title text-white">
                            Bank-grade payment security
                        </div>

                        <div class="mp-timeline-text !text-[#9DBBAF]">
                            Payments held safely until you're both happy
                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- =====================================================
            REGISTER
        ====================================================== --}}
        <section class="mp-auth-form">

            <div class="mp-card mp-auth-card">

                <h2
                    class="mb-1
                           font-['Bricolage_Grotesque']
                           text-[22px]
                           font-bold"
                >
                    Create your account
                </h2>


                <p class="mp-small mp-muted mb-4">

                    Already registered?

                    <a
                        href="{{
                            route(
                                'login',
                                request()->filled('redirect')
                                    ? [
                                        'redirect' =>
                                            request('redirect')
                                    ]
                                    : []
                            )
                        }}"
                        class="font-semibold text-[#7A5AF8]"
                    >
                        Log in
                    </a>

                </p>


                {{-- =================================================
                    ERRORS
                ================================================== --}}
                @if($errors->any())

                    <div
                        class="mb-4
                               rounded-xl
                               border border-red-200
                               bg-red-50
                               p-3
                               text-[12px]
                               text-red-700"
                    >

                        <ul class="list-disc space-y-1 pl-4">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif



                <form
                    id="register-form"
                    method="POST"
                    action="{{ route('register.store') }}"
                >

                    @csrf


                    <input
                        id="account-role"
                        type="hidden"
                        name="preferred_role"
                        value="{{ old('preferred_role', 'seller') }}"
                    >


                    {{-- =============================================
                        ROLE SELECTOR
                    ============================================== --}}
                    <div class="mp-segment">

                        <button
                            type="button"
                            class="account-role-btn
                                   {{
                                        old('preferred_role', 'seller') === 'seller'
                                            ? 'active'
                                            : ''
                                   }}"
                            data-role="seller"
                        >
                            I mostly sell
                        </button>


                        <button
                            type="button"
                            class="account-role-btn
                                   {{
                                        old('preferred_role') === 'buyer'
                                            ? 'active'
                                            : ''
                                   }}"
                            data-role="buyer"
                        >
                            I mostly buy
                        </button>

                    </div>



                    {{-- =============================================
                        FULL NAME
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="full_name">
                            Full name
                        </label>

                        <input
                            id="full_name"
                            type="text"
                            name="full_name"
                            value="{{ old('full_name') }}"
                            placeholder="e.g. Chiamaka Nwosu"
                            autocomplete="name"
                            required
                        >

                    </div>



                    {{-- =============================================
                        PHONE
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="phone">
                            Phone number
                        </label>

                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="0803 123 4567"
                            autocomplete="tel"
                            inputmode="tel"
                            maxlength="20"
                            aria-describedby="phone-help phone-client-error"
                            required
                        >

                        <p
                            id="phone-help"
                            class="mt-1.5 text-[11px] leading-[1.5] text-[#7A8680]"
                        >
                          
                        </p>

                        <p
                            id="phone-client-error"
                            class="mt-1.5 hidden text-[11px] font-semibold text-red-600"
                            role="alert"
                        ></p>

                    </div>



                    {{-- =============================================
                        EMAIL
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="email">
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                        >

                    </div>



                    {{-- =============================================
                        PASSWORD
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="register_password">
                            Password
                        </label>

                        <div class="relative">

                            <input
                                id="register_password"
                                type="password"
                                name="password"
                                placeholder="Minimum 8 characters"
                                minlength="8"
                                autocomplete="new-password"
                                class="!pr-[48px]"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle absolute right-[14px] top-1/2 flex h-[34px] w-[34px] -translate-y-1/2 items-center justify-center rounded-lg text-[#77827C] transition hover:bg-[#F1F4F2] hover:text-[#0B3D2E] focus:outline-none focus:ring-2 focus:ring-[#12B76A]/25"
                                data-target="register_password"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <i class="fa-regular fa-eye text-[15px]"></i>
                            </button>

                        </div>

                    </div>



                    {{-- =============================================
                        CONFIRM PASSWORD
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="password_confirmation">
                            Confirm password
                        </label>

                        <div class="relative">

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your password"
                                minlength="8"
                                autocomplete="new-password"
                                class="!pr-[48px]"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle absolute right-[14px] top-1/2 flex h-[34px] w-[34px] -translate-y-1/2 items-center justify-center rounded-lg text-[#77827C] transition hover:bg-[#F1F4F2] hover:text-[#0B3D2E] focus:outline-none focus:ring-2 focus:ring-[#12B76A]/25"
                                data-target="password_confirmation"
                                aria-label="Show confirm password"
                                aria-pressed="false"
                            >
                                <i class="fa-regular fa-eye text-[15px]"></i>
                            </button>

                        </div>

                    </div>



                    {{-- =============================================
                        SUBMIT
                    ============================================== --}}
                    <button
                        type="submit"
                        class="mp-btn
                               mp-btn-green
                               mp-btn-lg
                               w-full"
                    >
                        Create account
                    </button>


                    <p
                        class="mt-[14px]
                               text-center
                               text-[13px]
                               text-[#5A6660]"
                    >

                        By continuing you agree to our

                        <a
                            href="{{ route('terms-and-conditions') }}"
                            class="font-semibold text-[#7A5AF8]"
                        >
                            Terms
                        </a>,

                        <a
                            href="{{ route('privacy-policy') }}"
                            class="font-semibold text-[#7A5AF8]"
                        >
                            Privacy Policy
                        </a>

                        and

                        <a
                            href="{{ route('escrow-policy') }}"
                            class="font-semibold text-[#7A5AF8]"
                        >
                            Escrow Policy
                        </a>.

                    </p>

                </form>

            </div>

        </section>

    </div>

</div>

@endsection



@push('scripts')

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Preferred Role Selector
        |--------------------------------------------------------------------------
        */

        const roleButtons =
            document.querySelectorAll(
                '.account-role-btn'
            );

        const roleInput =
            document.getElementById(
                'account-role'
            );


        roleButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        roleButtons.forEach(
                            function (item) {

                                item.classList.remove(
                                    'active'
                                );

                            }
                        );


                        button.classList.add(
                            'active'
                        );


                        roleInput.value =
                            button.dataset.role;

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Nigerian Phone Validation
        |--------------------------------------------------------------------------
        | Accepted examples:
        | 0803 123 4567
        | 08031234567
        | +234 803 123 4567
        | 2348031234567
        |
        | The server remains the authoritative validator. This browser validation
        | exists only to give the user immediate feedback before submission.
        |--------------------------------------------------------------------------
        */

        const registerForm =
            document.getElementById(
                'register-form'
            );

        const phoneInput =
            document.getElementById(
                'phone'
            );

        const phoneError =
            document.getElementById(
                'phone-client-error'
            );


        function normalizeNigerianPhone(
            value
        ) {

            const rawPhone =
                String(
                    value || ''
                )
                    .trim();


            if (
                !/^\+?[0-9\s().-]+$/.test(
                    rawPhone
                )
            ) {

                return null;
            }


            const digits =
                rawPhone.replace(
                    /\D/g,
                    ''
                );


            let nationalNumber =
                '';


            if (
                digits.length === 11
                &&
                digits.startsWith(
                    '0'
                )
            ) {

                nationalNumber =
                    digits.slice(
                        1
                    );

            } else if (
                digits.length === 13
                &&
                digits.startsWith(
                    '234'
                )
            ) {

                nationalNumber =
                    digits.slice(
                        3
                    );

            }


            if (
                !/^[789]\d{9}$/.test(
                    nationalNumber
                )
            ) {

                return null;
            }


            return '+234' + nationalNumber;
        }


        function formatNigerianPhone(
            normalizedPhone
        ) {

            if (
                !normalizedPhone
                ||
                !/^\+234[789]\d{9}$/.test(
                    normalizedPhone
                )
            ) {

                return normalizedPhone || '';
            }


            const nationalNumber =
                normalizedPhone.slice(
                    4
                );


            return '+234 '
                + nationalNumber.slice(0, 3)
                + ' '
                + nationalNumber.slice(3, 6)
                + ' '
                + nationalNumber.slice(6);
        }


        function showPhoneError(
            message
        ) {

            if (
                !phoneInput
            ) {

                return;
            }


            phoneInput.setCustomValidity(
                message
            );


            if (
                phoneError
            ) {

                phoneError.textContent =
                    message;

                phoneError.classList.toggle(
                    'hidden',
                    message === ''
                );
            }
        }


        function validatePhone() {

            if (
                !phoneInput
            ) {

                return true;
            }


            const normalizedPhone =
                normalizeNigerianPhone(
                    phoneInput.value
                );


            if (
                !normalizedPhone
            ) {

                showPhoneError(
                    'Enter a valid Nigerian mobile number, for example 0803 123 4567 or +234 803 123 4567.'
                );

                return false;
            }


            showPhoneError(
                ''
            );


            return true;
        }


        if (
            phoneInput
        ) {

            phoneInput.addEventListener(
                'input',
                function () {

                    showPhoneError(
                        ''
                    );

                }
            );


            phoneInput.addEventListener(
                'blur',
                function () {

                    const normalizedPhone =
                        normalizeNigerianPhone(
                            phoneInput.value
                        );


                    if (
                        normalizedPhone
                    ) {

                        phoneInput.value =
                            formatNigerianPhone(
                                normalizedPhone
                            );

                        showPhoneError(
                            ''
                        );

                    } else if (
                        phoneInput.value.trim() !== ''
                    ) {

                        validatePhone();
                    }

                }
            );
        }


        if (
            registerForm
        ) {

            registerForm.addEventListener(
                'submit',
                function (event) {

                    if (
                        !validatePhone()
                    ) {

                        event.preventDefault();

                        phoneInput.focus();

                        return;
                    }

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Password Visibility Toggles
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.password-toggle'
            )
            .forEach(
                function (toggleButton) {

                    toggleButton.addEventListener(
                        'click',
                        function () {

                            const inputId =
                                toggleButton.dataset.target;

                            const input =
                                document.getElementById(
                                    inputId
                                );


                            if (
                                !input
                            ) {

                                return;
                            }


                            const showingPassword =
                                input.type === 'text';


                            input.type =
                                showingPassword
                                    ? 'password'
                                    : 'text';


                            toggleButton.setAttribute(
                                'aria-pressed',
                                showingPassword
                                    ? 'false'
                                    : 'true'
                            );


                            toggleButton.setAttribute(
                                'aria-label',
                                showingPassword
                                    ? 'Show password'
                                    : 'Hide password'
                            );


                            const icon =
                                toggleButton.querySelector(
                                    'i'
                                );


                            if (
                                icon
                            ) {

                                icon.classList.toggle(
                                    'fa-eye',
                                    showingPassword
                                );

                                icon.classList.toggle(
                                    'fa-eye-slash',
                                    !showingPassword
                                );
                            }

                        }
                    );

                }
            );

    }
);
</script>

@endpush