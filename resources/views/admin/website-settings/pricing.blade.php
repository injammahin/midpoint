@extends('admin.layouts.app')

@section('title', 'Pricing Management')
@section('page-title', 'Pricing Management')

@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="admin-pricing-head">

        <div>
            <h2>Pricing Management</h2>

            <p>
                Manage seller fees, buyer fees, VAT and public pricing content.
            </p>
        </div>

        <a
            href="{{ route('pricing') }}"
            target="_blank"
            class="admin-pricing-view"
        >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            View pricing page
        </a>

    </div>


    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="admin-success-alert">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- =========================================================
        VALIDATION ERRORS
    ========================================================== --}}
    @if($errors->any())

        <div class="admin-pricing-error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <strong>
                    Please correct the following fields:
                </strong>

                <ul>
                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach
                </ul>

            </div>

        </div>

    @endif


    {{-- =========================================================
        FORM
    ========================================================== --}}
    <form
        method="POST"
        action="{{ route('admin.website-settings.pricing.update') }}"
        id="admin-pricing-form"
    >

        @csrf
        @method('PUT')


        <div class="admin-pricing-layout">

            {{-- =====================================================
                LEFT COLUMN
            ====================================================== --}}
            <div class="admin-pricing-settings">


                {{-- =================================================
                    PAGE CONTENT
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-file-lines"></i>
                        </span>

                        <div>
                            <h3>Page Content</h3>

                            <p>
                                Main public pricing page heading.
                            </p>
                        </div>

                    </div>


                    <div class="admin-pricing-field">

                        <label for="page_eyebrow">
                            Eyebrow
                        </label>

                        <input
                            id="page_eyebrow"
                            type="text"
                            name="page_eyebrow"
                            value="{{ old('page_eyebrow', $pricing->page_eyebrow) }}"
                            required
                        >

                    </div>


                    <div class="admin-pricing-field">

                        <label for="page_title">
                            Page title
                        </label>

                        <input
                            id="page_title"
                            type="text"
                            name="page_title"
                            value="{{ old('page_title', $pricing->page_title) }}"
                            required
                        >

                    </div>


                    <div class="admin-pricing-field">

                        <label for="page_subtitle">
                            Subtitle
                        </label>

                        <textarea
                            id="page_subtitle"
                            name="page_subtitle"
                            rows="3"
                            required
                        >{{ old('page_subtitle', $pricing->page_subtitle) }}</textarea>

                    </div>

                </section>



                {{-- =================================================
                    PRICING EXAMPLE
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-calculator"></i>
                        </span>

                        <div>
                            <h3>Pricing Example</h3>

                            <p>
                                Used to demonstrate fee calculations on the public page.
                            </p>
                        </div>

                    </div>


                    <div class="admin-pricing-grid-2">

                        <div class="admin-pricing-field">

                            <label for="pricing-currency">
                                Currency symbol
                            </label>

                            <input
                                id="pricing-currency"
                                type="text"
                                name="currency_symbol"
                                value="{{ old('currency_symbol', $pricing->currency_symbol) }}"
                                maxlength="10"
                                required
                            >

                        </div>


                        <div class="admin-pricing-field">

                            <label for="pricing-product-price">
                                Example product price
                            </label>

                            <input
                                id="pricing-product-price"
                                type="number"
                                name="example_product_price"
                                value="{{ old('example_product_price', $pricing->example_product_price) }}"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>

                </section>



                {{-- =================================================
                    SELLER PRICING
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-store"></i>
                        </span>

                        <div>
                            <h3>Seller Pricing</h3>

                            <p>
                                Configure the seller service fee and VAT.
                            </p>
                        </div>

                    </div>


                    <div class="admin-pricing-field">

                        <label for="seller_badge">
                            Seller badge
                        </label>

                        <input
                            id="seller_badge"
                            type="text"
                            name="seller_badge"
                            value="{{ old('seller_badge', $pricing->seller_badge) }}"
                            required
                        >

                    </div>


                    <div class="admin-pricing-grid-2">

                        <div class="admin-pricing-field">

                            <label for="seller-fee-percent">
                                Service fee (%)
                            </label>

                            <input
                                id="seller-fee-percent"
                                type="number"
                                name="seller_service_fee_percent"
                                value="{{ old('seller_service_fee_percent', $pricing->seller_service_fee_percent) }}"
                                min="0"
                                max="100"
                                step="0.001"
                                required
                            >

                        </div>


                        <div class="admin-pricing-field">

                            <label for="seller-vat-percent">
                                VAT on service fee (%)
                            </label>

                            <input
                                id="seller-vat-percent"
                                type="number"
                                name="seller_vat_percent"
                                value="{{ old('seller_vat_percent', $pricing->seller_vat_percent) }}"
                                min="0"
                                max="100"
                                step="0.001"
                                required
                            >

                        </div>

                    </div>


                    <div class="admin-pricing-field">

                        <label for="seller_description">
                            Seller description
                        </label>

                        <textarea
                            id="seller_description"
                            name="seller_description"
                            rows="5"
                            required
                        >{{ old('seller_description', $pricing->seller_description) }}</textarea>

                    </div>

                </section>



                {{-- =================================================
                    BUYER PRICING
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-cart-shopping"></i>
                        </span>

                        <div>
                            <h3>Buyer Pricing</h3>

                            <p>
                                Set the buyer fee to 0% when buyers pay no Midpoint fee.
                            </p>
                        </div>

                    </div>


                    <div class="admin-pricing-field">

                        <label for="buyer_badge">
                            Buyer badge
                        </label>

                        <input
                            id="buyer_badge"
                            type="text"
                            name="buyer_badge"
                            value="{{ old('buyer_badge', $pricing->buyer_badge) }}"
                            required
                        >

                    </div>


                    <div class="admin-pricing-field">

                        <label for="buyer-fee-percent">
                            Buyer service fee (%)
                        </label>

                        <input
                            id="buyer-fee-percent"
                            type="number"
                            name="buyer_service_fee_percent"
                            value="{{ old('buyer_service_fee_percent', $pricing->buyer_service_fee_percent) }}"
                            min="0"
                            max="100"
                            step="0.001"
                            required
                        >

                    </div>


                    <div class="admin-pricing-field">

                        <label for="buyer_description">
                            Buyer description
                        </label>

                        <textarea
                            id="buyer_description"
                            name="buyer_description"
                            rows="4"
                            required
                        >{{ old('buyer_description', $pricing->buyer_description) }}</textarea>

                    </div>

                </section>



                {{-- =================================================
                    LABELS & DELIVERY
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-font"></i>
                        </span>

                        <div>
                            <h3>Labels & Delivery</h3>

                            <p>
                                Manage text displayed inside the pricing cards.
                            </p>
                        </div>

                    </div>


                    @php
                        $labelFields = [
                            'product_price_label' => 'Product price label',
                            'seller_fee_label' => 'Seller fee label',
                            'vat_label' => 'VAT label',
                            'total_charges_label' => 'Total charges label',
                            'seller_receive_label' => 'Seller receive label',
                            'buyer_fee_label' => 'Buyer fee label',
                            'buyer_total_label' => 'Buyer total label',
                            'delivery_label' => 'Delivery label',
                            'delivery_value' => 'Delivery value',
                        ];
                    @endphp


                    <div class="admin-pricing-grid-2">

                        @foreach($labelFields as $field => $label)

                            <div class="admin-pricing-field">

                                <label for="{{ $field }}">
                                    {{ $label }}
                                </label>

                                <input
                                    id="{{ $field }}"
                                    type="text"
                                    name="{{ $field }}"
                                    value="{{ old($field, $pricing->{$field}) }}"
                                    required
                                >

                            </div>

                        @endforeach

                    </div>

                </section>



                {{-- =================================================
                    PROTECTION / REFUND INFORMATION
                ================================================== --}}
                <section class="admin-card admin-pricing-section">

                    <div class="admin-pricing-section-head">

                        <span>
                            <i class="fa-solid fa-circle-info"></i>
                        </span>

                        <div>
                            <h3>Protection & Refund Information</h3>

                            <p>
                                Manage additional information displayed below pricing.
                            </p>
                        </div>

                    </div>


                    <div class="admin-pricing-field">

                        <label for="protection_note">
                            Protection note
                        </label>

                        <textarea
                            id="protection_note"
                            name="protection_note"
                            rows="3"
                            required
                        >{{ old('protection_note', $pricing->protection_note) }}</textarea>

                    </div>


                    <label class="admin-pricing-toggle">

                        <input
                            type="checkbox"
                            name="refund_notice_enabled"
                            value="1"
                            {{ old('refund_notice_enabled', $pricing->refund_notice_enabled) ? 'checked' : '' }}
                        >

                        <span>

                            <strong>
                                Show refund notice
                            </strong>

                            <small>
                                Display the refund information box on the public pricing page.
                            </small>

                        </span>

                    </label>


                    <div class="admin-pricing-field">

                        <label for="refund_notice_title">
                            Refund notice title
                        </label>

                        <input
                            id="refund_notice_title"
                            type="text"
                            name="refund_notice_title"
                            value="{{ old('refund_notice_title', $pricing->refund_notice_title) }}"
                        >

                    </div>


                    <div class="admin-pricing-field">

                        <label for="refund_notice_text">
                            Refund notice text
                        </label>

                        <textarea
                            id="refund_notice_text"
                            name="refund_notice_text"
                            rows="5"
                        >{{ old('refund_notice_text', $pricing->refund_notice_text) }}</textarea>

                    </div>

                </section>

            </div>



            {{-- =====================================================
                RIGHT LIVE PREVIEW
            ====================================================== --}}
            <aside class="admin-pricing-preview-column">

                <div class="admin-card admin-pricing-preview">

                    <div class="admin-pricing-preview-head">

                        <div>
                            <h3>Live Calculation</h3>

                            <p>
                                Preview based on current fee values.
                            </p>
                        </div>

                        <span>
                            Preview
                        </span>

                    </div>


                    {{-- SELLER PREVIEW --}}
                    <div class="admin-pricing-preview-card seller">

                        <small>
                            Seller
                        </small>


                        <div class="admin-pricing-preview-main">

                            <strong id="preview-seller-fee">
                                0%
                            </strong>

                            <span id="preview-vat-label">
                                + VAT
                            </span>

                        </div>


                        <div class="admin-pricing-preview-line">

                            <span>
                                Product
                            </span>

                            <strong id="preview-product-price">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-line">

                            <span>
                                Service fee
                            </span>

                            <strong id="preview-seller-fee-value">
                                − {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-line">

                            <span>
                                VAT
                            </span>

                            <strong id="preview-vat">
                                − {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-line">

                            <span>
                                Total charges
                            </span>

                            <strong id="preview-total-charges">
                                − {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-total">

                            <span>
                                Seller receives
                            </span>

                            <strong id="preview-seller-receives">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>

                    </div>


                    {{-- BUYER PREVIEW --}}
                    <div class="admin-pricing-preview-card buyer">

                        <small>
                            Buyer
                        </small>


                        <div class="admin-pricing-preview-main">

                            <strong id="preview-buyer-fee">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-line">

                            <span>
                                Product
                            </span>

                            <strong id="preview-buyer-product">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div
                            class="admin-pricing-preview-line"
                            id="preview-buyer-fee-row"
                        >

                            <span>
                                Buyer service fee
                            </span>

                            <strong id="preview-buyer-fee-value">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>


                        <div class="admin-pricing-preview-total purple">

                            <span>
                                Buyer pays
                            </span>

                            <strong id="preview-buyer-total">
                                {{ $pricing->currency_symbol }}0
                            </strong>

                        </div>

                    </div>

                </div>

            </aside>

        </div>



        {{-- =========================================================
            SAVE BAR
        ========================================================== --}}
        <div class="admin-pricing-save-bar">

            <div>

                <i class="fa-solid fa-circle-info"></i>

                Changes affect the public pricing page after saving.

            </div>


            <button
                type="submit"
                id="save-pricing-button"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Save Pricing Settings

            </button>

        </div>

    </form>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const priceInput =
        document.getElementById('pricing-product-price');

    const currencyInput =
        document.getElementById('pricing-currency');

    const sellerFeeInput =
        document.getElementById('seller-fee-percent');

    const sellerVatInput =
        document.getElementById('seller-vat-percent');

    const buyerFeeInput =
        document.getElementById('buyer-fee-percent');


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const getNumber = (element) => {

        if (!element) {
            return 0;
        }

        const value =
            parseFloat(element.value);

        return Number.isFinite(value)
            ? value
            : 0;
    };


    const formatMoney = (amount, symbol) => {

        const formatted =
            new Intl.NumberFormat(
                'en-NG',
                {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }
            ).format(amount);

        return `${symbol}${formatted}`;
    };


    const formatPercent = (value) => {

        return parseFloat(
            Number(value).toFixed(3)
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Live Preview
    |--------------------------------------------------------------------------
    */

    const updatePreview = () => {

        const symbol =
            currencyInput?.value?.trim()
            || '₦';


        const productPrice =
            getNumber(priceInput);


        const sellerFeePercent =
            getNumber(sellerFeeInput);


        const sellerVatPercent =
            getNumber(sellerVatInput);


        const buyerFeePercent =
            getNumber(buyerFeeInput);


        /*
        |--------------------------------------------------------------------------
        | Seller Calculations
        |--------------------------------------------------------------------------
        */

        const sellerFee =
            productPrice
            * sellerFeePercent
            / 100;


        const vat =
            sellerFee
            * sellerVatPercent
            / 100;


        const totalCharges =
            sellerFee + vat;


        const sellerReceives =
            Math.max(
                0,
                productPrice - totalCharges
            );


        /*
        |--------------------------------------------------------------------------
        | Buyer Calculations
        |--------------------------------------------------------------------------
        */

        const buyerFee =
            productPrice
            * buyerFeePercent
            / 100;


        const buyerTotal =
            productPrice + buyerFee;


        /*
        |--------------------------------------------------------------------------
        | Seller Preview
        |--------------------------------------------------------------------------
        */

        const sellerFeePercentElement =
            document.getElementById(
                'preview-seller-fee'
            );


        if (sellerFeePercentElement) {

            sellerFeePercentElement.textContent =
                `${formatPercent(sellerFeePercent)}%`;
        }


        const previewVatLabel =
            document.getElementById(
                'preview-vat-label'
            );


        if (previewVatLabel) {

            previewVatLabel.style.display =
                sellerVatPercent > 0
                    ? ''
                    : 'none';
        }


        const previewProductPrice =
            document.getElementById(
                'preview-product-price'
            );


        if (previewProductPrice) {

            previewProductPrice.textContent =
                formatMoney(
                    productPrice,
                    symbol
                );
        }


        const previewSellerFeeValue =
            document.getElementById(
                'preview-seller-fee-value'
            );


        if (previewSellerFeeValue) {

            previewSellerFeeValue.textContent =
                `− ${formatMoney(
                    sellerFee,
                    symbol
                )}`;
        }


        const previewVat =
            document.getElementById(
                'preview-vat'
            );


        if (previewVat) {

            previewVat.textContent =
                `− ${formatMoney(
                    vat,
                    symbol
                )}`;
        }


        const previewTotalCharges =
            document.getElementById(
                'preview-total-charges'
            );


        if (previewTotalCharges) {

            previewTotalCharges.textContent =
                `− ${formatMoney(
                    totalCharges,
                    symbol
                )}`;
        }


        const previewSellerReceives =
            document.getElementById(
                'preview-seller-receives'
            );


        if (previewSellerReceives) {

            previewSellerReceives.textContent =
                formatMoney(
                    sellerReceives,
                    symbol
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Buyer Preview
        |--------------------------------------------------------------------------
        */

        const previewBuyerFee =
            document.getElementById(
                'preview-buyer-fee'
            );


        if (previewBuyerFee) {

            previewBuyerFee.textContent =
                buyerFeePercent === 0
                    ? `${symbol}0`
                    : `${formatPercent(
                        buyerFeePercent
                    )}%`;
        }


        const previewBuyerProduct =
            document.getElementById(
                'preview-buyer-product'
            );


        if (previewBuyerProduct) {

            previewBuyerProduct.textContent =
                formatMoney(
                    productPrice,
                    symbol
                );
        }


        const previewBuyerFeeValue =
            document.getElementById(
                'preview-buyer-fee-value'
            );


        if (previewBuyerFeeValue) {

            previewBuyerFeeValue.textContent =
                `+ ${formatMoney(
                    buyerFee,
                    symbol
                )}`;
        }


        const buyerFeeRow =
            document.getElementById(
                'preview-buyer-fee-row'
            );


        if (buyerFeeRow) {

            buyerFeeRow.style.display =
                buyerFeePercent > 0
                    ? 'flex'
                    : 'none';
        }


        const previewBuyerTotal =
            document.getElementById(
                'preview-buyer-total'
            );


        if (previewBuyerTotal) {

            previewBuyerTotal.textContent =
                formatMoney(
                    buyerTotal,
                    symbol
                );
        }

    };


    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    [
        priceInput,
        currencyInput,
        sellerFeeInput,
        sellerVatInput,
        buyerFeeInput,

    ].forEach((input) => {

        input?.addEventListener(
            'input',
            updatePreview
        );

        input?.addEventListener(
            'change',
            updatePreview
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Initial Preview
    |--------------------------------------------------------------------------
    */

    updatePreview();

});
</script>

@endpush