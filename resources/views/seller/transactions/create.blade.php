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
                                \App\Support\RichTextSanitizer::sanitize(
                                    (string) 
                                    $product->description
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

                                <span>
                                    Item description / agreed condition
                                </span>


                                <small>
                                    Maximum 20,000 characters
                                </small>

                            </label>


                            <textarea
                                id="transactionDescription"
                                name="description"
                                placeholder="Describe condition, specification, warranty, accessories and anything agreed with the buyer."
                                aria-describedby="transactionDescriptionMeta transactionDescriptionError"
                                aria-required="true"
                            >{{ \App\Support\RichTextSanitizer::sanitize((string) old('description')) }}</textarea>


                            <div
                                class="ct-description-meta"
                                id="transactionDescriptionMeta"
                            >

                                <span>
                                    Add formatting, lists, links or a table when needed.
                                </span>


                                <strong id="transactionDescriptionCount">
                                    0 / 20,000 characters
                                </strong>

                            </div>


                            <p
                                class="ct-description-error"
                                id="transactionDescriptionError"
                                role="alert"
                                hidden
                            ></p>

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


                            <label
                                class="ct-upload"
                                id="transactionImagePicker"
                            >

                                <i class="fa-regular fa-images"></i>


                                <strong>
                                    Add transaction images
                                </strong>


                                <span id="transactionImageCounter">
                                    0 / 4 images selected · up to 5 MB each
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


                        <div class="ct-field ct-delivery-field">

                            <label for="transactionDeliveryNote">

                                <span>
                                    Delivery arrangement
                                </span>

                                <small>
                                    Optional · maximum 3,000 characters
                                </small>

                            </label>


                            <textarea
                                id="transactionDeliveryNote"
                                name="delivery_note"
                                placeholder="e.g. Seller will arrange delivery within Lagos. Delivery expected within 1–2 business days."
                                aria-describedby="transactionDeliveryMeta transactionDeliveryError"
                            >{{ \App\Support\RichTextSanitizer::sanitize((string) old('delivery_note')) }}</textarea>


                            <div
                                class="ct-description-meta"
                                id="transactionDeliveryMeta"
                            >

                                <span>
                                    Add formatting, lists or links when needed.
                                </span>


                                <strong id="transactionDeliveryCount">
                                    0 / 3,000 characters
                                </strong>

                            </div>


                            <p
                                class="ct-description-error"
                                id="transactionDeliveryError"
                                role="alert"
                                hidden
                            ></p>

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

                <aside
                    class="ct-summary"
                    data-service-fee-rate="{{ $serviceFeeRate }}"
                    data-vat-rate="{{ $vatRate }}"
                >

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


                    {{-- =================================================
                        SELLER PAYOUT BREAKDOWN
                    ================================================== --}}

                    <div class="ct-payout-card">

                        <div class="ct-payout-heading">

                            <span>

                                <i class="fa-solid fa-wallet"></i>

                            </span>


                            <div>

                                <h3>
                                    Seller payout breakdown
                                </h3>


                                <p>
                                    Estimated deductions when the buyer's payment is released.
                                </p>

                            </div>

                        </div>


                        <div class="ct-payout-lines">

                            <div>

                                <span>
                                    Buyer payment
                                </span>


                                <strong id="summarySellerGross">
                                    ₦0
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Midpoint service fee
                                    ({{ rtrim(rtrim(number_format($serviceFeeRate, 2), '0'), '.') }}%)
                                </span>


                                <strong
                                    id="summaryServiceFee"
                                    class="ct-deduction"
                                >
                                    −₦0
                                </strong>

                            </div>


                            <div>

                                <span>
                                    VAT on service fee
                                    ({{ rtrim(rtrim(number_format($vatRate, 2), '0'), '.') }}%)
                                </span>


                                <strong
                                    id="summaryVat"
                                    class="ct-deduction"
                                >
                                    −₦0
                                </strong>

                            </div>


                            <div class="ct-payout-charges">

                                <span>
                                    Total deductions
                                </span>


                                <strong id="summarySellerCharges">
                                    −₦0
                                </strong>

                            </div>

                        </div>


                        <div class="ct-payout-total">

                            <span>
                                You receive
                            </span>


                            <strong id="summarySellerReceives">
                                ₦0
                            </strong>

                        </div>


                        <p class="ct-payout-note">

                            <i class="fa-solid fa-circle-info"></i>

                            The service fee applies to the full transaction amount
                            (product subtotal + delivery fee). VAT applies only to the
                            Midpoint service fee.

                        </p>

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

        <link
            href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css"
            rel="stylesheet"
        >

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


        /*
        |--------------------------------------------------------------------------
        | Summernote Transaction Description
        |--------------------------------------------------------------------------
        */

        .ct-page .note-editor.note-frame {
            overflow: hidden;
            border: 1px solid #DCE5E0 !important;
            border-radius: 10px !important;
            box-shadow: none !important;
        }

        .ct-page .note-editor.note-frame.note-focused {
            border-color: #12B76A !important;
            box-shadow: 0 0 0 3px rgba(18,183,106,.08) !important;
        }

        .ct-page .note-toolbar {
            padding: 7px !important;
            border-bottom: 1px solid #E7ECE9 !important;
            background: #F8FAF9 !important;
        }

        .ct-page .note-btn {
            border-color: #DCE5E0 !important;
            background: #FFFFFF !important;
            color: #3C4942 !important;
        }

        .ct-page .note-editable {
            min-height: 190px;
            padding: 12px !important;
            background: #FFFFFF;
            color: #17251F;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            line-height: 1.7;
        }

        .ct-delivery-field .note-editable {
            min-height: 145px;
        }

        .ct-page .note-placeholder {
            color: #9AA59F !important;
            font-size: 12px;
        }

        .ct-page .note-statusbar {
            border-top: 1px solid #E7ECE9 !important;
            background: #F8FAF9 !important;
        }

        .ct-description-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 7px;
            color: #7B8781;
            font-size: 8px;
            line-height: 1.45;
        }

        .ct-description-meta strong {
            flex: 0 0 auto;
            color: #526059;
            font-size: 8px;
            font-weight: 700;
        }

        .ct-description-meta strong.is-near-limit {
            color: #B54708;
        }

        .ct-description-meta strong.is-at-limit {
            color: #B42318;
        }

        .ct-description-error {
            margin: 6px 0 0;
            color: #B42318;
            font-size: 9px;
            font-weight: 600;
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
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 9px;
            margin-top: 10px;
        }

        .ct-image-preview:empty {
            display: none;
        }

        .ct-selected-image {
            position: relative;
            min-width: 0;
            overflow: hidden;
            border: 1px solid #DCE5E0;
            border-radius: 11px;
            aspect-ratio: 1 / 1;
            background: #FFFFFF;
        }

        .ct-selected-image img {
            width: 100%;
            height: 100%;
            padding: 4px;
            object-fit: contain;
        }

        .ct-selected-image-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 24px;
            height: 24px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: rgba(180, 35, 24, .94);
            color: #FFFFFF;
            font-size: 10px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(180, 35, 24, .24);
        }

        .ct-selected-image-remove:hover {
            background: #912018;
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
            display: flex;
            flex-direction: column;
            gap: 14px;
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


        /*
        |--------------------------------------------------------------------------
        | Seller Payout Breakdown
        |--------------------------------------------------------------------------
        */

        .ct-payout-card {
            padding: 18px;
            border: 1px solid #DDE5E1;
            border-radius: 17px;
            background: #FFFFFF;
            box-shadow: 0 12px 35px -30px rgba(11,61,46,.30);
        }

        .ct-payout-heading {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding-bottom: 13px;
            border-bottom: 1px solid #E8ECEA;
        }

        .ct-payout-heading > span {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #E8F7EF;
            color: #087443;
        }

        .ct-payout-heading h3 {
            margin: 0;
            color: #17251F;
            font-size: 12px;
            font-weight: 800;
        }

        .ct-payout-heading p {
            margin: 3px 0 0;
            color: #7B8781;
            font-size: 8px;
            line-height: 1.45;
        }

        .ct-payout-lines {
            padding: 9px 0;
            border-bottom: 1px solid #E8ECEA;
        }

        .ct-payout-lines > div {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            color: #68756E;
            font-size: 9px;
        }

        .ct-payout-lines > div > span {
            min-width: 0;
        }

        .ct-payout-lines strong {
            flex: 0 0 auto;
            color: #26342D;
        }

        .ct-payout-lines strong.ct-deduction,
        .ct-payout-charges strong {
            color: #B42318;
        }

        .ct-payout-charges {
            margin-top: 4px;
            padding-top: 9px !important;
            border-top: 1px dashed #D9E2DD;
            font-weight: 700;
        }

        .ct-payout-total {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 0 10px;
        }

        .ct-payout-total span {
            color: #0B3D2E;
            font-size: 11px;
            font-weight: 800;
        }

        .ct-payout-total strong {
            color: #087443;
            font-size: 21px;
            font-weight: 800;
        }

        .ct-payout-note {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            margin: 0;
            padding: 9px;
            border-radius: 9px;
            background: #F7F9F8;
            color: #718078;
            font-size: 7px;
            line-height: 1.55;
        }

        .ct-payout-note i {
            margin-top: 2px;
            color: #12B76A;
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

            .ct-image-preview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ct-description-meta {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
        }

        </style>

    @endpush



    @push('scripts')

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

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


                const deliveryNote =
                    document.getElementById(
                        'transactionDeliveryNote'
                    );


                const transactionForm =
                    document.getElementById(
                        'createTransactionForm'
                    );


                const descriptionCounter =
                    document.getElementById(
                        'transactionDescriptionCount'
                    );


                const descriptionError =
                    document.getElementById(
                        'transactionDescriptionError'
                    );


                const deliveryCounter =
                    document.getElementById(
                        'transactionDeliveryCount'
                    );


                const deliveryError =
                    document.getElementById(
                        'transactionDeliveryError'
                    );


                const summaryPanel =
                    document.querySelector(
                        '.ct-summary'
                    );


                const serviceFeeRate =
                    Math.max(
                        0,
                        parseFloat(
                            summaryPanel
                                ?.dataset
                                .serviceFeeRate
                            ||
                            0
                        )
                    );


                const vatRate =
                    Math.max(
                        0,
                        parseFloat(
                            summaryPanel
                                ?.dataset
                                .vatRate
                            ||
                            0
                        )
                    );


                const richTextToolbar = [

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

                ];


                /*
                |--------------------------------------------------------------------------
                | Summernote Description
                |--------------------------------------------------------------------------
                */

                const descriptionLimit =
                    20000;


                let descriptionEditorReady =
                    false;


                let resettingDescription =
                    false;


                let lastAcceptedDescription =
                    description
                        ?.value
                    ||
                    '';


                function descriptionPlainText(
                    html
                ) {

                    const container =
                        document.createElement(
                            'div'
                        );


                    container.innerHTML =
                        html
                        ||
                        '';


                    return (
                        container.textContent
                        ||
                        container.innerText
                        ||
                        ''
                    )
                        .replace(
                            /\u200B/g,
                            ''
                        );
                }


                function descriptionLength(
                    html
                ) {

                    return Array
                        .from(
                            descriptionPlainText(
                                html
                            )
                        )
                        .length;
                }


                function renderDescriptionStatus(
                    length,
                    message = ''
                ) {

                    if (
                        descriptionCounter
                    ) {

                        descriptionCounter.textContent =
                            length.toLocaleString()
                            +
                            ' / 20,000 characters';


                        descriptionCounter.classList.toggle(
                            'is-near-limit',
                            length >= 18000
                            &&
                            length < descriptionLimit
                        );


                        descriptionCounter.classList.toggle(
                            'is-at-limit',
                            length >= descriptionLimit
                        );
                    }


                    if (
                        descriptionError
                    ) {

                        descriptionError.textContent =
                            message;


                        descriptionError.hidden =
                            message === '';
                    }
                }


                function currentDescription()
                {
                    if (
                        descriptionEditorReady
                        &&
                        window.jQuery
                    ) {

                        return $('#transactionDescription')
                            .summernote(
                                'code'
                            );
                    }


                    return description
                        ?.value
                        ||
                        '';
                }


                function setDescription(
                    html
                ) {

                    const nextDescription =
                        html
                        ||
                        '';


                    if (
                        descriptionEditorReady
                        &&
                        window.jQuery
                    ) {

                        resettingDescription =
                            true;


                        $('#transactionDescription')
                            .summernote(
                                'code',
                                nextDescription
                            );


                        resettingDescription =
                            false;
                    } else if (
                        description
                    ) {

                        description.value =
                            nextDescription;
                    }


                    lastAcceptedDescription =
                        nextDescription;


                    renderDescriptionStatus(
                        descriptionLength(
                            nextDescription
                        )
                    );
                }


                if (
                    window.jQuery
                    &&
                    $('#transactionDescription').length
                ) {

                    $('#transactionDescription')
                        .summernote({

                            placeholder:
                                'Describe the condition, specifications, warranty, accessories, delivery terms and everything agreed with the buyer...',

                            height:
                                210,

                            minHeight:
                                190,

                            dialogsInBody:
                                true,

                            toolbar:
                                richTextToolbar,

                            callbacks: {

                                onChange:
                                    function (contents) {

                                        if (
                                            resettingDescription
                                        ) {

                                            return;
                                        }


                                        const length =
                                            descriptionLength(
                                                contents
                                            );


                                        if (
                                            length
                                            >
                                            descriptionLimit
                                        ) {

                                            resettingDescription =
                                                true;


                                            $('#transactionDescription')
                                                .summernote(
                                                    'code',
                                                    lastAcceptedDescription
                                                );


                                            resettingDescription =
                                                false;


                                            renderDescriptionStatus(
                                                descriptionLength(
                                                    lastAcceptedDescription
                                                ),
                                                'The description cannot exceed 20,000 characters.'
                                            );


                                            return;
                                        }


                                        lastAcceptedDescription =
                                            contents;


                                        renderDescriptionStatus(
                                            length
                                        );
                                    },

                                onKeydown:
                                    function (event) {

                                        const length =
                                            descriptionLength(
                                                currentDescription()
                                            );


                                        const allowedKeys = [
                                            'Backspace',
                                            'Delete',
                                            'ArrowLeft',
                                            'ArrowRight',
                                            'ArrowUp',
                                            'ArrowDown',
                                            'Home',
                                            'End',
                                            'Tab',
                                        ];


                                        if (
                                            length >= descriptionLimit
                                            &&
                                            !allowedKeys.includes(
                                                event.key
                                            )
                                            &&
                                            !event.ctrlKey
                                            &&
                                            !event.metaKey
                                        ) {

                                            event.preventDefault();


                                            renderDescriptionStatus(
                                                length,
                                                'The description cannot exceed 20,000 characters.'
                                            );
                                        }
                                    },

                                onImageUpload:
                                    function () {

                                        alert(
                                            'Please use the transaction image uploader for item photos.'
                                        );
                                    },
                            },
                        });


                    descriptionEditorReady =
                        true;


                    lastAcceptedDescription =
                        currentDescription();


                    renderDescriptionStatus(
                        descriptionLength(
                            lastAcceptedDescription
                        )
                    );
                } else if (
                    description
                ) {

                    description.maxLength =
                        descriptionLimit;


                    description.addEventListener(
                        'input',
                        function () {

                            renderDescriptionStatus(
                                Array
                                    .from(
                                        description.value
                                    )
                                    .length
                            );
                        }
                    );


                    renderDescriptionStatus(
                        Array
                            .from(
                                description.value
                            )
                            .length
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Summernote Delivery Arrangement
                |--------------------------------------------------------------------------
                */

                const deliveryLimit =
                    3000;


                let deliveryEditorReady =
                    false;


                let resettingDelivery =
                    false;


                let lastAcceptedDelivery =
                    deliveryNote
                        ?.value
                    ||
                    '';


                function renderDeliveryStatus(
                    length,
                    message = ''
                ) {

                    if (
                        deliveryCounter
                    ) {

                        deliveryCounter.textContent =
                            length.toLocaleString()
                            +
                            ' / 3,000 characters';


                        deliveryCounter.classList.toggle(
                            'is-near-limit',
                            length >= 2700
                            &&
                            length < deliveryLimit
                        );


                        deliveryCounter.classList.toggle(
                            'is-at-limit',
                            length >= deliveryLimit
                        );
                    }


                    if (
                        deliveryError
                    ) {

                        deliveryError.textContent =
                            message;


                        deliveryError.hidden =
                            message === '';
                    }
                }


                function currentDeliveryNote()
                {
                    if (
                        deliveryEditorReady
                        &&
                        window.jQuery
                    ) {

                        return $('#transactionDeliveryNote')
                            .summernote(
                                'code'
                            );
                    }


                    return deliveryNote
                        ?.value
                        ||
                        '';
                }


                if (
                    window.jQuery
                    &&
                    $('#transactionDeliveryNote').length
                ) {

                    $('#transactionDeliveryNote')
                        .summernote({

                            placeholder:
                                'e.g. Seller will arrange delivery within Lagos. Delivery is expected within 1–2 business days...',

                            height:
                                165,

                            minHeight:
                                145,

                            dialogsInBody:
                                true,

                            toolbar:
                                richTextToolbar,

                            callbacks: {

                                onChange:
                                    function (contents) {

                                        if (
                                            resettingDelivery
                                        ) {

                                            return;
                                        }


                                        const length =
                                            descriptionLength(
                                                contents
                                            );


                                        if (
                                            length
                                            >
                                            deliveryLimit
                                        ) {

                                            resettingDelivery =
                                                true;


                                            $('#transactionDeliveryNote')
                                                .summernote(
                                                    'code',
                                                    lastAcceptedDelivery
                                                );


                                            resettingDelivery =
                                                false;


                                            renderDeliveryStatus(
                                                descriptionLength(
                                                    lastAcceptedDelivery
                                                ),
                                                'The delivery arrangement cannot exceed 3,000 characters.'
                                            );


                                            return;
                                        }


                                        lastAcceptedDelivery =
                                            contents;


                                        renderDeliveryStatus(
                                            length
                                        );
                                    },

                                onKeydown:
                                    function (event) {

                                        const length =
                                            descriptionLength(
                                                currentDeliveryNote()
                                            );


                                        const allowedKeys = [
                                            'Backspace',
                                            'Delete',
                                            'ArrowLeft',
                                            'ArrowRight',
                                            'ArrowUp',
                                            'ArrowDown',
                                            'Home',
                                            'End',
                                            'Tab',
                                        ];


                                        if (
                                            length >= deliveryLimit
                                            &&
                                            !allowedKeys.includes(
                                                event.key
                                            )
                                            &&
                                            !event.ctrlKey
                                            &&
                                            !event.metaKey
                                        ) {

                                            event.preventDefault();


                                            renderDeliveryStatus(
                                                length,
                                                'The delivery arrangement cannot exceed 3,000 characters.'
                                            );
                                        }
                                    },

                                onImageUpload:
                                    function () {

                                        alert(
                                            'Images are not allowed inside the delivery arrangement.'
                                        );
                                    },
                            },
                        });


                    deliveryEditorReady =
                        true;


                    lastAcceptedDelivery =
                        currentDeliveryNote();


                    renderDeliveryStatus(
                        descriptionLength(
                            lastAcceptedDelivery
                        )
                    );
                } else if (
                    deliveryNote
                ) {

                    deliveryNote.maxLength =
                        deliveryLimit;


                    deliveryNote.addEventListener(
                        'input',
                        function () {

                            renderDeliveryStatus(
                                Array
                                    .from(
                                        deliveryNote.value
                                    )
                                    .length
                            );
                        }
                    );


                    renderDeliveryStatus(
                        Array
                            .from(
                                deliveryNote.value
                            )
                            .length
                    );
                }


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

                                        setDescription(
                                            ''
                                        );

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


                            setDescription(
                                product.description
                            );


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


                function roundMoney(
                    value
                ) {

                    return Math.round(
                        (
                            Number(
                                value
                            )
                            +
                            Number.EPSILON
                        )
                        *
                        100
                    )
                    /
                    100;
                }


                function setSummaryMoney(
                    id,
                    value,
                    deduction = false
                ) {

                    const element =
                        document.getElementById(
                            id
                        );


                    if (!element) {
                        return;
                    }


                    element.textContent =
                        deduction
                            ? '−' + money(value)
                            : money(value);
                }


                function updateSummary()
                {
                    const price =
                        Math.max(
                            0,
                            parseFloat(
                                unitPrice
                                    ?.value
                                ||
                                0
                            )
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
                        Math.max(
                            0,
                            parseFloat(
                                deliveryFee
                                    ?.value
                                ||
                                0
                            )
                            ||
                            0
                        );


                    const subtotal =
                        roundMoney(
                            price
                            *
                            Math.max(
                                qty,
                                1
                            )
                        );


                    const total =
                        roundMoney(
                            subtotal
                            +
                            delivery
                        );


                    /*
                    |------------------------------------------------------------------
                    | Match Server / Paystack Seller Fee Algorithm
                    |------------------------------------------------------------------
                    |
                    | Service fee:
                    | Product subtotal + delivery fee.
                    |
                    | VAT:
                    | Service fee only.
                    |
                    | This means the Midpoint service fee is charged on the full
                    | transaction amount paid by the buyer.
                    |
                    */

                    const feeBaseAmount =
                        roundMoney(
                            subtotal
                            +
                            delivery
                        );


                    const serviceFee =
                        roundMoney(
                            feeBaseAmount
                            *
                            (
                                serviceFeeRate
                                /
                                100
                            )
                        );


                    const vat =
                        roundMoney(
                            serviceFee
                            *
                            (
                                vatRate
                                /
                                100
                            )
                        );


                    const sellerCharges =
                        roundMoney(
                            serviceFee
                            +
                            vat
                        );


                    const sellerReceives =
                        roundMoney(
                            Math.max(
                                0,
                                total
                                -
                                sellerCharges
                            )
                        );


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


                    setSummaryMoney(
                        'summarySellerGross',
                        total
                    );


                    setSummaryMoney(
                        'summaryServiceFee',
                        serviceFee,
                        true
                    );


                    setSummaryMoney(
                        'summaryVat',
                        vat,
                        true
                    );


                    setSummaryMoney(
                        'summarySellerCharges',
                        sellerCharges,
                        true
                    );


                    setSummaryMoney(
                        'summarySellerReceives',
                        sellerReceives
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
                | Transaction Image Manager
                |--------------------------------------------------------------------------
                |
                | Browser file inputs replace their FileList on every selection. Keep an
                | independent array, merge every new selection into it, and write the
                | complete array back through DataTransfer so all four images submit.
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


                const imageCounter =
                    document.getElementById(
                        'transactionImageCounter'
                    );


                let selectedImages =
                    [];


                function imageFileKey(
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


                function syncSelectedImages()
                {
                    if (!imagesInput) {
                        return;
                    }


                    const transfer =
                        new DataTransfer();


                    selectedImages.forEach(
                        function (file) {

                            transfer.items.add(
                                file
                            );
                        }
                    );


                    imagesInput.files =
                        transfer.files;
                }


                function renderSelectedImages()
                {
                    if (!imagePreview) {
                        return;
                    }


                    imagePreview.innerHTML =
                        '';


                    selectedImages.forEach(
                        function (
                            file,
                            index
                        ) {

                            const card =
                                document.createElement(
                                    'div'
                                );


                            card.className =
                                'ct-selected-image';


                            const image =
                                document.createElement(
                                    'img'
                                );


                            image.alt =
                                file.name;


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


                            const removeButton =
                                document.createElement(
                                    'button'
                                );


                            removeButton.type =
                                'button';


                            removeButton.className =
                                'ct-selected-image-remove';


                            removeButton.setAttribute(
                                'aria-label',
                                'Remove '
                                +
                                file.name
                            );


                            removeButton.innerHTML =
                                '<i class="fa-solid fa-xmark"></i>';


                            removeButton.addEventListener(
                                'click',
                                function () {

                                    selectedImages.splice(
                                        index,
                                        1
                                    );


                                    syncSelectedImages();

                                    renderSelectedImages();
                                }
                            );


                            card.appendChild(
                                image
                            );


                            card.appendChild(
                                removeButton
                            );


                            imagePreview.appendChild(
                                card
                            );
                        }
                    );


                    if (
                        imageCounter
                    ) {

                        imageCounter.textContent =
                            selectedImages.length
                            +
                            ' / 4 images selected · up to 5 MB each';
                    }
                }


                if (
                    imagesInput
                    &&
                    imagePreview
                ) {

                    imagesInput.addEventListener(
                        'change',
                        function () {

                            const incomingFiles =
                                Array.from(
                                    imagesInput.files
                                    ||
                                    []
                                );


                            const allowedTypes =
                                new Set([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ]);


                            const maximumBytes =
                                5
                                *
                                1024
                                *
                                1024;


                            const knownFiles =
                                new Set(
                                    selectedImages
                                        .map(
                                            imageFileKey
                                        )
                                );


                            let invalidFileFound =
                                false;


                            incomingFiles.forEach(
                                function (file) {

                                    if (
                                        !allowedTypes.has(
                                            file.type
                                        )
                                        ||
                                        file.size > maximumBytes
                                    ) {

                                        invalidFileFound =
                                            true;


                                        return;
                                    }


                                    const key =
                                        imageFileKey(
                                            file
                                        );


                                    if (
                                        !knownFiles.has(
                                            key
                                        )
                                    ) {

                                        selectedImages.push(
                                            file
                                        );


                                        knownFiles.add(
                                            key
                                        );
                                    }
                                }
                            );


                            if (
                                invalidFileFound
                            ) {

                                alert(
                                    'Only JPG, PNG or WEBP images up to 5 MB are allowed.'
                                );
                            }


                            if (
                                selectedImages.length
                                >
                                4
                            ) {

                                selectedImages =
                                    selectedImages.slice(
                                        0,
                                        4
                                    );


                                alert(
                                    'You can upload a maximum of 4 transaction images.'
                                );
                            }


                            syncSelectedImages();

                            renderSelectedImages();
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Final Description Validation
                |--------------------------------------------------------------------------
                */

                if (
                    transactionForm
                    &&
                    description
                ) {

                    transactionForm.addEventListener(
                        'submit',
                        function (event) {

                            const html =
                                currentDescription();


                            const plainText =
                                descriptionPlainText(
                                    html
                                )
                                    .replace(
                                        /\u00A0/g,
                                        ' '
                                    )
                                    .trim();


                            const length =
                                Array
                                    .from(
                                        plainText
                                    )
                                    .length;


                            if (
                                length === 0
                                ||
                                length > descriptionLimit
                            ) {

                                event.preventDefault();


                                renderDescriptionStatus(
                                    length,
                                    length === 0
                                        ? 'Please enter the item description or agreed condition.'
                                        : 'The description cannot exceed 20,000 characters.'
                                );


                                if (
                                    descriptionEditorReady
                                ) {

                                    $('#transactionDescription')
                                        .summernote(
                                            'focus'
                                        );
                                } else {

                                    description.focus();
                                }


                                return;
                            }


                            description.value =
                                html;
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Final Delivery Arrangement Validation
                |--------------------------------------------------------------------------
                */

                if (
                    transactionForm
                    &&
                    deliveryNote
                ) {

                    transactionForm.addEventListener(
                        'submit',
                        function (event) {

                            const html =
                                currentDeliveryNote();


                            const plainText =
                                descriptionPlainText(
                                    html
                                )
                                    .replace(
                                        /\u00A0/g,
                                        ' '
                                    )
                                    .trim();


                            const length =
                                Array
                                    .from(
                                        plainText
                                    )
                                    .length;


                            if (
                                length
                                >
                                deliveryLimit
                            ) {

                                event.preventDefault();


                                renderDeliveryStatus(
                                    length,
                                    'The delivery arrangement cannot exceed 3,000 characters.'
                                );


                                if (
                                    deliveryEditorReady
                                ) {

                                    $('#transactionDeliveryNote')
                                        .summernote(
                                            'focus'
                                        );
                                } else {

                                    deliveryNote.focus();
                                }


                                return;
                            }


                            deliveryNote.value =
                                length === 0
                                    ? ''
                                    : html;
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
