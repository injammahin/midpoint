@extends('frontend.layouts.app')

@section('title', 'Forgot Password | Midpoint')

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
                    Get back to your account safely.
                </h1>


                <p
                    class="max-w-[400px]
                           text-[#C8DAD2]"
                >
                    Enter the email linked to your Midpoint
                    account. We'll send you a secure,
                    time-limited link to choose a new password.
                </p>

            </div>


            <div
                class="mp-small
                       mt-auto
                       text-[#9DBBAF]"
            >
                Your transactions and funds remain protected
                while you recover access.
            </div>

        </section>



        {{-- =========================================
            FORGOT PASSWORD FORM
        ========================================== --}}

        <section class="mp-auth-form">

            <div class="mp-card mp-auth-card">


                <div
                    class="mb-5
                           grid h-12 w-12
                           place-items-center
                           rounded-[14px]
                           bg-[#E8F7EF]
                           text-[18px]
                           text-[#0E7A4C]"
                >

                    <i class="fa-solid fa-key"></i>

                </div>


                <h2
                    class="mb-1
                           font-['Bricolage_Grotesque']
                           text-[22px]
                           font-bold"
                >
                    Forgot your password?
                </h2>


                <p
                    class="mp-small
                           mp-muted
                           mb-5"
                >
                    No problem. Enter your account email and
                    we'll send you a reset link.
                </p>



                {{-- =====================================
                    SUCCESS
                ====================================== --}}

                @if(session('status'))

                    <div
                        class="mb-5
                               rounded-xl
                               border border-[#ABEFC6]
                               bg-[#ECFDF3]
                               px-4 py-3
                               text-[13px]
                               leading-[1.55]
                               text-[#067647]"
                    >

                        <div
                            class="flex
                                   items-start
                                   gap-2"
                        >

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



                {{-- =====================================
                    FORM
                ====================================== --}}

                <form
                    method="POST"
                    action="{{
                        route(
                            'password.email'
                        )
                    }}"
                    autocomplete="on"
                >

                    @csrf


                    <div class="mp-field">

                        <label for="email">

                            Email address

                        </label>


                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                            autofocus
                        >

                    </div>


                    <button
                        type="submit"
                        class="mp-btn
                               mp-btn-primary
                               mp-btn-lg
                               w-full"
                    >

                        <i
                            class="fa-regular
                                   fa-paper-plane"
                        ></i>

                        Send reset link

                    </button>

                </form>



                <div
                    class="my-5
                           border-t
                           border-[#E4EAE6]"
                ></div>


                <a
                    href="{{ route('login') }}"
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
                               fa-arrow-left"
                    ></i>

                    Back to log in

                </a>



                {{-- =====================================
                    SECURITY NOTE
                ====================================== --}}

                <div
                    class="mt-5
                           rounded-xl
                           bg-[#F7F9F8]
                           px-4 py-3
                           text-[11px]
                           leading-[1.6]
                           text-[#718078]"
                >

                    <i
                        class="fa-solid
                               fa-shield-halved
                               mr-1
                               text-[#0E7A4C]"
                    ></i>

                    For privacy, Midpoint does not confirm
                    whether a specific email address has an
                    account.

                </div>

            </div>

        </section>

    </div>

</div>

@endsection