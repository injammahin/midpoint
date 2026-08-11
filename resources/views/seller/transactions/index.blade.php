@extends('seller.layouts.app')

@section('title', 'Transactions')

@section('content')

@include(
    'shared.transactions.index',
    [
        'mode' => 'seller',
    ]
)

@endsection