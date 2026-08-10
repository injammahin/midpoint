@extends('frontend.layouts.app')

@section('title', 'Create Account | MidPoint')

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
                class="mb-auto flex
                       items-center gap-[9px]
                       font-['Bricolage_Grotesque']
                       text-[20px]
                       font-extrabold"
            >

                <span
                    class="grid h-8 w-8
                           place-items-center
                           rounded-[10px]
                           bg-gradient-to-br
                           from-[#0B3D2E]
                           to-[#12B76A]
                           text-[15px]
                           text-white"
                >
                    M
                </span>

                <span>
                    <span class="text-white">Mid</span><span class="text-[#C4B5FD]">Point</span>
                </span>

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
                            Buyers pay no MidPoint fees at all
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
                            placeholder="0803 xxx xxxx"
                            autocomplete="tel"
                            required
                        >

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

                        <input
                            id="register_password"
                            type="password"
                            name="password"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                    </div>



                    {{-- =============================================
                        CONFIRM PASSWORD
                    ============================================== --}}
                    <div class="mp-field">

                        <label for="password_confirmation">
                            Confirm password
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Re-enter your password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

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

    }
);
</script>

@endpush