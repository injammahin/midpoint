@extends('frontend.layouts.app')


@section('title', 'Start secure transaction | Midpoint')


@section('hide_footer', true)


@section('content')

@php

    $mainImage =
        $sellerProduct
            ->main_image;


    $stock =
        (int)
        $sellerProduct
            ->stock;


    $oldQuantity =
        max(
            1,
            (int)
            old(
                'quantity',
                1
            )
        );


    $oldDeliveryFee =
        old(
            'delivery_fee',
            ''
        );

@endphp


<section class="market-checkout-page">

    <div class="market-checkout-shell">


        <a
            href="{{ route('featured-businesses.show', $seller) }}"
            class="market-back-link"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to {{ $businessName }}

        </a>


        <div class="market-page-heading">

            <h1>
                Start secure transaction
            </h1>


            <p>

                You're buying from a verified seller.
                Your payment is held by Midpoint until you inspect
                and accept the item.

            </p>

        </div>


        @if(session('error'))

            <div class="market-alert market-alert-error">

                {{ session('error') }}

            </div>

        @endif


        @if($errors->any())

            <div class="market-alert market-alert-error">

                <strong>
                    Please check the form:
                </strong>


                <ul>

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('buyer.products.checkout.store', $sellerProduct) }}"
            id="marketCheckoutForm"
        >

            @csrf


            <div class="market-checkout-grid">


                <div class="market-left-column">


                    <div class="market-card market-order-card">


                        <div class="market-product-summary">


                            <div class="market-product-image">

                                @if($mainImage)

                                    <img
                                        src="{{ asset('storage/' . $mainImage) }}"
                                        alt="{{ $sellerProduct->name }}"
                                    >

                                @else

                                    <i class="fa-solid fa-bag-shopping"></i>

                                @endif

                            </div>


                            <div class="market-product-copy">


                                <div class="market-title-line">

                                    <h2>
                                        {{ $sellerProduct->name }}
                                    </h2>


                                    <span class="market-verified-badge">

                                        <i class="fa-solid fa-check"></i>

                                        Verified seller

                                    </span>

                                </div>


                                <p class="market-seller-line">

                                    Sold by {{ $businessName }}

                                    @if($businessLocation)

                                        <span>
                                            • {{ $businessLocation }}
                                        </span>

                                    @endif

                                </p>


                                <p class="market-description">

                                    {{
                                        \Illuminate\Support\Str::limit(
                                            trim(
                                                strip_tags(
                                                    $sellerProduct->description
                                                )
                                            ),
                                            150
                                        )
                                    }}

                                </p>


                                <div class="market-price-meta">

                                    <span>

                                        Price:

                                        <strong>
                                            ₦{{ number_format((float) $sellerProduct->price, 0) }}
                                        </strong>

                                    </span>


                                    <span>

                                        Delivery:

                                        <strong>
                                            Arranged by seller
                                        </strong>

                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="market-divider"></div>


                        <div class="market-form-row-two">


                            <div class="market-field">

                                <label for="quantity">

                                    Quantity

                                </label>


                                <input
                                    id="quantity"
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="{{ max(1, min(100, $stock)) }}"
                                    value="{{ old('quantity', 1) }}"
                                    {{ $stock <= 0 ? 'disabled' : '' }}
                                    required
                                >


                                <small>

                                    {{ number_format($stock) }}
                                    unit(s) currently in stock.

                                </small>

                            </div>


                            <div class="market-field">

                                <label for="delivery_fee">

                                    Delivery price as discussed with seller (₦)

                                    <span class="market-required">
                                        *
                                    </span>

                                </label>


                                <input
                                    id="delivery_fee"
                                    type="number"
                                    name="delivery_fee"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('delivery_fee') }}"
                                    placeholder="0"
                                    {{ $stock <= 0 ? 'disabled' : '' }}
                                    required
                                >


                                <small>

                                    Agree this with the seller first.
                                    If delivery isn't needed, enter 0.
                                    This field can't be left empty.

                                </small>

                            </div>

                        </div>


                        <div class="market-field market-full-field">

                            <label for="delivery_address">

                                Delivery address

                                <span class="market-required">
                                    *
                                </span>

                            </label>


                            <input
                                id="delivery_address"
                                type="text"
                                name="delivery_address"
                                value="{{ old('delivery_address') }}"
                                placeholder="e.g. 12 Awolowo Avenue, Bodija, Ibadan"
                                maxlength="2000"
                                {{ $stock <= 0 ? 'disabled' : '' }}
                                required
                            >

                        </div>


                        <div class="market-field market-full-field">

                            <label for="buyer_phone">

                                Phone number for the rider

                                <span class="market-required">
                                    *
                                </span>

                            </label>


                            <input
                                id="buyer_phone"
                                type="text"
                                name="buyer_phone"
                                value="{{ old('buyer_phone', $buyer->phone ?? '') }}"
                                placeholder="0803 xxx xxxx"
                                maxlength="40"
                                {{ $stock <= 0 ? 'disabled' : '' }}
                                required
                            >

                        </div>


                    </div>


                    {{-- ======================================================
                        WHAT HAPPENS AFTER PAYMENT
                    ======================================================= --}}

                    <div class="market-card market-steps-card">

                        <h3>
                            What happens after you pay
                        </h3>


                        <div class="market-step-list">


                            <div class="market-step-item">

                                <span class="market-step-number">
                                    1
                                </span>


                                <div>

                                    <strong>
                                        Midpoint holds your money
                                    </strong>

                                    <p>
                                        The seller never sees your payment until you're satisfied.
                                    </p>

                                </div>

                            </div>


                            <div class="market-step-item">

                                <span class="market-step-number">
                                    2
                                </span>


                                <div>

                                    <strong>
                                        Seller dispatches
                                    </strong>

                                    <p>
                                        You track the delivery from your buyer dashboard.
                                    </p>

                                </div>

                            </div>


                            <div class="market-step-item">

                                <span class="market-step-number">
                                    3
                                </span>


                                <div>

                                    <strong>

                                        {{
                                            (int)
                                            config(
                                                'secure_transactions.inspection_hours',
                                                8
                                            )
                                        }}-hour inspection

                                    </strong>


                                    <p>

                                        Accept to release funds,
                                        or open a dispute if something's wrong.

                                    </p>

                                </div>

                            </div>


                        </div>

                    </div>


                </div>


                {{-- ==========================================================
                    PAYMENT SUMMARY
                =========================================================== --}}

                <aside class="market-card market-payment-card">


                    <h3>
                        You pay
                    </h3>


                    <p class="market-payment-subtitle">
                        No Midpoint fees for buyers.
                    </p>


                    <div class="market-summary-line">

                        <span>
                            Product price
                        </span>


                        <strong id="marketProductSubtotal">

                            ₦{{
                                number_format(
                                    (float)
                                    $sellerProduct->price
                                    *
                                    $oldQuantity,
                                    0
                                )
                            }}

                        </strong>

                    </div>


                    <div class="market-summary-line">

                        <span>
                            Delivery (as discussed with seller)
                        </span>


                        <strong id="marketDeliveryDisplay">

                            ₦{{
                                is_numeric(
                                    $oldDeliveryFee
                                )
                                    ? number_format(
                                        (float)
                                        $oldDeliveryFee,
                                        0
                                    )
                                    : '0'
                            }}

                        </strong>

                    </div>


                    <div class="market-total-box">

                        <span>
                            Total
                        </span>


                        <strong id="marketTotalDisplay">

                            ₦{{
                                number_format(

                                    (
                                        (float)
                                        $sellerProduct->price
                                        *
                                        $oldQuantity
                                    )

                                    +

                                    (
                                        is_numeric(
                                            $oldDeliveryFee
                                        )
                                            ? (float)
                                            $oldDeliveryFee
                                            : 0
                                    ),

                                    0
                                )
                            }}

                        </strong>

                    </div>


                    @if($stock > 0)

                        <button
                            type="submit"
                            class="market-pay-button"
                            id="marketPayButton"
                        >

                            <i class="fa-solid fa-shield-halved"></i>

                            Pay securely —

                            <span id="marketButtonTotal">

                                ₦{{
                                    number_format(

                                        (
                                            (float)
                                            $sellerProduct->price
                                            *
                                            $oldQuantity
                                        )

                                        +

                                        (
                                            is_numeric(
                                                $oldDeliveryFee
                                            )
                                                ? (float)
                                                $oldDeliveryFee
                                                : 0
                                        ),

                                        0
                                    )
                                }}

                            </span>

                        </button>

                    @else

                        <button
                            type="button"
                            class="market-pay-button market-pay-button-disabled"
                            disabled
                        >

                            <i class="fa-solid fa-box-open"></i>

                            Out of stock

                        </button>

                    @endif


                    <p class="market-payment-note">

                        Both the product price and the delivery amount are held
                        in escrow until you accept the item or the

                        {{
                            (int)
                            config(
                                'secure_transactions.inspection_hours',
                                8
                            )
                        }}-hour inspection ends.

                    </p>


                    <p class="market-payment-note">

                        By continuing, you agree to Midpoint's
                        secure transaction process.

                        <a href="{{ route('escrow-policy') }}">
                            See our Escrow Policy.
                        </a>

                    </p>


                </aside>


            </div>

        </form>


    </div>

</section>

@endsection


@push('styles')

<style>

    .market-checkout-page {
        padding: 44px 20px 70px;
        background: #F6F9F7;
    }


    .market-checkout-shell {
        width: 100%;
        max-width: 1040px;
        margin: 0 auto;
    }


    .market-back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
        color: #08B968;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }


    .market-page-heading h1 {
        margin: 0;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 25px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #0D1712;
    }


    .market-page-heading p {
        margin: 5px 0 23px;
        color: #66756E;
        font-size: 13px;
        line-height: 1.6;
    }


    .market-checkout-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.45fr)
            minmax(300px, .95fr);
        gap: 22px;
        align-items: start;
    }


    .market-left-column {
        display: grid;
        gap: 22px;
    }


    .market-card {
        border: 1px solid #DDE6E1;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow:
            0 9px 24px
            rgba(17, 52, 39, 0.045);
    }


    .market-order-card {
        padding: 24px;
    }


    .market-product-summary {
        display: grid;
        grid-template-columns:
            92px
            minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }


    .market-product-image {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 92px;
        height: 92px;
        overflow: hidden;
        border-radius: 17px;
        background: #EAF8F1;
        color: #6953DB;
        font-size: 28px;
    }


    .market-product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }


    .market-title-line {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }


    .market-title-line h2 {
        margin: 0;
        color: #0D1712;
        font-size: 16px;
        font-weight: 800;
    }


    .market-verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        background: #E9FBF1;
        padding: 5px 8px;
        color: #087C4B;
        font-size: 10px;
        font-weight: 700;
    }


    .market-seller-line,
    .market-description,
    .market-price-meta {
        margin: 6px 0 0;
        color: #5D6B64;
        font-size: 12px;
        line-height: 1.55;
    }


    .market-description {
        color: #25332C;
    }


    .market-price-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        color: #101A15;
    }


    .market-divider {
        height: 1px;
        margin: 21px 0 18px;
        background: #E6ECE8;
    }


    .market-form-row-two {
        display: grid;
        grid-template-columns:
            1fr
            1fr;
        gap: 18px;
    }


    .market-field {
        display: flex;
        flex-direction: column;
    }


    .market-full-field {
        margin-top: 18px;
    }


    .market-field label {
        margin-bottom: 7px;
        color: #111B16;
        font-size: 12px;
        font-weight: 800;
    }


    .market-required {
        color: #E53E3E;
    }


    .market-field input {
        width: 100%;
        min-height: 44px;
        border: 1px solid #D8E2DC;
        border-radius: 11px;
        background: #FFFFFF;
        padding: 10px 12px;
        color: #111B16;
        font-size: 13px;
        outline: none;
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }


    .market-field input:focus {
        border-color: #17B96C;
        box-shadow:
            0 0 0 3px
            rgba(23, 185, 108, .10);
    }


    .market-field small {
        margin-top: 6px;
        color: #7C8982;
        font-size: 10px;
        line-height: 1.45;
    }


    .market-payment-card {
        position: sticky;
        top: 88px;
        padding: 24px;
    }


    .market-payment-card h3,
    .market-steps-card h3 {
        margin: 0;
        color: #111B16;
        font-size: 15px;
        font-weight: 800;
    }


    .market-payment-subtitle {
        margin: 4px 0 21px;
        color: #7A8780;
        font-size: 11px;
    }


    .market-summary-line {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        margin-top: 18px;
        color: #67736D;
        font-size: 12px;
    }


    .market-summary-line strong {
        color: #111B16;
        white-space: nowrap;
    }


    .market-total-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 22px;
        border-radius: 12px;
        background: #7657F7;
        padding: 17px;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
    }


    .market-total-box strong {
        font-size: 19px;
        font-weight: 800;
    }


    .market-pay-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        margin-top: 14px;
        border: 0;
        border-radius: 12px;
        background: #10B96B;
        padding: 13px 15px;
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
    }


    .market-pay-button:hover {
        background: #0DA65F;
    }


    .market-pay-button-disabled,
    .market-pay-button-disabled:hover {
        background: #9AA7A0;
        cursor: not-allowed;
    }


    .market-payment-note {
        margin: 14px 0 0;
        color: #737F79;
        font-size: 10px;
        line-height: 1.65;
    }


    .market-payment-note a {
        color: #7657F7;
        font-weight: 700;
        text-decoration: none;
    }


    .market-steps-card {
        padding: 23px 24px;
    }


    .market-step-list {
        margin-top: 14px;
    }


    .market-step-item {
        position: relative;
        display: grid;
        grid-template-columns:
            26px
            1fr;
        gap: 10px;
        padding-bottom: 20px;
    }


    .market-step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 12px;
        top: 24px;
        width: 1px;
        height: calc(100% - 15px);
        background: #D6E5DD;
    }


    .market-step-item:last-child {
        padding-bottom: 0;
    }


    .market-step-number {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0DB96A;
        color: #FFFFFF;
        font-size: 10px;
        font-weight: 800;
    }


    .market-step-item strong {
        display: block;
        color: #111B16;
        font-size: 12px;
    }


    .market-step-item p {
        margin: 4px 0 0;
        color: #6F7D75;
        font-size: 10px;
        line-height: 1.5;
    }


    .market-alert {
        margin-bottom: 18px;
        border-radius: 12px;
        padding: 13px 15px;
        font-size: 12px;
        line-height: 1.5;
    }


    .market-alert-error {
        border: 1px solid #F5C2C7;
        background: #FFF1F2;
        color: #9B1C1C;
    }


    .market-alert ul {
        margin: 6px 0 0 17px;
    }


    @media (max-width: 860px) {

        .market-checkout-grid {
            grid-template-columns: 1fr;
        }


        .market-payment-card {
            position: static;
        }

    }


    @media (max-width: 620px) {

        .market-checkout-page {
            padding:
                28px
                14px
                50px;
        }


        .market-order-card,
        .market-payment-card,
        .market-steps-card {
            padding: 18px;
        }


        .market-product-summary {
            grid-template-columns:
                72px
                minmax(0, 1fr);
        }


        .market-product-image {
            width: 72px;
            height: 72px;
        }


        .market-form-row-two {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const unitPrice =
            {{ json_encode((float) $sellerProduct->price) }};


        const maxStock =
            {{ json_encode(max(0, min(100, $stock))) }};


        const quantityInput =
            document.getElementById(
                'quantity'
            );


        const deliveryInput =
            document.getElementById(
                'delivery_fee'
            );


        const subtotalDisplay =
            document.getElementById(
                'marketProductSubtotal'
            );


        const deliveryDisplay =
            document.getElementById(
                'marketDeliveryDisplay'
            );


        const totalDisplay =
            document.getElementById(
                'marketTotalDisplay'
            );


        const buttonTotal =
            document.getElementById(
                'marketButtonTotal'
            );


        const formatNaira =
            function (
                amount
            ) {

                return '₦'
                    +
                    new Intl.NumberFormat(
                        'en-NG',
                        {
                            maximumFractionDigits:
                                2
                        }
                    )
                    .format(
                        amount
                        ||
                        0
                    );
            };


        const refreshTotal =
            function () {

                if (
                    !quantityInput
                    ||
                    !deliveryInput
                ) {
                    return;
                }


                let quantity =
                    parseInt(
                        quantityInput.value
                        ||
                        '1',
                        10
                    );


                if (
                    !Number.isFinite(
                        quantity
                    )
                    ||
                    quantity < 1
                ) {

                    quantity =
                        1;

                }


                if (
                    maxStock > 0
                    &&
                    quantity > maxStock
                ) {

                    quantity =
                        maxStock;


                    quantityInput.value =
                        maxStock;

                }


                let delivery =
                    parseFloat(
                        deliveryInput.value
                        ||
                        '0'
                    );


                if (
                    !Number.isFinite(
                        delivery
                    )
                    ||
                    delivery < 0
                ) {

                    delivery =
                        0;

                }


                const subtotal =
                    unitPrice
                    *
                    quantity;


                const total =
                    subtotal
                    +
                    delivery;


                if (
                    subtotalDisplay
                ) {

                    subtotalDisplay.textContent =
                        formatNaira(
                            subtotal
                        );

                }


                if (
                    deliveryDisplay
                ) {

                    deliveryDisplay.textContent =
                        formatNaira(
                            delivery
                        );

                }


                if (
                    totalDisplay
                ) {

                    totalDisplay.textContent =
                        formatNaira(
                            total
                        );

                }


                if (
                    buttonTotal
                ) {

                    buttonTotal.textContent =
                        formatNaira(
                            total
                        );

                }

            };


        if (
            quantityInput
        ) {

            quantityInput
                .addEventListener(
                    'input',
                    refreshTotal
                );

        }


        if (
            deliveryInput
        ) {

            deliveryInput
                .addEventListener(
                    'input',
                    refreshTotal
                );

        }


        refreshTotal();

    }
);

</script>

@endpush