@extends('frontend.layouts.app')


@section('title', 'Featured Businesses | MidPoint')


@section('content')

<section class="fb-directory">

    <div class="fb-wrap">


        {{-- =========================================================
            HEADER
        ========================================================== --}}

        <header class="fb-hero">

            <div class="fb-eyebrow">

                <span></span>

                Featured businesses

            </div>


            <h1>
                Verified sellers you can trust.
            </h1>


            <p>
                Discover active MidPoint Verified Sellers, search by
                business, product, category or location, and visit each
                shop before starting a secure transaction.
            </p>

        </header>



        {{-- =========================================================
            FILTERS
        ========================================================== --}}

        <form
            method="GET"
            action="{{ route('featured-businesses') }}"
            class="fb-filter-card"
        >

            <div class="fb-search-field">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search seller, Android watch, iPhone, laptop..."
                >

            </div>


            <div class="fb-select-wrap">

                <i class="fa-solid fa-layer-group"></i>

                <select name="category">

                    <option value="">
                        All categories
                    </option>

                    @foreach ($categories as $categoryOption)

                        <option
                            value="{{ $categoryOption }}"
                            @selected($category === $categoryOption)
                        >
                            {{ $categoryOption }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="fb-select-wrap">

                <i class="fa-solid fa-location-dot"></i>

                <select name="location">

                    <option value="">
                        All locations
                    </option>

                    @foreach ($locations as $locationOption)

                        <option
                            value="{{ $locationOption }}"
                            @selected($location === $locationOption)
                        >
                            {{ $locationOption }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="fb-select-wrap">

                <i class="fa-solid fa-arrow-down-wide-short"></i>

                <select name="sort">

                    <option
                        value="newest"
                        @selected($sort === 'newest')
                    >
                        Newest
                    </option>

                    <option
                        value="name"
                        @selected($sort === 'name')
                    >
                        Business name
                    </option>

                    <option
                        value="rating"
                        @selected($sort === 'rating')
                    >
                        Highest rating
                    </option>

                    <option
                        value="products"
                        @selected($sort === 'products')
                    >
                        Most products
                    </option>

                </select>

            </div>


            <input
                type="hidden"
                name="per_page"
                value="{{ $perPage }}"
            >


            <button
                type="submit"
                class="fb-filter-button"
            >

                <i class="fa-solid fa-sliders"></i>

                Search

            </button>

        </form>



        {{-- =========================================================
            RESULT BAR
        ========================================================== --}}

        <div class="fb-result-bar">

            <div>

                <strong>
                    {{ number_format($sellers->total()) }}
                </strong>

                verified
                {{ $sellers->total() === 1 ? 'business' : 'businesses' }}

                @if ($search !== '')

                    matching

                    <strong>
                        "{{ $search }}"
                    </strong>

                @endif

            </div>


            <div class="fb-result-actions">

                @if ($search !== '' || $category !== '' || $location !== '')

                    <a
                        href="{{ route('featured-businesses') }}"
                        class="fb-clear-filter"
                    >

                        <i class="fa-solid fa-xmark"></i>

                        Clear filters

                    </a>

                @endif


                <form
                    method="GET"
                    action="{{ route('featured-businesses') }}"
                >

                    <input
                        type="hidden"
                        name="search"
                        value="{{ $search }}"
                    >

                    <input
                        type="hidden"
                        name="category"
                        value="{{ $category }}"
                    >

                    <input
                        type="hidden"
                        name="location"
                        value="{{ $location }}"
                    >

                    <input
                        type="hidden"
                        name="sort"
                        value="{{ $sort }}"
                    >


                    <label>

                        Show

                        <select
                            name="per_page"
                            onchange="this.form.submit()"
                        >

                            @foreach ([12, 30, 45] as $size)

                                <option
                                    value="{{ $size }}"
                                    @selected($perPage === $size)
                                >
                                    {{ $size }}
                                </option>

                            @endforeach

                        </select>

                    </label>

                </form>

            </div>

        </div>



        {{-- =========================================================
            SELLERS
        ========================================================== --}}

        @if ($sellers->count())

            <div class="fb-business-grid">

                @foreach ($sellers as $seller)

                    @php
                        $subscription =
                            $seller->activeSellerSubscription;

                        $application =
                            optional($subscription)->application;

                        $profile =
                            $seller->sellerBusinessProfile;

                        $businessName =
                            optional($application)->business_name
                            ?: $seller->name;

                        $categoryName =
                            optional($application)->category
                            ?: 'Verified Seller';

                        $publicLocation =
                            optional($profile)->location
                            ?: optional($application)->location
                            ?: 'Location not specified';

                        $description =
                            optional($profile)->tagline
                            ?: optional($profile)->about
                            ?: optional($application)->description
                            ?: 'Verified MidPoint seller.';

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

                        $gradientList = [
                            'linear-gradient(135deg,#0B3D2E,#12B76A)',
                            'linear-gradient(135deg,#6941C6,#9E77ED)',
                            'linear-gradient(135deg,#B54708,#F79009)',
                            'linear-gradient(135deg,#175CD3,#53B1FD)',
                            'linear-gradient(135deg,#9E165F,#EE46BC)',
                        ];

                        $gradient =
                            $gradientList[
                                $seller->id % count($gradientList)
                            ];
                    @endphp


                    <article class="fb-business-card">

                        <div class="fb-business-top">


                            {{-- =====================================
                                PROFILE IMAGE
                            ====================================== --}}

                            <div
                                class="fb-business-avatar"
                                style="background: {{ $gradient }};"
                            >

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



                            <div class="fb-business-title">

                                <div class="fb-business-name-row">

                                    <h2>
                                        {{ $businessName }}
                                    </h2>


                                    <span class="fb-verified-mini">

                                        <i class="fa-solid fa-check"></i>

                                        Verified

                                    </span>

                                </div>


                                <p>
                                    {{ $categoryName }}
                                </p>

                            </div>



                            <div class="fb-rating">

                                @if ($seller->seller_rating)

                                    <i class="fa-solid fa-star"></i>

                                    {{ number_format($seller->seller_rating, 1) }}

                                @else

                                    <span>
                                        New
                                    </span>

                                @endif

                            </div>

                        </div>



                        <p class="fb-description">
                            {{ \Illuminate\Support\Str::limit(strip_tags($description), 145) }}
                        </p>



                        <div class="fb-business-details">

                            <span>

                                <i class="fa-solid fa-location-dot"></i>

                                {{ $publicLocation }}

                            </span>


                            <span>

                                <i class="fa-solid fa-box"></i>

                                {{ number_format($seller->active_products_count) }}

                                listed products

                            </span>


                            @if ($seller->seller_review_count > 0)

                                <span>

                                    <i class="fa-solid fa-star"></i>

                                    {{ number_format($seller->seller_review_count) }}

                                    {{ $seller->seller_review_count === 1 ? 'review' : 'reviews' }}

                                </span>

                            @endif


                            @if ($subscription && $subscription->package_name)

                                <span>

                                    <i class="fa-solid fa-gem"></i>

                                    {{ $subscription->package_name }}

                                </span>

                            @endif

                        </div>



                        {{-- =========================================
                            SOCIAL INDICATORS
                        ========================================== --}}

                        @if (
                            $profile
                            &&
                            (
                                $profile->whatsapp_enabled
                                ||
                                $profile->website_url
                                ||
                                $profile->instagram_url
                            )
                        )

                            <div class="fb-contact-chips">

                                @if ($profile->whatsapp_enabled && $profile->whatsapp_number)

                                    <span class="whatsapp">

                                        <i class="fa-brands fa-whatsapp"></i>

                                        WhatsApp

                                    </span>

                                @endif


                                @if ($profile->website_url)

                                    <span>

                                        <i class="fa-solid fa-globe"></i>

                                        Website

                                    </span>

                                @endif


                                @if ($profile->instagram_url)

                                    <span>

                                        <i class="fa-brands fa-instagram"></i>

                                        Instagram

                                    </span>

                                @endif

                            </div>

                        @endif



                        <div class="fb-business-footer">

                            <a
                                href="{{ route('featured-businesses.show', $seller) }}"
                                class="fb-view-button"
                            >

                                View business

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>

                        </div>

                    </article>

                @endforeach

            </div>



            {{-- =====================================================
                PAGINATION
            ====================================================== --}}

            <div class="fb-pagination-wrap">

                <div class="fb-pagination-meta">

                    Showing

                    <strong>
                        {{ $sellers->firstItem() }}
                    </strong>

                    -

                    <strong>
                        {{ $sellers->lastItem() }}
                    </strong>

                    of

                    <strong>
                        {{ $sellers->total() }}
                    </strong>

                    businesses

                </div>


                <div class="fb-pagination-links">
                    {{ $sellers->onEachSide(1)->links() }}
                </div>

            </div>

        @else

            <div class="fb-empty">

                <div class="fb-empty-icon">

                    <i class="fa-solid fa-store-slash"></i>

                </div>


                <h2>
                    No verified businesses found
                </h2>


                <p>
                    Try another seller name, product, category or location.
                </p>


                <a href="{{ route('featured-businesses') }}">
                    Reset all filters
                </a>

            </div>

        @endif



        {{-- =========================================================
            CTA
        ========================================================== --}}

        <div class="fb-seller-cta">

            <div>

                <h3>
                    Own a business?
                </h3>


                <p>
                    Become verified, activate a seller package
                    and list your products here.
                </p>

            </div>


            <a href="{{ route('verified-sellers') }}">

                Become a verified seller

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </div>

</section>


@push('styles')

<style>

    .fb-directory {
        min-height: calc(100vh - 70px);
        padding: 58px 20px 70px;
        background: #F6F9F7;
    }

    .fb-wrap {
        width: 100%;
        max-width: 1160px;
        margin: 0 auto;
    }

    .fb-hero {
        max-width: 720px;
        margin-bottom: 30px;
    }

    .fb-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        color: #0E9F63;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .fb-eyebrow span {
        width: 22px;
        height: 2px;
        border-radius: 20px;
        background: #12B76A;
    }

    .fb-hero h1 {
        margin: 0;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: clamp(31px, 4vw, 47px);
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1.07;
    }

    .fb-hero p {
        max-width: 650px;
        margin: 14px 0 0;
        color: #58645E;
        font-size: 14px;
        line-height: 1.7;
    }

    .fb-filter-card {
        display: grid;
        grid-template-columns:
            minmax(260px, 1.8fr)
            minmax(155px, .8fr)
            minmax(155px, .8fr)
            minmax(145px, .7fr)
            auto;
        gap: 9px;
        padding: 13px;
        border: 1px solid #DCE5E0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 10px 35px -25px rgba(11,61,46,.30);
    }

    .fb-search-field,
    .fb-select-wrap {
        position: relative;
    }

    .fb-search-field i,
    .fb-select-wrap i {
        position: absolute;
        left: 13px;
        top: 50%;
        color: #809089;
        font-size: 11px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .fb-search-field input,
    .fb-select-wrap select {
        width: 100%;
        height: 45px;
        padding: 0 12px 0 36px;
        border: 1px solid #E0E7E3;
        border-radius: 11px;
        background: #FAFCFB;
        color: #233129;
        font-family: inherit;
        font-size: 11px;
        outline: none;
    }

    .fb-search-field input:focus,
    .fb-select-wrap select:focus {
        border-color: #12B76A;
        box-shadow: 0 0 0 3px rgba(18,183,106,.08);
    }

    .fb-filter-button {
        height: 45px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 18px;
        border: 0;
        border-radius: 11px;
        background: #0B3D2E;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .fb-result-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin: 17px 0;
        color: #6A7770;
        font-size:12px;
    }

    .fb-result-bar strong {
        color: #15231C;
    }

    .fb-result-actions {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .fb-clear-filter {
        color: #D92D20;
        font-weight: 700;
        text-decoration: none;
    }

    .fb-result-actions label {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .fb-result-actions select {
        height: 34px;
        padding: 0 9px;
        border: 1px solid #DCE5E0;
        border-radius: 8px;
        background: #FFFFFF;
        font-size:12px;
    }

    .fb-business-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .fb-business-card {
        min-width: 0;
        min-height: 300px;
        display: flex;
        flex-direction: column;
        padding: 20px;
        border: 1px solid #DCE5E0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 8px 27px -20px rgba(11,61,46,.28);
        transition: .17s ease;
    }

    .fb-business-card:hover {
        border-color: #BBDDCB;
        transform: translateY(-3px);
        box-shadow: 0 18px 40px -28px rgba(11,61,46,.40);
    }

    .fb-business-top {
        display: flex;
        align-items: flex-start;
        gap: 11px;
    }

    .fb-business-avatar {
        width: 56px;
        height: 56px;
        flex: 0 0 56px;
        overflow: hidden;
        display: grid;
        place-items: center;
        border-radius: 15px;
        color: #FFFFFF;
        font-size: 15px;
        font-weight: 800;
    }

    .fb-business-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        object-position: center;
    }

    .fb-business-title {
        min-width: 0;
        flex: 1;
    }

    .fb-business-name-row {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fb-business-name-row h2 {
        max-width: 175px;
        overflow: hidden;
        margin: 2px 0 1px;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 14px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fb-business-title p {
        margin: 2px 0 0;
        color: #718078;
        font-size:11px;
    }

    .fb-verified-mini {
        flex: none;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 6px;
        border-radius: 999px;
        background: #ECFDF3;
        color: #067647;
        font-size: 7px;
        font-weight: 800;
    }

    .fb-rating {
        flex: none;
        padding-top: 4px;
        color: #087443;
        font-size:12px;
        font-weight: 800;
    }

    .fb-rating i {
        color: #F4B400;
    }

    .fb-rating span {
        padding: 4px 7px;
        border-radius: 999px;
        background: #F2F5F3;
        color: #748079;
        font-size: 8px;
    }

    .fb-description {
        min-height: 58px;
        margin: 16px 0;
        color: #59675F;
        font-size:12px;
        line-height: 1.65;
    }

    .fb-business-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        color: #718078;
        font-size: 8px;
    }

    .fb-business-details span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .fb-business-details i {
        color: #12B76A;
    }

    .fb-contact-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 13px;
    }

    .fb-contact-chips span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        border-radius: 999px;
        background: #F3F6F4;
        color: #647169;
        font-size: 7px;
        font-weight: 700;
    }

    .fb-contact-chips .whatsapp {
        background: #ECFDF3;
        color: #15803D;
    }

    .fb-business-footer {
        margin-top: auto;
        padding-top: 18px;
    }

    .fb-view-button {
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        background: #12B76A;
        color: #FFFFFF;
        font-size:12px;
        font-weight: 800;
        text-decoration: none;
    }

    .fb-pagination-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 25px;
    }

    .fb-pagination-meta {
        color: #718078;
        font-size:11px;
    }

    .fb-pagination-links nav {
        font-size:12px;
    }

    .fb-empty {
        padding: 65px 20px;
        border: 1px dashed #CFDBD5;
        border-radius: 18px;
        background: #FFFFFF;
        text-align: center;
    }

    .fb-empty-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin: 0 auto 15px;
        border-radius: 16px;
        background: #E8F7EF;
        color: #12B76A;
        font-size: 20px;
    }

    .fb-empty h2 {
        margin: 0;
        font-size: 18px;
    }

    .fb-empty p {
        color: #718078;
        font-size:12px;
    }

    .fb-empty a {
        color: #087443;
        font-size:12px;
        font-weight: 800;
    }

    .fb-seller-cta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-top: 30px;
        padding: 24px;
        border-radius: 18px;
        background: linear-gradient(120deg,#0B3D2E,#6941C6);
        color: #FFFFFF;
    }

    .fb-seller-cta h3 {
        margin: 0 0 3px;
        font-size: 16px;
    }

    .fb-seller-cta p {
        margin: 0;
        color: #E2DDFB;
        font-size:12px;
    }

    .fb-seller-cta a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 15px;
        border-radius: 10px;
        background: #FFFFFF;
        color: #0B3D2E;
        font-size:12px;
        font-weight: 800;
        text-decoration: none;
    }

    @media(max-width: 980px) {

        .fb-filter-card {
            grid-template-columns: 1fr 1fr;
        }

        .fb-search-field {
            grid-column: 1 / -1;
        }

        .fb-business-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    @media(max-width: 640px) {

        .fb-directory {
            padding: 40px 14px;
        }

        .fb-filter-card,
        .fb-business-grid {
            grid-template-columns: 1fr;
        }

        .fb-search-field {
            grid-column: auto;
        }

        .fb-result-bar,
        .fb-pagination-wrap,
        .fb-seller-cta {
            flex-direction: column;
            align-items: stretch;
        }

        .fb-result-actions {
            justify-content: space-between;
        }

    }

</style>

@endpush


@endsection