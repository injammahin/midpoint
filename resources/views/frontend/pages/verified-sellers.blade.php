@extends('frontend.layouts.app')

@section('title', 'Become a Verified Seller | Midpoint')


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
    | Defensive Defaults
    |--------------------------------------------------------------------------
    */

    $latestApplication =
        $latestApplication
        ?? null;


    $latestSubscription =
        $latestSubscription
        ?? null;


    $approvedApplication =
        $approvedApplication
        ?? null;


    $activeSubscription =
        $activeSubscription
        ?? null;


    $pendingInvoice =
        $pendingInvoice
        ?? null;


    $latestPaidInvoice =
        $latestPaidInvoice
        ?? null;


    $sellerWallet =
        $sellerWallet
        ?? null;


    $sellerWalletBalance =
        round(
            (float) (
                $sellerWalletBalance
                ?? 0
            ),
            2
        );


    $canQuickRenew =
        (bool) (
            $canQuickRenew
            ?? false
        );



    /*
    |--------------------------------------------------------------------------
    | Paid Invoice Download
    |--------------------------------------------------------------------------
    */

    $downloadInvoice =
        $latestPaidInvoice;


    if (
        !$downloadInvoice
        &&
        $latestApplication
        &&
        $latestApplication->invoice
        &&
        $latestApplication->invoice->status === 'paid'
    ) {

        $downloadInvoice =
            $latestApplication->invoice;
    }



    /*
    |--------------------------------------------------------------------------
    | Application Status
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
    */

    $hasActiveSubscription =
        !is_null(
            $activeSubscription
        );



    /*
    |--------------------------------------------------------------------------
    | Quick Renewal
    |--------------------------------------------------------------------------
    */

    $quickRenewAvailable =
        auth()->check()
        &&
        !$hasActiveSubscription
        &&
        $canQuickRenew;



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
        (
            $quickRenewAvailable

            ||

            (
                $applicationStatus === 'expired'
                &&
                $approvedApplication
                &&
                $latestSubscription
            )
        );



    /*
    |--------------------------------------------------------------------------
    | Can Submit New Seller Application
    |--------------------------------------------------------------------------
    */

    $canApply =
        !$hasActiveSubscription
        &&
        !$isExpiredSeller
        &&
        (
            !$latestApplication

            ||

            in_array(
                $applicationStatus,
                [
                    'revision_required',
                    'superseded',
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
    | Pending Invoice
    |--------------------------------------------------------------------------
    */

    $isPaymentPending =
        auth()->check()
        &&
        $pendingInvoice
        &&
        $pendingInvoice->status === 'unpaid';



    /*
    |--------------------------------------------------------------------------
    | Active Seller
    |--------------------------------------------------------------------------
    */

    $isActiveSeller =
        auth()->check()
        &&
        $hasActiveSubscription;

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

                    <div class="flex items-start gap-3">

                        <div
                            class="
                                grid
                                h-8
                                w-8
                                flex-none
                                place-items-center
                                rounded-full
                                bg-[#FFE4E6]
                                text-[#B42318]
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-circle-exclamation
                                "
                            ></i>

                        </div>


                        <div>

                            <strong
                                class="
                                    block
                                    text-[13px]
                                    text-[#B42318]
                                "
                            >

                                Payment could not be completed

                            </strong>


                            <p
                                class="
                                    mt-1
                                    text-[12px]
                                    leading-5
                                    text-[#B42318]
                                "
                            >

                                {{ session('error') }}

                            </p>

                        </div>

                    </div>

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

                        <i
                            class="
                                fa-solid
                                fa-circle-exclamation
                            "
                        ></i>

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
                            'Once approved, Midpoint creates your package invoice. Payment is required before seller activation.',
                    ],

                    [
                        'number' => '3',

                        'title' =>
                            'List & get buyers',

                        'text' =>
                            'After activation, list products within your package limit and connect with buyers through Midpoint.',
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

                        {{-- LEFT --}}
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
                                                (float) (
                                                    $activeSubscription->package_price
                                                    ?:
                                                    $activeSubscription->price
                                                ),
                                                0
                                            )
                                        }}

                                        /{{ $activeSubscription->billing_period }}

                                    </span>

                                </div>

                            </div>

                        </div>



                        {{-- RIGHT --}}
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

                        You can upgrade to a higher-priced package at any time.
                        Midpoint automatically credits the unused value of
                        your current plan toward the upgrade.

                        Downgrades are not available.

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

                    <div class="flex items-start gap-4">

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

                            <i
                                class="
                                    fa-solid
                                    fa-clock-rotate-left
                                "
                            ></i>

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

                                Because Midpoint has already approved your seller
                                verification, you do not need to submit your
                                business information or verification documents again.

                                Renew the same package or upgrade to a
                                higher-priced package below.

                                Downgrades are not allowed.

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
                            | Package Theme
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
                            | Selected Package
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
                            | Current Active Plan
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
                            | Reference Subscription
                            |--------------------------------------------------------------------------
                            |
                            | Active seller:
                            | current active subscription
                            |
                            | Expired seller:
                            | latest historical subscription
                            |
                            */

                            $referenceSubscription =
                                $hasActiveSubscription

                                    ? $activeSubscription

                                    : (
                                        $isExpiredSeller
                                            ? $latestSubscription
                                            : null
                                    );



                            /*
                            |--------------------------------------------------------------------------
                            | Reference Price
                            |--------------------------------------------------------------------------
                            */

                            $referencePackagePrice =
                                $referenceSubscription

                                    ? (float) (
                                        $referenceSubscription->package_price
                                        ?:
                                        $referenceSubscription->price
                                    )

                                    : 0;



                            /*
                            |--------------------------------------------------------------------------
                            | Selected Card Price
                            |--------------------------------------------------------------------------
                            */

                            $packagePrice =
                                (float) $package->price;



                            /*
                            |--------------------------------------------------------------------------
                            | Same Package
                            |--------------------------------------------------------------------------
                            */

                            $isSameReferencePackage =
                                $referenceSubscription
                                &&
                                (int) $referenceSubscription->seller_package_id
                                ===
                                (int) $package->id;



                            /*
                            |--------------------------------------------------------------------------
                            | Higher Package
                            |--------------------------------------------------------------------------
                            */

                            $isHigherPackage =
                                $referenceSubscription
                                &&
                                !$isSameReferencePackage
                                &&
                                $packagePrice
                                >
                                $referencePackagePrice;



                            /*
                            |--------------------------------------------------------------------------
                            | Downgrade
                            |--------------------------------------------------------------------------
                            */

                            $isBlockedByNoDowngrade =
                                $referenceSubscription
                                &&
                                !$isSameReferencePackage
                                &&
                                $packagePrice
                                <=
                                $referencePackagePrice;



                            /*
                            |--------------------------------------------------------------------------
                            | Active Seller Upgrade
                            |--------------------------------------------------------------------------
                            */

                            $canUpgradeWhileActive =
                                $hasActiveSubscription
                                &&
                                $isHigherPackage;



                            /*
                            |--------------------------------------------------------------------------
                            | Expired Seller Renewal / Upgrade
                            |--------------------------------------------------------------------------
                            */

                            $canRenewOrUpgradeAfterExpiry =
                                $isExpiredSeller
                                &&
                                (
                                    $isSameReferencePackage
                                    ||
                                    $isHigherPackage
                                );



                            /*
                            |--------------------------------------------------------------------------
                            | Show Renewal / Upgrade Form
                            |--------------------------------------------------------------------------
                            */

                            $showRenewUpgrade =
                                $canUpgradeWhileActive
                                ||
                                $canRenewOrUpgradeAfterExpiry;



                            /*
                            |--------------------------------------------------------------------------
                            | Locked Card
                            |--------------------------------------------------------------------------
                            */

                            $packageLocked =
                                $isBlockedByNoDowngrade;

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

                                {{
                                    $packageLocked
                                        ? 'is-locked-plan'
                                        : ''
                                }}
                            "

                            data-package-card="{{ $package->id }}"
                        >


                            {{-- MOST POPULAR --}}
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



                            {{-- CURRENT PLAN --}}
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

                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-check
                                        "
                                    ></i>

                                    Current plan

                                </span>

                            @endif



                            {{-- PACKAGE BADGE --}}
                            <span
                                class="
                                    mp-badge
                                    {{ $badgeClass }}
                                "
                            >

                                {{ $package->name }}

                            </span>



                            {{-- PRICE --}}
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



                            {{-- DESCRIPTION --}}
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



                            {{-- FEATURES --}}
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
                                PACKAGE ACTION
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

                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-check
                                        "
                                    ></i>

                                    Active Plan

                                </button>



                            @elseif($packageLocked)

                                <button
                                    type="button"
                                    disabled

                                    title="
                                        Package downgrades are not allowed.
                                        You can renew your current package or
                                        upgrade to a higher-priced package.
                                    "

                                    class="
                                        mp-btn
                                        mp-btn-outline
                                        mt-auto
                                        cursor-not-allowed
                                        opacity-60
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-arrow-down-long
                                        "
                                    ></i>

                                     Unavailable

                                </button>



                            @elseif($showRenewUpgrade)

                                @php

                                    if ($isSameReferencePackage) {

                                        $renewButtonText =
                                            'Renew '
                                            .
                                            $package->name;


                                        $renewButtonIcon =
                                            'fa-rotate';

                                    } else {

                                        $renewButtonText =
                                            'Upgrade to '
                                            .
                                            $package->name;


                                        $renewButtonIcon =
                                            'fa-arrow-trend-up';
                                    }

                                @endphp


                                <form
                                    method="POST"

                                    action="{{
                                        route(
                                            'seller-subscriptions.renew'
                                        )
                                    }}"

                                    class="
                                        seller-package-change-form
                                        mt-auto
                                    "
                                >

                                    @csrf


                                    <input
                                        type="hidden"
                                        name="seller_package_id"
                                        value="{{ $package->id }}"
                                    >


                                    <button
                                        type="submit"
                                        class="
                                            mp-btn
                                            {{ $buttonClass }}
                                            w-full
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                {{ $renewButtonIcon }}
                                            "
                                        ></i>

                                        {{ $renewButtonText }}

                                    </button>

                                </form>



                            @else

                                {{-- FIRST-TIME SELLER PACKAGE SELECTION --}}

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

                                    @if($isSelected)

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

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | Purchase Type
                    |--------------------------------------------------------------------------
                    */

                    $invoicePurchaseType =
                        $pendingInvoice->purchase_type
                        ?:
                        'initial';



                    /*
                    |--------------------------------------------------------------------------
                    | Package Name
                    |--------------------------------------------------------------------------
                    */

                    $invoicePackageName =
                        $pendingInvoice->package_name

                        ?:

                        (
                            $pendingInvoice->package
                                ?->name

                            ?:

                            (
                                $latestSubscription
                                    ?->package_name

                                ?:

                                (
                                    $latestApplication
                                        ?->package_name

                                    ?:

                                    'Seller Package'
                                )
                            )
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Product Limit
                    |--------------------------------------------------------------------------
                    */

                    $invoiceProductLimit =
                        (int) (
                            $pendingInvoice->product_limit

                            ?:

                            (
                                $pendingInvoice->package
                                    ?->product_limit

                                ?:

                                (
                                    $latestSubscription
                                        ?->product_limit

                                    ?:

                                    (
                                        $latestApplication
                                            ?->product_limit

                                        ?:

                                        0
                                    )
                                )
                            )
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Business Name
                    |--------------------------------------------------------------------------
                    */

                    $invoiceBusinessName =
                        $approvedApplication
                            ?->business_name

                        ?:

                        (
                            $latestApplication
                                ?->business_name

                            ?:

                            'Seller Business'
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Recurring Invoice
                    |--------------------------------------------------------------------------
                    |
                    | Wallet payment is available only for:
                    |
                    | renewal
                    | upgrade
                    |
                    */

                    $isRecurringInvoice =
                        in_array(
                            $invoicePurchaseType,
                            [
                                'renewal',
                                'upgrade',
                            ],
                            true
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Invalid Historical Downgrade Invoice
                    |--------------------------------------------------------------------------
                    */

                    $isDowngradeInvoice =
                        $invoicePurchaseType
                        ===
                        'downgrade';



                    /*
                    |--------------------------------------------------------------------------
                    | Full Target Package Price
                    |--------------------------------------------------------------------------
                    */

                    $invoicePackagePrice =
                        round(
                            (float) (
                                $pendingInvoice->package_price

                                ?:

                                $pendingInvoice->amount
                            ),
                            2
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Proration Credit
                    |--------------------------------------------------------------------------
                    */

                    $invoiceProrationCredit =
                        round(
                            (float) (
                                $pendingInvoice->proration_credit
                                ?? 0
                            ),
                            2
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Used Current-Plan Value
                    |--------------------------------------------------------------------------
                    */

                    $invoiceProrationUsedAmount =
                        round(
                            (float) (
                                $pendingInvoice->proration_used_amount
                                ?? 0
                            ),
                            2
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Previous Package
                    |--------------------------------------------------------------------------
                    */

                    $previousPackageName =
                        $pendingInvoice
                            ->renewalOfSubscription
                            ?->package_name

                        ?:

                        (
                            $latestSubscription
                                ?->package_name

                            ?:

                            'Current package'
                        );



                    /*
                    |--------------------------------------------------------------------------
                    | Wallet Availability
                    |--------------------------------------------------------------------------
                    */

                    $walletCanPay =
                        $isRecurringInvoice
                        &&
                        $sellerWalletBalance
                        >=
                        (float) $pendingInvoice->amount;



                    /*
                    |--------------------------------------------------------------------------
                    | Wallet Shortfall
                    |--------------------------------------------------------------------------
                    */

                    $walletShortfall =
                        max(
                            0,
                            round(
                                (float) $pendingInvoice->amount
                                -
                                $sellerWalletBalance,
                                2
                            )
                        );

                @endphp



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


                    {{-- =================================================
                        INVOICE HEADER
                    ================================================== --}}

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

                                @if($invoicePurchaseType === 'renewal')

                                    Renewal ready

                                @elseif($invoicePurchaseType === 'upgrade')

                                    Upgrade ready

                                @elseif($invoicePurchaseType === 'downgrade')

                                    Payment unavailable

                                @else

                                    Application approved

                                @endif

                            </span>


                            <h2
                                class="
                                    mt-3
                                    font-['Bricolage_Grotesque']
                                    text-[22px]
                                    font-bold
                                "
                            >

                                @if($invoicePurchaseType === 'renewal')

                                    Seller Package Renewal Invoice

                                @elseif($invoicePurchaseType === 'upgrade')

                                    Seller Package Upgrade Invoice

                                @elseif($invoicePurchaseType === 'downgrade')

                                    Invalid Seller Package Invoice

                                @else

                                    Seller Package Invoice

                                @endif

                            </h2>


                            <p
                                class="
                                    mp-small
                                    mp-muted
                                    mt-1
                                "
                            >

                                @if($invoicePurchaseType === 'upgrade')

                                    Complete payment to activate
                                    your upgraded package immediately.

                                @elseif($invoicePurchaseType === 'renewal')

                                    Complete payment and your seller package
                                    will reactivate automatically.

                                @elseif($invoicePurchaseType === 'downgrade')

                                    Package downgrades are no longer allowed.

                                @else

                                    Complete payment to activate
                                    your verified seller account.

                                @endif

                            </p>

                        </div>


                        <div class="text-right max-sm:text-left">

                            <span class="mp-small mp-muted">

                                Invoice

                            </span>


                            <strong
                                class="
                                    mt-1
                                    block
                                    text-[12px]
                                "
                            >

                                {{ $pendingInvoice->invoice_number }}

                            </strong>

                        </div>

                    </div>



                    {{-- =================================================
                        INVOICE SUMMARY
                    ================================================== --}}

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

                                {{ $invoiceBusinessName }}

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

                                {{ $invoicePackageName }}

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

                                Purchase type

                            </span>


                            <strong class="capitalize">

                                {{
                                    str_replace(
                                        '_',
                                        ' ',
                                        $invoicePurchaseType
                                    )
                                }}

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
                                        $invoiceProductLimit
                                    )
                                }}

                                products

                            </strong>

                        </div>



                        {{-- =============================================
                            UPGRADE PRORATION
                        ============================================== --}}

                        @if($invoiceProrationCredit > 0)

                            <div
                                class="
                                    mt-3
                                    border-t
                                    border-[#DCE5E0]
                                    pt-3
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

                                        {{ $invoicePackageName }}
                                        full price

                                    </span>


                                    <strong>

                                        ₦{{
                                            number_format(
                                                $invoicePackagePrice,
                                                2
                                            )
                                        }}

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

                                        {{ $previousPackageName }}
                                        value already used

                                    </span>


                                    <strong>

                                        ₦{{
                                            number_format(
                                                $invoiceProrationUsedAmount,
                                                2
                                            )
                                        }}

                                    </strong>

                                </div>


                                <div
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        py-2
                                        text-[#067647]
                                    "
                                >

                                    <span>

                                        Unused
                                        {{ $previousPackageName }}
                                        credit

                                    </span>


                                    <strong>

                                        -₦{{
                                            number_format(
                                                $invoiceProrationCredit,
                                                2
                                            )
                                        }}

                                    </strong>

                                </div>


                                <div
                                    class="
                                        mt-2
                                        rounded-[10px]
                                        border
                                        border-[#ABEFC6]
                                        bg-[#ECFDF3]
                                        px-3
                                        py-2
                                        text-[10px]
                                        leading-5
                                        text-[#067647]
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-info
                                            mr-1
                                        "
                                    ></i>

                                    Midpoint calculated the unused value from
                                    the remaining time on your current package
                                    and automatically applied it to this upgrade.

                                </div>

                            </div>

                        @endif



                        {{-- PAYMENT DUE --}}
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
                                            ->format(
                                                'd M Y'
                                            )
                                    }}

                                </strong>

                            </div>

                        @endif



                        {{-- TOTAL --}}
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
                                        2
                                    )
                                }}

                            </strong>

                        </div>

                    </div>



                    {{-- =================================================
                        INVALID DOWNGRADE INVOICE
                    ================================================== --}}

                    @if($isDowngradeInvoice)

                        <div
                            class="
                                rounded-[16px]
                                border
                                border-[#FECDD3]
                                bg-[#FFF1F2]
                                p-5
                            "
                        >

                            <div class="flex items-start gap-3">

                                <div
                                    class="
                                        grid
                                        h-10
                                        w-10
                                        flex-none
                                        place-items-center
                                        rounded-full
                                        bg-[#FFE4E6]
                                        text-[#B42318]
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-ban
                                        "
                                    ></i>

                                </div>


                                <div>

                                    <strong
                                        class="
                                            block
                                            text-[14px]
                                            text-[#B42318]
                                        "
                                    >

                                        This package change cannot be paid

                                    </strong>


                                    <p
                                        class="
                                            mt-1
                                            text-[11px]
                                            leading-5
                                            text-[#B42318]
                                        "
                                    >

                                        Midpoint does not allow package
                                        downgrades. Renew your current package
                                        or choose a higher-priced package.

                                    </p>

                                </div>

                            </div>

                        </div>



                    {{-- =================================================
                        RECURRING PAYMENT
                        WALLET + PAYSTACK
                    ================================================== --}}

                    @elseif($isRecurringInvoice)

                        <div
                            class="
                                rounded-[16px]
                                border
                                border-[#DCE5E0]
                                bg-[#F8FBF9]
                                p-5
                            "
                        >

                            <div class="flex items-start gap-3">

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
                                            fa-wallet
                                        "
                                    ></i>

                                </div>


                                <div class="min-w-0 flex-1">

                                    <strong
                                        class="
                                            block
                                            text-[14px]
                                            text-[#0B3D2E]
                                        "
                                    >

                                        Choose how you want to pay

                                    </strong>


                                    <p
                                        class="
                                            mp-small
                                            mp-muted
                                            mt-1
                                            leading-5
                                        "
                                    >

                                        As an existing seller, you can pay this

                                        {{
                                            $invoicePurchaseType === 'upgrade'
                                                ? 'upgrade'
                                                : 'renewal'
                                        }}

                                        using your Midpoint Wallet or continue
                                        with Paystack.

                                    </p>


                                    <div
                                        class="
                                            mt-3
                                            flex
                                            flex-wrap
                                            items-center
                                            gap-2
                                        "
                                    >

                                        <span
                                            class="
                                                mp-badge
                                                mp-badge-green
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-wallet
                                                    mr-1
                                                "
                                            ></i>

                                            Wallet:

                                            ₦{{
                                                number_format(
                                                    $sellerWalletBalance,
                                                    2
                                                )
                                            }}

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
                                                    fa-shield-halved
                                                    mr-1
                                                "
                                            ></i>

                                            Paystack available

                                        </span>

                                    </div>

                                </div>

                            </div>


                            <button
                                type="button"
                                id="open-package-payment-modal"
                                class="
                                    mp-btn
                                    mp-btn-primary
                                    mp-btn-lg
                                    mt-5
                                    w-full
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-money-check-dollar
                                    "
                                ></i>

                                Choose Payment Method

                            </button>


                            @if(!$walletCanPay)

                                <p
                                    class="
                                        mp-small
                                        mp-muted
                                        mt-3
                                        text-center
                                        leading-5
                                    "
                                >

                                    Your available wallet balance is

                                    <strong>

                                        ₦{{
                                            number_format(
                                                $sellerWalletBalance,
                                                2
                                            )
                                        }}

                                    </strong>.

                                    You need

                                    <strong class="text-[#B54708]">

                                        ₦{{
                                            number_format(
                                                $walletShortfall,
                                                2
                                            )
                                        }}

                                    </strong>

                                    more to pay from your wallet.

                                    Paystack remains available.

                                </p>

                            @endif

                        </div>



                        {{-- =============================================
                            PAYMENT METHOD MODAL
                        ============================================== --}}

                        <div
                            id="package-payment-modal"

                            class="
                                package-payment-modal
                            "

                            data-auto-open="{{
                                session(
                                    'open_package_payment_modal'
                                )
                                    ? '1'
                                    : '0'
                            }}"

                            aria-hidden="true"

                            role="dialog"

                            aria-modal="true"

                            aria-labelledby="
                                package-payment-modal-title
                            "
                        >


                            {{-- OVERLAY --}}
                            <button
                                type="button"

                                class="
                                    package-payment-modal-overlay
                                "

                                data-close-package-payment-modal

                                aria-label="
                                    Close payment modal
                                "
                            ></button>



                            {{-- DIALOG --}}
                            <div
                                class="
                                    package-payment-modal-dialog
                                "
                            >


                                {{-- HEADER --}}
                                <div
                                    class="
                                        package-payment-modal-header
                                    "
                                >

                                    <div>

                                        <span
                                            class="
                                                mp-badge
                                                mp-badge-green
                                            "
                                        >

                                            {{
                                                ucfirst(
                                                    $invoicePurchaseType
                                                )
                                            }}

                                            payment

                                        </span>


                                        <h3
                                            id="
                                                package-payment-modal-title
                                            "

                                            class="
                                                package-payment-modal-title
                                            "
                                        >

                                            Pay

                                            ₦{{
                                                number_format(
                                                    (float)
                                                    $pendingInvoice->amount,
                                                    2
                                                )
                                            }}

                                        </h3>


                                        <p
                                            class="
                                                mp-small
                                                mp-muted
                                                mt-1
                                            "
                                        >

                                            Invoice

                                            {{
                                                $pendingInvoice
                                                    ->invoice_number
                                            }}

                                            ·

                                            {{ $invoicePackageName }}

                                        </p>

                                    </div>


                                    <button
                                        type="button"

                                        class="
                                            package-payment-modal-close
                                        "

                                        data-close-package-payment-modal

                                        aria-label="
                                            Close payment modal
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-xmark
                                            "
                                        ></i>

                                    </button>

                                </div>



                                {{-- PRORATION NOTICE --}}
                                @if($invoiceProrationCredit > 0)

                                    <div
                                        class="
                                            package-proration-notice
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-circle-check
                                                mr-1
                                            "
                                        ></i>


                                        <strong>

                                            ₦{{
                                                number_format(
                                                    $invoiceProrationCredit,
                                                    2
                                                )
                                            }}

                                            unused-plan credit applied.

                                        </strong>


                                        You are paying only the remaining
                                        upgrade amount instead of paying
                                        the full

                                        {{ $invoicePackageName }}

                                        price again.

                                    </div>

                                @endif



                                {{-- PAYMENT METHODS --}}
                                <div
                                    class="
                                        package-payment-method-grid
                                    "
                                >


                                    {{-- =====================================
                                        MIDPOINT WALLET
                                    ====================================== --}}

                                    <div
                                        class="
                                            package-payment-option
                                            {{
                                                $walletCanPay
                                                    ? 'wallet-available'
                                                    : 'wallet-unavailable'
                                            }}
                                        "
                                    >

                                        <div
                                            class="
                                                package-payment-icon
                                                wallet-icon
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-wallet
                                                "
                                            ></i>

                                        </div>


                                        <h4
                                            class="
                                                package-payment-option-title
                                            "
                                        >

                                            Midpoint Wallet

                                        </h4>


                                        <p class="mp-small mp-muted">

                                            Available balance

                                        </p>


                                        <strong
                                            class="
                                                package-wallet-balance
                                            "
                                        >

                                            ₦{{
                                                number_format(
                                                    $sellerWalletBalance,
                                                    2
                                                )
                                            }}

                                        </strong>


                                        @if($walletCanPay)

                                            <p
                                                class="
                                                    package-wallet-status
                                                    wallet-status-success
                                                "
                                            >

                                                Your wallet has enough
                                                available balance.

                                                ₦{{
                                                    number_format(
                                                        (float)
                                                        $pendingInvoice->amount,
                                                        2
                                                    )
                                                }}

                                                will be deducted immediately
                                                and recorded in your wallet
                                                transaction history.

                                            </p>


                                            <form
                                                method="POST"

                                                action="{{
                                                    route(
                                                        'seller-invoices.wallet-pay',
                                                        $pendingInvoice
                                                    )
                                                }}"

                                                class="
                                                    package-payment-form
                                                    mt-auto
                                                "
                                            >

                                                @csrf


                                                <button
                                                    type="submit"
                                                    class="
                                                        mp-btn
                                                        mp-btn-green
                                                        w-full
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fa-solid
                                                            fa-wallet
                                                        "
                                                    ></i>


                                                    Pay

                                                    ₦{{
                                                        number_format(
                                                            (float)
                                                            $pendingInvoice->amount,
                                                            2
                                                        )
                                                    }}

                                                    from Wallet

                                                </button>

                                            </form>


                                        @else

                                            <p
                                                class="
                                                    package-wallet-status
                                                    wallet-status-error
                                                "
                                            >

                                                Insufficient available balance.

                                                You need

                                                <strong>

                                                    ₦{{
                                                        number_format(
                                                            $walletShortfall,
                                                            2
                                                        )
                                                    }}

                                                </strong>

                                                more to use your
                                                Midpoint Wallet.

                                            </p>


                                            <button
                                                type="button"
                                                disabled
                                                class="
                                                    mp-btn
                                                    mp-btn-outline
                                                    mt-auto
                                                    w-full
                                                    cursor-not-allowed
                                                    opacity-60
                                                "
                                            >

                                                <i
                                                    class="
                                                        fa-solid
                                                        fa-lock
                                                    "
                                                ></i>

                                                Wallet balance too low

                                            </button>

                                        @endif

                                    </div>



                                    {{-- =====================================
                                        PAYSTACK
                                    ====================================== --}}

                                    <div
                                        class="
                                            package-payment-option
                                            paystack-option
                                        "
                                    >

                                        <div
                                            class="
                                                package-payment-icon
                                                paystack-icon
                                            "
                                        >

                                            <i
                                                class="
                                                    fa-solid
                                                    fa-shield-halved
                                                "
                                            ></i>

                                        </div>


                                        <h4
                                            class="
                                                package-payment-option-title
                                            "
                                        >

                                            Paystack Checkout

                                        </h4>


                                        <p
                                            class="
                                                mp-small
                                                mp-muted
                                                leading-5
                                            "
                                        >

                                            Continue with the existing
                                            secure Paystack checkout.

                                            Paystack will display the
                                            supported payment methods
                                            available for your account.

                                        </p>



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
                                                    package-test-mode
                                                "
                                            >

                                                <strong>

                                                    Test mode:

                                                </strong>

                                                your current Paystack
                                                test keys will process
                                                a simulated payment.

                                            </div>

                                        @endif


                                        <form
                                            method="POST"

                                            action="{{
                                                route(
                                                    'seller-invoices.pay',
                                                    $pendingInvoice
                                                )
                                            }}"

                                            class="
                                                package-payment-form
                                                mt-auto
                                            "
                                        >

                                            @csrf


                                            <button
                                                type="submit"
                                                class="
                                                    mp-btn
                                                    mp-btn-primary
                                                    w-full
                                                "
                                            >

                                                <i
                                                    class="
                                                        fa-solid
                                                        fa-arrow-up-right-from-square
                                                    "
                                                ></i>


                                                Pay

                                                ₦{{
                                                    number_format(
                                                        (float)
                                                        $pendingInvoice->amount,
                                                        2
                                                    )
                                                }}

                                                with Paystack

                                            </button>

                                        </form>

                                    </div>

                                </div>



                                {{-- MODAL FOOTER --}}
                                <div
                                    class="
                                        package-payment-modal-footer
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-shield
                                            mr-1
                                        "
                                    ></i>

                                    Both payment methods use the same
                                    Midpoint seller-package activation flow.

                                    After payment, your package is activated
                                    and your paid PDF invoice is emailed to you.

                                </div>

                            </div>

                        </div>



                    {{-- =================================================
                        INITIAL PACKAGE PAYMENT
                        EXISTING PAYSTACK FLOW
                    ================================================== --}}

                    @else

                        <div
                            class="
                                rounded-[16px]
                                border
                                border-[#DCE5E0]
                                bg-[#F8FBF9]
                                p-5
                            "
                        >

                            <div class="flex items-start gap-3">

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

                                        Your first seller package must
                                        be paid through Paystack.

                                        Continue to Paystack's secure checkout.

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


                                    Your current test keys will process
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



                            {{-- PAYSTACK FORM --}}
                            <form
                                method="POST"

                                action="{{
                                    route(
                                        'seller-invoices.pay',
                                        $pendingInvoice
                                    )
                                }}"

                                class="
                                    package-payment-form
                                    mt-5
                                "
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


                                    Pay

                                    ₦{{
                                        number_format(
                                            (float)
                                            $pendingInvoice->amount,
                                            2
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

                                Card details are entered on Paystack's
                                checkout, not on Midpoint.

                                After successful verification,
                                your seller package is activated
                                automatically and a confirmation email
                                with a PDF invoice is sent to you.

                            </p>

                        </div>

                    @endif

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


                    <span
                        class="
                            mp-badge
                            mp-badge-green
                        "
                    >

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



                    {{-- REMAINING TIME --}}
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

                            <span
                                class="
                                    mp-small
                                    mp-muted
                                "
                            >

                                Days remaining

                            </span>


                            <strong
                                class="
                                    text-[13px]
                                    text-[#087443]
                                "
                            >

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

                                <span
                                    class="
                                        mp-small
                                        mp-muted
                                    "
                                >

                                    Started

                                </span>


                                <strong class="text-[12px]">

                                    {{
                                        $activeSubscription
                                            ->started_at
                                            ->format(
                                                'd M Y, h:i A'
                                            )
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

                                <span
                                    class="
                                        mp-small
                                        mp-muted
                                    "
                                >

                                    Expires

                                </span>


                                <strong class="text-[12px]">

                                    {{
                                        $activeSubscription
                                            ->expires_at
                                            ->format(
                                                'd M Y, h:i A'
                                            )
                                    }}

                                </strong>

                            </div>

                        @endif

                    </div>



                    {{-- ACTIONS --}}
                    <div
                        class="
                            mt-6
                            flex
                            flex-wrap
                            items-center
                            justify-center
                            gap-3
                        "
                    >

                        <a
                            href="{{
                                route(
                                    'seller.products'
                                )
                            }}"
                            class="
                                mp-btn
                                mp-btn-primary
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-bag-shopping
                                "
                            ></i>

                            Manage Products

                        </a>


                        @if($downloadInvoice)

                            <a
                                href="{{
                                    route(
                                        'seller-invoices.download',
                                        $downloadInvoice
                                    )
                                }}"
                                class="
                                    mp-btn
                                    mp-btn-outline
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


                    <span
                        class="
                            mp-badge
                            mp-badge-slate
                        "
                    >

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

                        Midpoint is reviewing your seller
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

                            <span
                                class="
                                    mp-small
                                    mp-muted
                                "
                            >

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

                            <span
                                class="
                                    mp-small
                                    mp-muted
                                "
                            >

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

                            <i
                                class="
                                    fa-solid
                                    fa-rotate-left
                                "
                            ></i>

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

                        @if($isRevisionRequired)

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
                                        (float)
                                        $selectedPackage->price,
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

                        action="{{
                            route(
                                'seller-applications.store'
                            )
                        }}"

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


                            {{-- BUSINESS NAME --}}
                            <div class="mp-field">

                                <label>

                                    Business name

                                </label>


                                <input
                                    type="text"

                                    name="business_name"

                                    value="{{
                                        old(
                                            'business_name'
                                        )
                                    }}"

                                    placeholder="
                                        e.g. Temi Gadgets
                                    "

                                    required
                                >

                            </div>



                            {{-- CATEGORY --}}
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



                            {{-- LOCATION --}}
                            <div class="mp-field">

                                <label>

                                    Location

                                </label>


                                <input
                                    type="text"

                                    name="location"

                                    value="{{
                                        old(
                                            'location'
                                        )
                                    }}"

                                    placeholder="
                                        e.g. Ikeja, Lagos
                                    "

                                    required
                                >

                            </div>



                            {{-- PHONE --}}
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

                                    placeholder="
                                        0803 xxx xxxx
                                    "

                                    required
                                >

                            </div>



                            {{-- CAC / BVN --}}
                            <div class="mp-field">

                                <label>

                                    CAC number
                                    (or BVN for individuals)

                                </label>


                                <input
                                    type="text"

                                    name="cac_or_bvn"

                                    value="{{
                                        old(
                                            'cac_or_bvn'
                                        )
                                    }}"

                                    placeholder="
                                        RC1234567
                                    "

                                    required
                                >

                            </div>



                            {{-- STORE LINK --}}
                            <div class="mp-field">

                                <label>

                                    Existing store link

                                </label>


                                <input
                                    type="url"

                                    name="store_link"

                                    value="{{
                                        old(
                                            'store_link'
                                        )
                                    }}"

                                    placeholder="
                                        https://instagram.com/yourstore
                                    "
                                >

                            </div>

                        </div>



                        {{-- DESCRIPTION --}}
                        <div class="mp-field">

                            <label>

                                Short business description

                            </label>


                            <textarea
                                name="description"

                                rows="4"

                                placeholder="
                                    What do you sell, and what makes buyers trust you?
                                "

                                required
                            >{{ old('description') }}</textarea>

                        </div>



                        {{-- DOCUMENTS --}}
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

                                accept="
                                    .jpg,
                                    .jpeg,
                                    .png,
                                    .pdf
                                "
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

                                    <i
                                        class="
                                            fa-solid
                                            fa-paperclip
                                        "
                                    ></i>


                                    <span>

                                        CAC certificate,
                                        valid ID,
                                        or utility bill

                                    </span>


                                    <strong
                                        class="
                                            text-[#12B76A]
                                        "
                                    >

                                        browse

                                    </strong>

                                </div>


                                <div
                                    id="verification-file-name"
                                    class="mt-2 text-xs"
                                ></div>

                            </label>


                            <small
                                class="
                                    mp-muted
                                    mt-2
                                    block
                                "
                            >

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
                                                $guestIntendedUrl,
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

                                <i
                                    class="
                                        fa-solid
                                        fa-right-to-bracket
                                    "
                                ></i>

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


                            {{-- =============================================
                                EMAIL NOT VERIFIED
                            ============================================== --}}

                            @if(!auth()->user()->hasVerifiedEmail())

                                <a
                                    href="{{
                                        route(
                                            'verification.notice'
                                        )
                                    }}"

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

                                    Your Midpoint email address
                                    must be verified first.

                                </p>



                            @else


                                {{-- =============================================
                                    VERIFIED USER
                                ============================================== --}}

                                @if($selectedPackage)

                                    <button
                                        type="submit"

                                        id="
                                            submit-seller-application
                                        "

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
                                                fa-paper-plane
                                            "
                                        ></i>


                                        @if($isRevisionRequired)

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

    /*
    |--------------------------------------------------------------------------
    | Package Grid
    |--------------------------------------------------------------------------
    */

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


    .seller-package-card.is-locked-plan {
        background:
            linear-gradient(
                180deg,
                #FAFBFA 0%,
                #FFFFFF 100%
            );
    }


    .active-plan-card {
        box-shadow:
            0 16px 45px rgba(18,183,106,.08);
    }



    /*
    |--------------------------------------------------------------------------
    | Package Payment Modal
    |--------------------------------------------------------------------------
    */

    body.package-payment-modal-open {
        overflow:
            hidden;
    }


    .package-payment-modal {
        display:
            none;

        position:
            fixed;

        inset:
            0;

        z-index:
            99999;

        align-items:
            center;

        justify-content:
            center;

        padding:
            20px;
    }


    .package-payment-modal.is-open {
        display:
            flex;
    }


    .package-payment-modal-overlay {
        position:
            absolute;

        inset:
            0;

        width:
            100%;

        height:
            100%;

        border:
            0;

        background:
            rgba(7, 29, 22, .64);

        backdrop-filter:
            blur(4px);

        cursor:
            default;
    }


    .package-payment-modal-dialog {
        position:
            relative;

        z-index:
            1;

        width:
            100%;

        max-width:
            760px;

        max-height:
            calc(100vh - 40px);

        overflow-y:
            auto;

        border:
            1px solid #DCE5E0;

        border-radius:
            20px;

        background:
            #FFFFFF;

        box-shadow:
            0 28px 80px
            rgba(11, 61, 46, .24);

        animation:
            packagePaymentModalIn
            .18s ease;
    }


    @keyframes packagePaymentModalIn {

        from {
            opacity:
                0;

            transform:
                translateY(10px)
                scale(.985);
        }

        to {
            opacity:
                1;

            transform:
                translateY(0)
                scale(1);
        }

    }


    .package-payment-modal-header {
        display:
            flex;

        align-items:
            flex-start;

        justify-content:
            space-between;

        gap:
            18px;

        padding:
            24px 24px 18px;

        border-bottom:
            1px solid #E3EAE6;
    }


    .package-payment-modal-title {
        margin:
            10px 0 4px;

        color:
            #101915;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            22px;

        font-weight:
            800;
    }


    .package-payment-modal-close {
        display:
            grid;

        width:
            38px;

        height:
            38px;

        flex:
            none;

        place-items:
            center;

        border:
            1px solid #DCE5E0;

        border-radius:
            10px;

        background:
            #FFFFFF;

        color:
            #34423B;

        cursor:
            pointer;

        transition:
            background .15s ease,
            border-color .15s ease;
    }


    .package-payment-modal-close:hover {
        background:
            #F5F8F6;

        border-color:
            #C8D5CE;
    }


    .package-proration-notice {
        margin:
            18px 24px 0;

        padding:
            14px 16px;

        border:
            1px solid #ABEFC6;

        border-radius:
            12px;

        background:
            #ECFDF3;

        color:
            #067647;

        font-size:
            11px;

        line-height:
            1.7;
    }


    .package-payment-method-grid {
        display:
            grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap:
            14px;

        padding:
            20px 24px 24px;
    }


    .package-payment-option {
        display:
            flex;

        min-height:
            310px;

        flex-direction:
            column;

        padding:
            20px;

        border:
            1px solid #DCE5E0;

        border-radius:
            16px;

        background:
            #FFFFFF;
    }


    .package-payment-option.wallet-available {
        border-color:
            #ABEFC6;

        background:
            #F4FBF7;
    }


    .package-payment-option.wallet-unavailable {
        background:
            #FAFBFA;
    }


    .package-payment-icon {
        display:
            grid;

        width:
            44px;

        height:
            44px;

        place-items:
            center;

        border-radius:
            12px;

        font-size:
            18px;
    }


    .package-payment-icon.wallet-icon {
        background:
            #D1FADF;

        color:
            #067647;
    }


    .package-payment-icon.paystack-icon {
        background:
            #EEF2FF;

        color:
            #6246EA;
    }


    .package-payment-option-title {
        margin:
            15px 0 5px;

        color:
            #17251F;

        font-size:
            15px;

        font-weight:
            700;
    }


    .package-wallet-balance {
        display:
            block;

        margin-top:
            3px;

        color:
            #0B3D2E;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            25px;

        font-weight:
            800;
    }


    .package-wallet-status {
        margin:
            10px 0 16px;

        font-size:
            10px;

        line-height:
            1.7;
    }


    .wallet-status-success {
        color:
            #067647;
    }


    .wallet-status-error {
        color:
            #B54708;
    }


    .package-test-mode {
        margin-top:
            12px;

        padding:
            8px 10px;

        border:
            1px solid #FEDF89;

        border-radius:
            9px;

        background:
            #FFF7E8;

        color:
            #8A5A00;

        font-size:
            9px;

        line-height:
            1.6;
    }


    .package-payment-modal-footer {
        padding:
            0 24px 22px;

        color:
            #738078;

        font-size:
            10px;

        line-height:
            1.6;

        text-align:
            center;
    }



    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 980px) {

        .seller-packages-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }

    }


    @media (max-width: 700px) {

        .package-payment-method-grid {
            grid-template-columns:
                1fr;
        }


        .package-payment-modal {
            padding:
                12px;
        }


        .package-payment-modal-dialog {
            max-height:
                calc(100vh - 24px);

            border-radius:
                16px;
        }


        .package-payment-modal-header {
            padding:
                20px 18px 15px;
        }


        .package-proration-notice {
            margin:
                15px 18px 0;
        }


        .package-payment-method-grid {
            padding:
                16px 18px 20px;
        }


        .package-payment-modal-footer {
            padding:
                0 18px 20px;
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
        | First-Time Seller Package Buttons
        |--------------------------------------------------------------------------
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
                                theme
                                ===
                                'purple'
                            ) {

                                selectedPackageLabel
                                    .classList
                                    .add(
                                        'mp-badge-purple'
                                    );

                            } else if (
                                theme
                                ===
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
                        | Button Text
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
                        | Guest Login Redirect
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


        if (
            fileInput
        ) {

            fileInput.addEventListener(
                'change',
                function () {

                    const files =
                        Array.from(
                            this.files
                            ||
                            []
                        );


                    if (
                        !fileName
                    ) {

                        return;
                    }


                    if (
                        files.length
                        ===
                        0
                    ) {

                        fileName.textContent =
                            '';

                        return;
                    }


                    if (
                        files.length
                        ===
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



        /*
        |--------------------------------------------------------------------------
        | Package Payment Modal
        |--------------------------------------------------------------------------
        */

        const packagePaymentModal =
            document.getElementById(
                'package-payment-modal'
            );


        const openPackagePaymentModalButton =
            document.getElementById(
                'open-package-payment-modal'
            );


        const closePackagePaymentModalButtons =
            document.querySelectorAll(
                '[data-close-package-payment-modal]'
            );



        /*
        |--------------------------------------------------------------------------
        | Open Payment Modal
        |--------------------------------------------------------------------------
        */

        const openPackagePaymentModal =
            function () {

                if (
                    !packagePaymentModal
                ) {

                    return;
                }


                packagePaymentModal
                    .classList
                    .add(
                        'is-open'
                    );


                packagePaymentModal.setAttribute(
                    'aria-hidden',
                    'false'
                );


                document
                    .body
                    .classList
                    .add(
                        'package-payment-modal-open'
                    );
            };



        /*
        |--------------------------------------------------------------------------
        | Close Payment Modal
        |--------------------------------------------------------------------------
        */

        const closePackagePaymentModal =
            function () {

                if (
                    !packagePaymentModal
                ) {

                    return;
                }


                packagePaymentModal
                    .classList
                    .remove(
                        'is-open'
                    );


                packagePaymentModal.setAttribute(
                    'aria-hidden',
                    'true'
                );


                document
                    .body
                    .classList
                    .remove(
                        'package-payment-modal-open'
                    );
            };



        /*
        |--------------------------------------------------------------------------
        | Open Button
        |--------------------------------------------------------------------------
        */

        if (
            openPackagePaymentModalButton
        ) {

            openPackagePaymentModalButton
                .addEventListener(
                    'click',
                    function () {

                        openPackagePaymentModal();
                    }
                );
        }



        /*
        |--------------------------------------------------------------------------
        | Close Buttons
        |--------------------------------------------------------------------------
        */

        closePackagePaymentModalButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        closePackagePaymentModal();
                    }
                );
            }
        );



        /*
        |--------------------------------------------------------------------------
        | ESC Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key
                    ===
                    'Escape'
                    &&
                    packagePaymentModal
                    &&
                    packagePaymentModal
                        .classList
                        .contains(
                            'is-open'
                        )
                ) {

                    closePackagePaymentModal();
                }
            }
        );



        /*
        |--------------------------------------------------------------------------
        | Automatically Open Modal
        |--------------------------------------------------------------------------
        |
        | Used after:
        |
        | - creating renewal invoice
        | - creating upgrade invoice
        | - failed wallet payment
        |
        */

        if (
            packagePaymentModal
            &&
            packagePaymentModal
                .dataset
                .autoOpen
            ===
            '1'
        ) {

            openPackagePaymentModal();
        }



        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Payment Submission
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.package-payment-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function () {

                            const button =
                                form.querySelector(
                                    'button[type="submit"]'
                                );


                            if (
                                !button
                                ||
                                button.disabled
                            ) {

                                return;
                            }


                            button.disabled =
                                true;


                            button.classList.add(
                                'cursor-not-allowed',
                                'opacity-70'
                            );


                            button.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i> Processing payment...';
                        }
                    );
                }
            );



        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Renewal / Upgrade Invoice Submission
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.seller-package-change-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function () {

                            const button =
                                form.querySelector(
                                    'button[type="submit"]'
                                );


                            if (
                                !button
                                ||
                                button.disabled
                            ) {

                                return;
                            }


                            button.disabled =
                                true;


                            button.classList.add(
                                'cursor-not-allowed',
                                'opacity-70'
                            );


                            button.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i> Preparing invoice...';
                        }
                    );
                }
            );



        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Seller Application Submission
        |--------------------------------------------------------------------------
        */

        const sellerApplicationForm =
            document.getElementById(
                'verified-seller-form'
            );


        if (
            sellerApplicationForm
        ) {

            sellerApplicationForm.addEventListener(
                'submit',
                function () {

                    const button =
                        sellerApplicationForm
                            .querySelector(
                                'button[type="submit"]'
                            );


                    if (
                        !button
                        ||
                        button.disabled
                    ) {

                        return;
                    }


                    button.disabled =
                        true;


                    button.classList.add(
                        'cursor-not-allowed',
                        'opacity-70'
                    );


                    button.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> Submitting application...';
                }
            );
        }

    }
);

</script>

@endpush


@endsection