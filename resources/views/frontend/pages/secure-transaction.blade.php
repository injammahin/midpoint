@extends('frontend.layouts.app')


@section('title', 'Secure Transaction | MidPoint')


@section('content')


@php

    $seller =
        $transaction->seller;


    $application =
        optional(
            $sellerPlan
        )->application;


    $profile =
        $seller
            ->sellerBusinessProfile;


    $businessName =
        optional(
            $application
        )->business_name

        ?:

        $seller->name;


    $images =
        is_array(
            $transaction->images
        )
            ? $transaction->images
            : [];

@endphp



<section class="st-page">

    <div class="st-wrap">


        <div class="st-security">

            <i class="fa-solid fa-lock"></i>

            Secure MidPoint transaction

        </div>



        <div class="st-layout">


            {{-- =====================================================
                TRANSACTION
            ====================================================== --}}

            <div class="st-card">


                <div class="st-seller">

                    <div class="st-seller-avatar">

                        @if ($profile && $profile->profile_image_url)

                            <img
                                src="{{ $profile->profile_image_url }}"
                                alt="{{ $businessName }}"
                            >

                        @else

                            {{
                                strtoupper(
                                    substr(
                                        $businessName,
                                        0,
                                        1
                                    )
                                )
                            }}

                        @endif

                    </div>


                    <div>

                        <strong>
                            {{ $businessName }}
                        </strong>


                        <span>

                            <i class="fa-solid fa-circle-check"></i>

                            MidPoint Verified Seller

                        </span>

                    </div>

                </div>



                <div class="st-reference">

                    {{ $transaction->reference }}

                </div>



                <h1>
                    {{ $transaction->title }}
                </h1>



                @if (count($images))

                    <div class="st-images">

                        @foreach ($images as $image)

                            <img
                                src="{{ asset('storage/' . $image) }}"
                                alt="{{ $transaction->title }}"
                            >

                        @endforeach

                    </div>

                @endif



                <div class="st-description">

                    {{ $transaction->description }}

                </div>



                @if ($transaction->delivery_note)

                    <div class="st-delivery">

                        <i class="fa-solid fa-truck"></i>


                        <div>

                            <strong>
                                Delivery arrangement
                            </strong>


                            <p>
                                {{ $transaction->delivery_note }}
                            </p>

                        </div>

                    </div>

                @endif

            </div>



            {{-- =====================================================
                PAYMENT SUMMARY
            ====================================================== --}}

            <aside class="st-payment">

                <div class="st-payment-card">

                    <span class="st-payment-status">

                        <i class="fa-regular fa-clock"></i>

                        Awaiting payment

                    </span>


                    <h2>
                        Payment summary
                    </h2>



                    <div class="st-lines">

                        <div>

                            <span>
                                Item price
                            </span>

                            <strong>
                                ₦{{ number_format((float) $transaction->unit_price, 2) }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Quantity
                            </span>

                            <strong>
                                {{ $transaction->quantity }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                ₦{{ number_format((float) $transaction->subtotal, 2) }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Delivery
                            </span>

                            <strong>
                                ₦{{ number_format((float) $transaction->delivery_fee, 2) }}
                            </strong>

                        </div>

                    </div>



                    <div class="st-total">

                        <span>
                            Total to pay
                        </span>


                        <strong>
                            ₦{{ number_format((float) $transaction->total_amount, 2) }}
                        </strong>

                    </div>



                    <button
                        type="button"
                        class="st-pay"
                        disabled
                    >

                        <i class="fa-solid fa-shield-halved"></i>

                        Pay securely with Paystack

                    </button>


                    <div class="st-next">

                        <i class="fa-solid fa-circle-info"></i>

                        Paystack checkout will be connected in the next development step.

                    </div>



                    <div class="st-protection">

                        <strong>
                            MidPoint protection
                        </strong>


                        <span>

                            <i class="fa-solid fa-check"></i>

                            Logged-in verified buyer

                        </span>


                        <span>

                            <i class="fa-solid fa-check"></i>

                            Transaction amount locked server-side

                        </span>


                        <span>

                            <i class="fa-solid fa-check"></i>

                            {{ $transaction->inspection_hours }} hour inspection period

                        </span>

                    </div>

                </div>

            </aside>

        </div>

    </div>

</section>



@push('styles')

<style>

.st-page {
    min-height: calc(100vh - 70px);
    padding: 48px 18px 70px;
    background: #F6F9F7;
}

.st-wrap {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
}

.st-security {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    color: #087443;
    font-size: 9px;
    font-weight: 800;
}

.st-layout {
    display: grid;
    grid-template-columns: minmax(0,1fr) 325px;
    align-items: start;
    gap: 16px;
}

.st-card,
.st-payment-card {
    border: 1px solid #DCE5E0;
    border-radius: 18px;
    background: #FFFFFF;
    box-shadow: 0 12px 40px -32px rgba(11,61,46,.35);
}

.st-card {
    padding: 25px;
}

.st-seller {
    display: flex;
    align-items: center;
    gap: 10px;
}

.st-seller-avatar {
    width: 46px;
    height: 46px;
    overflow: hidden;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #0B3D2E;
    color: #FFFFFF;
    font-weight: 800;
}

.st-seller-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.st-seller strong,
.st-seller span {
    display: block;
}

.st-seller strong {
    color: #17251F;
    font-size: 11px;
}

.st-seller span {
    margin-top: 2px;
    color: #087443;
    font-size: 8px;
}

.st-reference {
    margin-top: 18px;
    color: #89948F;
    font-size: 8px;
    font-weight: 700;
}

.st-card h1 {
    margin: 5px 0 16px;
    font-family: 'Bricolage Grotesque',sans-serif;
    font-size: 25px;
    font-weight: 800;
}

.st-images {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 7px;
    margin-bottom: 18px;
}

.st-images img {
    width: 100%;
    height: 120px;
    padding: 5px;
    border: 1px solid #E0E7E3;
    border-radius: 11px;
    background: #F8FAF9;
    object-fit: contain;
}

.st-description {
    color: #536159;
    font-size: 10px;
    line-height: 1.75;
    white-space: pre-line;
}

.st-delivery {
    display: flex;
    gap: 9px;
    margin-top: 18px;
    padding: 12px;
    border-radius: 11px;
    background: #F7F9F8;
}

.st-delivery > i {
    color: #12B76A;
}

.st-delivery strong {
    font-size: 9px;
}

.st-delivery p {
    margin: 3px 0 0;
    color: #69766F;
    font-size: 8px;
    line-height: 1.55;
}

.st-payment {
    position: sticky;
    top: 90px;
}

.st-payment-card {
    padding: 21px;
}

.st-payment-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 8px;
    border-radius: 999px;
    background: #FFF7E8;
    color: #B54708;
    font-size: 7px;
    font-weight: 800;
}

.st-payment h2 {
    margin: 13px 0 12px;
    font-size: 14px;
}

.st-lines {
    padding: 8px 0;
    border-top: 1px solid #E8ECEA;
    border-bottom: 1px solid #E8ECEA;
}

.st-lines > div {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    color: #69766F;
    font-size: 9px;
}

.st-lines strong {
    color: #344139;
}

.st-total {
    padding: 15px 0;
}

.st-total span,
.st-total strong {
    display: block;
}

.st-total span {
    color: #637069;
    font-size: 9px;
}

.st-total strong {
    margin-top: 3px;
    color: #0B3D2E;
    font-size: 25px;
}

.st-pay {
    width: 100%;
    min-height: 44px;
    border: 0;
    border-radius: 10px;
    background: #A9D9C4;
    color: #FFFFFF;
    font-size: 10px;
    font-weight: 800;
    cursor: not-allowed;
}

.st-next {
    margin-top: 8px;
    color: #8A9690;
    font-size: 7px;
    line-height: 1.5;
    text-align: center;
}

.st-protection {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin-top: 17px;
    padding: 12px;
    border-radius: 10px;
    background: #F2FCF6;
}

.st-protection strong {
    color: #087443;
    font-size: 9px;
}

.st-protection span {
    color: #62756B;
    font-size: 8px;
}

.st-protection i {
    margin-right: 4px;
    color: #12B76A;
}

@media(max-width:800px) {

    .st-layout {
        grid-template-columns: 1fr;
    }

    .st-payment {
        position: static;
    }
}

</style>

@endpush


@endsection