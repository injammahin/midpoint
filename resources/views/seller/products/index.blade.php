@extends('seller.layouts.app')

@section('title', 'Listed Products')


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Limit
    |--------------------------------------------------------------------------
    */

    $limitReached =
        $usedProducts
        >=
        $productLimit;


    /*
    |--------------------------------------------------------------------------
    | Product Data For Edit Modal
    |--------------------------------------------------------------------------
    */

    $productEditData =
        $products
            ->mapWithKeys(
                function ($product) {

                    $images =
                        $product
                            ->all_images;


                    return [
                        $product->id => [

                            'id' =>
                                $product->id,

                            'name' =>
                                $product->name,

                            'description' =>
                                $product->description,

                            'price' =>
                                $product->price,

                            'stock' =>
                                $product->stock,

                            'update_url' =>
                                route(
                                    'seller.products.update',
                                    $product
                                ),

                            'delete_url' =>
                                route(
                                    'seller.products.destroy',
                                    $product
                                ),

                            'images' =>
                                collect($images)
                                    ->map(
                                        function ($path) {

                                            return [

                                                'path' =>
                                                    $path,

                                                'url' =>
                                                    asset(
                                                        'storage/'
                                                        .
                                                        $path
                                                    ),

                                            ];
                                        }
                                    )
                                    ->values()
                                    ->all(),

                        ],
                    ];
                }
            );

@endphp



<div class="seller-products-page">


    {{-- =========================================================
        FLASH MESSAGES
    ========================================================== --}}

    @if(session('success'))

        <div class="sp-alert sp-alert-success">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif



    @if(session('error'))

        <div class="sp-alert sp-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif



    @if($errors->any())

        <div class="sp-alert sp-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                @foreach($errors->all() as $error)

                    <div>
                        {{ $error }}
                    </div>

                @endforeach

            </div>

        </div>

    @endif



    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="sp-page-header">

        <div>

            <h1>
                Listed products
            </h1>


            <p>

                These appear on your Featured Business profile —
                buyers can start a secure transaction from any of them.

            </p>

        </div>


        <div class="sp-package-badge">

            <i class="fa-solid fa-circle-check"></i>

            Verified ·
            {{ $subscription->package_name }}
            package

        </div>

    </div>



    {{-- =========================================================
        SLOTS
    ========================================================== --}}

    <div class="sp-slot-card">

        <div class="sp-slot-top">

            <strong>
                Product slots used
            </strong>


            <strong>

                {{ $usedProducts }}
                /
                {{ $productLimit }}

            </strong>

        </div>


        <div class="sp-slot-row">

            <div class="sp-progress">

                <div
                    class="sp-progress-fill"
                    style="width: {{ $usagePercentage }}%;"
                ></div>

            </div>


            <div class="sp-upgrade">

                @if($upgradePackage)

                    <span>
                        Need more slots?
                    </span>


                    <a
                        href="{{
                            route(
                                'verified-sellers',
                                [
                                    'package' =>
                                        $upgradePackage->id
                                ]
                            )
                        }}"
                    >

                        Upgrade to
                        {{ $upgradePackage->name }}

                        ({{
                            number_format(
                                $upgradePackage
                                    ->product_limit
                            )
                        }}
                        products)

                    </a>

                @else

                    <span>

                        {{ $remainingProducts }}

                        slot{{
                            $remainingProducts === 1
                                ? ''
                                : 's'
                        }}

                        remaining

                    </span>

                @endif

            </div>

        </div>


        @if($subscription->expires_at)

            <div class="sp-package-expiry">

                <i class="fa-regular fa-clock"></i>

                Plan expires:

                <strong>

                    {{
                        $subscription
                            ->expires_at
                            ->format(
                                'd M Y, h:i A'
                            )
                    }}

                </strong>

                ·

                {{ $subscription->days_left }}
                days left

            </div>

        @endif

    </div>



    {{-- =========================================================
        MAIN
    ========================================================== --}}

    <div class="sp-main-grid">


        {{-- =====================================================
            ADD PRODUCT
        ====================================================== --}}

        <div class="sp-product-form-card">

            <div class="sp-form-heading">

                <h2>
                    Add a product
                </h2>


                <p>

                    Buyers will see exactly what you enter here.

                </p>

            </div>



            @if($limitReached)

                <div class="sp-limit-message">

                    <span class="sp-limit-icon">

                        <i class="fa-solid fa-lock"></i>

                    </span>


                    <div>

                        <strong>
                            Product limit reached
                        </strong>


                        <p>

                            Your
                            {{ $subscription->package_name }}
                            package allows
                            {{ $productLimit }}
                            products.

                        </p>


                        @if($upgradePackage)

                            <a
                                href="{{
                                    route(
                                        'verified-sellers',
                                        [
                                            'package' =>
                                                $upgradePackage->id
                                        ]
                                    )
                                }}"
                            >

                                Upgrade package →

                            </a>

                        @endif

                    </div>

                </div>

            @endif



            <form
                method="POST"

                action="{{
                    route(
                        'seller.products.store'
                    )
                }}"

                enctype="multipart/form-data"

                id="addProductForm"
            >

                @csrf



                {{-- =================================================
                    FLEXIBLE IMAGE MANAGER
                ================================================== --}}

                <div class="sp-field">

                    <label>
                        Product images
                    </label>


                    <input
                        id="addProductImages"

                        type="file"

                        name="images[]"

                        accept="
                            image/jpeg,
                            image/png,
                            image/webp
                        "

                        multiple

                        hidden

                        {{
                            $limitReached
                                ? 'disabled'
                                : ''
                        }}
                    >


                    <div
                        class="
                            sp-image-manager

                            {{
                                $limitReached
                                    ? 'is-disabled'
                                    : ''
                            }}
                        "
                    >

                        <button
                            type="button"
                            class="sp-image-add-button"
                            id="addProductImageButton"

                            {{
                                $limitReached
                                    ? 'disabled'
                                    : ''
                            }}
                        >

                            <i class="fa-regular fa-image"></i>


                            <strong>
                                Add product images
                            </strong>


                            <span>

                                Select one image at a time
                                or multiple images together.

                            </span>


                            <small>

                                Up to 4 · JPG, PNG or WEBP · max 5 MB each

                            </small>

                        </button>


                        <div
                            id="addProductImagePreview"
                            class="sp-image-selection-grid"
                        ></div>


                        <div
                            id="addImageCounter"
                            class="sp-image-counter"
                        >

                            0 / 4 images selected

                        </div>

                    </div>

                </div>



                {{-- =================================================
                    NAME
                ================================================== --}}

                <div class="sp-field">

                    <label for="addProductName">
                        Product name
                    </label>


                    <input
                        id="addProductName"

                        type="text"

                        name="name"

                        value="{{ old('name') }}"

                        placeholder="e.g. iPhone 13, 128GB (UK used)"

                        required

                        {{
                            $limitReached
                                ? 'disabled'
                                : ''
                        }}
                    >

                </div>



                {{-- =================================================
                    SUMMERNOTE DESCRIPTION
                ================================================== --}}

                <div class="sp-field">

                    <label for="addProductDescription">
                        Description
                    </label>


                    <textarea
                        id="addProductDescription"

                        name="description"

                        required

                        {{
                            $limitReached
                                ? 'disabled'
                                : ''
                        }}
                    >{{ old('description') }}</textarea>

                </div>



                {{-- =================================================
                    PRICE + STOCK
                ================================================== --}}

                <div class="sp-two-fields">

                    <div class="sp-field">

                        <label for="addProductPrice">
                            Price (₦)
                        </label>


                        <input
                            id="addProductPrice"

                            type="number"

                            name="price"

                            value="{{ old('price') }}"

                            min="1"

                            step="0.01"

                            placeholder="145000"

                            required

                            {{
                                $limitReached
                                    ? 'disabled'
                                    : ''
                            }}
                        >

                    </div>


                    <div class="sp-field">

                        <label for="addProductStock">
                            Stock available
                        </label>


                        <input
                            id="addProductStock"

                            type="number"

                            name="stock"

                            value="{{ old('stock', 1) }}"

                            min="0"

                            step="1"

                            required

                            {{
                                $limitReached
                                    ? 'disabled'
                                    : ''
                            }}
                        >

                    </div>

                </div>



                {{-- =================================================
                    DELIVERY
                ================================================== --}}

                <div class="sp-delivery-box">

                    <strong>

                        📦 Delivery: arranged by you

                    </strong>


                    <p>

                        You arrange delivery for every order
                        and mark it dispatched.

                        Agree any delivery cost with the buyer directly.

                    </p>

                </div>



                <button
                    type="submit"

                    class="sp-publish-button"

                    {{
                        $limitReached
                            ? 'disabled'
                            : ''
                    }}
                >

                    <i class="fa-solid fa-box-open"></i>

                    {{
                        $limitReached
                            ? 'Product limit reached'
                            : 'Publish product'
                    }}

                </button>


                <p class="sp-publish-note">

                    Goes live on your profile immediately.

                </p>

            </form>

        </div>



        {{-- =====================================================
            LIVE PRODUCTS
        ====================================================== --}}

        <div class="sp-live-products">

            <div class="sp-products-heading">

                <div>

                    <h2>
                        Your live products
                    </h2>


                    <p>

                        {{ $usedProducts }}

                        published product{{
                            $usedProducts === 1
                                ? ''
                                : 's'
                        }}

                    </p>

                </div>


                <a
                    href="{{ route('featured-businesses') }}"
                    class="sp-public-profile"
                >

                    View public profile →

                </a>

            </div>



            @if($products->isNotEmpty())

                <div class="sp-products-grid">


                    @foreach($products as $product)

                        @php

                            $productImages =
                                $product
                                    ->all_images;

                        @endphp


                        <article class="sp-product-card">


                            {{-- =========================================
                                IMAGE CAROUSEL
                            ========================================== --}}

                            <div
                                class="sp-product-carousel"

                                data-carousel
                            >

                                @if(count($productImages) > 0)

                                    <div class="sp-carousel-track">


                                        @foreach($productImages as $imageIndex => $image)

                                            <div
                                                class="
                                                    sp-carousel-slide

                                                    {{
                                                        $imageIndex === 0
                                                            ? 'is-active'
                                                            : ''
                                                    }}
                                                "
                                                data-slide="{{ $imageIndex }}"
                                            >

                                                <img
                                                    src="{{
                                                        asset(
                                                            'storage/'
                                                            .
                                                            $image
                                                        )
                                                    }}"

                                                    alt="{{ $product->name }}"
                                                >

                                            </div>

                                        @endforeach

                                    </div>



                                    {{-- =====================================
                                        PREVIOUS / NEXT
                                    ====================================== --}}

                                    @if(count($productImages) > 1)

                                        <button
                                            type="button"
                                            class="
                                                sp-carousel-control
                                                sp-carousel-prev
                                            "
                                            data-carousel-prev
                                            aria-label="Previous image"
                                        >

                                            <i class="fa-solid fa-chevron-left"></i>

                                        </button>


                                        <button
                                            type="button"
                                            class="
                                                sp-carousel-control
                                                sp-carousel-next
                                            "
                                            data-carousel-next
                                            aria-label="Next image"
                                        >

                                            <i class="fa-solid fa-chevron-right"></i>

                                        </button>


                                        <div class="sp-carousel-dots">

                                            @foreach($productImages as $imageIndex => $image)

                                                <button
                                                    type="button"

                                                    class="
                                                        sp-carousel-dot

                                                        {{
                                                            $imageIndex === 0
                                                                ? 'is-active'
                                                                : ''
                                                        }}
                                                    "

                                                    data-carousel-dot="{{ $imageIndex }}"

                                                    aria-label="
                                                        Image
                                                        {{ $imageIndex + 1 }}
                                                    "
                                                ></button>

                                            @endforeach

                                        </div>

                                    @endif


                                @else

                                    <div class="sp-product-placeholder">

                                        <i class="fa-solid fa-bag-shopping"></i>

                                    </div>

                                @endif



                                <span
                                    class="
                                        sp-product-status
                                        {{
                                            $product->is_active
                                                ? 'is-live'
                                                : 'is-hidden'
                                        }}
                                    "
                                >

                                    {{
                                        $product->is_active
                                            ? 'Live'
                                            : 'Hidden'
                                    }}

                                </span>

                            </div>



                            {{-- =========================================
                                BODY
                            ========================================== --}}

                            <div class="sp-product-body">

                                <h3>

                                    {{ $product->name }}

                                </h3>


                                <div class="sp-product-description-preview">

                                    {{
                                        \Illuminate\Support\Str::limit(
                                            trim(
                                                strip_tags(
                                                    $product->description
                                                )
                                            ),
                                            145
                                        )
                                    }}

                                </div>


                                <div class="sp-product-price">

                                    ₦{{
                                        number_format(
                                            (float)
                                            $product->price,
                                            0
                                        )
                                    }}

                                </div>


                                <div class="sp-product-meta">

                                    <span>

                                        <i class="fa-solid fa-box"></i>

                                        Stock:

                                        <strong>
                                            {{ $product->stock }}
                                        </strong>

                                    </span>


                                    <span>

                                        <i class="fa-solid fa-truck"></i>

                                        Seller delivery

                                    </span>

                                </div>



                                {{-- =====================================
                                    ACTIONS
                                ====================================== --}}

                                <div class="sp-product-actions">

                                    <span>

                                        Added
                                        {{
                                            $product
                                                ->created_at
                                                ->diffForHumans()
                                        }}

                                    </span>


                                    <div class="sp-product-action-buttons">

                                        {{-- Edit --}}
                                        <button
                                            type="button"

                                            class="sp-edit-button"

                                            data-edit-product="{{ $product->id }}"
                                        >

                                            <i class="fa-regular fa-pen-to-square"></i>

                                            Edit

                                        </button>


                                        {{-- Delete --}}
                                        <button
                                            type="button"

                                            class="sp-delete-button"

                                            data-delete-product="{{ $product->id }}"
                                        >

                                            <i class="fa-regular fa-trash-can"></i>

                                            Delete

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


            @else

                <div class="sp-empty-products">

                    <div class="sp-empty-icon">

                        <i class="fa-solid fa-box-open"></i>

                    </div>


                    <h3>
                        No live products yet
                    </h3>


                    <p>

                        Add your first product using the form.

                        It will appear here and on your
                        Featured Business profile.

                    </p>

                </div>

            @endif

        </div>

    </div>

</div>



{{-- =========================================================
    PRODUCT EDIT MODAL
========================================================== --}}

<div
    id="productEditModal"
    class="sp-modal"
    hidden
>

    <div
        class="sp-modal-backdrop"
        data-close-edit-modal
    ></div>


    <div
        class="
            sp-modal-dialog
            sp-edit-modal-dialog
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="editProductModalTitle"
    >

        <div class="sp-modal-header">

            <div>

                <span class="sp-modal-eyebrow">
                    Product manager
                </span>


                <h2 id="editProductModalTitle">
                    Edit product
                </h2>

            </div>


            <button
                type="button"
                class="sp-modal-close"
                data-close-edit-modal
                aria-label="Close"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>



        <form
            method="POST"
            enctype="multipart/form-data"
            id="editProductForm"
        >

            @csrf

            @method('PUT')



            <div class="sp-edit-modal-body">


                {{-- =================================================
                    EXISTING / NEW IMAGES
                ================================================== --}}

                <div class="sp-field">

                    <label>
                        Product images
                    </label>


                    <p class="sp-field-help">

                        Keep, remove, or add images.

                        You can add them one by one or select
                        several together.

                        Maximum 4 images total.

                    </p>


                    <div
                        id="editExistingImages"
                        class="sp-image-selection-grid"
                    ></div>


                    <input
                        id="editProductImages"

                        type="file"

                        name="images[]"

                        accept="
                            image/jpeg,
                            image/png,
                            image/webp
                        "

                        multiple

                        hidden
                    >


                    <button
                        type="button"
                        class="sp-edit-add-images"
                        id="editAddImagesButton"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add more images

                    </button>


                    <div
                        id="editNewImagePreview"
                        class="sp-image-selection-grid sp-edit-new-images"
                    ></div>


                    <div
                        id="editImageCounter"
                        class="sp-image-counter"
                    ></div>


                    <div id="editRemovedImageInputs"></div>

                </div>



                {{-- =================================================
                    NAME
                ================================================== --}}

                <div class="sp-field">

                    <label for="editProductName">
                        Product name
                    </label>


                    <input
                        id="editProductName"

                        type="text"

                        name="name"

                        required
                    >

                </div>



                {{-- =================================================
                    DESCRIPTION
                ================================================== --}}

                <div class="sp-field">

                    <label for="editProductDescription">
                        Description
                    </label>


                    <textarea
                        id="editProductDescription"
                        name="description"
                        required
                    ></textarea>

                </div>



                {{-- =================================================
                    PRICE / STOCK
                ================================================== --}}

                <div class="sp-two-fields">

                    <div class="sp-field">

                        <label for="editProductPrice">
                            Price (₦)
                        </label>


                        <input
                            id="editProductPrice"

                            type="number"

                            name="price"

                            min="1"

                            step="0.01"

                            required
                        >

                    </div>


                    <div class="sp-field">

                        <label for="editProductStock">
                            Stock available
                        </label>


                        <input
                            id="editProductStock"

                            type="number"

                            name="stock"

                            min="0"

                            step="1"

                            required
                        >

                    </div>

                </div>

            </div>



            <div class="sp-modal-footer">

                <button
                    type="button"
                    class="sp-modal-secondary"
                    data-close-edit-modal
                >

                    Cancel

                </button>


                <button
                    type="submit"
                    class="sp-modal-primary"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save changes

                </button>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
    DELETE CONFIRMATION MODAL
========================================================== --}}

<div
    id="productDeleteModal"
    class="sp-modal"
    hidden
>

    <div
        class="sp-modal-backdrop"
        data-close-delete-modal
    ></div>


    <div
        class="
            sp-modal-dialog
            sp-delete-modal-dialog
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteModalTitle"
    >

        <div class="sp-delete-icon">

            <i class="fa-regular fa-trash-can"></i>

        </div>


        <h2 id="deleteModalTitle">

            Delete this product?

        </h2>


        <p>

            You are about to permanently delete

            <strong id="deleteProductName"></strong>.

            Its uploaded images will also be removed.

        </p>


        <div class="sp-delete-modal-actions">

            <button
                type="button"
                class="sp-modal-secondary"
                data-close-delete-modal
            >

                Cancel

            </button>


            <form
                method="POST"
                id="deleteProductForm"
            >

                @csrf

                @method('DELETE')


                <button
                    type="submit"
                    class="sp-danger-button"
                >

                    <i class="fa-regular fa-trash-can"></i>

                    Yes, delete product

                </button>

            </form>

        </div>

    </div>

</div>



{{-- =========================================================
    PRODUCT JSON
========================================================== --}}

<script
    type="application/json"
    id="sellerProductData"
>
{!! json_encode(
    $productEditData,
    JSON_HEX_TAG
    |
    JSON_HEX_APOS
    |
    JSON_HEX_AMP
    |
    JSON_HEX_QUOT
) !!}
</script>



{{-- =========================================================
    STYLES
========================================================== --}}

@push('styles')

<link
    href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css"
    rel="stylesheet"
>


<style>

    /*
    |--------------------------------------------------------------------------
    | Variables
    |--------------------------------------------------------------------------
    */

    .seller-products-page {
        --sp-forest: #0B3D2E;
        --sp-green: #12B76A;
        --sp-green-dark: #087443;
        --sp-mint: #E8F7EF;
        --sp-purple: #7A5AF8;
        --sp-ink: #101915;
        --sp-muted: #5A6660;
        --sp-line: #E0E8E3;
        --sp-soft: #F8FBF9;
    }



    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .sp-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;

        margin-bottom: 16px;

        padding: 13px 15px;

        border-radius: 12px;

        font-size: 11px;
        line-height: 1.55;
    }


    .sp-alert-success {
        border: 1px solid #ABEFC6;

        background: #ECFDF3;

        color: #067647;
    }


    .sp-alert-error {
        border: 1px solid #FECDD3;

        background: #FFF1F2;

        color: #B42318;
    }



    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .sp-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;

        margin-bottom: 16px;
    }


    .sp-page-header h1 {
        margin: 0;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        color: var(--sp-ink);

        font-size: 24px;
        font-weight: 800;
    }


    .sp-page-header p {
        margin: 4px 0 0;

        color: var(--sp-muted);

        font-size: 11px;
    }


    .sp-package-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        padding: 8px 12px;

        border-radius: 999px;

        background: var(--sp-mint);

        color: var(--sp-green-dark);

        font-size:12px;
        font-weight: 700;

        white-space: nowrap;
    }



    /*
    |--------------------------------------------------------------------------
    | Slot Card
    |--------------------------------------------------------------------------
    */

    .sp-slot-card {
        margin-bottom: 20px;

        padding: 16px 18px;

        border: 1px solid var(--sp-line);
        border-radius: 16px;

        background: #FFFFFF;

        box-shadow:
            0 8px 22px -14px
            rgba(11,61,46,.18);
    }


    .sp-slot-top {
        display: flex;
        justify-content: space-between;
        gap: 15px;

        margin-bottom: 10px;

        font-size: 11px;
    }


    .sp-slot-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }


    .sp-progress {
        height: 6px;
        flex: 1;

        overflow: hidden;

        border-radius: 999px;

        background: #E9EEEB;
    }


    .sp-progress-fill {
        height: 100%;

        border-radius: inherit;

        background:
            linear-gradient(
                90deg,
                #12B76A,
                #7A5AF8
            );
    }


    .sp-upgrade {
        color: var(--sp-muted);

        font-size:11px;
    }


    .sp-upgrade a {
        color: var(--sp-purple);

        font-weight: 700;

        text-decoration: none;
    }


    .sp-package-expiry {
        margin-top: 10px;

        color: #7A8680;

        font-size: 8px;
    }


    .sp-package-expiry i {
        margin-right: 4px;

        color: var(--sp-green);
    }



    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    .sp-main-grid {
        display: grid;

        grid-template-columns:
            360px
            minmax(0, 1fr);

        align-items: start;

        gap: 20px;
    }



    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .sp-product-form-card {
        padding: 20px;

        border: 1px solid var(--sp-line);
        border-radius: 18px;

        background: #FFFFFF;

        box-shadow:
            0 8px 25px -16px
            rgba(11,61,46,.18);
    }


    .sp-form-heading {
        margin-bottom: 17px;
    }


    .sp-form-heading h2,
    .sp-products-heading h2 {
        margin: 0;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        color: var(--sp-ink);

        font-size: 17px;
        font-weight: 800;
    }


    .sp-form-heading p,
    .sp-products-heading p {
        margin: 3px 0 0;

        color: var(--sp-muted);

        font-size:11px;
    }



    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .sp-field {
        margin-bottom: 15px;
    }


    .sp-field > label {
        display: block;

        margin-bottom: 6px;

        color: #26342D;

        font-size:12px;
        font-weight: 700;
    }


    .sp-field-help {
        margin: -2px 0 9px;

        color: var(--sp-muted);

        font-size:11px;
        line-height: 1.55;
    }


    .sp-field input[type="text"],
    .sp-field input[type="number"] {
        width: 100%;

        height: 42px;

        padding: 0 12px;

        border: 1px solid #DCE5E0;
        border-radius: 11px;

        background: #FFFFFF;

        color: var(--sp-ink);

        font-size:12px;

        outline: none;
    }


    .sp-field input:focus {
        border-color: var(--sp-green);

        box-shadow:
            0 0 0 3px
            rgba(18,183,106,.08);
    }


    .sp-two-fields {
        display: grid;

        grid-template-columns:
            1fr
            1fr;

        gap: 12px;
    }



    /*
    |--------------------------------------------------------------------------
    | Flexible Image Manager
    |--------------------------------------------------------------------------
    */

    .sp-image-manager {
        padding: 10px;

        border: 1px dashed #D6E0DA;
        border-radius: 14px;

        background: #FBFCFB;
    }


    .sp-image-manager.is-disabled {
        opacity: .55;
    }


    .sp-image-add-button {
        width: 100%;
        min-height: 112px;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;

        padding: 15px;

        border: 0;
        border-radius: 10px;

        background: transparent;

        color: var(--sp-muted);

        cursor: pointer;
    }


    .sp-image-add-button i {
        color: var(--sp-green);

        font-size: 21px;
    }


    .sp-image-add-button strong {
        color: #34423B;

        font-size:12px;
    }


    .sp-image-add-button span {
        max-width: 260px;

        font-size:11px;

        line-height: 1.5;

        text-align: center;
    }


    .sp-image-add-button small {
        font-size: 8px;
    }


    .sp-image-selection-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 8px;

        margin-top: 8px;
    }


    .sp-selected-image {
        position: relative;

        height: 110px;

        overflow: hidden;

        border: 1px solid var(--sp-line);
        border-radius: 10px;

        background: #FFFFFF;
    }


    .sp-selected-image img {
        width: 100%;
        height: 100%;

        object-fit: contain;

        background: #F8FAF9;
    }


    .sp-selected-image-remove {
        position: absolute;

        right: 6px;
        top: 6px;

        width: 25px;
        height: 25px;

        display: grid;
        place-items: center;

        border: 0;
        border-radius: 50%;

        background: rgba(16,25,21,.84);

        color: #FFFFFF;

        cursor: pointer;

        font-size:11px;
    }


    .sp-image-counter {
        margin-top: 8px;

        color: #77847D;

        font-size: 8px;

        text-align: right;
    }



    /*
    |--------------------------------------------------------------------------
    | Summernote
    |--------------------------------------------------------------------------
    */

    .seller-products-page
    .note-editor.note-frame,
    .sp-modal-dialog
    .note-editor.note-frame {

        overflow: hidden;

        border: 1px solid #DCE5E0 !important;
        border-radius: 11px !important;

        box-shadow: none !important;
    }


    .seller-products-page
    .note-toolbar,
    .sp-modal-dialog
    .note-toolbar {

        padding: 7px !important;

        border-bottom: 1px solid #E7ECE9 !important;

        background: #F8FAF9 !important;
    }


    .seller-products-page
    .note-btn,
    .sp-modal-dialog
    .note-btn {

        border-color: #DCE5E0 !important;

        background: #FFFFFF !important;

        color: #3C4942 !important;
    }


    .seller-products-page
    .note-editable,
    .sp-modal-dialog
    .note-editable {

        min-height: 170px;

        padding: 13px !important;

        background: #FFFFFF;

        color: #25332C;

        font-family:
            'Inter',
            sans-serif;

        font-size: 11px;

        line-height: 1.7;
    }


    .seller-products-page
    .note-statusbar,
    .sp-modal-dialog
    .note-statusbar {

        border-top: 1px solid #E7ECE9 !important;

        background: #F8FAF9 !important;
    }



    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    */

    .sp-delivery-box {
        margin-bottom: 14px;

        padding: 13px;

        border: 1px solid var(--sp-green);
        border-radius: 11px;

        background: #F2FBF6;
    }


    .sp-delivery-box strong {
        font-size:12px;
    }


    .sp-delivery-box p {
        margin: 5px 0 0;

        color: var(--sp-muted);

        font-size:11px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    .sp-publish-button {
        width: 100%;
        min-height: 43px;

        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;

        border: 0;
        border-radius: 11px;

        background: var(--sp-green);

        color: #FFFFFF;

        font-size: 11px;
        font-weight: 800;

        cursor: pointer;
    }


    .sp-publish-button:disabled {
        background: #B7C3BD;

        cursor: not-allowed;
    }


    .sp-publish-note {
        margin: 9px 0 0;

        color: var(--sp-muted);

        font-size: 8px;

        text-align: center;
    }



    /*
    |--------------------------------------------------------------------------
    | Limit
    |--------------------------------------------------------------------------
    */

    .sp-limit-message {
        display: flex;
        gap: 10px;

        margin-bottom: 14px;
        padding: 12px;

        border: 1px solid #FEDF89;
        border-radius: 11px;

        background: #FFF9ED;
    }


    .sp-limit-icon {
        width: 30px;
        height: 30px;

        flex: 0 0 30px;

        display: grid;
        place-items: center;

        border-radius: 8px;

        background: #FEF0C7;

        color: #B54708;
    }


    .sp-limit-message strong {
        display: block;

        color: #B54708;

        font-size:12px;
    }


    .sp-limit-message p {
        margin: 3px 0;

        color: #7A5B28;

        font-size:11px;
    }


    .sp-limit-message a {
        color: var(--sp-purple);

        font-size:11px;
        font-weight: 700;

        text-decoration: none;
    }



    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    .sp-products-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;

        margin-bottom: 12px;
    }


    .sp-public-profile {
        padding: 7px 11px;

        border: 1px solid var(--sp-line);
        border-radius: 9px;

        background: #FFFFFF;

        color: var(--sp-forest);

        font-size: 8px;
        font-weight: 700;

        text-decoration: none;
    }


    .sp-products-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 14px;
    }


    .sp-product-card {
        overflow: hidden;

        border: 1px solid var(--sp-line);
        border-radius: 16px;

        background: #FFFFFF;

        box-shadow:
            0 6px 20px -13px
            rgba(11,61,46,.18);
    }



    /*
    |--------------------------------------------------------------------------
    | Product Carousel
    |--------------------------------------------------------------------------
    */

    .sp-product-carousel {
        position: relative;

        height: 220px;

        overflow: hidden;

        background: #F7F9F8;
    }


    .sp-carousel-track {
        position: relative;

        width: 100%;
        height: 100%;
    }


    .sp-carousel-slide {
        position: absolute;

        inset: 0;

        opacity: 0;

        pointer-events: none;

        transition:
            opacity .22s ease;
    }


    .sp-carousel-slide.is-active {
        opacity: 1;

        pointer-events: auto;
    }


    .sp-carousel-slide img {
        width: 100%;
        height: 100%;

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Do NOT crop uploaded product images.
        |
        */

        object-fit: contain;

        object-position: center;

        padding: 5px;

        background: #F8FAF9;
    }


    .sp-carousel-control {
        position: absolute;

        top: 50%;

        z-index: 5;

        width: 30px;
        height: 30px;

        display: grid;
        place-items: center;

        border: 1px solid rgba(0,0,0,.08);
        border-radius: 50%;

        background: rgba(255,255,255,.94);

        color: var(--sp-forest);

        box-shadow:
            0 4px 15px
            rgba(0,0,0,.10);

        cursor: pointer;

        transform:
            translateY(-50%);
    }


    .sp-carousel-prev {
        left: 8px;
    }


    .sp-carousel-next {
        right: 8px;
    }


    .sp-carousel-dots {
        position: absolute;

        bottom: 8px;
        left: 50%;

        z-index: 5;

        display: flex;
        gap: 5px;

        padding: 4px 6px;

        border-radius: 999px;

        background: rgba(255,255,255,.83);

        transform:
            translateX(-50%);
    }


    .sp-carousel-dot {
        width: 6px;
        height: 6px;

        padding: 0;

        border: 0;
        border-radius: 50%;

        background: #C6D0CB;

        cursor: pointer;
    }


    .sp-carousel-dot.is-active {
        width: 15px;

        border-radius: 999px;

        background: var(--sp-green);
    }


    .sp-product-placeholder {
        width: 100%;
        height: 100%;

        display: grid;
        place-items: center;

        color: var(--sp-green);

        font-size: 35px;
    }


    .sp-product-status {
        position: absolute;

        right: 9px;
        top: 9px;

        z-index: 7;

        padding: 5px 8px;

        border-radius: 999px;

        font-size: 7px;
        font-weight: 800;
    }


    .sp-product-status.is-live {
        background: #D1FADF;

        color: #067647;
    }


    .sp-product-status.is-hidden {
        background: #EEF1EF;

        color: #5A6660;
    }



    /*
    |--------------------------------------------------------------------------
    | Product Body
    |--------------------------------------------------------------------------
    */

    .sp-product-body {
        padding: 14px;
    }


    .sp-product-body h3 {
        margin: 0;

        color: var(--sp-ink);

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 13px;
        font-weight: 800;
    }


    .sp-product-description-preview {
        min-height: 43px;

        margin: 6px 0 10px;

        color: var(--sp-muted);

        font-size:11px;
        line-height: 1.55;
    }


    .sp-product-price {
        margin-bottom: 8px;

        color: var(--sp-ink);

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 17px;
        font-weight: 800;
    }


    .sp-product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;

        color: var(--sp-muted);

        font-size: 8px;
    }


    .sp-product-meta i {
        margin-right: 3px;

        color: var(--sp-green);
    }


    .sp-product-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;

        margin-top: 12px;
        padding-top: 10px;

        border-top: 1px solid #EDF1EF;

        color: #8A958F;

        font-size: 8px;
    }


    .sp-product-action-buttons {
        display: flex;
        align-items: center;
        gap: 5px;
    }


    .sp-edit-button,
    .sp-delete-button {
        display: inline-flex;
        align-items: center;
        gap: 4px;

        padding: 5px 7px;

        border: 0;
        border-radius: 7px;

        background: transparent;

        font-size: 8px;
        font-weight: 700;

        cursor: pointer;
    }


    .sp-edit-button {
        color: var(--sp-green-dark);
    }


    .sp-edit-button:hover {
        background: var(--sp-mint);
    }


    .sp-delete-button {
        color: #D92D20;
    }


    .sp-delete-button:hover {
        background: #FFF1F2;
    }



    /*
    |--------------------------------------------------------------------------
    | Empty
    |--------------------------------------------------------------------------
    */

    .sp-empty-products {
        min-height: 350px;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        border: 1px dashed #DCE5E0;
        border-radius: 17px;

        background: rgba(255,255,255,.5);

        text-align: center;
    }


    .sp-empty-icon {
        width: 50px;
        height: 50px;

        display: grid;
        place-items: center;

        margin-bottom: 10px;

        border-radius: 14px;

        background: var(--sp-mint);

        color: var(--sp-green);
    }


    .sp-empty-products h3 {
        margin: 0;

        font-size: 14px;
        font-weight: 800;
    }


    .sp-empty-products p {
        max-width: 330px;

        margin: 6px 0 0;

        color: var(--sp-muted);

        font-size:11px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    .sp-modal[hidden] {
        display: none !important;
    }


    .sp-modal {
        position: fixed;

        inset: 0;

        z-index: 5000;

        display: grid;
        place-items: center;

        padding: 20px;
    }


    .sp-modal-backdrop {
        position: absolute;

        inset: 0;

        background: rgba(10,22,17,.55);

        backdrop-filter:
            blur(4px);
    }


    .sp-modal-dialog {
        position: relative;

        z-index: 1;

        width: 100%;

        overflow: hidden;

        border: 1px solid #E0E7E3;
        border-radius: 18px;

        background: #FFFFFF;

        box-shadow:
            0 28px 80px
            rgba(5,25,16,.23);
    }


    .sp-edit-modal-dialog {
        max-width: 690px;

        max-height:
            calc(100vh - 40px);

        overflow-y: auto;
    }


    .sp-delete-modal-dialog {
        max-width: 420px;

        padding: 28px;

        text-align: center;
    }


    .sp-modal-header {
        position: sticky;

        top: 0;

        z-index: 10;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;

        padding: 18px 22px;

        border-bottom: 1px solid #E7ECE9;

        background: rgba(255,255,255,.97);

        backdrop-filter:
            blur(8px);
    }


    .sp-modal-eyebrow {
        display: block;

        margin-bottom: 3px;

        color: var(--sp-green);

        font-size: 8px;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .1em;
    }


    .sp-modal-header h2 {
        margin: 0;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 18px;
        font-weight: 800;
    }


    .sp-modal-close {
        width: 34px;
        height: 34px;

        display: grid;
        place-items: center;

        border: 1px solid #E0E7E3;
        border-radius: 9px;

        background: #FFFFFF;

        color: #5A6660;

        cursor: pointer;
    }


    .sp-edit-modal-body {
        padding: 22px;
    }


    .sp-modal-footer {
        position: sticky;

        bottom: 0;

        display: flex;
        justify-content: flex-end;
        gap: 9px;

        padding: 15px 22px;

        border-top: 1px solid #E7ECE9;

        background: #FFFFFF;
    }


    .sp-modal-primary,
    .sp-modal-secondary,
    .sp-danger-button,
    .sp-edit-add-images {
        min-height: 38px;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;

        padding: 0 14px;

        border-radius: 9px;

        font-size:11px;
        font-weight: 800;

        cursor: pointer;
    }


    .sp-modal-primary {
        border: 0;

        background: var(--sp-green);

        color: #FFFFFF;
    }


    .sp-modal-secondary {
        border: 1px solid #DDE5E1;

        background: #FFFFFF;

        color: #34423B;
    }


    .sp-edit-add-images {
        margin-top: 9px;

        border: 1px dashed #B7C7BE;

        background: #F8FBF9;

        color: var(--sp-green-dark);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Modal
    |--------------------------------------------------------------------------
    */

    .sp-delete-icon {
        width: 54px;
        height: 54px;

        display: grid;
        place-items: center;

        margin: 0 auto 13px;

        border-radius: 50%;

        background: #FFF1F2;

        color: #D92D20;

        font-size: 18px;
    }


    .sp-delete-modal-dialog h2 {
        margin: 0;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 19px;
        font-weight: 800;
    }


    .sp-delete-modal-dialog > p {
        margin: 8px auto 20px;

        color: var(--sp-muted);

        font-size:12px;
        line-height: 1.65;
    }


    .sp-delete-modal-actions {
        display: flex;
        justify-content: center;
        gap: 9px;
    }


    .sp-danger-button {
        border: 0;

        background: #D92D20;

        color: #FFFFFF;
    }



    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 1050px) {

        .sp-main-grid {
            grid-template-columns:
                330px
                minmax(0, 1fr);
        }


        .sp-products-grid {
            grid-template-columns:
                1fr;
        }

    }


    @media(max-width: 850px) {

        .sp-main-grid {
            grid-template-columns:
                1fr;
        }


        .sp-product-form-card {
            max-width: 600px;
        }

    }


    @media(max-width: 640px) {

        .sp-page-header,
        .sp-slot-row {
            flex-direction: column;
            align-items: stretch;
        }


        .sp-two-fields,
        .sp-products-grid {
            grid-template-columns:
                1fr;
        }


        .sp-product-carousel {
            height: 260px;
        }


        .sp-modal {
            padding: 8px;
        }


        .sp-edit-modal-dialog {
            max-height:
                calc(100vh - 16px);
        }


        .sp-delete-modal-actions {
            flex-direction: column;
        }


        .sp-delete-modal-actions form,
        .sp-delete-modal-actions button {
            width: 100%;
        }

    }

</style>

@endpush



{{-- =========================================================
    JAVASCRIPT
========================================================== --}}

@push('scripts')

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Product Data
        |--------------------------------------------------------------------------
        */

        const productDataElement =
            document.getElementById(
                'sellerProductData'
            );


        const products =
            productDataElement

                ? JSON.parse(
                    productDataElement.textContent
                )

                : {};


        /*
        |--------------------------------------------------------------------------
        | Summernote Options
        |--------------------------------------------------------------------------
        */

        const summernoteOptions = {

            placeholder:
                'Describe the condition, specifications, colour, warranty, what is included, delivery notes, and other important information...',

            height:
                190,

            minHeight:
                160,

            dialogsInBody:
                true,

            /*
            |--------------------------------------------------------------------------
            | We Don't Allow Summernote Embedded Images
            |--------------------------------------------------------------------------
            |
            | Product photos are managed by our dedicated 4-image uploader.
            |
            */

            toolbar: [

                [
                    'style',
                    [
                        'style',
                    ]
                ],

                [
                    'font',
                    [
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        'clear',
                    ]
                ],

                [
                    'para',
                    [
                        'ul',
                        'ol',
                        'paragraph',
                    ]
                ],

                [
                    'insert',
                    [
                        'link',
                        'hr',
                        'table',
                    ]
                ],

                [
                    'history',
                    [
                        'undo',
                        'redo',
                    ]
                ],

            ],

        };


        /*
        |--------------------------------------------------------------------------
        | Add Description
        |--------------------------------------------------------------------------
        */

        if (
            window.jQuery
            &&
            $('#addProductDescription').length
            &&
            !$('#addProductDescription').prop(
                'disabled'
            )
        ) {

            $('#addProductDescription')
                .summernote(
                    summernoteOptions
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Flexible File Manager Helper
        |--------------------------------------------------------------------------
        */

        function createFileManager({
            input,
            preview,
            counter,
            maxFiles = 4,
            getExistingCount = () => 0,
        }) {

            let selectedFiles =
                [];


            /*
            |--------------------------------------------------------------------------
            | Sync Browser File Input
            |--------------------------------------------------------------------------
            */

            function syncInput() {

                const transfer =
                    new DataTransfer();


                selectedFiles.forEach(
                    function (file) {

                        transfer.items.add(
                            file
                        );
                    }
                );


                input.files =
                    transfer.files;
            }


            /*
            |--------------------------------------------------------------------------
            | Unique File Key
            |--------------------------------------------------------------------------
            */

            function fileKey(
                file
            ) {

                return [
                    file.name,
                    file.size,
                    file.lastModified,
                ].join(
                    '-'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Render
            |--------------------------------------------------------------------------
            */

            function render() {

                preview.innerHTML =
                    '';


                selectedFiles.forEach(
                    function (
                        file,
                        index
                    ) {

                        const card =
                            document.createElement(
                                'div'
                            );


                        card.className =
                            'sp-selected-image';


                        const image =
                            document.createElement(
                                'img'
                            );


                        const objectUrl =
                            URL.createObjectURL(
                                file
                            );


                        image.src =
                            objectUrl;


                        image.onload =
                            function () {

                                URL.revokeObjectURL(
                                    objectUrl
                                );
                            };


                        const remove =
                            document.createElement(
                                'button'
                            );


                        remove.type =
                            'button';


                        remove.className =
                            'sp-selected-image-remove';


                        remove.innerHTML =
                            '<i class="fa-solid fa-xmark"></i>';


                        remove.addEventListener(
                            'click',
                            function () {

                                selectedFiles.splice(
                                    index,
                                    1
                                );


                                syncInput();

                                render();
                            }
                        );


                        card.appendChild(
                            image
                        );


                        card.appendChild(
                            remove
                        );


                        preview.appendChild(
                            card
                        );
                    }
                );


                if (counter) {

                    const total =
                        getExistingCount()
                        +
                        selectedFiles.length;


                    counter.textContent =
                        total
                        +
                        ' / '
                        +
                        maxFiles
                        +
                        ' images';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Selection
            |--------------------------------------------------------------------------
            */

            input.addEventListener(
                'change',
                function () {

                    const incoming =
                        Array.from(
                            input.files
                            ||
                            []
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Merge Old + New
                    |--------------------------------------------------------------------------
                    */

                    const known =
                        new Set(
                            selectedFiles
                                .map(
                                    fileKey
                                )
                        );


                    incoming.forEach(
                        function (file) {

                            const key =
                                fileKey(
                                    file
                                );


                            if (
                                !known.has(
                                    key
                                )
                            ) {

                                selectedFiles.push(
                                    file
                                );


                                known.add(
                                    key
                                );
                            }
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Maximum
                    |--------------------------------------------------------------------------
                    */

                    const allowed =
                        Math.max(
                            0,
                            maxFiles
                            -
                            getExistingCount()
                        );


                    if (
                        selectedFiles.length
                        >
                        allowed
                    ) {

                        selectedFiles =
                            selectedFiles.slice(
                                0,
                                allowed
                            );


                        alert(
                            'A product can have a maximum of 4 images.'
                        );
                    }


                    syncInput();

                    render();
                }
            );


            return {

                reset() {

                    selectedFiles =
                        [];


                    input.value =
                        '';


                    syncInput();

                    render();
                },


                render,


                getCount() {

                    return selectedFiles.length;
                },

            };
        }


        /*
        |--------------------------------------------------------------------------
        | ADD PRODUCT IMAGE MANAGER
        |--------------------------------------------------------------------------
        */

        const addImageInput =
            document.getElementById(
                'addProductImages'
            );


        const addImageButton =
            document.getElementById(
                'addProductImageButton'
            );


        const addImagePreview =
            document.getElementById(
                'addProductImagePreview'
            );


        const addImageCounter =
            document.getElementById(
                'addImageCounter'
            );


        let addImageManager =
            null;


        if (
            addImageInput
            &&
            addImagePreview
        ) {

            addImageManager =
                createFileManager({

                    input:
                        addImageInput,

                    preview:
                        addImagePreview,

                    counter:
                        addImageCounter,

                });


            addImageManager.render();
        }


        if (
            addImageButton
            &&
            addImageInput
        ) {

            addImageButton.addEventListener(
                'click',
                function () {

                    addImageInput.click();
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT CARD CAROUSELS
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-carousel]'
            )
            .forEach(
                function (
                    carousel
                ) {

                    const slides =
                        Array.from(
                            carousel.querySelectorAll(
                                '.sp-carousel-slide'
                            )
                        );


                    const dots =
                        Array.from(
                            carousel.querySelectorAll(
                                '[data-carousel-dot]'
                            )
                        );


                    let currentIndex =
                        0;


                    function show(
                        index
                    ) {

                        if (
                            slides.length ===
                            0
                        ) {
                            return;
                        }


                        if (
                            index < 0
                        ) {

                            index =
                                slides.length
                                -
                                1;
                        }


                        if (
                            index
                            >=
                            slides.length
                        ) {

                            index =
                                0;
                        }


                        currentIndex =
                            index;


                        slides.forEach(
                            function (
                                slide,
                                slideIndex
                            ) {

                                slide.classList.toggle(
                                    'is-active',
                                    slideIndex
                                    ===
                                    currentIndex
                                );
                            }
                        );


                        dots.forEach(
                            function (
                                dot,
                                dotIndex
                            ) {

                                dot.classList.toggle(
                                    'is-active',
                                    dotIndex
                                    ===
                                    currentIndex
                                );
                            }
                        );
                    }


                    const previous =
                        carousel.querySelector(
                            '[data-carousel-prev]'
                        );


                    const next =
                        carousel.querySelector(
                            '[data-carousel-next]'
                        );


                    if (previous) {

                        previous.addEventListener(
                            'click',
                            function () {

                                show(
                                    currentIndex
                                    -
                                    1
                                );
                            }
                        );
                    }


                    if (next) {

                        next.addEventListener(
                            'click',
                            function () {

                                show(
                                    currentIndex
                                    +
                                    1
                                );
                            }
                        );
                    }


                    dots.forEach(
                        function (
                            dot,
                            dotIndex
                        ) {

                            dot.addEventListener(
                                'click',
                                function () {

                                    show(
                                        dotIndex
                                    );
                                }
                            );
                        }
                    );


                    show(
                        0
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | EDIT MODAL
        |--------------------------------------------------------------------------
        */

        const editModal =
            document.getElementById(
                'productEditModal'
            );


        const editForm =
            document.getElementById(
                'editProductForm'
            );


        const editName =
            document.getElementById(
                'editProductName'
            );


        const editDescription =
            document.getElementById(
                'editProductDescription'
            );


        const editPrice =
            document.getElementById(
                'editProductPrice'
            );


        const editStock =
            document.getElementById(
                'editProductStock'
            );


        const editExistingImages =
            document.getElementById(
                'editExistingImages'
            );


        const editRemovedInputs =
            document.getElementById(
                'editRemovedImageInputs'
            );


        const editNewInput =
            document.getElementById(
                'editProductImages'
            );


        const editNewPreview =
            document.getElementById(
                'editNewImagePreview'
            );


        const editCounter =
            document.getElementById(
                'editImageCounter'
            );


        const editAddImagesButton =
            document.getElementById(
                'editAddImagesButton'
            );


        let currentExistingImages =
            [];


        let removedExistingPaths =
            new Set();


        let editImageManager =
            null;


        let editSummernoteReady =
            false;


        /*
        |--------------------------------------------------------------------------
        | Visible Existing Image Count
        |--------------------------------------------------------------------------
        */

        function activeExistingCount() {

            return currentExistingImages
                .filter(
                    function (image) {

                        return !removedExistingPaths
                            .has(
                                image.path
                            );
                    }
                )
                .length;
        }


        /*
        |--------------------------------------------------------------------------
        | Update Removed Inputs
        |--------------------------------------------------------------------------
        */

        function updateRemovedImageInputs() {

            editRemovedInputs.innerHTML =
                '';


            removedExistingPaths
                .forEach(
                    function (path) {

                        const input =
                            document.createElement(
                                'input'
                            );


                        input.type =
                            'hidden';


                        input.name =
                            'remove_images[]';


                        input.value =
                            path;


                        editRemovedInputs.appendChild(
                            input
                        );
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Image UI
        |--------------------------------------------------------------------------
        */

        function renderExistingImages() {

            editExistingImages.innerHTML =
                '';


            currentExistingImages
                .forEach(
                    function (image) {

                        const removed =
                            removedExistingPaths
                                .has(
                                    image.path
                                );


                        if (removed) {

                            return;
                        }


                        const card =
                            document.createElement(
                                'div'
                            );


                        card.className =
                            'sp-selected-image';


                        const img =
                            document.createElement(
                                'img'
                            );


                        img.src =
                            image.url;


                        img.alt =
                            'Existing product image';


                        const remove =
                            document.createElement(
                                'button'
                            );


                        remove.type =
                            'button';


                        remove.className =
                            'sp-selected-image-remove';


                        remove.innerHTML =
                            '<i class="fa-solid fa-xmark"></i>';


                        remove.addEventListener(
                            'click',
                            function () {

                                removedExistingPaths
                                    .add(
                                        image.path
                                    );


                                updateRemovedImageInputs();

                                renderExistingImages();


                                if (
                                    editImageManager
                                ) {

                                    editImageManager.render();
                                }
                            }
                        );


                        card.appendChild(
                            img
                        );


                        card.appendChild(
                            remove
                        );


                        editExistingImages
                            .appendChild(
                                card
                            );
                    }
                );


            if (
                editImageManager
            ) {

                editImageManager.render();

            } else {

                editCounter.textContent =
                    activeExistingCount()
                    +
                    ' / 4 images';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Initialize Edit File Manager
        |--------------------------------------------------------------------------
        */

        if (
            editNewInput
            &&
            editNewPreview
        ) {

            editImageManager =
                createFileManager({

                    input:
                        editNewInput,

                    preview:
                        editNewPreview,

                    counter:
                        editCounter,

                    getExistingCount:
                        activeExistingCount,

                });
        }


        if (
            editAddImagesButton
            &&
            editNewInput
        ) {

            editAddImagesButton
                .addEventListener(
                    'click',
                    function () {

                        if (
                            activeExistingCount()
                            +
                            (
                                editImageManager
                                    ? editImageManager.getCount()
                                    : 0
                            )
                            >=
                            4
                        ) {

                            alert(
                                'This product already has 4 images. Remove one before adding another.'
                            );


                            return;
                        }


                        editNewInput.click();
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Open Edit Modal
        |--------------------------------------------------------------------------
        */

        function openEditModal(
            product
        ) {

            currentExistingImages =
                Array.isArray(
                    product.images
                )
                    ? product.images
                    : [];


            removedExistingPaths =
                new Set();


            editForm.action =
                product.update_url;


            editName.value =
                product.name
                ||
                '';


            editPrice.value =
                product.price
                ||
                '';


            editStock.value =
                product.stock
                ??
                0;


            editRemovedInputs.innerHTML =
                '';


            if (
                editImageManager
            ) {

                editImageManager.reset();
            }


            renderExistingImages();


            editModal.hidden =
                false;


            document.body.style.overflow =
                'hidden';


            /*
            |--------------------------------------------------------------------------
            | Summernote Edit
            |--------------------------------------------------------------------------
            */

            setTimeout(
                function () {

                    if (
                        !editSummernoteReady
                    ) {

                        $('#editProductDescription')
                            .summernote(
                                summernoteOptions
                            );


                        editSummernoteReady =
                            true;
                    }


                    $('#editProductDescription')
                        .summernote(
                            'code',
                            product.description
                            ||
                            ''
                        );

                },
                30
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Edit Buttons
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-edit-product]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.dataset
                                    .editProduct;


                            const product =
                                products[id];


                            if (!product) {

                                return;
                            }


                            openEditModal(
                                product
                            );
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Close Edit Modal
        |--------------------------------------------------------------------------
        */

        function closeEditModal() {

            editModal.hidden =
                true;


            document.body.style.overflow =
                '';
        }


        document
            .querySelectorAll(
                '[data-close-edit-modal]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        closeEditModal
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | DELETE MODAL
        |--------------------------------------------------------------------------
        */

        const deleteModal =
            document.getElementById(
                'productDeleteModal'
            );


        const deleteForm =
            document.getElementById(
                'deleteProductForm'
            );


        const deleteName =
            document.getElementById(
                'deleteProductName'
            );


        function openDeleteModal(
            product
        ) {

            deleteName.textContent =
                product.name;


            deleteForm.action =
                product.delete_url;


            deleteModal.hidden =
                false;


            document.body.style.overflow =
                'hidden';
        }


        function closeDeleteModal() {

            deleteModal.hidden =
                true;


            document.body.style.overflow =
                '';
        }


        document
            .querySelectorAll(
                '[data-delete-product]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.dataset
                                    .deleteProduct;


                            const product =
                                products[id];


                            if (!product) {

                                return;
                            }


                            openDeleteModal(
                                product
                            );
                        }
                    );
                }
            );


        document
            .querySelectorAll(
                '[data-close-delete-modal]'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        closeDeleteModal
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Escape Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key
                    !==
                    'Escape'
                ) {

                    return;
                }


                if (
                    editModal
                    &&
                    !editModal.hidden
                ) {

                    closeEditModal();
                }


                if (
                    deleteModal
                    &&
                    !deleteModal.hidden
                ) {

                    closeDeleteModal();
                }
            }
        );

    }
);

</script>

@endpush


@endsection