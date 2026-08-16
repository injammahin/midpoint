@extends('frontend.layouts.app')

@section('title', 'Verify Email | Midpoint')

@section('hide_footer', '1')


@section('content')

<div
    class="min-h-[calc(100vh-66px)]
           bg-[#F6F9F7]
           px-4 py-14"
>

    <div
        class="mx-auto
               max-w-[520px]
               rounded-[18px]
               border border-[#E4EAE6]
               bg-white
               p-7
               text-center
               shadow-[0_20px_50px_-35px_rgba(11,61,46,.35)]
               sm:p-9"
    >

        <div
            class="mx-auto
                   grid h-16 w-16
                   place-items-center
                   rounded-full
                   bg-[#E8F7EF]
                   text-[24px]
                   text-[#12B76A]"
        >
            <i class="fa-regular fa-envelope"></i>
        </div>


        <h1
            class="mt-5
                   font-['Bricolage_Grotesque']
                   text-[26px]
                   font-extrabold"
        >
            Check your email
        </h1>


        <p
            class="mx-auto
                   mt-3
                   max-w-[410px]
                   text-[14px]
                   leading-[1.7]
                   text-[#5A6660]"
        >
            We sent a verification link to
            <strong class="text-[#0B3D2E]">
                {{ $user->email }}
            </strong>.
        </p>


        <p
            class="mt-2
                   text-[13px]
                   text-[#83908A]"
        >
            The verification link is valid for
            <strong>5 minutes</strong>.
        </p>


        @if(session('status') === 'verification-link-sent')

            <div
                class="mt-5
                       rounded-xl
                       bg-[#E8F7EF]
                       px-4 py-3
                       text-[13px]
                       font-semibold
                       text-[#0E7A4C]"
            >
                <i class="fa-solid fa-circle-check mr-1"></i>

                A new verification email has been sent.
            </div>

        @endif


        @error('resend')

            <div
                class="mt-5
                       rounded-xl
                       bg-red-50
                       px-4 py-3
                       text-[13px]
                       text-red-600"
            >
                {{ $message }}
            </div>

        @enderror


        <div
            class="my-6
                   border-t
                   border-[#E4EAE6]"
        ></div>


        <p
            class="mb-3
                   text-[13px]
                   text-[#5A6660]"
        >
            Didn't receive it?
        </p>


        <form
            method="POST"
            action="{{ route('verification.send') }}"
        >

            @csrf


            <button
                type="submit"
                id="resendVerificationButton"
                class="mp-btn
                       mp-btn-primary
                       w-full
                       disabled:cursor-not-allowed
                       disabled:opacity-50"
                data-seconds="{{ $secondsRemaining }}"
                {{ $secondsRemaining > 0 ? 'disabled' : '' }}
            >

                <span id="resendVerificationText">

                    @if($secondsRemaining > 0)

                        Send again in
                        {{ $secondsRemaining }}s

                    @else

                        Send verification email again

                    @endif

                </span>

            </button>

        </form>


        <form
            method="POST"
            action="{{ route('logout') }}"
            class="mt-3"
        >

            @csrf

            <button
                type="submit"
                class="text-[13px]
                       font-semibold
                       text-[#5A6660]
                       hover:text-[#0B3D2E]"
            >
                Use another account
            </button>

        </form>

    </div>

</div>

@endsection



@push('scripts')

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const button =
            document.getElementById(
                'resendVerificationButton'
            );

        const text =
            document.getElementById(
                'resendVerificationText'
            );


        if (!button || !text) {
            return;
        }


        let seconds =
            parseInt(
                button.dataset.seconds || '0',
                10
            );


        if (seconds <= 0) {
            return;
        }


        const timer =
            setInterval(
                function () {

                    seconds--;


                    if (seconds <= 0) {

                        clearInterval(
                            timer
                        );


                        button.disabled =
                            false;


                        text.textContent =
                            'Send verification email again';


                        return;
                    }


                    text.textContent =
                        `Send again in ${seconds}s`;

                },
                1000
            );

    }
);
</script>

@endpush