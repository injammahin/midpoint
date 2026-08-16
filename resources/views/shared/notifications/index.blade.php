@php

    $isSeller =
        $mode === 'seller';

    $tabs = [
        'all' => [
            'label' => 'All',
            'icon' => 'fa-border-all',
        ],

        'payment' => [
            'label' => 'Payments',
            'icon' => 'fa-money-bill-wave',
        ],

        'dispatch' => [
            'label' => 'Dispatch',
            'icon' => 'fa-box',
        ],

        'inspection' => [
            'label' => 'Inspection',
            'icon' => 'fa-stopwatch',
        ],

        'dispute' => [
            'label' => 'Disputes',
            'icon' => 'fa-scale-balanced',
        ],
    ];

@endphp


<div class="nt-page">


    @if(session('success'))

        <div class="nt-alert">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif



    <div class="nt-header">

        <div>

            <span class="nt-eyebrow">

                {{
                    $isSeller
                        ? 'Seller updates'
                        : 'Buyer updates'
                }}

            </span>


            <h1>
                Notification centre
            </h1>


            <p>

                {{
                    $isSeller
                        ? 'Stay updated on buyer payments, delivery progress, inspections and disputes.'
                        : 'Stay updated on payments, seller fulfilment, delivery, inspections and disputes.'
                }}

            </p>

        </div>



        <div class="nt-header-actions">


            @if($unreadCount > 0)

                <span class="nt-unread-summary">

                    <span class="nt-unread-dot"></span>

                    {{ $unreadCount }}

                    {{
                        $unreadCount === 1
                            ? 'unread'
                            : 'unread'
                    }}

                </span>

            @endif



            <form
                method="POST"
                action="{{ $markAllRoute }}"
            >

                @csrf


                <button
                    type="submit"
                    class="nt-mark-all"
                    {{ $unreadCount === 0 ? 'disabled' : '' }}
                >

                    <i class="fa-solid fa-check-double"></i>

                    Mark all as read

                </button>

            </form>

        </div>

    </div>



    <div class="nt-tabs">

        @foreach($tabs as $value => $tab)

            <a
                href="{{
                    request()->fullUrlWithQuery([
                        'filter' => $value,
                        'page' => null,
                    ])
                }}"

                class="
                    nt-tab
                    {{ $filter === $value ? 'active' : '' }}
                "
            >

                <i class="fa-solid {{ $tab['icon'] }}"></i>


                <span>
                    {{ $tab['label'] }}
                </span>


                @if(($counts[$value] ?? 0) > 0)

                    <small>
                        {{ $counts[$value] }}
                    </small>

                @endif

            </a>

        @endforeach

    </div>



    <div class="nt-card">


        @forelse($notifications as $notification)

            @php

                $type =
                    $notification->type
                    ?:
                    'general';


                $icon =
                    match($type) {

                        'payment' =>
                            'fa-money-bill-wave',

                        'dispatch' =>
                            'fa-box',

                        'inspection' =>
                            'fa-stopwatch',

                        'dispute' =>
                            'fa-scale-balanced',

                        default =>
                            'fa-bell',
                    };


                $openUrl =
                    route(
                        $openRouteName,
                        $notification
                    );


                $transaction =
                    $notification->transaction;

            @endphp


            <a
                href="{{ $openUrl }}"

                class="
                    nt-notification
                    {{ !$notification->read_at ? 'unread' : '' }}
                "
            >


                @if(!$notification->read_at)

                    <span class="nt-new-line"></span>

                @endif



                <div
                    class="
                        nt-icon
                        nt-icon-{{ $type }}
                    "
                >

                    <i
                        class="
                            fa-solid
                            {{ $icon }}
                        "
                    ></i>

                </div>



                <div class="nt-content">


                    <div class="nt-title-row">

                        <strong>
                            {{ $notification->title }}
                        </strong>


                        @if(!$notification->read_at)

                            <span class="nt-new-badge">
                                New
                            </span>

                        @endif

                    </div>



                    @if($notification->message)

                        <p>
                            {{ $notification->message }}
                        </p>

                    @endif



                    @if($transaction)

                        <div class="nt-transaction-meta">

                            <span>

                                <i class="fa-solid fa-shield-halved"></i>

                                {{ $transaction->reference }}

                            </span>


                            <span>

                                <i class="fa-solid fa-box-open"></i>

                                {{ $transaction->title }}

                            </span>


                            @if(
                                $isSeller
                                &&
                                $transaction->isMarketplaceCheckout()
                            )

                                <span>

                                    <i class="fa-solid fa-bag-shopping"></i>

                                    Marketplace order · Qty
                                    {{ number_format(
                                        (int) $transaction->quantity
                                    ) }}

                                </span>

                            @endif


                            @if(
                                $type === 'payment'
                                &&
                                $transaction->paid_amount
                            )

                                <span>

                                    <i class="fa-solid fa-naira-sign"></i>

                                    ₦{{ number_format(
                                        (float) $transaction->paid_amount,
                                        2
                                    ) }}

                                </span>

                            @endif

                        </div>

                    @endif

                </div>



                <div class="nt-time">

                    <span>
                        {{ $notification->created_at->diffForHumans() }}
                    </span>


                    <i class="fa-solid fa-chevron-right"></i>

                </div>

            </a>


        @empty

            <div class="nt-empty">

                <div class="nt-empty-icon">

                    <i class="fa-regular fa-bell"></i>

                </div>


                <h3>
                    No notifications here yet
                </h3>


                <p>

                    @if($filter === 'all')

                        New transaction activity will appear here automatically.

                    @else

                        You don't have any {{ strtolower($tabs[$filter]['label']) }} notifications yet.

                    @endif

                </p>

            </div>

        @endforelse

    </div>



    @if($notifications->hasPages())

        <div class="nt-pagination">

            {{ $notifications->links() }}

        </div>

    @endif

</div>



<style>

.nt-page {
    width: 100%;
}


.nt-alert {
    display: flex;
    align-items: center;
    gap: 9px;

    margin-bottom: 16px;
    padding: 13px 15px;

    border: 1px solid #ABEFC6;
    border-radius: 10px;

    background: #ECFDF3;

    color: #067647;

    font-size: 13px;
    font-weight: 600;
}


.nt-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 25px;

    margin-bottom: 21px;
}


.nt-eyebrow {
    display: block;

    color: #12B76A;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: .12em;

    text-transform: uppercase;
}


.nt-header h1 {
    margin: 6px 0 5px;

    color: #101915;

    font-family:
        'Bricolage Grotesque',
        sans-serif;

    font-size: 28px;
    font-weight: 800;
}


.nt-header p {
    max-width: 570px;

    margin: 0;

    color: #728078;

    font-size: 13px;
    line-height: 1.6;
}


.nt-header-actions {
    display: flex;
    align-items: center;

    gap: 10px;
}


.nt-unread-summary {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    min-height: 37px;

    padding: 0 11px;

    border: 1px solid #DCE5E0;
    border-radius: 9px;

    background: #FFFFFF;

    color: #536159;

    font-size: 10px;
    font-weight: 700;
}


.nt-unread-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #F04438;
}


.nt-mark-all {
    min-height: 37px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 0 13px;

    border: 1px solid #DCE5E0;
    border-radius: 9px;

    background: #FFFFFF;

    color: #0B3D2E;

    font-family: inherit;

    font-size: 10px;
    font-weight: 800;

    cursor: pointer;
}


.nt-mark-all:hover:not(:disabled) {
    border-color: #12B76A;

    background: #F2FCF6;
}


.nt-mark-all:disabled {
    opacity: .5;

    cursor: not-allowed;
}


.nt-tabs {
    display: flex;
    align-items: center;

    gap: 3px;

    margin-bottom: 14px;
    padding: 4px;

    border: 1px solid #DCE5E0;
    border-radius: 11px;

    background: #FFFFFF;
}


.nt-tab {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    min-height: 38px;

    padding: 0 13px;

    border-radius: 8px;

    color: #647169;

    font-size: 10px;
    font-weight: 700;

    text-decoration: none;
}


.nt-tab i {
    color: #8A9690;
}


.nt-tab small {
    min-width: 19px;
    height: 19px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 5px;

    border-radius: 999px;

    background: #F0F3F1;

    color: #617068;

    font-size: 10px;
    font-weight: 800;
}


.nt-tab:hover {
    background: #F7F9F8;

    color: #0B3D2E;
}


.nt-tab.active {
    background: #0B4B3C;

    color: #FFFFFF;
}


.nt-tab.active i {
    color: #FFFFFF;
}


.nt-tab.active small {
    background: rgba(255, 255, 255, .16);

    color: #FFFFFF;
}


.nt-card {
    overflow: hidden;

    border: 1px solid #DCE5E0;
    border-radius: 15px;

    background: #FFFFFF;

    box-shadow:
        0 18px 45px -38px
        rgba(11, 61, 46, .4);
}


.nt-notification {
    position: relative;

    display: grid;

    grid-template-columns:
        45px
        minmax(0, 1fr)
        auto;

    align-items: start;

    gap: 12px;

    min-height: 86px;

    padding: 16px 17px;

    border-bottom: 1px solid #E7ECE9;

    color: inherit;

    text-decoration: none;

    transition:
        background .15s ease,
        transform .15s ease;
}


.nt-notification:last-child {
    border-bottom: 0;
}


.nt-notification:hover {
    background: #F9FCFA;
}


.nt-notification.unread {
    background: #FCFFFD;
}


.nt-new-line {
    position: absolute;

    top: 0;
    bottom: 0;
    left: 0;

    width: 3px;

    background: #12B76A;
}


.nt-icon {
    width: 42px;
    height: 42px;

    display: grid;
    place-items: center;

    border-radius: 11px;

    font-size: 15px;
}


.nt-icon-payment {
    background: #EAF8F1;

    color: #087443;
}


.nt-icon-dispatch {
    background: #F3EEFF;

    color: #6941C6;
}


.nt-icon-inspection {
    background: #FFF7E8;

    color: #B54708;
}


.nt-icon-dispute {
    background: #FEF3F2;

    color: #D92D20;
}


.nt-icon-general {
    background: #EFF8FF;

    color: #175CD3;
}


.nt-content {
    min-width: 0;
}


.nt-title-row {
    display: flex;
    align-items: center;

    gap: 8px;
}


.nt-title-row strong {
    color: #17251F;

    font-size: 13px;
    font-weight: 800;

    line-height: 1.45;
}


.nt-new-badge {
    padding: 3px 6px;

    border-radius: 999px;

    background: #ECFDF3;

    color: #067647;

    font-size: 10px;
    font-weight: 800;
}


.nt-content p {
    margin: 5px 0 0;

    color: #66756D;

    font-size: 11px;
    line-height: 1.55;
}


.nt-transaction-meta {
    display: flex;
    flex-wrap: wrap;

    gap: 8px 15px;

    margin-top: 9px;
}


.nt-transaction-meta span {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    color: #839087;

    font-size: 10px;
    font-weight: 600;
}


.nt-transaction-meta i {
    color: #12B76A;
}


.nt-time {
    display: flex;
    align-items: center;

    gap: 10px;

    padding-top: 2px;

    color: #98A29D;
}


.nt-time span {
    white-space: nowrap;

    font-size: 10px;
}


.nt-time i {
    font-size: 10px;
}


.nt-empty {
    padding: 70px 20px;

    text-align: center;
}


.nt-empty-icon {
    width: 58px;
    height: 58px;

    display: grid;
    place-items: center;

    margin: 0 auto;

    border-radius: 16px;

    background: #EAF8F1;

    color: #087443;

    font-size: 21px;
}


.nt-empty h3 {
    margin: 13px 0 4px;

    color: #17251F;

    font-size: 15px;
}


.nt-empty p {
    margin: 0;

    color: #7A8780;

    font-size: 11px;
}


.nt-pagination {
    margin-top: 16px;
}


@media(max-width: 850px) {

    .nt-header {
        flex-direction: column;
    }


    .nt-header-actions {
        width: 100%;
    }


    .nt-tabs {
        overflow-x: auto;
    }


    .nt-tab {
        flex: 0 0 auto;
    }


    .nt-notification {
        grid-template-columns:
            42px
            minmax(0, 1fr);
    }


    .nt-time {
        grid-column: 2;

        justify-content: space-between;
    }

}


@media(max-width: 520px) {

    .nt-header h1 {
        font-size: 23px;
    }


    .nt-header-actions {
        align-items: stretch;
        flex-direction: column;
    }


    .nt-mark-all,
    .nt-unread-summary {
        width: 100%;
        justify-content: center;
    }


    .nt-notification {
        padding: 14px 13px;
    }

}

</style>