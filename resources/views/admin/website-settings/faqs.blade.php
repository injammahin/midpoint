@extends('admin.layouts.app')

@section('title', 'FAQ Page')
@section('page-title', 'FAQ Page')

@section('content')

<div class="admin-module-header">

    <h2>
        FAQ Management
    </h2>

    <p>
        Manage questions and answers displayed on the public website.
    </p>

</div>


<div class="admin-card admin-empty-module">

    <div>

        <div class="admin-empty-module-icon">
            <i class="fa-regular fa-circle-question"></i>
        </div>

        <h3>
            FAQ Page
        </h3>

        <p>
            FAQ CRUD, ordering, active status and homepage
            visibility will be implemented here.
        </p>

    </div>

</div>

@endsection