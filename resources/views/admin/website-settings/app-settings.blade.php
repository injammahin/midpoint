@extends('admin.layouts.app')

@section('title', 'App Settings')
@section('page-title', 'App Settings')

@section('content')

<div class="admin-module-header">

    <h2>
        App Settings
    </h2>

    <p>
        Manage global MidPoint website and application settings.
    </p>

</div>


<div class="admin-card admin-empty-module">

    <div>

        <div class="admin-empty-module-icon">
            <i class="fa-solid fa-gear"></i>
        </div>

        <h3>
            Application Settings
        </h3>

        <p>
            Site identity, contact details, social links,
            payment settings and platform configuration
            will be managed here.
        </p>

    </div>

</div>

@endsection