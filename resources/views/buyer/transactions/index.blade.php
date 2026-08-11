@extends('buyer.layouts.app')

@section('title', 'Transactions')

@section('content')

@include(
    'shared.transactions.index',
    [
        'mode' => 'buyer',
    ]
)

@endsection