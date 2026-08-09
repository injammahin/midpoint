@extends('account.layouts.app')


@section(
    'title',
    $pageTitle
)


@section('content')

<div class="account-placeholder">

    <div class="account-placeholder-icon">

        <i
            class="fa-solid {{
                $pageIcon
            }}"
        ></i>

    </div>


    <h1>
        {{ $pageTitle }}
    </h1>


    <p>

        This module is ready in the navigation.
        We will build its complete functionality next.

    </p>


    <a
        href="{{
            $dashboardRole === 'seller'
                ? route('seller.dashboard')
                : route('buyer.dashboard')
        }}"
        class="dashboard-primary-button"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to dashboard

    </a>

</div>

@endsection