@php

    $isBuyer =
        $mode === 'buyer';

    $isSeller =
        $mode === 'seller';

    $isMarketplaceOrder =
            $transaction->transaction_source
            ===
            'marketplace_checkout';


    $isSellerCreatedTransaction =
        $transaction->transaction_source
        ===
        'seller_link';


    /*
    |--------------------------------------------------------------------------
    | Ordered Product Information
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We use the values saved in SecureTransaction as the main source.
    |
    | That is intentional because they are the product snapshot at the
    | moment the buyer ordered.
    |
    | For example, if the seller later changes the live product name or
    | price, the historical transaction must still show what was actually
    | purchased.
    |
    */

    $orderedItemName =
        $transaction->title
        ?:
        $transaction->product?->name
        ?:
        'Item';


    $orderedQuantity =
        max(
            1,
            (int) $transaction->quantity
        );


    $orderedUnitPrice =
        (float) $transaction->unit_price;


    $orderedSubtotal =
        (float) $transaction->subtotal;


    $buyerDisplayName =
        $transaction->buyer?->name
        ?:
        $transaction->buyer_email
        ?:
        'Buyer';
    /*
    |--------------------------------------------------------------------------
    | Dispute State
    |--------------------------------------------------------------------------
    */

    $dispute =
        $transaction->dispute;


    $hasDispute =
        !is_null(
            $dispute
        );


    $isActiveDispute =
        $hasDispute
        &&
        $dispute->status
        !==
        \App\Models\TransactionDispute::STATUS_RESOLVED;


    $isResolvedDispute =
        $hasDispute
        &&
        $dispute->status
        ===
        \App\Models\TransactionDispute::STATUS_RESOLVED;


    $totalPaid =
        $transaction->paid_amount
        ?:
        $transaction->total_amount;

    $sellerNet =
        $transaction->seller_net_amount
        ?:
        $totalPaid;

    $countdownEnd =
        null;

    if (
        in_array(
            $transaction->status,
            [
                \App\Models\SecureTransaction::STATUS_DELIVERED,
                \App\Models\SecureTransaction::STATUS_INSPECTION,
            ],
            true
        )
        &&
        $transaction->auto_complete_at
    ) {
        $countdownEnd =
            $transaction->auto_complete_at;
    }

    $sellerProfile =
        $transaction
            ->seller
            ?->sellerBusinessProfile;

    $whatsappUrl =
        null;

    if (
        $sellerProfile
        &&
        $sellerProfile->whatsapp_enabled
    ) {
        $whatsappUrl =
            $sellerProfile->whatsappUrl(
                'Hi '
                .
                (
                    $transaction->seller?->name
                    ?:
                    'Seller'
                )
                .
                ', I am contacting you about delivery for Midpoint transaction '
                .
                $transaction->reference
                .
                '.'
            );
    }

@endphp


<div class="tm-page">


    @if(session('success'))

        <div class="tm-alert success">
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="tm-alert error">

            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach

        </div>

    @endif



    <div class="tm-header">

        <div>

            <a
                href="{{
                    $isBuyer
                        ? route('buyer.transactions')
                        : route('seller.transactions')
                }}"
                class="tm-back"
            >
                ← Back to transactions
            </a>


            @if(
                $isSeller
                &&
                $isMarketplaceOrder
            )

                <span class="tm-order-source marketplace">

                    <i class="fa-solid fa-bag-shopping"></i>

                    Marketplace order

                </span>

            @elseif(
                $isSeller
                &&
                $isSellerCreatedTransaction
            )

                <span class="tm-order-source seller-created">

                    <i class="fa-solid fa-link"></i>

                    Seller-created transaction

                </span>

            @endif


            <h1>
                {{ $orderedItemName }}
            </h1>


            <p>

                Transaction {{ $transaction->reference }}

                ·

                @if($isSeller)

                    {{ $orderedQuantity }}
                    {{ \Illuminate\Support\Str::plural('item', $orderedQuantity) }}

                    ·

                @endif

                Created {{ $transaction->created_at->format('d M Y') }}

            </p>

        </div>


        <span
            class="
                tm-status
                {{
                    $transaction->status
                    ===
                    \App\Models\SecureTransaction::STATUS_DISPUTED
                        ? 'disputed'
                        : ''
                }}
            "
        >

            {{ $transaction->status_label }}

        </span>

    </div>



    {{-- =========================================================
        ACTIVE / RESOLVED DISPUTE NOTICE
    ========================================================== --}}

    @if($isActiveDispute)

        <div class="tm-dispute-banner">

            <i class="fa-solid fa-triangle-exclamation"></i>


            <div>

                <strong>

                    @if(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_AWAITING_BUYER
                    )

                        Dispute awaiting buyer

                    @elseif(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_AWAITING_SELLER
                    )

                        Dispute awaiting seller

                    @elseif(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_UNDER_REVIEW
                    )

                        Dispute under review

                    @else

                        Transaction disputed

                    @endif

                </strong>


                <span>

                    @if(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_AWAITING_BUYER
                    )

                        Midpoint is waiting for additional information from the buyer.
                        Automatic completion and seller payout remain paused.

                    @elseif(
                        $dispute->status
                        ===
                        \App\Models\TransactionDispute::STATUS_AWAITING_SELLER
                    )

                        Midpoint is waiting for additional information from the seller.
                        Automatic completion and seller payout remain paused.

                    @else

                        Automatic completion and seller payout are paused while
                        Midpoint reviews this case.

                    @endif

                </span>

            </div>

        </div>


    @elseif($isResolvedDispute)

        <div class="tm-dispute-resolved-banner">

            <i class="fa-solid fa-circle-check"></i>


            <div>

                <strong>
                    Dispute resolved
                </strong>


                <span>
                    Midpoint has completed the dispute review.
                    This transaction has resumed its normal protection and settlement workflow.

                    @if($dispute->resolved_at)

                        Resolved

                        {{
                            $dispute
                                ->resolved_at
                                ->format(
                                    'd M Y, h:i A'
                                )
                        }}.

                    @endif

                </span>


                @if($dispute->admin_note)

                    <small>

                        <strong>
                            Midpoint resolution:
                        </strong>

                        {{ $dispute->admin_note }}

                    </small>

                @endif

            </div>

        </div>

    @endif



    <div class="tm-layout">


        <section class="tm-card">

            <h2>
                Transaction timeline
            </h2>


            <div class="tm-timeline">

                @foreach($timeline as $item)

                    <div
                        class="
                            tm-step
                            {{ $item['state'] }}
                        "
                    >

                        <div class="tm-dot">

                            @if($item['state'] === 'done')

                                <i class="fa-solid fa-check"></i>

                            @endif

                        </div>


                        <div>

                            <strong>
                                {{ $item['title'] }}
                            </strong>

                            <span>
                                {{ $item['meta'] }}
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>



        <aside class="tm-side">

            @if($isSeller)

                <div class="tm-card tm-order-details-card">


                    <div class="tm-order-card-heading">


                        <div>

                            <span class="tm-order-card-eyebrow">

                                @if($isMarketplaceOrder)

                                    MARKETPLACE ORDER

                                @elseif($isSellerCreatedTransaction)

                                    SECURE TRANSACTION

                                @else

                                    ORDER DETAILS

                                @endif

                            </span>


                            <h3>
                                Ordered item
                            </h3>

                        </div>


                        <div class="tm-order-box-icon">

                            <i class="fa-solid fa-box-open"></i>

                        </div>


                    </div>



                    {{-- =================================================
                        PRODUCT NAME
                    ================================================== --}}

                    <div class="tm-ordered-product">


                        <span>
                            Product
                        </span>


                        <strong>
                            {{ $orderedItemName }}
                        </strong>


                        @if($transaction->description)

                            <p>

                                {{
                                    \Illuminate\Support\Str::limit(
                                        $transaction->description,
                                        100
                                    )
                                }}

                            </p>

                        @endif


                    </div>



                    {{-- =================================================
                        QUANTITY
                    ================================================== --}}

                    <div class="tm-order-detail-row">


                        <span>

                            <i class="fa-solid fa-cubes"></i>

                            Quantity ordered

                        </span>


                        <strong class="tm-order-quantity">

                            {{ number_format($orderedQuantity) }}

                        </strong>


                    </div>



                    {{-- =================================================
                        UNIT PRICE
                    ================================================== --}}

                    <div class="tm-order-detail-row">


                        <span>

                            <i class="fa-solid fa-tag"></i>

                            Unit price

                        </span>


                        <strong>

                            ₦{{ number_format(
                                $orderedUnitPrice,
                                2
                            ) }}

                        </strong>


                    </div>



                    {{-- =================================================
                        SUBTOTAL
                    ================================================== --}}

                    <div class="tm-order-detail-row">


                        <span>

                            <i class="fa-solid fa-calculator"></i>

                            Product subtotal

                        </span>


                        <strong>

                            ₦{{ number_format(
                                $orderedSubtotal,
                                2
                            ) }}

                        </strong>


                    </div>



                    {{-- =================================================
                        PRODUCT ID
                    ================================================== --}}

                    @if($transaction->seller_product_id)

                        <div class="tm-order-detail-row">


                            <span>

                                <i class="fa-solid fa-hashtag"></i>

                                Product ID

                            </span>


                            <strong>

                                {{ $transaction->seller_product_id }}

                            </strong>


                        </div>

                    @endif



                    {{-- =================================================
                        BUYER
                    ================================================== --}}

                    <div class="tm-order-detail-row">


                        <span>

                            <i class="fa-solid fa-user"></i>

                            Ordered by

                        </span>


                        <strong>

                            {{ $buyerDisplayName }}

                        </strong>


                    </div>



                    {{-- =================================================
                        BUYER PHONE
                    ================================================== --}}

                    @if($transaction->buyer_phone)

                        <div class="tm-order-detail-row">


                            <span>

                                <i class="fa-solid fa-phone"></i>

                                Buyer phone

                            </span>


                            <strong>

                                {{ $transaction->buyer_phone }}

                            </strong>


                        </div>

                    @endif



                    {{-- =================================================
                        REFERENCE
                    ================================================== --}}

                    <div class="tm-order-reference">


                        <span>
                            Order reference
                        </span>


                        <strong>
                            {{ $transaction->reference }}
                        </strong>


                    </div>



                    {{-- =================================================
                        MARKETPLACE INFORMATION
                    ================================================== --}}

                    @if($isMarketplaceOrder)

                        <div class="tm-marketplace-note">


                            <i class="fa-solid fa-circle-check"></i>


                            <span>

                                This order was placed directly from your
                                listed products.

                            </span>


                        </div>

                    @endif


                </div>

            @endif

            @if($countdownEnd)

                <div
                    class="tm-card tm-countdown"
                    data-countdown-end="{{
                        $countdownEnd->toIso8601String()
                    }}"
                >

                    <h3>

                        {{
                            $transaction->status
                            ===
                            \App\Models\SecureTransaction::STATUS_INSPECTION

                                ? 'Inspection countdown'

                                : 'Auto-complete countdown'
                        }}

                    </h3>


                    <div class="tm-counter">

                        <div>

                            <strong id="countDays">
                                0
                            </strong>

                            <span>
                                DAY
                            </span>

                        </div>


                        <div>

                            <strong id="countHours">
                                0
                            </strong>

                            <span>
                                HRS
                            </span>

                        </div>


                        <div>

                            <strong id="countMinutes">
                                0
                            </strong>

                            <span>
                                MIN
                            </span>

                        </div>

                    </div>


                    <p>

                        @if(
                            $transaction->status
                            ===
                            \App\Models\SecureTransaction::STATUS_INSPECTION
                        )

                            Funds are automatically approved for release when the inspection period ends unless a dispute is opened.

                        @else

                            Transaction automatically completes after 3 days if the buyer takes no action.

                        @endif

                    </p>

                </div>

            @endif



            <div class="tm-card">

                <h3>
                    Money breakdown
                </h3>


                @if($isSeller)

                    <div class="tm-money">

                        <div>

                            <span>
                                Buyer paid
                            </span>

                            <strong>
                                ₦{{ number_format((float) $totalPaid, 2) }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Product subtotal
                            </span>

                            <strong>
                                ₦{{ number_format((float) $transaction->subtotal, 2) }}
                            </strong>

                        </div>


                        <div>

                            <span>
                                Delivery
                            </span>

                            <strong>
                                ₦{{ number_format((float) $transaction->delivery_fee, 2) }}
                            </strong>

                        </div>


                        <div class="deduction">

                            <span>
                                Service fee
                                ({{ rtrim(rtrim(number_format((float) $transaction->service_fee_rate, 2), '0'), '.') }}%)
                            </span>

                            <strong>
                                − ₦{{ number_format((float) $transaction->service_fee_amount, 2) }}
                            </strong>

                        </div>


                        <div class="deduction">

                            <span>
                                VAT
                                ({{ rtrim(rtrim(number_format((float) $transaction->vat_rate, 2), '0'), '.') }}% of fee)
                            </span>

                            <strong>
                                − ₦{{ number_format((float) $transaction->vat_amount, 2) }}
                            </strong>

                        </div>


                        <div class="total">

                            <span>
                                Seller receives
                            </span>

                            <strong>
                                ₦{{ number_format((float) $sellerNet, 2) }}
                            </strong>

                        </div>

                    </div>

                @else

                    <div class="tm-buyer-total">

                        <span>
                            Total paid
                        </span>

                        <strong>
                            ₦{{ number_format((float) $totalPaid, 2) }}
                        </strong>

                    </div>


                    <a
                        href="{{
                            route(
                                'buyer.transactions.invoice',
                                $transaction
                            )
                        }}"
                        class="tm-invoice"
                    >

                        <i class="fa-solid fa-file-pdf"></i>

                        Download payment invoice

                    </a>

                @endif

            </div>



            <div class="tm-card">

                <h3>
                    Parties
                </h3>


                <div class="tm-party">

                    <div class="tm-avatar">

                        {{
                            strtoupper(
                                substr(
                                    $transaction->seller?->name
                                    ?:
                                    'S',
                                    0,
                                    1
                                )
                            )
                        }}

                    </div>


                    <div>

                        <strong>
                            {{ $transaction->seller?->name ?: 'Seller' }}
                        </strong>

                        <span>
                            Seller
                        </span>

                    </div>

                </div>


                <div class="tm-divider"></div>


                <div class="tm-party">

                    <div class="tm-avatar buyer">

                        {{
                            strtoupper(
                                substr(
                                    $transaction->buyer?->name
                                    ?:
                                    'B',
                                    0,
                                    1
                                )
                            )
                        }}

                    </div>


                    <div>

                        <strong>

                            {{
                                $transaction->buyer?->name
                                ?:
                                $transaction->buyer_email
                            }}

                        </strong>

                        <span>
                            Buyer
                        </span>

                    </div>

                </div>

            </div>



            <div class="tm-card">

                <h3>
                    Delivery
                </h3>


                <p class="tm-delivery">

                    {{
                        $transaction->delivery_note
                        ?:
                        'Seller-arranged delivery.'
                    }}

                </p>


                @if($isBuyer && $whatsappUrl)

                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="tm-whatsapp"
                    >

                        <i class="fa-brands fa-whatsapp"></i>

                        Message seller about delivery

                    </a>

                @endif

            </div>



            @if(
                $isSeller
                &&
                !$isActiveDispute
            )

                <div class="tm-card">

                    <h3>
                        Update order status
                    </h3>


                    @php

                        $nextSellerStatus =
                            match($transaction->status) {

                                \App\Models\SecureTransaction::STATUS_PAYMENT_SECURED =>
                                    [
                                        'value' => \App\Models\SecureTransaction::STATUS_PREPARING_ITEM,
                                        'label' => 'Mark as Preparing item',
                                        'icon' => 'fa-box-open',
                                    ],

                                \App\Models\SecureTransaction::STATUS_PREPARING_ITEM =>
                                    [
                                        'value' => \App\Models\SecureTransaction::STATUS_DISPATCHED,
                                        'label' => 'Mark as Dispatched',
                                        'icon' => 'fa-truck',
                                    ],

                                \App\Models\SecureTransaction::STATUS_DISPATCHED =>
                                    [
                                        'value' => \App\Models\SecureTransaction::STATUS_IN_TRANSIT,
                                        'label' => 'Mark as In transit',
                                        'icon' => 'fa-truck-fast',
                                    ],

                                \App\Models\SecureTransaction::STATUS_IN_TRANSIT =>
                                    [
                                        'value' => \App\Models\SecureTransaction::STATUS_DELIVERED,
                                        'label' => 'Mark as Delivered',
                                        'icon' => 'fa-box-circle-check',
                                    ],

                                default =>
                                    null,
                            };

                    @endphp


                    @if($nextSellerStatus)

                        <form
                            method="POST"
                            action="{{
                                route(
                                    'seller.transactions.status.update',
                                    $transaction
                                )
                            }}"
                        >

                            @csrf
                            @method('PATCH')


                            <input
                                type="hidden"
                                name="status"
                                value="{{ $nextSellerStatus['value'] }}"
                            >


                            <button
                                type="submit"
                                class="tm-main-action"
                            >

                                <i
                                    class="
                                        fa-solid
                                        {{ $nextSellerStatus['icon'] }}
                                    "
                                ></i>

                                {{ $nextSellerStatus['label'] }}

                            </button>

                        </form>

                    @else

                        <p class="tm-info-text">
                            No seller action is required at the current stage.
                        </p>

                    @endif

                </div>

            @endif



            @if($isBuyer)

                <div class="tm-card">

                    <h3>
                        Buyer actions
                    </h3>


                    {{-- =================================================
                        DELIVERED
                    ================================================== --}}

                    @if(
                        $transaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_DELIVERED
                    )

                        <button
                            type="button"
                            class="tm-main-action"
                            id="openOrderReceivedModal"
                        >

                            <i class="fa-solid fa-box"></i>

                            Order received

                        </button>


                        @if(!$hasDispute)

                            <a
                                href="{{
                                    route(
                                        'buyer.transactions.dispute.create',
                                        $transaction
                                    )
                                }}"
                                class="tm-dispute-action"
                            >

                                <i class="fa-solid fa-scale-balanced"></i>

                                Open a dispute

                            </a>


                        @elseif($isResolvedDispute)

                            <div class="tm-resolved-small">

                                <strong>
                                    Previous dispute resolved
                                </strong>

                                <span>
                                    Midpoint has completed the dispute review.
                                    You can now continue with the normal transaction actions above.
                                </span>

                            </div>

                        @endif


                    {{-- =================================================
                        INSPECTION
                    ================================================== --}}

                    @elseif(
                        $transaction->status
                        ===
                        \App\Models\SecureTransaction::STATUS_INSPECTION
                    )

                        <form
                            method="POST"
                            action="{{
                                route(
                                    'buyer.transactions.accept',
                                    $transaction
                                )
                            }}"
                        >

                            @csrf


                            <button
                                type="submit"
                                class="tm-main-action"
                            >

                                <i class="fa-solid fa-check"></i>

                                Accept item & release funds

                            </button>

                        </form>


                        @if(!$hasDispute)

                            <a
                                href="{{
                                    route(
                                        'buyer.transactions.dispute.create',
                                        $transaction
                                    )
                                }}"
                                class="tm-dispute-action"
                            >

                                <i class="fa-solid fa-scale-balanced"></i>

                                Open a dispute

                            </a>


                        @elseif($isResolvedDispute)

                            <div class="tm-resolved-small">

                                <strong>
                                    Previous dispute resolved
                                </strong>

                                <span>
                                    Midpoint has completed the dispute review.
                                    You may accept the item and release funds when you are ready.
                                </span>

                            </div>

                        @endif


                    {{-- =================================================
                        ACTIVE DISPUTE
                    ================================================== --}}

                    @elseif($isActiveDispute)

                        <div class="tm-dispute-small">

                            <strong>

                                @if(
                                    $dispute->status
                                    ===
                                    \App\Models\TransactionDispute::STATUS_AWAITING_BUYER
                                )

                                    Action required from buyer

                                @elseif(
                                    $dispute->status
                                    ===
                                    \App\Models\TransactionDispute::STATUS_AWAITING_SELLER
                                )

                                    Awaiting seller response

                                @elseif(
                                    $dispute->status
                                    ===
                                    \App\Models\TransactionDispute::STATUS_UNDER_REVIEW
                                )

                                    Dispute under review

                                @else

                                    Transaction disputed

                                @endif

                            </strong>


                            <span>

                                @if(
                                    $dispute->status
                                    ===
                                    \App\Models\TransactionDispute::STATUS_AWAITING_BUYER
                                )

                                    Midpoint needs information or action from you.
                                    Please check your email and notifications.

                                @elseif(
                                    $dispute->status
                                    ===
                                    \App\Models\TransactionDispute::STATUS_AWAITING_SELLER
                                )

                                    Midpoint is waiting for additional information from the seller.

                                @else

                                    Seller payout is currently paused while Midpoint reviews the dispute.

                                @endif

                            </span>

                        </div>

                    @endif


                    <a
                        href="{{ route('support') }}"
                        class="tm-support-action"
                    >

                        <i class="fa-regular fa-comments"></i>

                        Contact support

                    </a>

                </div>

            @endif

        </aside>

    </div>

</div>



@if(
    $isBuyer
    &&
    $transaction->status
    ===
    \App\Models\SecureTransaction::STATUS_DELIVERED
)

    <div
        class="tm-modal"
        id="orderReceivedModal"
        aria-hidden="true"
    >

        <div class="tm-modal-backdrop"></div>


        <div class="tm-modal-card">

            <div class="tm-modal-icon">
                📦
            </div>


            <h2>
                Order received?
            </h2>


            <p>
                Confirming receipt does not immediately force you to release funds.
                Choose whether to accept the item now or use your inspection period.
            </p>


            <form
                method="POST"
                action="{{
                    route(
                        'buyer.transactions.accept',
                        $transaction
                    )
                }}"
            >

                @csrf


                <button
                    type="submit"
                    class="tm-modal-option"
                >

                    <span class="option-icon success">
                        ✓
                    </span>


                    <span>

                        <strong>
                            Accept item & release funds
                        </strong>

                        <small>
                            You've checked the item and are satisfied. Seller payout will begin immediately.
                        </small>

                    </span>

                </button>

            </form>



            <form
                method="POST"
                action="{{
                    route(
                        'buyer.transactions.inspection',
                        $transaction
                    )
                }}"
            >

                @csrf


                <button
                    type="submit"
                    class="tm-modal-option"
                >

                    <span class="option-icon inspection">
                        ⏱
                    </span>


                    <span>

                        <strong>
                            Go to 8-hour inspection
                        </strong>

                        <small>
                            Test or inspect the item. Funds remain pending release while the countdown runs.
                        </small>

                    </span>

                </button>

            </form>


            <button
                type="button"
                class="tm-modal-cancel"
                id="closeOrderReceivedModal"
            >
                Cancel — my order hasn't arrived yet
            </button>

        </div>

    </div>

@endif



<style>
.tm-order-source {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    margin-top: 9px;
    padding: 6px 9px;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 800;

    letter-spacing: .04em;
    text-transform: uppercase;
}


.tm-order-source.marketplace {
    background: #ECFDF3;
    color: #067647;
}


.tm-order-source.seller-created {
    background: #F4F3FF;
    color: #6941C6;
}


/*
|--------------------------------------------------------------------------
| Seller Order Details Card
|--------------------------------------------------------------------------
*/

.tm-order-details-card {
    border-color: #BFE7D0;
    background:
        linear-gradient(
            145deg,
            #FFFFFF 0%,
            #F6FFF9 100%
        );
}


.tm-order-card-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;

    margin-bottom: 15px;
}


.tm-order-card-heading h3 {
    margin: 4px 0 0;
}


.tm-order-card-eyebrow {
    display: block;

    color: #087647;

    font-size: 8px;
    font-weight: 800;

    letter-spacing: .06em;
}


.tm-order-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 36px;
    height: 36px;

    flex: 0 0 36px;

    border-radius: 10px;

    background: #EAFBF1;
    color: #087647;

    font-size: 15px;
}


/*
|--------------------------------------------------------------------------
| Ordered Product
|--------------------------------------------------------------------------
*/

.tm-ordered-product {
    margin-bottom: 12px;
    padding: 13px;

    border: 1px solid #DCEBE3;
    border-radius: 10px;

    background: #FFFFFF;
}


.tm-ordered-product > span {
    display: block;

    margin-bottom: 5px;

    color: #7B8781;

    font-size: 9px;
}


.tm-ordered-product > strong {
    display: block;

    color: #101915;

    font-size: 15px;
    font-weight: 800;
}


.tm-ordered-product p {
    margin: 6px 0 0;

    color: #748078;

    font-size: 9px;
    line-height: 1.5;
}


/*
|--------------------------------------------------------------------------
| Order Detail Row
|--------------------------------------------------------------------------
*/

.tm-order-detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    padding: 9px 0;

    border-bottom: 1px solid #ECF1EE;
}


.tm-order-detail-row > span {
    display: flex;
    align-items: center;
    gap: 6px;

    color: #68756E;

    font-size: 9px;
}


.tm-order-detail-row > span i {
    width: 13px;

    color: #82918A;

    text-align: center;
}


.tm-order-detail-row > strong {
    max-width: 55%;

    color: #17251F;

    font-size: 10px;

    text-align: right;

    word-break: break-word;
}


.tm-order-quantity {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 29px;
    height: 29px;

    padding: 0 8px;

    border-radius: 8px;

    background: #0B3D2E;

    color: #FFFFFF !important;

    font-size: 12px !important;
}


/*
|--------------------------------------------------------------------------
| Order Reference
|--------------------------------------------------------------------------
*/

.tm-order-reference {
    margin-top: 12px;
    padding: 10px;

    border-radius: 9px;

    background: #F3F7F5;
}


.tm-order-reference span,
.tm-order-reference strong {
    display: block;
}


.tm-order-reference span {
    margin-bottom: 4px;

    color: #7D8982;

    font-size: 8px;
}


.tm-order-reference strong {
    color: #0B3D2E;

    font-size: 9px;

    word-break: break-all;
}


/*
|--------------------------------------------------------------------------
| Marketplace Note
|--------------------------------------------------------------------------
*/

.tm-marketplace-note {
    display: flex;
    align-items: flex-start;

    gap: 7px;

    margin-top: 11px;
    padding: 10px;

    border-radius: 9px;

    background: #ECFDF3;
    color: #067647;

    font-size: 9px;
    line-height: 1.5;
}


.tm-marketplace-note i {
    margin-top: 2px;
}
.tm-page {
    width: 100%;
}

.tm-alert {
    margin-bottom: 15px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.55;
}

.tm-alert.success {
    background: #ECFDF3;
    color: #067647;
}

.tm-alert.error {
    background: #FEF3F2;
    color: #B42318;
}

.tm-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.tm-back {
    color: #12B76A;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.tm-header h1 {
    margin: 8px 0 4px;
    color: #101915;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 26px;
}

.tm-header p {
    margin: 0;
    color: #7B8781;
    font-size: 10px;
}

.tm-status {
    padding: 8px 12px;
    border-radius: 999px;
    background: #F4F3FF;
    color: #6941C6;
    font-size: 10px;
    font-weight: 800;
}

.tm-status.disputed {
    background: #FEF3F2;
    color: #D92D20;
}

.tm-dispute-banner {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    padding: 15px;
    border: 1px solid #FECDCA;
    border-radius: 12px;
    background: #FEF3F2;
    color: #B42318;
}

.tm-dispute-banner strong,
.tm-dispute-banner span {
    display: block;
}

.tm-dispute-banner strong {
    font-size: 13px;
}

.tm-dispute-banner span {
    margin-top: 3px;
    font-size: 10px;
    line-height: 1.55;
}

/*
|--------------------------------------------------------------------------
| Resolved Dispute
|--------------------------------------------------------------------------
*/

.tm-dispute-resolved-banner {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    padding: 15px;
    border: 1px solid #ABEFC6;
    border-radius: 12px;
    background: #ECFDF3;
    color: #067647;
}

.tm-dispute-resolved-banner > i {
    margin-top: 2px;
    font-size: 16px;
}

.tm-dispute-resolved-banner strong,
.tm-dispute-resolved-banner span,
.tm-dispute-resolved-banner small {
    display: block;
}

.tm-dispute-resolved-banner strong {
    font-size: 13px;
}

.tm-dispute-resolved-banner span {
    margin-top: 3px;
    font-size: 10px;
    line-height: 1.6;
}

.tm-dispute-resolved-banner small {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #CDEDD9;
    color: #46715C;
    font-size: 10px;
    line-height: 1.6;
}

.tm-dispute-resolved-banner small strong {
    display: inline;
    font-size: inherit;
}

.tm-resolved-small {
    margin-top: 10px;
    padding: 13px;
    border: 1px solid #ABEFC6;
    border-radius: 9px;
    background: #ECFDF3;
    color: #067647;
}

.tm-resolved-small strong,
.tm-resolved-small span {
    display: block;
}

.tm-resolved-small strong {
    font-size: 11px;
}

.tm-resolved-small span {
    margin-top: 4px;
    font-size: 10px;
    line-height: 1.55;
}

.tm-layout {
    display: grid;
    grid-template-columns:
        minmax(0, 1.5fr)
        315px;
    gap: 16px;
    align-items: start;
}

.tm-card {
    padding: 20px;
    border: 1px solid #DCE5E0;
    border-radius: 16px;
    background: #FFFFFF;
}

.tm-card h2,
.tm-card h3 {
    margin: 0 0 17px;
    color: #101915;
    font-size: 14px;
    font-weight: 800;
}

.tm-side {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.tm-step {
    position: relative;
    display: flex;
    gap: 11px;
    min-height: 62px;
}

.tm-step:not(:last-child)::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 10px;
    bottom: -1px;
    width: 1px;
    background: #DCE5E0;
}

.tm-dot {
    position: relative;
    z-index: 2;
    width: 22px;
    height: 22px;
    flex: 0 0 22px;
    display: grid;
    place-items: center;
    border: 1px solid #DCE5E0;
    border-radius: 50%;
    background: #FFFFFF;
    color: #FFFFFF;
    font-size: 10px;
}

.tm-step.done .tm-dot {
    border-color: #12B76A;
    background: #12B76A;
}

.tm-step.active .tm-dot {
    border: 2px solid #7557FF;
    box-shadow: 0 0 0 5px #F1EEFF;
}

.tm-step.active strong {
    color: #7557FF;
}

.tm-step strong {
    display: block;
    color: #202B25;
    font-size: 13px;
}

.tm-step span {
    display: block;
    margin-top: 4px;
    color: #758078;
    font-size: 10px;
    line-height: 1.5;
}

.tm-counter {
    display: grid;
    grid-template-columns:
        repeat(3, 1fr);
    gap: 8px;
}

.tm-counter > div {
    padding: 13px 6px;
    border-radius: 10px;
    background: #0B3D2E;
    color: #FFFFFF;
    text-align: center;
}

.tm-counter strong {
    display: block;
    font-size: 18px;
}

.tm-counter span {
    display: block;
    margin-top: 4px;
    color: #9FC3B6;
    font-size: 10px;
}

.tm-countdown p {
    margin: 11px 0 0;
    color: #77837C;
    font-size: 10px;
    line-height: 1.5;
    text-align: center;
}

.tm-money > div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    font-size: 10px;
}

.tm-money span {
    color: #65726B;
}

.tm-money strong {
    color: #17251F;
}

.tm-money .deduction strong {
    color: #F04438;
}

.tm-money .total {
    margin-top: 8px;
    padding: 14px;
    border-radius: 10px;
    background: #0B3D2E;
}

.tm-money .total span,
.tm-money .total strong {
    color: #FFFFFF;
}

.tm-buyer-total {
    padding: 16px;
    border-radius: 10px;
    background: #F2FCF6;
    text-align: center;
}

.tm-buyer-total span {
    display: block;
    color: #66756D;
    font-size: 10px;
}

.tm-buyer-total strong {
    display: block;
    margin-top: 5px;
    color: #0B3D2E;
    font-size: 24px;
}

.tm-invoice,
.tm-whatsapp,
.tm-support-action,
.tm-dispute-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    margin-top: 10px;
    padding: 11px;
    border: 1px solid #DCE5E0;
    border-radius: 9px;
    background: #FFFFFF;
    color: #0B3D2E;
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
}

.tm-whatsapp {
    color: #067647;
}

.tm-dispute-action {
    border-color: #FECDCA;
    background: #FEF3F2;
    color: #D92D20;
}

.tm-party {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tm-avatar {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #0B3D2E;
    color: #FFFFFF;
    font-size: 11px;
    font-weight: 800;
}

.tm-avatar.buyer {
    background: #7557FF;
}

.tm-party strong {
    display: block;
    font-size: 11px;
}

.tm-party span {
    display: block;
    margin-top: 2px;
    color: #7B8781;
    font-size: 10px;
}

.tm-divider {
    height: 1px;
    margin: 14px 0;
    background: #E6ECE9;
}

.tm-delivery {
    margin: 0;
    color: #65726B;
    font-size: 10px;
    line-height: 1.65;
}

.tm-main-action {
    width: 100%;
    min-height: 43px;
    border: 0;
    border-radius: 9px;
    background: #12B76A;
    color: #FFFFFF;
    font-family: inherit;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.tm-info-text,
.tm-coming {
    margin: 0;
    color: #7B8781;
    font-size: 10px;
    line-height: 1.55;
}

.tm-dispute-small {
    padding: 13px;
    border-radius: 9px;
    background: #FEF3F2;
    color: #B42318;
}

.tm-dispute-small strong,
.tm-dispute-small span {
    display: block;
}

.tm-dispute-small strong {
    font-size: 11px;
}

.tm-dispute-small span {
    margin-top: 4px;
    font-size: 10px;
}

.tm-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.tm-modal.open {
    display: flex;
}

.tm-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(16, 24, 20, .6);
    backdrop-filter: blur(4px);
}

.tm-modal-card {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 470px;
    padding: 28px;
    border-radius: 18px;
    background: #FFFFFF;
    text-align: center;
}

.tm-modal-icon {
    width: 56px;
    height: 56px;
    display: grid;
    place-items: center;
    margin: 0 auto;
    border-radius: 50%;
    background: #EAF8F1;
    font-size: 23px;
}

.tm-modal-card h2 {
    margin: 15px 0 7px;
    font-size: 21px;
}

.tm-modal-card > p {
    margin: 0 0 20px;
    color: #66756D;
    font-size: 11px;
    line-height: 1.6;
}

.tm-modal-option {
    width: 100%;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 10px;
    padding: 15px;
    border: 1px solid #DCE5E0;
    border-radius: 12px;
    background: #FFFFFF;
    font-family: inherit;
    text-align: left;
    cursor: pointer;
}

.option-icon {
    width: 39px;
    height: 39px;
    flex: 0 0 39px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    font-size: 16px;
}

.option-icon.success {
    background: #EAF8F1;
}

.option-icon.inspection {
    background: #F2EEFF;
}

.tm-modal-option strong {
    display: block;
    color: #17251F;
    font-size: 12px;
}

.tm-modal-option small {
    display: block;
    margin-top: 4px;
    color: #66756D;
    font-size: 10px;
    line-height: 1.55;
}

.tm-modal-cancel {
    margin-top: 17px;
    border: 0;
    background: transparent;
    color: #66756D;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

@media(max-width: 850px) {

    .tm-layout {
        grid-template-columns: 1fr;
    }

}

</style>



<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const countdown =
            document.querySelector(
                '[data-countdown-end]'
            );

        if (countdown) {

            const end =
                new Date(
                    countdown.dataset.countdownEnd
                ).getTime();


            function updateCountdown()
            {
                let difference =
                    Math.max(
                        0,
                        end - Date.now()
                    );


                const days =
                    Math.floor(
                        difference
                        /
                        86400000
                    );


                difference %=
                    86400000;


                const hours =
                    Math.floor(
                        difference
                        /
                        3600000
                    );


                difference %=
                    3600000;


                const minutes =
                    Math.floor(
                        difference
                        /
                        60000
                    );


                document.getElementById(
                    'countDays'
                ).textContent =
                    days;


                document.getElementById(
                    'countHours'
                ).textContent =
                    hours;


                document.getElementById(
                    'countMinutes'
                ).textContent =
                    minutes;
            }


            updateCountdown();


            setInterval(
                updateCountdown,
                30000
            );
        }


        const modal =
            document.getElementById(
                'orderReceivedModal'
            );


        const open =
            document.getElementById(
                'openOrderReceivedModal'
            );


        const close =
            document.getElementById(
                'closeOrderReceivedModal'
            );


        open?.addEventListener(
            'click',
            function () {

                modal?.classList.add(
                    'open'
                );

            }
        );


        close?.addEventListener(
            'click',
            function () {

                modal?.classList.remove(
                    'open'
                );

            }
        );


        modal
            ?.querySelector(
                '.tm-modal-backdrop'
            )
            ?.addEventListener(
                'click',
                function () {

                    modal.classList.remove(
                        'open'
                    );

                }
            );

    }
);

</script>