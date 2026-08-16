@extends('frontend.layouts.app')


@section('title', 'Opening secure payment | Midpoint')


@section('hide_footer', true)


@section('content')

<div class="min-h-[70vh] flex items-center justify-center px-4 py-16">

    <div
        class="
            w-full
            max-w-md
            rounded-2xl
            border
            border-[#DDE6E1]
            bg-white
            p-8
            text-center
            shadow-sm
        "
    >

        <div
            class="
                mx-auto
                flex
                h-14
                w-14
                items-center
                justify-center
                rounded-full
                bg-[#EAF8F1]
                text-[#08B968]
            "
        >

            <i class="fa-solid fa-shield-halved text-xl"></i>

        </div>


        <h1 class="mt-5 text-xl font-bold text-[#101915]">

            Opening secure payment

        </h1>


        <p class="mt-2 text-sm leading-6 text-[#65736C]">

            Your order has been created.
            We are taking you to Paystack to complete
            the secured payment.

        </p>


        <form
            id="marketplacePaymentForm"
            method="POST"
            action="{{ route('secure-transactions.paystack.initialize', $transaction) }}"
            class="mt-6"
        >

            @csrf


            <button
                type="submit"
                class="
                    w-full
                    rounded-xl
                    bg-[#10B96B]
                    px-5
                    py-3
                    text-sm
                    font-bold
                    text-white
                    hover:bg-[#0EA660]
                "
            >

                Continue to secure payment

            </button>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>

window.addEventListener(
    'load',
    function () {

        const form =
            document.getElementById(
                'marketplacePaymentForm'
            );


        if (
            form
        ) {

            setTimeout(
                function () {

                    form.submit();

                },
                250
            );

        }

    }
);

</script>

@endpush