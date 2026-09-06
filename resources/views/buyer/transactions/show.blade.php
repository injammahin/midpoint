@extends('buyer.layouts.app')

@section('title', $transaction->reference)

@section('content')

@include(
    'shared.transactions.show',
    [
        'mode' => 'buyer',
    ]
)


@if(
    $reviewRequired
    ?? false
)

    @include(
        'buyer.transactions.partials.seller-review-modal',
        [
            'transaction' =>
                $transaction,
        ]
    )

@endif

@endsection
