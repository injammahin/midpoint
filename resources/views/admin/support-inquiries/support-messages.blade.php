@extends('admin.layouts.app')

@section('title', 'Support Messages')
@section('page-title', 'Support Messages')

@section('content')

<div class="admin-module-header">

    <h2>
        Support Messages
    </h2>

    <p>
        Customer support requests and transaction assistance.
    </p>

</div>


<div class="admin-card admin-empty-module">

    <div>

        <div class="admin-empty-module-icon">
            <i class="fa-solid fa-headset"></i>
        </div>

        <h3>
            Support Inbox
        </h3>

        <p>
            Support tickets, priorities, assigned administrators,
            conversation history and resolution status will
            be handled here.
        </p>

    </div>

</div>

@endsection