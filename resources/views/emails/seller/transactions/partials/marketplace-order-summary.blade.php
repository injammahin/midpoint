@php

    /*
    |--------------------------------------------------------------------------
    | Marketplace Order Snapshot
    |--------------------------------------------------------------------------
    |
    | We intentionally use SecureTransaction snapshot fields here instead
    | of reading the live SellerProduct record.
    |
    | Your ProductCheckoutController already copies the product information
    | into SecureTransaction when the buyer checks out.
    |
    */

    $buyerName =
        $transaction->buyer?->name
        ?:
        $transaction->buyer_email;


    $quantity =
        max(
            1,
            (int) $transaction->quantity
        );


    $unitPrice =
        (float) $transaction->unit_price;


    $subtotal =
        (float) $transaction->subtotal;


    $deliveryFee =
        (float) $transaction->delivery_fee;


    $totalPaid =
        (float) (
            $transaction->paid_amount
            ?:
            $transaction->total_amount
        );


    /*
    |--------------------------------------------------------------------------
    | Product Snapshot Image
    |--------------------------------------------------------------------------
    */

    $transactionImages =
        is_array($transaction->images)
            ? array_values(
                array_filter(
                    $transaction->images
                )
            )
            : [];


    $firstImage =
        $transactionImages[0]
        ??
        null;

@endphp


<div class="mp-marketplace-order">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="mp-marketplace-header">


        <div class="mp-marketplace-header-left">


            <div class="mp-marketplace-badge">

                <i class="fa-solid fa-bag-shopping"></i>

                Marketplace order

            </div>


            <h2>

                You received a new order

            </h2>


            <p>

                <strong>
                    {{ $buyerName }}
                </strong>

                purchased

                <strong>
                    {{ $quantity }} × {{ $transaction->title }}
                </strong>

                from your listed products.

            </p>


        </div>


        <div class="mp-marketplace-reference">

            <span>
                Order reference
            </span>

            <strong>
                {{ $transaction->reference }}
            </strong>

        </div>


    </div>



    {{-- =========================================================
        PRODUCT + ORDER INFORMATION
    ========================================================== --}}

    <div class="mp-marketplace-body">


        {{-- Product --}}

        <div class="mp-marketplace-product">


            <div class="mp-marketplace-product-image">


                @if($firstImage)

                    <img
                        src="{{
                            asset(
                                'storage/'
                                .
                                ltrim(
                                    $firstImage,
                                    '/'
                                )
                            )
                        }}"
                        alt="{{ $transaction->title }}"
                    >

                @else

                    <div class="mp-marketplace-placeholder">

                        <i class="fa-solid fa-box-open"></i>

                    </div>

                @endif


            </div>


            <div class="mp-marketplace-product-content">


                <span class="mp-marketplace-small-label">

                    Ordered product

                </span>


                <h3>

                    {{ $transaction->title }}

                </h3>


                @if($transaction->description)

                    <p class="mp-marketplace-description">

                        {{
                            \Illuminate\Support\Str::limit(
                                $transaction->description,
                                190
                            )
                        }}

                    </p>

                @endif


                <div class="mp-marketplace-tags">


                    <span>

                        <i class="fa-solid fa-cubes"></i>

                        Quantity:
                        {{ $quantity }}

                    </span>


                    <span>

                        <i class="fa-solid fa-tag"></i>

                        ₦{{ number_format(
                            $unitPrice,
                            2
                        ) }}
                        each

                    </span>


                    @if($transaction->seller_product_id)

                        <span>

                            <i class="fa-solid fa-box"></i>

                            Product #{{ $transaction->seller_product_id }}

                        </span>

                    @endif


                </div>


            </div>


        </div>



        {{-- Order Money --}}

        <div class="mp-marketplace-money">


            <div class="mp-marketplace-money-row">

                <span>
                    Quantity
                </span>

                <strong>
                    {{ $quantity }}
                </strong>

            </div>


            <div class="mp-marketplace-money-row">

                <span>
                    Unit price
                </span>

                <strong>

                    ₦{{ number_format(
                        $unitPrice,
                        2
                    ) }}

                </strong>

            </div>


            <div class="mp-marketplace-money-row">

                <span>
                    Product subtotal
                </span>

                <strong>

                    ₦{{ number_format(
                        $subtotal,
                        2
                    ) }}

                </strong>

            </div>


            <div class="mp-marketplace-money-row">

                <span>
                    Delivery
                </span>

                <strong>

                    ₦{{ number_format(
                        $deliveryFee,
                        2
                    ) }}

                </strong>

            </div>


            <div class="mp-marketplace-money-row total">

                <span>
                    Buyer paid
                </span>

                <strong>

                    ₦{{ number_format(
                        $totalPaid,
                        2
                    ) }}

                </strong>

            </div>


        </div>


    </div>



    {{-- =========================================================
        BUYER DETAILS
    ========================================================== --}}

    <div class="mp-marketplace-section">


        <div class="mp-marketplace-section-title">

            <div class="mp-marketplace-section-icon">

                <i class="fa-solid fa-user"></i>

            </div>


            <div>

                <strong>
                    Buyer details
                </strong>

                <span>
                    Customer who placed this marketplace order
                </span>

            </div>

        </div>



        <div class="mp-marketplace-info-grid">


            <div class="mp-marketplace-info">

                <span>
                    Buyer name
                </span>

                <strong>
                    {{ $buyerName }}
                </strong>

            </div>


            <div class="mp-marketplace-info">

                <span>
                    Buyer email
                </span>

                <strong>

                    {{
                        $transaction->buyer_email
                        ?:
                        'Not provided'
                    }}

                </strong>

            </div>


            <div class="mp-marketplace-info">

                <span>
                    Delivery phone
                </span>

                <strong>

                    {{
                        $transaction->buyer_phone
                        ?:
                        'Not provided'
                    }}

                </strong>

            </div>


            <div class="mp-marketplace-info">

                <span>
                    Order type
                </span>

                <strong>
                    Listed product purchase
                </strong>

            </div>


        </div>


    </div>



    {{-- =========================================================
        DELIVERY
    ========================================================== --}}

    <div class="mp-marketplace-delivery">


        <div class="mp-marketplace-delivery-icon">

            <i class="fa-solid fa-location-dot"></i>

        </div>


        <div>

            <span>
                Delivery address
            </span>


            <strong>

                {{
                    $transaction->delivery_note
                    ?:
                    'No delivery address was provided.'
                }}

            </strong>

        </div>


    </div>



    {{-- =========================================================
        PAYMENT MESSAGE
    ========================================================== --}}

    @if(
        $transaction->payment_status
        ===
        \App\Models\SecureTransaction::PAYMENT_PAID
    )

        <div class="mp-marketplace-payment-secured">


            <i class="fa-solid fa-circle-check"></i>


            <div>

                <strong>
                    Buyer payment is secured
                </strong>

                <span>
                    Prepare the exact product and quantity shown above,
                    then continue updating the order status below.
                </span>

            </div>


        </div>

    @endif


</div>



<style>

    /*
    |--------------------------------------------------------------------------
    | Marketplace Order Card
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-order {

        margin-bottom: 18px;

        padding: 20px;

        border:
            1px solid
            #CFE7DA;

        border-radius:
            16px;

        background:
            linear-gradient(
                135deg,
                #FFFFFF 0%,
                #F7FFFA 100%
            );

        box-shadow:
            0 8px 24px
            rgba(
                11,
                61,
                46,
                .05
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-header {

        display: flex;

        align-items:
            flex-start;

        justify-content:
            space-between;

        gap:
            20px;

        padding-bottom:
            18px;

        border-bottom:
            1px solid
            #E4EEE9;

    }


    .mp-marketplace-header-left {

        min-width:
            0;

    }


    .mp-marketplace-badge {

        display:
            inline-flex;

        align-items:
            center;

        gap:
            6px;

        padding:
            7px 10px;

        border-radius:
            999px;

        background:
            #E9FBF1;

        color:
            #067647;

        font-size:
            10px;

        font-weight:
            800;

        text-transform:
            uppercase;

        letter-spacing:
            .04em;

    }


    .mp-marketplace-header h2 {

        margin:
            11px 0 5px;

        color:
            #111A16;

        font-size:
            20px;

        font-weight:
            800;

    }


    .mp-marketplace-header p {

        margin:
            0;

        color:
            #66756D;

        font-size:
            12px;

        line-height:
            1.65;

    }


    .mp-marketplace-reference {

        flex:
            0 0 215px;

        padding:
            11px 13px;

        border:
            1px solid
            #DFE9E4;

        border-radius:
            10px;

        background:
            #FFFFFF;

        text-align:
            right;

    }


    .mp-marketplace-reference span {

        display:
            block;

        margin-bottom:
            4px;

        color:
            #829088;

        font-size:
            9px;

    }


    .mp-marketplace-reference strong {

        display:
            block;

        color:
            #0B3D2E;

        font-size:
            11px;

        word-break:
            break-word;

    }


    /*
    |--------------------------------------------------------------------------
    | Product / Money
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-body {

        display:
            grid;

        grid-template-columns:
            minmax(0, 1.45fr)
            minmax(280px, .75fr);

        gap:
            16px;

        margin-top:
            18px;

    }


    .mp-marketplace-product {

        display:
            flex;

        align-items:
            flex-start;

        gap:
            14px;

        min-width:
            0;

        padding:
            15px;

        border:
            1px solid
            #E2EBE6;

        border-radius:
            12px;

        background:
            #FFFFFF;

    }


    .mp-marketplace-product-image {

        flex:
            0 0 100px;

        width:
            100px;

        height:
            100px;

        overflow:
            hidden;

        border-radius:
            11px;

        background:
            #EFF7F3;

    }


    .mp-marketplace-product-image img {

        display:
            block;

        width:
            100%;

        height:
            100%;

        object-fit:
            cover;

    }


    .mp-marketplace-placeholder {

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        width:
            100%;

        height:
            100%;

        color:
            #0B3D2E;

        font-size:
            27px;

    }


    .mp-marketplace-product-content {

        min-width:
            0;

    }


    .mp-marketplace-small-label {

        display:
            block;

        color:
            #85928B;

        font-size:
            9px;

        font-weight:
            700;

        text-transform:
            uppercase;

    }


    .mp-marketplace-product-content h3 {

        margin:
            5px 0;

        color:
            #15221C;

        font-size:
            16px;

    }


    .mp-marketplace-description {

        margin:
            0;

        color:
            #6E7D75;

        font-size:
            11px;

        line-height:
            1.55;

    }


    .mp-marketplace-tags {

        display:
            flex;

        flex-wrap:
            wrap;

        gap:
            6px;

        margin-top:
            11px;

    }


    .mp-marketplace-tags span {

        display:
            inline-flex;

        align-items:
            center;

        gap:
            5px;

        padding:
            6px 8px;

        border-radius:
            7px;

        background:
            #F2F7F4;

        color:
            #536259;

        font-size:
            9px;

        font-weight:
            700;

    }


    /*
    |--------------------------------------------------------------------------
    | Money
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-money {

        padding:
            14px;

        border:
            1px solid
            #E2EBE6;

        border-radius:
            12px;

        background:
            #FFFFFF;

    }


    .mp-marketplace-money-row {

        display:
            flex;

        align-items:
            center;

        justify-content:
            space-between;

        gap:
            10px;

        padding:
            8px 0;

        border-bottom:
            1px solid
            #EDF2EF;

    }


    .mp-marketplace-money-row:last-child {

        border-bottom:
            none;

    }


    .mp-marketplace-money-row span {

        color:
            #77857D;

        font-size:
            10px;

    }


    .mp-marketplace-money-row strong {

        color:
            #17251F;

        font-size:
            11px;

    }


    .mp-marketplace-money-row.total {

        margin-top:
            6px;

        padding:
            11px;

        border:
            none;

        border-radius:
            8px;

        background:
            #0B3D2E;

    }


    .mp-marketplace-money-row.total span,
    .mp-marketplace-money-row.total strong {

        color:
            #FFFFFF;

    }


    /*
    |--------------------------------------------------------------------------
    | Buyer Section
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-section {

        margin-top:
            16px;

        padding:
            15px;

        border:
            1px solid
            #E2EBE6;

        border-radius:
            12px;

        background:
            #FFFFFF;

    }


    .mp-marketplace-section-title {

        display:
            flex;

        align-items:
            center;

        gap:
            9px;

        margin-bottom:
            13px;

    }


    .mp-marketplace-section-icon {

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        width:
            34px;

        height:
            34px;

        flex:
            0 0 34px;

        border-radius:
            9px;

        background:
            #EAF9F1;

        color:
            #087647;

    }


    .mp-marketplace-section-title strong,
    .mp-marketplace-section-title span {

        display:
            block;

    }


    .mp-marketplace-section-title strong {

        color:
            #17251F;

        font-size:
            12px;

    }


    .mp-marketplace-section-title span {

        margin-top:
            2px;

        color:
            #85928B;

        font-size:
            9px;

    }


    .mp-marketplace-info-grid {

        display:
            grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap:
            10px;

    }


    .mp-marketplace-info {

        padding:
            10px;

        border-radius:
            8px;

        background:
            #F7FAF8;

    }


    .mp-marketplace-info span,
    .mp-marketplace-info strong {

        display:
            block;

    }


    .mp-marketplace-info span {

        margin-bottom:
            4px;

        color:
            #88958E;

        font-size:
            9px;

    }


    .mp-marketplace-info strong {

        color:
            #1A2821;

        font-size:
            10px;

        word-break:
            break-word;

    }


    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-delivery {

        display:
            flex;

        align-items:
            flex-start;

        gap:
            10px;

        margin-top:
            16px;

        padding:
            13px;

        border-radius:
            11px;

        background:
            #FFF8EA;

    }


    .mp-marketplace-delivery-icon {

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        width:
            32px;

        height:
            32px;

        flex:
            0 0 32px;

        border-radius:
            8px;

        background:
            #FFFFFF;

        color:
            #9A6B16;

    }


    .mp-marketplace-delivery span,
    .mp-marketplace-delivery strong {

        display:
            block;

    }


    .mp-marketplace-delivery span {

        margin-bottom:
            4px;

        color:
            #92712F;

        font-size:
            9px;

    }


    .mp-marketplace-delivery strong {

        color:
            #6D531E;

        font-size:
            11px;

        line-height:
            1.5;

    }


    /*
    |--------------------------------------------------------------------------
    | Secured Message
    |--------------------------------------------------------------------------
    */

    .mp-marketplace-payment-secured {

        display:
            flex;

        align-items:
            flex-start;

        gap:
            10px;

        margin-top:
            16px;

        padding:
            13px;

        border:
            1px solid
            #BDE6CE;

        border-radius:
            11px;

        background:
            #EEFBF3;

        color:
            #087647;

    }


    .mp-marketplace-payment-secured > i {

        margin-top:
            2px;

        font-size:
            17px;

    }


    .mp-marketplace-payment-secured strong,
    .mp-marketplace-payment-secured span {

        display:
            block;

    }


    .mp-marketplace-payment-secured strong {

        font-size:
            11px;

    }


    .mp-marketplace-payment-secured span {

        margin-top:
            3px;

        color:
            #527162;

        font-size:
            9px;

        line-height:
            1.5;

    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (
        max-width:
        900px
    ) {

        .mp-marketplace-header {

            flex-direction:
                column;

        }


        .mp-marketplace-reference {

            width:
                100%;

            flex:
                none;

            text-align:
                left;

        }


        .mp-marketplace-body {

            grid-template-columns:
                1fr;

        }

    }


    @media (
        max-width:
        600px
    ) {

        .mp-marketplace-order {

            padding:
                14px;

        }


        .mp-marketplace-product {

            flex-direction:
                column;

        }


        .mp-marketplace-product-image {

            width:
                100%;

            height:
                180px;

            flex:
                none;

        }


        .mp-marketplace-info-grid {

            grid-template-columns:
                1fr;

        }

    }

</style>