@extends('frontend.layouts.app')

@section('title', 'Log in | MidPoint')

@section('hide_footer', '1')

@section('content')

<div class="mp-page">

    <div class="mp-auth">

        {{-- =========================================
            LEFT SIDE
        ========================================== --}}
        <section class="mp-auth-art">

            <a
                href="{{ url('/') }}"
                class="mb-auto flex
                       items-center gap-[9px]
                       font-['Bricolage_Grotesque']
                       text-[20px] font-extrabold"
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
                           max-w-[520px]
                           font-['Bricolage_Grotesque']
                           text-[34px]
                           font-extrabold
                           text-white"
                >
                    Welcome back to the safe middle.
                </h1>


                <p
                    class="max-w-[380px]
                           text-[#C8DAD2]"
                >
                    Your transactions, payouts and inspections —
                    all exactly where you left them.
                </p>

            </div>


            <div
                class="mp-small mt-auto
                       text-[#9DBBAF]"
            >
                "Buy with confidence. Sell with confidence."
            </div>

        </section>


        {{-- =========================================
            LOGIN
        ========================================== --}}
        <section class="mp-auth-form">

            <div class="mp-card mp-auth-card">

                <h2
                    class="mb-1
                           font-['Bricolage_Grotesque']
                           text-[22px]
                           font-bold"
                >
                    Log in
                </h2>


                <p class="mp-small mp-muted mb-5">

                    New to MidPoint?

                    <a
                        href="{{
                            route(
                                'register',
                                request()->filled('redirect')
                                    ? [
                                        'redirect' =>
                                            request('redirect')
                                    ]
                                    : []
                            )
                        }}"
                        class="font-semibold
                               text-[#7A5AF8]"
                    >
                        Create an account
                    </a>

                </p>


                <form
                    method="POST"
                    action="{{ route('login.attempt') }}"
                    autocomplete="on"
                >
                    @csrf

                    @if(session('status'))

                        <div
                            class="mb-4 rounded-xl
                                border border-[#ABEFC6]
                                bg-[#ECFDF3]
                                px-4 py-3
                                text-[13px]
                                leading-[1.55]
                                text-[#067647]"
                        >

                            <div class="flex items-start gap-2">

                                <i
                                    class="fa-solid
                                        fa-circle-check
                                        mt-[3px]"
                                ></i>


                                <span>

                                    {{ session('status') }}

                                </span>

                            </div>

                        </div>

                    @endif

                    @if ($errors->any())

                        <div
                            class="mb-4 rounded-xl
                                border border-red-200
                                bg-red-50
                                px-4 py-3
                                text-[13px]
                                text-red-600"
                        >

                            {{ $errors->first() }}

                        </div>

                    @endif


                    <div class="mp-field">

                        <label for="login">
                            Email or username
                        </label>

                        <input
                            id="login"
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="Email or admin username"
                            autocomplete="username"
                            required
                            autofocus
                        >

                    </div>


                    <div class="mp-field">

                        <label for="password">
                            Password
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <div
                        class="mb-[18px]
                            flex items-center
                            justify-between gap-4"
                    >

                        <label
                            class="flex items-center
                                gap-[7px]
                                text-[13px]"
                        >

                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                            >

                            Remember me

                        </label>


                        <a
                            href="{{ route('password.request') }}"
                            class="text-[13px]
                                font-semibold
                                text-[#12B76A]
                                transition
                                hover:text-[#0B3D2E]"
                        >
                            Forgot password?
                        </a>

                    </div>


                    <button
                        type="submit"
                        class="mp-btn
                            mp-btn-primary
                            mp-btn-lg
                            w-full"
                    >
                        Log in
                    </button>

                </form>

            </div>

        </section>

    </div>

</div>


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.getElementById('login-form');

    form?.addEventListener('submit', function (event) {

        event.preventDefault();

        /*
        |--------------------------------------------------------------------------
        | Temporary UI behaviour
        |--------------------------------------------------------------------------
        |
        | We will replace this with Laravel authentication when we create
        | the authentication/user module.
        |
        */

        const message =
            document.getElementById('login-demo-message');

        message.textContent =
            'Login form validated successfully. Authentication backend will be connected next.';

        message.classList.remove('text-[#5A6660]');

        message.classList.add(
            'text-[#0E7A4C]',
            'font-semibold'
        );

    });

});

</script>

@endpush

@endsection