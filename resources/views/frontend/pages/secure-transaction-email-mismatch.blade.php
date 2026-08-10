@extends('frontend.layouts.app')


@section('title', 'Transaction Access Restricted | MidPoint')


@section('content')

<section class="min-h-[70vh] bg-[#F6F9F7] px-4 py-16">

    <div
        class="mx-auto max-w-[520px]
               rounded-[18px]
               border border-[#DCE5E0]
               bg-white
               p-8
               text-center"
    >

        <div
            class="mx-auto mb-4 grid
                   h-14 w-14
                   place-items-center
                   rounded-2xl
                   bg-[#FFF7E8]
                   text-[#B54708]"
        >
            <i class="fa-solid fa-user-lock"></i>
        </div>


        <h1
            class="font-['Bricolage_Grotesque']
                   text-[23px]
                   font-extrabold"
        >
            This transaction belongs to another buyer
        </h1>


        <p class="mt-3 text-[12px] leading-7 text-[#69766F]">

            This secure transaction was created for

            <strong>
                {{ $maskedEmail }}
            </strong>.

            Log in using the MidPoint account with that email address.

        </p>


        <form
            method="POST"
            action="{{ route('logout') }}"
            class="mt-6"
        >

            @csrf


            <button
                type="submit"
                class="rounded-xl
                       bg-[#0B3D2E]
                       px-5 py-3
                       text-[11px]
                       font-bold
                       text-white"
            >
                Log out and use another account
            </button>

        </form>

    </div>

</section>

@endsection