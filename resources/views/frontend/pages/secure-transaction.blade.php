@extends('frontend.layouts.app')


@section('title', 'Secure Transaction | MidPoint')


@section('content')


@php

    /*
    |--------------------------------------------------------------------------
    | Seller
    |--------------------------------------------------------------------------
    */

    $seller =
        $transaction->seller;


    /*
    |--------------------------------------------------------------------------
    | Optional Seller Package / Application
    |--------------------------------------------------------------------------
    |
    | A seller package is NOT required for a custom transaction.
    |
    */

    $application =
        optional(
            $sellerPlan
        )->application;


    /*
    |--------------------------------------------------------------------------
    | Business Profile
    |--------------------------------------------------------------------------
    */

    $profile =
        $seller
            ->sellerBusinessProfile;


    /*
    |--------------------------------------------------------------------------
    | Business / Seller Name
    |--------------------------------------------------------------------------
    */

    $businessName =
        optional(
            $application
        )->business_name

        ?:

        $seller->name;


    /*
    |--------------------------------------------------------------------------
    | Transaction Images
    |--------------------------------------------------------------------------
    */

    $images =
        is_array(
            $transaction->images
        )
            ? $transaction->images
            : [];


    /*
    |--------------------------------------------------------------------------
    | Verification Status
    |--------------------------------------------------------------------------
    |
    | Only sellers with an active seller package/application should
    | visually appear as a "Verified Seller".
    |
    | Custom transactions can still be created without a package.
    |
    */

    $isVerifiedSeller =
        !is_null(
            $sellerPlan
        )
        &&
        !is_null(
            $application
        );


    /*
    |--------------------------------------------------------------------------
    | Payment State
    |--------------------------------------------------------------------------
    */

    $isPaid =
        $transaction->payment_status
        ===
        \App\Models\SecureTransaction::PAYMENT_PAID;


    $isPending =
        $transaction->payment_status
        ===
        \App\Models\SecureTransaction::PAYMENT_PENDING;


    $isFailed =
        $transaction->payment_status
        ===
        \App\Models\SecureTransaction::PAYMENT_FAILED;

@endphp



<section class="st-page">

    <div class="st-wrap">


        {{-- =========================================================
            SECURITY LABEL
        ========================================================== --}}

        <div class="st-security">

            <i class="fa-solid fa-lock"></i>

            Secure MidPoint transaction

        </div>



        <div class="st-layout">


            {{-- =====================================================
                LEFT SIDE - TRANSACTION INFORMATION
            ====================================================== --}}

            <div class="st-card">


                {{-- =================================================
                    SELLER
                ================================================== --}}

                <div class="st-seller">

                    <div class="st-seller-avatar">

                        @if($profile && $profile->profile_image_url)

                            <img
                                src="{{ $profile->profile_image_url }}"
                                alt="{{ $businessName }}"
                            >

                        @else

                            <span>

                                {{
                                    strtoupper(
                                        substr(
                                            $businessName,
                                            0,
                                            1
                                        )
                                    )
                                }}

                            </span>

                        @endif

                    </div>



                    <div class="st-seller-info">

                        <strong>
                            {{ $businessName }}
                        </strong>


                        @if($isVerifiedSeller)

                            <span class="st-seller-verified">

                                <i class="fa-solid fa-circle-check"></i>

                                MidPoint Verified Seller

                            </span>

                        @else

                            <span class="st-seller-standard">

                                <i class="fa-solid fa-shield-halved"></i>

                                MidPoint Seller

                            </span>

                        @endif

                    </div>

                </div>



                {{-- =================================================
                    REFERENCE
                ================================================== --}}

                <div class="st-reference">

                    <span>
                        Transaction reference
                    </span>

                    <strong>
                        {{ $transaction->reference }}
                    </strong>

                </div>



                {{-- =================================================
                    TITLE
                ================================================== --}}

                <h1>
                    {{ $transaction->title }}
                </h1>



                {{-- =================================================
                    IMAGES
                ================================================== --}}

                @if(count($images))

                    <div
                        class="st-images {{ count($images) === 1 ? 'single' : '' }}"
                    >

                        @foreach($images as $image)

                            <div class="st-image-item">

                                <img
                                    src="{{ asset('storage/' . ltrim($image, '/')) }}"
                                    alt="{{ $transaction->title }}"
                                    loading="lazy"
                                >

                            </div>

                        @endforeach

                    </div>

                @endif



                {{-- =================================================
                    DESCRIPTION
                ================================================== --}}

                <div class="st-section-label">

                    <i class="fa-regular fa-file-lines"></i>

                    Item details

                </div>


                <div class="st-description">

                    {!! nl2br(e($transaction->description)) !!}

                </div>



                {{-- =================================================
                    DELIVERY
                ================================================== --}}

                @if($transaction->delivery_note)

                    <div class="st-delivery">

                        <div class="st-delivery-icon">

                            <i class="fa-solid fa-truck"></i>

                        </div>


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



                {{-- =================================================
                    BUYER INFORMATION
                ================================================== --}}

                <div class="st-buyer-security">

                    <i class="fa-solid fa-user-shield"></i>


                    <div>

                        <strong>
                            Buyer identity confirmed
                        </strong>


                        <span>

                            This secure transaction is assigned to

                            <b>
                                {{ $transaction->buyer_email }}
                            </b>.

                        </span>

                    </div>

                </div>

            </div>



            {{-- =====================================================
                RIGHT SIDE - PAYMENT
            ====================================================== --}}

            <aside class="st-payment">

                <div class="st-payment-card">


                    {{-- =============================================
                        FLASH MESSAGES
                    ============================================== --}}

                    @if(session('success'))

                        <div class="st-message st-message-success">

                            <i class="fa-solid fa-circle-check"></i>


                            <div>
                                {{ session('success') }}
                            </div>

                        </div>

                    @endif


                    @if(session('warning'))

                        <div class="st-message st-message-warning">

                            <i class="fa-solid fa-triangle-exclamation"></i>


                            <div>
                                {{ session('warning') }}
                            </div>

                        </div>

                    @endif


                    @if(session('error'))

                        <div class="st-message st-message-error">

                            <i class="fa-solid fa-circle-exclamation"></i>


                            <div>
                                {{ session('error') }}
                            </div>

                        </div>

                    @endif



                    {{-- =============================================
                        PAYMENT SUCCESSFUL
                    ============================================== --}}

                    @if($isPaid)

                        <span class="st-payment-status paid">

                            <i class="fa-solid fa-circle-check"></i>

                            Payment secured

                        </span>



                        <div class="st-paid-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>



                        <h2 class="st-paid-title">

                            Payment secured by MidPoint

                        </h2>



                        <p class="st-paid-description">

                            Your payment has been successfully verified.
                            The seller can now prepare and dispatch your item.

                        </p>



                        <div class="st-paid-amount">

                            <span>
                                Amount secured
                            </span>


                            <strong>

                                ₦{{ number_format(
                                    (float)
                                    (
                                        $transaction->paid_amount
                                        ?:
                                        $transaction->total_amount
                                    ),
                                    2
                                ) }}

                            </strong>

                        </div>



                        <div class="st-paid-information">


                            <div>

                                <span>
                                    Transaction
                                </span>


                                <strong>
                                    {{ $transaction->reference }}
                                </strong>

                            </div>



                            @if($transaction->paid_at)

                                <div>

                                    <span>
                                        Payment date
                                    </span>


                                    <strong>

                                        {{
                                            $transaction
                                                ->paid_at
                                                ->format(
                                                    'd M Y, h:i A'
                                                )
                                        }}

                                    </strong>

                                </div>

                            @endif



                            @if($transaction->paystack_reference)

                                <div>

                                    <span>
                                        Payment reference
                                    </span>


                                    <strong class="st-payment-reference">

                                        {{ $transaction->paystack_reference }}

                                    </strong>

                                </div>

                            @endif

                        </div>



                        {{-- =========================================
                            CURRENT ORDER STATE
                        ========================================== --}}

                        @if($transaction->status === \App\Models\SecureTransaction::STATUS_PAYMENT_SECURED)

                            <div class="st-order-state">

                                <div class="st-order-state-icon">

                                    <i class="fa-solid fa-box"></i>

                                </div>


                                <div>

                                    <strong>
                                        Waiting for seller dispatch
                                    </strong>


                                    <span>
                                        The seller has been cleared to prepare the order.
                                    </span>

                                </div>

                            </div>

                        @elseif($transaction->status === \App\Models\SecureTransaction::STATUS_DISPATCHED)

                            <div class="st-order-state dispatched">

                                <div class="st-order-state-icon">

                                    <i class="fa-solid fa-truck-fast"></i>

                                </div>


                                <div>

                                    <strong>
                                        Item dispatched
                                    </strong>


                                    <span>
                                        Your seller has marked the item as dispatched.
                                    </span>

                                </div>

                            </div>

                        @elseif($transaction->status === \App\Models\SecureTransaction::STATUS_INSPECTION)

                            <div class="st-order-state inspection">

                                <div class="st-order-state-icon">

                                    <i class="fa-solid fa-magnifying-glass"></i>

                                </div>


                                <div>

                                    <strong>
                                        Inspection period active
                                    </strong>


                                    <span>
                                        Check the item before approving payment release.
                                    </span>

                                </div>

                            </div>

                        @elseif($transaction->status === \App\Models\SecureTransaction::STATUS_COMPLETED)

                            <div class="st-order-state completed">

                                <div class="st-order-state-icon">

                                    <i class="fa-solid fa-circle-check"></i>

                                </div>


                                <div>

                                    <strong>
                                        Transaction completed
                                    </strong>


                                    <span>
                                        This MidPoint transaction has been completed.
                                    </span>

                                </div>

                            </div>

                        @endif



                        {{-- =========================================
                            PROTECTION
                        ========================================== --}}

                        <div class="st-protection">

                            <strong>
                                MidPoint protection
                            </strong>


                            <span>

                                <i class="fa-solid fa-check"></i>

                                Payment verified through Paystack

                            </span>


                            <span>

                                <i class="fa-solid fa-check"></i>

                                Payment amount verified server-side

                            </span>


                            <span>

                                <i class="fa-solid fa-check"></i>

                                {{ $transaction->inspection_hours }}
                                hour inspection after receipt

                            </span>

                        </div>



                    {{-- =============================================
                        PAYMENT NOT YET COMPLETED
                    ============================================== --}}

                    @else

                        <span
                            class="
                                st-payment-status

                                {{ $isPending ? 'pending' : '' }}

                                {{ $isFailed ? 'failed' : '' }}
                            "
                        >

                            @if($isPending)

                                <i class="fa-solid fa-spinner"></i>

                                Payment started

                            @elseif($isFailed)

                                <i class="fa-solid fa-circle-exclamation"></i>

                                Payment not completed

                            @else

                                <i class="fa-regular fa-clock"></i>

                                Awaiting payment

                            @endif

                        </span>



                        <h2>
                            Payment summary
                        </h2>



                        {{-- =========================================
                            PRICE BREAKDOWN
                        ========================================== --}}

                        <div class="st-lines">

                            <div>

                                <span>
                                    Item price
                                </span>


                                <strong>

                                    ₦{{ number_format(
                                        (float)
                                        $transaction->unit_price,
                                        2
                                    ) }}

                                </strong>

                            </div>



                            <div>

                                <span>
                                    Quantity
                                </span>


                                <strong>
                                    {{ number_format($transaction->quantity) }}
                                </strong>

                            </div>



                            <div>

                                <span>
                                    Subtotal
                                </span>


                                <strong>

                                    ₦{{ number_format(
                                        (float)
                                        $transaction->subtotal,
                                        2
                                    ) }}

                                </strong>

                            </div>



                            <div>

                                <span>
                                    Delivery
                                </span>


                                <strong>

                                    ₦{{ number_format(
                                        (float)
                                        $transaction->delivery_fee,
                                        2
                                    ) }}

                                </strong>

                            </div>

                        </div>



                        {{-- =========================================
                            TOTAL
                        ========================================== --}}

                        <div class="st-total">

                            <span>
                                Total to pay
                            </span>


                            <strong>

                                ₦{{ number_format(
                                    (float)
                                    $transaction->total_amount,
                                    2
                                ) }}

                            </strong>

                        </div>



                        {{-- =========================================
                            PAYSTACK PAYMENT FORM
                        ========================================== --}}

                        <form
                            method="POST"
                            action="{{ route('secure-transactions.paystack.initialize', $transaction) }}"
                            id="paystackPaymentForm"
                        >

                            @csrf


                            <button
                                type="submit"
                                class="st-pay"
                                id="paystackPaymentButton"
                            >

                                <i
                                    class="fa-solid fa-shield-halved"
                                    id="paystackPaymentIcon"
                                ></i>


                                <span id="paystackPaymentButtonText">

                                    @if($isPending)

                                        Continue Paystack payment

                                    @elseif($isFailed)

                                        Try payment again

                                    @else

                                        Pay securely with Paystack

                                    @endif

                                </span>

                            </button>

                        </form>



                        {{-- =========================================
                            PAYSTACK INFORMATION
                        ========================================== --}}

                        <div class="st-paystack-note">

                            <div class="st-paystack-note-icon">

                                <i class="fa-solid fa-lock"></i>

                            </div>


                            <div>

                                <strong>
                                    Secure Paystack checkout
                                </strong>


                                <span>
                                    Your card or banking information is handled by
                                    Paystack and is never stored by MidPoint.
                                </span>

                            </div>

                        </div>



                        {{-- =========================================
                            PROTECTION
                        ========================================== --}}

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

                                {{ $transaction->inspection_hours }}
                                hour inspection period

                            </span>

                        </div>

                    @endif

                </div>

            </aside>

        </div>

    </div>

</section>



@push('styles')

<style>

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

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



/*
|--------------------------------------------------------------------------
| Security Label
|--------------------------------------------------------------------------
*/

.st-security {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    margin-bottom: 14px;

    color: #087443;

    font-size: 11px;

    font-weight: 800;
}



/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

.st-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        325px;

    align-items: start;

    gap: 16px;
}



/*
|--------------------------------------------------------------------------
| Cards
|--------------------------------------------------------------------------
*/

.st-card,
.st-payment-card {
    border: 1px solid #DCE5E0;

    border-radius: 18px;

    background: #FFFFFF;

    box-shadow:
        0 12px 40px -32px
        rgba(11, 61, 46, .35);
}


.st-card {
    padding: 25px;
}



/*
|--------------------------------------------------------------------------
| Seller
|--------------------------------------------------------------------------
*/

.st-seller {
    display: flex;

    align-items: center;

    gap: 10px;
}


.st-seller-avatar {
    width: 46px;

    height: 46px;

    flex: 0 0 46px;

    overflow: hidden;

    display: grid;

    place-items: center;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #0B3D2E,
            #12B76A
        );

    color: #FFFFFF;

    font-size: 15px;

    font-weight: 800;
}


.st-seller-avatar img {
    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

    object-position: center;
}


.st-seller-info {
    min-width: 0;
}


.st-seller-info > strong {
    display: block;

    color: #17251F;

    font-size: 13px;
}


.st-seller-verified,
.st-seller-standard {
    display: inline-flex !important;

    align-items: center;

    gap: 4px;

    margin-top: 3px;

    font-size: 12px;
}


.st-seller-verified {
    color: #087443;
}


.st-seller-standard {
    color: #66756D;
}



/*
|--------------------------------------------------------------------------
| Reference
|--------------------------------------------------------------------------
*/

.st-reference {
    margin-top: 18px;
}


.st-reference span {
    display: block;

    margin-bottom: 2px;

    color: #98A29D;

    font-size: 11px;
}


.st-reference strong {
    color: #7C8882;

    font-size: 12px;

    font-weight: 700;
}



/*
|--------------------------------------------------------------------------
| Transaction Title
|--------------------------------------------------------------------------
*/

.st-card h1 {
    margin: 5px 0 16px;

    color: #101915;

    font-family:
        'Bricolage Grotesque',
        sans-serif;

    font-size: 25px;

    font-weight: 800;

    line-height: 1.2;
}



/*
|--------------------------------------------------------------------------
| Images
|--------------------------------------------------------------------------
*/

.st-images {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 7px;

    margin-bottom: 20px;
}


.st-images.single {
    grid-template-columns:
        minmax(0, 280px);
}


.st-image-item {
    height: 120px;

    overflow: hidden;

    border: 1px solid #E0E7E3;

    border-radius: 11px;

    background: #F8FAF9;
}


.st-image-item img {
    width: 100%;

    height: 100%;

    display: block;

    padding: 5px;

    object-fit: contain;

    object-position: center;
}



/*
|--------------------------------------------------------------------------
| Description
|--------------------------------------------------------------------------
*/

.st-section-label {
    display: flex;

    align-items: center;

    gap: 5px;

    margin-bottom: 7px;

    color: #344139;

    font-size: 11px;

    font-weight: 800;
}


.st-section-label i {
    color: #12B76A;
}


.st-description {
    color: #536159;

    font-size: 12px;

    line-height: 1.75;
}



/*
|--------------------------------------------------------------------------
| Delivery
|--------------------------------------------------------------------------
*/

.st-delivery {
    display: flex;

    gap: 10px;

    margin-top: 20px;

    padding: 13px;

    border-radius: 11px;

    background: #F7F9F8;
}


.st-delivery-icon {
    width: 34px;

    height: 34px;

    flex: 0 0 34px;

    display: grid;

    place-items: center;

    border-radius: 9px;

    background: #E8F7EF;

    color: #12B76A;
}


.st-delivery strong {
    color: #344139;

    font-size: 11px;
}


.st-delivery p {
    margin: 3px 0 0;

    color: #69766F;

    font-size: 12px;

    line-height: 1.55;
}



/*
|--------------------------------------------------------------------------
| Buyer Security
|--------------------------------------------------------------------------
*/

.st-buyer-security {
    display: flex;

    gap: 9px;

    margin-top: 14px;

    padding: 12px;

    border: 1px solid #D4EEE0;

    border-radius: 10px;

    background: #F5FCF8;

    color: #087443;
}


.st-buyer-security > i {
    margin-top: 2px;
}


.st-buyer-security strong,
.st-buyer-security span {
    display: block;
}


.st-buyer-security strong {
    font-size: 12px;
}


.st-buyer-security span {
    margin-top: 2px;

    color: #65776E;

    font-size: 11px;

    line-height: 1.5;
}



/*
|--------------------------------------------------------------------------
| Payment Card
|--------------------------------------------------------------------------
*/

.st-payment {
    position: sticky;

    top: 90px;
}


.st-payment-card {
    padding: 21px;
}



/*
|--------------------------------------------------------------------------
| Flash Messages
|--------------------------------------------------------------------------
*/

.st-message {
    display: flex;

    align-items: flex-start;

    gap: 7px;

    margin-bottom: 13px;

    padding: 10px 11px;

    border-radius: 9px;

    font-size: 12px;

    line-height: 1.5;
}


.st-message > i {
    margin-top: 2px;
}


.st-message-success {
    border: 1px solid #ABEFC6;

    background: #ECFDF3;

    color: #067647;
}


.st-message-warning {
    border: 1px solid #F5D199;

    background: #FFF8EB;

    color: #9A5B13;
}


.st-message-error {
    border: 1px solid #FECDCA;

    background: #FEF3F2;

    color: #B42318;
}



/*
|--------------------------------------------------------------------------
| Payment Status
|--------------------------------------------------------------------------
*/

.st-payment-status {
    display: inline-flex;

    align-items: center;

    gap: 4px;

    padding: 5px 8px;

    border-radius: 999px;

    background: #FFF7E8;

    color: #B54708;

    font-size: 11px;

    font-weight: 800;
}


.st-payment-status.pending {
    background: #EFF8FF;

    color: #175CD3;
}


.st-payment-status.failed {
    background: #FEF3F2;

    color: #B42318;
}


.st-payment-status.paid {
    background: #ECFDF3;

    color: #067647;
}



/*
|--------------------------------------------------------------------------
| Payment Heading
|--------------------------------------------------------------------------
*/

.st-payment h2 {
    margin: 13px 0 12px;

    color: #17251F;

    font-size: 14px;

    font-weight: 800;
}



/*
|--------------------------------------------------------------------------
| Price Breakdown
|--------------------------------------------------------------------------
*/

.st-lines {
    padding: 8px 0;

    border-top: 1px solid #E8ECEA;

    border-bottom: 1px solid #E8ECEA;
}


.st-lines > div {
    display: flex;

    justify-content: space-between;

    gap: 15px;

    padding: 6px 0;

    color: #69766F;

    font-size: 11px;
}


.st-lines strong {
    color: #344139;
}



/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

.st-total {
    padding: 15px 0;
}


.st-total span,
.st-total strong {
    display: block;
}


.st-total span {
    color: #637069;

    font-size: 11px;
}


.st-total strong {
    margin-top: 3px;

    color: #0B3D2E;

    font-size: 25px;

    font-weight: 800;
}



/*
|--------------------------------------------------------------------------
| Paystack Button
|--------------------------------------------------------------------------
*/

.st-pay {
    width: 100%;

    min-height: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    border: 0;

    border-radius: 10px;

    background: #12B76A;

    color: #FFFFFF;

    font-family: inherit;

    font-size: 12px;

    font-weight: 800;

    cursor: pointer;

    transition:
        background .16s ease,
        transform .16s ease,
        opacity .16s ease;
}


.st-pay:hover {
    background: #0EA861;

    transform: translateY(-1px);
}


.st-pay:disabled {
    opacity: .72;

    cursor: wait;

    transform: none;
}



/*
|--------------------------------------------------------------------------
| Paystack Note
|--------------------------------------------------------------------------
*/

.st-paystack-note {
    display: flex;

    gap: 8px;

    margin-top: 10px;

    padding: 10px;

    border-radius: 9px;

    background: #F7F9F8;
}


.st-paystack-note-icon {
    width: 28px;

    height: 28px;

    flex: 0 0 28px;

    display: grid;

    place-items: center;

    border-radius: 8px;

    background: #E8F7EF;

    color: #087443;

    font-size: 11px;
}


.st-paystack-note strong,
.st-paystack-note span {
    display: block;
}


.st-paystack-note strong {
    color: #344139;

    font-size: 12px;
}


.st-paystack-note span {
    margin-top: 2px;

    color: #7A8780;

    font-size: 11px;

    line-height: 1.5;
}



/*
|--------------------------------------------------------------------------
| MidPoint Protection
|--------------------------------------------------------------------------
*/

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

    font-size: 11px;
}


.st-protection span {
    color: #62756B;

    font-size: 12px;
}


.st-protection i {
    margin-right: 4px;

    color: #12B76A;
}



/*
|--------------------------------------------------------------------------
| Successful Payment
|--------------------------------------------------------------------------
*/

.st-paid-icon {
    width: 57px;

    height: 57px;

    display: grid;

    place-items: center;

    margin: 20px auto 11px;

    border-radius: 16px;

    background: #ECFDF3;

    color: #12B76A;

    font-size: 21px;
}


.st-paid-title {
    margin: 0 !important;

    font-family:
        'Bricolage Grotesque',
        sans-serif;

    font-size: 16px !important;

    text-align: center;
}


.st-paid-description {
    margin: 7px 0 17px;

    color: #69766F;

    font-size: 12px;

    line-height: 1.6;

    text-align: center;
}


.st-paid-amount {
    padding: 15px;

    border-radius: 11px;

    background: #F2FCF6;

    text-align: center;
}


.st-paid-amount span {
    display: block;

    color: #637069;

    font-size: 12px;
}


.st-paid-amount strong {
    display: block;

    margin-top: 3px;

    color: #0B3D2E;

    font-size: 25px;

    font-weight: 800;
}



/*
|--------------------------------------------------------------------------
| Successful Payment Information
|--------------------------------------------------------------------------
*/

.st-paid-information {
    margin-top: 13px;

    border-top: 1px solid #E8ECEA;
}


.st-paid-information > div {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 12px;

    padding: 9px 0;

    border-bottom: 1px solid #EEF2F0;
}


.st-paid-information span {
    color: #78857E;

    font-size: 12px;
}


.st-paid-information strong {
    max-width: 170px;

    color: #344139;

    font-size: 12px;

    text-align: right;
}


.st-payment-reference {
    overflow-wrap: anywhere;
}



/*
|--------------------------------------------------------------------------
| Order State
|--------------------------------------------------------------------------
*/

.st-order-state {
    display: flex;

    align-items: flex-start;

    gap: 9px;

    margin-top: 15px;

    padding: 12px;

    border-radius: 10px;

    background: #EFF8FF;

    color: #175CD3;
}


.st-order-state.dispatched {
    background: #FFF7E8;

    color: #B54708;
}


.st-order-state.inspection {
    background: #F4F3FF;

    color: #5925DC;
}


.st-order-state.completed {
    background: #ECFDF3;

    color: #067647;
}


.st-order-state-icon {
    width: 30px;

    height: 30px;

    flex: 0 0 30px;

    display: grid;

    place-items: center;

    border-radius: 8px;

    background: rgba(255,255,255,.75);
}


.st-order-state strong,
.st-order-state span {
    display: block;
}


.st-order-state strong {
    font-size: 12px;
}


.st-order-state span {
    margin-top: 2px;

    opacity: .78;

    font-size: 11px;

    line-height: 1.5;
}



/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width: 800px) {

    .st-layout {
        grid-template-columns: 1fr;
    }


    .st-payment {
        position: static;
    }


    .st-images {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media(max-width: 480px) {

    .st-page {
        padding:
            32px
            12px
            50px;
    }


    .st-card,
    .st-payment-card {
        border-radius: 14px;
    }


    .st-card {
        padding: 18px;
    }


    .st-images {
        grid-template-columns: 1fr 1fr;
    }


    .st-image-item {
        height: 105px;
    }


    .st-card h1 {
        font-size: 21px;
    }

}

</style>

@endpush



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Paystack Form
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'paystackPaymentForm'
            );


        /*
        |--------------------------------------------------------------------------
        | Payment Button
        |--------------------------------------------------------------------------
        */

        const button =
            document.getElementById(
                'paystackPaymentButton'
            );


        /*
        |--------------------------------------------------------------------------
        | Button Text
        |--------------------------------------------------------------------------
        */

        const buttonText =
            document.getElementById(
                'paystackPaymentButtonText'
            );


        /*
        |--------------------------------------------------------------------------
        | Button Icon
        |--------------------------------------------------------------------------
        */

        const buttonIcon =
            document.getElementById(
                'paystackPaymentIcon'
            );


        /*
        |--------------------------------------------------------------------------
        | Payment Already Completed
        |--------------------------------------------------------------------------
        */

        if (
            !form
            ||
            !button
        ) {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Checkout Submissions
        |--------------------------------------------------------------------------
        */

        let submitting =
            false;


        form.addEventListener(
            'submit',
            function (event) {

                /*
                |--------------------------------------------------------------------------
                | Already Submitting
                |--------------------------------------------------------------------------
                */

                if (
                    submitting
                ) {

                    event.preventDefault();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Lock
                |--------------------------------------------------------------------------
                */

                submitting =
                    true;


                button.disabled =
                    true;


                /*
                |--------------------------------------------------------------------------
                | Loading Text
                |--------------------------------------------------------------------------
                */

                if (
                    buttonText
                ) {

                    buttonText.textContent =
                        'Opening Paystack...';
                }


                /*
                |--------------------------------------------------------------------------
                | Loading Icon
                |--------------------------------------------------------------------------
                */

                if (
                    buttonIcon
                ) {

                    buttonIcon.className =
                        'fa-solid fa-spinner fa-spin';
                }

            }
        );

    }
);

</script>

@endpush


@endsection