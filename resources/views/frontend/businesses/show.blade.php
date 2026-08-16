@extends('frontend.layouts.app')


@section('title', $application->business_name . ' | Midpoint')


@section('content')


@php

    $profile =
        $businessProfile;


    $businessName =
        $application->business_name
        ?: $seller->name;


    $publicDescription =
        optional($profile)->about
        ?: $application->description
        ?: 'Verified business on Midpoint.';


    $publicTagline =
        optional($profile)->tagline;


    $publicLocation =
        optional($profile)->location
        ?: $application->location;


    $publicPhone =
        optional($profile)->phone
        ?: $application->phone;


    $initials =
        collect(
            preg_split(
                '/\s+/',
                trim($businessName)
            )
        )
        ->filter()
        ->take(2)
        ->map(
            fn ($word) =>
                strtoupper(
                    substr(
                        $word,
                        0,
                        1
                    )
                )
        )
        ->implode('');


    /*
    |--------------------------------------------------------------------------
    | Product Modal Payload
    |--------------------------------------------------------------------------
    */

    $productPayload =
        $products->mapWithKeys(
            function ($product) use (
                $seller,
                $profile,
                $businessName
            ) {

                $images =
                    is_array($product->images)
                        ? $product->images
                        : [];


                if (
                    count($images) === 0
                    &&
                    $product->image
                ) {
                    $images[] =
                        $product->image;
                }


                $buyUrl =

                    (int)
                    $product->stock
                    >
                    0

                        ?

                        route(
                            'buyer.products.checkout',
                            [

                                'sellerProduct' =>
                                    $product->id,

                            ]
                        )

                        :

                        null;


                $whatsappUrl =
                    null;


                if (
                    $profile
                    &&
                    $profile->whatsapp_enabled
                ) {

                    $whatsappUrl =
                        $profile->whatsappUrl(
                            'Hi '
                            .
                            $businessName
                            .
                            ', I found your '
                            .
                            $product->name
                            .
                            ' on Midpoint and would like to know more about it.'
                        );
                }


                return [

                    $product->id => [

                        'id' =>
                            $product->id,

                        'name' =>
                            $product->name,

                        'price' =>
                            number_format(
                                (float)
                                $product->price,
                                0
                            ),

                        'stock' =>
                            $product->stock,

                        'description' =>
                            $product->description,

                        'images' =>
                            collect($images)
                                ->map(
                                    fn ($path) =>
                                        asset(
                                            'storage/'
                                            .
                                            $path
                                        )
                                )
                                ->values()
                                ->all(),

                        'buy_url' =>
                            $buyUrl,

                        'whatsapp_url' =>
                            $whatsappUrl,

                    ],

                ];
            }
        );

@endphp



<section class="shop-page">

    <div class="shop-wrap">


        {{-- =========================================================
            BACK
        ========================================================== --}}

        <a
            href="{{ route('featured-businesses') }}"
            class="shop-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            All featured businesses

        </a>



        {{-- =========================================================
            BUSINESS HERO
        ========================================================== --}}

        <section class="shop-hero-card">

            <div class="shop-business-main">


                {{-- =================================================
                    PROFILE PICTURE
                ================================================== --}}

                <div class="shop-avatar">

                    @if ($profile && $profile->profile_image_url)

                        <img
                            src="{{ $profile->profile_image_url }}"
                            alt="{{ $businessName }}"
                        >

                    @else

                        <span>
                            {{ $initials }}
                        </span>

                    @endif

                </div>



                <div class="shop-business-copy">

                    <div class="shop-title-row">

                        <h1>
                            {{ $businessName }}
                        </h1>


                        <span class="shop-verified">

                            <i class="fa-solid fa-check"></i>

                            Verified Seller

                        </span>

                    </div>


                    <div class="shop-subtitle">

                        <span>
                            {{ $application->category ?: 'Verified Seller' }}
                        </span>


                        @if ($publicLocation)

                            <span>•</span>

                            <span>

                                <i class="fa-solid fa-location-dot"></i>

                                {{ $publicLocation }}

                            </span>

                        @endif


                        <span>•</span>


                        <span>
                            Joined {{ $seller->created_at->format('M Y') }}
                        </span>

                    </div>


                    @if ($publicTagline)

                        <div class="shop-tagline">
                            {{ $publicTagline }}
                        </div>

                    @endif


                    <div class="shop-business-description">
                        {{ \Illuminate\Support\Str::limit(strip_tags($publicDescription), 270) }}
                    </div>



                    {{-- =============================================
                        SOCIAL LINKS
                    ============================================== --}}

                    @if (
                        $profile
                        &&
                        (
                            $profile->website_url
                            ||
                            $profile->instagram_url
                            ||
                            $profile->facebook_url
                        )
                    )

                        <div class="shop-social-links">

                            @if ($profile->website_url)

                                <a
                                    href="{{ $profile->website_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Website"
                                >
                                    <i class="fa-solid fa-globe"></i>
                                </a>

                            @endif


                            @if ($profile->instagram_url)

                                <a
                                    href="{{ $profile->instagram_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Instagram"
                                >
                                    <i class="fa-brands fa-instagram"></i>
                                </a>

                            @endif


                            @if ($profile->facebook_url)

                                <a
                                    href="{{ $profile->facebook_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Facebook"
                                >
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>

                            @endif

                        </div>

                    @endif

                </div>

            </div>



            {{-- =====================================================
                HERO ACTIONS
            ====================================================== --}}

            <div class="shop-hero-actions">

                @if ($products->isNotEmpty())

                    <a
                        href="#products"
                        class="shop-primary-action"
                    >

                        <i class="fa-solid fa-shield-halved"></i>

                        Shop products

                    </a>

                @endif


                @if (
                    $profile
                    &&
                    $profile->whatsapp_enabled
                    &&
                    $profile->whatsappUrl()
                )

                    <a
                        href="{{ $profile->whatsappUrl() }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="shop-whatsapp-action"
                    >

                        <i class="fa-brands fa-whatsapp"></i>

                        Message on WhatsApp

                    </a>

                @endif


                @if ($profile && $profile->website_url)

                    <a
                        href="{{ $profile->website_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="shop-secondary-action"
                    >

                        <i class="fa-solid fa-globe"></i>

                        Visit website

                    </a>

                @elseif ($application->store_link)

                    <a
                        href="{{ $application->store_link }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="shop-secondary-action"
                    >

                        <i class="fa-solid fa-arrow-up-right-from-square"></i>

                        External store

                    </a>

                @endif

            </div>



            {{-- =====================================================
                METRICS
            ====================================================== --}}

            <div class="shop-metrics">

                <article>

                    <span>
                        Rating
                    </span>

                    <strong>

                        @if ($averageRating)

                            <i class="fa-solid fa-star"></i>

                            {{ $averageRating }}/5

                        @else

                            New

                        @endif

                    </strong>

                </article>


                <article>

                    <span>
                        Reviews
                    </span>

                    <strong>
                        {{ $reviewCount }}
                    </strong>

                </article>


                <article>

                    <span>
                        Listed products
                    </span>

                    <strong>
                        {{ $products->count() }}
                    </strong>

                </article>


                <article>

                    <span>
                        Seller package
                    </span>

                    <strong>
                        {{ $subscription->package_name }}
                    </strong>

                </article>

            </div>

        </section>



        {{-- =========================================================
            SECTION NAVIGATION
        ========================================================== --}}

        <nav class="shop-section-nav">

            <a href="#products">

                Products

                <span>
                    {{ $products->count() }}
                </span>

            </a>


            <a href="#about">
                About
            </a>


            <a href="#reviews">

                Reviews

                <span>
                    {{ $reviewCount }}
                </span>

            </a>

        </nav>



        {{-- =========================================================
            PRODUCTS
        ========================================================== --}}

        <section
            id="products"
            class="shop-section-card"
        >

            <div class="shop-section-heading">

                <div>

                    <h2>
                        Listed products
                    </h2>


                    <p>
                        Pick a product to start a secure,
                        escrow-protected purchase.
                    </p>

                </div>


                <span class="shop-product-count">

                    {{ $products->count() }}

                    of

                    {{ $subscription->product_limit }}

                    slots used

                </span>

            </div>



            @if ($products->isNotEmpty())

                <div class="shop-products-grid">

                    @foreach ($products as $product)

                        @php
                            $images =
                                is_array($product->images)
                                    ? $product->images
                                    : [];

                            if (
                                count($images) === 0
                                &&
                                $product->image
                            ) {
                                $images[] =
                                    $product->image;
                            }

                            $buyUrl =

                                (int)
                                $product->stock
                                >
                                0

                                    ?

                                    route(
                                        'buyer.products.checkout',
                                        [

                                            'sellerProduct' =>
                                                $product->id,

                                        ]
                                    )

                                    :

                                    null;

                            $productWhatsappUrl =
                                null;

                            if (
                                $profile
                                &&
                                $profile->whatsapp_enabled
                            ) {
                                $productWhatsappUrl =
                                    $profile->whatsappUrl(
                                        'Hi '
                                        .
                                        $businessName
                                        .
                                        ', I found your '
                                        .
                                        $product->name
                                        .
                                        ' on Midpoint and would like to know more about it.'
                                    );
                            }
                        @endphp


                        <article class="shop-product-card">


                            {{-- =====================================
                                IMAGE GALLERY
                            ====================================== --}}

                            <div
                                class="shop-product-gallery"
                                data-product-carousel
                            >

                                @if (count($images))

                                    @foreach ($images as $imageIndex => $image)

                                        <div
                                            class="shop-product-slide {{ $imageIndex === 0 ? 'active' : '' }}"
                                            data-product-slide
                                        >

                                            <img
                                                src="{{ asset('storage/' . $image) }}"
                                                alt="{{ $product->name }}"
                                                loading="lazy"
                                            >

                                        </div>

                                    @endforeach


                                    @if (count($images) > 1)

                                        <button
                                            type="button"
                                            class="shop-gallery-control prev"
                                            data-product-prev
                                            aria-label="Previous image"
                                        >

                                            <i class="fa-solid fa-chevron-left"></i>

                                        </button>


                                        <button
                                            type="button"
                                            class="shop-gallery-control next"
                                            data-product-next
                                            aria-label="Next image"
                                        >

                                            <i class="fa-solid fa-chevron-right"></i>

                                        </button>


                                        <div class="shop-gallery-dots">

                                            @foreach ($images as $imageIndex => $image)

                                                <button
                                                    type="button"
                                                    class="{{ $imageIndex === 0 ? 'active' : '' }}"
                                                    data-product-dot="{{ $imageIndex }}"
                                                    aria-label="Image {{ $imageIndex + 1 }}"
                                                ></button>

                                            @endforeach

                                        </div>

                                    @endif

                                @else

                                    <div class="shop-no-image">

                                        <i class="fa-solid fa-bag-shopping"></i>

                                    </div>

                                @endif

                            </div>



                            <div class="shop-product-content">

                                <h3>
                                    {{ $product->name }}
                                </h3>


                                <p>
                                    {{
                                        \Illuminate\Support\Str::limit(
                                            trim(
                                                strip_tags(
                                                    $product->description
                                                )
                                            ),
                                            115
                                        )
                                    }}
                                </p>


                                <strong class="shop-product-price">

                                    ₦{{ number_format((float) $product->price, 0) }}

                                </strong>



                                <div class="shop-product-stock">

                                    <span>

                                        <i class="fa-solid fa-box"></i>

                                        Stock:
                                        {{ $product->stock }}

                                    </span>


                                    <span>

                                        <i class="fa-solid fa-truck"></i>

                                        Seller delivery

                                    </span>

                                </div>



                                <div class="shop-product-buttons {{ $productWhatsappUrl ? 'has-whatsapp' : '' }}">

                                    @if ((int) $product->stock > 0)

                                        <a
                                            href="{{ $buyUrl }}"
                                            class="shop-buy-button"
                                        >

                                            <i class="fa-solid fa-shield-halved"></i>

                                            Buy securely

                                        </a>

                                    @else

                                        <span
                                            class="shop-buy-button shop-buy-button-disabled"
                                            aria-disabled="true"
                                        >

                                            <i class="fa-solid fa-box-open"></i>

                                            Out of stock

                                        </span>

                                    @endif


                                    <button
                                        type="button"
                                        class="shop-view-product"
                                        data-view-product="{{ $product->id }}"
                                        title="View product details"
                                    >

                                        <i class="fa-regular fa-eye"></i>

                                    </button>


                                    @if ($productWhatsappUrl)

                                        <a
                                            href="{{ $productWhatsappUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="shop-product-whatsapp"
                                            title="Ask on WhatsApp"
                                        >

                                            <i class="fa-brands fa-whatsapp"></i>

                                        </a>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="shop-empty-products">

                    <i class="fa-solid fa-box-open"></i>


                    <h3>
                        No products currently listed
                    </h3>


                    <p>
                        This seller has not published any products yet.
                    </p>

                </div>

            @endif

        </section>



        {{-- =========================================================
            ABOUT + PACKAGE
        ========================================================== --}}

        <div
            id="about"
            class="shop-info-grid"
        >

            <section class="shop-section-card">

                <div class="shop-section-heading">

                    <div>

                        <h2>
                            About {{ $businessName }}
                        </h2>

                        @if ($publicTagline)

                            <p>
                                {{ $publicTagline }}
                            </p>

                        @endif

                    </div>

                </div>


                <div class="shop-about-description">
                    {!! nl2br(e(strip_tags($publicDescription))) !!}
                </div>



                <div class="shop-about-list">

                    @if ($application->category)

                        <div>

                            <span>

                                <i class="fa-solid fa-layer-group"></i>

                                Category

                            </span>


                            <strong>
                                {{ $application->category }}
                            </strong>

                        </div>

                    @endif


                    @if ($publicLocation)

                        <div>

                            <span>

                                <i class="fa-solid fa-location-dot"></i>

                                Location

                            </span>


                            <strong>
                                {{ $publicLocation }}
                            </strong>

                        </div>

                    @endif


                    @if (
                        $profile
                        &&
                        $profile->show_phone
                        &&
                        $publicPhone
                    )

                        <div>

                            <span>

                                <i class="fa-solid fa-phone"></i>

                                Contact

                            </span>


                            <strong>
                                {{ $publicPhone }}
                            </strong>

                        </div>

                    @endif


                    @if (
                        $profile
                        &&
                        $profile->show_email
                    )

                        <div>

                            <span>

                                <i class="fa-solid fa-envelope"></i>

                                Email

                            </span>


                            <strong>
                                {{ $seller->email }}
                            </strong>

                        </div>

                    @endif


                    @if ($profile && $profile->business_hours)

                        <div>

                            <span>

                                <i class="fa-regular fa-clock"></i>

                                Business hours

                            </span>


                            <strong>
                                {{ $profile->business_hours }}
                            </strong>

                        </div>

                    @endif

                </div>



                @if (
                    $profile
                    &&
                    (
                        $profile->website_url
                        ||
                        $profile->instagram_url
                        ||
                        $profile->facebook_url
                    )
                )

                    <div class="shop-about-socials">

                        <strong>
                            Find us online
                        </strong>


                        <div>

                            @if ($profile->website_url)

                                <a
                                    href="{{ $profile->website_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    <i class="fa-solid fa-globe"></i>

                                    Website

                                </a>

                            @endif


                            @if ($profile->instagram_url)

                                <a
                                    href="{{ $profile->instagram_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    <i class="fa-brands fa-instagram"></i>

                                    Instagram

                                </a>

                            @endif


                            @if ($profile->facebook_url)

                                <a
                                    href="{{ $profile->facebook_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    <i class="fa-brands fa-facebook-f"></i>

                                    Facebook

                                </a>

                            @endif

                        </div>

                    </div>

                @endif

            </section>



            {{-- =====================================================
                VERIFIED PACKAGE
            ====================================================== --}}

            <section class="shop-section-card">

                <div class="shop-package-icon">

                    <i class="fa-solid fa-gem"></i>

                </div>


                <h2>
                    Verified seller package
                </h2>


                <p>
                    Active {{ $subscription->package_name }} package.
                </p>


                <div class="shop-package-pill">

                    {{ $subscription->package_name }}

                    · up to

                    {{ number_format($subscription->product_limit) }}

                    products

                </div>


                @if ($subscription->expires_at)

                    <small>

                        Verified through

                        {{ $subscription->expires_at->format('d M Y') }}

                    </small>

                @endif


                <div class="shop-package-security">

                    <i class="fa-solid fa-shield-halved"></i>

                    <div>

                        <strong>
                            Midpoint Verified
                        </strong>

                        <span>
                            This seller currently has an active verified
                            seller package.
                        </span>

                    </div>

                </div>

            </section>

        </div>



        {{-- =========================================================
            REVIEWS
        ========================================================== --}}

        <section
            id="reviews"
            class="shop-section-card shop-reviews-section"
        >

            <div class="shop-section-heading">

                <div>

                    <h2>
                        Customer reviews
                    </h2>

                    <p>
                        Reviews from Midpoint buyers.
                    </p>

                </div>


                @if ($averageRating)

                    <div class="shop-review-score">

                        <i class="fa-solid fa-star"></i>

                        {{ $averageRating }}

                        <span>
                            /5
                        </span>

                    </div>

                @endif

            </div>



            @if ($reviews->isNotEmpty())

                <div class="shop-reviews-list">

                    @foreach ($reviews as $review)

                        <article class="shop-review">

                            <div class="shop-review-top">

                                <div>

                                    <strong>
                                        {{ optional($review->buyer)->name ?: 'Midpoint Buyer' }}
                                    </strong>


                                    @if ($review->product)

                                        <span>
                                            Purchased {{ $review->product->name }}
                                        </span>

                                    @endif

                                </div>


                                <div class="shop-stars">

                                    @for ($i = 1; $i <= 5; $i++)

                                        <i
                                            class="fa-solid fa-star {{ $i <= $review->rating ? 'active' : '' }}"
                                        ></i>

                                    @endfor

                                </div>

                            </div>


                            <p>
                                {{ $review->review }}
                            </p>


                            <small>
                                {{ $review->created_at->format('d M Y') }}
                            </small>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="shop-no-reviews">

                    <i class="fa-regular fa-star"></i>


                    <h3>
                        No reviews yet
                    </h3>


                    <p>
                        This seller has not received any published buyer reviews yet.
                    </p>

                </div>

            @endif

        </section>

    </div>

</section>



{{-- =========================================================
    PRODUCT MODAL
========================================================== --}}

<div
    id="shopProductModal"
    class="shop-modal"
    hidden
>

    <div
        class="shop-modal-backdrop"
        data-close-product-modal
    ></div>


    <div class="shop-modal-dialog">

        <button
            type="button"
            class="shop-modal-close"
            data-close-product-modal
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <div class="shop-modal-grid">

            <div class="shop-modal-gallery">

                <div class="shop-modal-main-image">

                    <img
                        id="shopModalImage"
                        src=""
                        alt=""
                    >


                    <div
                        id="shopModalNoImage"
                        class="shop-modal-no-image"
                        hidden
                    >

                        <i class="fa-solid fa-bag-shopping"></i>

                    </div>


                    <button
                        type="button"
                        id="shopModalPrevious"
                        class="shop-modal-gallery-button previous"
                    >

                        <i class="fa-solid fa-chevron-left"></i>

                    </button>


                    <button
                        type="button"
                        id="shopModalNext"
                        class="shop-modal-gallery-button next"
                    >

                        <i class="fa-solid fa-chevron-right"></i>

                    </button>

                </div>


                <div
                    id="shopModalThumbnails"
                    class="shop-modal-thumbnails"
                ></div>

            </div>


            <div class="shop-modal-details">

                <span class="shop-modal-business">

                    <i class="fa-solid fa-circle-check"></i>

                    {{ $businessName }}

                </span>


                <h2 id="shopModalTitle"></h2>


                <div class="shop-modal-price">

                    ₦<span id="shopModalPrice"></span>

                </div>


                <div class="shop-modal-meta">

                    <span>

                        <i class="fa-solid fa-box"></i>

                        Stock:

                        <strong id="shopModalStock"></strong>

                    </span>


                    <span>

                        <i class="fa-solid fa-truck"></i>

                        Delivery arranged by seller

                    </span>

                </div>


                <div class="shop-modal-description-heading">
                    Product description
                </div>


                <div
                    id="shopModalDescription"
                    class="shop-rich-description"
                ></div>


                <a
                    id="shopModalBuyButton"
                    href="#"
                    class="shop-modal-buy"
                >

                    <i class="fa-solid fa-shield-halved"></i>

                    Buy securely

                </a>


                <a
                    id="shopModalWhatsappButton"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="shop-modal-whatsapp"
                    hidden
                >

                    <i class="fa-brands fa-whatsapp"></i>

                    Ask seller on WhatsApp

                </a>


                <div class="shop-modal-security-note">

                    <i class="fa-solid fa-lock"></i>

                    Your payment is protected through Midpoint escrow
                    until the transaction is completed.

                </div>

            </div>

        </div>

    </div>

</div>



<script
    type="application/json"
    id="shopProductPayload"
>
{!! json_encode(
    $productPayload,
    JSON_HEX_TAG
    |
    JSON_HEX_APOS
    |
    JSON_HEX_AMP
    |
    JSON_HEX_QUOT
) !!}
</script>



@push('styles')

<style>

    .shop-page {
        min-height: 100vh;
        padding: 45px 18px 70px;
        background: #F6F9F7;
    }

    .shop-wrap {
        width: 100%;
        max-width: 1040px;
        margin: 0 auto;
    }

    .shop-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 18px;
        color: #087443;
        font-size:12px;
        font-weight: 700;
        text-decoration: none;
    }

    .shop-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 24px;
        padding: 27px;
        border: 1px solid #DCE5E0;
        border-radius: 19px;
        background: #FFFFFF;
        box-shadow: 0 10px 35px -25px rgba(11,61,46,.32);
    }

    .shop-business-main {
        display: flex;
        align-items: flex-start;
        gap: 17px;
    }

    .shop-avatar {
        width: 82px;
        height: 82px;
        flex: 0 0 82px;
        overflow: hidden;
        display: grid;
        place-items: center;
        border-radius: 20px;
        background: linear-gradient(135deg,#0B3D2E,#12B76A);
        color: #FFFFFF;
        font-size: 23px;
        font-weight: 800;
    }

    .shop-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        object-position: center;
    }

    .shop-business-copy {
        min-width: 0;
        flex: 1;
    }

    .shop-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .shop-title-row h1 {
        margin: 0;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: -.025em;
    }

    .shop-verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 999px;
        background: #ECFDF3;
        color: #067647;
        font-size: 8px;
        font-weight: 800;
    }

    .shop-subtitle {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        margin-top: 4px;
        color: #6C7972;
        font-size:11px;
    }

    .shop-tagline {
        margin-top: 11px;
        color: #0B3D2E;
        font-size: 11px;
        font-weight: 800;
    }

    .shop-business-description {
        max-width: 600px;
        margin-top: 8px;
        color: #49574F;
        font-size:12px;
        line-height: 1.7;
    }

    .shop-social-links {
        display: flex;
        gap: 6px;
        margin-top: 12px;
    }

    .shop-social-links a {
        width: 31px;
        height: 31px;
        display: grid;
        place-items: center;
        border: 1px solid #DCE5E0;
        border-radius: 8px;
        background: #FFFFFF;
        color: #0B3D2E;
        font-size: 11px;
        text-decoration: none;
    }

    .shop-hero-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .shop-primary-action,
    .shop-secondary-action,
    .shop-whatsapp-action {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        font-size:12px;
        font-weight: 800;
        text-decoration: none;
    }

    .shop-primary-action {
        background: #12B76A;
        color: #FFFFFF;
    }

    .shop-whatsapp-action {
        background: #16A34A;
        color: #FFFFFF;
    }

    .shop-whatsapp-action i {
        font-size: 15px;
    }

    .shop-secondary-action {
        border: 1px solid #DCE5E0;
        color: #0B3D2E;
        background: #FFFFFF;
    }

    .shop-metrics {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 3px;
        border-top: 1px solid #E7ECE9;
    }

    .shop-metrics article {
        padding: 17px 17px 0;
    }

    .shop-metrics article:first-child {
        padding-left: 0;
    }

    .shop-metrics span {
        display: block;
        margin-bottom: 5px;
        color: #6F7D76;
        font-size:11px;
    }

    .shop-metrics strong {
        color: #101915;
        font-size: 18px;
        font-weight: 800;
    }

    .shop-metrics strong i {
        color: #F4B400;
        font-size: 13px;
    }

    .shop-section-nav {
        position: sticky;
        top: 68px;
        z-index: 20;
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 16px 0;
        padding: 7px;
        border: 1px solid #DFE7E3;
        border-radius: 13px;
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(10px);
    }

    .shop-section-nav a {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 8px;
        color: #536159;
        font-size:11px;
        font-weight: 700;
        text-decoration: none;
    }

    .shop-section-nav a:hover {
        background: #E8F7EF;
        color: #087443;
    }

    .shop-section-nav span {
        padding: 2px 5px;
        border-radius: 999px;
        background: #ECF2EF;
        font-size: 7px;
    }

    .shop-section-card {
        padding: 24px;
        border: 1px solid #DCE5E0;
        border-radius: 18px;
        background: #FFFFFF;
        box-shadow: 0 7px 25px -22px rgba(11,61,46,.28);
    }

    .shop-section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 19px;
    }
    .shop-buy-button-disabled {
        background: #9AA7A0 !important;
        cursor: not-allowed !important;
        pointer-events: none;
    }


    .shop-modal-buy-disabled {
        background: #9AA7A0 !important;
        cursor: not-allowed !important;
    }
    .shop-section-heading h2 {
        margin: 0;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 17px;
        font-weight: 800;
    }

    .shop-section-heading p {
        margin: 3px 0 0;
        color: #6C7972;
        font-size:11px;
    }

    .shop-product-count {
        padding: 6px 9px;
        border-radius: 999px;
        background: #E8F7EF;
        color: #087443;
        font-size: 8px;
        font-weight: 800;
    }

    .shop-products-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .shop-product-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid #DDE5E1;
        border-radius: 16px;
        background: #FFFFFF;
        transition: .16s ease;
    }

    .shop-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -25px rgba(11,61,46,.40);
    }

    .shop-product-gallery {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: #F7FAF8;
    }

    .shop-product-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .shop-product-slide.active {
        opacity: 1;
        pointer-events: auto;
    }

    .shop-product-slide img {
        width: 100%;
        height: 100%;
        padding: 8px;
        display: block;
        object-fit: contain;
        object-position: center;
        background: #F7FAF8;
    }

    .shop-no-image {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        background: #E8F7EF;
        color: #12B76A;
        font-size: 32px;
    }

    .shop-gallery-control {
        position: absolute;
        top: 50%;
        z-index: 5;
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border: 1px solid #E1E7E4;
        border-radius: 50%;
        background: rgba(255,255,255,.95);
        color: #0B3D2E;
        font-size:11px;
        cursor: pointer;
        transform: translateY(-50%);
        box-shadow: 0 4px 15px rgba(0,0,0,.08);
    }

    .shop-gallery-control.prev {
        left: 8px;
    }

    .shop-gallery-control.next {
        right: 8px;
    }

    .shop-gallery-dots {
        position: absolute;
        bottom: 8px;
        left: 50%;
        z-index: 5;
        display: flex;
        gap: 4px;
        padding: 4px 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.9);
        transform: translateX(-50%);
    }

    .shop-gallery-dots button {
        width: 6px;
        height: 6px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: #C5D0CA;
        cursor: pointer;
    }

    .shop-gallery-dots button.active {
        width: 15px;
        background: #12B76A;
    }

    .shop-product-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 15px;
    }

    .shop-product-content h3 {
        margin: 0;
        color: #101915;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.4;
    }

    .shop-product-content > p {
        min-height: 49px;
        margin: 7px 0 10px;
        color: #637069;
        font-size:11px;
        line-height: 1.6;
    }

    .shop-product-price {
        display: block;
        margin-bottom: 8px;
        color: #101915;
        font-size: 17px;
        font-weight: 800;
    }

    .shop-product-stock {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 10px;
        color: #6F7C75;
        font-size: 8px;
    }

    .shop-product-stock i {
        margin-right: 3px;
        color: #12B76A;
    }

    .shop-product-buttons {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px;
        gap: 7px;
        margin-top: auto;
        padding-top: 14px;
    }

    .shop-product-buttons.has-whatsapp {
        grid-template-columns: minmax(0, 1fr) 42px 42px;
    }

    .shop-buy-button,
    .shop-view-product,
    .shop-product-whatsapp {
        min-height: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border-radius: 9px;
        font-size:11px;
        font-weight: 800;
        text-decoration: none;
    }

    .shop-buy-button {
        background: #12B76A;
        color: #FFFFFF;
    }

    .shop-view-product {
        border: 1px solid #D9E3DE;
        background: #FFFFFF;
        color: #0B3D2E;
        font-size: 12px;
        cursor: pointer;
    }

    .shop-product-whatsapp {
        background: #EAFBF0;
        color: #16A34A;
        font-size: 14px;
    }

    .shop-info-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(260px, .8fr);
        gap: 16px;
        margin-top: 16px;
    }

    .shop-about-description {
        color: #526059;
        font-size:12px;
        line-height: 1.75;
    }

    .shop-about-list {
        margin-top: 18px;
        border-top: 1px solid #E8ECEA;
    }

    .shop-about-list > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 12px 0;
        border-bottom: 1px solid #EEF2F0;
        font-size:11px;
    }

    .shop-about-list span {
        color: #748079;
    }

    .shop-about-list i {
        width: 15px;
        color: #12B76A;
    }

    .shop-about-list strong {
        text-align: right;
    }

    .shop-about-socials {
        margin-top: 19px;
    }

    .shop-about-socials > strong {
        display: block;
        margin-bottom: 8px;
        color: #344139;
        font-size:11px;
    }

    .shop-about-socials > div {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .shop-about-socials a {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0 10px;
        border: 1px solid #DCE5E0;
        border-radius: 8px;
        color: #0B3D2E;
        font-size: 8px;
        font-weight: 700;
        text-decoration: none;
    }

    .shop-package-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 12px;
        background: #F0ECFF;
        color: #6941C6;
    }

    .shop-info-grid h2 {
        margin: 0 0 5px;
        font-size: 14px;
    }

    .shop-info-grid p {
        margin: 0;
        color: #6A7770;
        font-size:11px;
    }

    .shop-package-pill {
        display: inline-flex;
        margin: 13px 0 10px;
        padding: 6px 9px;
        border-radius: 999px;
        background: #F0ECFF;
        color: #6941C6;
        font-size: 8px;
        font-weight: 800;
    }

    .shop-info-grid small {
        display: block;
        color: #718078;
        font-size: 8px;
    }

    .shop-package-security {
        display: flex;
        gap: 8px;
        margin-top: 18px;
        padding: 11px;
        border-radius: 10px;
        background: #ECFDF3;
        color: #067647;
    }

    .shop-package-security > i {
        margin-top: 2px;
    }

    .shop-package-security strong,
    .shop-package-security span {
        display: block;
    }

    .shop-package-security strong {
        font-size: 8px;
    }

    .shop-package-security span {
        margin-top: 2px;
        font-size: 7px;
        line-height: 1.45;
    }

    .shop-reviews-section {
        margin-top: 16px;
    }

    .shop-review-score {
        color: #101915;
        font-size: 19px;
        font-weight: 800;
    }

    .shop-review-score i {
        color: #F4B400;
    }

    .shop-review-score span {
        color: #809089;
        font-size:11px;
    }

    .shop-reviews-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .shop-review {
        padding: 15px;
        border: 1px solid #E1E8E4;
        border-radius: 12px;
        background: #FAFCFB;
    }

    .shop-review-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .shop-review-top strong {
        display: block;
        color: #101915;
        font-size:12px;
    }

    .shop-review-top span {
        display: block;
        margin-top: 2px;
        color: #7A8780;
        font-size: 8px;
    }

    .shop-stars {
        display: flex;
        gap: 2px;
    }

    .shop-stars i {
        color: #D5DCD8;
        font-size: 8px;
    }

    .shop-stars i.active {
        color: #F4B400;
    }

    .shop-review p {
        margin: 11px 0;
        color: #536159;
        font-size:11px;
        line-height: 1.65;
    }

    .shop-review small {
        color: #89948F;
        font-size: 7px;
    }

    .shop-no-reviews,
    .shop-empty-products {
        padding: 50px 20px;
        text-align: center;
        color: #748079;
    }

    .shop-no-reviews > i,
    .shop-empty-products > i {
        margin-bottom: 10px;
        color: #12B76A;
        font-size: 25px;
    }

    .shop-no-reviews h3,
    .shop-empty-products h3 {
        margin: 0;
        color: #28362F;
        font-size: 13px;
    }

    .shop-no-reviews p,
    .shop-empty-products p {
        margin: 5px 0 0;
        font-size:11px;
    }

    .shop-modal[hidden] {
        display: none !important;
    }

    .shop-modal {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .shop-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(9,24,17,.62);
        backdrop-filter: blur(5px);
    }

    .shop-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(900px, 100%);
        max-height: calc(100vh - 35px);
        overflow-y: auto;
        border: 1px solid #DDE5E1;
        border-radius: 20px;
        background: #FFFFFF;
        box-shadow: 0 30px 90px rgba(0,0,0,.28);
    }

    .shop-modal-close {
        position: absolute;
        right: 13px;
        top: 13px;
        z-index: 20;
        width: 37px;
        height: 37px;
        display: grid;
        place-items: center;
        border: 1px solid #DCE5E0;
        border-radius: 10px;
        background: rgba(255,255,255,.96);
        color: #3D4B44;
        cursor: pointer;
    }

    .shop-modal-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
    }

    .shop-modal-gallery {
        padding: 20px;
        background: #F8FAF9;
    }

    .shop-modal-main-image {
        position: relative;
        height: 470px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid #E2E8E5;
        border-radius: 15px;
        background: #FFFFFF;
    }

    .shop-modal-main-image img {
        width: 100%;
        height: 100%;
        padding: 10px;
        object-fit: contain;
        object-position: center;
    }

    .shop-modal-no-image {
        color: #12B76A;
        font-size: 45px;
    }

    .shop-modal-gallery-button {
        position: absolute;
        top: 50%;
        width: 37px;
        height: 37px;
        display: grid;
        place-items: center;
        border: 1px solid #DDE5E1;
        border-radius: 50%;
        background: rgba(255,255,255,.96);
        color: #0B3D2E;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .shop-modal-gallery-button.previous {
        left: 10px;
    }

    .shop-modal-gallery-button.next {
        right: 10px;
    }

    .shop-modal-thumbnails {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        overflow-x: auto;
    }

    .shop-modal-thumbnail {
        width: 62px;
        height: 62px;
        flex: 0 0 62px;
        overflow: hidden;
        padding: 3px;
        border: 1px solid #DCE5E0;
        border-radius: 9px;
        background: #FFFFFF;
        cursor: pointer;
    }

    .shop-modal-thumbnail.active {
        border-color: #12B76A;
        box-shadow: 0 0 0 2px rgba(18,183,106,.12);
    }

    .shop-modal-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .shop-modal-details {
        padding: 55px 26px 27px;
    }

    .shop-modal-business {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 10px;
        color: #087443;
        font-size: 8px;
        font-weight: 800;
    }

    .shop-modal-details h2 {
        margin: 0;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.22;
    }

    .shop-modal-price {
        margin: 12px 0;
        color: #101915;
        font-size: 24px;
        font-weight: 800;
    }

    .shop-modal-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding-bottom: 17px;
        border-bottom: 1px solid #E8ECEA;
        color: #69766F;
        font-size: 8px;
    }

    .shop-modal-meta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .shop-modal-meta i {
        color: #12B76A;
    }

    .shop-modal-description-heading {
        margin: 17px 0 8px;
        color: #28362F;
        font-size:12px;
        font-weight: 800;
    }

    .shop-rich-description {
        max-height: 270px;
        overflow-y: auto;
        color: #526059;
        font-size:12px;
        line-height: 1.72;
    }

    .shop-rich-description img {
        max-width: 100%;
        height: auto;
    }

    .shop-rich-description table {
        width: 100%;
        border-collapse: collapse;
    }

    .shop-rich-description td,
    .shop-rich-description th {
        padding: 6px;
        border: 1px solid #DCE5E0;
    }

    .shop-modal-buy,
    .shop-modal-whatsapp {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        font-size:12px;
        font-weight: 800;
        text-decoration: none;
    }

    .shop-modal-buy {
        margin-top: 20px;
        background: #12B76A;
        color: #FFFFFF;
    }

    .shop-modal-whatsapp {
        margin-top: 8px;
        background: #16A34A;
        color: #FFFFFF;
    }

    .shop-modal-whatsapp[hidden] {
        display: none !important;
    }

    .shop-modal-security-note {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-top: 10px;
        color: #7A8780;
        font-size: 8px;
        line-height: 1.5;
    }

    .shop-modal-security-note i {
        margin-top: 2px;
        color: #12B76A;
    }

    @media(max-width: 850px) {

        .shop-hero-card,
        .shop-modal-grid {
            grid-template-columns: 1fr;
        }

        .shop-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .shop-info-grid {
            grid-template-columns: 1fr;
        }

        .shop-modal-main-image {
            height: 350px;
        }

    }

    @media(max-width: 600px) {

        .shop-business-main {
            flex-direction: column;
        }

        .shop-metrics,
        .shop-products-grid,
        .shop-reviews-list {
            grid-template-columns: 1fr;
        }

        .shop-section-nav {
            overflow-x: auto;
        }

        .shop-section-nav a {
            flex: none;
        }

        .shop-product-buttons.has-whatsapp {
            grid-template-columns: minmax(0, 1fr) 42px 42px;
        }

        .shop-modal {
            padding: 7px;
        }

        .shop-modal-dialog {
            border-radius: 15px;
        }

        .shop-modal-main-image {
            height: 290px;
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
        | Product Card Carousels
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-product-carousel]'
            )
            .forEach(
                function (carousel) {

                    const slides =
                        Array.from(
                            carousel.querySelectorAll(
                                '[data-product-slide]'
                            )
                        );


                    const dots =
                        Array.from(
                            carousel.querySelectorAll(
                                '[data-product-dot]'
                            )
                        );


                    let current =
                        0;


                    function show(
                        index
                    ) {
                        if (
                            slides.length === 0
                        ) {
                            return;
                        }


                        if (
                            index < 0
                        ) {
                            index =
                                slides.length - 1;
                        }


                        if (
                            index >=
                            slides.length
                        ) {
                            index =
                                0;
                        }


                        current =
                            index;


                        slides.forEach(
                            function (
                                slide,
                                slideIndex
                            ) {

                                slide.classList.toggle(
                                    'active',
                                    slideIndex === current
                                );
                            }
                        );


                        dots.forEach(
                            function (
                                dot,
                                dotIndex
                            ) {

                                dot.classList.toggle(
                                    'active',
                                    dotIndex === current
                                );
                            }
                        );
                    }


                    const previous =
                        carousel.querySelector(
                            '[data-product-prev]'
                        );


                    const next =
                        carousel.querySelector(
                            '[data-product-next]'
                        );


                    if (previous) {

                        previous.addEventListener(
                            'click',
                            function () {

                                show(
                                    current - 1
                                );
                            }
                        );
                    }


                    if (next) {

                        next.addEventListener(
                            'click',
                            function () {

                                show(
                                    current + 1
                                );
                            }
                        );
                    }


                    dots.forEach(
                        function (
                            dot,
                            index
                        ) {

                            dot.addEventListener(
                                'click',
                                function () {

                                    show(
                                        index
                                    );
                                }
                            );
                        }
                    );


                    show(0);
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Product Modal
        |--------------------------------------------------------------------------
        */

        const modal =
            document.getElementById(
                'shopProductModal'
            );


        const payloadElement =
            document.getElementById(
                'shopProductPayload'
            );


        if (
            !modal
            ||
            !payloadElement
        ) {
            return;
        }


        let products = {};


        try {

            products =
                JSON.parse(
                    payloadElement.textContent
                );

        } catch (error) {

            console.error(
                'Unable to parse product payload.',
                error
            );

            return;
        }


        const modalImage =
            document.getElementById(
                'shopModalImage'
            );


        const noImage =
            document.getElementById(
                'shopModalNoImage'
            );


        const previousButton =
            document.getElementById(
                'shopModalPrevious'
            );


        const nextButton =
            document.getElementById(
                'shopModalNext'
            );


        const thumbnails =
            document.getElementById(
                'shopModalThumbnails'
            );


        const title =
            document.getElementById(
                'shopModalTitle'
            );


        const price =
            document.getElementById(
                'shopModalPrice'
            );


        const stock =
            document.getElementById(
                'shopModalStock'
            );


        const description =
            document.getElementById(
                'shopModalDescription'
            );


        const buyButton =
            document.getElementById(
                'shopModalBuyButton'
            );


        const whatsappButton =
            document.getElementById(
                'shopModalWhatsappButton'
            );
                if (
            buyButton
        ) {

            buyButton.addEventListener(
                'click',
                function (
                    event
                ) {

                    if (
                        buyButton.getAttribute(
                            'aria-disabled'
                        )
                        ===
                        'true'
                    ) {

                        event.preventDefault();

                    }

                }
            );

        }


        let currentImages =
            [];


        let currentIndex =
            0;


        function showModalImage(
            index
        ) {
            if (
                currentImages.length === 0
            ) {

                modalImage.hidden =
                    true;

                noImage.hidden =
                    false;

                previousButton.hidden =
                    true;

                nextButton.hidden =
                    true;

                thumbnails.hidden =
                    true;

                return;
            }


            if (
                index < 0
            ) {
                index =
                    currentImages.length - 1;
            }


            if (
                index >=
                currentImages.length
            ) {
                index =
                    0;
            }


            currentIndex =
                index;


            thumbnails.hidden =
                false;

            modalImage.hidden =
                false;

            noImage.hidden =
                true;

            modalImage.src =
                currentImages[currentIndex];


            const multiple =
                currentImages.length > 1;


            previousButton.hidden =
                !multiple;

            nextButton.hidden =
                !multiple;


            thumbnails
                .querySelectorAll(
                    '.shop-modal-thumbnail'
                )
                .forEach(
                    function (
                        thumbnail,
                        thumbnailIndex
                    ) {

                        thumbnail.classList.toggle(
                            'active',
                            thumbnailIndex === currentIndex
                        );
                    }
                );
        }


        function renderThumbnails()
        {
            thumbnails.innerHTML =
                '';


            currentImages.forEach(
                function (
                    image,
                    index
                ) {

                    const button =
                        document.createElement(
                            'button'
                        );


                    button.type =
                        'button';


                    button.className =
                        'shop-modal-thumbnail';


                    const img =
                        document.createElement(
                            'img'
                        );


                    img.src =
                        image;


                    img.alt =
                        'Product image';


                    button.appendChild(
                        img
                    );


                    button.addEventListener(
                        'click',
                        function () {

                            showModalImage(
                                index
                            );
                        }
                    );


                    thumbnails.appendChild(
                        button
                    );
                }
            );
        }


        function openProduct(
            product
        ) {
            currentImages =
                Array.isArray(
                    product.images
                )
                    ? product.images
                    : [];


            title.textContent =
                product.name;


            price.textContent =
                product.price;


            stock.textContent =
                product.stock;


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Product descriptions must already be sanitized when stored.
            |
            */

            description.innerHTML =
                product.description
                ||
                'No product description available.';


            if (
                Number(
                    product.stock
                )
                >
                0

                &&

                product.buy_url
            ) {

                buyButton.href =
                    product.buy_url;


                buyButton.classList.remove(
                    'shop-modal-buy-disabled'
                );


                buyButton.removeAttribute(
                    'aria-disabled'
                );


                buyButton.innerHTML =
                    '<i class="fa-solid fa-shield-halved"></i> Buy securely';

            } else {

                buyButton.href =
                    '#';


                buyButton.classList.add(
                    'shop-modal-buy-disabled'
                );


                buyButton.setAttribute(
                    'aria-disabled',
                    'true'
                );


                buyButton.innerHTML =
                    '<i class="fa-solid fa-box-open"></i> Out of stock';

            }


            if (
                product.whatsapp_url
            ) {

                whatsappButton.href =
                    product.whatsapp_url;

                whatsappButton.hidden =
                    false;

            } else {

                whatsappButton.href =
                    '#';

                whatsappButton.hidden =
                    true;
            }


            renderThumbnails();


            showModalImage(
                0
            );


            modal.hidden =
                false;


            document.body.style.overflow =
                'hidden';
        }


        document
            .querySelectorAll(
                '[data-view-product]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.dataset
                                    .viewProduct;


                            if (
                                !products[id]
                            ) {
                                return;
                            }


                            openProduct(
                                products[id]
                            );
                        }
                    );
                }
            );


        previousButton.addEventListener(
            'click',
            function () {

                showModalImage(
                    currentIndex - 1
                );
            }
        );


        nextButton.addEventListener(
            'click',
            function () {

                showModalImage(
                    currentIndex + 1
                );
            }
        );


        function closeModal()
        {
            modal.hidden =
                true;


            document.body.style.overflow =
                '';


            modalImage.src =
                '';


            thumbnails.innerHTML =
                '';
        }


        document
            .querySelectorAll(
                '[data-close-product-modal]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        closeModal
                    );
                }
            );


        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape'
                    &&
                    !modal.hidden
                ) {

                    closeModal();
                }
            }
        );

    }
);

</script>

@endpush


@endsection