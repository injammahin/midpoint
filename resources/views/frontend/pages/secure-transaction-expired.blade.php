@extends('frontend.layouts.app')


@section('title', 'Transaction Link Expired | MidPoint')


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
            <i class="fa-regular fa-clock"></i>
        </div>


        <h1 class="text-[23px] font-extrabold">
            Transaction link expired
        </h1>


        <p class="mt-3 text-[12px] leading-6 text-[#69766F]">

            {{ $transaction->reference }}
            has expired before payment was completed.

            Contact the seller and ask them to create a new secure
            MidPoint transaction.

        </p>

    </div>

</section>

@endsection