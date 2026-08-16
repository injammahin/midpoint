@extends('seller.layouts.app')

@section('title', $transaction->reference)

@section('content')


@include(
    'shared.transactions.show',
    [
        'mode' => 'seller',
    ]
)


@endsection