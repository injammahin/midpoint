@extends('seller.layouts.app')


@section('title', 'Notifications')


@section('content')

@include(
    'shared.notifications.index',
    [
        'mode' =>
            'seller',

        'markAllRoute' =>
            route(
                'seller.notifications.read-all'
            ),

        'openRouteName' =>
            'seller.notifications.open',
    ]
)

@endsection