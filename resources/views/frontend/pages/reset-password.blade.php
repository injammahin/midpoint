@extends('frontend.layouts.app')

@section('title', 'Reset Password | MidPoint')

@section('hide_footer', '1')


@section('content')

<div class="mp-page">

    <div class="mp-auth">


        {{-- =========================================
            LEFT SIDE
        ========================================== --}}

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

                    <span class="text-white">
                        Mid
                    </span>

                    <span class="text-[#C4B5FD]">
                        Point
                    </span>

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
                    Choose a new secure password.
                </h1>


                <p
                    class="max-w-[410px]
                           text-[#C8DAD2]"
                >
                    Your reset link is checked by MidPoint
                    before any password is changed. After a
                    successful reset, the link cannot be used
                    again.
                </p>

            </div>


            <div
                class="mp-small
                       mt-auto
                       text-[#9DBBAF]"
            >
                Use a password that you do not reuse on
                another website.
            </div>

        </section>



        {{-- =========================================
            RESET PASSWORD
        ========================================== --}}

        <section class="mp-auth-form">

            <div class="mp-card mp-auth-card">


                <div
                    class="mb-5
                           grid h-12 w-12
                           place-items-center
                           rounded-[14px]
                           bg-[#F0ECFF]
                           text-[18px]
                           text-[#6941C6]"
                >

                    <i class="fa-solid fa-lock"></i>

                </div>


                <h2
                    class="mb-1
                           font-['Bricolage_Grotesque']
                           text-[22px]
                           font-bold"
                >
                    Reset password
                </h2>


                <p
                    class="mp-small
                           mp-muted
                           mb-5"
                >
                    Enter and confirm the new password for
                    your MidPoint account.
                </p>



                {{-- =====================================
                    ERRORS
                ====================================== --}}

                @if($errors->any())

                    <div
                        class="mb-5
                               rounded-xl
                               border border-red-200
                               bg-red-50
                               px-4 py-3
                               text-[13px]
                               leading-[1.55]
                               text-red-600"
                    >

                        <div
                            class="flex
                                   items-start
                                   gap-2"
                        >

                            <i
                                class="fa-solid
                                       fa-circle-exclamation
                                       mt-[3px]"
                            ></i>


                            <span>

                                {{ $errors->first() }}

                            </span>

                        </div>

                    </div>

                @endif



                <form
                    method="POST"
                    action="{{
                        route(
                            'password.update'
                        )
                    }}"
                    autocomplete="on"
                >

                    @csrf


                    {{-- RESET TOKEN --}}
                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >



                    {{-- EMAIL --}}
                    <div class="mp-field">

                        <label for="email">

                            Email address

                        </label>


                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{
                                old(
                                    'email',
                                    $email
                                )
                            }}"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                            readonly
                            class="bg-[#F7F9F8]
                                   text-[#66736C]"
                        >

                    </div>



                    {{-- NEW PASSWORD --}}
                    <div class="mp-field">

                        <label for="password">

                            New password

                        </label>


                        <div class="relative">

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter your new password"
                                autocomplete="new-password"
                                required
                                class="pr-12"
                            >


                            <button
                                type="button"
                                class="password-toggle
                                       absolute
                                       right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[#78857E]
                                       hover:text-[#0B3D2E]"
                                data-target="password"
                                aria-label="Show password"
                            >

                                <i
                                    class="fa-regular
                                           fa-eye"
                                ></i>

                            </button>

                        </div>

                    </div>



                    {{-- CONFIRM PASSWORD --}}
                    <div class="mp-field">

                        <label
                            for="password_confirmation"
                        >

                            Confirm new password

                        </label>


                        <div class="relative">

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your new password"
                                autocomplete="new-password"
                                required
                                class="pr-12"
                            >


                            <button
                                type="button"
                                class="password-toggle
                                       absolute
                                       right-3
                                       top-1/2
                                       -translate-y-1/2
                                       text-[#78857E]
                                       hover:text-[#0B3D2E]"
                                data-target="password_confirmation"
                                aria-label="Show password confirmation"
                            >

                                <i
                                    class="fa-regular
                                           fa-eye"
                                ></i>

                            </button>

                        </div>

                    </div>



                    {{-- PASSWORD REQUIREMENTS --}}
                    <div
                        class="mb-5
                               rounded-xl
                               bg-[#F7F9F8]
                               px-4 py-3
                               text-[11px]
                               leading-[1.7]
                               text-[#66736C]"
                    >

                        <strong
                            class="mb-1
                                   block
                                   text-[#26342D]"
                        >
                            Password requirements
                        </strong>


                        At least 8 characters and must contain
                        letters and numbers.

                    </div>



                    <button
                        type="submit"
                        class="mp-btn
                               mp-btn-primary
                               mp-btn-lg
                               w-full"
                    >

                        <i
                            class="fa-solid
                                   fa-shield-halved"
                        ></i>

                        Reset password

                    </button>

                </form>



                <div
                    class="my-5
                           border-t
                           border-[#E4EAE6]"
                ></div>


                <a
                    href="{{
                        route(
                            'password.request'
                        )
                    }}"
                    class="flex
                           items-center
                           justify-center
                           gap-2
                           text-[13px]
                           font-semibold
                           text-[#0B3D2E]
                           hover:text-[#12B76A]"
                >

                    <i
                        class="fa-solid
                               fa-rotate-right"
                    ></i>

                    Request a new reset link

                </a>

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
        | Show / Hide Password
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.password-toggle'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const input =
                                document.getElementById(
                                    button.dataset.target
                                );


                            if (!input) {
                                return;
                            }


                            const showing =
                                input.type ===
                                'text';


                            input.type =
                                showing
                                    ? 'password'
                                    : 'text';


                            button.innerHTML =
                                showing

                                    ? '<i class="fa-regular fa-eye"></i>'

                                    : '<i class="fa-regular fa-eye-slash"></i>';


                            button.setAttribute(
                                'aria-label',

                                showing
                                    ? 'Show password'
                                    : 'Hide password'
                            );

                        }
                    );
                }
            );

    }
);

</script>

@endpush