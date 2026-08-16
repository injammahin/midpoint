@php
    $isSeller = $mode === 'seller';

    $statusOptions = [
        'awaiting_payment' => 'Awaiting payment',
        'payment_secured' => 'Payment secured',
        'dispatched' => 'Dispatched',
        'inspection' => 'Inspection',
        'disputed' => 'Disputed',
        'release_approved' => 'Release approved',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="tx-page">

    <div class="tx-head">

        <div>

            <span class="tx-eyebrow">
                Secure transactions
            </span>

            <h1>
                Transactions
            </h1>

            <p>
                {{ $isSeller ? 'Manage the secure transactions you created for buyers.' : 'Track your paid Midpoint transactions and order progress.' }}
            </p>

        </div>

        @if($isSeller)

            <a
                href="{{ route('seller.transactions.create') }}"
                class="tx-create"
            >
                <i class="fa-solid fa-plus"></i>
                Create transaction
            </a>

        @endif

    </div>

    <div class="tx-stats">

        @foreach($stats as $label => $value)

            <div class="tx-stat">

                <span>
                    {{ ucfirst($label) }}
                </span>

                <strong>
                    {{ number_format($value) }}
                </strong>

            </div>

        @endforeach

    </div>

    <div class="tx-panel">

        <form
            method="GET"
            class="tx-filter"
        >

            <div class="tx-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search transaction, item or buyer..."
                >

            </div>

            <select name="status">

                <option value="">
                    All statuses
                </option>

                @foreach($statusOptions as $value => $label)

                    <option
                        value="{{ $value }}"
                        @selected(request('status') === $value)
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>

            <button type="submit">
                Filter
            </button>

        </form>

        <div class="tx-list">

            @forelse($transactions as $transaction)

                @php
                    $detailUrl = $isSeller
                        ? route('seller.transactions.show', $transaction)
                        : route('buyer.transactions.show', $transaction);

                    $amount = $transaction->paid_amount ?: $transaction->total_amount;

                    $statusClass = str_replace('_', '-', $transaction->status);
                @endphp

                <article class="tx-row">

                    <div class="tx-product">

                        <div class="tx-product-icon">

                            <i class="fa-solid fa-box"></i>

                        </div>

                    <div class="tx-product-copy">


                        <a href="{{ $detailUrl }}">

                            {{ $transaction->title }}

                        </a>


                        @if($isSeller)

                            <div class="tx-order-meta">


                                @if(
                                    $transaction->transaction_source
                                    ===
                                    'marketplace_checkout'
                                )

                                    <span class="tx-source marketplace">

                                        <i class="fa-solid fa-bag-shopping"></i>

                                        Marketplace order

                                    </span>


                                @elseif(
                                    $transaction->transaction_source
                                    ===
                                    'seller_link'
                                )

                                    <span class="tx-source seller-link">

                                        <i class="fa-solid fa-link"></i>

                                        Seller-created

                                    </span>

                                @endif


                                <span class="tx-qty">

                                    Qty:
                                    {{ number_format(
                                        max(
                                            1,
                                            (int) $transaction->quantity
                                        )
                                    ) }}

                                </span>


                            </div>

                        @endif


                        <span>

                            {{ $transaction->reference }}

                        </span>


                    </div>

                    </div>

                    <div class="tx-person">

                        <span>
                            {{ $isSeller ? 'Buyer' : 'Seller' }}
                        </span>

                        <strong>
                            @if($isSeller)
                                {{ $transaction->buyer?->name ?: $transaction->buyer_email }}
                            @else
                                {{ $transaction->seller?->name ?: 'Seller' }}
                            @endif
                        </strong>

                    </div>

                    <div class="tx-amount">

                        <span>
                            Amount
                        </span>

                        <strong>
                            ₦{{ number_format((float) $amount, 2) }}
                        </strong>

                    </div>

                    <div class="tx-status">

                        <span class="tx-badge {{ $statusClass }}">
                            {{ $transaction->status_label }}
                        </span>

                        <small>
                            {{ $transaction->created_at->diffForHumans() }}
                        </small>

                    </div>

                    <div class="tx-actions">

                        @if($isSeller && $transaction->payment_status !== \App\Models\SecureTransaction::PAYMENT_PAID)

                            <button
                                type="button"
                                class="tx-copy"
                                data-copy="{{ $transaction->share_url }}"
                                title="Copy buyer link"
                            >
                                <i class="fa-regular fa-copy"></i>
                            </button>

                        @endif

                        <a
                            href="{{ $detailUrl }}"
                            class="tx-open"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>

                </article>

            @empty

                <div class="tx-empty">

                    <i class="fa-regular fa-file-lines"></i>

                    <h3>
                        No transactions found
                    </h3>

                    <p>
                        {{ $isSeller ? 'Create your first secure transaction to get started.' : 'Your completed payments will appear here.' }}
                    </p>

                </div>

            @endforelse

        </div>

        @if($transactions->hasPages())

            <div class="tx-pagination">
                {{ $transactions->links() }}
            </div>

        @endif

    </div>

</div>

<style>

.tx-page {
    width:100%;
}

.tx-head {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}

.tx-eyebrow {
    color:#12B76A;
    font-size: 11px;
    font-weight:800;
    letter-spacing:.1em;
    text-transform:uppercase;
}
.tx-source-pill {
    display:inline-flex !important;

    align-items:center;

    gap:5px;

    width:max-content;

    margin-top:5px;

    padding:4px 7px;

    border-radius:999px;

    font-size:9px !important;
    font-weight:800;

    line-height:1;
}


.tx-source-pill.marketplace {
    background:#ECFDF3;

    color:#067647 !important;
}


.tx-source-pill.seller-link {
    background:#F3F0FF;

    color:#6941C6 !important;
}
.tx-head h1 {
    margin:4px 0;
    color:#101915;
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:27px;
}

.tx-head p {
    margin:0;
    color:#748079;
    font-size: 12px;
}

.tx-create {
    display:flex;
    align-items:center;
    gap:7px;
    padding:11px 15px;
    border-radius:10px;
    background:#12B76A;
    color:#fff;
    font-size: 11px;
    font-weight:800;
    text-decoration:none;
}

.tx-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:16px;
}

.tx-stat {
    padding:15px;
    border:1px solid #DCE5E0;
    border-radius:13px;
    background:#fff;
}
.tx-product-copy {
    min-width: 0;
}


.tx-order-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;

    gap: 5px;

    margin-top: 5px;
    margin-bottom: 4px;
}


.tx-source,
.tx-qty {
    display: inline-flex !important;
    align-items: center;

    gap: 4px;

    width: max-content;

    padding: 4px 6px;

    border-radius: 999px;

    font-size: 8px !important;
    font-weight: 800;

    line-height: 1;
}


.tx-source.marketplace {
    background: #ECFDF3;

    color: #067647 !important;
}


.tx-source.seller-link {
    background: #F4F3FF;

    color: #6941C6 !important;
}


.tx-qty {
    background: #F2F4F3;

    color: #47544D !important;
}
.tx-stat span,
.tx-stat strong {
    display:block;
}

.tx-stat span {
    color:#7B8781;
    font-size: 10px;
}

.tx-stat strong {
    margin-top:4px;
    color:#0B3D2E;
    font-size:21px;
}

.tx-panel {
    border:1px solid #DCE5E0;
    border-radius:17px;
    background:#fff;
    overflow:hidden;
}

.tx-filter {
    display:flex;
    gap:8px;
    padding:15px;
    border-bottom:1px solid #E6ECE9;
}

.tx-search {
    position:relative;
    flex:1;
}

.tx-search i {
    position:absolute;
    top:50%;
    left:11px;
    transform:translateY(-50%);
    color:#8A9690;
    font-size: 11px;
}

.tx-search input,
.tx-filter select {
    width:100%;
    height:40px;
    border:1px solid #DCE5E0;
    border-radius:9px;
    background:#fff;
    font-size: 11px;
    outline:none;
}

.tx-search input {
    padding:0 12px 0 31px;
}

.tx-filter select {
    width:170px;
    padding:0 10px;
}

.tx-filter button {
    padding:0 17px;
    border:0;
    border-radius:9px;
    background:#0B3D2E;
    color:#fff;
    font-size: 11px;
    font-weight:700;
}

.tx-row {
    display:grid;
    grid-template-columns:minmax(220px,1.7fr) 1fr .8fr 1fr auto;
    align-items:center;
    gap:14px;
    padding:14px 16px;
    border-bottom:1px solid #EEF2F0;
}

.tx-product {
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
}

.tx-product-icon {
    width:38px;
    height:38px;
    flex:0 0 38px;
    display:grid;
    place-items:center;
    border-radius:10px;
    background:#E8F7EF;
    color:#087443;
}

.tx-product a {
    display:block;
    overflow:hidden;
    color:#18241F;
    font-size: 12px;
    font-weight:800;
    text-decoration:none;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.tx-product span,
.tx-person span,
.tx-amount span {
    display:block;
    margin-top:2px;
    color:#8A9690;
    font-size: 9px;;
}

.tx-person strong,
.tx-amount strong {
    display:block;
    margin-top:3px;
    color:#344139;
    font-size: 11px;
}

.tx-badge {
    display:inline-flex;
    padding:5px 8px;
    border-radius:999px;
    background:#F2F4F3;
    color:#59655F;
    font-size: 9px;;
    font-weight:800;
}

.tx-badge.payment-secured,
.tx-badge.completed {
    background:#ECFDF3;
    color:#067647;
}

.tx-badge.awaiting-payment {
    background:#FFF7E8;
    color:#B54708;
}

.tx-badge.dispatched {
    background:#EFF8FF;
    color:#175CD3;
}

.tx-badge.inspection {
    background:#F4F3FF;
    color:#6941C6;
}

.tx-badge.disputed {
    background:#FEF3F2;
    color:#B42318;
}

.tx-status small {
    display:block;
    margin-top:4px;
    color:#98A29D;
    font-size: 9px;;
}

.tx-actions {
    display:flex;
    align-items:center;
    gap:6px;
}

.tx-copy {
    width:34px;
    height:34px;
    border:1px solid #DCE5E0;
    border-radius:9px;
    background:#fff;
    color:#0B3D2E;
    cursor:pointer;
}

.tx-open {
    display:flex;
    align-items:center;
    gap:5px;
    padding:9px 11px;
    border-radius:9px;
    background:#F2FCF6;
    color:#087443;
    font-size: 10px;
    font-weight:800;
    text-decoration:none;
}

.tx-empty {
    padding:60px 20px;
    text-align:center;
}

.tx-empty i {
    color:#12B76A;
    font-size:28px;
}

.tx-empty h3 {
    margin:10px 0 4px;
}

.tx-empty p {
    margin:0;
    color:#7C8882;
    font-size: 11px;
}

.tx-pagination {
    padding:15px;
}

@media(max-width:900px) {
    .tx-stats {
        grid-template-columns:repeat(2,1fr);
    }

    .tx-row {
        grid-template-columns:1fr;
    }
}

</style>

<script>

document.addEventListener('click', async function (event) {
    const button = event.target.closest('.tx-copy');

    if (!button) {
        return;
    }

    try {
        await navigator.clipboard.writeText(
            button.dataset.copy
        );

        button.innerHTML =
            '<i class="fa-solid fa-check"></i>';

        setTimeout(function () {
            button.innerHTML =
                '<i class="fa-regular fa-copy"></i>';
        }, 1500);
    } catch (error) {
        console.error(error);
    }
});

</script>