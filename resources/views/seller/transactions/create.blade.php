@extends('seller.layouts.app')


@section('title', 'Create Transaction')


@section('content')

@php

    $selectedType =
        old(
            'transaction_type',
            $products->isNotEmpty()
                ? 'listed'
                : 'custom'
        );


    $productPayload =
        $products
            ->map(
                function ($product) {

                    return [

                        'id' =>
                            $product->id,

                        'name' =>
                            $product->name,

                        'description' =>
                            trim(
                                strip_tags(
                                    $product->description
                                )
                            ),

                        'price' =>
                            (float)
                            $product->price,

                        'stock' =>
                            (int)
                            $product->stock,

                        'image' =>
                            $product->main_image
                                ? asset(
                                    'storage/'
                                    .
                                    $product->main_image
                                )
                                : null,

                    ];
                }
            )
            ->values();

@endphp


<div class="ct-page">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="ct-header">

        <div>

            <div class="ct-eyebrow">
                Secure transaction
            </div>


            <h1>
                Create transaction
            </h1>


            <p>
                Create a protected transaction and send the generated
                Midpoint link directly to your buyer.
            </p>

        </div>


        @if ($subscription)

            <div class="ct-package">

                <i class="fa-solid fa-circle-check"></i>

                {{ $subscription->package_name }} package

            </div>

        @else

            <div class="ct-package ct-package-free">

                <i class="fa-solid fa-shield-halved"></i>

                No package required

            </div>

        @endif

    </div>



    {{-- =========================================================
        ERRORS
    ========================================================== --}}

    @if ($errors->any())

        <div class="ct-alert">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <strong>
                    Please check the transaction information.
                </strong>

                <span>
                    {{ $errors->first() }}
                </span>

            </div>

        </div>

    @endif



    <form
        method="POST"
        action="{{ route('seller.transactions.store') }}"
        enctype="multipart/form-data"
        id="createTransactionForm"
    >

        @csrf


        <div class="ct-layout">


            {{-- =====================================================
                MAIN
            ====================================================== --}}

            <div class="ct-main">


                {{-- =================================================
                    TRANSACTION TYPE
                ================================================== --}}

                <section class="ct-card">

                    <div class="ct-card-heading">

                        <span>
                            1
                        </span>


                        <div>

                            <h2>
                                What are you selling?
                            </h2>

                            <p>
                                Use an existing listing or create a custom transaction.
                            </p>

                        </div>

                    </div>


                    <div class="ct-type-grid">

                        @if ($products->isNotEmpty())

                            <label
                                class="ct-type-option"
                                data-type-option="listed"
                            >

                                <input
                                    type="radio"
                                    name="transaction_type"
                                    value="listed"
                                    @checked($selectedType === 'listed')
                                >


                                <span class="ct-type-icon">

                                    <i class="fa-solid fa-box-open"></i>

                                </span>


                                <span>

                                    <strong>
                                        Listed product
                                    </strong>

                                    <small>
                                        Start from one of your published products.
                                    </small>

                                </span>

                            </label>

                        @endif


                        <label
                            class="ct-type-option"
                            data-type-option="custom"
                        >

                            <input
                                type="radio"
                                name="transaction_type"
                                value="custom"
                                @checked($selectedType === 'custom')
                            >


                            <span class="ct-type-icon purple">

                                <i class="fa-solid fa-pen-to-square"></i>

                            </span>


                            <span>

                                <strong>
                                    Custom transaction
                                </strong>

                                <small>
                                    Enter the agreed item manually.
                                </small>

                            </span>

                        </label>

                    </div>



                    @if ($products->isNotEmpty())

                        <div
                            class="ct-product-selector"
                            id="listedProductSection"
                        >

                            <label>
                                Choose product
                            </label>


                            <select
                                name="seller_product_id"
                                id="sellerProductSelect"
                            >

                                <option value="">
                                    Select listed product
                                </option>


                                @foreach ($products as $product)

                                    <option
                                        value="{{ $product->id }}"
                                        @selected(
                                            old('seller_product_id')
                                            ==
                                            $product->id
                                        )
                                    >
                                        {{ $product->name }}
                                        ·
                                        ₦{{ number_format((float) $product->price, 0) }}
                                        ·
                                        {{ $product->stock }} in stock
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    @endif



                    {{-- =================================================
                        SNAPSHOT
                    ================================================== --}}

                    <div class="ct-field">

                        <label for="transactionTitle">
                            Item / product title
                        </label>


                        <input
                            id="transactionTitle"
                            type="text"
                            name="title"
                            maxlength="255"
                            value="{{ old('title') }}"
                            placeholder="e.g. iPhone 15 Pro 256GB"
                            required
                        >

                    </div>


                    <div class="ct-field">

                        <label for="transactionDescription">

                            Item description / agreed condition

                        </label>


                        <textarea
                            id="transactionDescription"
                            name="description"
                            rows="6"
                            maxlength="5000"
                            placeholder="Describe condition, specification, warranty, accessories and anything agreed with the buyer."
                            required
                        >{{ old('description') }}</textarea>

                    </div>



                    {{-- =================================================
                        IMAGES
                    ================================================== --}}

                    <div class="ct-field">

                        <label>

                            Transaction images

                            <small>
                                Optional · maximum 4
                            </small>

                        </label>


                        <label class="ct-upload">

                            <i class="fa-regular fa-images"></i>


                            <strong>
                                Add transaction images
                            </strong>


                            <span>
                                JPG, PNG or WEBP · up to 5 MB each
                            </span>


                            <input
                                type="file"
                                name="images[]"
                                id="transactionImages"
                                accept=".jpg,.jpeg,.png,.webp"
                                multiple
                                hidden
                            >

                        </label>


                        <div
                            id="transactionImagePreview"
                            class="ct-image-preview"
                        ></div>


                        <p class="ct-help">
                            If you choose a listed product and do not upload new
                            images, Midpoint will automatically copy its existing
                            product images into this transaction.
                        </p>

                    </div>

                </section>



                {{-- =================================================
                    PRICE
                ================================================== --}}

                <section class="ct-card">

                    <div class="ct-card-heading">

                        <span>
                            2
                        </span>


                        <div>

                            <h2>
                                Price & delivery
                            </h2>

                            <p>
                                Set exactly what this buyer has agreed to pay.
                            </p>

                        </div>

                    </div>


                    <div class="ct-three-fields">

                        <div class="ct-field">

                            <label for="transactionQuantity">
                                Quantity
                            </label>


                            <input
                                id="transactionQuantity"
                                type="number"
                                name="quantity"
                                min="1"
                                max="100"
                                value="{{ old('quantity', 1) }}"
                                required
                            >

                        </div>


                        <div class="ct-field">

                            <label for="transactionUnitPrice">
                                Unit price (₦)
                            </label>


                            <input
                                id="transactionUnitPrice"
                                type="number"
                                name="unit_price"
                                min="1"
                                step="0.01"
                                value="{{ old('unit_price') }}"
                                placeholder="145000"
                                required
                            >

                        </div>


                        <div class="ct-field">

                            <label for="transactionDeliveryFee">
                                Delivery fee (₦)
                            </label>


                            <input
                                id="transactionDeliveryFee"
                                type="number"
                                name="delivery_fee"
                                min="0"
                                step="0.01"
                                value="{{ old('delivery_fee', 0) }}"
                                placeholder="0"
                            >

                        </div>

                    </div>


                    <div class="ct-field">

                        <label for="transactionDeliveryNote">

                            Delivery arrangement

                            <small>
                                Optional
                            </small>

                        </label>


                        <textarea
                            id="transactionDeliveryNote"
                            name="delivery_note"
                            rows="4"
                            maxlength="3000"
                            placeholder="e.g. Seller will arrange delivery within Lagos. Delivery expected within 1–2 business days."
                        >{{ old('delivery_note') }}</textarea>

                    </div>

                </section>



                {{-- =================================================
                    BUYER
                ================================================== --}}

                <section class="ct-card">

                    <div class="ct-card-heading">

                        <span>
                            3
                        </span>


                        <div>

                            <h2>
                                Buyer
                            </h2>

                            <p>
                                The secure link will be restricted to this email.
                            </p>

                        </div>

                    </div>


                    <div class="ct-two-fields">

                        <div class="ct-field">

                            <label for="transactionBuyerEmail">

                                Buyer email

                                <small>
                                    Required
                                </small>

                            </label>


                            <input
                                id="transactionBuyerEmail"
                                type="email"
                                name="buyer_email"
                                value="{{ old('buyer_email') }}"
                                placeholder="buyer@example.com"
                                required
                            >

                        </div>


                        <div class="ct-field">

                            <label for="transactionBuyerPhone">

                                Buyer phone

                                <small>
                                    Optional
                                </small>

                            </label>


                            <input
                                id="transactionBuyerPhone"
                                type="text"
                                name="buyer_phone"
                                maxlength="40"
                                value="{{ old('buyer_phone') }}"
                                placeholder="+234..."
                            >

                        </div>

                    </div>


                    <div class="ct-security-note">

                        <i class="fa-solid fa-lock"></i>


                        <div>

                            <strong>
                                Buyer identity protection
                            </strong>


                            <p>
                                The person opening the link must log in to
                                Midpoint using this email address. A different
                                account cannot claim the transaction.
                            </p>

                        </div>

                    </div>

                </section>

            </div>



            {{-- =====================================================
                SUMMARY
            ====================================================== --}}

            <aside class="ct-summary">

                <div class="ct-summary-card">

                    <div class="ct-summary-icon">

                        <i class="fa-solid fa-shield-halved"></i>

                    </div>


                    <h2>
                        Transaction summary
                    </h2>


                    <p>
                        Review the buyer's payment amount before creating the link.
                    </p>



                    <div class="ct-summary-lines">

                        <div>

                            <span>
                                Unit price
                            </span>

                            <strong id="summaryUnitPrice">
                                ₦0
                            </strong>

                        </div>


                        <div>

                            <span>
                                Quantity
                            </span>

                            <strong id="summaryQuantity">
                                1
                            </strong>

                        </div>


                        <div>

                            <span>
                                Subtotal
                            </span>

                            <strong id="summarySubtotal">
                                ₦0
                            </strong>

                        </div>


                        <div>

                            <span>
                                Delivery
                            </span>

                            <strong id="summaryDelivery">
                                ₦0
                            </strong>

                        </div>

                    </div>



                    <div class="ct-summary-total">

                        <span>
                            Buyer pays
                        </span>


                        <strong id="summaryTotal">
                            ₦0
                        </strong>

                    </div>



                    <div class="ct-protection">

                        <div>

                            <i class="fa-solid fa-link"></i>

                            <span>
                                Link valid for
                                {{ config('secure_transactions.link_expiry_days', 7) }}
                                days
                            </span>

                        </div>


                        <div>

                            <i class="fa-regular fa-clock"></i>

                            <span>
                                {{ config('secure_transactions.inspection_hours', 8) }}
                                hour inspection after delivery
                            </span>

                        </div>


                        <div>

                            <i class="fa-solid fa-user-shield"></i>

                            <span>
                                Buyer must use the assigned email
                            </span>

                        </div>

                    </div>



                    <button
                        type="submit"
                        class="ct-generate"
                    >

                        <i class="fa-solid fa-link"></i>

                        Generate secure link

                    </button>


                    <small class="ct-summary-footnote">
                        No Paystack payment is created yet. The payment
                        session begins only after the buyer opens this link.
                    </small>

                </div>

            </aside>

        </div>

    </form>

</div>



<script
    type="application/json"
    id="sellerProductPayload"
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

.ct-page {
    width: 100%;
}

.ct-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.ct-eyebrow {
    margin-bottom: 4px;
    color: #12B76A;
    font-size:12px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.ct-header h1 {
    margin: 0;
    color: #101915;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 27px;
    font-weight: 800;
}

.ct-header p {
    margin: 5px 0 0;
    color: #6D7973;
    font-size: 11px;
}

.ct-package {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 11px;
    border-radius: 999px;
    background: #ECFDF3;
    color: #067647;
    font-size:11px;
    font-weight: 800;
}

.ct-alert {
    display: flex;
    gap: 9px;
    margin-bottom: 16px;
    padding: 12px 14px;
    border: 1px solid #FECDD3;
    border-radius: 11px;
    background: #FFF1F2;
    color: #B42318;
    font-size:11px;
}

.ct-alert strong,
.ct-alert span {
    display: block;
}

.ct-alert span {
    margin-top: 2px;
}

.ct-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 310px;
    align-items: start;
    gap: 18px;
}

.ct-main {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.ct-card,
.ct-summary-card {
    border: 1px solid #DDE5E1;
    border-radius: 17px;
    background: #FFFFFF;
    box-shadow: 0 12px 35px -30px rgba(11,61,46,.30);
}

.ct-card {
    padding: 22px;
}

.ct-card-heading {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 19px;
}

.ct-card-heading > span {
    width: 31px;
    height: 31px;
    flex: 0 0 31px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #E8F7EF;
    color: #087443;
    font-size:12px;
    font-weight: 800;
}

.ct-card-heading h2 {
    margin: 0;
    color: #101915;
    font-size: 14px;
    font-weight: 800;
}

.ct-card-heading p {
    margin: 2px 0 0;
    color: #7B8781;
    font-size:11px;
}

.ct-type-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 10px;
    margin-bottom: 17px;
}

.ct-type-option {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px;
    border: 1px solid #DDE5E1;
    border-radius: 12px;
    cursor: pointer;
}

.ct-type-option:has(input:checked) {
    border-color: #12B76A;
    background: #F2FCF6;
    box-shadow: 0 0 0 2px rgba(18,183,106,.08);
}

.ct-type-option input {
    position: absolute;
    opacity: 0;
}

.ct-type-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #E8F7EF;
    color: #087443;
}

.ct-type-icon.purple {
    background: #F0ECFF;
    color: #6941C6;
}

.ct-type-option strong,
.ct-type-option small {
    display: block;
}

.ct-type-option strong {
    color: #26342D;
    font-size:12px;
}

.ct-type-option small {
    margin-top: 2px;
    color: #7B8781;
    font-size: 8px;
    line-height: 1.45;
}

.ct-product-selector,
.ct-field {
    margin-bottom: 14px;
}

.ct-product-selector label,
.ct-field label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
    color: #344139;
    font-size:11px;
    font-weight: 700;
}

.ct-field label small {
    color: #8C9791;
    font-size: 7px;
    font-weight: 500;
}

.ct-product-selector select,
.ct-field input,
.ct-field textarea {
    width: 100%;
    border: 1px solid #DCE5E0;
    border-radius: 10px;
    background: #FFFFFF;
    color: #17251F;
    font-family: inherit;
    font-size:12px;
    outline: none;
}

.ct-product-selector select,
.ct-field input {
    height: 44px;
    padding: 0 12px;
}

.ct-field textarea {
    padding: 11px 12px;
    line-height: 1.6;
    resize: vertical;
}

.ct-product-selector select:focus,
.ct-field input:focus,
.ct-field textarea:focus {
    border-color: #12B76A;
    box-shadow: 0 0 0 3px rgba(18,183,106,.08);
}

.ct-upload {
    min-height: 105px;
    display: flex !important;
    flex-direction: column;
    justify-content: center !important;
    gap: 4px;
    border: 1px dashed #C9D8D0;
    border-radius: 12px;
    background: #FAFCFB;
    cursor: pointer;
    text-align: center;
}

.ct-upload i {
    color: #12B76A;
    font-size: 20px;
}

.ct-upload strong {
    color: #344139;
    font-size:11px;
}

.ct-upload span {
    color: #89958F;
    font-size: 7px;
}

.ct-help {
    margin: 5px 0 0;
    color: #8A9690;
    font-size: 7px;
    line-height: 1.5;
}

.ct-image-preview {
    display: grid;
    grid-template-columns: repeat(4, 70px);
    gap: 7px;
    margin-top: 8px;
}

.ct-image-preview img {
    width: 70px;
    height: 70px;
    padding: 3px;
    border: 1px solid #DCE5E0;
    border-radius: 9px;
    object-fit: contain;
    background: #FFFFFF;
}

.ct-three-fields {
    display: grid;
    grid-template-columns: .6fr 1fr 1fr;
    gap: 10px;
}

.ct-two-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.ct-security-note {
    display: flex;
    gap: 9px;
    padding: 12px;
    border: 1px solid #C8EAD8;
    border-radius: 11px;
    background: #F2FCF6;
}

.ct-security-note > i {
    margin-top: 2px;
    color: #087443;
}

.ct-security-note strong {
    color: #05603A;
    font-size:11px;
}

.ct-security-note p {
    margin: 3px 0 0;
    color: #587568;
    font-size: 8px;
    line-height: 1.55;
}

.ct-summary {
    position: sticky;
    top: 90px;
}

.ct-summary-card {
    padding: 21px;
}

.ct-summary-icon {
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    margin-bottom: 12px;
    border-radius: 12px;
    background: #E8F7EF;
    color: #087443;
}

.ct-summary h2 {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
}

.ct-summary > .ct-summary-card > p {
    margin: 4px 0 17px;
    color: #78857E;
    font-size: 8px;
    line-height: 1.5;
}

.ct-summary-lines {
    border-top: 1px solid #E8ECEA;
    border-bottom: 1px solid #E8ECEA;
    padding: 10px 0;
}

.ct-summary-lines > div,
.ct-summary-total {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.ct-summary-lines > div {
    padding: 6px 0;
    color: #68756E;
    font-size:11px;
}

.ct-summary-lines strong {
    color: #26342D;
}

.ct-summary-total {
    align-items: flex-end;
    padding: 15px 0;
}

.ct-summary-total span {
    color: #536159;
    font-size:12px;
    font-weight: 700;
}

.ct-summary-total strong {
    color: #0B3D2E;
    font-size: 24px;
    font-weight: 800;
}

.ct-protection {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 17px;
    padding: 11px;
    border-radius: 10px;
    background: #F7F9F8;
}

.ct-protection > div {
    display: flex;
    gap: 6px;
    color: #69766F;
    font-size: 8px;
}

.ct-protection i {
    width: 13px;
    color: #12B76A;
}

.ct-generate {
    width: 100%;
    min-height: 43px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 0;
    border-radius: 10px;
    background: #12B76A;
    color: #FFFFFF;
    font-size:12px;
    font-weight: 800;
    cursor: pointer;
}

.ct-summary-footnote {
    display: block;
    margin-top: 9px;
    color: #8B9690;
    font-size: 7px;
    line-height: 1.5;
    text-align: center;
}

@media(max-width: 900px) {

    .ct-layout {
        grid-template-columns: 1fr;
    }

    .ct-summary {
        position: static;
    }
}

@media(max-width: 620px) {

    .ct-header {
        flex-direction: column;
    }

    .ct-type-grid,
    .ct-three-fields,
    .ct-two-fields {
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

        /*
        |--------------------------------------------------------------------------
        | Product Payload
        |--------------------------------------------------------------------------
        */

        const payloadElement =
            document.getElementById(
                'sellerProductPayload'
            );


        let products =
            [];


        try {

            products =
                JSON.parse(
                    payloadElement
                        ?.textContent
                    ||
                    '[]'
                );

        } catch (error) {

            console.error(
                'Unable to load seller product data.',
                error
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const productSection =
            document.getElementById(
                'listedProductSection'
            );


        const productSelect =
            document.getElementById(
                'sellerProductSelect'
            );


        const title =
            document.getElementById(
                'transactionTitle'
            );


        const description =
            document.getElementById(
                'transactionDescription'
            );


        const quantity =
            document.getElementById(
                'transactionQuantity'
            );


        const unitPrice =
            document.getElementById(
                'transactionUnitPrice'
            );


        const deliveryFee =
            document.getElementById(
                'transactionDeliveryFee'
            );


        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */

        function selectedType()
        {
            return document.querySelector(
                'input[name="transaction_type"]:checked'
            )?.value;
        }


        function updateType()
        {
            const type =
                selectedType();


            if (productSection) {

                productSection.style.display =
                    type === 'listed'
                        ? ''
                        : 'none';
            }


            if (
                productSelect
            ) {

                productSelect.required =
                    type === 'listed';


                if (
                    type === 'custom'
                ) {

                    productSelect.value =
                        '';
                }
            }
        }


        document
            .querySelectorAll(
                'input[name="transaction_type"]'
            )
            .forEach(
                function (input) {

                    input.addEventListener(
                        'change',
                        function () {

                            updateType();


                            if (
                                this.value ===
                                'custom'
                            ) {

                                title.value =
                                    '';

                                description.value =
                                    '';

                                unitPrice.value =
                                    '';

                                quantity.value =
                                    1;

                                updateSummary();
                            }
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Select Existing Product
        |--------------------------------------------------------------------------
        */

        if (productSelect) {

            productSelect.addEventListener(
                'change',
                function () {

                    const product =
                        products.find(
                            function (item) {

                                return String(
                                    item.id
                                )
                                ===
                                String(
                                    productSelect.value
                                );
                            }
                        );


                    if (!product) {
                        return;
                    }


                    title.value =
                        product.name;


                    description.value =
                        product.description;


                    unitPrice.value =
                        product.price;


                    quantity.value =
                        1;


                    quantity.max =
                        product.stock;


                    updateSummary();
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Money Summary
        |--------------------------------------------------------------------------
        */

        function money(
            value
        ) {

            return new Intl
                .NumberFormat(
                    'en-NG',
                    {
                        style:
                            'currency',

                        currency:
                            'NGN',

                        maximumFractionDigits:
                            2,
                    }
                )
                .format(
                    value || 0
                );
        }


        function updateSummary()
        {
            const price =
                parseFloat(
                    unitPrice
                        ?.value
                    ||
                    0
                );


            const qty =
                parseInt(
                    quantity
                        ?.value
                    ||
                    1,
                    10
                );


            const delivery =
                parseFloat(
                    deliveryFee
                        ?.value
                    ||
                    0
                );


            const subtotal =
                price
                *
                Math.max(
                    qty,
                    1
                );


            const total =
                subtotal
                +
                delivery;


            document.getElementById(
                'summaryUnitPrice'
            ).textContent =
                money(
                    price
                );


            document.getElementById(
                'summaryQuantity'
            ).textContent =
                qty;


            document.getElementById(
                'summarySubtotal'
            ).textContent =
                money(
                    subtotal
                );


            document.getElementById(
                'summaryDelivery'
            ).textContent =
                money(
                    delivery
                );


            document.getElementById(
                'summaryTotal'
            ).textContent =
                money(
                    total
                );
        }


        [
            quantity,
            unitPrice,
            deliveryFee,
        ]
            .filter(Boolean)
            .forEach(
                function (input) {

                    input.addEventListener(
                        'input',
                        updateSummary
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Image Preview
        |--------------------------------------------------------------------------
        */

        const imagesInput =
            document.getElementById(
                'transactionImages'
            );


        const imagePreview =
            document.getElementById(
                'transactionImagePreview'
            );


        if (
            imagesInput
            &&
            imagePreview
        ) {

            imagesInput.addEventListener(
                'change',
                function () {

                    imagePreview.innerHTML =
                        '';


                    const files =
                        Array.from(
                            imagesInput.files
                            ||
                            []
                        )
                        .slice(
                            0,
                            4
                        );


                    files.forEach(
                        function (file) {

                            const reader =
                                new FileReader();


                            reader.onload =
                                function (event) {

                                    const image =
                                        document.createElement(
                                            'img'
                                        );


                                    image.src =
                                        event.target.result;


                                    imagePreview.appendChild(
                                        image
                                    );
                                };


                            reader.readAsDataURL(
                                file
                            );
                        }
                    );
                }
            );
        }


        updateType();
        updateSummary();

    }
);

</script>

@endpush


@endsection