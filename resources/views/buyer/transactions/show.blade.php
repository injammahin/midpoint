@extends('buyer.layouts.app')

@section('title', $transaction->reference)

@section('content')

@include(
    'shared.transactions.show',
    [
        'mode' => 'buyer',
    ]
)

@endsection