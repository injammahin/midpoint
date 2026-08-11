@extends('buyer.layouts.app')


@section('title', 'Notifications')


@section('content')

@include(
    'shared.notifications.index',
    [
        'mode' =>
            'buyer',

        'markAllRoute' =>
            route(
                'buyer.notifications.read-all'
            ),

        'openRouteName' =>
            'buyer.notifications.open',
    ]
)

@endsection