@extends($layout)


@section(
    'title',
    'Dispute Resolution Room | Midpoint'
)


@section('content')

<div class="participant-dispute-page">

    <div class="participant-dispute-head">

        <div>

            <div class="participant-dispute-eyebrow">
                Transaction dispute
            </div>


            <h1>

                {{ $transaction->reference }}

            </h1>


            <p>

                {{
                    $transaction->title
                    ?:
                    'Midpoint secure transaction'
                }}

            </p>

        </div>


        <a
            href="{{
                $role === 'seller'
                    ? route(
                        'seller.transactions.show',
                        [
                            'secureTransaction' =>
                                $transaction->public_token,
                        ]
                    )
                    : route(
                        'buyer.transactions.show',
                        [
                            'secureTransaction' =>
                                $transaction->public_token,
                        ]
                    )
            }}"
            class="participant-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to transaction

        </a>

    </div>


    <div class="participant-dispute-summary">

        <div>

            <span>
                Buyer paid
            </span>

            <strong>

                ₦{{
                    number_format(
                        (float)
                        $transaction->paid_amount,
                        2
                    )
                }}

            </strong>

        </div>


        <div>

            <span>
                Dispute status
            </span>

            <strong>

                {{ $dispute->status_label }}

            </strong>

        </div>


        <div>

            <span>
                Opened
            </span>

            <strong>

                {{
                    optional(
                        $dispute->opened_at
                    )->format(
                        'd M Y'
                    )
                }}

            </strong>

        </div>

    </div>


    @include(
        'shared.disputes.room-panel',
        [
            'dispute' =>
                $dispute,

            'messages' =>
                $messages,

            'roomRole' =>
                $role,

            'adminMode' =>
                false,
        ]
    )

</div>

@endsection


@push('styles')

<style>

    .participant-dispute-page {

        width:
            100%;

    }


    .participant-dispute-head {

        display:
            flex;

        align-items:
            flex-start;

        justify-content:
            space-between;

        gap:
            20px;

        margin-bottom:
            18px;

    }


    .participant-dispute-eyebrow {

        margin-bottom:
            4px;

        color:
            #12B76A;

        font-size:
            10px;

        font-weight:
            800;

        letter-spacing:
            .10em;

        text-transform:
            uppercase;

    }


    .participant-dispute-head h1 {

        margin:
            0;

        color:
            #13231B;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            24px;

        font-weight:
            800;

    }


    .participant-dispute-head p {

        margin:
            3px 0 0;

        color:
            #718078;

        font-size:
            10px;

    }


    .participant-back {

        display:
            inline-flex;

        align-items:
            center;

        gap:
            6px;

        min-height:
            38px;

        padding:
            0 12px;

        border:
            1px solid #DCE5E0;

        border-radius:
            9px;

        background:
            #FFFFFF;

        color:
            #0B3D2E;

        font-size:
            9px;

        font-weight:
            800;

        text-decoration:
            none;

    }


    .participant-dispute-summary {

        display:
            grid;

        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );

        gap:
            10px;

        margin-bottom:
            15px;

    }


    .participant-dispute-summary > div {

        padding:
            13px;

        border:
            1px solid #E1E8E4;

        border-radius:
            11px;

        background:
            #FFFFFF;

    }


    .participant-dispute-summary span,
    .participant-dispute-summary strong {

        display:
            block;

    }


    .participant-dispute-summary span {

        color:
            #7B8781;

        font-size:
            8px;

    }


    .participant-dispute-summary strong {

        margin-top:
            3px;

        color:
            #26342D;

        font-size:
            11px;

    }


    @media(max-width: 640px) {

        .participant-dispute-head {

            flex-direction:
                column;

        }


        .participant-dispute-summary {

            grid-template-columns:
                1fr;

        }

    }

</style>

@endpush
