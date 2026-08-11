@extends('seller.layouts.app')


@section('title', 'Transaction Created')


@section('content')


<div class="tg-page">

    @if (session('success'))

        <div class="tg-success">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif
    @if (session('warning'))

        <div class="tg-warning">

            <i class="fa-solid fa-triangle-exclamation"></i>


            <div>

                <strong>
                    Email delivery issue
                </strong>


                <span>
                    {{ session('warning') }}
                </span>

            </div>

        </div>

    @endif


    <div class="tg-card">


        <div class="tg-icon">

            <i class="fa-solid fa-link"></i>

        </div>


        <div class="tg-status">
            Awaiting buyer
        </div>


        <h1>
            Secure transaction created
        </h1>


        <p class="tg-intro">

            MidPoint has sent this secure transaction link to

            <strong>
                {{ $transaction->buyer_email }}
            </strong>.

            You can also copy or share the link manually below.

        </p>



        <div class="tg-reference">

            <span>
                Transaction reference
            </span>

            <strong>
                {{ $transaction->reference }}
            </strong>

        </div>



        <div class="tg-summary">

            <div>

                <span>
                    Item
                </span>

                <strong>
                    {{ $transaction->title }}
                </strong>

            </div>


            <div>

                <span>
                    Quantity
                </span>

                <strong>
                    {{ $transaction->quantity }}
                </strong>

            </div>


            <div>

                <span>
                    Buyer pays
                </span>

                <strong>
                    ₦{{ number_format((float) $transaction->total_amount, 2) }}
                </strong>

            </div>


            <div>

                <span>
                    Link expires
                </span>

                <strong>
                    {{
                        $transaction
                            ->link_expires_at
                            ?->format(
                                'd M Y, h:i A'
                            )
                    }}
                </strong>

            </div>

        </div>



        <div class="tg-link-label">
            Secure buyer link
        </div>


        <div class="tg-link-box">

            <input
                id="generatedTransactionLink"
                type="text"
                readonly
                value="{{ $transaction->share_url }}"
            >


            <button
                type="button"
                id="copyTransactionLink"
            >

                <i class="fa-regular fa-copy"></i>

                Copy

            </button>

        </div>



        <div class="tg-actions">

            <a
                href="https://wa.me/?text={{ rawurlencode(
                    'I created a secure MidPoint transaction for you: '
                    .
                    $transaction->share_url
                ) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="whatsapp"
            >

                <i class="fa-brands fa-whatsapp"></i>

                Share on WhatsApp

            </a>


            <a
                href="{{ $transaction->share_url }}"
                target="_blank"
                class="preview"
            >

                <i class="fa-regular fa-eye"></i>

                Open link

            </a>

        </div>



        <div class="tg-info">

            <i class="fa-solid fa-shield-halved"></i>


            <div>

                <strong>
                    Buyer protected link
                </strong>

                <p>
                    Only the MidPoint account using
                    {{ $transaction->buyer_email }}
                    can claim this transaction.
                </p>

            </div>

        </div>

    </div>

</div>



@push('styles')

<style>

.tg-page {
    max-width: 720px;
    margin: 0 auto;
}
.tg-warning {
    display: flex;
    align-items: flex-start;
    gap: 9px;

    margin-bottom: 14px;

    padding: 12px 14px;

    border: 1px solid #F5D199;
    border-radius: 11px;

    background: #FFF8EB;

    color: #9A5B13;

    font-size:11px;
}

.tg-warning > i {
    margin-top: 2px;
}

.tg-warning strong,
.tg-warning span {
    display: block;
}

.tg-warning strong {
    margin-bottom: 3px;

    color: #7A4610;

    font-size:11px;
}

.tg-warning span {
    color: #9A671F;
    line-height: 1.5;
}
.tg-success {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 14px;
    padding: 11px 13px;
    border: 1px solid #ABEFC6;
    border-radius: 10px;
    background: #ECFDF3;
    color: #067647;
    font-size:11px;
}

.tg-card {
    padding: 38px;
    border: 1px solid #DCE5E0;
    border-radius: 20px;
    background: #FFFFFF;
    box-shadow: 0 18px 50px -38px rgba(11,61,46,.35);
    text-align: center;
}

.tg-icon {
    width: 58px;
    height: 58px;
    display: grid;
    place-items: center;
    margin: 0 auto 11px;
    border-radius: 16px;
    background: #E8F7EF;
    color: #087443;
    font-size: 19px;
}

.tg-status {
    display: inline-flex;
    margin-bottom: 10px;
    padding: 5px 8px;
    border-radius: 999px;
    background: #FFF7E8;
    color: #B54708;
    font-size: 7px;
    font-weight: 800;
}

.tg-card h1 {
    margin: 0;
    font-family: 'Bricolage Grotesque',sans-serif;
    font-size: 24px;
    font-weight: 800;
}

.tg-intro {
    max-width: 520px;
    margin: 8px auto 20px;
    color: #69766F;
    font-size:12px;
    line-height: 1.65;
}

.tg-reference {
    padding: 13px;
    border-radius: 11px;
    background: #F7F9F8;
}

.tg-reference span,
.tg-reference strong {
    display: block;
}

.tg-reference span {
    color: #7A8780;
    font-size: 7px;
}

.tg-reference strong {
    margin-top: 3px;
    color: #17251F;
    font-size: 12px;
}

.tg-summary {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 8px;
    margin: 16px 0;
}

.tg-summary > div {
    padding: 12px;
    border: 1px solid #E3E9E6;
    border-radius: 10px;
    text-align: left;
}

.tg-summary span,
.tg-summary strong {
    display: block;
}

.tg-summary span {
    color: #7C8882;
    font-size: 7px;
}

.tg-summary strong {
    margin-top: 3px;
    color: #26342D;
    font-size:11px;
}

.tg-link-label {
    margin: 19px 0 6px;
    color: #344139;
    font-size:11px;
    font-weight: 800;
    text-align: left;
}

.tg-link-box {
    display: flex;
    gap: 7px;
}

.tg-link-box input {
    min-width: 0;
    flex: 1;
    height: 44px;
    padding: 0 11px;
    border: 1px solid #DCE5E0;
    border-radius: 10px;
    background: #F8FAF9;
    color: #526059;
    font-size:11px;
}

.tg-link-box button {
    min-width: 86px;
    border: 0;
    border-radius: 10px;
    background: #0B3D2E;
    color: #FFFFFF;
    font-size:11px;
    font-weight: 800;
    cursor: pointer;
}

.tg-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 10px;
}

.tg-actions a {
    min-height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
    font-size:11px;
    font-weight: 800;
    text-decoration: none;
}

.tg-actions .whatsapp {
    background: #16A34A;
    color: #FFFFFF;
}

.tg-actions .preview {
    border: 1px solid #DCE5E0;
    color: #0B3D2E;
}

.tg-info {
    display: flex;
    gap: 9px;
    margin-top: 18px;
    padding: 12px;
    border-radius: 11px;
    background: #F2FCF6;
    color: #087443;
    text-align: left;
}

.tg-info strong {
    font-size:11px;
}

.tg-info p {
    margin: 2px 0 0;
    color: #637E70;
    font-size: 8px;
}

@media(max-width:600px) {

    .tg-card {
        padding: 22px;
    }

    .tg-summary,
    .tg-actions {
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

        const button =
            document.getElementById(
                'copyTransactionLink'
            );


        const input =
            document.getElementById(
                'generatedTransactionLink'
            );


        button?.addEventListener(
            'click',
            async function () {

                try {

                    await navigator
                        .clipboard
                        .writeText(
                            input.value
                        );


                    const original =
                        button.innerHTML;


                    button.innerHTML =
                        '<i class="fa-solid fa-check"></i> Copied';


                    setTimeout(
                        function () {

                            button.innerHTML =
                                original;
                        },
                        1800
                    );

                } catch (error) {

                    input.select();

                    document.execCommand(
                        'copy'
                    );
                }
            }
        );

    }
);

</script>

@endpush


@endsection