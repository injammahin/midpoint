@extends('frontend.layouts.app')

@section('title', 'Become a Verified Seller | MidPoint')


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Selected Package
    |--------------------------------------------------------------------------
    */

    $oldPackageId =
        old(
            'seller_package_id'
        );


    $selectedPackage =
        null;


    if ($oldPackageId) {

        $selectedPackage =
            $packages->firstWhere(
                'id',
                (int) $oldPackageId
            );
    }


    if (!$selectedPackage) {

        $selectedPackage =
            $defaultPackage;
    }



    /*
    |--------------------------------------------------------------------------
    | Latest Application Status
    |--------------------------------------------------------------------------
    */

    $applicationStatus =
        $latestApplication
            ? $latestApplication->status
            : null;



    /*
    |--------------------------------------------------------------------------
    | Active Subscription
    |--------------------------------------------------------------------------
    |
    | This comes from VerifiedSellerController:
    |
    | $activeSubscription
    |
    */

    $hasActiveSubscription =
        isset($activeSubscription)
        &&
        !is_null(
            $activeSubscription
        );



    /*
    |--------------------------------------------------------------------------
    | Can Apply
    |--------------------------------------------------------------------------
    |
    | Can apply when:
    |
    | - No application exists
    | - Revision required
    | - Old application superseded
    | - Previous package expired
    |
    | Cannot apply while an active subscription exists.
    |
    */

    $canApply =
        !$hasActiveSubscription
        &&
        (
            !$latestApplication

            ||

            in_array(
                $applicationStatus,
                [
                    'revision_required',
                    'superseded',
                    'expired',
                ],
                true
            )
        );



    /*
    |--------------------------------------------------------------------------
    | Under Review
    |--------------------------------------------------------------------------
    */

    $isUnderReview =
        auth()->check()
        &&
        $applicationStatus === 'submitted';



    /*
    |--------------------------------------------------------------------------
    | Revision Required
    |--------------------------------------------------------------------------
    */

    $isRevisionRequired =
        auth()->check()
        &&
        $applicationStatus === 'revision_required';



    /*
    |--------------------------------------------------------------------------
    | Payment Pending
    |--------------------------------------------------------------------------
    */

    $isPaymentPending =
        auth()->check()
        &&
        $applicationStatus === 'payment_pending'
        &&
        $pendingInvoice;



    /*
    |--------------------------------------------------------------------------
    | Active Seller
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We use the subscription now, not only the application status.
    |
    */

    $isActiveSeller =
        auth()->check()
        &&
        $hasActiveSubscription;



    /*
    |--------------------------------------------------------------------------
    | Expired Seller
    |--------------------------------------------------------------------------
    */

    $isExpiredSeller =
        auth()->check()
        &&
        !$hasActiveSubscription
        &&
        $applicationStatus === 'expired';

@endphp



<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap">


            {{-- =====================================================
                HEADING
            ====================================================== --}}

            <div class="mp-section-head mx-auto text-center">

                <div class="mp-eyebrow justify-center">
                    Verified sellers
                </div>


                <h1>
                    Get featured. List products. Sell on sight.
                </h1>


                <p>
                    Verified sellers appear on Featured Businesses
                    with listed products, so buyers can start a
                    secure transaction with one click.
                </p>

            </div>



            {{-- =====================================================
                SUCCESS MESSAGE
            ====================================================== --}}

            @if(session('success'))

                <div
                    class="
                        mp-card
                        mx-auto
                        mb-6
                        max-w-[820px]
                        !border-[#ABEFC6]
                        bg-[#ECFDF3]
                        p-4
                    "
                >

                    <div class="flex items-start gap-3">

                        <div
                            class="
                                grid
                                h-8
                                w-8
                                flex-none
                                place-items-center
                                rounded-full
                                bg-[#D1FADF]
                                text-[#067647]
                            "
                        >

                            <i class="fa-solid fa-check"></i>

                        </div>


                        <div>

                            <strong
                                class="
                                    block
                                    text-[13px]
                                    text-[#067647]
                                "
                            >
                                Success
                            </strong>


                            <p
                                class="
                                    mt-1
                                    text-[12px]
                                    leading-5
                                    text-[#067647]
                                "
                            >
                                {{ session('success') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif



            {{-- =====================================================
                ERROR MESSAGE
            ====================================================== --}}

            @if(session('error'))

                <div
                    class="
                        mp-card
                        mx-auto
                        mb-6
                        max-w-[820px]
                        !border-[#FECDD3]
                        bg-[#FFF1F2]
                        p-4
                    "
                >

                    <strong class="text-[13px] text-[#B42318]">

                        {{ session('error') }}

                    </strong>

                </div>

            @endif



            {{-- =====================================================
                VALIDATION ERRORS
            ====================================================== --}}

            @if($errors->any())

                <div
                    class="
                        mp-card
                        mx-auto
                        mb-6
                        max-w-[820px]
                        !border-[#FECDD3]
                        bg-[#FFF1F2]
                        p-5
                    "
                >

                    <div
                        class="
                            mb-3
                            flex
                            items-center
                            gap-2
                            font-bold
                            text-[#B42318]
                        "
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>

                        Please check the following

                    </div>


                    <div
                        class="
                            flex
                            flex-col
                            gap-1
                            text-[12px]
                            text-[#B42318]
                        "
                    >

                        @foreach($errors->all() as $error)

                            <div>
                                • {{ $error }}
                            </div>

                        @endforeach

                    </div>

                </div>

            @endif



            {{-- =====================================================
                STEPS
            ====================================================== --}}

            @php

                $steps = [

                    [
                        'number' => '1',

                        'title' =>
                            'Apply & verify',

                        'text' =>
                            'Submit your business details and verification documents. Our team manually reviews every application.',
                    ],

                    [
                        'number' => '2',

                        'title' =>
                            'Get approved & pay',

                        'text' =>
                            'Once approved, MidPoint creates your package invoice. Payment is required before seller activation.',
                    ],

                    [
                        'number' => '3',

                        'title' =>
                            'List & get buyers',

                        'text' =>
                            'After activation, list products within your package limit and connect with buyers through MidPoint.',
                    ],

                ];

            @endphp


            <div class="mp-grid-3 mb-11">

                @foreach($steps as $step)

                    <div class="mp-card p-[26px]">

                        <div
                            class="
                                mb-4
                                grid
                                h-[42px]
                                w-[42px]
                                place-items-center
                                rounded-[13px]
                                bg-[#0B3D2E]
                                font-['Bricolage_Grotesque']
                                text-[18px]
                                font-extrabold
                                text-[#7EF0B6]
                            "
                        >

                            {{ $step['number'] }}

                        </div>


                        <h2
                            class="
                                mb-2
                                font-['Bricolage_Grotesque']
                                text-[18px]
                                font-bold
                            "
                        >

                            {{ $step['title'] }}

                        </h2>


                        <p class="mp-muted text-[14px]">

                            {{ $step['text'] }}

                        </p>

                    </div>

                @endforeach

            </div>



            {{-- =====================================================
                ACTIVE PLAN INFORMATION
            ====================================================== --}}

            @if($activeSubscription)

                <div
                    class="
                        active-plan-card
                        mp-card
                        mx-auto
                        mb-8
                        max-w-[880px]
                        !border-[#ABEFC6]
                        bg-[#F3FFF8]
                        p-6
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-6
                            max-md:flex-col
                            max-md:items-start
                        "
                    >

                        {{-- Left --}}
                        <div class="flex items-center gap-4">

                            <div
                                class="
                                    grid
                                    h-[52px]
                                    w-[52px]
                                    flex-none
                                    place-items-center
                                    rounded-[15px]
                                    bg-[#D1FADF]
                                    text-[20px]
                                    text-[#067647]
                                "
                            >

                                <i class="fa-solid fa-crown"></i>

                            </div>


                            <div>

                                <span
                                    class="
                                        mp-badge
                                        mp-badge-green
                                    "
                                >

                                    Active Seller Plan

                                </span>


                                <h2
                                    class="
                                        mt-2
                                        font-['Bricolage_Grotesque']
                                        text-[22px]
                                        font-bold
                                    "
                                >

                                    {{ $activeSubscription->package_name }}

                                </h2>


                                <div
                                    class="
                                        mt-1
                                        flex
                                        flex-wrap
                                        items-center
                                        gap-x-4
                                        gap-y-1
                                        text-[12px]
                                        text-[#5A6660]
                                    "
                                >

                                    <span>

                                        <i
                                            class="
                                                fa-solid
                                                fa-box-open
                                                mr-1
                                                text-[#12B76A]
                                            "
                                        ></i>

                                        {{
                                            number_format(
                                                $activeSubscription->product_limit
                                            )
                                        }}

                                        products

                                    </span>


                                    <span>

                                        <i
                                            class="
                                                fa-solid
                                                fa-credit-card
                                                mr-1
                                                text-[#12B76A]
                                            "
                                        ></i>

                                        ₦{{
                                            number_format(
                                                (float) $activeSubscription->price,
                                                0
                                            )
                                        }}

                                        /{{ $activeSubscription->billing_period }}

                                    </span>

                                </div>

                            </div>

                        </div>



                        {{-- Right --}}
                        <div
                            class="
                                rounded-[14px]
                                border
                                border-[#ABEFC6]
                                bg-white
                                px-5
                                py-4
                                text-right
                                max-md:w-full
                                max-md:text-left
                            "
                        >

                            <span
                                class="
                                    block
                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-[.08em]
                                    text-[#66756D]
                                "
                            >

                                Time remaining

                            </span>


                            <strong
                                class="
                                    mt-1
                                    block
                                    font-['Bricolage_Grotesque']
                                    text-[24px]
                                    font-extrabold
                                    text-[#087443]
                                "
                            >

                                {{ $activeSubscription->days_left }}

                                {{
                                    $activeSubscription->days_left === 1
                                        ? 'day'
                                        : 'days'
                                }}

                                left

                            </strong>


                            @if($activeSubscription->expires_at)

                                <span
                                    class="
                                        mt-1
                                        block
                                        text-[11px]
                                        text-[#66756D]
                                    "
                                >

                                    Expires

                                    {{
                                        $activeSubscription
                                            ->expires_at
                                            ->format(
                                                'd M Y, h:i A'
                                            )
                                    }}

                                </span>

                            @endif

                        </div>

                    </div>


                    <div
                        class="
                            mt-5
                            border-t
                            border-[#D9F0E3]
                            pt-4
                            text-[11px]
                            leading-5
                            text-[#66756D]
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-circle-info
                                mr-1
                                text-[#12B76A]
                            "
                        ></i>

                        Your current package remains active until the
                        expiration time shown above.

                        Package selection will become available again
                        after your plan expires.

                    </div>

                </div>

            @endif



            {{-- =====================================================
                EXPIRED PACKAGE NOTICE
            ====================================================== --}}

            @if($isExpiredSeller)

                <div
                    class="
                        mp-card
                        mx-auto
                        mb-8
                        max-w-[820px]
                        !border-[#FEDF89]
                        bg-[#FFFDF5]
                        p-6
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            gap-4
                        "
                    >

                        <div
                            class="
                                grid
                                h-[46px]
                                w-[46px]
                                flex-none
                                place-items-center
                                rounded-full
                                bg-[#FEF0C7]
                                text-[#B54708]
                            "
                        >

                            <i class="fa-solid fa-clock-rotate-left"></i>

                        </div>


                        <div>

                            <span
                                class="
                                    mp-badge
                                    bg-[#FEF0C7]
                                    text-[#B54708]
                                "
                            >

                                Package expired

                            </span>


                            <h2
                                class="
                                    mt-3
                                    font-['Bricolage_Grotesque']
                                    text-[20px]
                                    font-bold
                                "
                            >

                                Your seller package has expired

                            </h2>


                            <p
                                class="
                                    mp-muted
                                    mt-2
                                    max-w-[620px]
                                    text-[13px]
                                    leading-6
                                "
                            >

                                Your previous seller package is no longer active.

                                You can now select any available package below
                                and submit a new application to renew or upgrade
                                your seller account.

                            </p>

                        </div>

                    </div>

                </div>

            @endif



            {{-- =====================================================
                PACKAGES
            ====================================================== --}}

            @if($packages->isNotEmpty())

                <div class="seller-packages-grid grid gap-5">

                    @foreach($packages as $package)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Theme
                            |--------------------------------------------------------------------------
                            */

                            $theme =
                                $package->theme
                                ?: 'green';


                            if ($theme === 'purple') {

                                $badgeClass =
                                    'mp-badge-purple';

                                $buttonClass =
                                    'mp-btn-purple';

                            } elseif ($theme === 'slate') {

                                $badgeClass =
                                    'mp-badge-slate';

                                $buttonClass =
                                    'mp-btn-outline';

                            } else {

                                $badgeClass =
                                    'mp-badge-green';

                                $buttonClass =
                                    'mp-btn-green';

                            }



                            /*
                            |--------------------------------------------------------------------------
                            | Selected
                            |--------------------------------------------------------------------------
                            */

                            $isSelected =
                                $selectedPackage
                                &&
                                (int) $selectedPackage->id
                                ===
                                (int) $package->id;



                            /*
                            |--------------------------------------------------------------------------
                            | Current Active Package
                            |--------------------------------------------------------------------------
                            */

                            $isCurrentActivePlan =
                                $hasActiveSubscription
                                &&
                                (int) $activeSubscription->seller_package_id
                                ===
                                (int) $package->id;



                            /*
                            |--------------------------------------------------------------------------
                            | Plan Locked
                            |--------------------------------------------------------------------------
                            |
                            | User cannot purchase another package until the
                            | current subscription expires.
                            |
                            */

                            $packageLocked =
                                $hasActiveSubscription;



                            /*
                            |--------------------------------------------------------------------------
                            | Renew / Upgrade State
                            |--------------------------------------------------------------------------
                            */

                            $showRenewUpgrade =
                                $isExpiredSeller;

                        @endphp


                        <div
                            class="
                                mp-card
                                seller-package-card
                                relative
                                flex
                                flex-col
                                p-7

                                {{
                                    $package->is_popular
                                        ? 'border-2 !border-[#12B76A]'
                                        : ''
                                }}

                                {{
                                    !$packageLocked
                                    &&
                                    $isSelected
                                        ? 'is-selected'
                                        : ''
                                }}

                                {{
                                    $isCurrentActivePlan
                                        ? 'is-active-plan'
                                        : ''
                                }}
                            "

                            data-package-card="{{ $package->id }}"
                        >


                            {{-- Popular --}}
                            @if($package->is_popular)

                                <span
                                    class="
                                        absolute
                                        -top-3
                                        left-1/2
                                        -translate-x-1/2
                                        whitespace-nowrap
                                        mp-badge
                                        mp-badge-green
                                    "
                                >

                                    Most popular

                                </span>

                            @endif



                            {{-- Current Plan --}}
                            @if($isCurrentActivePlan)

                                <span
                                    class="
                                        absolute
                                        right-4
                                        top-4
                                        inline-flex
                                        items-center
                                        gap-1
                                        rounded-full
                                        bg-[#D1FADF]
                                        px-2.5
                                        py-1
                                        text-[9px]
                                        font-bold
                                        text-[#067647]
                                    "
                                >

                                    <i class="fa-solid fa-circle-check"></i>

                                    Current plan

                                </span>

                            @endif



                            {{-- Package Badge --}}
                            <span
                                class="
                                    mp-badge
                                    {{ $badgeClass }}
                                "
                            >

                                {{ $package->name }}

                            </span>



                            {{-- Price --}}
                            <div
                                class="
                                    mt-[14px]
                                    font-['Bricolage_Grotesque']
                                    text-[38px]
                                    font-extrabold
                                "
                            >

                                ₦{{
                                    number_format(
                                        (float) $package->price,
                                        0
                                    )
                                }}


                                <span
                                    class="
                                        text-[15px]
                                        font-semibold
                                        text-[#5A6660]
                                    "
                                >

                                    /{{ $package->billing_period }}

                                </span>

                            </div>



                            {{-- Description --}}
                            <div
                                class="
                                    mp-small
                                    mp-muted
                                    mb-4
                                    mt-1
                                "
                            >

                                {{
                                    $package->description
                                    ?: 'Seller package'
                                }}

                            </div>



                            {{-- Features --}}
                            <div
                                class="
                                    mp-small
                                    mb-5
                                    flex
                                    flex-col
                                    gap-[9px]
                                "
                            >

                                <div class="flex items-start gap-2">

                                    <span class="text-[#12B76A]">
                                        ✓
                                    </span>


                                    <span>

                                        Up to

                                        <strong>

                                            {{
                                                number_format(
                                                    $package->product_limit
                                                )
                                            }}

                                            listed products

                                        </strong>

                                    </span>

                                </div>


                                @foreach(($package->features ?? []) as $feature)

                                    <div class="flex items-start gap-2">

                                        <span class="text-[#12B76A]">
                                            ✓
                                        </span>


                                        <span>
                                            {{ $feature }}
                                        </span>

                                    </div>

                                @endforeach

                            </div>



                            {{-- =================================================
                                PACKAGE BUTTON
                            ================================================== --}}

                            @if($isCurrentActivePlan)

                                <button
                                    type="button"
                                    disabled
                                    class="
                                        mp-btn
                                        mp-btn-green
                                        mt-auto
                                        cursor-not-allowed
                                        opacity-70
                                    "
                                >

                                    <i class="fa-solid fa-circle-check"></i>

                                    Active Plan

                                </button>


                            @elseif($packageLocked)

                                <button
                                    type="button"
                                    disabled
                                    class="
                                        mp-btn
                                        mp-btn-outline
                                        mt-auto
                                        cursor-not-allowed
                                        opacity-60
                                    "
                                >

                                    <i class="fa-solid fa-lock"></i>

                                    Current plan active

                                </button>


                            @else

                                <button
                                    type="button"

                                    data-package-id="{{ $package->id }}"

                                    data-package="{{ $package->name }}"

                                    data-price="₦{{
                                        number_format(
                                            (float) $package->price,
                                            0
                                        )
                                    }}/{{ $package->billing_period }}"

                                    data-theme="{{ $theme }}"

                                    class="
                                        verified-package-btn
                                        mp-btn
                                        {{ $buttonClass }}
                                        mt-auto
                                    "
                                >

                                    @if($showRenewUpgrade)

                                        Renew / Upgrade {{ $package->name }}

                                    @elseif($isSelected)

                                        Selected {{ $package->name }}

                                    @else

                                        Choose {{ $package->name }}

                                    @endif

                                </button>

                            @endif

                        </div>

                    @endforeach

                </div>


            @else

                <div class="mp-card p-8 text-center">

                    <strong>
                        No seller packages are currently available.
                    </strong>

                </div>

            @endif



            {{-- =====================================================
                PAYMENT INVOICE
            ====================================================== --}}

            @if($isPaymentPending)

                <div
                    id="seller-invoice"
                    class="
                        mp-card
                        mx-auto
                        mt-[34px]
                        max-w-[760px]
                        !border-[#12B76A]
                        p-[30px]
                    "
                >

                    <div
                        class="
                            mb-6
                            flex
                            items-start
                            justify-between
                            gap-5
                            max-sm:flex-col
                        "
                    >

                        <div>

                            <span class="mp-badge mp-badge-green">

                                Application approved

                            </span>


                            <h2
                                class="
                                    mt-3
                                    font-['Bricolage_Grotesque']
                                    text-[22px]
                                    font-bold
                                "
                            >

                                Seller Package Invoice

                            </h2>


                            <p class="mp-small mp-muted mt-1">

                                Complete payment to activate
                                your verified seller account.

                            </p>

                        </div>


                        <div class="text-right max-sm:text-left">

                            <span class="mp-small mp-muted">
                                Invoice
                            </span>


                            <strong class="mt-1 block text-[12px]">

                                {{ $pendingInvoice->invoice_number }}

                            </strong>

                        </div>

                    </div>



                    <div
                        class="
                            mb-6
                            rounded-[16px]
                            border
                            border-[#DCE5E0]
                            bg-[#F8FBF9]
                            p-5
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-2
                            "
                        >

                            <span class="mp-muted">
                                Business
                            </span>


                            <strong>
                                {{ $latestApplication->business_name }}
                            </strong>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-2
                            "
                        >

                            <span class="mp-muted">
                                Package
                            </span>


                            <strong>
                                {{ $latestApplication->package_name }}
                            </strong>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-2
                            "
                        >

                            <span class="mp-muted">
                                Product allowance
                            </span>


                            <strong>

                                {{
                                    number_format(
                                        $latestApplication->product_limit
                                    )
                                }}

                                products

                            </strong>

                        </div>


                        @if($pendingInvoice->due_at)

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-4
                                    py-2
                                "
                            >

                                <span class="mp-muted">
                                    Payment due
                                </span>


                                <strong>

                                    {{
                                        $pendingInvoice
                                            ->due_at
                                            ->format('d M Y')
                                    }}

                                </strong>

                            </div>

                        @endif


                        <div
                            class="
                                mt-3
                                flex
                                items-center
                                justify-between
                                gap-4
                                border-t
                                border-[#DCE5E0]
                                pt-5
                            "
                        >

                            <strong>
                                Amount due
                            </strong>


                            <strong
                                class="
                                    font-['Bricolage_Grotesque']
                                    text-[28px]
                                    font-extrabold
                                    text-[#0B3D2E]
                                "
                            >

                                ₦{{
                                    number_format(
                                        (float) $pendingInvoice->amount,
                                        0
                                    )
                                }}

                            </strong>

                        </div>

                    </div>



{{-- =================================================
    PAYSTACK PAYMENT
================================================== --}}

<div
    class="
        rounded-[16px]
        border
        border-[#DCE5E0]
        bg-[#F8FBF9]
        p-5
    "
>

    <div
        class="
            flex
            items-start
            gap-3
        "
    >

        <div
            class="
                grid
                h-10
                w-10
                flex-none
                place-items-center
                rounded-full
                bg-[#D1FADF]
                text-[#067647]
            "
        >

            <i
                class="
                    fa-solid
                    fa-shield-halved
                "
            ></i>

        </div>


        <div>

            <strong
                class="
                    block
                    text-[14px]
                    text-[#0B3D2E]
                "
            >

                Secure payment with Paystack

            </strong>


            <p
                class="
                    mp-small
                    mp-muted
                    mt-1
                    leading-5
                "
            >

                Continue to Paystack's secure checkout.

                Paystack will display the payment methods
                available for your account and environment.

            </p>

        </div>

    </div>



    {{-- TEST MODE --}}
    @if(
        strtolower(
            (string) config(
                'services.paystack.mode',
                'test'
            )
        )
        ===
        'test'
    )

        <div
            class="
                mt-4
                rounded-[12px]
                border
                border-[#FEDF89]
                bg-[#FFF7E8]
                px-4
                py-3
                text-[11px]
                leading-5
                text-[#8A5A00]
            "
        >

            <strong
                class="
                    text-[#B54708]
                "
            >

                Paystack Test Mode:

            </strong>


            your current test keys will process
            a simulated payment only.

            No real money is charged in test mode.

        </div>

    @endif



    {{-- PAYMENT OPTIONS --}}
    <div
        class="
            mt-4
            flex
            flex-wrap
            gap-2
        "
    >

        <span
            class="
                mp-badge
                mp-badge-slate
            "
        >

            <i
                class="
                    fa-regular
                    fa-credit-card
                    mr-1
                "
            ></i>

            Card

        </span>


        <span
            class="
                mp-badge
                mp-badge-slate
            "
        >

            <i
                class="
                    fa-solid
                    fa-building-columns
                    mr-1
                "
            ></i>

            Bank

        </span>


        <span
            class="
                mp-badge
                mp-badge-slate
            "
        >

            <i
                class="
                    fa-solid
                    fa-mobile-screen-button
                    mr-1
                "
            ></i>

            USSD

        </span>


        <span
            class="
                mp-badge
                mp-badge-slate
            "
        >

            <i
                class="
                    fa-solid
                    fa-money-bill-transfer
                    mr-1
                "
            ></i>

            Transfer

        </span>

    </div>



    {{-- PAY --}}
    <form
        method="POST"
        action="{{
            route(
                'seller-invoices.pay',
                $pendingInvoice
            )
        }}"
        class="mt-5"
    >

        @csrf


        <button
            type="submit"
            class="
                mp-btn
                mp-btn-primary
                mp-btn-lg
                w-full
            "
        >

            <i
                class="
                    fa-solid
                    fa-lock
                "
            ></i>


            Pay ₦{{
                number_format(
                    (float) $pendingInvoice->amount,
                    0
                )
            }}

            with Paystack

        </button>

    </form>



    <p
        class="
            mp-small
            mp-muted
            mt-3
            text-center
            leading-5
        "
    >

        Card details are entered on Paystack's checkout,
        not on MidPoint.

        After successful verification,
        your seller package is activated automatically
        and a confirmation email with a PDF invoice
        is sent to you.

    </p>

</div>

                </div>

            @endif



            {{-- =====================================================
                ACTIVE SELLER
            ====================================================== --}}

            @if($isActiveSeller)

                <div
                    class="
                        mp-card
                        mx-auto
                        mt-[34px]
                        max-w-[760px]
                        !border-[#12B76A]
                        p-[32px]
                        text-center
                    "
                >

                    <div
                        class="
                            mx-auto
                            mb-4
                            grid
                            h-[58px]
                            w-[58px]
                            place-items-center
                            rounded-full
                            bg-[#E8F7EF]
                            text-[22px]
                            text-[#087443]
                        "
                    >

                        <i class="fa-solid fa-check"></i>

                    </div>


                    <span class="mp-badge mp-badge-green">

                        Verified seller active

                    </span>


                    <h2
                        class="
                            mt-4
                            font-['Bricolage_Grotesque']
                            text-[23px]
                            font-bold
                        "
                    >

                        Your seller account is active

                    </h2>


                    <p
                        class="
                            mp-muted
                            mx-auto
                            mt-2
                            max-w-[540px]
                        "
                    >

                        Your

                        <strong>
                            {{ $activeSubscription->package_name }}
                        </strong>

                        package allows up to

                        <strong>
                            {{
                                number_format(
                                    $activeSubscription->product_limit
                                )
                            }}
                        </strong>

                        listed products.

                    </p>



                    {{-- Remaining Time --}}
                    <div
                        class="
                            mx-auto
                            mt-6
                            max-w-[500px]
                            rounded-[14px]
                            border
                            border-[#D9F0E3]
                            bg-[#F8FFFB]
                            p-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-1
                            "
                        >

                            <span class="mp-small mp-muted">
                                Days remaining
                            </span>


                            <strong class="text-[13px] text-[#087443]">

                                {{ $activeSubscription->days_left }}

                                {{
                                    $activeSubscription->days_left === 1
                                        ? 'day'
                                        : 'days'
                                }}

                            </strong>

                        </div>


                        @if($activeSubscription->started_at)

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-4
                                    py-1
                                "
                            >

                                <span class="mp-small mp-muted">
                                    Started
                                </span>


                                <strong class="text-[12px]">

                                    {{
                                        $activeSubscription
                                            ->started_at
                                            ->format('d M Y, h:i A')
                                    }}

                                </strong>

                            </div>

                        @endif


                        @if($activeSubscription->expires_at)

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-4
                                    py-1
                                "
                            >

                                <span class="mp-small mp-muted">
                                    Expires
                                </span>


                                <strong class="text-[12px]">

                                    {{
                                        $activeSubscription
                                            ->expires_at
                                            ->format('d M Y, h:i A')
                                    }}

                                </strong>

                            </div>

                        @endif

                    </div>


                    <a
                        href="{{ route('seller.products') }}"
                        class="
                            mp-btn
                            mp-btn-primary
                            mt-6
                        "
                    >

                        <i class="fa-solid fa-bag-shopping"></i>

                        Manage Products

                    </a>
                    @if(
                        $latestApplication
                        &&
                        $latestApplication->invoice
                        &&
                        $latestApplication->invoice->status === 'paid'
                    )

                        <a
                            href="{{
                                route(
                                    'seller-invoices.download',
                                    $latestApplication->invoice
                                )
                            }}"
                            class="
                                mp-btn
                                mp-btn-outline
                                mt-3
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-file-pdf
                                "
                            ></i>

                            Download Paid Invoice

                        </a>

                    @endif
                </div>

            @endif



            {{-- =====================================================
                UNDER REVIEW
            ====================================================== --}}

            @if($isUnderReview)

                <div
                    class="
                        mp-card
                        mx-auto
                        mt-[34px]
                        max-w-[720px]
                        p-[32px]
                        text-center
                    "
                >

                    <div
                        class="
                            mx-auto
                            mb-4
                            grid
                            h-[58px]
                            w-[58px]
                            place-items-center
                            rounded-full
                            bg-[#EEF4FF]
                            text-[21px]
                            text-[#3538CD]
                        "
                    >

                        <i class="fa-regular fa-clock"></i>

                    </div>


                    <span class="mp-badge mp-badge-slate">

                        Under review

                    </span>


                    <h2
                        class="
                            mt-4
                            font-['Bricolage_Grotesque']
                            text-[22px]
                            font-bold
                        "
                    >

                        Application submitted successfully

                    </h2>


                    <p
                        class="
                            mp-muted
                            mx-auto
                            mt-2
                            max-w-[540px]
                            leading-6
                        "
                    >

                        MidPoint is reviewing your seller
                        verification request.

                        You will receive an email when
                        the review status changes.

                    </p>


                    <div
                        class="
                            mx-auto
                            mt-6
                            max-w-[470px]
                            rounded-[14px]
                            border
                            border-[#E1E8E4]
                            bg-[#F8FBF9]
                            p-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-1
                            "
                        >

                            <span class="mp-small mp-muted">
                                Reference
                            </span>


                            <strong class="text-[12px]">

                                {{ $latestApplication->reference }}

                            </strong>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-4
                                py-1
                            "
                        >

                            <span class="mp-small mp-muted">
                                Package
                            </span>


                            <strong class="text-[12px]">

                                {{ $latestApplication->package_name }}

                            </strong>

                        </div>

                    </div>

                </div>

            @endif



            {{-- =====================================================
                REVISION NOTICE
            ====================================================== --}}

            @if($isRevisionRequired)

                <div
                    class="
                        mp-card
                        mx-auto
                        mt-[34px]
                        max-w-[720px]
                        !border-[#FEDF89]
                        bg-[#FFFDF5]
                        p-5
                    "
                >

                    <div class="flex items-start gap-3">

                        <div
                            class="
                                grid
                                h-9
                                w-9
                                flex-none
                                place-items-center
                                rounded-full
                                bg-[#FEF0C7]
                                text-[#B54708]
                            "
                        >

                            <i class="fa-solid fa-rotate-left"></i>

                        </div>


                        <div>

                            <strong
                                class="
                                    text-[14px]
                                    text-[#B54708]
                                "
                            >

                                Revision required

                            </strong>


                            <p
                                class="
                                    mt-2
                                    text-[13px]
                                    leading-6
                                    text-[#7A5B28]
                                "
                            >

                                {{ $latestApplication->revision_note }}

                            </p>


                            <p
                                class="
                                    mt-2
                                    text-[11px]
                                    leading-5
                                    text-[#8A6D3B]
                                "
                            >

                                Submit a new application below
                                with the requested corrections.

                            </p>

                        </div>

                    </div>

                </div>

            @endif



            {{-- =====================================================
                APPLICATION FORM
            ====================================================== --}}

            @if($canApply)

                <div
                    id="verified-application"
                    class="
                        mp-card
                        mx-auto
                        mt-[34px]
                        max-w-[720px]
                        p-[30px]
                    "
                >

                    <h2
                        class="
                            font-['Bricolage_Grotesque']
                            text-[18px]
                            font-bold
                        "
                    >

                        @if($isExpiredSeller)

                            Renew or Upgrade Seller Package

                        @elseif($isRevisionRequired)

                            Re-apply to become a Verified Seller

                        @else

                            Apply to become a Verified Seller

                        @endif

                    </h2>


                    <div
                        class="
                            mp-small
                            mp-muted
                            mb-[18px]
                            mt-1
                        "
                    >

                        Selected package:


                        <span
                            id="selected-package"
                            class="
                                mp-badge
                                mp-badge-green
                                ml-1
                            "
                        >

                            @if($selectedPackage)

                                {{ $selectedPackage->name }}

                                ·

                                ₦{{
                                    number_format(
                                        (float) $selectedPackage->price,
                                        0
                                    )
                                }}/{{ $selectedPackage->billing_period }}

                            @else

                                No package selected

                            @endif

                        </span>

                    </div>



                    <form
                        id="verified-seller-form"
                        method="POST"
                        action="{{ route('seller-applications.store') }}"
                        enctype="multipart/form-data"
                    >

                        @csrf


                        <input
                            type="hidden"
                            name="seller_package_id"
                            id="selected-package-input"
                            value="{{
                                old(
                                    'seller_package_id',
                                    $selectedPackage
                                        ? $selectedPackage->id
                                        : ''
                                )
                            }}"
                        >



                        <div class="mp-grid-2">

                            <div class="mp-field">

                                <label>
                                    Business name
                                </label>


                                <input
                                    type="text"
                                    name="business_name"
                                    value="{{ old('business_name') }}"
                                    placeholder="e.g. Temi Gadgets"
                                    required
                                >

                            </div>



                            <div class="mp-field">

                                <label>
                                    Category
                                </label>


                                <select
                                    name="category"
                                    required
                                >

                                    @php

                                        $categories = [

                                            'Phones & Electronics',

                                            'Beauty & Hair',

                                            'Fashion & Tailoring',

                                            'Home & Kitchen',

                                            'Power & Tools',

                                            'Books & Stationery',

                                            'Other',

                                        ];

                                    @endphp


                                    @foreach($categories as $category)

                                        <option
                                            value="{{ $category }}"
                                            {{
                                                old('category') === $category
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >

                                            {{ $category }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>



                            <div class="mp-field">

                                <label>
                                    Location
                                </label>


                                <input
                                    type="text"
                                    name="location"
                                    value="{{ old('location') }}"
                                    placeholder="e.g. Ikeja, Lagos"
                                    required
                                >

                            </div>



                            <div class="mp-field">

                                <label>
                                    Phone / WhatsApp
                                </label>


                                <input
                                    type="tel"
                                    name="phone"
                                    value="{{
                                        old(
                                            'phone',
                                            auth()->check()
                                                ? auth()->user()->phone
                                                : ''
                                        )
                                    }}"
                                    placeholder="0803 xxx xxxx"
                                    required
                                >

                            </div>



                            <div class="mp-field">

                                <label>
                                    CAC number
                                    (or BVN for individuals)
                                </label>


                                <input
                                    type="text"
                                    name="cac_or_bvn"
                                    value="{{ old('cac_or_bvn') }}"
                                    placeholder="RC1234567"
                                    required
                                >

                            </div>



                            <div class="mp-field">

                                <label>
                                    Existing store link
                                </label>


                                <input
                                    type="url"
                                    name="store_link"
                                    value="{{ old('store_link') }}"
                                    placeholder="https://instagram.com/yourstore"
                                >

                            </div>

                        </div>



                        <div class="mp-field">

                            <label>
                                Short business description
                            </label>


                            <textarea
                                name="description"
                                rows="4"
                                placeholder="What do you sell, and what makes buyers trust you?"
                                required
                            >{{ old('description') }}</textarea>

                        </div>



                        <div class="mp-field">

                            <label>
                                Upload verification documents
                            </label>


                            <input
                                id="verification-documents"
                                type="file"
                                name="documents[]"
                                multiple
                                class="hidden"
                                accept=".jpg,.jpeg,.png,.pdf"
                            >


                            <label
                                for="verification-documents"
                                class="mp-upload !mb-0"
                            >

                                <div
                                    class="
                                        flex
                                        items-center
                                        justify-center
                                        gap-2
                                    "
                                >

                                    <i class="fa-solid fa-paperclip"></i>


                                    <span>

                                        CAC certificate,
                                        valid ID,
                                        or utility bill

                                    </span>


                                    <strong class="text-[#12B76A]">

                                        browse

                                    </strong>

                                </div>


                                <div
                                    id="verification-file-name"
                                    class="mt-2 text-xs"
                                ></div>

                            </label>


                            <small class="mp-muted mt-2 block">

                                JPG, PNG or PDF.
                                Maximum 5 files, 10 MB each.

                            </small>

                        </div>



                        {{-- =================================================
                            GUEST
                        ================================================== --}}

                        @guest

                            @php

                                $guestIntendedUrl =
                                    route(
                                        'verified-sellers'
                                    );


                                if ($selectedPackage) {

                                    $guestIntendedUrl .=
                                        '?package='
                                        .
                                        $selectedPackage->id;
                                }


                                $guestIntendedUrl .=
                                    '#verified-application';

                            @endphp


                            <a
                                id="seller-login-button"
                                href="{{
                                    route(
                                        'login',
                                        [
                                            'redirect' =>
                                                $guestIntendedUrl
                                        ]
                                    )
                                }}"
                                class="
                                    mp-btn
                                    mp-btn-primary
                                    mp-btn-lg
                                    w-full
                                "
                            >

                                <i class="fa-solid fa-right-to-bracket"></i>

                                Login to apply

                            </a>


                            <p
                                class="
                                    mp-small
                                    mp-muted
                                    mt-3
                                    text-center
                                "
                            >

                                Login and verify your email
                                before submitting your application.

                            </p>


                        @else


                            {{-- =================================================
                                UNVERIFIED USER
                            ================================================== --}}

                            @if(!auth()->user()->hasVerifiedEmail())

                                <a
                                    href="{{ route('verification.notice') }}"
                                    class="
                                        mp-btn
                                        mp-btn-primary
                                        mp-btn-lg
                                        w-full
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-envelope-circle-check
                                        "
                                    ></i>

                                    Verify email to apply

                                </a>


                                <p
                                    class="
                                        mp-small
                                        mp-muted
                                        mt-3
                                        text-center
                                    "
                                >

                                    Your MidPoint email address
                                    must be verified first.

                                </p>


                            @else

                                @if($selectedPackage)

                                    <button
                                        type="submit"
                                        class="
                                            mp-btn
                                            mp-btn-primary
                                            mp-btn-lg
                                            w-full
                                        "
                                    >

                                        <i class="fa-solid fa-paper-plane"></i>

                                        @if($isExpiredSeller)

                                            Submit Renewal / Upgrade Application

                                        @elseif($isRevisionRequired)

                                            Re-submit Application

                                        @else

                                            Submit Application

                                        @endif

                                    </button>


                                @else

                                    <button
                                        type="button"
                                        disabled
                                        class="
                                            mp-btn
                                            mp-btn-primary
                                            mp-btn-lg
                                            w-full
                                            opacity-50
                                        "
                                    >

                                        Select a package first

                                    </button>

                                @endif

                            @endif

                        @endguest



                        <p
                            class="
                                mp-small
                                mp-muted
                                mt-3
                                text-center
                            "
                        >

                            You'll only be billed
                            after your business is approved.

                        </p>

                    </form>

                </div>

            @endif

        </div>

    </section>

</div>



{{-- =========================================================
    STYLE
========================================================== --}}

@push('styles')

<style>

    .seller-packages-grid {
        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );
    }


    .seller-package-card {
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease,
            opacity .18s ease;
    }


    .seller-package-card:hover {
        transform:
            translateY(-3px);
    }


    .seller-package-card.is-selected {
        border-color:
            #12B76A !important;

        box-shadow:
            0 0 0 2px rgba(18,183,106,.08),
            0 14px 35px rgba(11,61,46,.08);
    }


    .seller-package-card.is-active-plan {
        border-color:
            #12B76A !important;

        background:
            linear-gradient(
                180deg,
                #F7FFF9 0%,
                #FFFFFF 100%
            );

        box-shadow:
            0 0 0 2px rgba(18,183,106,.08),
            0 16px 40px rgba(18,183,106,.10);
    }


    .active-plan-card {
        box-shadow:
            0 16px 45px rgba(18,183,106,.08);
    }


    @media (max-width: 980px) {

        .seller-packages-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }

    }


    @media (max-width: 650px) {

        .seller-packages-grid {
            grid-template-columns:
                1fr;
        }

    }

</style>

@endpush



{{-- =========================================================
    JAVASCRIPT
========================================================== --}}

@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Package Buttons
        |--------------------------------------------------------------------------
        |
        | Only enabled package buttons have:
        |
        | .verified-package-btn
        |
        | Therefore Active Plan / Locked buttons are automatically ignored.
        |
        */

        const packageButtons =
            document.querySelectorAll(
                '.verified-package-btn'
            );


        const packageCards =
            document.querySelectorAll(
                '[data-package-card]'
            );


        const selectedPackageLabel =
            document.getElementById(
                'selected-package'
            );


        const selectedPackageInput =
            document.getElementById(
                'selected-package-input'
            );


        const applicationSection =
            document.getElementById(
                'verified-application'
            );


        const loginButton =
            document.getElementById(
                'seller-login-button'
            );


        const verifiedSellerUrl =
            @json(
                route(
                    'verified-sellers'
                )
            );


        const loginUrl =
            @json(
                route(
                    'login'
                )
            );



        /*
        |--------------------------------------------------------------------------
        | Package Selection
        |--------------------------------------------------------------------------
        */

        packageButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const packageId =
                            button.dataset.packageId;


                        const packageName =
                            button.dataset.package;


                        const packagePrice =
                            button.dataset.price;


                        const theme =
                            button.dataset.theme
                            ||
                            'green';



                        /*
                        |--------------------------------------------------------------------------
                        | Hidden Package ID
                        |--------------------------------------------------------------------------
                        */

                        if (
                            selectedPackageInput
                        ) {

                            selectedPackageInput.value =
                                packageId;

                        }



                        /*
                        |--------------------------------------------------------------------------
                        | Selected Package Label
                        |--------------------------------------------------------------------------
                        */

                        if (
                            selectedPackageLabel
                        ) {

                            selectedPackageLabel.textContent =
                                packageName
                                +
                                ' · '
                                +
                                packagePrice;


                            selectedPackageLabel.className =
                                'mp-badge ml-1';


                            if (
                                theme ===
                                'purple'
                            ) {

                                selectedPackageLabel
                                    .classList
                                    .add(
                                        'mp-badge-purple'
                                    );

                            } else if (
                                theme ===
                                'slate'
                            ) {

                                selectedPackageLabel
                                    .classList
                                    .add(
                                        'mp-badge-slate'
                                    );

                            } else {

                                selectedPackageLabel
                                    .classList
                                    .add(
                                        'mp-badge-green'
                                    );

                            }

                        }



                        /*
                        |--------------------------------------------------------------------------
                        | Selected Card
                        |--------------------------------------------------------------------------
                        */

                        packageCards.forEach(
                            function (card) {

                                const selected =
                                    card.dataset.packageCard
                                    ===
                                    packageId;


                                card.classList.toggle(
                                    'is-selected',
                                    selected
                                );

                            }
                        );



                        /*
                        |--------------------------------------------------------------------------
                        | Button Labels
                        |--------------------------------------------------------------------------
                        */

                        packageButtons.forEach(
                            function (item) {

                                if (
                                    item.dataset.packageId
                                    ===
                                    packageId
                                ) {

                                    item.textContent =
                                        'Selected '
                                        +
                                        item.dataset.package;

                                } else {

                                    item.textContent =
                                        'Choose '
                                        +
                                        item.dataset.package;

                                }

                            }
                        );



                        /*
                        |--------------------------------------------------------------------------
                        | Guest Redirect
                        |--------------------------------------------------------------------------
                        */

                        if (
                            loginButton
                        ) {

                            const intendedUrl =
                                verifiedSellerUrl
                                +
                                '?package='
                                +
                                encodeURIComponent(
                                    packageId
                                )
                                +
                                '#verified-application';


                            loginButton.href =
                                loginUrl
                                +
                                '?redirect='
                                +
                                encodeURIComponent(
                                    intendedUrl
                                );

                        }



                        /*
                        |--------------------------------------------------------------------------
                        | Scroll To Application Form
                        |--------------------------------------------------------------------------
                        */

                        if (
                            applicationSection
                        ) {

                            applicationSection.scrollIntoView({
                                behavior:
                                    'smooth',

                                block:
                                    'start',
                            });

                        }

                    }
                );

            }
        );



        /*
        |--------------------------------------------------------------------------
        | Verification Documents
        |--------------------------------------------------------------------------
        */

        const fileInput =
            document.getElementById(
                'verification-documents'
            );


        const fileName =
            document.getElementById(
                'verification-file-name'
            );


        if (fileInput) {

            fileInput.addEventListener(
                'change',
                function () {

                    const files =
                        Array.from(
                            this.files
                            ||
                            []
                        );


                    if (!fileName) {

                        return;

                    }


                    if (
                        files.length ===
                        0
                    ) {

                        fileName.textContent =
                            '';

                        return;

                    }


                    if (
                        files.length ===
                        1
                    ) {

                        fileName.textContent =
                            files[0].name;

                        return;

                    }


                    fileName.textContent =
                        files.length
                        +
                        ' files selected';

                }
            );

        }

    }
);

</script>

@endpush


@endsection