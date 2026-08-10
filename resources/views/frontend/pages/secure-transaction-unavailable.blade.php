@extends('frontend.layouts.app')


@section('title', 'Transaction Temporarily Unavailable | MidPoint')


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
                   bg-red-50
                   text-red-600"
        >
            <i class="fa-solid fa-shield-halved"></i>
        </div>


        <h1 class="text-[23px] font-extrabold">
            Transaction unavailable
        </h1>


        <p class="mt-3 text-[12px] leading-6 text-[#69766F]">

            The seller currently does not have an active MidPoint
            seller package, so this transaction cannot proceed to payment.

        </p>

    </div>

</section>

@endsection